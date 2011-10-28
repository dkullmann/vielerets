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

//
// select only mode
//
if (array_key_exists('SELECT-ONLY', $vars)) {
     if (array_key_exists('CANCEL', $vars)) {
          locate_next_screen($SCREEN[$vars['CANCEL-LOCATION']] .
                             '?ELEMENT=' . $vars['ELEMENT']);
     } else {
          if (!array_key_exists('CLEAR', $vars) &&
              !array_key_exists('ALL', $vars)) {
               if (array_key_exists('FIELD', $vars)) {

//
// update configuration 
//
                    $LOCATION = determine_type($vars['ELEMENT-TYPE']);
                    $CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);
                    if (array_key_exists('CLEANUP', $vars)) {
                         $matrix = $CONFIGURATION->getVariable($vars['FIELD']);
                         $matrix['SOURCE'] = $vars['SOURCE'];

//
// reset variable if a new one is present
//
                         $MAP_EDITOR = new MapEditor($vars['FIELD']);
                         $variable = $MAP_EDITOR->asVariable($matrix);
                         if ($variable != null) {
                              $CONFIGURATION->setVariable('$' . $vars['FIELD'], $variable);
                         }
                         $url = $SCREEN[$vars['PASSTHRU']] . 
                                '?ELEMENT=' . $vars['ELEMENT'] .
                                '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                                '&FIELD=' . $vars['FIELD'];
                    } else {
//
// pull together an array of arguments  
//
                         $selected = null;
                         $field_array = explode(',', $vars['UNIVERSE']);
                         foreach ($field_array as $num => $item) {
                              if (array_key_exists($item, $vars)) {
                                   $selected[] = $item;
                              }
                         }
                         $value = '';
                         if ($selected != null) {
                              $value = implode(',', $selected);
                         }
                         $CONFIGURATION->setValue($vars['FIELD'], $value);
                         $url = $SCREEN['EXTRACT_MENU'] . 
                                     '?ELEMENT=' . $vars['ELEMENT'] .
                                     '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'];
                    }
                    $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

                    locate_next_screen($url);
               }
          }
     }
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// setup classes 
//
$EXTRACT = new Extract();
$CONFIGURATION = $EXTRACT->getConfiguration($vars['ELEMENT']);

$source = $CONFIGURATION->getValue('SOURCE');
$SOURCE = new Source();
$S_CONFIGURATION = $SOURCE->getConfiguration($source);

$target = $CONFIGURATION->getValue('TARGET');
$TARGET = new Target();
$T_CONFIGURATION = $TARGET->getConfiguration($target);

//
// using view.php 
//
$bottom_message = 'Editing extract [' . $vars['ELEMENT'] . ']';

//
// generate human friendly field translations
//
$resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
$class = $S_CONFIGURATION->getValue('SELECTION_CLASS');
$METADATA_CLASS = new ClassMetadata($source, 
                                    $resource);
$standardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
$systemClass = $METADATA_CLASS->getSystemClass($class,
                                               $standardNames);

$METADATA = new TableMetadata($source, 
                              $systemClass);
$trans = $METADATA->findNames($standardNames, true);
$nd_list = $METADATA->findDisplayFields($standardNames, true);
//print_r($trans);
//print('<br/>');
//print_r($nd_list);

//
// determine mapping type
//
$type = $T_CONFIGURATION->getValue('TYPE');

//
// type dependent processing
//
$FORMATTER = new TableFormatter();
$items[] = null;

//
// setup editor 
//
     $fieldName = 'MAP';
     $MAP_EDITOR = new MapEditor($fieldName);
     $MAP_EDITOR->setValueColumnName($source);
     $MAP_EDITOR->setKeyColumnName($target);

//
// check if this is expert level
//
     if (array_key_exists('LEVEL', $vars)) {
          if ($vars['LEVEL'] = 'EXPERT') {
               $MAP_EDITOR->setExpertLevel(true);
          }
     }

//
// get the universe of values for the right side
//
     $universe = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));

