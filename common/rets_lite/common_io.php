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

// Common IO functions

class CommonRequest
     extends AbstractRetsExchange {
	var $address;
	var $port;
	var $message;
	var $login;
	var $password;
	var $args;
	var $headers;
	var $body;
	var $socket = false;
	var $returnCode;
	var $userAgent;
	var $isGet;
	var $sessionID;
	var $protocol;
	var $encodedAuth = null;

	function CommonRequest($userAgent)
	{
		parent::AbstractRetsExchange();
		$this->userAgent = $userAgent;
	}

	function getBody()
	{
		return $this->body;
	}

	function checkReturnCode()
	{
		$status_code = 100;
		while ($status_code == 100) {
			$result = $this->readUntilCrlf(false);

			if ($this->transportTrace) {
				$this->trace('RESPONSE ...');
				$this->rawTrace($result);
			}

			// If nothing returned from the server, maybe it disconnected?

			if ($result == null) {
				return;
			}

			// determine if the header contains status

			$pieces = explode(' ', $result);
			// if ($pieces[0] == $this->protocol)
			// {
			// $status_code = $pieces[1];
			// }
			if (preg_match("/^HTTP/i", $pieces[0])) {
				$status_code = $pieces[1];
			}
		}
		$this->returnCode = $status_code;
	}


	function hasMime()
	{
		if ($this->headerExists('MIME-VERSION')) {
			return true;
		}else {
			if ($this->transportTrace) {
				$this->trace('DETECTED that the MIME-VERSION is not set');
			}

			// don't believe it.  MarketLinx does not set Mime-Version for images!

			$contentType = $this->getHeader('CONTENT-TYPE');
			if (strpos($contentType, 'image') !== false){
				if ($this->transportTrace) {
					$this->trace('WITHOUT a MIME-VERSION set, an image was still detected, overriding server');
				}
				return true;
			}
		}
		return false;
	}

	function read_payload()
	{
		$content_encoding = $this->getHeader('CONTENT-ENCODING');
		if ($this->transportTrace) {
			if ($content_encoding != null) {
				$this->trace('FIXME: The PAYLOAD is encoded as ' . $content_encoding);
			}
		}

		// Transfer Encoding

		$result = $this->getHeader('TRANSFER-ENCODING');
		if ($result != null) {
			if (strlen($result) > 0 && $result == 'chunked') {
				if ($this->transportTrace) {
					$this->trace('READING the PAYLOAD as Chunked');
				}
				$buffer = $this->getChunkedBuffer();
			}
		}else {

			// read Content Length Body
			$chunk = $this->getHeader('CONTENT-LENGTH');
			if ($chunk != null) {
				if (strlen($chunk) > 0) {
					if ($this->transportTrace) {
						$this->trace('READING the PAYLOAD with Content-Length');
					}
					$buffer = $this->getLengthBuffer($chunk);
				}
			}else {
				// check for MIME
 
				if ($this->hasMime()) {

					// check for multipart

					$multipart = false;
					$contentType = $this->getHeader('CONTENT-TYPE');
					if ($contentType != null) {
						$pos = strpos($contentType, 'multipart/');
						if ($pos === false) {
							if ($this->transportTrace) {
								$this->trace('READING the PAYLOAD as Mime encoded');
							}
							$buffer = $this->getMimeBuffer();
						}else {
							if ($this->transportTrace) {
								$this->trace('READING the PAYLOAD as Multipart');
							}

							$pos = strpos($contentType, '=');
							$boundary = substr($contentType,
								$pos + 1,
								strlen($contentType));
							$boundary = trim($boundary);
							$boundary = trim($boundary, '"');
							$buffer = $this->getMultipartBuffer($boundary);
						}
					}
				}else {
					if ($this->transportTrace) {
						$this->trace('READING the PAYLOAD as Mime encoded because mime type not set');
					}
					$buffer = $this->getMimeBuffer();
				}
			}
		}

/*
		// if compression is available, use it
		// first, check if the content is encoded with gzip

		$content_encoding = $this->getHeader('CONTENT-ENCODING');
		if ($content_encoding == 'application/gzip') {
			if ($this->transportTrace) {
				$this->trace('DECODING a content encoded PAYLOAD');
			}
			$tpname = tempnam('/tmp', 'gzip');
			$tp = fopen($tpname, 'wb');
			fwrite($tp, $buffer);
			fclose($tp);
			$buffer = null;
			$gp = gzopen($tpname, 'rb');
			while (!gzeof($gp)) {
				$buffer .= gzgetc($gp);
			}
			gzclose($gp);
			// $gzarray = gzfile( $tpname );
			// $junk = length( $gzArray );
			// echo "$junk";
			// $buffer = $gzArray[ 0 ];
			unlink($tpname);
		}
*/

		if ($this->payloadTrace) {
			$this->trace('PAYLOAD ...');
			$this->rawTrace($buffer);
		}
		return $buffer;
	}

	function setCredential($auth) {
		if (!$auth) {
if ($this->transportTrace) {
$this->trace('SET credential failed because no credential is present');
}
			return;
		}
if ($this->transportTrace) {
$this->trace('SET credential - succeeded');
}
		$this->encodedAuth = $auth;
	}

	function shareCredential() {
		if (!$this->encodedAuth) {
if ($this->transportTrace) {
$this->trace('SHARE credential failed because no credential is present');
}
			return false;
		}
                if(is_object($this->encodedAuth)){
//print_r($this->encodedAuth);
if ($this->transportTrace) {
$this->trace('SHARE credential - succeeded using an object');
}
                } else {
if ($this->transportTrace) {
$this->trace('SHARE credential - succeeded without an object');
}
                }
		return $this->encodedAuth;
	}

	function openSocket()
	{
		if ($this->transportTrace) {
			$this->trace('OPENING socket for ' . get_class($this) . ' on ' . $this->address . ' ' . $this->port);
		}
		if ($this->socket) {
			if ($this->transportTrace) {
				$this->trace('Socket already open for ' . get_class($this) . ' on ' . $this->address . ' ' . $this->port);
			}
			return true;
                }
		$this->socket = @fsockopen($this->address, $this->port, $errno, $errstr);
		if ($this->transportTrace) {
			if (!$this->socket) {
				$this->trace('FAILURE on open, error number: ' . $errno . '  text: ' . $errstr);
			} else {
				$this->trace('SUCCESS on open');
			}
		}
		if ($this->socket) {
			return true;
		}
		return false;
	}

	function closeSocket() {

		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return;
		}

		if ($this->transportTrace) {
			$this->trace('CLOSING socket for ' . get_class($this) . ' on ' . $this->address . ' ' . $this->port);
		}
		fclose($this->socket);
		$this->socket = false;
	}

	function send_authenticate()
	{
		$this->read_headers();
		$result = $this->getHeader('WWW-AUTHENTICATE');
		if (strlen($result) > 0) {
			$this->read_body(true);

//------------
			$AUTH = new Authorization($this->login,
					$this->password,
					$result,
					$this->message,
					$this->sessionID);
if ($this->transportTrace || $this->streamTrace) {
$this->trace('GENERATE a credential'); 
}
			$this->encodedAuth = $AUTH;
			$this->send_message($AUTH->generate());
//------------
/*
			$pieces = explode(' ', $result);
			$auth_type = $pieces[0];
			$localAuth = null;

			switch ($auth_type) {

				case 'Basic':
					$this->encodedAuth = 'Authorization: Basic ' . 
						base64_encode($this->login . ':' . $this->password);
					$localAuth = $this->encodedAuth;
					break;

				case 'Digest':

					// break apart authentication tokens

					$offset = strpos($auth_type, $result);
					$auth_tokens = substr($result,
						$offset + strlen($auth_type) + 1,
						strlen($result));
					$auth_tokens = trim($auth_tokens);
					$auth_pieces = explode(',', $auth_tokens);
					$count = 0;
					$total_size = sizeof($auth_pieces);
					while ($count < $total_size) {
						$check = trim($auth_pieces[$count]);
						$offset = strpos($check, '=');
						if ($offset) {
							$key = substr($check, 0, $offset);
							$value = substr($check, $offset + 1, strlen($check));
							if (strpos($value, ',') > 0) {
								$value = substr($value, 0, strlen($value) - 1);
							}
							$results[$key] = $value;
						}
						$count = $count + 1;
					}
	
					// construct A1

					$auth_realm = $this->strip_quotes($results['realm']);
					$A1 = $this->login . ':' . $auth_realm . ':' . $this->password;
//print("<XMP>A1 -$A1-</XMP>");

					// construct A2

					$uri_pieces = explode(' ', $this->message);
					$uri_method = $uri_pieces[0];
					$uri_item = $uri_pieces[1];
					// echo "<XMP>METHOD $uri_method ITEM $uri_item</XMP>";
					$A2 = $uri_method . ':' . $uri_item;
//print("<XMP>A2 -$A2-</XMP>");

					// miscellaneous elements

					$auth_opaque = $this->strip_quotes($results['opaque']);
					$auth_nonce = $this->strip_quotes($results['nonce']);

					// check for 2069 style authorization

					$is2069 = false;
					if (!array_key_exists('qop', $results) && !array_key_exists('auth_qop', $results)) {
						$is2069 = true;
					}

					if ($is2069) {
						if ($this->transportTrace) {
							$this->trace('AUTHENTICATING with RFC 2069');
						}

						// response construction

						$raw_digest = md5($A1) . ':' . $auth_nonce . ':' . md5($A2);

						$localAuth = 'Authorization: Digest username="' . $this->login . '", realm="' . $auth_realm . '", nonce="' . $auth_nonce . '", uri="' . $uri_item . '", response="' . md5($raw_digest) . '", opaque="' . $auth_opaque . '"';
					}else {
						if ($this->transportTrace) {
							$this->trace('AUTHENTICATING with RFC 2617');
						}

						// qop dissection

						$check_qop = $this->strip_quotes($results['qop']);
						$qop_pieces = explode(',', $check_qop);
						foreach($qop_pieces as $piece) {
							if ($piece == 'auth') {
								$auth_qop = 'auth';
							}
							if ($piece == 'auth-int') {
								$auth_qop = 'auth-int';
								$client_password = 'hello';
								$auth_cnonce = $this->userAgent . ':' .
									$client_password . ':' .
									$this->sessionID . ':' .
									$auth_nonce; 
							}
						}
	
						// response construction
	
						$auth_nc = '00000001';
						$auth_cnonce = '0a4f113b';
						$raw_digest = md5($A1) . ':' . $auth_nonce . ':' . $auth_nc . ':' . $auth_cnonce . ':' . $auth_qop . ':' . md5($A2);
	
						$localAuth = 'Authorization: Digest username="' . $this->login . '", realm="' . $auth_realm . '", nonce="' . $auth_nonce . '", uri="' . $uri_item . '", qop="' . $auth_qop . '", nc=' . $auth_nc . ', cnonce="' . $auth_cnonce . '", response="' . md5($raw_digest) . '", opaque="' . $auth_opaque . '"';
					}
					$this->encodedAuth = null;
					break;

				default:
					$this->encodedAuth = null;
					break;
			}
			$this->send_message($localAuth);
//------------
*/
		}
		$this->checkReturnCode();
	}

	function strip_quotes($input)
	{
		$result = trim($input);
		if (strpos($result, '"') == 0) {
			$result = substr($result, 1, strlen($result) - 2);
		}

		return $result;
	}

	function getMimeBuffer()
	{
		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return null;
		}

		$buffer = null;
		while (!feof ($this->socket)) {
			$buffer .= fgetc($this->socket);
		}

		return $buffer;
	}

	function getMultipartBuffer($boundary)
	{
		// index to the first boundary

		$raw = fgetc($this->socket);
		$raw = fgetc($this->socket);

		// read boundaries

		$buffer = null;
		$flag = true;
		$endCheck = '--' . $boundary . '--';
		while ($flag === true) {
			$candidate = $this->getOneMultipart($boundary);
			if ($endCheck == trim($candidate)) {
				$buffer .= $candidate;
				$flag = false;
			}else {
				$buffer .= $candidate . $this->readUntilCrlf() . "\r\n";
			}
		}
//		if ($this->payloadTrace) {
//			$this->trace('MULTIPART PAYLOAD ...');
//			$this->rawTrace($buffer);
//		}
		return $buffer;
	}

	function readUntilCrlf($binary_data = true)
	{
		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return;
		}

		$flag = true;
		$buffer = null;

		if ($binary_data === true) {

			// a one-character-at-a-time socket read

			while ($flag == true) {
				if (!feof($this->socket)) {
					$raw = fgetc($this->socket);
					$buffer .= $raw;
					if ($raw == "\r") {
						$raw = fgetc($this->socket);
						$buffer .= $raw;
						if ($raw == "\n") {
							$flag = false;
						}
					}
				}else {
					$flag = false;
				}
			}
		}else {

			// a on-line-at-a-time read, stopping on CRLF

			while ($flag == true) {
				$buffer = fgets($this->socket, 4096);
				if ($buffer === false) {
//					$this->closeSocket();
					return null;
				}
				$buffer = trim($buffer);
				if (strlen($buffer) > 0) {
					$flag = false;
				}
			}
		}

		return $buffer;
	}

	function getOneMultipart($boundary)
	{
		$buffer = null;
		$endCheck = '--' . $boundary . '--';
		$flag = true;
		while ($flag == true) {
			$buf = $this->readUntilCrlf();
			$check = trim($buf);
			if (strlen($check) == 0) {
				$flag = false;
			}else {
				$buffer .= $buf;
				if ($check == $endCheck) {
					$flag = false;
				}
			}
		}

		return $buffer;
	}

	function getChunkedBuffer()
	{
		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return;
		}

		$count = 0;
		$chunk = 1;
		$buffer = null;
		while ($chunk != 0) {
			if (!feof($this->socket)) {
				$hex_chunk = fgets($this->socket, 4096);
				$chunk = hexdec(trim($hex_chunk));
				if ($chunk > 0) {
					$raw = $this->getLengthBuffer($chunk + 2);
					$pos = strrpos($raw, "\r\n");
					if ($pos > 0) {
						$raw = substr($raw, 0, $pos);
					}
					$pos = strpos($raw, "\r\n");
					if ($pos === false) {
					}else {
						$len = strlen($raw);
						if ($pos == 0 && $len > 2) {
							$raw = substr($raw, $pos + 2, $len);
						}
					}

					$buffer .= $raw;
					$count++;
				}
			}else {
				$chunk = 0;
			}
		}

		return $buffer;
	}

	function getLengthBuffer($chunk)
	{
		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return;
		}

		$count = 0;
		$buffer = null;
		while ($count < $chunk) {
			if (!feof ($this->socket)) {
				$buffer .= fgetc($this->socket);
			}
			$count++;
		}

		return $buffer;
	}

	function register_variable($key,
				$remote_value) {
		if ($remote_value != null) {
			global ${$key};
			if (${$key} != null) {
				if (strlen(${$key}) > 0) {
					if ($key == 'remote_cookies') {
						$existing = explode(',', ${$key});
						$old = null;
						foreach ($existing as $key2 => $value2) {
							$old[$value2] = true;
						}
						$additions = explode(',', $remote_value);
						foreach ($additions as $key2 => $value2) {
							$old[$value2] = true;
						}
						$newValue = null;
						foreach ($old as $key2 => $value2) {
                                                	$newValue .= $key2 . ',';
						}
						$remote_value = substr($newValue, 
							0, 
							strrpos($newValue, ','));
					}
				}
			}
			${$key} = $remote_value;
		}
	}

	function read_headers()
	{
		// if the socket is not valid, don't even bother

		if (!$this->socket) {
			return;
		}

		$count = 0;
		$flag = true;
		$result = null;
		while ($flag) {
			if (!feof($this->socket)) {
				$raw = trim(fgets($this->socket, 4096));
				if (strlen($raw) == 0) {
					$flag = false;
				}else {
					if ($this->transportTrace) {
						$this->rawTrace($raw);
					}
					$result[$count] = $raw;
					$count++;
				}
			}else {
				$flag = false;
			}
		}

		$cookie = $this->getHeader('COOKIE');
		$this->headers = null;
		$this->setHeader('COOKIE', $cookie);
		if ($result != null) {
			foreach ($result as $num => $header) {
				$temp = explode(':', $header);

				// value of the header might have a literal :

				$value = trim(substr($header, 
					strpos($header, ':') + 1,
					strlen($header)));
				$key = trim($temp[0]);

				$pos = strpos($key, 'Set-Cookie');
				if ($pos === false) {
					$pos = strpos($key, 'Content-type');
					if ($pos === false) {
					} else {
						$key = 'Content-Type';
					}
					$this->setHeader($key, $value);
				} else {
					$cookie = $this->getHeader('COOKIE');
 
					$crumb_pos = strpos($value, '=');
					$crumb_key = substr($value, 0, $crumb_pos);
					$crumb_key = trim($crumb_key);
					$crumb_value = substr($value, $crumb_pos + 1, strlen($value));
					$crumb_value = trim($crumb_value);

					$temp_value = $crumb_value;

					$store_pos = strpos($temp_value, ';');
					if ($store_pos === false) {
					} else {
						$crumb_value = substr($temp_value, 0, $store_pos);
					}

					$path_pos = strpos($temp_value, 'path=');
					if ($path_pos === false) {
					} else {
						$crumb_value .= '; path=' . substr($temp_value, 
                                	                                           $path_pos+5,
                                        	                                   strlen($temp_value));
					}

					$cookie[$crumb_key] = $crumb_value;
					$this->setHeader('COOKIE', $cookie);
				}
			}
		}

		// trace facility

/*
		if ($this->transportTrace) {
			foreach ($this->headers as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						$this->trace("COOKIE: KEY $key2 VALUE $value2");
					}
				} else {
					$this->trace("HEADER: KEY $key VALUE $value");
				}
			}
		}
*/

		// register cookies

		foreach ($this->headers as $key => $value) {
			if (is_array($value)) {
				$buf = '';
				foreach ($value as $key2 => $value2) {
					$buf .= $key2 . ',';
					$this->register_variable($key2, $value2);
				}
				$len = strlen($buf);
				if ($len > 0) {
					$buf = substr($buf, 0, $len - 1);
					$this->register_variable('remote_cookies', $buf);
				}
			}
		}
	}

	function setCookies($cookie) {
		if (!$cookie) {
if ($this->transportTrace) {
$this->trace('SET cookies - failed');
}
			return;
		}
if ($this->transportTrace) {
$this->trace('SET cookies - succeded');
}
//		$this->setHeader('COOKIE', $cookie);
		$newCookies = $this->getHeader('COOKIE');
		foreach ($cookie as $key => $value) {
		     $newCookies[$key] = $value;
                }
		$this->setHeader('COOKIE', $newCookies);
	}

	function shareCookies() {
		if (!$this->headerExists('COOKIE')) {
if ($this->transportTrace) {
$this->trace('SHARE cookies - failed');
}
			return false;
		} 
if ($this->transportTrace) {
$this->trace('SHARE cookies - succeded');
}
		return $this->getHeader('COOKIE');
	}

	function headerExists($needle)
	{
		if ($this->headers == null) {
			return false;
		}
		if (array_key_exists(strtoupper($needle), $this->headers)) {
			return true;
		}

		return false;
	}

	function setHeader($key,
		$value)
	{
		$this->headers[strtoupper($key)] = $value;
	}

	function getHeader($needle)
	{
		if ($this->headerExists($needle)) {
			$value = $this->headers[strtoupper($needle)];
			if (is_array($value)) {
				return $value;
			} 
			return trim($value);
		}

		return null;
	}
}

