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

class File extends Response
{
    private $name;
    private $filename;
    private $url;

    public function __construct($user, $request, $timestamp, $hidden, $url, $filename)
    {
        parent::__construct($user, $request, $timestamp, $hidden);

        $this->url = $url;
        $this->filename = $filename;
    }

    public function getName() : string {
        return $this->timestamp->format('Y-m-d H.i.s') . " - " . filter_filename($this->getFilename());
    }

    public function getFilename() : string {
        return $this->filename;
    }

    public function getUrl() : string {
        return $this->url;
    }

    public function getContent() : string {
        return '<div style="background-color: #aeffa8;">'.
                    "<p><b>Timestamp:</b> {$this->getTimestamp()}<br />".
                    "<b>Attachment:</b> $this->filename<br />".
                    "<b>Uploaded by:</b> $this->user".
            ($this->isHidden() ? "<br /><b>Hidden for caller</b></p>" : "</p>").
                "</div><hr /><br /><br />";
    }

}