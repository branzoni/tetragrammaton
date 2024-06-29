<?php

namespace Tet\Mail;

class SMTP
{
	private Socket $socket;
	private string $login;
	private string $password;

	public function connect(string $hostname, int $port, int $timeout): void
	{
		$this->socket->open($hostname, $port, $timeout);
	}

	public function setLoginAndPassword(string $login, string $password): void
	{
		$this->login = $login;
		$this->password = $password;
	}

	public function sendMessageAndQuit(\Tet\Mail\Message $message): void
	{
		if (!this->login || !$this->password) {
			throw new \Exception('Login and password are required.');
		}

		$this->sendHELO("MyMail");
		$this->sendSTARTTLS();
		$this->sendHELO("MyMail");
		$this->sendAUTHLOGIN();
		$this->sendCommand(base64_encode($this->login));
		$this->sendCommand(base64_encode($this->password));
		$this->sendMAILFROM($message->from);
		$this->sendRCPTTO($message->to);
		$this->sendDATA($message->output());
		$this->sendQUIT();
		$this->socket->close();
	}

	public function sendCommand(string $command): ?string
	{
		return $this->socket->writeAndRead("$command\r\n");
	}

	public function sendHELO(string $serverName): ?string
	{
		return $this->sendCommand("EHLO $serverName");
	}

	public function sendSTARTTLS()
	{
		$this->sendCommand("STARTTLS");
		$this->socket->enableCrypto();
	}

	public function sendAUTHLOGIN(): string
	{
		return $this->sendCommand("AUTH LOGIN");
	}

	public function sendMAILFROM($value)
	{
		$this->sendCommand("MAIL FROM: <$value>");
	}

	public function sendRCPTTO($value)
	{
		$this->sendCommand("RCPT TO: <$value>");
	}

	public function sendQUIT()
	{
		$this->sendCommand("QUIT");
	}

	public function sendDATA($data)
	{
		$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

		$this->sendCommand("DATA");

		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);
		$length = (mb_detect_encoding($data, mb_detect_order(), true) == 'ASCII') ? 998 : 249;
		$lines = explode("\n", $data);

		foreach ($lines as $line) {
			$lineParts = str_split($line, $length);
			foreach ($lineParts as $linePart) {
				if (!$isWindows) $linePart = str_replace("\n", "\r\n", $linePart);
				$this->socket->writeAndRead($linePart . "\r\n");
			}
		}

		$this->socket->writeAndRead(".\r\n");
	}
}