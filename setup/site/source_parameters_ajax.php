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
/*
     $fields = Array();
     $fields[] = 'A_BINARY_ITEM';
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
     setConfigurationFromArgs($CONFIGURATION,'QUERY_ITEMS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SUMMARY_ITEMS', $vars);
     setConfigurationFromArgs($CONFIGURATION,'OWNERSHIP_VARIABLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DATE_VARIABLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'RESTRICTED_INDICATOR', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MEDIA_TYPE', $vars);

//-----------

     $resource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
     $class = $CONFIGURATION->getValue('SELECTION_CLASS');
     $METADATA_CLASS = new ClassMetadata($configName, $resource);
     $standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
     $systemClass = $METADATA_CLASS->getSystemClass($class, $standardNames);
     $METADATA_TABLE = new TableMetadata($configName, $systemClass);
     $METADATA_TABLE->read();

//
// populate UI forms with QUERY_ITEMS that are Lookups
//
     $translationTable = $METADATA_TABLE->findNames($standardNames, true);
     $names = $CONFIGURATION->getValue('QUERY_ITEMS');
     $fields = explode(',', $names);
     foreach ($fields as $num => $visible_name) 
     {
          $lookupName = $METADATA_TABLE->findLookupName($visible_name, $standardNames);
          if ($lookupName != null)
          {
               $variable_name = $CONFIGURATION->ensureLegalVariablename('$' . $visible_name) .  '_FORM';
               $lookupType = $METADATA_TABLE->findLookupType($visible_name, $standardNames);
               $METADATA_LOOKUP = new LookupTypeMetadata($configName, $lookupName);
               if ($METADATA_LOOKUP->exists())
               {
                         $data = $METADATA_LOOKUP->asArray($lookupType);
                         $field_name = '$' . $variable_name;
                         $aVar = $field_name . '=array(';
                         foreach ($data as $key => $value) 
                         {
                              $aVar .= '"' .  $key .  '"=>"' .  $value .  '",';
                         }
                         $aVar = substr($aVar, 0, strlen($aVar) - 1) . ');';
                         $CONFIGURATION->setVariable($field_name, $aVar);
               }
          }
     }

//
// save configuration
//
     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $nextScreen = null;
     $message = null;
     switch ($vars['MODE'])
     {
          case 'OVERRIDE':
               $nextScreen = 'SOURCE_MENU';
               break;

          case 'PASSTHRU':
               $nextScreen = 'SETUP_INDEX';
               $message = '&MESSAGE=Source [' . $configName . '] defined and created.';
               break;

          case 'UPDATE':
               $nextScreen = 'SOURCE_MENU';
               break;

     }

     $url = $SCREEN[$nextScreen] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
            $message;

     locate_next_screen($url);
}

//--------------------

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

$FORMATTER = new AjaxFormatter();

if (array_key_exists('MODE', $vars))
{
     print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
     print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));
     print($FORMATTER->formatHiddenField('MODE', $vars['MODE']));
}

print($FORMATTER->renderStartFrame(null, null, null) .
      $FORMATTER->renderStartFrameItem() .
      $FORMATTER->renderStartPanel('Parameters that control Extracts') .
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
