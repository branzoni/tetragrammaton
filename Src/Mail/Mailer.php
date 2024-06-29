<?php

namespace Tet\Mail;

class Mailer
{
    public static string $hostname;
    public static int $port;
    public static int $timeout; // в миллисекундах
    public static string $login;
    public static string $password;

	public function setSMTP($host, $port, $timeout, $login, $password) {
		self::$hostname = $host;
		self::$port = $port;
		self::$timeout = $timeout;
		self::$login = $login;
		self::$password = $password;
	}

    public static function send(\Tet\Mail\Message $message): void
    {
//        $message = new Message(
//			self::$messageSubject,
//			self::$messageText,
//			self::$messageAttachments,
//			self::$messageTo,
//			self::$messageFrom,
//			self::$messageSender,
//			self::$messageReplyTo,
//			self::$messageAsHTML
//		);

		if (!self::$hostname || !self::$port || !self::$login || !self::$password || self::$timeout) {
			throw new \Exception('Mailer requires hostname, port, login, password, timeout');
		}

        $smtp = new SMTP;
        $smtp->connect(self::$hostname, self::$port, self::$timeout);
		$smtp->setLoginAndPassword(self::$login, self::$password);
		$smtp->sendMessageAndQuit($message);
    }
}
