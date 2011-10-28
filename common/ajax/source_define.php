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

function ajax_processValue($aName,
                           $aValue,
                           $env = null) {

     $trace = null;

//     $trace = $aName . ' ' . $aValue;
//     $trace = print_r($env, true);

//
// set up defaults
//
     $SOURCE = new Source();
     $existing_sources = sizeof($SOURCE->getExisting());
     if ($existing_sources == 0) {
          $element = DEFAULT_CONFIG_NAME;
     } else {
          $element = DEFAULT_CONFIG_NAME . '_' . $existing_sources;
     }
     $description = 'A RETS Source';
     $retsServerAccount = 'Joe';
     $retsServerPassword = 'Schmoe';
     $retsServerURL = 'http://demo.crt.realtors.org:6103/rets/login';
     $retsClientPassword = '';
     $application = 'VieleRETS';
     $version = '1.1.8';
     $postRequests = 'true';
     $detectedDefaultRetsVersion = '1.5';
     $selectionResource = 'Property';
     $selectionClass = 'ResidentialProperty';
     $detectedStandardNames = 'false';

     if ($env == null) {
          $env['ELEMENT'] = $element;
          $env['DESCRIPTION'] = $description;
          $env['RETS_SERVER_ACCOUNT'] = $retsServerAccount;
          $env['RETS_SERVER_PASSWORD'] = $retsServerPassword;
          $env['RETS_SERVER_URL'] = $retsServerURL;
          $env['RETS_CLIENT_PASSWORD'] = $retsClientPassword;
          $env['APPLICATION'] = $application;
          $env['VERSION'] = $version;
          $env['POST_REQUESTS'] = $postRequests;
          $env['DETECTED_DEFAULT_RETS_VERSION'] = $detectedDefaultRetsVersion;
          $env['SELECTION_RESOURCE'] = $selectionResource;
          $env['SELECTION_CLASS'] = $selectionClass;
          $env['MODE'] = 'PASSTHRU';
     } else {
          if (array_key_exists('viele_mode',$env)) {
               $LOCATION = determine_type($env['ELEMENT-TYPE']);
               $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
               $retsServerAccount = $CONFIGURATION->getValue('RETS_SERVER_ACCOUNT');
               $retsServerPassword = $CONFIGURATION->getValue('RETS_SERVER_PASSWORD');
               $retsServerURL = $CONFIGURATION->getValue('RETS_SERVER_URL');
               $retsClientPassword = $CONFIGURATION->getValue('RETS_CLIENT_PASSWORD');
               $application = $CONFIGURATION->getValue('APPLICATION');
               $version = $CONFIGURATION->getValue('VERSION');
               $postRequests = $CONFIGURATION->getBooleanValue('POST_REQUESTS');
               $detectedDefaultRetsVersion = $CONFIGURATION->getValue('DETECTED_DEFAULT_RETS_VERSION');
               $selectionResource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
               $selectionClass = $CONFIGURATION->getValue('SELECTION_CLASS');
               $detectedStandardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          }
     }

//
// weigh input
//
     if (array_key_exists('ELEMENT',$env)) {
          $element = $env['ELEMENT'];
     }

     if (array_key_exists('DESCRIPTION',$env)) {
          $description = $env['DESCRIPTION'];
     }

     if (array_key_exists('RETS_SERVER_ACCOUNT',$env)) {
          $retsServerAccount = $env['RETS_SERVER_ACCOUNT'];
     }

     if (array_key_exists('RETS_SERVER_PASSWORD',$env)) {
          $retsServerPassword = $env['RETS_SERVER_PASSWORD'];
     }

     if (array_key_exists('RETS_SERVER_URL',$env)) {
          $retsServerURL = $env['RETS_SERVER_URL'];
     }

     if (array_key_exists('RETS_CLIENT_PASSWORD',$env)) {
          $retsClientPassword = $env['RETS_CLIENT_PASSWORD'];
     }

     if (array_key_exists('APPLICATION',$env)) {
          $application = $env['APPLICATION'];
     }

     if (array_key_exists('VERSION',$env)) {
          $version = $env['VERSION'];
     }

     if (array_key_exists('POST_REQUESTS',$env)) {
          $postRequests = $env['POST_REQUESTS'];
     }

     if (array_key_exists('DETECTED_DEFAULT_RETS_VERSION',$env)) {
          $detectedDefaultRetsVersion = $env['DETECTED_DEFAULT_RETS_VERSION'];
     }

     if (array_key_exists('SELECTION_RESOURCE',$env)) {
          $selectionResource = $env['SELECTION_RESOURCE'];
     }

     if (array_key_exists('SELECTION_CLASS',$env)) {
          $selectionClass = $env['SELECTION_CLASS'];
     }

     $FORMATTER = new AjaxFormatter();

     $blockSubmit = false;
     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatSingleEntryField('Name',
                                                   'ELEMENT',
                                                   $element,
                                                   32);

     $items[] = $FORMATTER->formatSingleEntryField('Description',
                                                   'DESCRIPTION',
                                                   $description,
                                                   32);

     $items[] = $FORMATTER->formatSeparator('Connection Information');

     $CONNECTION = verifyTransport($retsServerURL,
                                   $application,
                                   $version,
                                   $detectedDefaultRetsVersion);

     if ($CONNECTION->getErrorNumber() == 0) {

          $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Server URL',
                                                        'RETS_SERVER_URL',
                                                        $retsServerURL,
                                                        49);

          $items[] = $FORMATTER->formatSingleEntryField('Application Name',
                                                        'APPLICATION',
                                                        $application,
                                                        20);
          $items[] = $FORMATTER->formatSingleEntryField('Application Version',
                                                        'VERSION',
                                                        $version);
          $options = null;
          $options['1.0'] = '1.0 (deprecated)';
          $options['1.5'] = '1.5';
          $options['1.7'] = '1.7';
          $options['1.7.2'] = '1.7.2';
          $items[] = $FORMATTER->formatSelectField('RETS Version of the server',
                                                   'DETECTED_DEFAULT_RETS_VERSION',
                                                   $detectedDefaultRetsVersion,
                                                   $options);

          $detectedServerName = $CONNECTION->getServerName();
          $items[] = $FORMATTER->formatDisplayField('Server Name',
                                                    $detectedServerName,
                                                    'green');
          $items[] = $FORMATTER->formatHiddenField('DETECTED_SERVER_NAME', $detectedServerName);

          $items[] = $FORMATTER->formatSeparator('RETS Account');

          $CONNECTION = verifyRETS($element,
                                   $retsServerAccount,
                                   $retsServerPassword,
                                   $retsServerURL,
                                   $detectedDefaultRetsVersion,
                                   $application,
                                   $version,
                                   $retsClientPassword,
                                   $postRequests);
          if ($CONNECTION->getErrorNumber() == 0)
          {
               $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Account',
                                                             'RETS_SERVER_ACCOUNT',
                                                             $retsServerAccount,
                                                             32);

               $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Password',
                                                             'RETS_SERVER_PASSWORD',
                                                             $retsServerPassword,
                                                             32);
               $items[] = $FORMATTER->formatSingleEntryField('Client Password<br/>(if required)',
                                                             'RETS_CLIENT_PASSWORD',
                                                             $retsClientPassword,
                                                             32);
               $items[] = $FORMATTER->formatBinaryField('POST style HTTP Requests<br/>(you should use POST)',
                                                        'POST_REQUESTS',
                                                        $postRequests);

               $items[] = $FORMATTER->formatSeparator('Type of Information');

               $EXCHANGE = new Exchange($element);
               $EXCHANGE->loginDirect($retsServerAccount,
                                      $retsServerPassword,
                                      $retsServerURL,
                                      $detectedDefaultRetsVersion,
                                      $application,
                                      $version,
                                      $retsClientPassword,
                                      $postRequests);

