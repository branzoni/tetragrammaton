<?php

namespace Tet\Mail;

class Socket
{
	private $handle;
	private bool $debug = false;

	public function open(string $hostname, int $port, int $timeout, $debug = false): void
	{
		$this->debug = $debug;
		$this->handle = fsockopen($hostname, $port, $errorCode, $errorMessage, $timeout / 1000);
		if (!$this->handle) throw new \Exception("Socket opening failure: $errorMessage", $errorCode);

		socket_set_timeout($this->handle, 0, $timeout * 1000);
	}

	public function close(): void
	{
		fclose($this->handle);
	}

	public function write(string $data): void
	{
		if (!$this->isOpened()) throw new \Exception("Socket Object: socket not opened");;
		if ($this->debug) $this->echo(" - WRITE: $data");
		fputs($this->handle, $data);
	}

	public function read(): string
	{
		if (!$this->isOpened()) throw new \Exception("Socket Object: socket not opened");;

		$data = "";
		while ($buffer = fgets($this->handle, 515)) {
			$data .= $buffer;
		}

		if ($this->debug) $this->echo(" - READ: $data");
		return $data;
	}

	public function writeAndRead(string $data): ?string
	{
		$this->write($data);
		return $this->read();
	}

	public function isOpened(): bool
	{
		return boolval($this->handle);
	}

	public function enableCrypto($method = STREAM_CRYPTO_METHOD_TLS_CLIENT): bool
	{
		return stream_socket_enable_crypto($this->handle, true, $method);
	}

	private function echo(string $message)
	{
		echo Date('d-m-Y h:i:s') . "$message\r\n";
	}
}