class Authorization {

	var $authType;
	var $authRealm;
	var $authNC = 0;
	var $login;
	var $password;
	var $components = null;
	var $A1 = null;
	var $A2 = null;
	var $uri_item = null;
	var $sessionID = null;
	var $basicAuth = null;

	function Authorization($login,
				$password,
				$aString,
				$aMessage,
                                $sessionID) {
		if (strlen($aString) > 0) {

			// save session ID

			$this->sessionID = $sessionID;

			// decompose the auth string

			$pieces = explode(' ', $aString);
			$this->authType = $pieces[0];
			$this->login = $login;
			$this->password = $password;
			switch ($this->authType) {

				case 'Basic':
					$this->basicAuth = 'Authorization: Basic ' . 
						base64_encode($this->login . ':' . $this->password);
					break;

				case 'Digest':
					$auth_tokens = substr($aString, 
						strpos($this->authType, $aString) + strlen($this->authType) + 1,
						strlen($aString));
					$authPieces = explode(',', trim($auth_tokens));
					$count = 0;
					$total_size = sizeof($authPieces);
					while ($count < $total_size) {
						$check = trim($authPieces[$count]);
						$offset = strpos($check, '=');
						if ($offset) {
							$key = substr($check, 0, $offset);
							$value = substr($check, $offset + 1, strlen($check));
							if (strpos($value, ',') > 0) {
								$value = substr($value, 0, strlen($value) - 1);
							}
							$this->components[$key] = $value;
						}
						$count = $count + 1;
					}

					// check for 2069 style authorization

					$this->is2069 = false;
					if (!array_key_exists('qop', $this->components) && 
						!array_key_exists('auth_qop', $this->components)) {
						$this->is2069 = true;
					}

					// construct A1

					$this->authRealm = $this->strip_quotes($this->components['realm']);
					$this->A1 = md5($this->login . ':' . $this->authRealm . ':' . $this->password);
//print("<XMP>A1 -$A1-</XMP>");

					// construct A2

					$uri_pieces = explode(' ', $aMessage);
					$uri_method = $uri_pieces[0];
					$this->uri_item = $uri_pieces[1];
// echo "<XMP>METHOD $uri_method ITEM $uri_item</XMP>";
					$this->A2 = md5($uri_method . ':' . $this->uri_item);
//print("<XMP>A2 -$A2-</XMP>");
					break;

                        }
		}
	}

