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

//     $trace = print_r($env, true);
//     $trace = $aName . ' ' . $aValue;

//
// set up defaults
//
     $element = '';
     $queryItems = '';
     $summaryItems = '';
     $ownershipVariable = '';
     $dateVariable = '';
     $selectionResource = '';
     $selectionClass = '';
     $uniqueKey = '';
     $detectedStandardNames = false;
     $detectedMaximumRetsVersion = '';
     $compactDecodedFormat = false;
     $pagination = false;
     $simultaneousLogins = false;
     $offsetAdjustment = false;
     $mediaMultipart = false;
     $mediaLocation = false;
     $restrictedIndicator = '';
     $mediaType = '';
     $postRequests = 'true';

     if (array_key_exists('viele_mode',$env)) {
          $LOCATION = determine_type($env['ELEMENT-TYPE']);
          $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
          $summaryItems = $CONFIGURATION->getValue('SUMMARY_ITEMS');
          $queryItems = $CONFIGURATION->getValue('QUERY_ITEMS');
          $ownershipVariable = $CONFIGURATION->getValue('OWNERSHIP_VARIABLE');
          $dateVariable = $CONFIGURATION->getValue('DATE_VARIABLE');
          $selectionResource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
          $selectionClass = $CONFIGURATION->getValue('SELECTION_CLASS');
          $uniqueKey = $CONFIGURATION->getValue('UNIQUE_KEY');
          $detectedStandardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          $detectedMaximumRetsVersion = $CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION');
          $compactDecodedFormat = $CONFIGURATION->getBooleanValue('COMPACT_DECODED_FORMAT');
          $pagination = $CONFIGURATION->getBooleanValue('PAGINATION');
          $simultaneousLogins = $CONFIGURATION->getBooleanValue('SIMULTANEOUS_LOGINS');
          $offsetAdjustment = $CONFIGURATION->getValue('OFFSET_ADJUSTMENT');
          $mediaMultipart = $CONFIGURATION->getBooleanValue('MEDIA_MULTIPART');
          $mediaLocation = $CONFIGURATION->getBooleanValue('MEDIA_LOCATION');
          if (!$mediaLocation) {
               $mediaLocation = 'false';
          } else {
               $mediaLocation = 'true';
          }
          $restrictedIndicator = $CONFIGURATION->getValue('RESTRICTED_INDICATOR');
          $mediaType = $CONFIGURATION->getValue('MEDIA_TYPE');
          $postRequests = $CONFIGURATION->getBooleanValue('POST_REQUESTS');
     }

