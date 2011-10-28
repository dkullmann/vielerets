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

class Source 
     extends Location 
{
     function Source($directory = null)
     {
          if ($directory == null)
          {
               parent::Location(SOURCE_DIRECTORY);
          }
          else
          {
               parent::Location($directory);
          }
     }

     function copyMetadata($oldName,
                           $newName)
     {
          $METADATA = new Metadata($oldName);
          $METADATA->copyMetadata($newName);
     }

     function isValidConfiguration($aName)
     {
          $S_CONFIGURATION = $this->getConfiguration($aName);
          $METADATA_CLASS = new ClassMetadata($aName, 
                                              $S_CONFIGURATION->getValue('SELECTION_RESOURCE'));
          if (!$METADATA_CLASS->isValid()) 
          {
               return false;
          }
          return true;
     }

     function moveConfiguration($oldName,
                                $newName)
     {
//
// move metadata
//
          $METADATA = new Metadata($oldName);
          $METADATA->copyMetadata($newName);
          $METADATA->remove();

//
// move configuration
//
          $this->copyConfiguration($oldName, $newName);
          $this->removeConfiguration($oldName);
     }
}

function verifyTransport($retsServerURL,
                         $application,
                         $version,
                         $detectedDefaultRetsVersion) {
     if ($retsServerURL == null || $retsServerURL == '') {
          $err_number = 101;
          $err_text = 'No URL given';
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;
     }
     $buffer = parse_url($retsServerURL);
     $address = $buffer['host'];
     if (array_key_exists('port', $buffer)) {
          $port = $buffer['port'];
     } else {
          $port = DEFAULT_PORT;
     }

//
// check URL for login 
//
     if (array_key_exists('path', $buffer)) {
         $command = $buffer['path'];
     } else {
          $err_number = 100;
          $err_text = 'No login command given';
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;
     }
     $pos = strpos($command, '/');
     if ($pos === false) {
          $err_number = 100;
          $err_text = 'No login command given';
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;
     }

     if ($pos == 0) {
          $command = substr($command,
                            $pos + 1,
                            strlen($command));
     }
//echo "<XMP>COMMAND $command</XMP>";

//
// check socket
//
     set_time_limit(0);
     $socket = @fsockopen($address, $port, $errno, $errstr);
     if (!$socket) {
          $err_number = 200;
//
// posix codes - linux /usr/include/asm/errno.h
//
//       	0 	General error	
//       	16 	No network connect	
// ETIMEDOUT 	110	Connection timed out
// ECONNREFUSED	111	Connection refused
// EHOSTDOWN	112	Host is down
// EHOSTUNREACH	113	No route to host
//
          switch ($errno) {
               case 0:
                    $err_text = 'Not found - Check URL ' . $address;
                    break;

               case 16:
                    $err_text = 'No Network - ' .
                                $address .
                                ' cannot be contacted';
                    break;

               case 110:
                    $err_text = 'Timeout - Check firewall for port ' .  $port;
                    break;

               case 111:
                    $err_text = 'Refused - Check RETS server or firewall for port ' .  $port;
                    break;

               case 64:
                    $err_number = 102;
                    $err_text = 'Not found - Check URL ' . $address;
	
                    break;

               case 77:
                    $err_number = 102;
                    $err_text = 'Not found - Check URL ' . $address;
	
                    break;

               case 5296416:
                    $err_number = 102;
                    $err_text = 'Not found - Check URL ' . $address;
	
                    break;

               default:
                    $err_text = 'Received code ' .  $errno .  '-' .  
                                $errstr;
                    break;
          }
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;

     }

//
// prepare message 
//
     if (array_key_exists('query', $buffer)) {
          $query = $buffer['query'];
          if (strlen($query) > 0) {
               $command = $command . '?' . $query;
          }
//echo "<XMP>QUERY $query</XMP>";
     }

     $first = 'GET /' . $command . ' HTTP/1.1' . "\r\n";

     $host_header = 'Host: ' . 
                    $address . 
                    ':' . 
                    $port . 
                    "\r\n";
     $accept_header = sprintf('Accept: */*' . "\r\n");
     $agent_header = 'User-Agent: ' . 
                     $application .
                     '/' . 
                     $version .
                     "\r\n";

     $rets_header = 'RETS-Version: RETS/' .  
                    $detectedDefaultRetsVersion .
                    "\r\n";

     $message = $first .
                $host_header .
                $accept_header .
                $agent_header .
                $rets_header . "\r\n";

//echo "<XMP>$message</XMP>";

//
// write to the socket
//
     $write = @fwrite($socket, $message);
     if (!$write) {
          $err_number = 300;
          $err_text = 'Check command ' . $command;
          fclose($socket);
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;
     }

//
// read return code 
//
     $flag = true;
     while ($flag) {
          $ret = fgets($socket, 4096);
          if ($ret === false) {
               $err_number = 700;
               $err_text = 'Server terminated prematurely reading return code';
               fclose($socket);
               return;
          }
          $ret = trim($ret);
          if (strlen($ret) > 0) {
               $flag = false;
          }
     }
//echo "<XMP>$ret</XMP>";

//
// HTTP 1.1 check
//
     $pos = strpos($ret, 'TTP/1.1');
     if (!$pos) {
          $err_number = 400;
          $err_text = 'No support for HTTP 1.1';
          fclose($socket);
          $connection = new HttpConnection();
          $connection->setError($err_number, $err_text);
          return $connection;
     }

//
// check if server returns an auth challenge (401)
//
/*
     $pos = strpos($ret, "401");
     if (!$pos) 
     {
          $err_number = 500;
          $err_text = "No authentication challenge, check URL";
          fclose($socket);
          return;
     }
*/

//
// read header lines 
//
     $flag = true;
     $count = 0;
     while ($flag) {
          if (!feof($socket)) {
               $raw = fgets($socket, 4096);
               if ($raw === false) {
                    $err_number = 701;
                    $err_text = 'Server terminated prematurely on headers';
                    fclose($socket);
                    $connection = new HttpConnection();
                    $connection->setError($err_number, $err_text);
                    return $connection;
               }
               $raw = trim($raw);
               if (strlen($raw) == 0) {
                    $flag = false;
               } else {
                    $result[$count] = $raw;
                    $count++;
               }
          } else { 
               $flag = false;
          }
     }
//print_r($result);

//
// close connection to the server
//
     fclose($socket);

//
// construct header array, skipping cookies
//
     $headers = null;
     foreach ($result as $num => $header) {
          $hpos = strpos($header, ':');
          $key = trim(substr($header, 0, $hpos));
          $value = trim(substr($header, $hpos + 1, strlen($header)));

          $pos = strpos($key, 'Set-Cookie');
          if ($pos === false) {
               $headers[strtoupper($key)] = $value;
          } 
     }
//print_r($headers);

//
// try to identify the server
//
     if (array_key_exists('SERVER', $headers)) {
          $server_name = $headers['SERVER'];
          if ($server_name == null) {
//               $err_number = 600;
//               $err_text = "Server does not identify itself";
//               return;
               $server_name = 'none';
          }
     } else {
          $server_name = 'none';
     }

//
// all is well
//     
     $connection = new HttpConnection();
     $connection->setError(0, 'success');
     $connection->setServerName($server_name);

//
// try to identify the server timezone
//
     if (array_key_exists('DATE', $headers)) {
          $connection->setServerDate($headers['DATE']);
     }

     return $connection; 
}

