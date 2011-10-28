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

if (array_key_exists('CANCEL', $vars))
{
     locate_next_screen($SCREEN['SETUP_INDEX']);
}

$SOURCE = new Source();

if (array_key_exists('SELECT-ONLY', $vars))
{
//
// make sure there are no arrays
//
     foreach ($vars as $key => $value)
     {
          if (is_array($value))
          {
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
     $fields[] = 'DISPLAY_PERFORMANCE';
     $fields[] = 'DISPLAY_RETS';
     $fields[] = 'DISPLAY_PROVIDER_NOTICE';
     $fields[] = 'DISPLAY_ACCOUNT';
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
     setConfigurationFromArgs($CONFIGURATION,'DISPLAY_PERFORMANCE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DISPLAY_RETS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DISPLAY_PROVIDER_NOTICE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DISPLAY_ACCOUNT', $vars);

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $url = $SCREEN['SOURCE_MENU'] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'];

     locate_next_screen($url);
}

//--------------------

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

$FORMATTER = new AjaxFormatter();

print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));

print($FORMATTER->renderStartFrame(null, null, null) .
      $FORMATTER->renderStartFrameItem() .
      $FORMATTER->renderStartPanel('Additional Information Displays') .
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
