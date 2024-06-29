<?php

namespace Tet\Mail;

class Socket
{

	public string $hostname;
	public int $port;
	public int $timeout; // в миллисекундах
	private $handle;
	public bool $debug = false;

	function open(): void
	{
		if (!$this->hostname) throw new \Exception("Socket Object: hostname not set");
		if (!$this->port) throw new \Exception("Socket Object: port not set");
		if (!$this->timeout) throw new \Exception("Socket Object: timeout not set");

		$this->handle = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout / 1000);
		if (!$this->handle) throw new \Exception("Socket Object: handle creating failure");

		socket_set_timeout($this->handle, 0, $this->timeout * 1000);
	}

	private function echo(string $message)
	{
		echo Date('d-m-Y h:i:s') . "$message\r\n";
	}

	function isOpened(): bool
	{
		return boolval($this->handle);
	}

	function write(string $data): void
	{
		if (!$this->isOpened()) throw new \Exception("Socket Object: socket not opened");;
		if ($this->debug) $this->echo(" - WRITE: $data");
		fputs($this->handle, $data);
	}

	function read(): string
	{
		if (!$this->isOpened()) throw new \Exception("Socket Object: socket not opened");;

		$data = "";
		while ($buffer = fgets($this->handle, 515)) {
			$data .= $buffer;
		}

		if ($this->debug) $this->echo(" - READ: $data\r\n");
		return $data;
	}

	function writeAndRead(string $data): ?string
	{
		$this->write($data);
		return $this->read();
	}

	function enableCrypto($method = STREAM_CRYPTO_METHOD_TLS_CLIENT): bool
	{
		return stream_socket_enable_crypto($this->handle, true, $method);
	}
}
