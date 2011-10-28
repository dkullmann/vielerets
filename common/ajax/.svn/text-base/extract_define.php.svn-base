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

//
// set up defaults
//
     $EXTRACT = new Extract();
     $existing_extracts = sizeof($EXTRACT->getExisting());
     if ($existing_extracts == 0) {
          $element = DEFAULT_CONFIG_NAME;
     } else {
          $element = DEFAULT_CONFIG_NAME . '_' . $existing_extracts;
     }
     $source = '';
     $target = '';
     $user = '';
     $refresh = 'true';
     $mlsOnly = 'false';
     $classNameStyle = '';
     $traceExtract = 'false';
     $batchSize = 20;
     $maxImageCount = 10;
     $columnList = '';
     $workingFilePath = realpath('../..');
     $cacheSize = 2048;
     $limit = 0;
     $rightMap = null;
     $templateMap = null;
     $templateMapIndex = null;
     $rightImageMap = null;
     $metaIndex = null;
     $statusVariable = null;
     $statusVariableValue = null;
     if ($env == null) {
          $env['ELEMENT'] = $element;
          $env['REFRESH'] = $refresh;
          $env['MLS_ONLY'] = $mlsOnly;
          $env['TRACE'] = $traceExtract;
          $env['MAX_IMAGE_COUNT'] = $maxImageCount;
          $env['WORKING_FILE_PATH'] = $workingFilePath;
          $env['CACHE_SIZE'] = $cacheSize;
          $env['LIMIT'] = $limit;
          $env['MODE'] = 'PASSTHRU';
          $env['USER'] = $user;
          $env['RIGHT_MAP'] = $rightMap;
          $env['TEMPLATE_MAP'] = $templateMap;
          $env['TEMPLATE_MAP_INDEX'] = $templateMapIndex;
          $env['RIGHT_IMAGE_MAP'] = $rightImageMap;
     } else {
          if (array_key_exists('viele_mode',$env)) {
               $LOCATION = determine_type($env['ELEMENT-TYPE']);
               $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
               $source = $CONFIGURATION->getValue('SOURCE');
               $target = $CONFIGURATION->getValue('TARGET');
               $user = $CONFIGURATION->getValue('USER');
               $refresh = $CONFIGURATION->getBooleanValue('REFRESH');
               if (!$refresh) {
                    $refresh = 'false';
               } else {
                    $refresh = 'true';
               }
               $mlsOnly = $CONFIGURATION->getBooleanValue('MLS_ONLY');
               if (!$mlsOnly) {
                    $mlsOnly = 'false';
               } else {
                    $mlsOnly = 'true';
               }
               $classNameStyle = $CONFIGURATION->getValue('CLASS_NAME_STYLE');
               $traceExtract = $CONFIGURATION->getBooleanValue('TRACE');
               if (!$traceExtract) {
                    $traceExtract = 'false';
               } else {
                    $traceExtract = 'true';
               }
               $batchSize = $CONFIGURATION->getValue('BATCH_SIZE');
               $maxImageCount = $CONFIGURATION->getValue('MAX_IMAGE_COUNT');
               $columnList = $CONFIGURATION->getValue('COLUMN_LIST');
               $workingFilePath = $CONFIGURATION->getValue('WORKING_FILE_PATH');
               $cacheSize = $CONFIGURATION->getValue('CACHE_SIZE');
               $limit = $CONFIGURATION->getValue('LIMIT');
               $temp = $CONFIGURATION->getVariable('MAP');
               if ($temp != null) {
                    $rightMap = $temp['SOURCE'];
                    $metaIndex = $CONFIGURATION->getVariable('METACOLUMN_MAP');
                    $temp = $CONFIGURATION->getVariable('IMAGE_MAP');
                    if ($temp != null) {
                         $rightImageMap = $temp['SOURCE'];
                    }
               }
               $statusVariable = $CONFIGURATION->getValue('STATUS_VARIABLE');
               $statusVariableValue = $CONFIGURATION->getValue('STATUS_VARIABLE_VALUE');
          }
     }

//
// weight input
//

