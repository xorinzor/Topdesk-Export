<?php
/*
 * Copyright 2019 Jorin Vermeulen
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class Email extends Response
{
    private $url;

    public function __construct($user, $request, $timestamp, $hidden, $url)
    {
        parent::__construct($user, $request, $timestamp, $hidden);

        $this->url = $url;
    }

    public function getName() : string {
        return $this->timestamp->format('Y-m-d H.i.s') . " - " . filter_filename($this->getRequest()) . ".email.json";
    }

    public function getUrl() : string {
        return $this->url;
    }

    public function getContent() : string {
        return
            "<div style='background-color:#ffe8a8;'>".
            "<p><b>Timestamp:</b> {$this->getTimestamp()}<br />".
            "<b>Email sent by:</b> $this->user</p>".
            "$this->request</div><hr /><br /><br />";
    }

}