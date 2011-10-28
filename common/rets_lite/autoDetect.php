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

set_time_limit(0);

define('AUTO_DETECT_REFRESH_METADATA', true);
define('AUTO_DETECT_SECOND_SAMPLE_SIZE', 5);
define('AUTO_DETECT_REFERENCE_SAMPLE_SIZE', 100);

class AbstractStylist {

     function flush_to_browser() {
          for($i = 0; $i < 40000; $i++) {
               echo ' ';
          }
          flush();
     }

     function displayText($text) {
          echo $this->formatText($text);
          $this->flush_to_browser();
     }

     function formatText($message) {
          return '<font style="' .
                 'vertical-align:middle;' . 
                 'font-family:' . FONT_FACE . ';' .
                 'font-size:' . DATA_POINT_SIZE . ';' .
//                 'color:' . FONT_COLOR . ';' .
//                 'color:' . PROJECT_FONT_COLOR . ';' .
                 'color:blue;' .
                 '">' .
                 $message . 
                 '</font>';
     }
}

class TextStylist
     extends AbstractStylist {

     function displayMessage($text) {
          $this->displayText($text . '</br>');
     }
}

class NoteStylist 
     extends AbstractStylist {

     function displayMessage($text) {
          echo $this->formatText('.');
          $this->flush_to_browser();
     }
}

