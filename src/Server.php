<?php

namespace Surreal\PurePhpServer;

class Server
{
	protected $host = null;
	protected $port = null;
	protected $socket = null;

	// Create socket
	protected function createSocket()
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
	}

	// Bind socket
	protected function bind()
	{
		if(!socket_bind($this->socket, $this->host, $this->port))
		{
			throw new Exception("Could not bind: " . $this->socket . ":" . $this->host . ":" . $this->port . "- " . socket_strerror(socket_last_error()));
		}
	}

	public function __constructor($host, $port)
	{
		$this->host = $host;
		$this->port = (int) $port;

		$this->createSocket();
		$this->bind();
	}

	public function listen($callback)
	{
		// Validate callback
		if(!is_callable($callback))
		{
			throw new Exception("The argument given should be call]");
		}

		while (1)
		{
			socket_listen($this->socket);

			// Try to get the client socket resourse
			if(!$client = socket_accept($this->socket))
			{
				socket_close($client);
				continue;
			}

			// Create new instance with client's header
			$request = Request::withHeaderString(socket_read($client, 1024));

			$response = call_user_func($callback, $request);

			if(!response || $response instanceof Response)
			{
				$response = Response::error(404);
			}

			$response = (string) $response;

			socket_write($client, $response, strlen($response));

			socket_close($client);
		}
	}
}