<?php

namespace Tet\Mail;

class Message
{
    public string $subject;
    public bool $html;
    public $reply_to = null;
    public $sender;
    public $from;
    public $to;

    public $text;
    public $attachments;
    private $boundary;

    const EOL = "\n";

    function output()
    {
        return $this->getHeader() . $this->getBody();
    }

    private function getBoundary()
    {
        if (!$this->boundary) $this->boundary = '----=_NextPart_' . md5(time());
        return $this->boundary;
    }

    private function getHeader()
    {
        $header = 'MIME-Version: 1.0' . $this::EOL;
        $header .= 'To: <' . $this->to . '>' . $this::EOL;
        $header .= 'Subject: =?UTF-8?B?' . base64_encode($this->subject) . '?=' . $this::EOL;
        $header .= 'Date: ' . date('D, d M Y H:i:s O') . $this::EOL;
        $header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . $this::EOL;

        if (!$this->reply_to) $header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . $this::EOL;
        else $header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->reply_to) . '?= <' . $this->reply_to . '>' . $this::EOL;
        
        $header .= 'Return-Path: ' . $this->from . $this::EOL;
        $header .= 'X-Mailer: PHP/' . phpversion() . $this::EOL;
        $header .= 'Content-Type: multipart/mixed; boundary="' . $this->getBoundary() . '"' . $this::EOL . $this::EOL;

        return $header;
    }

    private function getBody()
    {
        if (!$this->html) $body = $this->getBodyAsHTML();
        else $body = $this->getBodyAsPlainText();       

        foreach ($this->attachments as $attachment) {
            if (file_exists($attachment)) {
                $body = $this->addAttachment($body, $attachment);                
            }
        }

        $body .= '--' . $this->getBoundary() . '--' . $this::EOL;
        return $body;
    }

    function getBodyAsHTML()
    {
        $body = '--' . $this->getBoundary() . $this::EOL;
        $body .= 'Content-Type: text/plain; charset="utf-8"' . $this::EOL;
        $body .= 'Content-Transfer-Encoding: base64' . $this::EOL . $this::EOL;
        $body .= base64_encode($this->text) . $this::EOL;
        return $body;
    }

    function getBodyAsPlainText()
    {
        $body = '--' . $this->getBoundary() . $this::EOL;
        $body .= 'Content-Type: multipart/alternative; boundary="' . $this->getBoundary() . '_alt"' . $this::EOL . $this::EOL;
        $body .= '--' . $this->getBoundary() . '_alt' . $this::EOL;
        $body .= 'Content-Type: text/plain; charset="utf-8"' . $this::EOL;
        $body .= 'Content-Transfer-Encoding: base64' . $this::EOL . $this::EOL;
        $body .= base64_encode($this->text) . $this::EOL;

        $body .= '--' . $this->getBoundary() . '_alt' . $this::EOL;
        $body .= 'Content-Type: text/html; charset="utf-8"' . $this::EOL;
        $body .= 'Content-Transfer-Encoding: base64' . $this::EOL . $this::EOL;
        $body .= base64_encode($this->html) . $this::EOL;
        $body .= '--' . $this->getBoundary() . '_alt--' . $this::EOL;
        return $body;
    }

    function addAttachment($body, $attachment)
    {
        $content = $this->getAttachmentContent($attachment);
        $body .= '--' . $this->getBoundary() . $this::EOL;
        $body .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . $this::EOL;
        $body .= 'Content-Transfer-Encoding: base64' . $this::EOL;
        $body .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . $this::EOL;
        $body .= 'Content-ID: <' . urlencode(basename($attachment)) . '>' . $this::EOL;
        $body .= 'X-Attachment-Id: ' . urlencode(basename($attachment)) . $this::EOL . $this::EOL;
        $body .= chunk_split(base64_encode($content));
        return $body;
    }

    private function getAttachmentContent(string $filename)
    {
        $handle = fopen($filename, 'r');
        $content = fread($handle, filesize($filename));
        fclose($handle);
        return $content;
    }
}
