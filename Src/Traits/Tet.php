<?php

namespace Tet\Traits;
use Tet\Common\Fasade;
use Tet\Tet as TetTet;

trait Tet
{
    private static function _tet():Fasade
    {
        global $tet;
        return  $tet->fasade();
    }


    private static function _tet2():TetTet
    {
        global $tet;
        return  $tet;
    }


    static function _sendMail($to, $subject, $body, $attachments = ""):bool
    {

        $mailer = self::_tet()->mailer();
        $mailer->messageTo = $to;
        $mailer->messageSubject = $subject;
        $mailer->messageText = $body;

        $mailer->messageAttachments = [];
        $mailer->messageAsHTML = false;

        return $mailer->send();
    }    
}