class AutoDetect
     extends AbstractRetsExchange {

     var $parms = null;
     var $metadataObservations = null;
     var $dataObservations = null;
     var $contextName = null;
     var $records = null;
     var $referenceRows = 0;
     var $workingText = null;
     var $probedResource = null;
     var $probedClass = null;
     var $probedStandardNames = false;
     var $systemNameOnly = false;
     var $EXCHANGE = null;
     var $SEARCH_REQUEST = null;

     function AutoDetect($contextName, 
                         $verbose) {
          $this->contextName = $contextName;
          if ($verbose) {
               $this->registerDisplayHandler(new TextStylist());
          } else {
               $this->registerDisplayHandler(new NoteStylist());
          }
     }

     function resetProbe() {
          $this->parms = null;
          $this->records = null;
          $this->rawRows = 0;
          $this->workingText = null;
          $this->probedResource = null;
          $this->probedClass = null;
          $this->probedStandardNames = false;
          $this->EXCHANGE = null;
          $this->SEARCH_REQUEST = null;
     }

     function getDetectedParameters() {
          return $this->parms;
     }

     function setParameter($name,
                           $value) {
          return $this->parms[$name] = $value;
     }

     function getMetadataObservations() {
          return $this->metadataObservations;
     }

     function addMetadataObservation($text) {
          $this->metadataObservations[] = $text;
     }

     function getDataObservations() {
          $this->performAnalysis();
          return $this->dataObservations;
     }

     function addDataObservation($text) {
          $this->dataObservations[] = $text;
     }

     function displayMessage($text) {
          $this->DISPLAY_HANDLER->displayMessage($text);
     }

     function displayText($text) {
          $this->DISPLAY_HANDLER->displayText($text . '</br>');
     }

     function setSystemNameOnly($value) {
          $this->systemNameOnly = $value;
     }

     function probe_metadata($account,
                    $password,
                    $url,
                    $application,
                    $version,
                    $client_password,
                    $resource,
                    $class,
                    $retsVersion,
                    $postRequests) {
     
          $this->EXCHANGE = new Exchange($this->contextName);
          $this->EXCHANGE->setTraceDevice($this->traceDevice);
          $this->EXCHANGE->setPayloadTrace($this->payloadTrace);
          $this->EXCHANGE->setStreamTrace($this->streamTrace);
          $this->EXCHANGE->setTransportTrace($this->transportTrace);

//
// display manager
//
          $this->EXCHANGE->registerDisplayHandler($this->DISPLAY_HANDLER);

          $this->parms['metadata_found'] = false;
//
// account
// password 
// url 
// user_agent 
// client_password 
// is_property_resource 
// 
          $result = $this->probe0($account,
                                  $password,
                                  $url,
                                  $application,
                                  $version,
                                  $client_password,
                                  $resource,
                                  $class,
                                  $retsVersion,
                                  $postRequests);
          if ($result) {
//
// default_rets_version
// maximum_rets_version
// standard_class 
// system_class 
// working_resource 
// working_class 
// standard_names
// 
               $result = $this->probe1();
$this->displayText('');
          }
          if ($result) {
//
// maximum_rets_version
//
               $result = $this->probe2($account,
                                       $password,
                                       $url,
                                       $application,
                                       $version,
                                       $client_password,
                                       $postRequests);
          }
          if ($result) 
          {
//
// non_display_count 
// non_search_count 
// unique_count 
// field_count 
// all_fields 
// all_types 
// all_interpretations 
// all_reqs 
// unique_key 
//
               $result = $this->probe3($account,
                                       $password,
                                       $url,
                                       $application,
                                       $version,
                                       $client_password,
                                       $postRequests);
          }
          if ($result) {
//
// display_count 
// search_count 
// search_fields 
//
               $result = $this->probe4();
          }

          if ($result) {
               $this->parms['metadata_found'] = true;
          }
     }

     function probe_data_direct($account,
                    $password,
                    $url,
                    $application,
                    $version,
                    $client_password,
                    $resource,
                    $class,
                    $rets_version,
                    $postRequests,
                    $unique_key,
                    $standard_names,
                    $known_listing) {
          $this->EXCHANGE = new Exchange($this->contextName);
          $this->EXCHANGE->setTraceDevice($this->traceDevice);
          $this->EXCHANGE->setPayloadTrace($this->payloadTrace);
          $this->EXCHANGE->setStreamTrace($this->streamTrace);
          $this->EXCHANGE->setTransportTrace($this->transportTrace);

//
// login
//
          $result = $this->EXCHANGE->loginDirect($account,
                                                 $password,
                                                 $url,
                                                 $rets_version,
                                                 $application,
                                                 $version,
                                                 $client_password,
                                                 $postRequests);
          if (!$result) {
$this->addMetadataObservation('Could not login, check name and password');
               return false;
          }

//
// setup up obvious parameters
//
          $this->parms['data_found'] = false;
          $this->parms['second_pass_direct_data'] = false;
          $this->parms['supports_null_query'] = false;

//
// setup passes parameters
//
          $this->parms['maximum_rets_version'] = $rets_version;
          $this->parms['working_resource'] = $resource;
          $this->parms['working_class'] = $class;
          $this->parms['unique_key'] = $unique_key;
          $this->parms['standard_names'] = $standard_names;
//
// determine if resource is "property"
//
          if (strpos(strtoupper($resource), 'PROPERTY') === false) {
               $this->parms['is_property_resource'] = false;
          } else {
               $this->parms['is_property_resource'] = true;
          }

//
// null_query_option
// reference_query 
// supports_null_query 
// data_found 
//
          $result = $this->probe5_direct($known_listing);
$this->displayText('');
          if ($result) {
//
// data_found 
// search_content_type 
// cursors 
// count_option 
// offset_option 
// pagination 
// offset_adjustment 
//
               $result = $this->probe6();
$this->displayText('');
          }
          if ($result) {
//
// compact_decoded_format 
//
              $result = $this->probe7();
         }
         if ($result) {
//
// media_type 
// image_support 
//
              $result = $this->probe8();
          }
          if ($result) {
//
// image_support 
// image_capabilities 
// known_record 
//
              $result = $this->probe9();
          }
          if ($result) {
//
// image_capabilities 
// media_multipart 
// media_location 
//
               $result = $this->probe10();
          }
//
// logout 
//
          $this->EXCHANGE->logoutDirect();
          $this->EXCHANGE->finish();

     }

     function probe_data() {
          $this->parms['data_found'] = false;
//
// null_query_option
// reference_query 
// supports_null_query 
// data_found 
//
          $result = $this->probe5();
$this->displayText('');
          if (!$this->parms['second_pass_direct_data']) {

               if ($result) {
//
// data_found 
// search_content_type 
// cursors 
// count_option 
// offset_option 
// pagination 
// offset_adjustment 
//
                    $result = $this->probe6();
$this->displayText('');
               }

               if ($result) {
//
// compact_decoded_format 
//
                    $result = $this->probe7();
               }
               if ($result) {
//
// media_type 
// image_support 
//
                    $result = $this->probe8();
               }
               if ($result) {
//
// image_support 
// image_capabilities 
// known_record 
//
                    $result = $this->probe9();
               }
               if ($result) {
//
// image_capabilities 
// media_multipart 
// media_location 
//
                    $result = $this->probe10();
               }
//
// logout 
//
               $this->EXCHANGE->logoutDirect();
               $this->EXCHANGE->finish();

          }
     }

     function probe0($account,
                     $password,
                     $url,
                     $application,
                     $version,
                     $client_password,
                     $resource,
                     $class,
                     $retsVersion,
                     $postRequests) {
$this->displayText('Check sanity of the given Resource and Class');

//
// reset the metadata 
//
          if (AUTO_DETECT_REFRESH_METADATA) {
               $METADATA = new METADATA($this->contextName);
               $METADATA->remove();
          }
          $result = $this->EXCHANGE->loginDirect($account,
                                                 $password,
                                                 $url,
                                                 $retsVersion,
                                                 $application,
                                                 $version,
                                                 $client_password,
                                                 $postRequests);
          if (!$result) {
$this->addMetadataObservation('Could not login, check name and password');
               return false;
          }

//
// list of resource SystemNames and StandardNames
//
          $resourceContext = null;

          $list = $this->EXCHANGE->resources(false);
          $resourceContext['systemNames'] = $list;

          $list = $this->EXCHANGE->resources(true);
          $resourceContext['standardNames'] = $list;

//
// determine if the given resource is systemName or StandardName
//
          $resourceList = null;
          $isSystemContext = false;
          $isStandardContext = false;
          foreach ($resourceContext as $contextKey => $list) {
               foreach ($list as $key => $value) {
                    $resourceList[$value] = true;
                    if ($value == $resource) {
                         if ($contextKey == 'systemNames') {
                              $isSystemContext = true;
                         } else {
                              $isStandardContext = true;
                         }
                    }
               }
          }
          if (!$isSystemContext && !$isStandardContext) {
$this->displayMessage('Could not find the resource ' . $resource);
$this->displayMessage('');
$this->displayMessage('LIST OF RESOURCES FOUND');
$this->displayMessage('');
               foreach ($resourceList as $key => $value) {
$this->displayMessage($key);
               }
$this->displayMessage('');
               return false;
          }

//
// construct list of classes by context
//
          $classContext = null;
          if ($isSystemContext) {
               foreach ($resourceContext['systemNames'] as $key => $value) {
                    if ($value == $resource) {
                         $classContext['systemNames'] = $this->EXCHANGE->classes($value, false);
                    }
               }
          }
          if ($isStandardContext) {
               foreach ($resourceContext['standardNames'] as $key => $value) {
                    if ($value == $resource) {
                         $classContext['standardNames'] = $this->EXCHANGE->classes($value, true);
                    }
               }
          }

//
// try to match the class name
//
          $classList = null;
          $classContextType = null;
          foreach ($classContext as $contextType => $list) {
               foreach ($list as $key => $value) {
                    $classList[$value] = true;
                    if ($value == $class) {
                         $classContextType = $contextType;
                    }
               }
          }
          if ($classContextType == null) {
$this->displayMessage('Could not find the class ' . $class . ' for resource ' . $resource);
$this->displayMessage('');
$this->displayMessage('LIST OF CLASSES FOUND');
$this->displayMessage('');
               foreach ($classList as $key => $value) {
$this->displayMessage($key);
               }
$this->displayMessage('');
               return false;
          }

//
// capture probed resource and class
//
          $this->probedResource = $resource;
          $this->probedClass = $class;
          if ($classContextType == 'systemNames') {
               $this->probedStandardNames = false;
          } else {
               $this->probedStandardNames = true;
          }

//
// determine if resource is "property"
//
          if (strpos(strtoupper($this->probedResource), 'PROPERTY') === false) {
               $this->parms['is_property_resource'] = false;
$this->addMetadataObservation('"' . $this->probedResource . '" is NOT a property resource');
          } else {
               $this->parms['is_property_resource'] = true;
$this->addMetadataObservation('"' . $this->probedResource . '" is a property resource');
          }
$this->addMetadataObservation('Using Class "' . $this->probedClass . '"');

//
// capture the state
//
          $this->parms['account'] = $account;
          $this->parms['password'] = $password;
          $this->parms['url'] = $url;
          $this->parms['user_agent'] = $this->EXCHANGE->getUserAgent();
          $this->parms['client_password'] = $client_password;

          return true;
     }     

     function probe1() {
$this->displayText('Refresh METADATA');
          if (AUTO_DETECT_REFRESH_METADATA) {
               $this->EXCHANGE->refreshMetadata($this->contextName,
                                                $this->probedResource,
                                                $this->probedClass,
                                                $this->probedStandardNames);
          }

//
// set default rets version 
//
          $this->parms['default_rets_version'] = $this->EXCHANGE->getRetsVersion();
$this->addMetadataObservation('Server supports RETS version ' . $this->parms['default_rets_version']);

//
// check if STANDARD_NAMES are available 
//
          $METADATA_CLASS = new ClassMetadata($this->contextName, 
                                              $this->probedResource);
          $standardClass = $METADATA_CLASS->getStandardClass($this->probedClass,
                                                             $this->probedStandardNames);
          $this->parms['standard_class'] = $standardClass;
          $systemClass = $METADATA_CLASS->getSystemClass($this->probedClass,
                                                         $this->probedStandardNames);
          $this->parms['system_class'] = $systemClass;

          if ($this->systemNameOnly) {
               $snames = false;
$this->addMetadataObservation('Package DEFINE prevented looking for StandardNames');
          } else {
               $METADATA_TABLE = new TableMetadata($this->contextName, 
                                                   $systemClass);

/*
//
// determine the number of system name fields 
//
               $system_fields = sizeof($METADATA_TABLE->findNames(false, true));

//
// determine the number of standard name fields 
//
               $standard_fields = sizeof($METADATA_TABLE->findNames(true, true));

//
// threshold logic
//
               if ($standard_fields < ($system_fields * .5))
               {
                    $snames = false;
$this->displayText(' - Less than half of the fields have StandardNames');
               }
               else
               {
                    $snames = true;
               }
*/
               $translations = $METADATA_TABLE->findTranslations();
               $check = true;
               foreach ($translations as $key => $value) {
                    if ($value == null) {
                         $check = false;
                    }
               }
               if ($check) {
                    $snames = true;
               } else {
                    $snames = false;
$this->addMetadataObservation('Not all fields have StandardNames');
               }
          }
//$snames = false;

          if ($snames) {
               $this->parms['working_class'] = $standardClass;
          } else {
               $this->parms['working_class'] = $systemClass;
          }

          $this->parms['working_resource'] = $this->probedResource;
          $this->parms['standard_names'] = $snames;

//
// finished with server
//
          $this->EXCHANGE->logoutDirect();
          $this->EXCHANGE->finish();
          return true;
     }     

     function probe2($account,
                     $password,
                     $url,
                     $application,
                     $version,
                     $client_password,
                     $postRequests) {
$this->displayText('Determine RETS version');
          $max_rets_version = $this->parms['default_rets_version'];
          $trials[] = '1.0';
          $trials[] = '1.5';
          $trials[] = '1.7';
          $trials[] = '1.7.2';
          $try = false;
          foreach ($trials as $key => $value) {
               if ($value == $this->parms['default_rets_version']) {
                    $try = true;
               } else {
                    if ($try) {
                         $result = $this->EXCHANGE->loginDirect($account,
                                                                $password,
                                                                $url,
                                                                $value,
                                                                $application,
                                                                $version,
                                                                $client_password,
                                                                $postRequests);
                         if ($result) {
                              $result = $this->EXCHANGE->getRetsVersion();
                              if ($result == $value) {
                                   $max_rets_version = $result;
                              }
                              $this->EXCHANGE->logoutDirect();
                              $this->EXCHANGE->finish();
                         } else {
                              $try = false;
                         }
                    }
               }
          }
          if ($max_rets_version == '1.0') {
$this->addMetadataObservation('As of April 2005 RETS Version 1.0 is not "RETS Compliant" (report this)');
          }
          $this->parms['maximum_rets_version'] = $max_rets_version;
$this->addMetadataObservation('Maximum RETS version ' . $this->parms['maximum_rets_version']);

          return true;
     }

     function probe3($account,
                     $password,
                     $url,
                     $application,
                     $version,
                     $client_password,
                     $postRequests) {
$this->displayText('Find unique key');
//
// determine unique element 
//
          $unique_key = null;
          $result = $this->EXCHANGE->loginDirect($account,
                                                 $password,
                                                 $url,
                                                 $this->parms['maximum_rets_version'],
                                                 $application,
                                                 $version,
                                                 $client_password,
                                                 $postRequests);
          if (!$result) {
$this->addMetadataObservation('Could not login, check name and password');
               return false;
          }

          $METADATA_TABLE = new TableMetadata($this->contextName, 
                                              $this->parms['system_class']);
          $nd_list = $METADATA_TABLE->findDisplayFields($this->parms['standard_names'],
                                                        true);
          $this->parms['non_display_count'] = sizeof($nd_list);

          $ns_list = $METADATA_TABLE->findQueryFields($this->parms['standard_names'],
                                                      true);
          $this->parms['non_search_count'] = sizeof($ns_list);

          $un_list = $METADATA_TABLE->findUniqueFields($this->parms['standard_names']);
          if ($un_list == null) {
$this->addMetadataObservation('No UNIQUE columns in METADATA-TABLE (report this)');
          }
          $this->parms['unique_count'] = sizeof($un_list);

          $el_list = $METADATA_TABLE->findNames($this->parms['standard_names']);
          $this->parms['field_count'] = sizeof($el_list);
          $this->parms['all_fields'] = implode(',', $el_list);
//
// ADDED 1.1.8
//

          $types = $METADATA_TABLE->findDataTypes($this->parms['standard_names']);
          $aList = null;
          foreach ($el_list as $key => $value) {
               if (array_key_exists($value, $types)) {
                    $aList[] = $types[$value];
               } else {
$this->addMetadataObservation('No DataType in METADATA-TABLE for '. $value . ' (report this)');
                    $aList[] = 'NULL';
               }
          } 
          $this->parms['all_types'] = implode(',', $aList);

          $interpretations = $METADATA_TABLE->findInterpretations($this->parms['standard_names']);
          $aList = null;
          foreach ($el_list as $key => $value) {
               if ($interpretations == null) {
                    $aList[] = 'NULL';
               } else {
                    if (array_key_exists($value, $interpretations)) {
                         $aList[] = $interpretations[$value];
                    } else {
                         $aList[] = 'NULL';
                    }
               }
          } 
//          $this->parms['all_interpretations'] = implode(',', $METADATA_TABLE->findInterpretations($this->parms['standard_names']));
          $this->parms['all_interpretations'] = implode(',', $aList);

          $aList = null;
          $reqs = $METADATA_TABLE->findRequiredFields($this->parms['standard_names']);
          if ($reqs == null) {
               foreach ($el_list as $key => $value) {
                    $aList[] = 'NULL';
               } 
          } else {
               foreach ($el_list as $key => $value) {
                    if (array_key_exists($value, $reqs)) {
                         $aList[] = $reqs[$value];
                    } else {
                         $aList[] = 'NULL';
                    }
               } 
          }
          $this->parms['all_reqs'] = implode(',', $aList);

          $keyOK = false;
          $candidate = $this->lookupKeyField();
          if ($candidate != null) {
               $keyOK = true;
               $unique_key = $candidate;

//
// check if the key is displayable and can be queried
//
               $defined = $this->keyDefined($candidate,
                                            $nd_list,
                                            $ns_list,
                                            $un_list);

               if (!$defined) {
$this->addMetadataObservation($this->workingText . ' (report this)');
$this->addMetadataObservation('Unique Text Element: [' .  $unique_key .  '] set from METADATA-RESOURCE');
               }

//
// check system name
//
               if ($this->parms['standard_names']) {
                    $st_list = $METADATA_TABLE->findTranslations();
                    foreach ($st_list as $key => $value) {
                         if ($value == $unique_key) {
                              $systemName = $key;
                         }
                    }
               } else {
                    $systemName = $unique_key;
               }
//
// for FlexMLS servers - hack for unique-id
//
               $useSystemName = false;
               switch ($systemName) {
                    case 'LIST_1':
                         $useSystemName = true;
                         break;

                    case 'MEMBER_0':
                         $useSystemName = true;
                         break;
 
                    case 'OFFICE_0':
                         $useSystemName = true;
                         break;

               }
               if ($useSystemName) {
//
// change to system names 
//
$this->addMetadataObservation('FlexMLS - look for {KeyField} without StandardNames');
                    $this->parms['standard_names'] = false;
                    $this->parms['working_class'] = $this->parms['system_class'];
                    $nd_list = $METADATA_TABLE->findDisplayFields($this->parms['standard_names'],
                                                                  true);
                    $this->parms['non_display_count'] = sizeof($nd_list);
                    $ns_list = $METADATA_TABLE->findQueryFields($this->parms['standard_names'],
                                                                true);
                    $this->parms['non_search_count'] = sizeof($ns_list);
                    $un_list = $METADATA_TABLE->findUniqueFields($this->parms['standard_names']);
                    if ($un_list == null) {
$this->addMetadataObservation('No UNIQUE columns in METADATA-TABLE for SystemNames either (report this)');
                    }
                    $this->parms['unique_count'] = sizeof($un_list);
                    $el_list = $METADATA_TABLE->findNames($this->parms['standard_names']);
                    $this->parms['field_count'] = sizeof($el_list);
                    $this->parms['all_fields'] = implode(',', $el_list);
//
// ADDED 1.1.8
//
                    $this->parms['all_types'] = implode(',',$METADATA_TABLE->findDataTypes($this->parms['standard_names']));
                    $unique_key = $systemName;
               }

          } else {

//
// start grasping a straws
//
               if ($un_list == null) {

                    if ($this->parms['standard_names']) {
//
// see if keyField can be determined with systemNames
//
$this->addMetadataObservation('Looking for {KeyField} without StandardNames');
                         $this->parms['standard_names'] = false;
                         $this->parms['working_class'] = $this->parms['system_class'];
                         $nd_list = $METADATA_TABLE->findDisplayFields($this->parms['standard_names'],
                                                                       true);
                         $this->parms['non_display_count'] = sizeof($nd_list);
                         $ns_list = $METADATA_TABLE->findQueryFields($this->parms['standard_names'],
                                                                     true);
                         $this->parms['non_search_count'] = sizeof($ns_list);
                         $un_list = $METADATA_TABLE->findUniqueFields($this->parms['standard_names']);
                         if ($un_list == null) {
$this->addMetadataObservation('No UNIQUE columns in METADATA-TABLE for SystemNames either (report this)');
                         }
                         $this->parms['unique_count'] = sizeof($un_list);
                         $el_list = $METADATA_TABLE->findNames($this->parms['standard_names']);
                         $this->parms['field_count'] = sizeof($el_list);
                         $this->parms['all_fields'] = implode(',', $el_list);
//
// ADDED 1.1.8
//
                         $this->parms['all_types'] = implode(',',$METADATA_TABLE->findDataTypes($this->parms['standard_names']));
                         $candidate = $this->lookupKeyField();
                         if ($candidate != null) {
                              $keyOK = true;
                              $unique_key = $candidate;

//
// check if the key is displayable and can be queried
//
                              $defined = $this->keyDefined($candidate,
                                                           $nd_list,
                                                           $ns_list,
                                                           $un_list);
                              if (!$defined) {
$this->addMetadataObservation($this->workingText . ' (report this)');
$this->addMetadataObservation('Unique Text Element: [' .  $unique_key .  '] set from METADATA-RESOURCE without StandardNames');
                              }
                         }

                    }

                    if (!$keyOK) {
$this->addMetadataObservation('[KeyField] not defined correctly in METADATA-RESOURCE (report this)');
                    }
               } else {
$this->addMetadataObservation('[KeyField] not defined correctly in METADATA-RESOURCE (report this)');
//
// work around to bad vendor configuration 
//
                    if (sizeof($un_list) > 1) {
$this->addMetadataObservation('Looking for first unique element in METADATA-TABLE');
                    } else {
$this->addMetadataObservation('Found a unique element in METADATA-TABLE');
                    }

//
// use first unique element
//
                    $found = false;
                    foreach ($un_list as $key => $candidate) {
                         if (!$found) {
$this->addMetadataObservation('Examining [' . $candidate . '] as a unique element (METADATA-TABLE)');
                              $defined = $this->keyDefined($candidate,
                                                           $nd_list,
                                                           $ns_list,
                                                           $un_list);

                              if ($defined) {
                                   $unique_key = $candidate;
$this->addMetadataObservation('Work around to misconfiguration found');
$this->addMetadataObservation('Unique Text Element: [' .  $unique_key .  '] set from METADATA-RESOURCE');
                                   $found = true;
                                   $keyOK = true;
                              } else {
                                   $err_text .= $text;
                              }
                         }
                    }
               }
          }

//
// note major problem
//
          if (!$keyOK) {
               $this->EXCHANGE->logoutDirect();
               $this->EXCHANGE->finish();
$this->addMetadataObservation('Unique Text Element: Not Found (Report this)');
               return false;
          }
          $this->parms['unique_key'] = $unique_key;

          return true;
     }

     function probe4() {
$this->displayText('Find useful fields (search and/or display)');
          $METADATA_TABLE = new TableMetadata($this->contextName, 
                                              $this->parms['system_class']);
//
// create list of displayable columns
//
          $list = $METADATA_TABLE->findDisplayFields($this->parms['standard_names']);
          if ($list == null) {
$this->addMetadataObservation('No fields identified as displayable in METADATA-TABLE (report this)');
          }
          $this->parms['display_count'] = sizeof($list);

//
// create list of queryable columns
//
          $list = $METADATA_TABLE->findQueryFields($this->parms['standard_names']);
          if ($list == null) {
$this->addMetadataObservation('No fields identified as queryable in METADATA-TABLE (report this)');
               $this->parms['search_count'] = 0;
          } else {
               $this->parms['search_count'] = sizeof($list);
               $this->parms['search_fields'] = implode(',', $list);
          }

$this->addMetadataObservation($this->parms['display_count'] . ' fields are displayable');
$this->addMetadataObservation($this->parms['search_count'] . ' fields are searchable');

          return true;
     }

     function probe5_direct($knownListing) {
$this->displayText('Reading the listing [' . $knownListing . ']');

//
// create a request
//
          $this->SEARCH_REQUEST = new SearchRequest($this->parms['maximum_rets_version'],
                                                    $this->parms['working_resource'],
                                                    $this->parms['working_class'],
                                                    $this->parms['unique_key'],
                                                    $this->contextName);
//
// try the DIRECT method
//

          $nullQueryOption = 'DIRECT';
//$this->displayMessage('- trying ' . $nullQueryOption . ' methodology');
          $query = '(' . $this->parms['unique_key'] . '=' . $knownListing . ')';
          $this->parms['reference_query'] = $query;
          $this->SEARCH_REQUEST->setQueryCriteria($query, true);
          $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                               $this->parms['standard_names'],
                                                               1,
                                                               false);
          $rawRows = $SEARCH_RESPONSE->getRowCount();
          if ($rawRows != 0) {
$this->addDataObservation('REFERENCE collected with known listing [' . $knownListing);
               $this->parms['null_query_option'] = $nullQueryOption;
               return true;
          } else {
$this->displayMessage($SEARCH_RESPONSE->getError());
          }

//
// all attempts failed
//
          $this->parms['data_found'] = false;
$this->addDataObservation('REFERENCE could not be collected. Known listing [' . 
                          $knownListing . '] was not found.');

          return false;
     }

     function probe5() {
$this->displayText('Determine the NULL query methodology');
          $this->parms['second_pass_direct_data'] = false;

//
// create a request
//
          $this->SEARCH_REQUEST = new SearchRequest($this->parms['maximum_rets_version'],
                                                    $this->parms['working_resource'],
                                                    $this->parms['working_class'],
                                                    $this->parms['unique_key'],
                                                    $this->contextName);
          $this->SEARCH_REQUEST->setLimit(AUTO_DETECT_REFERENCE_SAMPLE_SIZE);

//
// determine the null query option 
//
//print($this->parms['maximum_rets_version']);
          $methodology = null;
          if ($this->parms['maximum_rets_version'] == '1.7' ||
              $this->parms['maximum_rets_version'] == '1.7.2') {
               $methodology[] = 'REQUIREDS_ANY';
          } else {
               $methodology[] = 'REQUIREDS';
          }
          $methodology[] = 'UNIQUE_IDENTIFIER';
          if ($this->parms['maximum_rets_version'] == '1.7' ||
              $this->parms['maximum_rets_version'] == '1.7.2') {
               $methodology[] = 'LISTING_STATUS_ANY';
          } else {
               $methodology[] = 'LISTING_STATUS';
          }
          $methodology[] = 'FIRST_INTEGER';
          $methodology[] = 'FAIL';
          foreach ($methodology as $key => $nullQueryOption) {
$this->displayMessage('- trying ' . $nullQueryOption . ' methodology');
               $query = $this->EXCHANGE->createNullQuery($this->parms['working_resource'],
                                                         $this->parms['working_class'],
                                                         $this->parms['standard_names'],
                                                         $this->parms['maximum_rets_version'],
                                                         $this->parms['unique_key'],
                                                         $nullQueryOption);
               if ($query != null) {
//print('- trying ' . $key . ' ' . $nullQueryOption . ' methodology ' . $query . '<br/>');
$this->displayMessage('- sending generated Query: ' . $query);
                    $this->parms['reference_query'] = $query;
                    $this->SEARCH_REQUEST->setQueryCriteria($query, true);
                    $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                                         $this->parms['standard_names'],
                                                                         1,
                                                                         false);
                    $rawRows = $SEARCH_RESPONSE->getRowCount();
                    if ($rawRows != 0) {
                         $this->parms['null_query_option'] = $nullQueryOption;
                         $this->parms['supports_null_query'] = true;
                         return true;
                    } else {
//print($SEARCH_RESPONSE->getError());
$this->displayMessage($SEARCH_RESPONSE->getError());
                    }
               }
          }

