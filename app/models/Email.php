<?php


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