//     if (array_key_exists('ELEMENT',$env))
//     {
//          $element = $env['ELEMENT'];
//     }

     if (array_key_exists('SOURCE',$env)) {
          $source = $env['SOURCE'];
     }

     if (array_key_exists('TARGET',$env)) {
          $target = $env['TARGET'];
     }

     if (array_key_exists('USER',$env)) {
          $user = $env['USER'];
     }

     if (array_key_exists('REFRESH',$env)) {
          $refresh = $env['REFRESH'];
     }

     if (array_key_exists('MLS_ONLY',$env)) {
          $mlsOnly = $env['MLS_ONLY'];
     }

     if (array_key_exists('CLASS_NAME_STYLE',$env)) {
          $classNameStyle = $env['CLASS_NAME_STYLE'];
     }

     if (array_key_exists('TRACE',$env)) {
          $traceExtract = $env['TRACE'];
     }

     if (array_key_exists('BATCH_SIZE',$env)) {
          $batchSize = $env['BATCH_SIZE'];
     }

     if (array_key_exists('MAX_IMAGE_COUNT',$env)) {
          $maxImageCount = $env['MAX_IMAGE_COUNT'];
     }

     if (array_key_exists('COLUMN_LIST',$env)) {
          if (is_array($env['COLUMN_LIST'])) {
               $columnList = implode(',',$env['COLUMN_LIST']);
          } else {
               $columnList = $env['COLUMN_LIST'];
          }
     }

     if (array_key_exists('WORKING_FILE_PATH',$env)) {
          $workingFilePath = $env['WORKING_FILE_PATH'];
     }

     if (array_key_exists('CACHE_SIZE',$env)) {
          $cacheSize = $env['CACHE_SIZE'];
     }

     if (array_key_exists('LIMIT',$env)) {
          $limit = $env['LIMIT'];
     }

     if (array_key_exists('RIGHT_MAP',$env)) {
          $rightMap = $env['RIGHT_MAP'];
     }

     if (array_key_exists('TEMPLATE_MAP',$env)) {
          $templateMap = $env['TEMPLATE_MAP'];
          $templateMapIndex = $env['TEMPLATE_MAP_INDEX'];
     }

     if (array_key_exists('RIGHT_IMAGE_MAP',$env)) {
          $rightImageMap = $env['RIGHT_IMAGE_MAP'];
     }

     if (array_key_exists('STATUS_VARIABLE',$env)) {
          $statusVariable = $env['STATUS_VARIABLE'];
     }

     if (array_key_exists('STATUS_VARIABLE_VALUE',$env)) {
          $statusVariableValue = $env['STATUS_VARIABLE_VALUE'];
     }

     $FORMATTER = new AjaxFormatter();

     $blockSubmit = false;
     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatSingleEntryField('Name',
                                                   'ELEMENT',
                                                   $env['ELEMENT'],
                                                   32);

//
// build list of sources
//
     $label = 'Read from SOURCE';
     $SOURCE = new Source();
     $result_source = $SOURCE->getExistingForSetup();
     if (sizeof($result_source) == 1) {
          foreach ($result_source as $key => $value) {
               $S_CONFIGURATION = $SOURCE->getConfiguration($key);
               $items[] = $FORMATTER->formatDisplayField($label,
                                                         $S_CONFIGURATION->getValue('DETECTED_SERVER_NAME') . ' (' . $key . ')');
               $items[] = $FORMATTER->formatHiddenField('SOURCE',$key);
          }
     } else {
          $STYLIST = new Stylist();
          $display_source = null;
          $first = null;
          foreach ($result_source as $key => $value) {
               if ($first == null) {
                    $first = $key;
               }
               $S_CONFIGURATION = $SOURCE->getConfiguration($key);
//               $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(),
//                                                   $S_CONFIGURATION->getValue('SELECTION_RESOURCE'));
//               if (!$METADATA_CLASS->isValid()) {
//                    $display_source[$key] = $S_CONFIGURATION->getValue('DETECTED_SERVER_NAME') .
//                                            $FORMATTER->renderError(' (Invalid metadata)');
//               } else {
               $display_source[$key] = $S_CONFIGURATION->getValue('DETECTED_SERVER_NAME');
//               }
          }
          if (strlen($target) == 0) {
               $target = $first;
          }
          if (strlen($source) == 0) {
               $source = $first;
          }
          $items[] = $FORMATTER->formatRadioField($label,
                                                  'SOURCE',
                                                  $source,
                                                  $display_source,
                                                  null,
                                                  true);
          $S_CONFIGURATION = $SOURCE->getConfiguration($source);
     }

