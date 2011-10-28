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
     $selectionResource = '';
     $selectionClass = '';
     $uniqueKey = '';
     $detectedStandardNames = 'false';
     $detectedMaximumRetsVersion = '';
     $nullQueryOption = null;
     $compactDecodedFormat = 'false';
     $pagination = 'false';
     $simultaneousLogins = 'false';
     $offsetAdjustment = 'false';
     $mediaMultipart = 'false';
     $mediaLocation = 'false';

     if (array_key_exists('viele_mode',$env)) {
          $LOCATION = determine_type($env['ELEMENT-TYPE']);
          $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
          $selectionResource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
          $selectionClass = $CONFIGURATION->getValue('SELECTION_CLASS');
          $uniqueKey = $CONFIGURATION->getValue('UNIQUE_KEY');
          $detectedStandardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
          $detectedMaximumRetsVersion = $CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION');
          $nullQueryOption = $CONFIGURATION->getValue('NULL_QUERY_OPTION');
          $compactDecodedFormat = $CONFIGURATION->getBooleanValue('COMPACT_DECODED_FORMAT');
          $pagination = $CONFIGURATION->getBooleanValue('PAGINATION');
          $simultaneousLogins = $CONFIGURATION->getBooleanValue('SIMULTANEOUS_LOGINS');
          $offsetAdjustment = $CONFIGURATION->getValue('OFFSET_ADJUSTMENT');
          $mediaMultipart = $CONFIGURATION->getBooleanValue('MEDIA_MULTIPART');
          $mediaLocation = $CONFIGURATION->getBooleanValue('MEDIA_LOCATION');
     }

//
// weigh input
//
     if (array_key_exists('ELEMENT',$env)) {
          $element = $env['ELEMENT'];
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

     if (array_key_exists('NULL_QUERY_OPTION',$env)) {
          $nullQueryOption = $env['NULL_QUERY_OPTION'];
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

     $FORMATTER = new AjaxFormatter();

     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatDisplayField('Resource', $selectionResource);
//
// determine if resource is "property"
//
     $isPropertyResource = true;
     if (strpos(strtoupper($selectionResource), 'PROPERTY') === false) {
          $isPropertyResource = false;
     }

     $METADATA_CLASS = new ClassMetadata($env['ELEMENT'], $selectionResource);
     $cn_array = $METADATA_CLASS->findNames($detectedStandardNames, true);
     $items[] = $FORMATTER->formatDisplayField('Class', $selectionClass . ' (' . $cn_array[$selectionClass] . ')');

     if ($detectedStandardNames) {
          $items[] = $FORMATTER->formatDisplayField('Standard Names', 'true');
     } else {
          $items[] = $FORMATTER->formatDisplayField('Standard Names', 'false');
     }

     $items[] = $FORMATTER->formatSeparator('Standard Settings');

     $METADATA_CLASS = new ClassMetadata($env['ELEMENT'], $selectionResource);
     $systemClass = $METADATA_CLASS->getSystemClass($selectionClass, false);
     $METADATA_TABLE = new TableMetadata($env['ELEMENT'], $systemClass);
     $field_array = $METADATA_TABLE->findNames($detectedStandardNames);
     $trans = $METADATA_TABLE->findNames($detectedStandardNames, true);
     $fields = null;
     foreach ($field_array as $num => $item) {
          $fields[$item] = $trans[$item] . ' (' . $item . ')';
     }
     $items[] = $FORMATTER->formatSelectField('Unique Key',
                                              'UNIQUE_KEY',
                                              $uniqueKey,
                                              $fields,
                                              null,
                                              false);

     $options = null;
     $options['1.0'] = '1.0';
     $options['1.5'] = '1.5';
     $options['1.7'] = '1.7 (June 2009 Level)';
     $options['1.7.2'] = '1.7.2';
     $items[] = $FORMATTER->formatRadioField('Maximum RETS Version',
                                             'DETECTED_MAXIMUM_RETS_VERSION',
                                             $detectedMaximumRetsVersion,
                                             $options,
                                             null,
                                             true,
                                             true);

     $items[] = $FORMATTER->formatBinaryField('Simultaneous Logins?',
                                              'SIMULTANEOUS_LOGINS',
                                              $simultaneousLogins);

     $options = null;
     $options['UNIQUE_IDENTIFIER'] = 'Unique Identifier';
     if ($isPropertyResource) {
          if ($detectedMaximumRetsVersion == '1.7' ||
              $detectedMaximumRetsVersion == '1.7.2') {
               $options['LISTING_STATUS_ANY'] = 'Listing Status';
          } else {
               $options['LISTING_STATUS'] = 'Listing Status';
          }
     }
     if ($detectedMaximumRetsVersion == '1.7' ||
         $detectedMaximumRetsVersion == '1.7.2') {
          $options['REQUIREDS_ANY'] = 'MLS-defined Required Fields';
     } else {
          $options['REQUIREDS'] = 'MLS-defined Required Fields';
     }
     $options['FIRST_INTEGER'] = 'First Integer';
     $options['DIRECT'] = 'Could not be determined';
     $items[] = $FORMATTER->formatSelectField('NULL Query Basis',
                                              'NULL_QUERY_OPTION',
                                              $nullQueryOption,
                                              $options);

     $items[] = $FORMATTER->formatSeparator('Text Data Settings');

     $items[] = $FORMATTER->formatBinaryField('COMPACT-DECODED Format?',
                                              'COMPACT_DECODED_FORMAT',
                                              $compactDecodedFormat);

     if ($nullQueryOption == 'DIRECT') {
          $items[] = $FORMATTER->formatBinaryField('Pagination?<br/>(Needs to be set manually)',
                                                   'PAGINATION',
                                                   $pagination);
     } else {
          $items[] = $FORMATTER->formatBinaryField('Pagination?',
                                                   'PAGINATION',
                                                   $pagination);
     }
     if ($pagination == 'true') {
          $options = null;
          $options['0'] = '0 (the most common case)';
          $options['-1'] = '-1';
          if ($nullQueryOption == 'DIRECT') {
               $items[] = $FORMATTER->formatRadioField('Pagination Offset Start<br/>(Needs to be set manually)',
                                                       'OFFSET_ADJUSTMENT',
                                                       $offsetAdjustment,
                                                       $options,
                                                       null,
                                                       true,
                                                       true);
          } else {
               $items[] = $FORMATTER->formatRadioField('Pagination Offset Start',
                                                       'OFFSET_ADJUSTMENT',
                                                       $offsetAdjustment,
                                                       $options,
                                                       null,
                                                       true,
                                                       true);
          }
     }


//
// only for resources that have images (property)
//
     if (strpos(strtoupper($selectionResource), 'PROPERTY') === false) {
     } else {
          $items[] = $FORMATTER->formatSeparator('Image Data Settings');

          $items[] = $FORMATTER->formatBinaryField('Images with Multi-Part?',
                                                   'MEDIA_MULTIPART',
                                                   $mediaMultipart);

          $items[] = $FORMATTER->formatBinaryField('Images as URLs?',
                                                   'MEDIA_LOCATION',
                                                   $mediaLocation);
     }



//-------------------

     $items[] = $FORMATTER->formatHiddenField('MODE', $env['MODE']);
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $env['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $env['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', $env['PASSTHRU']);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $env['PASSTHRU-LOCATION']);

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('SOURCE_OVERRIDE'), $items) .
            ']]></HTML>';

}

//
//------------

?>
