<?php
// Copyright (C) 2003-2010 National Association of REALTORS(R)
//
// All rights reserved.
//
// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use, copy,
// modify, merge, publish, distribute, and/or sell copies of the
// Software, and to permit persons to whom the Software is furnished
// to do so, provided that the above copyright notice(s) and this
// permission notice appear in all copies of the Software and that
// both the above copyright notice(s) and this permission notice
// appear in supporting documentation.
// ------------

// Network IO functions

class NetRequest 
	extends CommonRequest {

	function NetRequest($userAgent,
		$http_11 = true)
	{
		parent::CommonRequest($userAgent);

		$this->sessionID = '';

		// protocol options

		if ($http_11) {
			$this->protocol = 'HTTP/1.1';
		}else {
			$this->protocol = 'HTTP/1.0';
		}
	}

	function fetch($url,
		$aLogin = null,
		$aPassword = null,
		$asPost = false)
	{
		// parse url 

		$buffer = parse_url($url);
		$this->address = 'localhost';
		if (array_key_exists('HTTP_HOST', $_SERVER)) {
			$this->address = $_SERVER['HTTP_HOST'];
		}
		if (array_key_exists('host', $buffer)) {
			$this->address = $buffer['host'];
		}

		$this->port = '80';
		if (array_key_exists('port', $buffer)) {
			$this->port = $buffer['port'];
		}

		$this->args = '';
		if (array_key_exists('query', $buffer)) {
			$someArgs = $buffer['query'];
			if ($someArgs) {
				$this->args = $someArgs;
			}
		}

		if (!$asPost) {
			$this->isGet = true;
			$this->message = 'GET ';
		}else {
			$this->isGet = false;
			$this->message = 'POST ';
		}

		if (array_key_exists('path', $buffer)) {
			$aMessage = $buffer['path'];
			$this->message .= $aMessage;
		}else {
			$this->message .= './';
		}

		// set auth parameters 

		$this->login = $aLogin;
		$this->password = $aPassword;

		// send message

		$this->send_message();
		$buffer = null;
		if ($this->socket) {

			// read the result

			$this->checkReturnCode();

			// Look for authentication

			if ($this->returnCode == '401') {
				if (!$this->transportTrace) {
					if ($this->payloadTrace) {
						$this->trace('AUTHORIZATION required');
					}
				}
				$this->send_authenticate();

				// see if the login was good

				if ($this->returnCode == '401') {
					if (!$this->transportTrace) {
						if ($this->payloadTrace) {
							$this->trace('REQUIRES second authorization');
						}
					}
					$this->send_authenticate();
				}
			}


			// see if the function is implemented 

			if ($this->returnCode == '501') {
				if (!$this->transportTrace) {
					if ($this->payloadTrace) {
						$this->trace('ERROR: server does not support this function (501)');
					}
				}
				return;
			}

			// check if result is success

			$this->read_headers();
			if ($this->returnCode == '200') {

				// read body

				$this->read_body();
			}
		}
		return $this->body;
	}

	function send_message($encodedAuth = null)
	{

		// append args if GET style

		$first = $this->message;
		if ($this->isGet) {
			if (strlen($this->args) > 0) {
				$first .= '?' . $this->args;
			}
		}
		$first .= ' ' . $this->protocol . "\r\n";

		// optionally, the authentication information

		$auth_header = null;
		if (isset($encodedAuth)) {
			$auth_header = $encodedAuth . "\r\n";
		}

		// add arguments to payload if POST style

		$content_type_header = null;
		if ($this->isGet) {
			$content_length_header = "\r\n";
		}else {
			if (strlen($this->args) > 0) {
				$content_type_header = 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
				$content_length_header = 'Content-Length: ' .
				strlen($this->args) . "\r\n\r\n" . $this->args;
			}else {
				$content_length_header = 'Content-Length: 0' . "\r\n\r\n";
			}
		}

		// send the message

		$single_stream = $first . 
				$auth_header . 
				'Host: ' . $this->address . ':' . $this->port . "\r\n" . 
				'Accept: */*' . "\r\n" . 
				'User-Agent: ' . $this->userAgent . "\r\n" . 
				'Date: ' . gmdate('D, d M Y G:i:s T') . "\r\n" .
				'Pragma: no-cache' . "\r\n" . 
				$content_type_header . 
				$content_length_header;

		if (!$this->socket) {
			$this->openSocket();
		}

		if ($this->socket) {
			if ($this->transportTrace) {
				$this->trace('SENDING  ... ');
				$this->rawTrace($single_stream);
			}else {
				if ($this->payloadTrace) {
					$this->trace('SENDING  ... ');
					$this->rawtrace($first);
					if (!$this->isGet) {
						if ($this->args != null) {
							$this->rawTrace($this->args);
						}
					}
				}
			}

			$success = @fwrite($this->socket, $single_stream);
			if (!$success) {

				// this can happen when
				// a) you are expecting a keep-alive server
				// b) that server has a response that is not authenticated

				// echo "<XMP>Write failed</XMP>";
				$this->openSocket();
				fwrite($this->socket, $single_stream);
			}
		}
	}

	function read_body()
	{

		$this->body = $this->read_payload();

		// close socket to the server

		$this->closeSocket();
	}

}

// Network IO functions

// ---------------------

?>