//
// list of fields in the SOURCE
//
     $summaryItems = $S_CONFIGURATION->getValue('SUMMARY_ITEMS');

//
// build list of targets
//
     $label = 'Write to TARGET';
     $TARGET = new Target();
     $result_target = $TARGET->getExistingForSetup();
     if (sizeof($result_target) == 1) {
          foreach ($result_target as $key => $value) {
               $T_CONFIGURATION = $TARGET->getConfiguration($key);
               $items[] = $FORMATTER->formatDisplayField($label,
                                                         $T_CONFIGURATION->getValue('DESCRIPTION') . ' (' . $key . ')');
               $items[] = $FORMATTER->formatHiddenField('TARGET',$key);
          }
     } else {
          $display_target = null;
          $first = null;
          foreach ($result_target as $key => $value) {
               if ($first == null) {
                    $first = $key;
               }
               $T_CONFIGURATION = $TARGET->getConfiguration($key);
               $display_target[$key] = $T_CONFIGURATION->getValue('DESCRIPTION');
          }
          if (strlen($target) == 0) {
               $target = $first;
          }
          $items[] = $FORMATTER->formatRadioField($label,
                                                  'TARGET',
                                                  $target,
                                                  $display_target,
                                                  null,
                                                  true);
          $T_CONFIGURATION = $TARGET->getConfiguration($target);
     }
     $type = $T_CONFIGURATION->getValue('TYPE');

//
// make sure SOURCE and TARGET are valid
//
     if (!$SOURCE->isValidConfiguration($S_CONFIGURATION->getName())) {
          $metadataStatus = 'Metadata for this SOURCE is not usable, refresh the metadata';
/*
          $metadataStatus = 'Current metadata is not valid ';
          $EXCHANGE = new Exchange($sourceName);
          if ($EXCHANGE->loginDirect($S_CONFIGURATION->getValue('RETS_SERVER_ACCOUNT'),
                                     $S_CONFIGURATION->getValue('RETS_SERVER_PASSWORD'),
                                     $S_CONFIGURATION->getValue('RETS_SERVER_URL'),
                                     $S_CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION'),
                                     $S_CONFIGURATION->getValue('APPLICATION'),
                                     $S_CONFIGURATION->getValue('VERSION'),
                                     $S_CONFIGURATION->getValue('RETS_CLIENT_PASSWORD'),
                                     $S_CONFIGURATION->getBooleanValue('POST_REQUESTS'))) {
               $EXCHANGE->refreshMetadata($sourceName,
                                          $selectionResource,
                                          $S_CONFIGURATION->getValue('SELECTION_CLASS'),
                                          $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES'));
               $EXCHANGE->logoutDirect();
               $metadataStatus .= 'but was refreshed from the MLS.';
          } else {
               $metadataStatus .= 'and the MLS cannot be contacted.';
          }
*/
          $items[] = $FORMATTER->formatDisplayField('Metadata Status', $metadataStatus, 'red');
          return '<HTML><![CDATA[' .
                 $trace .
                 $FORMATTER->formatPage(localize('MAIN_INDEX'), $items) .
                 ']]></HTML>';
     }
     if (!$TARGET->isValidConfiguration($T_CONFIGURATION->getName())) {
          $targetStatus = 'TARGET is not usable, check the configuration';
          $items[] = $FORMATTER->formatDisplayField('TARGET Status', $targetStatus, 'red');
          return '<HTML><![CDATA[' .
                 $trace .
                 $FORMATTER->formatPage(localize('MAIN_INDEX'), $items) .
                 ']]></HTML>';
     }

