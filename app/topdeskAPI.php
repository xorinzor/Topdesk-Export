<?php

class topdeskAPI
{
    private $baseUrl;
    private $username;
    private $password;
    private $authToken;

    public function __construct($baseUrl, $username, $password) {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;

        $this->authToken = base64_encode($username . ":" . $password);
    }

    /**
     * Fetches a file from cache (if it exists)
     * @param $id
     * @return false|string
     */
    private function getFromCache($id) {
        return json_decode(file_get_contents(".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $id . ".cache"), true);
    }

    /**
     * Deletes a file from cache (if it exists)
     * @param $id
     */
    private function removeFromCache($id) {
        @unlink(".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $id . ".cache");
    }

    /**
     * Adds a file to the cache
     * @param $id
     * @param $content
     */
    private function addToCache($id, $content) {
        file_put_contents(".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $id . ".cache", json_encode($content));
    }

    /**
     * Checks if a file exists in cache
     * @param $id
     * @return bool
     */
    private function cacheExists($id) {
        return file_exists(".." . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $id . ".cache");
    }

    /**
     * Makes an API call to the Topdesk installation, and returns the result.
     * Note: These results can return a HTTP-code 206, implying a partial result.
     * @param $url
     * @param array $params
     * @param array $headers
     * @param bool $enableCache
     * @param bool $decode
     * @return array|false|string
     * @throws Exception
     */
    public function makeCall($url, $params = array(), $headers = array(), $enableCache = true, $decode = false) {
        $headers = array_merge([
            'Accept: application/json',
            'Authorization: Basic ' . $this->authToken,
            'Cache-Control: no-cache',
            'Accept-Encoding: gzip, deflate'
        ], $headers);

        //Prevent duplicate headers, this would create an invalid HTTP request
        $headers = array_unique($headers);

        //Check if caching is enabled, and if so, create a unique ID for the request
        if($enableCache) {
            $uniqId = md5($url . json_encode($headers) . json_encode($params));
        }

        //If cache is enabled, check if a cache file exists for the request
        if ($enableCache && $this->cacheExists($uniqId)) {
            $response = $this->getFromCache($uniqId);
        }
        //No cache file found or caching is disabled, perform a new request.
        else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . ((count($params) > 0) ? '?' . http_build_query($params) : ''));
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                throw new Exception("An error occured during the cURL request");
            }

            $response = [
                'http_code' => $http_code,
                'result' => $response
            ];

            //Save the request to a cache file if a valid request has been made to prevent caching errors
            if ($enableCache && ($response['http_code'] === 200 || $http_code === 201 || $http_code === 206)) {
                $response['uniqId'] = $uniqId;
                $this->addToCache($uniqId, $response);
            }
        }

        //If $decode equals true, the response should be decoded.
        if($decode) {
            $response['result'] = json_decode($response['result']);
        }

        //Return the response.
        return $response;
    }

    /**
     * A wrapper for the makeCall() method to handle HTTP-Code 206, which would imply a partial result.
     * This method will keep fetching data until all the data has been fetched.
     * @param $url
     * @param array $params
     * @param array $headers
     * @param bool $enableCache
     * @param bool $decode
     * @return mixed
     * @throws Exception
     */
    public function makeCompleteCall($url, $params = array(), $headers = array(), $enableCache = true, $decode = false) {
        $tempCache = [];

        //Check if caching is enabled, and if so, create a unique ID for the request
        if($enableCache) {
            $uniqId = md5($url . json_encode($headers) . json_encode($params));
        }

        //If cache is enabled, check if a cache file exists for the request
        if ($enableCache && $this->cacheExists($uniqId)) {
            $response = $this->getFromCache($uniqId);
        }
        else {
            //Set the new offset for our next API call, this creates a unique uniqId for the first call
            //Preventing creating a cache file with the same ID as the makeCompleteCall (due to the same parameters)
            $params['start'] = 0;

            //Make the original API call with the same parameters.
            $call = $this->makeCall($url, $params, $headers, $enableCache, true);
            $response = $call['result'];

            //Add the unique ID from the call to our tempCache array for removal at a later stage.
            if($enableCache) {
                $tempCache[] = $call['uniqId'];
            }

            //Check if a partial result has been returned by the call.
            if ($call['http_code'] == 206) {
                $start = count($response);

                //Check if the offset needs adjusting based on the parameters passed to this call
                $start += $params['start'];

                //Keep making API calls if the call has returned a 206 code
                while ($call['http_code'] === 206) {
                    //Set the new offset for our next API call
                    $params['start'] = $start;

                    //Fetch the next set of data.
                    $call = $this->makeCall($url, $params, $headers, true, true);

                    //Add the unique ID from the call to our tempCache array for removal at a later stage.
                    if($enableCache) {
                        $tempCache[] = $call['uniqId'];
                    }

                    //Append the results to the "main" array
                    $response = array_merge($response, $call['result']);

                    //Adjust our offset
                    $start += count($call['result']);
                }
            }

            //Return the data from our request
            $response = [
                'http_code' => $call['http_code'],
                'result'  => ($decode) ? $response : json_encode($response)
            ];

            //Save the request to a cache file if a valid request has been made to prevent caching errors
            if ($enableCache && ($response['http_code'] === 200 || $response['http_code'] === 201)) {
                $response['uniqId'] = $uniqId;
                $this->addToCache($uniqId, $response);

                //Remove our temporary cache files for all sub-calls to free up space
                foreach($tempCache as $tempFile) {
                    $this->removeFromCache($tempFile);
                }
            }
        }

        //Return the result
        return $response;
    }

    /**
     * returns a list of all ticket IDs
     * @param int $count
     * @return mixed
     * @throws Exception
     */
    public function getIncidentIds($count = 1000) {
        $result = $this->makeCompleteCall($this->baseUrl . 'incidents', ['page_size' => $count], [], true, true);
        $result = $result['result'];

        array_walk($result, function(&$item, $key) {
            return $item = $item->number;
        });

        sort($result, SORT_STRING);

        return $result;
    }

    /**
     * Fetches a complete ticket (including responses and attachments)
     * @param $id
     * @return Incident
     * @throws Exception
     */
    public function getIncident($id) {
        $inc = $this->makeCall($this->baseUrl . 'incidents/number/' . $id, [], [], true, true);
        $inc = $inc['result'];

        //Create our Incident object
        $i = new Incident($inc->id, $inc->number, $inc->briefDescription, $inc->operatorGroup->name, $inc->operator->name, $inc->caller->dynamicName, $inc->callDate, $inc);

        //Add the request from the ticket as our first response
        $i->addResponse(new Response($inc->caller->dynamicName, nl2br($inc->request), $inc->callDate, 0));

        //Get all responses and attachments
        $responses = $this->getProgressTrail($inc->id);

        if (!is_null($responses)) {
            //By default Topdesk sorts the responses in descending order, we want it to be ascending.
            $responses = array_reverse($responses);

            //Parse every response, determine if it's an attachment, email, or response.
            foreach ($responses as $response) {
                //attachment
                if (property_exists($response, "downloadUrl")) {
                    $user = "System";
                    if (property_exists($response, "person") && !is_null($response->person)) {
                        $user = $response->person->name;
                    } elseif (property_exists($response, "operator") && !is_null($response->operator)) {
                        $user = $response->operator->name;
                    }
                    $i->addResponse(new File($user, $response->fileName, $response->entryDate, $response->invisibleForCaller, $response->downloadUrl, $response->fileName));
                }
                //Email ticket action (ie: Ticket closed, request for information, etc.)
                elseif (property_exists($response, "details")) {
                    $i->addResponse(new Email($response->sender, $response->title, $response->entryDate, 0, $response->details));
                }
                //Response
                else {
                    $i->addResponse(new Response($response->operator->name, $response->memoText, $response->entryDate, $response->invisibleForCaller));
                }
            }
        }

        //Return the ticket
        return $i;
    }

    /**
     * Returns all responses for a specific ticket
     * @param $ticketId
     * @return mixed
     * @throws Exception
     */
    public function getProgressTrail($ticketId) {
        return $this->makeCompleteCall($this->baseUrl . 'incidents/id/'.$ticketId.'/progresstrail', ['page_size' => 100, 'inlineimages' => 'true'], [], true, true)['result'];
    }
}