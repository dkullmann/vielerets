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
// if the configuration does not exist, create it 
//
     if (!$SOURCE->exists($configName)) {
//
// guard against illegal names 
//
          $configName = preg_replace("/[^a-zA-Z0-9\-_\.]+/", "_", 
                                     $configName);
          $configFile = $SOURCE->toPath($configName);
          copy(SOURCE_TEMPLATE, $configFile);
     }

//
// update configuration 
//
     $LOCATION = determine_type($vars['ELEMENT-TYPE']);
     $CONFIGURATION = $LOCATION->getConfiguration($configName);

//
// fix binary elements
//
     $fields = Array();
     $fields[] = 'POST_REQUESTS';
     $fields[] = 'DETECTED_STANDARD_NAMES';
     foreach ($fields as $key => $name) {
          if (!array_key_exists($name, $vars)) {
               $vars[$name] = 'false';
          } else {
               $vars[$name] = getBooleanStringFromArg($vars[$name]);
          }
     }

//
// set values in the configuration
//
     setConfigurationFromArgs($CONFIGURATION,'DESCRIPTION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'RETS_SERVER_URL', $vars);
     setConfigurationFromArgs($CONFIGURATION,'APPLICATION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'VERSION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DETECTED_DEFAULT_RETS_VERSION', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DETECTED_SERVER_NAME', $vars);
     setConfigurationFromArgs($CONFIGURATION,'RETS_SERVER_ACCOUNT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'RETS_SERVER_PASSWORD', $vars);
     setConfigurationFromArgs($CONFIGURATION,'RETS_CLIENT_PASSWORD', $vars);
     setConfigurationFromArgs($CONFIGURATION,'POST_REQUESTS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SELECTION_RESOURCE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SELECTION_CLASS', $vars);

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $nextScreen = 'SOURCE_MENU';
     $mode = null;
     if (array_key_exists('MODE', $vars)) { 
          if ($vars['MODE'] == 'PASSTHRU') { 
               $nextScreen = 'SOURCE_AUTO_DETECT';
               $mode = '&MODE=PASSTHRU';
          }
     }

     $url = $SCREEN[$nextScreen] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
            $mode;

    locate_next_screen($url);
}

//--------------------

//
// build list of existing sources 
//
$existing_sources = $SOURCE->getExisting();
$existing = null;
if ($existing_sources != null) {
     foreach ($existing_sources as $name => $path) {
          $CONFIGURATION = $SOURCE->getConfiguration($name);
          $desc = $CONFIGURATION->getValue('DETECTED_SERVER_NAME');
          $existing[$name] = $desc;
     }
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface', true, true);

$FORMATTER = new AjaxFormatter();

if (array_key_exists('MODE', $vars)) {
     print($FORMATTER->formatHiddenField('MODE', $vars['MODE']));
     if ($vars['MODE'] == 'UPDATE') {
          print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
          print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));
          print($FORMATTER->renderStartFrame(null, null, null) .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel('Change a SOURCE') .
                $FORMATTER->renderStartInnerFrame() .
                create_ajax_target(true) .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                $FORMATTER->renderEndFrame());
     } else {
          print($FORMATTER->renderStartFrame(null, null, null) .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel('Create a SOURCE') .
                $FORMATTER->renderStartInnerFrame() .
                create_ajax_target() .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                $FORMATTER->renderEndFrame());
     }
} else {
     print($FORMATTER->formatHiddenField('MODE', 'PASSTHRU'));
     print($FORMATTER->renderStartFrame(null, null, null) .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel('Create a SOURCE') .
           $FORMATTER->renderStartInnerFrame() .
           create_ajax_target() .
           $FORMATTER->renderEndInnerFrame() .
           $FORMATTER->renderEndPanel() .
           $FORMATTER->renderEndFrameItem() .
           $FORMATTER->renderEndFrame());
}

$HTML->finish();

//
//------------

?>