//
// Batch Information 
//
     $items[] = $FORMATTER->formatSeparator('Batch Control');

     $items[] = $FORMATTER->formatSingleEntryField('Download Limit',
                                                   'LIMIT',
                                                   $limit,
                                                   4);

     $items[] = $FORMATTER->formatBinaryField('Trace during execution',
                                              'TRACE',
                                              $traceExtract);

     if ($S_CONFIGURATION->getBooleanValue('PAGINATION')) {
          $items[] = $FORMATTER->formatSingleEntryField('Batch Size',
                                                        'BATCH_SIZE',
                                                        $batchSize,
                                                        8);
     } else {
          $items[] = $FORMATTER->formatHiddenField('BATCH_SIZE', $batchSize);

          $items[] = $FORMATTER->formatPathField('Disk Cache file directory',
                                                 'WORKING_FILE_PATH',
                                                 $workingFilePath,
                                                 32);

          $options = null;
          $options['1024'] = '1K';
          $options['2048'] = '2K';
          $options['4096'] = '4K';
          $options['8092'] = '8K';
          $options['16184'] = '16K';
          $options['32368'] = '32K';
          $options['64636'] = '64K';
          $items[] = $FORMATTER->formatRadioField('Cache Size',
                                                  'CACHE_SIZE',
                                                  $cacheSize,
                                                  $options,
                                                  null,
                                                  true,
                                                  true);
     }

//
// Images 
//

     if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES')) {
          $items[] = $FORMATTER->formatSeparator('Images');
          $items[] = $FORMATTER->formatSingleEntryField('Maximum images to download',
                                                        'MAX_IMAGE_COUNT',
                                                        $maxImageCount,
                                                        4);
     } else {
          if ($type == 'OR') {
               $items[] = $FORMATTER->formatSeparator('Images');
               $items[] = $FORMATTER->formatSingleEntryField('Maximum images to download',
                                                             'MAX_IMAGE_COUNT',
                                                             $maxImageCount,
                                                             4);
          } else {
               $items[] = $FORMATTER->formatHiddenField('MAX_IMAGE_COUNT', '0');
          }
     }

     if ($type == 'OR') {

          $items[] = $FORMATTER->formatSeparator('Target Specific');

//
// refresh
//
          $items[] = $FORMATTER->formatBinaryField('Refresh if duplicate',
                                                   'REFRESH',
                                                   $refresh);

//
// lookup a list of user names
//
          $conn = ADONewConnection($T_CONFIGURATION->getValue('BRAND'));
          @$conn->PConnect($T_CONFIGURATION->getValue('SERVER'),
                          $T_CONFIGURATION->getValue('ACCOUNT'),
                          $T_CONFIGURATION->getValue('PASSWORD'),
                          $T_CONFIGURATION->getValue('DATABASE'));
          $user_id = $T_CONFIGURATION->getValue('USERID_FIELD');
          $user_name = $T_CONFIGURATION->getValue('USERID_NAME');
          $sql = 'SELECT ' . $user_id . ',' . $user_name .
                ' FROM ' . $T_CONFIGURATION->getValue('USER_TABLE');
          $recordSet = $conn->Execute($sql);
          if ($recordSet === false) {
               echo 'ERROR: '.$sql;
          }
          $user_table = null;
          while (!$recordSet->EOF) {
               $t_user_id = $recordSet->fields[$user_id];
               $t_user_name = $recordSet->fields[$user_name];
               $user_table[$t_user_id] = $t_user_name;
               $recordSet->MoveNext();
          }
          $conn->Close();
          if (sizeof($user_table) == 1) {
               $items[] = $FORMATTER->formatDisplayField('Listings Owner',
                                                         $t_user_name);
               $items[] = $FORMATTER->formatHiddenField('USER',$t_user_id);
          } else {
               $items[] = $FORMATTER->formatRadioField('Listings Owner',
                                                       'USER',
                                                       $user,
                                                       $user_table,
                                                       null,
                                                       true);
          }

//
// is Class standardName, visibleName, description or systemName
//
          $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
          $className = $S_CONFIGURATION->getValue('SELECTION_CLASS');

//
// if standard names, find class system name
//
          $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(), $resource);
          $standardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          if ($standardNames) {
               $result = $METADATA_CLASS->contentsAsString();
               $translationParser = new TranslationParser();
               $classTable = $translationParser->parse($result,
                                                       'StandardName',
                                                       'ClassName',
                                                       'METADATA-CLASS');
               $className = $classTable[$className];
          }

//
// read class metadata to create options
//
          $METADATA_CLASS->read();

