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
// includes 
//
include_once('./controller.php');

set_time_limit(0);

define('CLEANUP_ON_FAILURE', false);

if (array_key_exists('CANCEL', $vars)) {
     $url = $SCREEN[$vars['PASSTHRU-LOCATION']] . 
            '?ELEMENT=' . $vars['ELEMENT'];
     locate_next_screen($url);
} else {
     if (array_key_exists('SELECT-ONLY', $vars)) {
          if (array_key_exists('PASSTHRU-LOCATOION', $vars)) {
               $passthru_location = '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'];
          } else {
               $passthru_location = '';
          }
          $url = $SCREEN[$vars['PASSTHRU']] . 
                         '?ELEMENT=' . $vars['ELEMENT'] .
                         '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                         $passthru_location .
                         '&MODE=' . $vars['MODE'];
          locate_next_screen($url);
     }
}

//
// start the page
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// make sure a listing was passed
//
$showError = false;
if (!array_key_exists('KNOWN_LISTING', $vars)) {
     $showError = true;
} else {
     if (strlen($vars['KNOWN_LISTING']) == 0) {
          $showError = true;
     }
}
if ($showError) {
     $FORMATTER = new TableFormatter();
     $items = null;
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');
     $items[] = $FORMATTER->formatBlock('No Listing was specified so no tests could be run.');
     $items[] = $FORMATTER->formatBlock('Please re-run Auto-Detection providing a Listing that exists.', 'red');
     $items[] = $FORMATTER->formatBlock('Hit the "Back" button on the browser and try again.</br>The "Return" button below ends Auto-Detection.');
     $items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
     if ($vars['MODE'] == 'PASSTHRU') {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'SETUP_INDEX');
     } else {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_MENU');
     }
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);

     $FORMATTER->printNotice($items, 
                             $SCREEN['SOURCE_AUTO_DETECT'], 
                             'Results for [' .  $vars['ELEMENT'] .  ']',
                             'Auto-Detection Direct Trial Results',
                             false,
                             'Return');
     $FORMATTER->finish();

     $HTML->finish();
     exit();
}

//
// display a note to the user
//
$FORMATTER = new TableFormatter();
$buffer = null;
$buffer .= '<!-- Notice -->' . "\r\n";
$buffer .= $FORMATTER->renderStartFrameItem();
$buffer .= $FORMATTER->renderStartPanel();
$buffer .= $FORMATTER->renderStartInnerFrame();
$buffer .= $FORMATTER->renderTitle('Auto-Detection Direct<br/>');
$buffer .= $FORMATTER->formatText('<br/>This process uses a single listing to',
                                  PROJECT_FONT_COLOR);
$buffer .= $FORMATTER->formatText('<br/>determining server media capabilities.<br/><br/>',
                                  PROJECT_FONT_COLOR);
$buffer .= $FORMATTER->renderError('Please read the Auto-Detection Results</br>');
$buffer .= $FORMATTER->renderEndInnerFrame();
$buffer .= $FORMATTER->renderEndPanel();
$buffer .= $FORMATTER->renderEndFrameItem();
$buffer .= '<!-- Notice -->' . "\r\n";
print($buffer);
$FORMATTER->finish();
flush_to_browser();

//
// read file
//
$SOURCE = new Source();
$CONFIGURATION = $SOURCE->getConfiguration($vars['ELEMENT']);
if ($vars['MODE'] != 'PASSTHRU') {
     $saved_query_items = $CONFIGURATION->getValue('QUERY_ITEMS');
}

//
// check RETS capabilities
//
$AUTO_DETECT = new AutoDetect($vars['ELEMENT'], false);
//$AUTO_DETECT->setSystemNameOnly(true);
$AUTO_DETECT->setTraceDevice(LOG_DIRECTORY . '/autoDetect_direct.log');
$AUTO_DETECT->initializeTraceDevice();
$AUTO_DETECT->setPayloadTrace(true);
//$AUTO_DETECT->setStreamTrace($this->streamTrace);
$AUTO_DETECT->setTransportTrace(true);
$AUTO_DETECT->resetProbe();


//
// find direct data
//
$err_number = 0;
$AUTO_DETECT->probe_data_direct($CONFIGURATION->getValue('RETS_SERVER_ACCOUNT'),
                    $CONFIGURATION->getValue('RETS_SERVER_PASSWORD'),
                    $CONFIGURATION->getValue('RETS_SERVER_URL'),
                    $CONFIGURATION->getValue('APPLICATION'),
                    $CONFIGURATION->getValue('VERSION'),
                    $CONFIGURATION->getValue('RETS_CLIENT_PASSWORD'),
                    $CONFIGURATION->getValue('SELECTION_RESOURCE'),
                    $CONFIGURATION->getValue('SELECTION_CLASS'),
                    $CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION'),
                    $CONFIGURATION->getValue('POST_REQUESTS'),
                    $CONFIGURATION->getValue('UNIQUE_KEY'),
                    $CONFIGURATION->getValue('STANDARD_NAMES'),
                    $vars['KNOWN_LISTING']);