//
// note failure of NULL QUERY
//
          $this->parms['supports_null_query'] = false;
          $this->parms['second_pass_direct_data'] = true;
$this->displayMessage('REFERENCE could not be collected with NULL query options.');

/*
//
// collect a known listing and try the DIRECT method
//
//print('<script type="text/javascript">' .
//      'alert("enter a known listing")' .
//      '</script>');
$suppliedListing = 'localhost-125';

          $nullQueryOption = 'DIRECT';
$this->displayMessage('- trying ' . $nullQueryOption . ' methodology');
          $query = '(' . $this->parms['unique_key'] . '=' . $suppliedListing . ')';
$this->displayMessage('- sending generated Query: ' . $query);
          $this->parms['reference_query'] = $query;
          $this->SEARCH_REQUEST->setQueryCriteria($query, true);
          $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                               $this->parms['standard_names'],
                                                               1,
                                                               false);
          $rawRows = $SEARCH_RESPONSE->getRowCount();
          if ($rawRows != 0) {
               $this->parms['null_query_option'] = $nullQueryOption;
               return true;
          } else {
$this->displayMessage($SEARCH_RESPONSE->getError());
          }

//
// all attempts failed
//
          $this->parms['data_found'] = false;
$this->addDataObservation('REFERENCE could not be collected. Supplied listing [' . 
                          $suppliedListing . '] was not found.');
*/
          return false;
     }

     function probe6() {
$this->displayText('Determine support for pagination');
//
// determine query options from reference set 
//
          $this->SEARCH_REQUEST = new SearchRequest($this->parms['maximum_rets_version'],
                                                    $this->parms['working_resource'],
                                                    $this->parms['working_class'],
                                                    $this->parms['unique_key'],
                                                    $this->contextName);
          $this->SEARCH_REQUEST->setLimit(AUTO_DETECT_REFERENCE_SAMPLE_SIZE);
          $this->SEARCH_REQUEST->setQueryCriteria($this->parms['reference_query'], true);
          $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                               $this->parms['standard_names'],
                                                               1,
                                                               false);
          $this->referenceRows = $SEARCH_RESPONSE->getRowCount();
          if ($this->referenceRows == 0) {
               $this->parms['data_found'] = false;
$this->addDataObservation('REFERENCE could not be collected. No data on server.');
               return false;
          }

          $this->parms['data_found'] = true;
          $this->parms['search_content_type'] = $SEARCH_RESPONSE->getContentType();
          $firstRecord = null;
          $secondRecord = null;
          $thirdRecord = null;
          for ($i = 0; $i < $this->referenceRows; ++$i) {
               $row = $SEARCH_RESPONSE->getRow($i);
if ($i < AUTO_DETECT_SECOND_SAMPLE_SIZE)
{
$this->displayMessage(' - Record #' . $i . ': ' . $row[$this->parms['unique_key']]);
}
               $this->records[] = $row[$this->parms['unique_key']];
               if ($firstRecord == null) {
                    $firstRecord = $row[$this->parms['unique_key']];
               } else {
                    if ($secondRecord == null) {
                         $secondRecord = $row[$this->parms['unique_key']];
                    } else {
                         if ($thirdRecord == null) {
                              $thirdRecord = $row[$this->parms['unique_key']];
                         }
                    }
               }
          } 