function verifyRETS($element,
                    $retsServerAccount,
                    $retsServerPassword,
                    $retsServerURL,
                    $detectedDefaultRetsVersion,
                    $application,
                    $version,
                    $retsClientPassword,
                    $postRequests) {
     $err_number = 100;
     $err_text = 'This can be due to a number of reasons:<br/>' .
                 '<ol>' .
                 '<li>Incorrect <b>url</b>.</li><br/>' .
                 '<li>Wrong <b>account</b> or <b>password</b>.</li><br/>' .
                 '<li>If your server requires a <b>client</b><br/>' .
                 '<b>password</b>, a wrong value</li>' .
                 '</ol>' .
                 'Please check these values and remember<br/>' .
                 'that they are case-sensitive.';
     $EXCHANGE = new Exchange($element);
     $result = $EXCHANGE->loginDirect($retsServerAccount,
                                      $retsServerPassword,
                                      $retsServerURL,
                                      $detectedDefaultRetsVersion,
                                      $application,
                                      $version,
                                      $retsClientPassword,
                                      $postRequests);
     if ($result) {
//
// check for error
//
          if ($EXCHANGE->hasErrors()) {
               $err_number = 105;
               $err_text = $EXCHANGE->getLastError();
          } else {
               $EXCHANGE->logoutDirect();
               $err_number = 0;
               $err_text = 'Success';
          }
      }
//      else
//      {
//          if ($EXCHANGE->hasErrors())
//          {
//               $err_number = 100;
//               $err_text = $EXCHANGE->getLastError();
//         }
//      }

     $connection = new HttpConnection();
     $connection->setError($err_number, $err_text);

     return $connection; 
}

//------------

class HttpConnection {
     var $error_number;
     var $error_text;
     var $server_name;
     var $server_date;

     function HttpConnection() {
     }

     function setServerName($value) {
          $this->server_name = $value;
     }

     function setServerDate($value) {
          $this->server_date = $value;
     }

     function setError($number,
                       $text) {
          $this->error_number = $number;
          $this->error_text = $text;
     }

     function getErrorNumber() {
          return $this->error_number;
     }

     function getErrorText() {
          return trim($this->error_text);
     }

     function getServerName() {
          return $this->server_name;
     }

     function getServerDate() {
          return $this->server_date;
     }

     function getServerTimeOffset() {
//
// server local time
//
          $s_time = explode(' ', $this->server_date);

//
// server UTC time
//
          date_default_timezone_set('UTC');
          $display = date(DATE_W3C,strtotime($this->server_date));
          $u_time_whole = substr($display, strpos($display, 'T') + 1, strlen($display));
          $u_time = substr($u_time_whole, 0, strpos($u_time_whole, '+'));

          return (strtotime($s_time[4])-strtotime($u_time))/3600;
     }

}

//
//------------

?>