//
// weigh input
//
     if (array_key_exists('ELEMENT',$env)) {
          $element = $env['ELEMENT'];
     }

     if (array_key_exists('SUMMARY_ITEMS',$env)) {
          $summaryItems = $env['SUMMARY_ITEMS'];
     }

     if (array_key_exists('QUERY_ITEMS',$env)) {
          $queryItems = $env['QUERY_ITEMS'];
     }

     if (array_key_exists('OWNERSHIP_VARIABLE',$env)) {
          $ownershipVariable = $env['OWNERSHIP_VARIABLE'];
     }

     if (array_key_exists('DATE_VARIABLE',$env)) {
          $dateVariable = $env['DATE_VARIABLE'];
     }

     if (array_key_exists('SELECTION_RESOURCE',$env)) {
          $selectionResource = $env['SELECTION_RESOURCE'];
     }

     if (array_key_exists('SELECTION_CLASS',$env)) {
          $selectionClass = $env['SELECTION_CLASS'];
     }

     if (array_key_exists('UNIQUE_KEY',$env)) {
          $uniqueKey = $env['UNIQUE_KEY'];
     }

     if (array_key_exists('DETECTED_STANDARD_NAMES',$env)) {
          $detectedStandardNames = $env['DETECTED_STANDARD_NAMES'];
     }

     if (array_key_exists('DETECTED_MAXIMUM_RETS_VERSION',$env)) {
          $detectedMaximumRetsVersion = $env['DETECTED_MAXIMUM_RETS_VERSION'];
     }

     if (array_key_exists('COMPACT_DECODED_FORMAT',$env)) {
          $compactDecodedFormat = $env['COMPACT_DECODED_FORMAT'];
     }

     if (array_key_exists('PAGINATION',$env)) {
          $pagination = $env['PAGINATION'];
     }

     if (array_key_exists('SIMULTANEOUS_LOGINS',$env)) {
          $simultaneousLogins = $env['SIMULTANEOUS_LOGINS'];
     }

     if (array_key_exists('MEDIA_BYPASS',$env)) {
          $mediaBypass = $env['MEDIA_BYPASS'];
     }

     if (array_key_exists('OFFSET_ADJUSTMENT',$env)) {
          $offsetAdjustment = $env['OFFSET_ADJUSTMENT'];
     }

     if (array_key_exists('MEDIA_MULTIPART',$env)) {
          $mediaMultipart = $env['MEDIA_MULTIPART'];
     }

     if (array_key_exists('MEDIA_LOCATION',$env)) {
          $mediaLocation = $env['MEDIA_LOCATION'];
     }

     if (array_key_exists('RESTRICTED_INDICATOR',$env)) {
          $restrictedIndicator = $env['RESTRICTED_INDICATOR'];
     }

     if (array_key_exists('MEDIA_TYPE',$env)) {
          $mediaType = $env['MEDIA_TYPE'];
     }

     if (array_key_exists('POST_REQUESTS',$env)) {
          $postRequests = $env['POST_REQUESTS'];
     }

     $FORMATTER = new AjaxFormatter();

     $blockSubmit = false;
     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatDisplayField('Resource', $selectionResource);

     $METADATA_CLASS = new ClassMetadata($env['ELEMENT'], $selectionResource);
     $cn_array = $METADATA_CLASS->findNames($detectedStandardNames, true);
     $items[] = $FORMATTER->formatDisplayField('Class', $selectionClass . ' (' . $cn_array[$selectionClass] . ')');

     $systemClass = $METADATA_CLASS->getSystemClass($selectionClass, false);
     $METADATA_TABLE = new TableMetadata($env['ELEMENT'], $systemClass);
     $field_array = $METADATA_TABLE->findNames($detectedStandardNames);
     $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
     $fields = null;
     foreach ($field_array as $num => $item) {
          $fields[$item] = $trans[$item] . ' (' . $item . ')';
     }

     $items[] = $FORMATTER->formatSeparator('Fields to Download');
     $notational = $METADATA_TABLE->findDisplayFields($detectedStandardNames, true);
     if (array_key_exists('SOA_ACTION',$env)) {
          if ($env['SOA_ACTION'] == 'FILTER__SUMMARY_ITEMS') {
               $temp = '';
               foreach ($fields as $key => $value) {
                    if (!array_key_exists($key,$notational)) {
                         $temp .= $key . ',';
                    }
               }
               $summaryItems = substr($temp,1,strlen($temp) -1);
          }
     }
     $items[] = $FORMATTER->formatMultiSelectField('This list can be further limited during EXTRACT definition.<br/><br/>Specifying only fields you will use make further definition more efficient.  There is no advantage to selecting everything.<br/><br/>If you do not select any fields, no data will be downloaded.<br><br/>Sometimes fields marked as "not displayable" are downloadable.',
                                                   'SUMMARY_ITEMS',
                                                    $summaryItems,
                                                    $fields,
                                                    null,
                                                    $notational,
                                                    'Marked as Not Displayable on the Server',
                                                    false,
                                                    true,
                                                    4,
                                                    true);

     $items[] = $FORMATTER->formatSeparator('Fields Used to Query');

//
// only show searchable fields
//
     $searchable = $METADATA_TABLE->findSearchableFields($detectedStandardNames);
     $lookup_fields = $METADATA_TABLE->findDataLookupTypes($detectedStandardNames, false);
     $ab_fields = null;
     $ab_lookup_fields = null;
     foreach ($fields as $key => $value) {
          if (array_key_exists($key,$searchable)) {
               $ab_fields[$key] = $value;
               if (array_key_exists($key,$lookup_fields)) {
                    $ab_lookup_fields[$key] = true;
               }
          }
     }

     $statusColor = null;
     if($postRequests == 'true') {
          $displayText = 'Only the selected fields will be able to included in a query. <br/><br/>There is no advantage to selecting everything, you will just complicate the process. Just pick the few you will use.<br/><br/>The MLS may not define all fields as searchable';
     } else {
          $check = explode(',', $queryItems);
$trace .= sizeof($check) . ' ' . MAX_QUERY_FIELDS;
          $displayText = 'Only the selected fields will be able to included in a query. <br/><br/>If you are using a RETS server that only supports GET processing, you will will be limited to 15 fields. Just pick the few you will use.';
          if (sizeof($check) > MAX_QUERY_FIELDS) {
               $statusColor = 'red';
               $blockSubmit = true;
          }
     }
     if ($queryItems == '') {
          $queryItems = $uniqueKey;
     }
     $items[] = $FORMATTER->formatMultiSelectField($displayText,
                                                   'QUERY_ITEMS',
                                                    $queryItems,
                                                    $ab_fields,
                                                    $statusColor,
                                                    $ab_lookup_fields,
                                                    'Server has pre-defined "Lookup" values');

     $date_fields = $METADATA_TABLE->findDateFields($detectedStandardNames);
