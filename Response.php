<?php


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