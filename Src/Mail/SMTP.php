<?php

namespace Tet\Mail;

class SMTP
{
	public string $hostname;
	public int $port;
	public int $timeout; // в миллисекундах
	private Socket $socket;

	function connect(): bool
	{
		$this->socket = new Socket;
		$this->socket->hostname = $this->hostname;
		$this->socket->port = $this->port;
		$this->socket->timeout = $this->timeout;
		return $this->socket->open();
	}

	function sendCommand(string $commad):?string
	{
		return $this->socket->writeAndRead("$commad\r\n");
	}

	function sendHELO(string$serverName):?string
	{
		return $this->sendCommand("EHLO $serverName");
	}

	function sendSTARTTLS()
	{
		$this->sendCommand("STARTTLS");
		$this->socket->enableCrypto();
	}

	function sendAUTHLOGIN():string
	{
		return $this->sendCommand("AUTH LOGIN");
	}

	function sendMAILFROM($value)
	{
		$this->sendCommand("MAIL FROM: <$value>");
	}

	function sendRCPTTO($value)
	{
		$this->sendCommand("RCPT TO: <$value>");
	}

	function sendQUIT()
	{
		$this->sendCommand("QUIT");
	}

	function sendDATA($data)
	{
		$this->sendCommand("DATA");

		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);
		$length = (mb_detect_encoding($data, mb_detect_order(), true) == 'ASCII') ? 998 : 249;
		$lines = explode("\n", $data);

		foreach ($lines as $line) {
			$results = str_split($line, $length);
			foreach ($results as $result) {
				if (substr(PHP_OS, 0, 3) != 'WIN') {
					$this->socket->writeAndRead($result . "\r\n");
				} else {
					$this->socket->writeAndRead(str_replace("\n", "\r\n", $result) . "\r\n");
				}
			}
		}

		$this->socket->writeAndRead(".\r\n");
	}
}
