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
include_once(AJAX_DIRECTORY . '/ajaxFormatter.php');

//--------------------
//
// create a default return location
//

if (array_key_exists('CANCEL', $vars)) {
     locate_next_screen($SCREEN['SETUP_INDEX']);
}

$SOURCE = new Source();

if (array_key_exists('SELECT-ONLY', $vars)) {
//
// make sure there are no arrays
//
     foreach ($vars as $key => $value) {
          if (is_array($value)) {
               $vars[$key] = implode(',',$value);
          }
     }
     $configName = $vars['ELEMENT'];

//
// update configuration 
//
     $LOCATION = determine_type($vars['ELEMENT-TYPE']);
     $CONFIGURATION = $LOCATION->getConfiguration($configName);

//
// fix binary elements
//
     $fields = Array();
     $fields[] = 'DETECTED_STANDARD_NAMES';
     $fields[] = 'COMPACT_DECODED_FORMAT';
     $fields[] = 'MEDIA_BYPASS';
     $fields[] = 'MEDIA_LOCATION';
     $fields[] = 'MEDIA_MULTIPART';
     $fields[] = 'PAGINATION';
     $fields[] = 'SIMULTANEOUS_LOGINS';
     foreach ($fields as $key => $name) {
          if (!array_key_exists($name, $vars)) {
               $vars[$name] = 'false';
          } else {
//               if ($vars[$name] == '1') {
//                    $vars[$name] = 'true';
//               } else {
//                    if ($vars[$name] == '0') {
//                         $vars[$name] = 'false';
//                    }
//               }
               $vars[$name] = getBooleanStringFromArg($vars[$name]);
          }
     }

//
// set values in the configuration
//
     setConfigurationFromArgs($CONFIGURATION,'UNIQUE_KEY', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DETECTED_STANDARD_NAMES', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DETECTED_MAXIMUM_RETS_VERSION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'COMPACT_DECODED_FORMAT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'NULL_QUERY_OPTION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'PAGINATION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SIMULTANEOUS_LOGINS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MEDIA_BYPASS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'OFFSET_ADJUSTMENT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MEDIA_MULTIPART', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MEDIA_LOCATION', $vars);

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $url = $SCREEN[$vars['PASSTHRU']] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
            '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
            '&MODE=' . $vars['MODE'];

     locate_next_screen($url);
}

//--------------------

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

$FORMATTER = new AjaxFormatter();

if (array_key_exists('MODE', $vars)) {
     print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
     print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));
     print($FORMATTER->formatHiddenField('MODE', $vars['MODE']));
     switch ($vars['MODE']) {
          case 'OVERRIDE':
               print($FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_MENU'));
               print($FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $vars['PASSTHRU-LOCATION']));
               break;

          case 'PASSTHRU':
               print($FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_QUERY_PARAMETERS'));
               print($FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SETUP_INDEX'));
               break;

          case 'UPDATE':
               print($FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_QUERY_PARAMETERS'));
               print($FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $vars['PASSTHRU-LOCATION']));
               break;

     }

}

print($FORMATTER->renderStartFrame(null, null, null) .
      $FORMATTER->renderStartFrameItem() .
      $FORMATTER->renderStartPanel('Override Auto-Detection (if necessary)') .
      $FORMATTER->renderStartInnerFrame() .
      create_ajax_target(true) .
      $FORMATTER->renderEndInnerFrame() .
      $FORMATTER->renderEndPanel() .
      $FORMATTER->renderEndFrameItem() .
      $FORMATTER->renderEndFrame());

$HTML->finish();

//
//------------

?>
