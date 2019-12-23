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

class Response
{
    protected $user;
    protected $request;
    protected $email;
    protected $hidden;
    protected $timestamp;

    public function __construct($user, $request, $timestamp, $hidden) {
        $this->user = $user;
        $this->request = $request;
        $this->timestamp = new DateTime($timestamp);
        $this->hidden = $hidden;
    }

    public function isHidden() {
        return $this->hidden == true;
    }

    public function getTimestamp() : string {
        return $this->timestamp->format("d-m-Y H:i:s");
    }

    public function getRequest() {
        return $this->request;
    }

    public function getContent() : string {
        return '<div style="background-color: ' . ($this->isHidden() ? "#CCCCCC" : "#FFFFFF"). ';">'.
                "<p><b>Timestamp:</b> {$this->getTimestamp()}<br />".
                "<b>User:</b> $this->user</p>".
                "$this->request".
                '</div><hr /><br /><br />';
    }

}