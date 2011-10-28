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
          if (array_key_exists('KNOWN_LISTING', $vars)) {
               $url = $SCREEN[$vars['PASSTHRU']] . 
                              '?ELEMENT=' . $vars['ELEMENT'] .
                              '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                              '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
                              '&MODE=' . $vars['MODE'] .
                              '&KNOWN_LISTING=' . $vars['KNOWN_LISTING'];
          } else {
               $url = $SCREEN[$vars['PASSTHRU']] . 
                              '?ELEMENT=' . $vars['ELEMENT'] .
                              '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                              '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
                              '&MODE=' . $vars['MODE'];
          }
          locate_next_screen($url);
     }
}


//
// start the page
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// display a note to the user
//
$FORMATTER = new TableFormatter();
$buffer = null;
$buffer .= '<!-- Notice -->' . "\r\n";
$buffer .= $FORMATTER->renderStartFrameItem();
$buffer .= $FORMATTER->renderStartPanel();
$buffer .= $FORMATTER->renderStartInnerFrame();
$buffer .= $FORMATTER->renderTitle('Auto-Detection<br/>');
$buffer .= $FORMATTER->formatText('<br/>This process allows important items like fields',
                                  PROJECT_FONT_COLOR);
$buffer .= $FORMATTER->formatText('<br/>and media capabilities to be determined.<br/><br/>',
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
$saved_query_items = null;
if ($vars['MODE'] != 'PASSTHRU') {
     $saved_query_items = $CONFIGURATION->getValue('QUERY_ITEMS');
}

//
// check RETS capabilities
//
$AUTO_DETECT = new AutoDetect($vars['ELEMENT'], false);
//$AUTO_DETECT->setSystemNameOnly(true);
$AUTO_DETECT->setTraceDevice(LOG_DIRECTORY . '/autoDetect.log');
$AUTO_DETECT->initializeTraceDevice();
$AUTO_DETECT->setPayloadTrace(true);
//$AUTO_DETECT->setStreamTrace($this->streamTrace);
$AUTO_DETECT->setTransportTrace(true);
$AUTO_DETECT->resetProbe();

//
// analyze metadata 
//
$AUTO_DETECT->probe_metadata($CONFIGURATION->getValue('RETS_SERVER_ACCOUNT'),
                    $CONFIGURATION->getValue('RETS_SERVER_PASSWORD'),
                    $CONFIGURATION->getValue('RETS_SERVER_URL'),
                    $CONFIGURATION->getValue('APPLICATION'),
                    $CONFIGURATION->getValue('VERSION'),
                    $CONFIGURATION->getValue('RETS_CLIENT_PASSWORD'),
                    $CONFIGURATION->getValue('SELECTION_RESOURCE'),
                    $CONFIGURATION->getValue('SELECTION_CLASS'),
                    $CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION'),
                    $CONFIGURATION->getValue('POST_REQUESTS'));

$parms = $AUTO_DETECT->getDetectedParameters();
$FORMATTER = new TableFormatter();
if (!$parms['metadata_found']) {
     $err_number = 2;
     print('<!-- Notice -->' . CRLF .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel() .
           $FORMATTER->renderStartInnerFrame() .
           $FORMATTER->renderTitle('Metadata Auto-Detection Complete<br/><br/>') .
           $FORMATTER->formatText(implode('<br>', $AUTO_DETECT->getMetadataObservations()),
                                  'red') .
           $FORMATTER->renderError('<br/><br/>Errors exist</br>') .
           $FORMATTER->renderEndInnerFrame() .
           $FORMATTER->renderEndPanel() .
           $FORMATTER->renderEndFrameItem() .
           '<!-- Notice -->' . CRLF);
} else {
     $CONFIGURATION->setValue('DETECTED_DEFAULT_RETS_VERSION', $parms['default_rets_version']);
     $CONFIGURATION->setValue('DETECTED_MAXIMUM_RETS_VERSION', $parms['maximum_rets_version']);
     $CONFIGURATION->setValue('SELECTION_RESOURCE', $parms['working_resource']); 
     $CONFIGURATION->setValue('SELECTION_CLASS', $parms['working_class']); 
     $CONFIGURATION->setValue('DETECTED_STANDARD_NAMES', $parms['standard_names']);
     $CONFIGURATION->setValue('UNIQUE_KEY', $parms['unique_key']);
     $CONFIGURATION->setValue('SUMMARY_ITEMS', $parms['all_fields']);
//
// ADDED 1.1.8
//
     $CONFIGURATION->setValue('ALL_TYPES', $parms['all_types']);
     $CONFIGURATION->setValue('ALL_FIELDS', $parms['all_fields']);
     $CONFIGURATION->setValue('ALL_INTERPRETATIONS', $parms['all_interpretations']);
     $CONFIGURATION->setValue('ALL_REQUIREDS', $parms['all_reqs']);
     $SOURCE->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

     print('<!-- Notice -->' . CRLF .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel() .
           $FORMATTER->renderStartInnerFrame() .
           $FORMATTER->renderTitle('Metadata Auto-Detection Complete<br/><br/>') .
           $FORMATTER->formatText(implode('<br>', $AUTO_DETECT->getMetadataObservations()),
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
// find data
//
$err_number = 0;
$AUTO_DETECT->probe_data();
$parms = $AUTO_DETECT->getDetectedParameters();
$FORMATTER = new TableFormatter();
if (!$parms['second_pass_direct_data']) {
     if (!$parms['data_found']) {
          $err_number = 1;
          renderDataError($FORMATTER, $AUTO_DETECT);
     } else {
          saveDataDetection($CONFIGURATION, $parms, $vars['MODE'], $saved_query_items);
          $SOURCE->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);
          renderDataComplete($FORMATTER, $AUTO_DETECT);
     }
} else {
     if ($parms['maximum_rets_version'] == '1.7' ||
         $parms['maximum_rets_version'] == '1.7.2') {
          print('<!-- Notice -->' . CRLF .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel() .
                $FORMATTER->renderStartInnerFrame() .
                $FORMATTER->renderTitle('Data Auto-Detection Not Complete<br/><br/>') .
                $FORMATTER->formatText('This server reports to support RETS 1.7 or 1.7.2 in earlier tests<br/>' .
                                       'but is failing some required query functionality.', 'red') .
                $FORMATTER->renderError('<br/><br/>Attempting fall back to RETS 1.5</br>') .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                '<!-- Notice -->' . CRLF);
          $FORMATTER->finish();
          flush_to_browser();
          $AUTO_DETECT->setParameter('default_rets_version', '1.5');
          $AUTO_DETECT->setParameter('maximum_rets_version', '1.5');
          $AUTO_DETECT->probe_data();
          $parms = $AUTO_DETECT->getDetectedParameters();
          $FORMATTER = new TableFormatter();
          if (!$parms['second_pass_direct_data']) {
               if (!$parms['data_found']) {
                    $err_number = 1;
                    renderDataError($FORMATTER, $AUTO_DETECT);
               } else {
                    saveDataDetection($CONFIGURATION, $parms, $vars['MODE'], $saved_query_items);
                    $SOURCE->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);
                    renderDataComplete($FORMATTER, $AUTO_DETECT);
               }
          }
     }
}
$FORMATTER->finish();
flush_to_browser();


//
// present an action to the user 
//
$FORMATTER = new TableFormatter();
$topMessage = 'Auto-Detect Trial Results';
$items = null;
$items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);
//$items[] = $FORMATTER->formatBoldText($CONFIGURATION->getValue('RETS_SERVER_URL'),
//                                      PROJECT_FONT_COLOR);

if ($parms['second_pass_direct_data']) {
     $items[] = $FORMATTER->formatBlock(implode('<br>', $AUTO_DETECT->getDataObservations()), 'red');
     $items[] = $FORMATTER->formatEntryField('Known Listing (with images)', 'KNOWN_LISTING', '');
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_AUTO_DETECT_DIRECT');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');
     $items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
     if ($vars['MODE'] == 'PASSTHRU') {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SETUP_INDEX');
     } else {
          $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SOURCE_MENU');
     }
     $next_screen = 'SOURCE_AUTO_DETECT';
     $FORMATTER->printNotice($items, 
                             $SCREEN[$next_screen], 
                             'Results for [' .  $vars['ELEMENT'] .  ']',
                             $topMessage,
                             true);
} else {
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
     $FORMATTER->printNotice($items, 
                             $SCREEN[$next_screen], 
                             'Results for [' .  $vars['ELEMENT'] .  ']',
                             $topMessage);
}

$FORMATTER->finish();

$HTML->finish();

function flush_to_browser() {
     for($i = 0; $i < 40000; $i++) {
          echo ' ';
     }
     flush();
}

function renderDataError($FORMATTER,
                         $AUTO_DETECT) {
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
}

function renderDataComplete($FORMATTER,
                         $AUTO_DETECT) {
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

function saveDataDetection(&$CONFIGURATION, 
                           $parms,
                           $mode,
                           $saved_query_items) {
     $CONFIGURATION->setValue('COMPACT_DECODED_FORMAT', $parms['compact_decoded_format']);
     $CONFIGURATION->setValue('DETECTED_DEFAULT_RETS_VERSION', $parms['default_rets_version']);
     $CONFIGURATION->setValue('DETECTED_MAXIMUM_RETS_VERSION', $parms['maximum_rets_version']);
     $CONFIGURATION->setValue('COMPACT_DECODED_FORMAT', $parms['compact_decoded_format']);
     if ($mode != 'PASSTHRU') {
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
}

?>
