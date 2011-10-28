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

//------------
//
// IO functions
//

class Request
     extends CommonRequest {

     var $userAgentPassword;
     var $retsVersion;
     var $contentID;
     var $error;
     var $lastError;
     var $start = 0;
     var $finish = 0;
     var $sessionID;
     var $sessionHeader;
     var $isSessionCookie = false;

     function Request($anAddress,
                      $aPort,
                      $aLogin,
                      $aPassword,
                      $userAgent,
                      $userAgentPassword,
                      $retsVersion,
                      $asPost = false) {
          parent::CommonRequest($userAgent);

          $this->protocol = 'HTTP/1.1';
          $this->address = $anAddress;
          $this->port = $aPort;
          $this->login = $aLogin;
          $this->password = $aPassword;
          $this->userAgentPassword = $userAgentPassword;
          $this->setRetsVersion($retsVersion);
          if (!$asPost) {
               $this->isGet = true;
          } else {
               $this->isGet = false;
          }

     }

	function setSocket($socket) {
		if (!$socket) {
if ($this->transportTrace) {
$this->trace('SET socket - failed');
}
			return;
		}
if ($this->transportTrace) {
$this->trace('SET socket - succeded');
}
		$this->socket = $socket;
	}

	function shareSocket() {
		if (!$this->socket) {
if ($this->transportTrace) {
$this->trace('SHARE socket - failed');
}
			return false;
		} 
if ($this->transportTrace) {
$this->trace('SHARE socket - succeded');
}
		return $this->socket;
	}

	function getMessage() {
		return $this->message;
	}

	function setContext($aMessage,
			$someArgs) {
		if (!$someArgs) {
			$this->args = '';
		} else {
			$this->args = $someArgs;
		}
		$this->message = $aMessage;
		if ($this->isGet) {
			$this->message = 'GET /' . $aMessage;
		} else {
			$this->message = 'POST /' . $aMessage;
		}
	}

	function hasErrors() {
		return $this->error;
	}

     function getLastError() {
          return $this->lastError;
     }

     function getNetworkTime() {
          return $this->finish - $this->start;
     }

     function setRetsVersion($version) {
          if ($version == null) {
               $this->retsVersion = null;
               return;
          }

          if ($version === false) {
               $this->retsVersion = null;
               return;
          }

          if (strlen($version) == 0) {
               $this->retsVersion = null;
               return;
          }

          if ($version == 'Undefined') {
               $this->retsVersion = null;
               return;
          }

          if (strpos($version, 'ETS/') === false) {
               $this->retsVersion = 'RETS/' . $version;
          } else {
               $this->retsVersion = $version;
          }
     }

     function getRetsVersion() {
          if ($this->retsVersion == 'RETS/1.7.2') {
               return '1.7.2';
          }
 
          if ($this->retsVersion == 'RETS/1.7') {
               return '1.7';
          }
 
          if ($this->retsVersion == 'RETS/1.5') {
               return '1.5';
          }
 
          return '1.0';
     }

     function process($read_raw = false,
                      $metadata_container = null,
                      $encodedAuth = null) {
          $this->start = time();
          $this->error = false;
//
// send message
//
          $this->send_message($encodedAuth);
          if (!$this->socket) {
               $this->error = true;
               $this->lastError = 'Could not open a connection';
               return;
          }
 
//
// read the result 
//
          $this->checkReturnCode();

//
// ignore HTTP 100
//
          if ($this->returnCode == '100') {
               $this->read_headers();
               $this->read_body($read_raw, $metadata_container);
               $this->checkReturnCode();
          }

//
// Look for authentication
//
          if ($this->returnCode == '401') {
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('SERVER requires authorization');
	}
}
               $this->send_authenticate();

//
// second authorization (FNIS)
//
               if ($this->returnCode == '401') {
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('SERVER requires second authorization');
	}
}
                    $this->send_authenticate();
               }
          }


          $this->read_headers();
          switch ($this->returnCode) {

               case '200':
//
// success 
//
                    $this->setRetsSession();
                    $this->setRetsVersion($this->getHeader('RETS-VERSION'));
                    $this->contentID = $this->getHeader('CONTENT-ID');
                    $this->read_body($read_raw, $metadata_container);
                    break;

               case '400':
//
// see if the function exists (Rappatoni)
//
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('SERVER does not support this function');
	}
}
                    $this->error = true;
                    $this->lastError = 'format error, use packet sniffer';
                    $this->read_body($read_raw, $metadata_container);
                    break;

               case '401':
