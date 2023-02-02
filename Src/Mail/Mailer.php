<?php

namespace Tet\Mail;

class Mailer
{
	public string $hostname;
	public int $port;
	public int $timeout; // в миллисекундах

	public string $smtpLogin;
	public string $smtpPassword;

	public string $messageTo;
	public string $messageFrom;
	public string $messageSender;
	public string $messageReplyTo = '';
	public string $messageText;
	public string $messageSubject;
	public array $messageAttachments;
	public string $messageAsHTML;

	function send(): bool
	{
		$message = $this->createMessage();
		$smtp = $this->createSMTP();

		if (!$smtp->connect()) return false;
		$smtp->sendHELO("MyMail");
		$smtp->sendSTARTTLS();
		$smtp->sendHELO("MyMail");
		$smtp->sendAUTHLOGIN();
		$smtp->sendCommand(base64_encode($this->smtpLogin));
		$smtp->sendCommand(base64_encode($this->smtpPassword));
		$smtp->sendMAILFROM($this->messageFrom);
		$smtp->sendRCPTTO($this->messageTo);
		$smtp->sendDATA($message->output());
		$smtp->sendQUIT();

		return true;
	}

	private function createMessage(): Message
	{		
		$message = new Message;
		$message->to = $this->messageTo;
		$message->subject = $this->messageSubject;
		$message->text = $this->messageText;
		$message->attachments = $this->messageAttachments;
		$message->from = $this->messageFrom;
		$message->sender = $this->messageSender;
		$message->html = $this->messageAsHTML;
		$message->reply_to = $this->messageReplyTo;
		return $message;
	}

	private function createSMTP(): SMTP
	{
		$smtp = new SMTP;
		$smtp->hostname = $this->hostname;
		$smtp->port = $this->port;
		$smtp->timeout = $this->timeout;
		return $smtp;
	}
}
