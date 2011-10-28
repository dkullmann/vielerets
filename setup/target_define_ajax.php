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

$TARGET = new Target();

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
     if (!$TARGET->exists($configName)) {
//
// guard against illegal names 
//
          $configName = preg_replace("/[^a-zA-Z0-9\-_\.]+/", "_", 
                                     $configName);
          $configFile = $TARGET->toPath($configName);
          copy(TARGET_TEMPLATE, $configFile);
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
     $fields[] = 'AUTO_CREATE';
     $fields[] = 'IMAGE_REFERENCE_ONLY';
     $fields[] = 'IMAGE_ENCODED_URL';
     $fields[] = 'INCLUDE_IMAGES';
     $fields[] = 'THUMBNAILS';
     $fields[] = 'GD_VERSION_2';
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
     setConfigurationFromArgs($CONFIGURATION,'TYPE', $vars);

     $type = $CONFIGURATION->getValue('TYPE');
     switch ($type) {
          case 'BASE':
               break;

          case 'CSV':
               break;

          case 'OR':
               setConfigurationFromArgs($CONFIGURATION,'CLASS_TABLE', $vars);
               setConfigurationFromArgs($CONFIGURATION,'CLASS_LISTING_TABLE', $vars);
               setConfigurationFromArgs($CONFIGURATION,'DAYS_UNTIL_EXPIRATION', $vars);
               setConfigurationFromArgs($CONFIGURATION,'GD_VERSION_2', $vars);
               setConfigurationFromArgs($CONFIGURATION,'IMAGE_UPLOAD_PATH', $vars);
               setConfigurationFromArgs($CONFIGURATION,'INDEX_TABLE', $vars);
               setConfigurationFromArgs($CONFIGURATION,'METADATA_TABLE', $vars);
               setConfigurationFromArgs($CONFIGURATION,'PATH_TO_IMAGEMAGICK', $vars);
               setConfigurationFromArgs($CONFIGURATION,'REQUIRED_LIST', $vars);
               setConfigurationFromArgs($CONFIGURATION,'THUMBNAIL_PROGRAM', $vars);
               setConfigurationFromArgs($CONFIGURATION,'TYPE_LIST', $vars);
               setConfigurationFromArgs($CONFIGURATION,'THUMBNAILS', $vars);
               setConfigurationFromArgs($CONFIGURATION,'THUMBNAIL_QUALITY', $vars);
               setConfigurationFromArgs($CONFIGURATION,'THUMBNAIL_WIDTH', $vars);
               setConfigurationFromArgs($CONFIGURATION,'USER_ELEMENT_TABLE', $vars);
               setConfigurationFromArgs($CONFIGURATION,'USER_TABLE', $vars);
               break;

          case 'RDB':
               setConfigurationFromArgs($CONFIGURATION,'AUTO_CREATE', $vars);
               if ($vars['AUTO_CREATE'] == 'false') {
                    setConfigurationFromArgs($CONFIGURATION,'DATA_TABLE_KEY', $vars);
                    setConfigurationFromArgs($CONFIGURATION,'IMAGE_COLUMN_LIST', $vars);
                    setConfigurationFromArgs($CONFIGURATION,'IMAGE_TABLE_KEY', $vars);
               }
               break;

          case 'XML':
               setConfigurationFromArgs($CONFIGURATION,'CONTAINER_NAME', $vars);
               break;

     }

     setConfigurationFromArgs($CONFIGURATION,'ACCOUNT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'BRAND', $vars);
     setConfigurationFromArgs($CONFIGURATION,'CACHE_PATH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'COLUMN_LIST', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DATA_DOWNLOAD_PATH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DATA_TABLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'DATABASE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'FILE_NAME', $vars);
     setConfigurationFromArgs($CONFIGURATION,'FORMAT', $vars);
     setConfigurationFromArgs($CONFIGURATION,'IMAGE_DOWNLOAD_PATH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'IMAGE_ENCODED_URL', $vars);
     setConfigurationFromArgs($CONFIGURATION,'IMAGE_FILE_NAME', $vars);
     setConfigurationFromArgs($CONFIGURATION,'IMAGE_REFERENCE_ONLY', $vars);
     setConfigurationFromArgs($CONFIGURATION,'IMAGE_TABLE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'INCLUDE_IMAGES', $vars);
     setConfigurationFromArgs($CONFIGURATION,'LISTING_DESCRIPTION_TEMPLATE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'METADATA_FIELD', $vars);
     setConfigurationFromArgs($CONFIGURATION,'METADATA_REQUIRED', $vars);
     setConfigurationFromArgs($CONFIGURATION,'METADATA_TYPE', $vars);
     setConfigurationFromArgs($CONFIGURATION,'OPEN_REALTY_INSTALL_PATH', $vars);
     setConfigurationFromArgs($CONFIGURATION,'PASSWORD', $vars);
     setConfigurationFromArgs($CONFIGURATION,'SERVER', $vars);

     $LOCATION->saveConfiguration($CONFIGURATION, $configName);

     $url = $SCREEN['SETUP_INDEX'] .
            '?ELEMENT=' . $configName .
            '&MESSAGE=Target [' . $configName . '] defined and created.';
     locate_next_screen($url);
}

//--------------------

//
// build list of existing targets 
//
$existing_targets = $TARGET->getExisting();
$existing = null;
if ($existing_targets != null) {
     foreach ($existing_targets as $name => $path) {
          $CONFIGURATION = $TARGET->getConfiguration($name);
          $desc = $CONFIGURATION->getValue('TYPE');
          $existing[$name] = $desc;
     }
}

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
                $FORMATTER->renderStartPanel('Change a TARGET') .
                $FORMATTER->renderStartInnerFrame() .
                create_ajax_target(true) .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                $FORMATTER->renderEndFrame());
     } else {
          print($FORMATTER->renderStartFrame(null, null, null) .
                $FORMATTER->renderStartFrameItem() .
                $FORMATTER->renderStartPanel('Create a TARGET') .
                $FORMATTER->renderStartInnerFrame() .
                create_ajax_target() .
                $FORMATTER->renderEndInnerFrame() .
                $FORMATTER->renderEndPanel() .
                $FORMATTER->renderEndFrameItem() .
                $FORMATTER->renderEndFrame());
     }
} else {
     print($FORMATTER->renderStartFrame(null, null, null) .
           $FORMATTER->renderStartFrameItem() .
           $FORMATTER->renderStartPanel('Create a TARGET') .
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