//
// prepare SOURCE 
//
     $source_matrix = $CONFIGURATION->getVariable($fieldName);
     if ($source_matrix == null) {
//
// set all fields in the map to null
//
          $source_matrix['TARGET'] = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
          $list = null;
          foreach ($universe as $key => $value) {
               $list[] = null;
          }
          $source_matrix['SOURCE'] = $list;
     } else {
          if ($type == 'OR') {
//
// check if column list is current
// 
               $existingColumns = $source_matrix['TARGET'];
               $availableColumns = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
               if (sizeof($existingColumns) != sizeof($availableColumns) ) {
                    $changeMessage = 'Map is being rebuilt because at least one field was ';
                    if (sizeof($existingColumns) > sizeof($availableColumns) ) {
                         $changeMessage .= 'removed from';
                    } else {
                         $changeMessage .= 'added to';
                    }
                    $changeMessage .= ' the TARGET';
$items[] = $FORMATTER->STYLIST->formatBoldText($changeMessage, 'red');
                    $existingSource = $source_matrix['SOURCE'];
                    $newTarget = null;
                    $newSource = null;
                    foreach ($availableColumns as $key => $value) {
                         $found = false;
                         foreach ($existingColumns as $key1 => $value1) {
                              if ($value == $value1) {
                                   $found = true;
                                   break;
                              }
                         }
                         if (!$found) {
                              $newTarget[] = $value; 
                              $newSource[] = NO_VALUE_INDICATOR; 
$items[] = $FORMATTER->STYLIST->formatBoldText('Added Field ', 'red') . 
           ' ' .
           $FORMATTER->STYLIST->formatText($value, 'red');
                         } else {
                              $newTarget[] = $value; 
                              $newSource[] = $existingSource[$key]; 
                         } 
                    }
                    $source_matrix['SOURCE'] = $newSource;
                    $source_matrix['TARGET'] = $newTarget;
               }
          }
     }

//
// hard wire the UNIQUE_KEY to the key column
//
     $data_table_key = null; 
     $uniqueColumn = null;
     $unique_key = $S_CONFIGURATION->getValue('UNIQUE_KEY');
     if ($type == 'RDB') { 
          $data_table_key = $T_CONFIGURATION->getValue('DATA_TABLE_KEY');
          if ($data_table_key != null) {
               $list = $source_matrix['SOURCE'];
               $target_list = $source_matrix['TARGET'];
               for ($i = 0; $i < sizeof($target_list); ++$i) {
                    $candidate = $target_list[$i];
                    if ($candidate == $data_table_key) {
                         $unique_column = $i;
                    }
               }
               $list[$unique_column] = $unique_key;
               $source_matrix['SOURCE'] = $list;
               $MAP_EDITOR->setStateText('* Defined in the TARGET for the unique identifier');
          }
     }
     if ($type == 'OR') { 
          $list = $source_matrix['SOURCE'];
          $target_list = $source_matrix['TARGET'];
          for ($i = 0; $i < sizeof($target_list); ++$i) {
               $candidate = $target_list[$i];
               if ($candidate == 'mls') {
                    $unique_column = $i;
               }
          }
          $list[$unique_column] = $unique_key;
          $source_matrix['SOURCE'] = $list;
     }

//
// create column titles 
//
     $column_titles['TARGET'] = $T_CONFIGURATION->getValue('DESCRIPTION'); 
     $column_titles['SOURCE'] = 'RETS Server'; 

//
// check for required fields
//
     $required_matrix['SOURCE'] = null; 
     $list = null;

     if ($data_table_key != null) {
          $target_list = $source_matrix['TARGET'];
          $list[$target_list[$unique_column]] = true;
     }

     $temp = explode(',', $T_CONFIGURATION->getValue('REQUIRED_LIST'));
     foreach ($temp as $key => $value) {
          $list[$value] = true;
     }

//
// add the mls number as a required field for OR
//
     if ($type == 'OR') {
          $list['mls'] = true;
     }
     $required_matrix['TARGET'] = $list;

//
// write out variable to the config file as a template
//
     $CONFIGURATION->setVariable('$' . $fieldName, 
                                 $MAP_EDITOR->asVariable($source_matrix));
     $EXTRACT->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

//
// use Meta Columns if appropriate
//
     $metaColumnIndicator = null;
     if ($EXTRACT->supportsMetaColumns()) {
          $metaColumnIndicator = $EXTRACT->getMetaColumnIndicator();
     }

//
// render
//
     $items[] = $MAP_EDITOR->render($FORMATTER,
                                    $source_matrix,
                                    $column_titles,
                                    $required_matrix,
                                    $universe,
                                    $trans,
                                    $metaColumnIndicator);

     $items[] = $FORMATTER->formatHiddenField('CLEANUP', TRUE);
     $items[] = $FORMATTER->formatHiddenField('FIELD', $fieldName);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'EXTRACT');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'EXTRACT_MENU');
     $items[] = $FORMATTER->formatHiddenField('CANCEL-LOCATION', 'EXTRACT_MENU');

     if ($MAP_EDITOR->getExpertLevel()) {
          $message = 'WARNING: No Validation for Experts';
     } else {
          $message = 'Map the Relationship';
     }
     $FORMATTER->printForm($items, 
                           $SCREEN['EXTRACT_MAP_ORIG'], 
                           $bottom_message,
                           $message,
                           false,
                           false,
                           false,
                           null,
                           null,
                           true);

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
