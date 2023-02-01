<?php

namespace Tet;

class Socket
{
	
	use Exceptionable;

	public string $hostname;
	public int $port;
	public int $timeout; // в миллисекундах
	private $handle;
	public bool $debug = false;

	function open(): bool
	{
		if (!$this->hostname) $this->exception("hostname not set");
		if (!$this->port) $this->exception("port not set");
		if (!$this->timeout) $this->exception("timeout not set");		
	
		$this->handle = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout / 1000);		
		if (!$this->handle) return false;
		socket_set_timeout($this->handle, 0, $this->timeout * 1000);		
		return boolval($this->handle);
	}

	private function echo(string $message)
	{
		echo Date('d-m-Y h:i:s') . "$message<br>";
	}

	function isOpened():bool
	{
		return $this->handle;
	}

	function write(string $data): bool
	{
		if(!$this->isOpened()) return false;
		if ($this->debug) $this->echo(" - WRITE: $data");
		return fputs($this->handle, $data);
	}

	function read(): string
	{
		if(!$this->isOpened()) return false;
		
		$data = "";
		while ($buffer = fgets($this->handle, 515)) {
			$data .= $buffer;
		}

		if ($this->debug) $this->echo(" - READ: $data<br>");
		return $data;
	}

	function writeAndRead(string $data): ?string
	{
		if (!$this->write($data)) return null;
		return $this->read();
	}

	function enableCrypto($method = STREAM_CRYPTO_METHOD_TLS_CLIENT): bool
	{
		return stream_socket_enable_crypto($this->handle, true, $method);
	}
}