$parms = $AUTO_DETECT->getDetectedParameters();
$FORMATTER = new TableFormatter();
if (!$parms['data_found']) {
     $err_number = 1;
     print('<!-- Notice -->' . CRLF .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel() .
           $FORMATTER->renderStartInnerFrame() .
           $FORMATTER->renderTitle('Data Auto-Detection Complete<br/><br/>') .
           $FORMATTER->formatText(implode('<br>', $AUTO_DETECT->getDataObservations()),
                                  'red') .
           $FORMATTER->renderError('<br/><br/>Errors exist</br>') .
           $FORMATTER->renderEndInnerFrame() .
           $FORMATTER->renderEndPanel() .
           $FORMATTER->renderEndFrameItem() .
           '<!-- Notice -->' . CRLF);
} else {
/*
     $CONFIGURATION->setValue('COMPACT_DECODED_FORMAT', $parms['compact_decoded_format']);
     if ($vars['MODE'] != 'PASSTHRU') {
          $CONFIGURATION->setValue('QUERY_ITEMS', $saved_query_items);
     } else {
          $CONFIGURATION->setValue('QUERY_ITEMS', $parms['unique_key']);
//          $CONFIGURATION->setValue('QUERY_ITEMS', $parms['search_fields']);
     }

//
// ADDED 1.1.8
//
     $CONFIGURATION->setValue('NULL_QUERY_OPTION', $parms['null_query_option']);
     if ($parms['pagination']) {
          $CONFIGURATION->setValue('PAGINATION', true);
          $CONFIGURATION->setValue('OFFSET_ADJUSTMENT', $parms['offset_adjustment']);
     } else {
          $CONFIGURATION->setValue('PAGINATION', false);
     } 
     if ($parms['is_property_resource']) {
          $CONFIGURATION->setValue('MEDIA_BYPASS', false);
          if ($parms['image_support']) {
               if (array_key_exists('media_multipart', $parms)) {
                    $CONFIGURATION->setBooleanValue('MEDIA_MULTIPART', $parms['media_multipart']);
               }
               if (array_key_exists('media_location', $parms)) {
                    $CONFIGURATION->setBooleanValue('MEDIA_LOCATION', $parms['media_location']);
               }
          }
     } else {
          $CONFIGURATION->setValue('MEDIA_BYPASS', true);
     }
     $SOURCE->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);
*/

     print('<!-- Notice -->' . CRLF .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel() .
           $FORMATTER->renderStartInnerFrame() .
           $FORMATTER->renderTitle('Data Auto-Detection Complete<br/><br/>') .
           $FORMATTER->formatText(implode('<br>', $AUTO_DETECT->getDataObservations()),
                                  'green') .
           $FORMATTER->renderError('<br/></br>') .
           $FORMATTER->renderEndInnerFrame() .
           $FORMATTER->renderEndPanel() .
           $FORMATTER->renderEndFrameItem() .
           '<!-- Notice -->' . CRLF);
}
$FORMATTER->finish();
flush_to_browser();

//
// using view.php 
//
$FORMATTER = new TableFormatter();
$items = null;
//$items[] = $FORMATTER->formatBoldText($CONFIGURATION->getValue('RETS_SERVER_URL'),
//                                      PROJECT_FONT_COLOR);
//
// check for errors
//
if ($err_number != 0) {
     if (CLEANUP_ON_FAILURE) {
//
// delete of metadata and source 
//
          $METADATA = new Metadata($vars['ELEMENT']);
          $METADATA->remove();
          $SOURCE->removeConfiguration($vars['ELEMENT']);
     }

//
// display message
//
     $items[] = $FORMATTER->formatText('Report failures to your Provider', 'red');
     $next_screen = 'SETUP_INDEX';
} else {
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_OVERRIDE');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');
     $items[] = $FORMATTER->formatText('Data can be read from the server', 'green');
     $items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
     if ($vars['MODE'] == 'PASSTHRU') {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SETUP_INDEX');
     } else {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SOURCE_MENU');
     }
     $next_screen = 'SOURCE_AUTO_DETECT';
}
$items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);

$FORMATTER->printNotice($items, 
                        $SCREEN[$next_screen], 
                        'Results for [' .  $vars['ELEMENT'] .  ']',
                        'Auto-Detection Direct Trial Results');
$FORMATTER->finish();

$HTML->finish();

function flush_to_browser() {
     for($i = 0; $i < 40000; $i++) {
          echo ' ';
     }
     flush();
}

?>