//
// credentials are wrong 
//
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('Credentials do not match');
	}
}
                    $this->error = true;
                    $this->lastError = 'Bad name/password combination';
                    break;

               case '404':
//
// not found 
//
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('Not Found');
	}
}
                    $this->error = true;
                    $this->lastError = 'Not Found';
                    $this->read_body($read_raw, $metadata_container);
                    break;

               case '501':
//
// see if the function exists (MRIS)
//
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('SERVER does not support this function');
	}
}
                    $this->error = true;
                    break;

               default:
if (!$this->transportTrace) {
	if ($this->payloadTrace) {
		$this->trace('Failed exchange, return code: ' . $this->returnCode);
	}
}
                    $this->error = true;

          }
          $this->finish = time();
     }

     function processTextStream(&$HANDLER,
                      $encodedAuth = null) {
//
// send message
//
          $this->send_message($encodedAuth);
          if (!$this->socket) {
               $this->error = true;
               return;
          }

//
// read the result 
//
          $this->checkReturnCode();

//
// ignore HTTP 100
//
          if ($this->returnCode == '100') {
               $this->read_headers();
               $this->read_body($read_raw, $metadata_container);
               $this->checkReturnCode();
          }

//
// Look for authentication
//
          if ($this->returnCode == '401') {
               $this->send_authenticate();
          }

//
// see if the login was good
//
          if ($this->returnCode == '401') {
               $this->send_authenticate();
          }

//
// check if result is success 
//
          $this->read_headers();
          if ($this->returnCode == '200') {

//
// process RETS headers
//
               $this->setRetsSession();
               $this->setRetsVersion($this->getHeader('RETS-VERSION'));
               $this->contentID = $this->getHeader('CONTENT-ID');

//
// read body
//
               $HANDLER->prepare();
               $this->read_body_text_stream($HANDLER);
               $HANDLER->finish();
          }
     }

     function send_message($encodedAuth = null) {

//
// append args if GET style 
//
          $first = $this->message;
          if ($this->isGet) {
               if (strlen($this->args) > 0) {
                    $first .= '?' . $this->args;
               }
          }
          $first .= ' ' . $this->protocol . CRLF;

//
// optionally, the authentication information 
//
          $auth_header = null;
          if ($encodedAuth != null) {
               if (is_object($encodedAuth)) {
                    $auth_header = $encodedAuth->generate() . CRLF;
               } else {
                    $auth_header = $encodedAuth . CRLF;
               }
          }

//
// add RETS headers
//
          $rets_request_id = 'U571';
          $rets_headers = 'RETS-Request-ID: ' . $rets_request_id . CRLF;
          if ($this->retsVersion != null) {
               $rets_headers .= 'RETS-Version: ' . $this->retsVersion . CRLF;

//
// add RETS-UA header
//
               if ($this->userAgentPassword != null) {
                    $userAgent = $this->userAgent;
                    $pos = strpos($userAgent, '/');
                    if ($pos > -1) {
                         $userAgent = trim(substr($userAgent, 0, $pos));
                    }
                    $A1 = $userAgent . ':' . $this->userAgentPassword;
                    $A1en = md5($A1);

//
// InterRealty hack - do not recalculate!
//
//                    $session_id = $this->sessionID;
                    $session_id = '';
                    $ua_digest_response = $A1en . ':' .
                                          $rets_request_id . ':' .
                                          $session_id . ':' .
                                          $this->retsVersion; 
//echo '<XMP>DR $ua_digest_response</XMP>';
                    $rets_headers .= 'RETS-UA-Authorization: Digest ' .  
                                     md5($ua_digest_response) .  
                                     CRLF;
               }
          }

//
// if compression is available, use it
//
          $encoding_header = null;
/*
     if (RETS_USE_GZIP) 
     {
          if (extension_loaded('zlib'))
          {  
               $encoding_header = 'Accept-Encoding: application/gzip' . CRLF;

          }
     }
*/

//
// add arguments to payload if POST style 
//
          $content_type_header = null; 
          if ($this->isGet) {
               $content_length_header = CRLF;
          } else {
               if (strlen($this->args) > 0) {
                    $content_type_header = 'Content-Type: ' .
                                           'application/x-www-form-urlencoded' .
                                           CRLF;
                    $content_length_header = 'Content-Length: ' .
                                             strlen($this->args) .
                                             CRLF . CRLF .
                                             $this->args;
               } else {
                    $content_length_header = 'Content-Length: 0' . CRLF . CRLF;
               }
          }

//
// recreate cookies
//
          $cookie_header = null;
          global $remote_cookies;
          if (strlen($remote_cookies) > 0) {
               $cookie_headers = explode(',', $remote_cookies);
               if (is_array($cookie_headers)) {
                    foreach ($cookie_headers as $key => $value) {
                         global ${$value};
                         $pass_value = ${$value};
                         if (strlen($pass_value) > 0) {
                              $cookie_header .= 'Cookie: ' . 
                                                $value . '=' . $pass_value . 
                                                CRLF;
                         }
                    }
               }
          }

//
// send the message
//
          $single_stream = $first .
                           $auth_header .
                           'Host: ' . $this->address . ':' . $this->port . CRLF .
                           'Accept: */*' . CRLF .
                           'User-Agent: ' . $this->userAgent . CRLF .
                           'Date: ' . gmdate('D, d M Y G:i:s T') . CRLF .
                           $rets_headers .
                           $encoding_header .
                           $cookie_header .
                           'Pragma: no-cache' . CRLF .
                           $content_type_header .
                           $content_length_header;

          $this->openSocket();

          if ($this->socket) {
if ($this->transportTrace) {
	$this->trace('SENDING ...');
	$this->rawTrace($single_stream);
} else {
	if ($this->payloadTrace) {
		$this->trace('SENDING ...');
		$this->rawtrace($first);
		if (!$this->isGet) {
			$this->rawtrace($first);
			if ($this->args != null) {
				$this->rawTrace($this->args);
			}
		}
	}
}

               $success = @fwrite($this->socket, $single_stream);
               if (!$success) {
//
// this can happen when 
// a) you are expecting a keep-alive server
// b) that server has a response that is not authenticated
//
//echo '<XMP>Write failed</XMP>';
                    $this->openSocket();
                    @fwrite($this->socket, $single_stream);
               }
          }
     }

     function read_body_text_stream(&$HANDLER) {
//
// Transfer Encoding - the most likely form, chunked
//
          $result = $this->getHeader('TRANSFER-ENCODING');
          if ($result != null) {
               if (strlen($result) > 0) {
                    if ($result == 'chunked') {
if ($this->transportTrace) {
	$this->trace('READING the PAYLOAD as chunked');
}
                         $chunk = 1;
                         while ($chunk != 0) {
                              if (!feof($this->socket)) {
                                   $hex_chunk = fgets($this->socket, 4096);
                                   $chunk = hexdec(trim($hex_chunk));
                                   if ($chunk > 0) {
                                        $raw = $this->getLengthBuffer($chunk + 2);
                                        $pos = strrpos($raw, CRLF);
                                        if ($pos > 0) { 
                                            $raw = substr($raw, 0, $pos);
                                        }
                                        $pos = strpos($raw, CRLF);
                                        if ($pos === false) {
                                        } else {
                                             $len = strlen($raw);
                                             if ($pos == 0 && $len > 2) {
                                                  $raw = substr($raw, $pos + 2, $len);
                                             }
                                        }
if ($this->payloadTrace)
{
$this->trace('PAYLOAD ...');
$this->rawTrace($raw);
}
                                        $HANDLER->handleStream($raw);
                                   }
                              } else { 
                                   $chunk = 0;
                              }
                         }
                    }
               }
          } else {
//
// read an ascii stream
//
if ($this->transportTrace) {
$this->trace('READING the PAYLOAD as text');
}
//flush_output('payload as text ');
               $continue = true;
               while ($continue) {
                    if (!feof($this->socket)) {
                         $raw = fgets($this->socket, 4096);
//                         $HANDLER->handleStream($raw);
                         if ($raw === false) {
                              $continue = false;
                         } else {
                              $HANDLER->handleStream($raw);
                         }
                    } else {
                         $continue = false;
                    }
               }
          }
if ($this->transportTrace) {
$this->trace('FINISHED reading');
}

//
// check to see if the server wants this connection closed
//
          $check = $this->getHeader('CONNECTION');
          if ($check != null) {
               $check = strtoupper($check);
               if ($check == 'CLOSE') {
if ($this->transportTrace) {
$this->trace('CLOSING the connection because the server asked us to');
}
                    $this->closeSocket();
               }
          } 
     }

     function read_body($read_raw = false,
                        $metadata_container = null) {

//
// Transfer Encoding 
//
          $buffer = $this->read_payload();

          if (!$read_raw) {
if ($this->payloadTrace) {
$this->trace('INTERPRET the PAYLOAD as XML through parsing');
}
               $errorParser = new ErrorParser();
               $rawBuffer = $buffer;
               $buffer = $errorParser->parse($buffer);
               if (!$errorParser->error_check) {
                    if (REFORMAT_RETS_RESPONSES) {
//
// reformat the RETS response to account for errors and poor formatting 
//
                         $content_type = $this->getHeader('CONTENT-TYPE');

                         $envelopeParser = new EnvelopeParser();
                         $buffer = $envelopeParser->parse($buffer,
                                                          $content_type,
                                                          $metadata_container);
                         $formatParser = new FormatParser();
                         $buffer = $formatParser->parse($buffer);
if ($this->payloadTrace)
{
$this->trace('RESULTS of XML reformating ...');
$this->trace($buffer);
}
                    } else {
//
// return the original payload, stripped of XML header 
//
                         if (strpos($rawBuffer, '<?') === false) {
                              $buffer = $rawBuffer;
                         } else {
                              $buffer = substr($rawBuffer, 
                                               strpos($rawBuffer, '>') + 1,
                                               strlen($rawBuffer));
                         }
                    }
               } else {
if ($this->transportTrace) {
$this->trace('ERROR ...');
$this->rawTrace($rawBuffer);
}
                    $this->error = true;
                    $this->lastError = $errorParser->getErrorText();
                    $buffer = null;
               }
          }
          $this->body = $buffer;

//
// check to see if the server wants this connection closed
//
          $check = $this->getHeader('CONNECTION');
          if ($check != null) {
               if (strtoupper($check) == 'CLOSE') {
if ($this->transportTrace) {
$this->trace('CLOSING the connection because the server asked us to');
}
                    $this->closeSocket();
               }
          } 
     }

     function showRemoteCookies() {
$this->trace('SHOWING remote cookies');
          global $remote_cookies;
          if (strlen($remote_cookies) > 0) {
               $cookie_headers = explode(',', $remote_cookies);
               if (is_array($cookie_headers)) {
                    foreach ($cookie_headers as $key => $value) {
                         global ${$value};
                         $pass_value = ${$value};
                         if (strlen($pass_value) > 0) {
$this->trace('COOKIE: ' .  $value . '=' . $pass_value);
                         }
                    }
               }
          }
     }

     function clearRetsSession() {
//$this->trace('###########CLEAR COOKIES');
          global $remote_cookies;
          if (strlen($remote_cookies) > 0) {
               $cookie_headers = explode(',', $remote_cookies);
               if (is_array($cookie_headers)) {
                    foreach ($cookie_headers as $key => $value) {
                         global ${$value};
                         ${$value} = null;
                    }
               }
          }

          $this->headers = null;
          $this->sessionID = null;
          $this->sessionHeader = null;
     }

     function setRetsSession() {
          $headerName = null;
          $result = null;
          foreach ($this->headers as $key => $value) {
               if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                         if (strpos(strtoupper($key2), 'SESSION') > 0) {
                              $this->isSessionCookie = true;
                              $headerName = $key2;
                              $result = $value2;
                              $pos = strpos($result, ';');
                              if ($pos > 0) {
                                   $result = substr($result, 0, $pos);
                              }
                         }
                    }
               } else {
                    if (strpos(strtoupper($key), 'SESSION') > 0) {
                         $this->isSessionCookie = false;
                         $headerName = $key;
                         $result = $value;
                    }
               }
          }
          $this->sessionID = trim($result);
          $this->sessionHeader = $headerName;
     }

     function getRetsSessionHeader() {
          return $this->sessionHeader;
     }

     function getRetsSession() {
          return $this->sessionID;
     }

     function isRetsSessionCookie() {
          return $this->isSessionCookie;
     }

}

//
// IO functions
//
//---------------------

?>
