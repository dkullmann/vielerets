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

//
// defines
//
define('DEFAULT_APPLICATION', 'ExchangeClient');// default application
define('DEFAULT_VERSION','0.9.1');		// default version
define('DEFAULT_PORT','80');		        // default HTTP port 
define('REFORMAT_RETS_RESPONSES', false);	// reformat for poorly coded RETS servers 

class Exchange 
     extends AbstractRetsExchange 
{

     var $perf;
     var $rt;
     var $name;
     var $error;
     var $lastError;
     var $networkTime = 0;
     var $urls;
     var $args;
     var $member;
     var $userAgent;
     var $userAgentPassword;
     var $retsVersion;
     var $retsSession;
     var $retsSessionHeader;
     var $isRetsSessionCookie;
     var $postRequests;
     var $defaultImageCount = 6;
     var $imageThreshold = 1000;
     var $socket = false;
     var $cookies = null;
     var $encodedAuth = null;
     var $credentialList = null;
     var $basicAuthFlag = false;

     function Exchange($name = null) {
          parent::AbstractRetsExchange();
          $this->reset();
          if ($name != null) {
               $this->setName($name);
          }
     }

     function reset() {
          $this->rt = null;
          $this->rt['TOTAL'] = null;
          $this->rt['LOGIN'] = null;
          $this->rt['ACTION'] = null;
          $this->rt['SEARCH'] = null;
          $this->rt['GETMETADATA'] = null;
          $this->rt['GETOBJECT'] = null;
          $this->rt['LOGOUT'] = null;
 
          $this->perf = null; 
          $this->perf['POST_ACTION'] = null;
          $this->perf['PRE_ACTION'] = null;
          $this->perf['POST_GETOBJECT'] = null;
          $this->perf['PRE_GETOBJECT'] = null;
          $this->perf['POST_SEARCH'] = null;
          $this->perf['PRE_SEARCH'] = null;
          $this->perf['POST_GETMETADATA'] = null;
          $this->perf['PRE_GETMETADATA'] = null;
          $this->perf['POST_LOGOUT'] = null;
          $this->perf['PRE_LOGOUT'] = null;

          $this->networkTime = 0;

          $this->stampEvent('BEGIN');
     }

     function finish() {
          $this->stampEvent('END');

          $this->rt['TOTAL'] += $this->perf['END'] - 
                                $this->perf['BEGIN'];
     }

     function stampEvent($eventName) {
          list($usec, $sec) = explode(' ',microtime());
          $this->perf[$eventName] = ((float)$usec + (float)$sec);
          switch ($eventName) {
            case 'POST_LOGIN':
               $this->rt['LOGIN'] += $this->perf['POST_LOGIN'] - $this->perf['PRE_LOGIN'];
               break;
        
            case 'POST_ACTION':
               $this->rt['ACTION'] += $this->perf['POST_ACTION'] - $this->perf['PRE_ACTION'];
               break;
        
            case 'POST_SEARCH':
               $this->rt['SEARCH'] += $this->perf['POST_SEARCH'] - $this->perf['PRE_SEARCH'];
               break;
        
            case 'POST_GETMETADATA':
               $this->rt['GETMETADATA'] += $this->perf['POST_GETMETADATA'] - $this->perf['PRE_GETMETADATA'];
               break;
        
            case 'POST_GETOBJECT':
               $this->rt['GETOBJECT'] += $this->perf['POST_GETOBJECT'] - $this->perf['PRE_GETOBJECT'];
               break;
        
            case 'POST_LOGOUT':
               $this->rt['LOGOUT'] += $this->perf['POST_LOGOUT'] - $this->perf['PRE_LOGOUT'];
               break;
        
          }
     }

     function getArgs($transType) {
          if (array_key_exists($transType, $this->args)) {
               return $this->args[$transType];
          }
          return '';
     }

     function getRuntime() {
          return $this->rt;
     }

     function getNetworkTime() {
          return $this->networkTime;
     }

     function getDefaultImageCount() {
          return $this->defaultImageCount;
     }

     function setDefaultImageCount($value) {
          $this->defaultImageCount = $value;
     }

     function getImageThreshold() {
          return $this->imageThreshold;
     }

     function setImageThreshold($value) {
          $this->imageThreshold = $value;
     }

     function addSubtransaction($EXCHANGE) {
          $this->networkTime += $EXCHANGE->getNetworkTime();
          $sub = $EXCHANGE->getRuntime();
          foreach($sub as $key => $val) {
               $this->rt[$key] += $val;
          }
     }

     function ensureMetadata($cName,
                             $resource,
                             $class,
                             $standardNames) {
          $METADATA_RESOURCE = new ResourceMetadata($cName);
          if (!$METADATA_RESOURCE->isValid()) {
               $this->refreshMetadata($cName,
                                      $resource,
                                      $class,
                                      $standardNames);
          }
     }

     function refreshMetadata($cName,
                              $resource,
                              $class,
                              $standardNames) {
//
// convert class name is necessary
//
          $METADATA_CLASS = new ClassMetadata($cName, $resource);
          $systemClass = $METADATA_CLASS->getSystemClass($class, $standardNames);

//
// Refresh Resource
//
          $METADATA_RESOURCE = new ResourceMetadata($cName);
          $METADATA_RESOURCE->remove();
          $METADATA_RESOURCE->updateContents($this->serverResourceMetadata());

          $this->refreshCoreMetadata($cName,
                                     $resource,
                                     $systemClass);
     }

     function refreshCoreMetadata($cName,
                                  $resource,
                                  $systemClass) {
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - Resource ' . $resource);
}

//
// Object 
//
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - ' . $resource . ' Objects');
}
          $METADATA = new ObjectMetadata($cName, $resource);
          $METADATA->updateContents($this->serverObjectMetadata($resource));

//
// Search Help 
//
/*
if ($this->useDisplayHandler)
{
$this->DISPLAY_HANDLER->displayMessage(" - " . $resource . " SearchHelp");
}
          $METADATA = new SearchHelpMetadata($cName, $resource);
          $METADATA->updateContents($this->serverSearchHelpMetadata($resource));
*/

//
// Class 
//
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - ' . $resource . ' Classes');
}
          $METADATA = new ClassMetadata($cName, $resource);
          $METADATA->updateContents($this->serverClassMetadata($resource));

//
// Table 
//
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - Table for ' . $resource . ', Class ' . $systemClass);
}
          $METADATA_TABLE = new TableMetadata($cName, $systemClass);
          $METADATA_TABLE->updateContents($this->serverTableMetadata($resource, $systemClass));

//
// Lookup 
//
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - ' . $resource . ' Lookups');
}
          $METADATA = new LookupMetadata($cName, $resource);
          $METADATA->updateContents($this->serverLookupMetadata($resource));

