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
include_once(COMMON_DIRECTORY . '/map_editor.php');
include_once(AJAX_DIRECTORY . '/ajaxFormatter.php');

//--------------------
//
// create a default return location
//

if (array_key_exists('CANCEL', $vars)) {
     locate_next_screen($SCREEN['SETUP_INDEX']);
}

$EXTRACT = new EXTRACT();

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
     if (!$EXTRACT->exists($configName)) {
//
// guard against illegal names 
//
          $configName = preg_replace("/[^a-zA-Z0-9\-_\.]+/", "_", 
                                     $configName);
          $configFile = $EXTRACT->toPath($configName);
          copy(EXTRACT_TEMPLATE, $configFile);
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
     $fields[] = 'REFRESH';
     $fields[] = 'MLS_ONLY';
     $fields[] = 'TRACE';
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
     setConfigurationFromArgs($CONFIGURATION,'SOURCE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'TARGET', $vars);
     setConfigurationFromArgs($CONFIGURATION,'USER', $vars);
     setConfigurationFromArgs($CONFIGURATION,'REFRESH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MLS_ONLY', $vars);
     setConfigurationFromArgs($CONFIGURATION,'CLASS_NAME_STYLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'TRACE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'BATCH_SIZE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'MAX_IMAGE_COUNT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'COLUMN_LIST', $vars);
     setConfigurationFromArgs($CONFIGURATION,'WORKING_FILE_PATH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'CACHE_SIZE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'LIMIT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'STATUS_VARIABLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'STATUS_VARIABLE_VALUE', $vars);

     $message = null;
     $nextScreen = 'EXTRACT_MAP';
     $mode = null;
     if (array_key_exists('MODE', $vars)) {
          $mode = '&MODE=' . $vars['MODE'];
     }


     $TARGET = new Target();
     $T_CONFIGURATION = $TARGET->getConfiguration($CONFIGURATION->getValue('TARGET'));
     $type = $T_CONFIGURATION->getValue('TYPE');

     if ($type == 'OR' || $type == 'RDB') {
          $autoCreate = $T_CONFIGURATION->getBooleanValue('AUTO_CREATE');
          if ($autoCreate) {
//
// lookup metadata 
//
               $SOURCE = new Source();
               $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
               $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
               $class = $S_CONFIGURATION->getValue('SELECTION_CLASS');
               $METADATA_CLASS = new ClassMetadata($CONFIGURATION->getValue('SOURCE'), $resource);
               $standardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
               $systemClass = $METADATA_CLASS->getSystemClass($class, $standardNames);
               $METADATA_TABLE = new TableMetadata($vars['ELEMENT'], $systemClass);
               $trans = $METADATA_TABLE->findDBNames($standardNames, true);
               $items = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
               $complete = true;
               foreach ($items as $key => $value) {
                    if ($trans[$value] == null) {
                         $complete = false;
                    }
               }

//
// create mapping of DBNames
//
               $uniqueKey = $S_CONFIGURATION->getValue('UNIQUE_KEY');
               $temp_s = null;
               $temp_t = null;
               if ($complete) {
                    foreach ($items as $key => $value) {
                         $temp_s[] = $value;
                         $temp_t[] = 'C_' . $trans[$value];
                         if ($value == $uniqueKey) {
                              $T_CONFIGURATION->setValue('DATA_TABLE_KEY', 'C_' . $trans[$value]);
                         }
                    }
               } else {
//
// create mapping of SystemNames or StandardNames
//
                    foreach ($items as $key => $value) {
                         $temp_s[] = $value;
                         $temp_t[] = 'C_' . $value;
                    }
                    $T_CONFIGURATION->setValue('DATA_TABLE_KEY', 'C_' . $uniqueKey);
               }

//
// create data map
//
               $MAP_EDITOR = new MapEditor('MAP');
               $matrix = $CONFIGURATION->getVariable('MAP');
               $matrix['TARGET'] = $temp_t;
               $matrix['SOURCE'] = $temp_s;
               $variable = $MAP_EDITOR->asVariable($matrix);
               if ($variable != null) {
                    $CONFIGURATION->setVariable('$' . 'MAP', $variable);
               }
               $T_CONFIGURATION->setValue('COLUMN_LIST', implode(',', $temp_t));

//
// create image map
//
               if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES') ) {
                    $MAP_EDITOR = new MapEditor('IMAGE_MAP');
                    $matrix = $CONFIGURATION->getVariable('IMAGE_MAP');
                    $temp_s = null;
                    $temp_s[0] = 'ID';
                    $temp_s[1] = 'INDEX';
                    $temp_s[2] = 'URL';
                    $temp_s[3] = 'PATH';
                    $temp_t = null;
                    $temp_t[0] = 'C_' . $uniqueKey;
                    $temp_t[1] = 'C_INDEX';
                    $temp_t[2] = 'C_URL';
                    $temp_t[3] = 'C_PATH';
                    $matrix['TARGET'] = $temp_t;
                    $matrix['SOURCE'] = $temp_s;
                    $variable = $MAP_EDITOR->asVariable($matrix);
                    if ($variable != null) {
                         $CONFIGURATION->setVariable('$' . 'IMAGE_MAP', $variable);
                    }
                    $T_CONFIGURATION->setValue('IMAGE_TABLE_KEY', 'C_' . $uniqueKey);
                    $T_CONFIGURATION->setValue('IMAGE_COLUMN_LIST', implode(',', $temp_t));
               }
               $T_LOCATION = determine_type('TARGET');
               $T_LOCATION->saveConfiguration($T_CONFIGURATION, $CONFIGURATION->getValue('TARGET'));
               $nextScreen = 'SETUP_INDEX';
               $message = '&MESSAGE=Extract [' . $configName . '] defined and created.';
               $mode = null;
          } else {
               $SOURCE = new Source();
               $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
//
// create image map
//
               if ($type == 'RDB') {
                    if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES') ) {
                         $MAP_EDITOR = new MapEditor('IMAGE_MAP');
                         $matrix = $T_CONFIGURATION->getVariable('IMAGE_MAP');
                         $matrix['TARGET'] = explode(',', $T_CONFIGURATION->getValue('IMAGE_COLUMN_LIST'));
                         $matrix['SOURCE'] = getMapFromArgs('RIGHT_IMAGE_MAP', $vars);
                         $variable = $MAP_EDITOR->asVariable($matrix);
                         if ($variable != null) {
                              $CONFIGURATION->setVariable('$' . 'IMAGE_MAP', $variable);
                         }
                    }
               }

               $options = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
	       if (sizeof($options) < FAST_MAP_THRESHOLD) {
//
// create data map
//
                    $MAP_EDITOR = new MapEditor('MAP');
                    $matrix = $T_CONFIGURATION->getVariable('MAP');
                    $matrix['TARGET'] = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
                    $matrix['SOURCE'] = getMapFromArgs('RIGHT_MAP', $vars);
                    $variable = $MAP_EDITOR->asVariable($matrix);
                    if ($variable != null) {
                         $CONFIGURATION->setVariable('$' . 'MAP', $variable);
                    }

//
// create metaColumns
//
                    $metaColumn = getMapFromArgs('TEMPLATE_MAP', $vars);
                    if ($metaColumn != null) {
                         $CONFIGURATION->setVariable('$' . 'METACOLUMN_MAP', $metaColumn);
                    } 

//
// bypass other screens and return to main menu
//
                    $nextScreen = 'SETUP_INDEX';
                    if ($mode = 'UPDATE') {
                         $message = '&MESSAGE=Extract [' . $configName . '] changed.';
                    } else {
                         $message = '&MESSAGE=Extract [' . $configName . '] defined and created.';
                    }
                    $mode = null;
               } else {
                    if ($mode = 'UPDATE') {
                         $nextScreen = 'EXTRACT_MENU';
                         $mode = null;
                    }
               }
          }
     }

     if ($type == 'CSV' || $type == 'XML') {
          $MAP_EDITOR = new MapEditor('METACOLUMN_MAP');
          $CONFIGURATION->setVariable('$' . 'METACOLUMN_MAP', $MAP_EDITOR->nullVariable());
          $MAP_EDITOR = new MapEditor('MAP');
          $CONFIGURATION->setVariable('$' . 'MAP', $MAP_EDITOR->nullVariable());
          $MAP_EDITOR = new MapEditor('IMAGE_MAP');
          $CONFIGURATION->setVariable('$' . 'IMAGE_MAP', $MAP_EDITOR->nullVariable());
          $nextScreen = 'SETUP_INDEX';
          $message = '&MESSAGE=Extract [' . $configName . '] defined and created.';
          $mode = null;
     }

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $url = $SCREEN[$nextScreen] .
            '?ELEMENT=' . $configName .
            '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
            $mode .
            $message;
     locate_next_screen($url);
}

//--------------------

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

$FORMATTER = new AjaxFormatter();

if (array_key_exists('MODE', $vars)) {
     print($FORMATTER->formatHiddenField('MODE', $vars['MODE']));
     if ($vars['MODE'] == 'UPDATE') {
          print($FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']));
          print($FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']));
          print($FORMATTER->renderStartFrame(null, null, null) .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel('Change an Extract') .
                $FORMATTER->renderStartInnerFrame() .
                create_ajax_target(true) .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                $FORMATTER->renderEndFrame());
     } else {
          print($FORMATTER->renderStartFrame(null, null, null) .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel('Create an Extract') .
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
           $FORMATTER->renderStartPanel('Create an Extract') .
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