//
// create options
//
          $options = null;
          $aName = $METADATA_CLASS->findField($className, 'VisibleName');
          if ($aName != null) {
               $options['VisibleName'] = $aName;
          }
          $aName = $METADATA_CLASS->findField($className, 'Description');
          if ($aName != null) {
               $options['Description'] = $aName;
          }
          $aName = $METADATA_CLASS->findField($className, 'StandardName');
          if ($aName != null) {
               $options['StandardName'] = $aName;
          }
          $aName = $METADATA_CLASS->findField($className, 'ClassName');
          $options['ClassName'] = $aName;

          $items[] = $FORMATTER->formatRadioField('Class Name Style',
                                                  'CLASS_NAME_STYLE',
                                                  $classNameStyle,
                                                  $options,
                                                  null,
                                                  true,
                                                  true);

//
// can OR contain UGC (can synchronization be used)
//
          $items[] = $FORMATTER->formatBinaryField('MLS Contents Only </br>(allows synchronization)',
                                                   'MLS_ONLY',
                                                   $mlsOnly);

          if ($mlsOnly == 'true') {

//
// field to use for status
//
               $systemClass = $METADATA_CLASS->getSystemClass($className, false);
               $METADATA_TABLE = new TableMetadata($S_CONFIGURATION->getName(), $systemClass);
               $detectedStandardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
               $unique_key = $S_CONFIGURATION->getValue('UNIQUE_KEY');
               $field_array = $METADATA_TABLE->findNames($detectedStandardNames);
               $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
               $fields = null;
               foreach ($field_array as $num => $item) {
                    $fields[$item] = $trans[$item] . ' (' . $item . ')';
               }

               $lookup_fields = $METADATA_TABLE->findDataLookupTypes($detectedStandardNames, false);

//
// Filter out fields that could not possible be and Agent ID 
// do not include UniqueID, dates, lookups, measurements or currency
//
               $text_only_fields = null;
               $text_only_fields['NONE'] = 'Do set Automatically';
               foreach ($fields as $num => $item) {
                    if ($num != $unique_key) {
                         if (array_key_exists($num,$lookup_fields)) {
                              $text_only_fields[$num] = $item;
                         }
                    }
               }

//$trace = $statusVariable;
               $items[] = $FORMATTER->formatSelectField('Field used for "status".<br/>(Leave unselected to bypasses synchronization based on status)',
                                                        'STATUS_VARIABLE',
                                                        $statusVariable,
                                                        $text_only_fields);
               if (array_key_exists($statusVariable,$fields)) {
                    $lookupName = $METADATA_TABLE->findLookupName($statusVariable, 
                                                                  $detectedStandardNames);
                    $METADATA_LOOKUP = new LookupTypeMetadata($S_CONFIGURATION->getName(), 
                                                              $lookupName);
                    $data = $METADATA_LOOKUP->asArray();
                    $options = null;
                    foreach ($data as $key => $value) {
                         $options[$value] = $key;
                    } 
//$trace = print_r($options, true);
//$trace = $statusVariableValue;
//                    $items[] = $FORMATTER->formatRadioField('Value of [' . $statusVariable . '] used to denote "active"',
//                                                            'STATUS_VARIABLE_VALUE',
//                                                            $statusVariableValue,
//                                                            $options,
//                                                            null,
//                                                            true,
//                                                            true);
                    $items[] = $FORMATTER->formatMultiSelectField('Values of [' . $statusVariable . '] to be shown in OpenRealty as "inactive"',
                                              'STATUS_VARIABLE_VALUE',
                                              $statusVariableValue,
                                              $options);
                    if ($statusVariableValue == null) {
                         $blockSubmit = true;
                    }
               }
          }
     }