//
// LookupType 
//
          $lookups = $METADATA_TABLE->findDataLookupNames(); 
          if ($lookups != null) {
               foreach ($lookups as $key => $lookup) {
if ($this->useDisplayHandler) {
$this->DISPLAY_HANDLER->displayMessage(' - LookupTypes for ' . $resource . ', Lookup ' . $lookup);
}
                    $METADATA = new LookupTypeMetadata($cName, $lookup);
                    $METADATA->updateContents($this->serverLookupTypeMetadata($resource, $lookup));
               }
          }
     }

     function loginDirect($account,
                          $password,
                          $url,
                          $retsVersion,
                          $application = null,
                          $version = null,
                          $clientPassword = null,
                          $postRequests) {
          $this->stampEvent('PRE_LOGIN');

          $this->member = new Member($account, $password);
          $this->postRequests = $postRequests;

          $this->error = false;
          $this->setUserAgent($application, $version);
          $this->setUserAgentPassword($clientPassword);
          $this->retsVersion = $retsVersion;

//
// parse URL
//
          $buffer = parse_url($url);
          $remoteAddress = $this->deriveRemoteAddress($buffer);
          $remotePort = $this->deriveRemotePort($buffer);

//
// perform initial login
//
          $query = null;
          if (array_key_exists('query', $buffer)) {
               $query = $buffer['query'];
          }
          $this->urls['INITIAL_LOGIN'] = $url; 
          $REQUEST = $this->readURL('INITIAL_LOGIN', $query, $this->postRequests);
          if ($this->error) {
               return false;
          }

//
// process response
//
          $this->retsSession = $REQUEST->getRetsSession();
          $this->retsSessionHeader = $REQUEST->getRetsSessionHeader();
          $this->isRetsSessionCookie = $REQUEST->isRetsSessionCookie();
          $this->setRetsVersion($REQUEST->getRetsVersion());
 
          $buffer = $REQUEST->getBody();

          if (strlen($buffer) > 0) {
               global $LOGIN,
                      $ACTION,
                      $LOGOUT,
                      $GETMETADATA,
                      $GETOBJECT,
                      $SEARCH,
                      $MEMBERNAME,
                      $USER; 

               $LOGIN = null;
               $ACTION = null;
               $LOGOUT = null;
               $GETMETADATA = null;
               $GETOBJECT = null;
               $SEARCH = null;
               $MEMBERNAME = null;
               $USER = null;

               $this->registerVariables($buffer, $REQUEST);

//
// validate URLs
//
               $this->urls['LOGIN'] = $this->ensureUrl($remoteAddress, 
                                                       $remotePort, 
                                                       $LOGIN);
               if ($ACTION != null) {

                    $this->urls['ACTION'] = $this->ensureUrl($remoteAddress, 
                                                             $remotePort, 
                                                             $ACTION);
               }

               $this->urls['SEARCH'] = $this->ensureUrl($remoteAddress, 
                                                        $remotePort, 
                                                        $SEARCH);
               $this->urls['GETOBJECT'] = $this->ensureUrl($remoteAddress, 
                                                           $remotePort, 
                                                           $GETOBJECT);
               $this->urls['GETMETADATA'] = $this->ensureUrl($remoteAddress, 
                                                             $remotePort, 
                                                             $GETMETADATA);
               $this->urls['LOGOUT'] = $this->ensureUrl($remoteAddress, 
                                                        $remotePort, 
                                                        $LOGOUT);

//
// capture the member name 
//
               global $MEMBERNAME;
               if ($MEMBERNAME == null) {
//                    $this->error = true;
//                    $this->lastError = 'No member name';
                    $this->setError('No member name');
                    return false;
               }
               $this->member->setName($MEMBERNAME);

//
// capture the agent id 
//
               global $USER;
               if ($USER != null) {
                    $token = explode(',', $USER);
                    reset($token);
                    $this->member->setAgentID($token[0]);
               }

//
// capture the broker id 
//
               global $BROKER;
               if ($BROKER != null) {
                    $this->member->setBrokerID($BROKER);
               }
               $this->stampEvent('POST_LOGIN');
          } else {
               $this->setError('No capabilities');
               $this->stampEvent('POST_LOGIN');
               return false;
          }

//
// perform action if required
//
          if ($this->urls != null) {
               if (array_key_exists('ACTION', $this->urls)) {
                    $REQUEST = $this->readURL('ACTION', null, false, true); 
//$junk = $REQUEST->getBody();
//$this->setError('<XMP>' . $junk . '</XMP>');
//echo "<XMP>BODY $junk</XMP>";
                    $this->member->setNotice($REQUEST->getBody());
               }
          }

          return true;
     }

     function readURL($url_name,
                      $args = null,
                      $send_post,
                      $read_raw = false,
                      $metadata_container = null) {
          $buffer = parse_url($this->urls[$url_name]);
if ($this->transportTrace || $this->streamTrace) {
//$this->trace('FUNCTION: ' . $url_name);
//$this->trace($buffer);
}
          $anAddress = $this->deriveRemoteAddress($buffer);
          $aPort = $this->deriveRemotePort($buffer);
//
// using io.php 
//
/*
if ($this->retsSessionHeader != null)
{
$this->trace('#########');
if ($this->isRetsSessionCookie)
{
$this->trace('COOKIE');
}
else
{
$this->trace('HEADER');
}
$this->trace($this->retsSession);
$this->trace($this->retsSessionHeader);
}
*/

if ($this->transportTrace || $this->streamTrace) {
$this->trace('CREATE new Request for ' . $url_name);
}

          $REQUEST = new Request($anAddress,
                                 $aPort,
                                 $this->member->account,
                                 $this->member->password,
                                 $this->getUserAgent(),
                                 $this->getUserAgentPassword(),
                                 $this->getRetsVersion(),
                                 $send_post);
          $REQUEST->setContext($this->deriveCommand($buffer), $args);
          $REQUEST->setPayloadTrace($this->payloadTrace);
          $REQUEST->setTransportTrace($this->transportTrace);
          $REQUEST->setTraceDevice($this->traceDevice);

//
// use information from the last transfer
//
          $REQUEST->setSocket($this->socket);
          $REQUEST->setCookies($this->cookies);
          $aMessage = $REQUEST->getMessage();

          if ($this->credentialList != null) {
               $found = false;
               if ($this->basicAuthFlag) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('LOOK for an existing credential for ALL transmissions'); 
}
                    $REQUEST->setCredential($this->credentialList['ALL']);
                    $found = true;
               } else {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('LOOK for an existing credential for [' . $aMessage . ']'); 
}
                    if (array_key_exists($aMessage, $this->credentialList)) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('REUSE an existing credential'); 
}
                         $this->encodedAuth = $this->credentialList[$aMessage];
                         $REQUEST->setCredential($this->encodedAuth);
                         $found = true;
                    }
               }
               if (!$found) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('CREATE a new credential'); 
}
                         $this->encodedAuth = false;
               }
          } else {
               $REQUEST->setCredential($this->encodedAuth);
          }

//
// send the message
//
if ($this->transportTrace || $this->streamTrace) {
     if ($read_raw) {
$this->trace('READ as raw stream');
     } else {
$this->trace('READ as an XML formatted stream');
     }
}
          $REQUEST->process($read_raw, $metadata_container, $this->encodedAuth);
          $this->networkTime += $REQUEST->getNetworkTime();

          if ($REQUEST->hasErrors()) {
               $this->setError($REQUEST->getLastError());
          }