//$trace .= print_r($date_fields, true);
//$trace .= '<br/>';
//$trace .= print_r($lookup_fields, true);

//
// only for resources that are properties.
//
     if (strpos(strtoupper($selectionResource), 'PROPERTY') === false) {
     } else {

//
// create more filters
//
          $filters = null;
          if ($date_fields != null) {
               $filters[] = $date_fields;
          }
          if ($lookup_fields != null) {
               $filters[] = $lookup_fields;
          }
          $sqft_array = $METADATA_TABLE->findUnitsFields('SQFEET', $detectedStandardNames);
          if ($sqft_array != null) {
               $filters[] = $sqft_array;
          }
          $currency_array = $METADATA_TABLE->findCurrencyFields($detectedStandardNames);
          if ($currency_array != null) {
               $filters[] = $currency_array;
          }

//
// Filter out fields that could not possibly be an Agent ID 
// do not include UniqueID, dates, lookups, measurements or currency
//
          $text_only_fields = null;
          foreach ($fields as $num => $item) {
               if ($num != $uniqueKey) {
                    $found = false;
                    foreach ($filters as $ref => $aFilter) {
                         if (array_key_exists($num,$aFilter)) {
                              $found = true;
                         }
                    }
                    if (!$found) {
                         if (array_key_exists($num,$searchable)) {
                              $text_only_fields[$num] = $item;
                         }
                    }
               }
          }
//$trace .= print_r($text_only_fields, true);
//$trace .= print_r($fields, true);

          $items[] = $FORMATTER->formatSeparator('Ownership');

          $items[] = $FORMATTER->formatSelectField('Field used to identify your listings.',
                                                   'OWNERSHIP_VARIABLE',
                                                   $ownershipVariable,
                                                   $text_only_fields);
          if (!array_key_exists($ownershipVariable,$fields)) {
               $blockSubmit = true;
          }
     }

//
// the field that tells you something changed - DELTA
//
     $items[] = $FORMATTER->formatSeparator('Fields that Determine What is New');
     if ($date_fields == null) {
          $items[] = $FORMATTER->formatDisplayField('There are no fields on the server that can be used to determine when information has changed.<br/>Typically fields defined as Date or DateTime are used for this purpose.', 
                                                    'This feature is NOT CONFIGURED or ENABLED',
                                                    'red');
     } else {
          $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
          $fields = null;
          $notational = null;
          foreach ($date_fields as $num => $item) {
               if (array_key_exists($item,$searchable)) {
                    $fields[$item] = $trans[$item] . ' (' . $item . ')';
                    if ($METADATA_TABLE->findDataType($item, $detectedStandardNames) == 'Date') {
                         $notational[$item] = true;
                    }
               }
          }
          $items[] = $FORMATTER->formatMultiSelectField('Some servers have separate data and image fields.<br/><br/>You should select only those fields that give an accurate picture of what has changed between runs.<br/><br/>Update processing is designed to use DateTime formatting because it includes the time of day.',
                                                        'DATE_VARIABLE',
                                                         $dateVariable,
                                                         $fields,
                                                         null,
                                                         $notational,
                                                         'Field expressed in Date (not DateTime) format.');
//          if (!array_key_exists($dateVariable,$fields)) {
//               $blockSubmit = true;
//          }
     }

//
// Text string used by the MLS to block data
//
     $items[] = $FORMATTER->formatSeparator('Data Security');
     $items[] = $FORMATTER->formatSingleEntryField('Text to replace restricted data',
                                                   'RESTRICTED_INDICATOR',
                                                   $restrictedIndicator,
                                                   24);

//
// only for resources that have images (property)
//
     if (strpos(strtoupper($selectionResource), 'PROPERTY') === false) {
     } else {
          $items[] = $FORMATTER->formatSeparator('Image Settings');

          $METADATA_OBJECT = new ObjectMetadata($env['ELEMENT'], $selectionResource);
          $field_array = $METADATA_OBJECT->findNames();
          $fields = null;
          foreach ($field_array as $num => $item) {
               $fields[$item] = $item;
          }
          $items[] = $FORMATTER->formatSelectField('Type of image to download.',
                                                   'MEDIA_TYPE',
                                                   $mediaType,
                                                   $fields);
     }

//-------------------

     $overrideSubmit = null;
     if ($blockSubmit) {
          $overrideSubmit = $FORMATTER->formatPageSubmit('Connect', 'SOA_CONNECT');
     }
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $env['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $env['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('MODE', $env['MODE']);

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('SOURCE_QUERY_PARAMETERS'), $items, $overrideSubmit) .
            ']]></HTML>';

}

//
//------------

?>
