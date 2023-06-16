<?php

namespace Tet\Mail;

use Tet\Mail\Message;

class Mailer
{
    public static string $smtpHostname;
    public static int $smtpPort;
    public static int $smtpTimeout; // в миллисекундах
    public static string $smtpLogin;
    public static string $smtpPassword;

    public static string $messageTo;
    public static string $messageFrom;
    public static string $messageSender;
    public static string $messageReplyTo = '';
    public static string $messageText;
    public static string $messageSubject;
    public static array $messageAttachments;
    public static string $messageAsHTML;

    static function send(): bool
    {
        $message = self::createMessage();

        $smtp = self::createSMTP();
        if (!$smtp->connect()) return false;

        $smtp->sendHELO("MyMail");
        $smtp->sendSTARTTLS();
        $smtp->sendHELO("MyMail");
        $smtp->sendAUTHLOGIN();
        $smtp->sendCommand(base64_encode(self::$smtpLogin));
        $smtp->sendCommand(base64_encode(self::$smtpPassword));
        $smtp->sendMAILFROM(self::$messageFrom);
        $smtp->sendRCPTTO(self::$messageTo);
        $smtp->sendDATA($message->output());
        $smtp->sendQUIT();

        return true;
    }

    private static function createMessage(): Message
    {
        $message = new Message;
        $message->to = self::$messageTo;
        $message->subject = self::$messageSubject;
        $message->text = self::$messageText;
        $message->attachments = self::$messageAttachments;
        $message->from = self::$messageFrom;
        $message->sender = self::$messageSender;
        $message->html = self::$messageAsHTML;
        $message->reply_to = self::$messageReplyTo;
        return $message;
    }


    private static function createSMTP(): SMTP
    {
        $smtp = new SMTP;
        $smtp->hostname = self::$smtpHostname;
        $smtp->port = self::$smtpPort;
        $smtp->timeout = self::$smtpTimeout;
        return $smtp;
    }
}