//
// remember information for the next transfer
//
          $this->encodedAuth = $REQUEST->shareCredential();
          if (is_object($this->encodedAuth)) {
               if ($this->encodedAuth->isBasicAuth()) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('SAVE the credential for ALL tranmissions'); 
}
                    $this->credentialList['ALL'] = $this->encodedAuth;
                    $this->basicAuthFlag = true;
               } else {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('SAVE the credential for [' . $aMessage . ']'); 
}
                    $this->credentialList[$aMessage] = $this->encodedAuth;
               }
          }
          $this->cookies = $REQUEST->shareCookies();
          $this->socket = $REQUEST->shareSocket();

          return $REQUEST;
     }

     function getUserAgentPassword() {
          return $this->userAgentPassword;
     }

     function setUserAgentPassword($aValue) {
          if ($aValue == '') {
               $this->userAgentPassword = null;
          } else {
               $this->userAgentPassword = $aValue;
          }
     }

     function getUserAgent() {
          return $this->userAgent;
     }

     function setUserAgent($application = null,
                           $version = null) {
          if ($version == null && $application != null) {
//
// Rappatoni allows a User-Agent that does not have a version component
//
               $this->userAgent = $application;
          } else {
               if ($application == null) {
                     $application = DEFAULT_APPLICATION;
                     $version = DEFAULT_VERSION;
               }
               $this->userAgent = $application . '/' .  $version;
          }
     }

     function getRetsVersion() {
          return $this->retsVersion;
     }

     function setRetsVersion($version) {
          $this->retsVersion = $version;
     }

     function registerVariables($buffer,
                                $REQUEST) {
          $buffer = substr($buffer, strpos($buffer, '>') + 1, strlen($buffer));

//
// for version 1.5 and above move beyond the <RETS> tag to the RETS-RESPONSE tag
//
          $retsVersion = $REQUEST->getRetsVersion();
          if ($retsVersion == '1.5' || $retsVersion == '1.7' || $retsVersion == '1.7.2') {
               $buffer = substr($buffer, strpos($buffer, '>') + 1, strlen($buffer));
          }

//
// determine the position of the closing tag and take a substring 
//
          $buffer = substr($buffer, 0, strpos($buffer, "<"));
          $buffer = trim($buffer);

//
//  determine division
//
          $div_stream = "\r\n";
          $pos = strpos($buffer, $div_stream);
          if ($pos === false) {
               $div_stream = "\r";
               $pos = strpos($buffer, $div_stream);
               if ($pos === false) {
                    $div_stream = "\n";
                    $pos = strpos($buffer, $div_stream);
                    if ($pos === false) {
                        $div_stream = ' ';
                    }
               } 
          }

//
//  Divide long stream into smaller ones
//
          $args = explode($div_stream, $buffer);
          foreach ($args as $key => $value) {
               $temp = explode('=', $value);
               if (sizeof($temp) > 1) {
                    $k = trim($temp[0]);
                    $v = trim($temp[1]);
                    if (sizeof($temp) == 3) {
                         $v .= '=' . $temp[2];
                    }
//
// using component.php
//
                    $REQUEST->register_variable(strtoupper($k), $v);
               }
          }
     }

     function ensureUrl($anAddress, 
                        $aPort, 
                        $uri) {
          $uri = trim($uri);
          if ($uri == null) {
               return null;
          }
          if (strlen($uri) == 0) {
               return null;
          }
          if (strpos($uri, 'http://') === false) {
               return 'http://' . $anAddress . ':' . $aPort . $uri;
          }

          return $uri;
     }

     function hasErrors() {
          return $this->error;
     }

     function getLastError() {
          return $this->lastError;
     }

     function setError($text) {
          $this->error = true;
          $this->lastError = $text;
     }

     function resetErrors() {
          $this->error = false;
          $this->lastError = null;
     }

     function setName($name) {
          $this->name = $name;
     }

     function getOwnerID() {
          return $this->member->getAgentID();
     }

     function countDataDirect($DATA_REQUEST,
                              $standard_names,
                              $compact_decoded_format = true) {
          $this->stampEvent('PRE_SEARCH');

//
// select clause 
//
          $select_args = $DATA_REQUEST->getSelectClause();
          $searchArgs['SELECT CLAUSE'] = $select_args;

//
// query clause 
//
          $query_args = $DATA_REQUEST->getQueryClause();
          $searchArgs['QUERY CLAUSE'] = $query_args;

//
// query description clause 
//
          $type_args = $DATA_REQUEST->getTypeClause($compact_decoded_format,
                                                    $standard_names,
                                                    1,
                                                    true);
          $searchArgs['TYPE CLAUSE'] = $type_args;

//
// contact server 
//
          $this->args['SEARCH'] = $searchArgs;
          $REQUEST = $this->readURL('SEARCH', 
                                    $select_args . '&' .  $query_args . '&' .  $type_args,
                                    $this->postRequests);

//
// return a SearchResponse object
//
          $DATA_RESPONSE = new SearchResponse($REQUEST->getBody(),
                                              $REQUEST->getHeader('CONTENT-TYPE'));
          $DATA_RESPONSE->setTransportTrace($this->transportTrace);
          $DATA_RESPONSE->setTraceDevice($this->traceDevice);

//
// finish
//
          $this->stampEvent('POST_SEARCH');

          return $DATA_RESPONSE;
     }

     function searchDataDirect($DATA_REQUEST,
                               $standard_names,
                               $startingWith = 1,
                               $compact_decoded_format = true) {

          $this->stampEvent('PRE_SEARCH');

//
// select clause 
//
          $select_args = $DATA_REQUEST->getSelectClause();
          $searchArgs['SELECT CLAUSE'] = $select_args;

//
// query clause 
//
          $query_args = $DATA_REQUEST->getQueryClause();
          $searchArgs['QUERY CLAUSE'] = $query_args;

//
// query description clause 
//
          $type_args = $DATA_REQUEST->getTypeClause($compact_decoded_format,
                                                    $standard_names,
                                                    $startingWith);
          $searchArgs['TYPE CLAUSE'] = $type_args;

//
// contact server 
//
if ($this->payloadTrace) {
$this->trace('SELECT CLAUSE: ' . $select_args);
$this->trace('QUERY CLAUSE (decoded): ' .  urldecode($query_args));
$this->trace('TYPE CLAUSE: ' . $type_args);
}
          $this->args['SEARCH'] = $searchArgs;
          $REQUEST = $this->readURL('SEARCH', 
                                    $select_args . '&' .  $query_args . '&' .  $type_args,
                                    $this->postRequests);
//
// return a SearchResponse object
//
          $DATA_RESPONSE = new SearchResponse($REQUEST->getBody(),
                                              $REQUEST->getHeader('CONTENT-TYPE'),
                                              $DATA_REQUEST->getSelectionTypes(),
                                              $DATA_REQUEST->getSelectionInterpretations());
          $DATA_RESPONSE->setTransportTrace($this->transportTrace);
          $DATA_RESPONSE->setTraceDevice($this->traceDevice);

//
// trap the error
//
          if ($REQUEST->getBody() == null) {
//               $DATA_RESPONSE->setError($REQUEST->lastError);
               $DATA_RESPONSE->setError($REQUEST->getLastError());
          }

//
// reformat currency items
//
          $criteria = explode(',', $DATA_REQUEST->getSelectionCriteria());
          $conversion = false;
          foreach ($criteria as $key => $value) {
               if ($DATA_RESPONSE->getInterpretation($value) == 'Currency') {
                    $conversion = true;
               }
          }
              
          if ($conversion) { 
               $rows = $DATA_RESPONSE->getValues();
               if ($rows != null) {
                    $n_rows = null;
                    foreach ($rows as $num => $row) {
                         $n_row = null;
                         foreach ($row as $key => $value) {
                              if ($DATA_RESPONSE->getInterpretation($key) == 'Currency') {
                                   $n_row[$key] = str_replace(",", "", $value);
                              } else {
                                   $n_row[$key] = $value;
                              }
                         }
                         $n_rows[] = $n_row;
                    }
                    $DATA_RESPONSE->setValues($n_rows);
               }
          }

//
// finish
//
          $this->stampEvent('POST_SEARCH');

          return $DATA_RESPONSE;

     }

     function searchDataTextStream($DATA_REQUEST,
                                   $compact_decoded_format,
                                   $standard_names,
                                   &$HANDLER,
                                   $startingWith = 1) {
          $this->stampEvent('PRE_SEARCH');

//
// select clause 
//
          $select_args = $DATA_REQUEST->getSelectClause();
          $searchArgs['SELECT CLAUSE'] = $select_args;

//
// query clause 
//
          $query_args = $DATA_REQUEST->getQueryClause();
          $searchArgs['QUERY CLAUSE'] = $query_args;

//
// query description clause 
//
          $type_args = $DATA_REQUEST->getTypeClause($compact_decoded_format,
                                                    $standard_names,
                                                    $startingWith);
          $searchArgs['TYPE CLAUSE'] = $type_args;

//
// contact server 
//
          $this->args['SEARCH'] = $searchArgs;
          $buffer = parse_url($this->urls['SEARCH']);
          $anAddress = $this->deriveRemoteAddress($buffer);
          $aPort = $this->deriveRemotePort($buffer);
          $aCommand = $this->deriveCommand($buffer);
//
// using io.php 
//
          $REQUEST = new Request($anAddress,
                                 $aPort,
                                 $this->member->account,
                                 $this->member->password,
                                 $this->getUserAgent(),
                                 $this->getUserAgentPassword(),
                                 $this->getRetsVersion(),
                                 true);
          $REQUEST->setContext($this->deriveCommand($buffer),
                               $select_args . '&' .  $query_args . '&' .  $type_args);
          $REQUEST->setPayloadTrace($this->payloadTrace);
          $REQUEST->setTransportTrace($this->transportTrace);
          $REQUEST->setTraceDevice($this->traceDevice);

//
// use information from the last transfer
//
          $REQUEST->setSocket($this->socket);
          $REQUEST->setCookies($this->cookies);
          $aMessage = $REQUEST->getMessage();
          if ($this->credentialList != null) {
               $found = false;
               if ($this->basicAuthFlag) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('LOOK for an existing credential for ALL transmissions'); 
}
                    $REQUEST->setCredential($this->credentialList['ALL']);
                    $found = true;
               } else {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('LOOK for an existing credential for [' . $aMessage . ']'); 
}
                    if (array_key_exists($aMessage, $this->credentialList)) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('REUSE an existing credential'); 
}
                         $this->encodedAuth = $this->credentialList[$aMessage];
                         $REQUEST->setCredential($this->encodedAuth);
                         $found = true;
                    }
               }
               if (!$found) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('CREATE a new credential'); 
}
                         $this->encodedAuth = false;
               }
          } else {
               $REQUEST->setCredential($this->encodedAuth);
          }

