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

class Incident
{
    private $id;
    private $number;
    private $briefDescription;
    private $data;
    private $responses;
    private $timestamp;
    private $caller;
    private $operatorGroup;
    private $operator;

    public function __construct($id, $number, $briefDescription, $operatorGroup, $operator, $caller, $timestamp, $data) {
        $this->id               = $id;
        $this->number           = $number;
        $this->briefDescription = $briefDescription;
        $this->operatorGroup    = $operatorGroup;
        $this->operator         = $operator;
        $this->caller           = $caller;
        $this->timestamp        = new DateTime($timestamp);
        $this->data             = $data;
        $this->responses        = array();
    }

    public function addResponse(Response $response) {
        $this->responses[] = $response;
    }

    public function getId() {
        return $this->id;
    }

    public function getNumber() {
        return $this->number;
    }

    public function getBriefDescription() {
        return $this->briefDescription;
    }

    public function getTimestamp() {
        return $this->timestamp->format("Y-m-d H:i:s");
    }

    public function getResponses() {
        return $this->responses;
    }

    public function getCaller() {
        return $this->caller;
    }

    public function getOperatorGroup() {
        return $this->operatorGroup;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function getData() {
        return $this->data;
    }
}