//
// test if standard names for classes are available
//
               $rawMetadata = $EXCHANGE->serverClassMetadata($selectionResource);
//$trace = '<xmp>' . $selectionResource . '</xmp>';
//$trace = '<xmp>' . $rawMetadata . '</xmp>';
               $nameParser = new TranslationParser();
               $field_array = $nameParser->parse($rawMetadata,
                                           'ClassName',
                                           'VisibleName',
                                           'METADATA-CLASS');
               $sn_field_array = $nameParser->parse($rawMetadata,
                                                'StandardName',
                                                'VisibleName',
                                                'METADATA-CLASS');
               if (array_key_exists('viele_mode',$env)) {
                    if ($detectedStandardNames) {
                         $standardNames = true;
                         $trans_class = $sn_field_array;
                         $items[] = $FORMATTER->formatHiddenField('DETECTED_STANDARD_NAMES', 'true');
                         $items[] = $FORMATTER->formatDisplayField('Standard Names', 'true', 'green');
                    } else {
                         $standardNames = false;
                         $trans_class = $field_array;
                         $items[] = $FORMATTER->formatHiddenField('DETECTED_STANDARD_NAMES', 'false');
                         $items[] = $FORMATTER->formatDisplayField('Standard Names', 'false', 'red');
                    }
               } else {
                    if (sizeof($field_array) == sizeof($sn_field_array)) {
                         $standardNames = true;
                         $trans_class = $sn_field_array;
                         $items[] = $FORMATTER->formatHiddenField('DETECTED_STANDARD_NAMES', 'true');
                         $items[] = $FORMATTER->formatDisplayField('Standard Names', 'true', 'green');
                    } else {
                         $standardNames = false;
                         $trans_class = $field_array;
                         $items[] = $FORMATTER->formatHiddenField('DETECTED_STANDARD_NAMES', 'false');
                         $items[] = $FORMATTER->formatDisplayField('Standard Names', 'false', 'red');
                    }
               }

               $rawMetadata = $EXCHANGE->serverResourceMetadata();
               $nameParser = new TranslationParser();
               $trans = $nameParser->parse($rawMetadata,
                                           'ResourceID',
                                           'VisibleName',
                                           'METADATA-RESOURCE');
               $temp = null;
               foreach ($trans as $key => $value) {
                    $temp[$key] = $value;
               }
               $items[] = $FORMATTER->formatRadioField('Resource',
                                                       'SELECTION_RESOURCE',
                                                       $selectionResource,
                                                       $temp,
                                                       null,
                                                       true);
               if ($trans_class == null) {
                    $items[] = $FORMATTER->formatTextField('Class',
                                                           'ERROR: No class(es) defined for Resource ' . $selectionResource,
                                                           'red');
                    $blockSubmit = true;
               } else {
                    $temp = null;
                    foreach ($trans_class as $key => $value) {
                         $temp[$key] = $value;
                    }
                    $items[] = $FORMATTER->formatRadioField('Class',
                                                            'SELECTION_CLASS',
                                                            $selectionClass,
                                                            $temp,
                                                            null,
                                                            true);
               }
               $EXCHANGE->logoutDirect();
          } else {
               $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Account',
                                                             'RETS_SERVER_ACCOUNT',
                                                             $retsServerAccount,
                                                             32,
                                                             'red');

               $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Password',
                                                             'RETS_SERVER_PASSWORD',
                                                             $retsServerPassword,
                                                             32,
                                                             'red');

               $items[] = $FORMATTER->formatSingleEntryField('Client Password<br/>(if required)',
                                                             'RETS_CLIENT_PASSWORD',
                                                             $retsClientPassword,
                                                             32,
                                                             'red');

               $items[] = $FORMATTER->formatBinaryField('POST style HTTP Requests<br/>(you should use POST)',
                                                        'POST_REQUESTS',
                                                        $postRequests,
                                                        'red');

               $items[] = $FORMATTER->formatDisplayField('Verifying RETS Capability',
                                                         'ERROR: ' .
                                                         $CONNECTION->getErrorNumber() . '. ' . $CONNECTION->getErrorText(),
                                                         'red');
               $blockSubmit = true;
          }
     } else {
          $items[] = $FORMATTER->formatSingleEntryField('MLS RETS Server URL',
                                                        'RETS_SERVER_URL',
                                                        $retsServerURL,
                                                        48,
                                                        'red');

          $items[] = $FORMATTER->formatSingleEntryField('Application Name',
                                                        'APPLICATION',
                                                        $application,
                                                        20,
                                                        'red');

          $items[] = $FORMATTER->formatSingleEntryField('Application Version',
                                                        'VERSION',
                                                        $version,
                                                        null,
                                                        'red');

          $options = null;
          $options['1.0'] = '1.0 (deprecated)';
          $options['1.5'] = '1.5';
          $options['1.7'] = '1.7';
          $options['1.7.2'] = '1.7.2';
          $items[] = $FORMATTER->formatSelectField('RETS Version of the server',
                                                   'DETECTED_DEFAULT_RETS_VERSION',
                                                   $detectedDefaultRetsVersion,
                                                   $options,
                                                   'red');

          $items[] = $FORMATTER->formatDisplayField('Verifying URL',
                                                    'ERROR: ' .
                                                    $CONNECTION->getErrorNumber() . '. ' . $CONNECTION->getErrorText(),
                                                    'red');


          $blockSubmit = true;
     }

//-------------------

     $overrideSubmit = null;
     if ($blockSubmit) {
          $overrideSubmit = $FORMATTER->formatPageSubmit('Connect', 'SOA_CONNECT');
     }

     $items[] = $FORMATTER->formatHiddenField(AJAX_BYPASS, 'APPLICATION,VERSION,DETECTED_DEFAULT_RETS_VERSION,RETS_SERVER_ACCOUNT,RETS_SERVER_PASSWORD,RETS_CLIENT_PASSWORD');
     $items[] = $FORMATTER->formatHiddenField('MODE', $env['MODE']);
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');

//
// html response
//
     if ($env['MODE'] == 'PASSTHRU') { 
         $nextScreen = 'NEW_SOURCE';
     } else {
         $nextScreen = 'SOURCE_CONNECTION';
     }
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize($nextScreen), $items, $overrideSubmit) .
            ']]></HTML>';

}

//
//------------

?>