//
// send the message
//
if ($this->transportTrace || $this->streamTrace) {
$this->trace('READ as a handled text stream');
}
          $REQUEST->processTextStream($HANDLER);

//
// remember information for the next transfer
//
          $this->encodedAuth = $REQUEST->shareCredential();
          if (is_object($this->encodedAuth)) {
               if ($this->encodedAuth->isBasicAuth()) {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('SAVE the credential for ALL tranmissions'); 
}
                    $this->credentialList['ALL'] = $this->encodedAuth;
                    $this->basicAuthFlag = true;
               } else {
if ($this->transportTrace || $this->streamTrace) {
$this->trace('SAVE the credential for [' . $aMessage . ']'); 
}
                    $this->credentialList[$aMessage] = $this->encodedAuth;
               }
          }
          $this->cookies = $REQUEST->shareCookies();
          $this->socket = $REQUEST->shareSocket();


//
// finish
//
          $this->stampEvent('POST_SEARCH');

     }

     function searchMedia_priv($MEDIA_REQUEST,
                               $max_images = null) {
          $this->stampEvent('PRE_GETOBJECT');
//
// determine the maximum number of images
//
          if ($max_images == null) {
               $max_images = $this->defaultImageCount;
          } else {
               if ($max_images == 0) {
                    $max_images = $this->defaultImageCount;
               }
          }

if ($this->transportTrace) {
$this->trace('CREATE links to images');
}
          if ($MEDIA_REQUEST->getMultipart() && 
              $MEDIA_REQUEST->getLocation()) {

//
// if server supports multipart objects and location
//

if ($this->transportTrace) {
$this->trace('DETECTED support for RETS Multipart option for GetObject');
}
               $largs = 'Resource=' . $MEDIA_REQUEST->getResource() .
                        '&Type=' . $MEDIA_REQUEST->getMediatype() .
                        '&ID=' . $MEDIA_REQUEST->getMediaID() .
                        '&Location=1';

//
// contact server
//
               $this->args['GETOBJECT'][] = $largs;
if ($this->streamTrace) {
$display = urldecode($largs);
$this->trace('ARGUMENTS: ' . $display); 
}
               $REQUEST = $this->readURL('GETOBJECT', $largs, $this->postRequests, true);

               $body = $REQUEST->getBody();

if ($this->streamTrace) {
     if (strlen($body) > $this->imageThreshold) { 
          return null;
     }
}

               $links = null;
//
// check for MIME 
//
               if ($REQUEST->headerExists('MIME-VERSION')) {
if ($this->transportTrace) {
$this->trace('FOUND a HTTP MIME-VERSION header');
}
               } else {
if ($this->transportTrace) {
$this->trace('MISSING a MIME-VERSION header (Not RETS Compliant, but compensating)');
}
               }

//
// check for multipart 
//
               $header = $REQUEST->getHeader('CONTENT-TYPE');
               $MULTIPART_RESPONSE = new MultipartResponse();
               $MULTIPART_RESPONSE->setImageThreshold($this->imageThreshold);
               if ($MULTIPART_RESPONSE->isMultipart($header)) {
if ($this->transportTrace) {
$this->trace('FOUND correct CONTENT-TYPE header');
}
                    $place = 0;
                    $parametersInMultipart = true;
                    $contents = $MULTIPART_RESPONSE->parseBody($body);
                    foreach ($contents as $key => $value) {
//
// content id
//
                         $contentID = $MULTIPART_RESPONSE->getHeader($key, 'CONTENT-ID');
                         if ($contentID == null) {
                              $contentID = $MEDIA_REQUEST->getListingNumber();
                              $parametersInMultipart = false;
                         }

//
// object id
//
                         $objectID = $MULTIPART_RESPONSE->getHeader($key, 'OBJECT-ID');
                         if ($objectID == null) {
                              $objectID = $place;
                              $parametersInMultipart = false;
                         }

                         $location = $MULTIPART_RESPONSE->getHeader($key, 'LOCATION');
                         if ($location != null) {
                              $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($contentID, $objectID),
                                                            $location,
                                                            $MULTIPART_RESPONSE->getHeader($key, 'CONTENT-DESCRIPTION'));
                              ++$place;
                         }
                    }
                    if (!$parametersInMultipart) {
if ($this->transportTrace) {
$this->trace('MISSING parameters within multipart GetObject reponse (report this)');
}
                    }
               } else {
if ($this->transportTrace) {
$this->trace('FOUND incorrect CONTENT-TYPE header, may not support Multipart');
}

//
// if content and object are not passed by the server, use request values
//
                    $contentID = $REQUEST->getHeader('CONTENT-ID');
                    if ($contentID == null) {
                         $contentID = $MEDIA_REQUEST->getListingNumber();
                    } 

                    $objectID = $REQUEST->getHeader('OBJECT-ID');
                    if ($objectID == null) {  
                         $objectID = $MEDIA_REQUEST->getObjectID();
                    }
                         
                    $location = $REQUEST->getHeader('LOCATION');
                    if ($location != null) {
                         $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($contentID, $objectID),
                                                       $location,
                                                       $REQUEST->getHeader('CONTENT-DESCRIPTION'));
                    }
               }

//
// make sure the images are in order numerically
// only leave images that are under the max number
//
               if ($links == null) {
                    return null;
               }
               $tempIndex = null;
               foreach ($links as $key => $aContainer) {
                    $tempIndex[] = $aContainer->getName();
               }
               asort($tempIndex);
               $nLinks = null;
               $place = 0;
               foreach ($tempIndex as $key => $aValue) {
                    if ($place < $max_images) {
	                 $nLinks[] = $links[$key];
                         ++$place;
                    }
               }

               return $nLinks;
          }

//----------------------------
//
// The idea here is to look for individual images, test their size and
// capture the URL.
//
          if (!$MEDIA_REQUEST->getMultipart() &&  
              $MEDIA_REQUEST->getLocation()) {
if ($this->transportTrace) {
$this->trace('MISSING Multipart option support');
}
               $links = null;
               $object_entity = $MEDIA_REQUEST->getListingNumber();
               $object_id = $MEDIA_REQUEST->getObjectID();
               if ($object_id == '*') {
                    for ($i = 1; $i <= $max_images; $i++) {
                         $mediaContainer = $this->returnMediaReference($MEDIA_REQUEST,
                                                                       $object_entity,
                                                                       $i);
                         if ($mediaContainer != null) {
                              $links[] = $mediaContainer; 
                         } else {
if ($this->transportTrace) {
$this->trace('MISSING image in position ' . $i . ', stopping search');
}
                              break;
                         }
                    }
               } else {
//
// generate  a single media object 
//
                    $mediaContainer = $this->returnMediaReference($MEDIA_REQUEST,
                                                                  $object_entity,
                                                                  $object_id);
                    if ($mediaContainer != null) {
                         $links[] = $mediaContainer;
                    }
               }

               return $links;
          }