	function isBasicAuth() {
		if ($this->basicAuth != null) {
			return true;
		}
		return false;
	}

	function generate() {
		switch ($this->authType) {

			case 'Basic':
				return $this->basicAuth; 
				break;

			case 'Digest':

				// miscellaneous elements

				$auth_opaque = $this->strip_quotes($this->components['opaque']);
				$auth_nonce = $this->strip_quotes($this->components['nonce']);

				if ($this->is2069) {
//print('AUTHENTICATING with RFC 2069');
				// response construction

					$raw_digest = $this->A1 . ':' . $auth_nonce . ':' . $this->A2;
					return 'Authorization: Digest username="' . $this->login . '", realm="' . $this->authRealm . '", nonce="' . $auth_nonce . '", uri="' . $this->uri_item . '", response="' . md5($raw_digest) . '", opaque="' . $auth_opaque . '"';
				}else {
//print('AUTHENTICATING with RFC 2617');
					// qop dissection

					$qop_pieces = explode(',', $this->strip_quotes($this->components['qop']));
					foreach($qop_pieces as $piece) {
						if ($piece == 'auth') {
							$auth_qop = 'auth';
						}
						if ($piece == 'auth-int') {
							$auth_qop = 'auth-int';
							$client_password = 'hello';
							$auth_cnonce = $this->userAgent . ':' .
								$client_password . ':' .
								$this->sessionID . ':' .
								$auth_nonce; 
						}
					}
	
					// response construction

					++$this->authNC;
					$authNC_padded = str_pad($this->authNC, 8, '0', STR_PAD_LEFT);
					$auth_cnonce = '0a4f113b';
					$raw_digest = $this->A1 . ':' . $auth_nonce . ':' . $authNC_padded . ':' . $auth_cnonce . ':' . $auth_qop . ':' . $this->A2;
	
					return 'Authorization: Digest username="' . $this->login . '", realm="' . $this->authRealm . '", nonce="' . $auth_nonce . '", uri="' . $this->uri_item . '", qop="' . $auth_qop . '", nc=' . $authNC_padded . ', cnonce="' . $auth_cnonce . '", response="' . md5($raw_digest) . '", opaque="' . $auth_opaque . '"';
				}
				break;

		}

 		return null;
	}

	function strip_quotes($input)
	{
		$result = trim($input);
		if (strpos($result, '"') == 0) {
			return substr($result, 1, strlen($result) - 2);
		}

		return $result;
	}

}

// Network IO functions

// ---------------------

?>
