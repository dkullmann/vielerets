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

//     $trace = print_r($env, true);
//     $trace = $aName . ' ' . $aValue;

//
// set up defaults
//
     $element = '';
     $selectionResource = '';
     $selectionClass = '';
     $detectedStandardNames = 'false';
     $detectedDefaultRetsVersion = '1.5';
     $retsServerAccount = '';
     $retsServerPassword = '';
     $retsServerURL = '';
     $retsClientPassword = '';
     $application = '';
     $version = '';
     $postRequests = 'false';

     if (array_key_exists('viele_mode',$env)) {
          $LOCATION = determine_type($env['ELEMENT-TYPE']);
          $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
          $selectionResource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
          $selectionClass = $CONFIGURATION->getValue('SELECTION_CLASS');
          $detectedStandardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          $detectedDefaultRetsVersion = $CONFIGURATION->getValue('DETECTED_DEFAULT_RETS_VERSION');
          $postRequests = $CONFIGURATION->getBooleanValue('POST_REQUESTS');
          $retsServerAccount = $CONFIGURATION->getValue('RETS_SERVER_ACCOUNT');
          $retsServerPassword = $CONFIGURATION->getValue('RETS_SERVER_PASSWORD');
          $retsServerURL = $CONFIGURATION->getValue('RETS_SERVER_URL');
          $retsClientPassword = $CONFIGURATION->getValue('RETS_CLIENT_PASSWORD');
          $application = $CONFIGURATION->getValue('APPLICATION');
          $version = $CONFIGURATION->getValue('VERSION');
     }

//
// weigh input
//
     if (array_key_exists('ELEMENT',$env)) {
          $element = $env['ELEMENT'];
     }

     if (array_key_exists('SELECTION_RESOURCE',$env)) {
          $selectionResource = $env['SELECTION_RESOURCE'];
     }

     if (array_key_exists('SELECTION_CLASS',$env)) {
          $selectionClass = $env['SELECTION_CLASS'];
     }

     if (array_key_exists('DETECTED_STANDARD_NAMES',$env)) {
          $detectedStandardNames = $env['DETECTED_STANDARD_NAMES'];
     }

     if (array_key_exists('DETECTED_DEFAULT_RETS_VERSION',$env)) {
          $detectedDefaultRetsVersion = $env['DETECTED_DEFAULT_RETS_VERSION'];
     }

     if (array_key_exists('POST_REQUESTS',$env)) {
          $postRequests = $env['POST_REQUESTS'];
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

     $FORMATTER = new AjaxFormatter();

     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $EXCHANGE = new Exchange($element);
     $status = $EXCHANGE->loginDirect($retsServerAccount,
                            $retsServerPassword,
                            $retsServerURL,
                            $detectedDefaultRetsVersion,
                            $application,
                            $version,
                            $retsClientPassword,
                            $postRequests);
     if ($status) {
          $items[] = $FORMATTER->formatDisplayField('Server', 'On-Line', 'green');
//
// test if standard names for classes are available
//
          $field_array = $EXCHANGE->classes($selectionResource, false);

          if ($detectedStandardNames) {
               $standardNames = true;
               $field_array = $EXCHANGE->classes($selectionResource, true);
               $items[] = $FORMATTER->formatDisplayField('Standard Names', 'true', 'green');
          } else {
               $standardNames = false;
               $items[] = $FORMATTER->formatDisplayField('Standard Names', 'false', 'red');
          }

          $resourceList = $EXCHANGE->resources(false);
          $trans = $EXCHANGE->resourceNames(false);
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

          $trans = $EXCHANGE->classNames($selectionResource, $standardNames);
          $temp = null;
          foreach ($trans as $key => $value) {
               $temp[$key] = $value;
          }
          $items[] = $FORMATTER->formatRadioField('Class',
                                                  'SELECTION_CLASS',
                                                  $selectionClass,
                                                  $temp,
                                                  null,
                                                  true);
          $EXCHANGE->logoutDirect();

     } else {
          $items[] = $FORMATTER->formatDisplayField('Server', 'Off-Line', 'red');
          $items[] = $FORMATTER->formatDisplayField('Account', $retsServerAccount);
          $items[] = $FORMATTER->formatDisplayField('Password', $retsServerPassword);
          $items[] = $FORMATTER->formatDisplayField('URL', $retsServerURL);
     }

//-------------------

     $items[] = $FORMATTER->formatHiddenField('MODE', $env['MODE']);
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $env['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $env['ELEMENT']);

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('SOURCE_DEFINE_RESOURCE'), $items) .
            ']]></HTML>';
}

//
//------------

?>