//
// servers where location is not supported 
// produces dummy results
//
          $links = null;
          $object_entity = $MEDIA_REQUEST->getListingNumber();
          $object_id = $MEDIA_REQUEST->getObjectID();
          if ($MEDIA_REQUEST->getMultipart() &&  
              !$MEDIA_REQUEST->getLocation()) {
               if ($object_id == '*') {
                    $images = $this->returnMediaObjects($MEDIA_REQUEST->getResource(),
                                                        $MEDIA_REQUEST->getMediaType(),
                                                        $object_entity);
                    foreach ($images as $key => $anImage) {
                         $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($object_entity, 
                                                                                  $key),
                                                       'DIRECT_URL',
                                                       'DIRECT_DESC');
                    }
               } else {
                    $anImage = $this->returnMediaObject($MEDIA_REQUEST->getResource(),
                                                        $MEDIA_REQUEST->getMediaType(),
                                                        $object_entity,
                                                        $object_id);
                    if ($anImage != null) {
                         $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($object_entity, 
                                                                                  $object_id),
                                                       'DIRECT_URL',
                                                       'DIRECT_DESC');
                    }
               }
          } else {
               if ($object_id == '*') {
                    for ($i = 1; $i <= $max_images; $i++) {
                         $anImage = $this->returnMediaObject($MEDIA_REQUEST->getResource(),
                                                             $MEDIA_REQUEST->getMediaType(),
                                                             $object_entity,
                                                             $i);
                         if ($anImage != null) {
                              $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($object_entity, 
                                                                                       $i),
                                                            'DIRECT_URL',
                                                            'DIRECT_DESC');
                         }
                    }
               } else {
                    $anImage = $this->returnMediaObject($MEDIA_REQUEST->getResource(),
                                                        $MEDIA_REQUEST->getMediaType(),
                                                        $object_entity,
                                                        $object_id);
                    if ($anImage != null) {
                         $links[] = new MediaContainer($MEDIA_REQUEST->getMediaID($object_entity, 
                                                                                  $object_id),
                                                       'DIRECT_URL',
                                                       'DIRECT_DESC');
                    }
               }
          }

          return $links;
     }

     function returnMediaReference($MEDIA_REQUEST,
                                   $object_entity,
                                   $object_id) {
          $largs = 'Resource=' . $MEDIA_REQUEST->getResource() .
                   '&Type=' . $MEDIA_REQUEST->getMediaType() .
                   '&ID=' .  $MEDIA_REQUEST->getMediaID($object_entity, $object_id) .
                   '&Location=1';
          $this->args['GETOBJECT'][] = $largs;
if ($this->transportTrace)
{
          $display = urldecode($largs);
$this->trace('ARGS: ' . $display); 
}
          $REQUEST = $this->readURL('GETOBJECT', 
                                    $largs, 
                                    $this->postRequests, 
                                    true);

//
// content description
//
          $contentDescription = $REQUEST->getHeader('CONTENT-DESCRIPTION');

//
// if location is contained in the header, use it
//
          $imageURL = $REQUEST->getHeader('LOCATION');
          if ($imageURL == null) {
//
// non-compliant with RETS, but read anyway 
//
               $buffer = $REQUEST->getBody();
               $pos = strpos($buffer, 'http');
               if ($pos > 0) {
                    $tempURL = substr($buffer, $pos, strlen($buffer));
                    $pos = strpos($tempURL, '</RETS>');
                    $imageURL = substr($tempURL, 0, $pos - 1);
                    $imageURL = trim($imageURL);
                    $pos = strpos($imageURL, '--');
                    if ($pos > 0) {
                         $imageURL = substr($imageURL, 0, $pos - 1);
                         $imageURL = trim($imageURL);
                    }
               }
          }

//
// only store valid URLs
//
          if ($imageURL != null) {
               return new MediaContainer($object_id,
                                         $imageURL,
                                         $contentDescription);
          }

          return null;
     }

     function searchMediaDirect($MEDIA_REQUEST,
                                $max_images = null) {
          $this->stampEvent('PRE_GETOBJECT');
          $result = $this->searchMedia_priv($MEDIA_REQUEST,
                                            $max_images);
          $this->stampEvent('POST_GETOBJECT');

          return $result;
     }

     function returnMediaObject($resource,
                          $type,
                          $listing_id,
                          $image_id) {
          $object_id = $listing_id . ':' . $image_id;

if ($this->streamTrace) {
$this->trace('DIRECT: Resource: ' . $resource . ' Type: ' . $type . ' ID: ' .$object_id);
}
          $largs = 'Resource=' . $resource .
                   '&Type=' . $type .
                   '&ID=' . $object_id;

//
// contact server
//
          $REQUEST = $this->readURL('GETOBJECT', $largs, $this->postRequests, true);
          $result = $REQUEST->getBody();

//
// if result is too small, it is probably an error
//
          if ($result != null) {
if ($this->streamTrace) {
$image_size = strlen($result);
$this->trace('SERVER returned ' . $image_size . ' bytes');
}
               if (strlen($result) <= $this->imageThreshold) {
                    $result = null;
               }
          }

//
// handle result
//
          if ($result == null) {
if ($this->streamTrace) {
$this->trace('MISSING image on server (DIRECT)');
}
if ($this->streamTrace) {
$this->trace('RETURNING null (DIRECT)');
} else {
               return null;
}
          }

if ($this->streamTrace) {
$this->trace('MISSING image on server (DIRECT)');
$this->trace($result);
} else {
          return $REQUEST->getBody();
}
     }

     function returnMediaObjects($resource,
                                 $type,
                                 $listing_id) {
if ($this->streamTrace) {
$this->trace('DIRECT-MULTIPART: Resource: ' . $resource . ' Type: ' . $type . ' ID: ' . $listing_id);
}
          $largs = 'Resource=' . $resource .
                   '&Type=' . $type .
                   '&ID=' . $listing_id . ':*' .
                   '&Location=0';

//
// contact server
//
          $this->args['GETOBJECT'][] = $largs;
          $REQUEST = $this->readURL('GETOBJECT', $largs, $this->postRequests, true);

          $body = $REQUEST->getBody();

//
// check for MIME 
//
          $mime = false;
          if ($REQUEST->headerExists('MIME-VERSION')) {
if ($this->transportTrace) {
$this->trace('FOUND a HTTP MIME-VERSION header');
}
               $mime = true;
          } else {
if ($this->transportTrace) {
$this->trace('MISSING a MIME-VERSION header (Not RETS Compliant, but compensating)');
}
               $mime = true;
          }

          $images = null;
          if($mime) {
//
// check for multipart 
//
               $header = $REQUEST->getHeader('CONTENT-TYPE');
               $MULTIPART_RESPONSE = new MultipartResponse();
               if ($MULTIPART_RESPONSE->isMultipart($header)) {
if ($this->transportTrace) {
$this->trace('FOUND correct CONTENT-TYPE header');
}
                    $contents = $MULTIPART_RESPONSE->parseBody($body);
                    $images = $MULTIPART_RESPONSE->getImages();
               } else {
                    if (strlen($body) > $this->imageThreshold) {
if ($this->streamTrace) {
$this->trace('MISSING container, single image found (DIRECT-MULTIPART)');
}
                         $images[] = trim($body);
                    }
               }
          }

          return $images;
     }

     function resources($standardNames) {
          return $this->getResourceMetadataNames($standardNames);
     }

     function resourceNames($standardNames) {
          return $this->getResourceMetadataNames($standardNames,
                                                 true);
     }

     function classes($resource,
                      $standardNames) {
          return $this->getClassMetadataNames($resource,
                                              $standardNames);
     }

     function classNames($resource,
                         $standardNames) {
          return $this->getClassMetadataNames($resource,
                                              $standardNames,
                                              true);
     }

     function objects($resource) {
          $result = $this->getObjectMetadata($resource);

//
// use rets_lite/xml.php 
//
          $translationParser = new TranslationParser();
          $table = $translationParser->parse($result,
                                             'ObjectType',
                                             'MIMEType',
                                             'METADATA-OBJECT');
          if ($table == null) {
               return null;
          }

//
// create a return array
//
          $field_array = null;
          foreach ($table as $key => $value) {
               $field_array[] = $key;
          }

          return $field_array;
     }

     function getMetadata($metadata_type,
                          $metadata_resource = null,
                          $metadata_class = null,
                          $metadata_update = null) {
          $metadata_container = null; 
          $largs = null;

          switch ($metadata_type) {
               case 'METADATA-SYSTEM': 
                    $largs = sprintf("Type=%s&ID=0", $metadata_type);
                    break;

               case 'METADATA-FOREIGNKEYS': 
                    $largs = sprintf("Type=%s&ID=0", $metadata_type);
                    break;

               case 'METADATA-RESOURCE': 
                    $largs = 'Type=' . $metadata_type . '&ID=0';
                    $metadata_container = $metadata_resource;
                    break;

               case 'METADATA-CLASS': 
                    $largs = 'Type=' . $metadata_type . '&ID=' . $metadata_resource;
                    break;

               case 'METADATA-TABLE': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . 
                             urlencode($metadata_resource . ':' . $metadata_class);
                    break;

               case 'METADATA-UPDATE': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . 
                             urlencode($metadata_resource . ':' . $metadata_class);
                    break;

               case 'METADATA-UPDATE_TYPE': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . 
                             urlencode($metadata_resource . ':' . $metadata_class . ':' . $metadata_update);
                    break;

               case 'METADATA-OBJECT': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . $metadata_resource;
                    break;

               case 'METADATA-LOOKUP': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . $metadata_resource;
                    break;

               case 'METADATA-LOOKUP_TYPE': 
                    $largs = 'Type=' . $metadata_type .  '&ID=' . 
                             urlencode($metadata_resource . ":" . $metadata_class);
                    break;

               case 'METADATA-SEARCH_HELP': 
                    $largs = 'Type=' . $metadata_type . '&ID=' . $metadata_resource;
                    break;

/*
          if (strcmp($metadata_type, "METADATA-EDITMASK") == 0) 
          {
               $largs = sprintf("Type=%s&ID=%s",
                                $metadata_type,
                                $metadata_resource);
               if (strcmp($metadata_resource, "0") != 0) 
               {
                    $metadata_map["RESOURCE"] = $metadata_resource;
               }
          }

          if (strcmp($metadata_type, "METADATA-UPDATE_HELP") == 0) 
          {
               $largs = sprintf("Type=%s&ID=%s",
                                $metadata_type,
                                $metadata_resource);
               if (strcmp($metadata_resource, "0") != 0) 
               {
                    $metadata_map["RESOURCE"] = $metadata_resource;
               }
          }

          if (strcmp($metadata_type, "METADATA-VALIDATION_LOOKUP") == 0) 
          {
               $largs = sprintf("Type=%s&ID=%s",
                                $metadata_type,
                                $metadata_resource);
               if (strcmp($metadata_resource, "0") != 0) 
               {
                    $metadata_map["RESOURCE"] = $metadata_resource;
               }
          }

          if (strcmp($metadata_type, "METADATA-VALIDATION_LOOKUP_TYPE") == 0) 
          {
               $tempID = $metadata_resource . ":" . $metadata_class;
               $largs = sprintf("Type=%s&ID=%s:%s",
                                $metadata_type,
                                urlencode($tempID));
               $metadata_map["RESOURCE"] = $metadata_resource;
               if (strcmp($metadata_class, "0") != 0) 
               {
                    $metadata_map["VALIDATIONLOOKUP"] = $metadata_class;
               }
          }

          if (strcmp($metadata_type, "METADATA-VALIDATION_EXPRESSION") == 0) 
          {
               $largs = sprintf("Type=%s&ID=%s",
                                $metadata_type,
                                $metadata_resource);
               if (strcmp($metadata_resource, "0") != 0) 
               {
                    $metadata_map["RESOURCE"] = $metadata_resource;
               }
          }

          if (strcmp($metadata_type, "METADATA-VALIDATION_EXTERNAL") == 0) 
          {
               $largs = sprintf("Type=%s&ID=%s",
                                $metadata_type,
                                $metadata_resource);
               if (strcmp($metadata_resource, "0") != 0) 
               {
                    $metadata_map["RESOURCE"] = $metadata_resource;
               }
          }

          if (strcmp($metadata_type, "METADATA-VALIDATION_EXTERNAL_TYPE") == 0) 
          {
               $tempID = $metadata_resource . ":" . $metadata_class;
               $largs = sprintf("Type=%s&ID=%s:%s",
                                $metadata_type,
                                $tempID);
               $metadata_map["RESOURCE"] = $metadata_resource;
               if (strcmp($metadata_class, "0") != 0) 
               {
                    $metadata_map["VALIDATIONEXTERNAL"] = $metadata_class;
               }
          }
*/
          }

//
//  Check if the passed arguments made sense
//
          if ($largs == null) {
               return 'The arguments related to your request do not make sense to me.';
          }

//
// add format
//
          $largs .= '&Format=COMPACT';

//
// contact server
//
          $this->args['GETMETADATA'] = $largs;
          $REQUEST = $this->readURL('GETMETADATA', 
                                    $largs,
                                    $this->postRequests,
                                    false,
                                    $metadata_container);

          return $REQUEST->getBody();
     }

     function serverLookupTypeMetadata($resource,
                                       $lookup) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-LOOKUP_TYPE',
                                       $resource,
                                       $lookup);

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverTableMetadata($resource,
                                  $class) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-TABLE',
                                       $resource,
                                       $class);

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverObjectMetadata($resource) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-OBJECT',
                                       $resource);

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverSearchHelpMetadata($resource) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-SEARCH_HELP',
                                       $resource);

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverLookupMetadata($resource) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-LOOKUP',
                                       $resource);

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverClassMetadata($resource) {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-CLASS',
                                       $resource);
          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function serverResourceMetadata() {
          $this->stampEvent('PRE_GETMETADATA');

          $result = $this->getMetadata('METADATA-RESOURCE');

          $this->stampEvent('POST_GETMETADATA');

          return $result;
     }

     function getTableMetadata($resource,
                               $class,
                               $standardNames) {
          if ($this->name != null) {
               $METADATA = new Metadata($this->name);
               if ($METADATA->exists()) {
//
// make sure the class is in systemName form
//
                    $METADATA_CLASS = new ClassMetadata($this->name, $resource);
                    $systemClass = $METADATA_CLASS->getSystemClass($class, $standardNames);

                    $METADATA = new TableMetadata($this->name, $systemClass);
                    if ($METADATA->exists()) {
                         return $METADATA->contentsAsString();
                    }
                    $result = $this->serverTableMetadata($resource, $systemClass);
                    $METADATA->updateContents($result);
                    return $result;
               }
          }

          return $this->serverTableMetadata($resource, $class);
     }

     function getLookupTypeMetadata($resource,
                                    $lookup) {
          if ($this->name != null) {
               $METADATA = new LookupTypeMetadata($this->name, $lookup);
               if ($METADATA->exists()) {
                    return $METADATA->contentsAsString();
               }
               $result = $this->serverLookupTypeMetadata($resource, $lookup);
               $METADATA->updateContents($result);
               return $result;
          }

          return $this->serverLookupTypeMetadata($resource, $lookup);
     }

     function getLookupMetadata($resource) {
          if ($this->name != null) {
               $METADATA = new LookupMetadata($this->name, $resource);
               if ($METADATA->exists()) {
                    return $METADATA->contentsAsString();
               }
               $result = $this->serverLookupMetadata($resource);
               $METADATA->updateContents($result);
               return $result;
          }

          return $this->serverLookupMetadata($resource);
     }

     function getObjectMetadata($resource) {
          if ($this->name != null)
          {
               $METADATA = new ObjectMetadata($this->name, $resource);
               if ($METADATA->exists()) {
                    return $METADATA->contentsAsString();
               }
               $result = $this->serverObjectMetadata($resource);
               $METADATA->updateContents($result);
               return $result;
          }

          return $this->serverObjectMetadata($resource);
     }

     function getClassMetadataNames($resource,
                                    $standardNames,
                                    $asAssociation = false) {
          if ($this->name == null) {
               return null;
          }
          $METADATA_CLASS = new ClassMetadata($this->name, $resource);
          if (!$METADATA_CLASS->exists()) {
               $METADATA_CLASS->updateContents($this->serverClassMetadata($resource));
          }
          return $METADATA_CLASS->findNames($standardNames, $asAssociation);
     }

     function getResourceMetadataNames($standardNames,
                                       $asAssociation = false) {
          if ($this->name == null) {
               return null;
          }
          $METADATA_RESOURCE = new ResourceMetadata($this->name);
          if (!$METADATA_RESOURCE->exists()) {
               $METADATA_RESOURCE->updateContents($this->serverResourceMetadata());
          }
          return $METADATA_RESOURCE->findNames($standardNames, $asAssociation);
     }

     function countAllQuery($resource,
                            $class,
                            $standardNames,
                            $max_rets_version,
                            $fieldName) {
//
// read metadata
//
          $METADATA_CLASS = new ClassMetadata($this->name, $resource);
          $METADATA_TABLE = new TableMetadata($this->name, $METADATA_CLASS->getSystemClass($class, $standardNames));
          $METADATA_TABLE->read();

          $count = 0;
          $lookupName = $METADATA_TABLE->findLookupName($fieldName, $standardNames);
          if ($lookupName != null) {
               $L_METADATA = new LookupTypeMetadata($this->name, $lookupName);
               if ($L_METADATA->exists()) {
//
// simulate .ANY. in 1.0 or 1.5 
//
                    $valueList = $L_METADATA->asArray();
                    foreach ($valueList as $key => $value) { 
                         ++$count;
                    }
               }
          }
          return $count;
     }

     function createAllQuery($resource,
                             $class,
                             $standardNames,
                             $max_rets_version,
                             $fieldName) {
//
// read metadata
//
          $METADATA_CLASS = new ClassMetadata($this->name, $resource);
          $METADATA_TABLE = new TableMetadata($this->name, $METADATA_CLASS->getSystemClass($class, $standardNames));
          $METADATA_TABLE->read();

          $lookupName = $METADATA_TABLE->findLookupName($fieldName, $standardNames);
          if ($lookupName != null) {
               $L_METADATA = new LookupTypeMetadata($this->name, $lookupName);
               if ($L_METADATA->exists()) {
//
// simulate .ANY. in 1.0 or 1.5 
//
                    $valueList = $L_METADATA->asArray();
                    $query = null;
                    foreach ($valueList as $key => $value) { 
                         $query .= $value . ',';
                    }
                    return '(' . $fieldName . '=|' . substr($query, 0, strlen($query) - 1) . ')';
               }
          }
          return null;
     }

     function createAnyQuery($resource,
                             $class,
                             $standardNames,
                             $max_rets_version,
                             $fieldName) {
//
// read metadata
//
          $METADATA_CLASS = new ClassMetadata($this->name, $resource);
          $METADATA_TABLE = new TableMetadata($this->name, $METADATA_CLASS->getSystemClass($class, $standardNames));
          $METADATA_TABLE->read();

          $lookupName = $METADATA_TABLE->findLookupName($fieldName, $standardNames);
          if ($lookupName != null) {
               $L_METADATA = new LookupTypeMetadata($this->name, $lookupName);
               if ($L_METADATA->exists()) {
//
// for servers that support 1.7 or 1.7.2 
//
                    return '(' . $fieldName . '=.ANY.)';
               }
          }
          return null;
     }

     function createNullQuery($resource,
                              $class,
                              $standardNames,
                              $max_rets_version,
                              $fieldName,
                              $nullQueryOption = null) {
//
// force a failure for testing purposes
//
          if ($nullQueryOption == 'FAIL') {
               return null;
          }

//
// if NULL queries are not supported (DIRECT method), return null 
//
          if ($nullQueryOption == 'DIRECT') {
               return null;
          }

//
// read metadata
//
          $METADATA_CLASS = new ClassMetadata($this->name, $resource);
          $METADATA_TABLE = new TableMetadata($this->name, $METADATA_CLASS->getSystemClass($class, $standardNames));
          $METADATA_TABLE->read();
          $lookupTypeList = $METADATA_TABLE->findDataLookupTypes($standardNames);
          $searchable = $METADATA_TABLE->findSearchableFields($standardNames);
//print_r($searchable);
//print('<br/>');

//
// use status field
//
          if ($nullQueryOption == 'LISTING_STATUS' ||
              $nullQueryOption == 'LISTING_STATUS_ANY' ) {
               $candidate = null;
               $nameList = $METADATA_TABLE->findNames($standardNames, true);
               if ($nameList != null) {
                    $candidateList = null;
                    foreach ($nameList as $key => $value) {
                         if (array_key_exists($key, $searchable)) {
                              if (strpos(strtoupper($value), 'STATU') === false) {
                              } else {
                                   $candidateList[] = $key;
                              }
                         }
                    }
                    if ($candidateList != null) {
                         if (sizeof($candidateList) > 1) {
                              foreach ($candidateList as $key => $kName) {
                                   if ($kName == 'STATUS') {
                                        $candidate = $kName;
                                        break;
                                   } 
                                   $check = $nameList[$kName];
//print('Candidate ' . $check . ' ' . $key . ' ' . $kName . '<br/>');
                                   if (strpos(strtoupper($check), 'LIS') === false) {
                                        if (strtoupper($check) == 'STATUS') {
                                             $candidate = $kName;
                                             break;
                                        }
                                   } else {
                                        $candidate = $kName;
                                        break;
                                   }
                              }
                              if ($candidate == null)
                              {
print('*** ERROR *** Could not use NULL_QUERY_STATUS, found ' . sizeof($candidateList) . ' matches for criteria.<br/>');
                              }
                         } else {
                              $candidate = $candidateList[0];
                         }
                    }
               }
               if ($candidate != null) {
                    if ($lookupTypeList != null) {
                         foreach ($lookupTypeList as $key => $lType) {
                              if ($key == $candidate) {
                                   if ($nullQueryOption == 'LISTING_STATUS') {
                                        return $this->createAllQuery($resource,
                                                                     $class,
                                                                     $standardNames,
                                                                     $max_rets_version,
                                                                     $candidate);
                                   } else {
                                        return $this->createAnyQuery($resource,
                                                                     $class,
                                                                     $standardNames,
                                                                     $max_rets_version,
                                                                     $candidate);
                                   }
                              }
                         }
print('*** ERROR *** No Lookup Metadata found for ' . $candidate . '<br/>');
                         return null;
                    }
               }
          }

//
// use required fields 
//
          if ($nullQueryOption == 'REQUIREDS' ||
              $nullQueryOption == 'REQUIREDS_ANY' ) {
               $queryOrder_1 = null;
               $queryOrder_2 = null;
               $queryMinCount_1 = null;
               $queryMinCount_2 = null;
               $queryItem_1 = null;
               $queryItem_2 = null;

               //
               // hack - create a list of fields that are known to be defined wrong
               //
               $blacklist = Array();
               $blacklist['17'] = true; // InterRealty

               $reqs = $METADATA_TABLE->findRequiredFields($standardNames);
               if ($reqs != null) {
                    foreach ($reqs as $key => $value) {
//print('req ' . $key . ' ' . $value . '<br/>');
                         if (!array_key_exists($key, $blacklist)) {
                              if (array_key_exists($key, $searchable)) {
                                   if (array_key_exists($key, $lookupTypeList)) {
                                        switch ($value) {
                                             case 1:
                                                  $queryOrder_1[] = $key;
                                                  $temp = $this->countAllQuery($resource,
                                                                               $class,
                                                                               $standardNames,
                                                                               $max_rets_version,
                                                                               $key);
                                                  if ($queryMinCount_1 == null) {
                                                       $queryMinCount_1 = $temp;
                                                       $queryItem_1 = $key;
                                                  } else {
                                                       if ($temp < $queryMinCount_1) {
                                                            $queryMinCount_1 = $temp;
                                                            $queryItem_1 = $key;
                                                      }
                                                  }
                                                  break;

                                             case 2:
                                                  $queryOrder_2[] = $key;
                                                  $temp = $this->countAllQuery($resource,
                                                                               $class,
                                                                               $standardNames,
                                                                               $max_rets_version,
                                                                               $key);
                                                  if ($queryMinCount_2 == null) {
                                                       $queryMinCount_2 = $temp;
                                                       $queryItem_2 = $key;
                                                  } else {
                                                       if ($temp < $queryMinCount_2) {
                                                            $queryMinCount_2 = $temp;
                                                            $queryItem_2 = $key;
                                                       }
                                                  }
                                                  break;

                                        }
                                   }
                              } else {
//print('*** ERROR *** Field ' . $key . ' defined as required, but is not searchable<br/>');
                              }
                         }
                    }
               }

               //
               // nothing defined by the MLS as required
               //
               if ($queryOrder_1 == null) {
                    return null;
               }

               $clause1 = null;
               $clause2 = null;
               $spacer = null;
               $prefix = null;
               $suffix = null;
               foreach ($queryOrder_1 as $key1 => $value1) {
                    if ($value1 == $queryItem_1) {
                         if ($nullQueryOption == 'REQUIREDS') {
                              $clause1 =  $this->createAllQuery($resource,
                                                                $class,
                                                                $standardNames,
                                                                $max_rets_version,
                                                                $value1);
                         } else {
                              $clause1 = $this->createAnyQuery($resource,
                                                               $class,
                                                               $standardNames,
                                                               $max_rets_version,
                                                               $value1);
                         }
                         if ($queryOrder_2 != null) {
                              $spacer = ',';
                              $prefix = '(';
                              $suffix = ')';
                              foreach ($queryOrder_2 as $key2 => $value2) {
                                   if ($value2 == $queryItem_2) {
                                        if ($nullQueryOption == 'REQUIREDS') {
                                             $clause2 =  $this->createAllQuery($resource,
                                                                               $class,
                                                                               $standardNames,
                                                                               $max_rets_version,
                                                                               $value2);
                                        } else {
                                             $clause2 = $this->createAnyQuery($resource,
                                                                              $class,
                                                                              $standardNames,
                                                                              $max_rets_version,
                                                                              $value2);
                                        }
                                   }
                              }
                         }
                    }
               }
               return $prefix . $clause1 . $spacer . $clause2 . $suffix;
          }

//
// use first Int field
//
          $typeList = $METADATA_TABLE->findDataTypes($standardNames);
          if ($nullQueryOption == 'FIRST_INTEGER' ) {
//               $items = $METADATA_TABLE->findQueryFields($standardNames);
//               foreach ($items as $key => $visibleName) {
               foreach ($searchable as $visibleName => $value) {
                    if ($METADATA_TABLE->findLookupType($visibleName, $standardNames) == null) {
                         if ($typeList[$visibleName] == 'Int') {
                              return '(' . $visibleName . '=0+)';
                         }
                    }
               }
          }

//
// for FlexMLS servers - hack for unique-id
//
          switch ($fieldName) {
               case 'LIST_1':
                    return '*';

               case 'MEMBER_0':
                    return '*';

               case 'OFFICE_0':
                    return '*';
          }

//
// if not displayable (InterRealty) use the first Int column 
//
          $displayable = false;
          $items = $METADATA_TABLE->findDisplayFields($standardNames);
          foreach ($items as $key => $value) {
               if ($value == $fieldName) {
                    $displayable = true;
               }
          }

          if (!$displayable) {
//               $items = $METADATA_TABLE->findQueryFields($standardNames);
//               if ($items != null) {
               if ($searchable != null) {
//                    foreach ($items as $key => $visibleName) {
                    foreach ($searchable as $visibleName => $value) {
                         if ($METADATA_TABLE->findLookupType($visibleName, $standardNames) == null) {
                              $aType = $typeList[$visibleName];
                              if ($aType == 'Int' ||
                                  $aType == 'Long' ||
                                  $aType == 'Tiny' ||
                                  $aType == 'Small') {
                                   return '(' . $visibleName . '=0+)';
                              }
                         }
                    }
               }
          }

//
// integer logic
//
          if ($typeList != null) {
               if (array_key_exists($fieldName, $typeList)) {
                    $aType = $typeList[$fieldName];
                    if ($aType == 'Int' ||
                        $aType == 'Tiny' ||
                        $aType == 'Small' ||
                        $aType == 'Long') {
                         return '(' . $fieldName . '=0+)';
                    }
               }
          }

//
// default return, string logic
//
          return '(' . $fieldName . '=a*)|' .
                 '(' . $fieldName . '=*a*)|' .
                 '(' . $fieldName . '=0*)|' .
                 '(' . $fieldName . '=*0*)';
     }

     function logoutDirect() {
          $this->stampEvent('PRE_LOGOUT');

          $REQUEST = $this->readURL('LOGOUT', null, $this->postRequests);
          $buffer = $REQUEST->getBody();
          if (strlen($buffer) > 0) {
               $this->registerVariables($buffer, $REQUEST);

//
// prepare message for return 
//
               global $SIGNOFFMESSAGE;
               if ($SIGNOFFMESSAGE != null) {
                    $this->member->setSignOff($SIGNOFFMESSAGE);
               }
          }

//echo "<XMP>CLEAR SESSION**********</XMP>";
          $REQUEST->clearRetsSession();
          if ($this->transportTrace) {
               $this->trace('CLOSING socket for ' . get_class($this) . ' due to RETS Logout');
          }
          if ($this->socket) {
               fclose($this->socket);
               $this->socket = false;
          }
          $this->cookies = null;

          $this->stampEvent('POST_LOGOUT');
          if ($this->member->getSignoff() == null) {
               return false;
          }

          return true;
     }

     function deriveRemoteAddress($buffer) {
          if (array_key_exists('host', $buffer)) {
               return $buffer['host'];
          }

          return $_SERVER['HTTP_HOST'];
     }

     function deriveRemotePort($buffer) {
          if (array_key_exists('port', $buffer)) {
               if (strlen($buffer['port']) > 0) {
                    return $buffer['port'];
               }
          }

          return DEFAULT_PORT;
     }

     function deriveCommand($buffer) {
          $rets_command = $buffer['path'];
          $pos = strpos($rets_command, '/');
          if ($pos === false) {
          } else {
               if ($pos == 0) {
                    $rets_command = substr($rets_command,
                                           $pos + 1,
                                           strlen($rets_command));
               }
          }

          return $rets_command;
     }

}

?>
