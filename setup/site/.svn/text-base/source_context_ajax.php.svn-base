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
/*
     $fields = Array();
     $fields[] = 'DETECTED_STANDARD_NAMES';
     foreach ($fields as $key => $name) {
          if (!array_key_exists($name, $vars)) {
               $vars[$name] = 'false';
          } else {
               $vars[$name] = getBooleanStringFromArg($vars[$name]);
          }
     }
*/

//
// set values in the configuration
//
     setConfigurationFromArgs($CONFIGURATION,'DETECTED_STANDARD_NAMES', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SELECTION_RESOURCE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SELECTION_CLASS', $vars);

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $url = $SCREEN['SOURCE_AUTO_DETECT'] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
            '&MODE=' . $vars['MODE'];

     locate_next_screen($url);
}

//--------------------

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface', true, true);

$FORMATTER = new AjaxFormatter();

if (array_key_exists('MODE', $vars)) {
     print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
     print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));
     print($FORMATTER->formatHiddenField('MODE', $vars['MODE']));
}

print($FORMATTER->renderStartFrame(null, null, null) .
      $FORMATTER->renderStartFrameItem() .
      $FORMATTER->renderStartPanel('Define the Context for this SOURCE') .
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
