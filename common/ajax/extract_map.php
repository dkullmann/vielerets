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

function ajax_processValue($aName,
                           $aValue,
                           $env = null) {

     $trace = null;

// $trace = print_r($env, true);
//     $trace = 'name ' . $aName . ' value ' . $aValue;
//return '<HTML><![CDATA[' . $trace . ']]></HTML>';

//
// lookup the map stored in the configuration 
//
     $LOCATION = determine_type($env['ELEMENT-TYPE']);
     $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
     $TARGET = new Target();
     $T_CONFIGURATION = $TARGET->getConfiguration($CONFIGURATION->getValue('TARGET'));
     $SOURCE = new Source();
     $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
     $temp = $CONFIGURATION->getVariable('MAP');
     if ($temp == null) {
//
// set all fields in the map to null
//
          $temp['TARGET'] = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
          $list = null;
          foreach ($temp['TARGET'] as $key => $value) {
               $list[] = NO_VALUE_INDICATOR;
          }
          $temp['SOURCE'] = $list;
     }
     $rightMap = $temp['SOURCE'];
     $leftMap = $temp['TARGET'];

//
// override the configuration if necessary 
//
     if (array_key_exists('RIGHT_MAP',$env)) {
          $rightMap = $env['RIGHT_MAP'];
     }

     if (array_key_exists('LEFT_MAP',$env)) {
          $leftMap = $env['LEFT_MAP'];
     }

     $blockSubmit = false;
     $items = null;

     $FORMATTER = new AjaxFormatter();

//
// field selection 
//
     $type = $T_CONFIGURATION->getValue('TYPE');
     if ($type == 'OR' || $type == 'RDB') {

//
// check data map sanity
//
          if (!is_array($rightMap)) {
               $rightMap = explode(',', $rightMap);
          }
          if (!is_array($leftMap)) {
               $leftMap = explode(',', $leftMap);
          }
          if (sizeof($rightMap) != sizeof($leftMap)) {
               $rightMap = null;
               foreach ($leftMap as $key => $value) {
                    $rightMap[$key] = NO_VALUE_INDICATOR;
               }
          }

//
// read information from the source configuration
//
          $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
          $className = $S_CONFIGURATION->getValue('SELECTION_CLASS');
          $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(), $resource);
          $systemClass = $METADATA_CLASS->getSystemClass($className, false);
          $METADATA_TABLE = new TableMetadata($S_CONFIGURATION->getName(), $systemClass);
          $detectedStandardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);

//
// translate option list
//
          $options = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
          $options2 = null;
          $options2[NO_VALUE_INDICATOR] = '** Not Used **';
          $options2[META_COLUMN_INDICATOR] = '** MetaColumn **';
          foreach ($options as $key => $value) {
               if (array_key_exists($value, $trans)) {
                    $options2[$value] = $trans[$value];
               }
          }

//
// mark options non-displayable
//
          $nd_list = $METADATA_TABLE->findDisplayFields($detectedStandardNames, true);
          foreach ($nd_list as $key => $value) {
               if (array_key_exists($key, $trans)) {
                    foreach ($options as $key2 => $value2) {
                         if ($value2 == $key) {
                              $options[$key2] .= ' (not displayable)';
                         }
                    }
                    foreach ($options2 as $key2 => $value2) {
                         if ($value2 == $trans[$key]) {
                              $options2[$key2] .= ' (not displayable)';
                         }
                    }
               }
          }
          asort($options2);

          $unique_key = $S_CONFIGURATION->getValue('UNIQUE_KEY');
          if ($type == 'RDB') {
//
// notations for unique
//
               $notedFieldsMissing = false;
               $notation = 'Defined in the TARGET as the unique identifier';
               $notational = null;
               $dataTableKey = $T_CONFIGURATION->getValue('DATA_TABLE_KEY'); 
               foreach ($leftMap as $key => $value) {
                    if ($dataTableKey == $value) {
                         $notational[$key] = true;
                         $rightMap[$key] = $unique_key;
                    }
               }
          } else {
//
// check if column list is current
// 
               $availableColumns = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
               if (sizeof($leftMap) != sizeof($availableColumns) ) {
                    $changeMessage = 'Map is being rebuilt because at least one field was ';
                    if (sizeof($leftMap) > sizeof($availableColumns) ) {
                         $changeMessage .= 'removed from';
                    } else {
                         $changeMessage .= 'added to';
                    }
                    $changeMessage .= ' the TARGET';
$items[] = $FORMATTER->STYLIST->formatBoldText($changeMessage, 'red');
                    $newTarget = null;
                    $newSource = null;
                    foreach ($availableColumns as $key => $value) {
                         $found = false;
                         foreach ($leftMap as $key1 => $value1) {
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
                              $newSource[] = $rightMap[$key]; 
                         } 
                    }
                    $rightMap = $newSource;
                    $leftMap = $newTarget;
               }

//
// notations required by OR 
//
               $notedFieldsMissing = true;
               $notation = 'Required fields';
               $notational = null;
               $temp = explode(',', $T_CONFIGURATION->getValue('REQUIRED_LIST'));
               foreach ($leftMap as $key => $value) {
                    foreach ($temp as $key2 => $value2) {
                         if ($value == $value2) {
                              $notational[$key] = true;
                         }
                    }
                    if ($value == 'mls') {
                         $notational[$key] = true;
                         $rightMap[$key] = $unique_key;
                    }
               }
          }

// reset the value
          $pos = strpos($env['viele_last'], 'SOA_BUTTON__');
          if ($pos === false) {
          } else {
               $search = $env['viele_last'];
               if (array_key_exists($search, $env)) {
                    $newValue = $env[$search];
                    $anIndex = $env[$search . '_INDEX'];
                    $rightMap[$anIndex] = $newValue;
               }
          }

// map elements
          $selection = null;
          if (array_key_exists('SOA_ACTION',$env)) {
               $selection = $env['SOA_ACTION'];
          }
	  $items[] = '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white"><tr><td>' .
                     $FORMATTER->formatMapElement($type, 
                                                  $leftMap,
                                                  'RETS Server', 
                                                  $rightMap,
                                                  $options2,
                                                  $selection,
                                                  $notational,
                                                  $notation,
                                                  $notedFieldsMissing) .
	             '</td></tr></table>';

          $items[] = $FORMATTER->formatHiddenField('RIGHT_MAP', implode($rightMap,','));
          $items[] = $FORMATTER->formatHiddenField('LEFT_MAP', implode($leftMap,','));
     }

     $overrideSubmit = null;
     if ($blockSubmit) {
          $overrideSubmit = $FORMATTER->formatPageSubmit('Connect', 'SOA_CONNECT');
     }
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $env['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $env['ELEMENT']);


//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('EXTRACT_MAP'), $items, $overrideSubmit) .
            ']]></HTML>';
}

//
//------------

?>