//
// field selection 
//
     if ($type == 'CSV' || $type == 'XML') {
          $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
          $className = $S_CONFIGURATION->getValue('SELECTION_CLASS');
          $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(), $resource);
          $systemClass = $METADATA_CLASS->getSystemClass($className, false);
          $METADATA_TABLE = new TableMetadata($S_CONFIGURATION->getName(), $systemClass);
          $detectedStandardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          $temp_name = explode(',',$summaryItems);
          $field_array = null;
          foreach ($temp_name as $key => $value) {
               $field_array[$key] = $value;
          }
          $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
          $fields = null;
          foreach ($field_array as $num => $item) {
               $fields[$item] = $trans[$item] . ' (' . $item . ')';
          }
          $items[] = $FORMATTER->formatSeparator('Fields to Download');
          $notational = $METADATA_TABLE->findDisplayFields($detectedStandardNames, true);
          $items[] = $FORMATTER->formatMultiSelectField('If you do not select any fields, no data will be downloaded.<br><br/>Sometimes fields marked as "not displayable" are downloadable.',
                                                        'COLUMN_LIST',
                                                         $columnList,
                                                         $fields,
                                                         null,
                                                         $notational,
                                                         'Marked as Not Displayable on the Server');
     }

     if ($type == 'OR' || $type == 'RDB') {
          $options = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
	  if (sizeof($options) < FAST_MAP_THRESHOLD) {

//
// data map
//
               $leftMap = explode(',', $T_CONFIGURATION->getValue('COLUMN_LIST'));
               if (!is_array($rightMap)) {
                    $rightMap = explode(',', $rightMap);
               }
               if (sizeof($rightMap) != sizeof($leftMap)) {
                    $rightMap = null;
                    foreach ($leftMap as $key => $value) {
                         $rightMap[$key] = '';
                    }
               }
               $items[] = $FORMATTER->formatSeparator('Field Map');

//
// not displayable
//
               $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
//$trace .= print_r($S_CONFIGURATION, true);
//$trace .= $resource . '<br/>';
               $className = $S_CONFIGURATION->getValue('SELECTION_CLASS');
               $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(), $resource);
               $systemClass = $METADATA_CLASS->getSystemClass($className, false);
               $METADATA_TABLE = new TableMetadata($S_CONFIGURATION->getName(), $systemClass);
               $detectedStandardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
               $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
               $nd_list = $METADATA_TABLE->findDisplayFields($detectedStandardNames, true);
               $unique_key = $S_CONFIGURATION->getValue('UNIQUE_KEY');

//
// translate option list
//
               $options2 = null;
//               $listKey = 0;
               foreach ($options as $key => $value) {
                    if (array_key_exists($value, $trans)) {
                         $options2[$value] = $trans[$value];
                    }
//                    if ($value == $unique_key) {
//                         $listKey = $key;
//                    }
               }

//
// mark options non-displayable
//
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

               if ($type == 'RDB') {
//
// notations for unique
//
                    $mapText = 'Map the data fields from the SOURCE to the TARGET';
                    $notedFieldsMissing = false;
                    $notation = 'Defined in the TARGET for the unique identifier';
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
// notations required by OR 
//
                    $mapText = 'Map the data fields from the SOURCE to the TARGET. <br/><br/>' .
                               'Indicated fields MUST be mapped';
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

               $items[] = $FORMATTER->formatMapField($mapText,
                                                     $T_CONFIGURATION->getValue('TYPE'), 
                                                     $leftMap,
                                                     'RETS Server', 
                                                     $rightMap,
                                                     $options2,
                                                     'RIGHT_MAP',
                                                     $notational,
                                                     $notation,
                                                     true,
                                                     null,
                                                     false,
                                                     $notedFieldsMissing);

//
// metaColumn map
//
               $resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
               $className = $S_CONFIGURATION->getValue('SELECTION_CLASS');
               $METADATA_CLASS = new ClassMetadata($S_CONFIGURATION->getName(), $resource);
               $systemClass = $METADATA_CLASS->getSystemClass($className, false);
               $METADATA_TABLE = new TableMetadata($S_CONFIGURATION->getName(), $systemClass);
               $universe = $METADATA_TABLE->findNames($S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES'));

               if ($metaIndex == null) {
                    $metaIndex = Array();
               }
               foreach ($rightMap as $key => $value) {
                         $pointer = $leftMap[$key];
                    if ($value == META_COLUMN_INDICATOR) {
                         if (!array_key_exists($pointer,$metaIndex)) {
                              $metaIndex[$pointer] = '{}';
                         }
                    }
               }

               if ($templateMapIndex != null) {
                    $templateMapIndex = explode(',',$templateMapIndex);
                    $templateMap = explode(',',$templateMap);
                    $templateMapCombined = Array();
                    foreach ($templateMapIndex as $key => $value) {
                         $templateMapCombined[$value] = $templateMap[$key];
                    }	
                    foreach ($metaIndex as $key => $value) {
                         if (array_key_exists($key,$templateMapCombined)) {
                              $metaIndex[$key] = $templateMapCombined[$key];
                         }
                    }
               }	
               if (sizeof($metaIndex) > 0 ) {
                    $items[] = $FORMATTER->formatSeparator('Meta-Column Definitions');

                    $items[] = $FORMATTER->formatTemplateField('Create Templates',
                                                          $T_CONFIGURATION->getValue('TYPE'), 
                                                          'Template', 
                                                          $metaIndex,
                                                          'TEMPLATE_MAP',
                                                          $universe);
               }
          }

          if ($type == 'RDB') {

               $items[] = $FORMATTER->formatSeparator('Target Specific');

//
// refresh
//
               $items[] = $FORMATTER->formatBinaryField('Refresh if duplicate',
                                                        'REFRESH',
                                                        $refresh);
//          $refreshSet = false;
//          $autoCreate = $T_CONFIGURATION->getBooleanValue('AUTO_CREATE');
//          if (!$autoCreate) {
//               $items[] = $FORMATTER->formatBinaryField('Refresh if duplicate',
//                                                        'REFRESH',
//                                                        $refresh);
//               $refreshSet = true;
//          }
//          if (!$refreshSet) {
//               $items[] = $FORMATTER->formatHiddenField('REFRESH', $refresh);
//          }

//
// can Synchronization be used 
//
               $items[] = $FORMATTER->formatBinaryField('MLS Contents Only </br>(allows synchronization)',
                                                        'MLS_ONLY',
                                                        $mlsOnly);

               if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES') ) {

                    $autoCreate = $T_CONFIGURATION->getBooleanValue('AUTO_CREATE');
                    if (!$autoCreate) {
//
// image map
//
                         $leftMap = explode(',', $T_CONFIGURATION->getValue('IMAGE_COLUMN_LIST'));
                         if (!is_array($rightImageMap)) {
                              $rightImageMap = explode(',', $rightImageMap);
                         }
                         if (sizeof($rightImageMap) != sizeof($leftMap)) {
                              $rightImageMap = null;
                              foreach ($leftMap as $key => $value) {
                                   $rightImageMap[$key] = '';
                              }
                         }
                         $items[] = $FORMATTER->formatSeparator('Image Map');

//
// notations for unique
//
                         $imageOptions = null;
                         $imageOptions['ID'] = 'Unique Identifier';
                         $imageOptions['INDEX'] = 'Image Number';
                         $imageOptions['URL'] = 'URL to the image';
                         $imageOptions['PATH'] = 'local disk location';
                         $unique_image_key = 'ID';

//
// notations for unique
//
                         $notation = 'Defined in the TARGET for the unique identifier';
                         $notational = null;
                         $imageTableKey = $T_CONFIGURATION->getValue('IMAGE_TABLE_KEY'); 
                         foreach ($leftMap as $key => $value) {
                              if ($imageTableKey == $value) {
                                   $notational[$key] = true;
                                   $rightImageMap[$key] = $unique_image_key;
                              }
                         }

                         $items[] = $FORMATTER->formatMapField('Map the image fields from the SOURCE to the TARGET',
                                                               $T_CONFIGURATION->getValue('TYPE'), 
                                                               $leftMap,
                                                               'RETS Server', 
                                                               $rightImageMap,
                                                               $imageOptions,
                                                               'RIGHT_IMAGE_MAP',
                                                               $notational,
                                                               $notation);
                    }
               }
          }
     }
/*
$trace = print_r($display_source, true);
*/

     $overrideSubmit = null;
     if ($blockSubmit) {
          $overrideSubmit = $FORMATTER->formatPageSubmit('Connect', 'SOA_CONNECT');
     }
     $items[] = $FORMATTER->formatHiddenField('MODE', $env['MODE']);
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'EXTRACT');

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('NEW_EXTRACT'), $items, $overrideSubmit) .
            ']]></HTML>';
}

//
//------------

?>
