<?php
namespace Tet;

class Mail{
    public  string $from;
    public string $to;
    public string $subject;
    public string $message;
    public string $attachments;

    function __construct($from="", $to="", $subject="", $message="", $attachments="")
    {
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
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