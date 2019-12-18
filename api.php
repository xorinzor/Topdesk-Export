<?php


class api
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

    private function getFromCache($id) {
        return file_get_contents("cache".DIRECTORY_SEPARATOR.$id.".cache");
    }

    private function addToCache($id, $content) {
        file_put_contents("cache".DIRECTORY_SEPARATOR.$id.".cache", $content);
    }

    private function cacheExists($id) {
        return file_exists("cache".DIRECTORY_SEPARATOR.$id.".cache");
    }

    public function getResponse($url) : string {
        $headers = [
            'Accept: application/json',
            'Authorization: Basic ' . $this->authToken,
            'Cache-Control: no-cache',
            'Accept-Encoding: gzip, deflate'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_ENCODING,"");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close ($ch);

        if($response === false) {
            throw new Exception("An error occured during the cURL request");
        }

        return $response;
    }

    private function makeCall($url, $headers = array()) : string {
        $headers = array_merge([
            'Accept: application/json',
            'Authorization: Basic ' . $this->authToken,
            'Cache-Control: no-cache',
            'Accept-Encoding: gzip, deflate'
        ], $headers);

        $uniqId = md5($url.json_encode($headers));

        if($this->cacheExists($uniqId)) {
            return $this->getFromCache($uniqId);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_ENCODING,"");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close ($ch);

        if($response === false) {
            throw new Exception("An error occured during the cURL request");
        }

        $this->addToCache($uniqId, $response);

        return $response;
    }

    public function getIncidentIds($count = 99999) {
        $result = json_decode($this->makeCall($this->baseUrl . 'incidents?page_size=' . $count));

        echo '<pre>';
        array_walk($result, function(&$item, $key) {
            return $item = $item->number;
        });

        return $result;
    }

    public function getIncident($id) {
        $inc = json_decode($this->makeCall($this->baseUrl . 'incidents/number/' . $id));

        //Process every incident
        $i = new Incident($inc->id, $inc->number, $inc->briefDescription, $inc->operatorGroup->name, $inc->operator->name, $inc->caller->dynamicName, $inc->callDate, $inc);
        $i->addResponse(new Response($inc->caller->dynamicName, nl2br($inc->request), $inc->callDate, 0));

        $responses = $this->getProgressTrail($inc->id);
        if (!is_null($responses)) {
            $responses = array_reverse($responses);
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
                } //ticket action (ie: Afmelden email sent to person)
                elseif (property_exists($response, "details")) {
                    $i->addResponse(new Email($response->sender, $response->title, $response->entryDate, 0, $response->details));
                } //text
                else {
                    $i->addResponse(new Response($response->operator->name, $response->memoText, $response->entryDate, $response->invisibleForCaller));
                }
            }
        }

        unset($inc);

        return $i;
    }

    public function getProgressTrail($ticketId) {
        return json_decode($this->makeCall($this->baseUrl . 'incidents/id/'.$ticketId.'/progresstrail?page_size=100&inlineimages=true'));
    }
}