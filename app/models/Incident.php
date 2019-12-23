<?php


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