//
// check if server supports the COUNT option
//
          $SEARCH_RESPONSE = $this->EXCHANGE->countDataDirect($this->SEARCH_REQUEST,
                                                              $this->parms['standard_names'],
                                                              false);
          $check = $SEARCH_RESPONSE->getQueryCount();
          if ($check == null) {
               $this->parms['cursors'] = false;
               $this->parms['count_option'] = false;
               $this->parms['offset_option'] = false;
               $this->parms['pagination'] = false;
               $this->parms['offset_adjustment'] = 0;
               return false;
          }
          $this->parms['count_option'] = true;

//
// check if server supports the OFFSET option
//
          if (!$this->parms['supports_null_query']) {
               $this->parms['cursors'] = false;
               $this->parms['offset_option'] = false;
               $this->parms['pagination'] = false;
               $this->parms['offset_adjustment'] = 0;
$this->addDataObservation('Support for OFFSET option cannot be determined because of DIRECT method');
               return true;
          }

//
// begin at row #2
//
$this->displayMessage('Begin at the second row');
          $this->SEARCH_REQUEST->setLimit(AUTO_DETECT_SECOND_SAMPLE_SIZE);
          $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                               $this->parms['standard_names'],
                                                               2,
                                                               false);
          $offsetRows = $SEARCH_RESPONSE->getRowCount();
          for ($i = 0; $i < $offsetRows; ++$i) {
               $row = $SEARCH_RESPONSE->getRow($i);
$this->displayMessage(' - Record #' .  $i .  ': ' .  $row[$this->parms['unique_key']]);
          }

          $row = $SEARCH_RESPONSE->getRow(0);
          $testRecord = $row[$this->parms['unique_key']];
          switch ($testRecord) {
               case $firstRecord:
                    $this->parms['cursors'] = true;
                    $this->parms['offset_option'] = false;
                    $this->parms['pagination'] = false;
                    $this->parms['offset_adjustment'] = 0;
                    break;

               case $secondRecord:
                    $this->parms['cursors'] = true;
                    $this->parms['offset_option'] = true;
                    $this->parms['pagination'] = true;
                    $this->parms['offset_adjustment'] = 0;
                    break;

               case $thirdRecord:
                    $this->parms['cursors'] = true;
                    $this->parms['offset_option'] = true;
                    $this->parms['pagination'] = true;
                    $this->parms['offset_adjustment'] = -1;
                    break;

               default:
                    $this->parms['cursors'] = false;
                    $this->parms['offset_option'] = false;
                    $this->parms['pagination'] = false;
                    $this->parms['offset_adjustment'] = 0;
          }

          return true;
     }

     function probe7() {
$this->displayText('Determine support for the COMPACT-DECODED format');
//
// confirm that COMPACT_DECODED_FORMAT is supported
//
          $this->SEARCH_REQUEST->setLimit(AUTO_DETECT_REFERENCE_SAMPLE_SIZE);
          $SEARCH_RESPONSE = $this->EXCHANGE->searchDataDirect($this->SEARCH_REQUEST,
                                                               $this->parms['standard_names'],
                                                               1,
                                                               true);
          $checkRows = $SEARCH_RESPONSE->getRowCount();
          if ($this->referenceRows == $checkRows) {
               $this->parms['compact_decoded_format'] = true;
          } else {
               $this->parms['compact_decoded_format'] = false;
          }

          return true;
     }

     function probe8() {
$this->displayText('Determine supported image types');

          if (!$this->parms['is_property_resource']) {
               $this->parms['image_support'] = false;
               return true;
          }

/*
$this->displayMessage('Determine defined image types -');
//
// look for media capabilities
// if not a standard type, default to first supported
//  
          $list = $this->EXCHANGE->objects($this->parms['working_resource']);
          $mediaType = null;
          $found = false;
          $looking = 'Photo';
          if ($list != null) {
               foreach ($list as $key => $value) {
                    if ($mediaType == null) {
                         $mediaType = $value;
                    }
                    if ($value == $looking) {
                         $mediaType = $value;
                         $found = true;
                    }
               }
          }
          if (!$found) {
               $this->parms['image_support'] = false;
$this->displayMessage('Image type in METADATA-OBJECT not defined. (report this)');
               return false;
          }
          $this->parms['media_type'] = $mediaType;
*/
          $this->parms['media_type'] = 'Photo';

          return true;
     }

     function probe9() {
          if (!$this->parms['is_property_resource']) {
               return true;
          }

$this->displayText('Find a listing with images');
//
// find a listing with images 
//
          $knownRecord = null;
          foreach ($this->records as $key => $listingNumber) {
               if ($this->EXCHANGE->returnMediaObject($this->parms['working_resource'],
                                                      $this->parms['media_type'],
                                                      $listingNumber,
                                                      0) != null) {
                    $knownRecord = $listingNumber;
                    break;
               }
          }

          if ($knownRecord == null) {
               $this->parms['image_support'] = false;
               $this->parms['image_capabilities'] = false;
$this->addDataObservation('No images found');
               return false;
          }
          $this->parms['image_support'] = true;
          $this->parms['known_record'] = $knownRecord;
$this->addDataObservation('Image(s) found for listing "' . $knownRecord . '"');

          return true;
     }

     function probe10() {
          if ($this->parms['is_property_resource']) {
$this->displayText('Determine imaging capabilities');
//
// look for media capabilities
//
               $MEDIA_SETTINGS = new AutoDetectMediaSettings();  
               $looking = true;
               $trial = 0;
               while (!$MEDIA_SETTINGS->areValid() && $looking) {
                    $MEDIA_SETTINGS->reset();
                    switch ($trial) {
                         case 0:
                              $MEDIA_SETTINGS->setMultipart(true);
                              $MEDIA_SETTINGS->setLocation(true);
                              break;

                         case 1:
                              $MEDIA_SETTINGS->setMultipart(true);
                              $MEDIA_SETTINGS->setLocation(false);
                              break;

                         case 2:
                              $MEDIA_SETTINGS->setMultipart(false);
                              $MEDIA_SETTINGS->setLocation(true);
                              break;

                         case 3:
                              $MEDIA_SETTINGS->setMultipart(false);
                              $MEDIA_SETTINGS->setLocation(false);
                              break;
                    }

                    if ($trial > 3) { 
                         $looking = false;
                    } else {
                         $MEDIA_REQUEST = new MediaRequest($this->parms['working_resource'],
                                                           $this->parms['known_record'],
                                                           $this->parms['media_type'],
                                                           $MEDIA_SETTINGS->getMultipart(),
                                                           $MEDIA_SETTINGS->getLocation(),
                                                           $this->contextName);
                         $list = $this->EXCHANGE->searchMediaDirect($MEDIA_REQUEST);
                         if ($list != null) {
                              if (sizeof($list) > 0) {
                                   $MEDIA_SETTINGS->setValid(true);
                                   break;
                              }
                         }
                    }
                    ++$trial;
               }

               if (!$MEDIA_SETTINGS->areValid()) {
                    $this->parms['image_capabilities'] = false;
                    return false;
               }
               $this->parms['image_capabilities'] = true;
               $this->parms['media_multipart'] = $MEDIA_SETTINGS->getMultipart();
               $this->parms['media_location'] = $MEDIA_SETTINGS->getLocation();
          }

          return true;
     }

     function lookupKeyField() {
//
// look for a setting in the resource metadata 
//
          $METADATA_RESOURCE = new ResourceMetadata($this->contextName);
          $keyName = $METADATA_RESOURCE->keyField($this->parms['working_resource'],
                                                  false);
 
//
// if not standard names return
//
          if (!$this->parms['standard_names']) {
               return $keyName;
          }

//
// read metadata
//
          $METADATA_CLASS = new ClassMetadata($this->contextName, 
                                              $this->parms['working_resource']);
          $systemClass = $METADATA_CLASS->getSystemClass($this->parms['working_class'],
                                                         $this->parms['standard_names']);
          $METADATA_TABLE = new TableMetadata($this->contextName, 
                                              $systemClass);
          $METADATA_TABLE->read();
          $table = $METADATA_TABLE->findTranslations();

//
// search for keyValue 
//
          foreach ($table as $key => $value) {
               if ($key == $keyName) {
                    return $value;
               }
          }

          return null;
     }

     function keyDefined($candidate,
                         $nd_list,
                         $ns_list,
                         $un_list) {
//
// test if key is displayable
//
          $keyDisplayable = true;
          if (array_key_exists($candidate, $nd_list)) {
               $keyDisplayable = false;
          }

//
// test if key is searchable
//
          $keySearchable = true;
          if (array_key_exists($candidate, $ns_list)) {
               $keySearchable = false;
          }

//
// test if key is unique 
//
          $keyUnique = false;
          if ($un_list != null) {
               foreach ($un_list as $key => $value) {
                    if ($value == $candidate) {
                         $keyUnique = true;
                    }
               }
          }

          $this->workingText = null;
//
// if all is well, return
//
          if ($keyDisplayable && 
              $keySearchable &&
              $keyUnique) {
               return true;
          }

//
// document reason why
//
          $this->workingText .= '[' . $candidate . '] missing a setting';
          if (!$keyDisplayable) { 
               $this->workingText .= ' {displayable}';
          }

          if (!$keySearchable) { 
               $this->workingText .= ' {searchable}';
          }

          if (!$keyUnique) {
               $this->workingText .= ' {unique}';
          }
          $this->workingText .= ' in METADATA-TABLE';

          return false;
     }

     function performAnalysis() {
          if ($this->parms != null) {
               if (!$this->parms['second_pass_direct_data']) {
                    if (!$this->parms['data_found']) {
$this->addDataObservation('No listing data found on server with Automated Queries.');
                    } else {
                         if (strpos($this->parms['search_content_type'],'text/xml') === false) {
$this->addDataObservation('Content-Type header of ' .  $this->parms['search_content_type'] .
                      ' returned for searches.  Should be text/xml. (report this)');
                         }
//                         if ($this->parms['null_query_option'] == 'DIRECT') {
                         if (!$this->parms['supports_null_query']) {
$this->addDataObservation('NULL query processing is not supported');
                         } else {
$this->addDataObservation('NULL queries supported with the ' . $this->parms['null_query_option'] . ' approach');
                         }
                         if ($this->parms['count_option']) {
$this->addDataObservation('COUNT option of RETS Search is supported.');
                         } else {
$this->addDataObservation('COUNT option of RETS Search is not supported. (report this)');
                         }
                         if ($this->parms['compact_decoded_format']) {
$this->addDataObservation('COMPACT-DECODED option of RETS Search is supported.');
                         } else {
$this->addDataObservation('COMPACT_DECODED option of RETS Search is not supported.');
                         }
                         if ($this->parms['offset_option']) {
$this->addDataObservation('OFFSET option of RETS Search supported.');
                              if ($this->parms['pagination']) {
                                   if ($this->parms['offset_adjustment'] == -1) {
$this->addDataObservation('Text Offset starts at 0 ("n-1" text server). Report this.');
//                                   } else {
//$this->addDataObservation('Text Offset starts at 1 ("n" text server).');
                                   }
$this->addDataObservation('Pagination supported.');
                              } else {
                                   if (!$this->parms['cursors']) {
$this->addDataObservation('Consistancy may not be dependable because cursors are not maintained');
                                   }
$this->addDataObservation('Pagination not possible.');
                              }
                         } else {
//                              if ($this->parms['null_query_option'] == 'DIRECT') {
                              if (!$this->parms['supports_null_query']) {
$this->addDataObservation('Pagination may be possible, but needs to be configured manually.');
                              } else {
$this->addDataObservation('OFFSET option of RETS Search not supported. (pagination not possible)');
                              }
                         }
                         if (!$this->parms['is_property_resource']) {
$this->addDataObservation('Images not supported for this type of resource.');
                         } else {
                              if (!$this->parms['image_support']) {
$this->addDataObservation('Media items could not be found. (sample size may be too small or there are no images)');
                              } else {
                                   if (!$this->parms['image_capabilities']) {
$this->addDataObservation('Media found but capabilities could not be determined. (report this)');
                                   } else {
                                        if ($this->parms['media_multipart']) {
$this->addDataObservation('MULTIPART option of RETS GetObject supported. (all images can be accessed in a single call)');
                                        } else {
$this->addDataObservation('MULTIPART option of RETS GetObject not supported. (Images can only be accessed with separate calls)');
                                        }
                                        if ($this->parms['media_location']) {
$this->addDataObservation('LOCATION option of RETS GetObject supported. (images can be referenced directly or by URL)');
                                        } else {
$this->addDataObservation('LOCATION option of RETS GetObject not supported. (images can be only referenced directly)');
                                        }
                                   }
                              }
                         }
                    }
               } else {
$this->addDataObservation('REFERENCE set could not be collected.  Please supply a known listing.</br>' .
                          'If you plan to extract images, make sure the listing has images');
               }
          } else {
$this->addDataObservation('Auto-Detection did not complete.');
          }
     }
}

class AutoDetectMediaSettings {
     var $multipart = false;
     var $location = false;
     var $valid = false;

     function AutoDetectMediaSettings() {
     }

     function reset() {
          $this->multipart = false;
          $this->location = false;
          $this->valid = false;
     }

     function setMultipart($value) {
          $this->multipart = $value;
     }

     function setLocation($value) {
          $this->location = $value;
     }

     function setValid($value) {
          $this->valid = $value;
     }

     function getMultipart() {
          return $this->multipart;
     }

     function getLocation() {
          return $this->location;
     }

     function areValid() {
          return $this->valid;
     }

}

?>
