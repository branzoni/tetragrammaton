<?php
namespace Tetra;

class Mail{
    private $from;
    private $to;
    private $subject;
    private $message;
    private $attachments;

    function __construct($from="", $to="", $subject="", $message="", $attachments="")
    {
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $message;
        $this->attachments= $attachments;
    }

    function send(){
        
        $headders="";

        return mail(
            $this->to,
            $this->subject,
            $this->message,
            $headders
        );
    }
}