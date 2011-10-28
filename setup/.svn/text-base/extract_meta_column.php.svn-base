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

if (array_key_exists('CANCEL', $vars)) {
     $url = $SCREEN[$vars['PASSTHRU-LOCATION']] . 
            '?ELEMENT=' . $vars['ELEMENT'];
     locate_next_screen($url);
} else {
     if (array_key_exists('SELECT-ONLY', $vars)) {
//
// update configuration 
//
          $LOCATION = determine_type($vars['ELEMENT-TYPE']);
          $CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);
          $CONFIGURATION->setVariable('$METACOLUMN_MAP', $vars['METACOLUMN_MAP']);
          $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

          $url = $SCREEN[$vars['PASSTHRU']] . 
                         '?ELEMENT=' . $vars['ELEMENT'] .
                         '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'];
          locate_next_screen($url);
     }
}
//
// setup classes 
//
$EXTRACT = new Extract();
$CONFIGURATION = $EXTRACT->getConfiguration($vars['ELEMENT']);

//
// read configuration file if required 
//
$TARGET = new Target();
$T_CONFIGURATION = $TARGET->getConfiguration($CONFIGURATION->getValue('TARGET'));
$type = $T_CONFIGURATION->getValue('TYPE');

if ($type == 'OR' || $type == 'RDB') {
     $checkArray = $CONFIGURATION->getVariable($vars['FIELD']);
     $metaIndex = $EXTRACT->detectMetaColumns($checkArray);

//
// display the list
//
     $HTML = new HTMLPage();
     $HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
     $FORMATTER = new TableFormatter();

//
// display list of fields
//
     $sMap = $checkArray['SOURCE'];
     $tMap = $checkArray['TARGET'];
     $SOURCE = new Source();
     $sourceName = $CONFIGURATION->getValue('SOURCE');
     $S_CONFIGURATION = $SOURCE->getConfiguration($sourceName);

     $METADATA_CLASS = new ClassMetadata($sourceName, $S_CONFIGURATION->getValue('SELECTION_RESOURCE'));
     $systemClass = $METADATA_CLASS->getSystemClass($S_CONFIGURATION->getValue('SELECTION_CLASS'), false);
     $METADATA_TABLE = new TableMetadata($sourceName, $systemClass);
     $temp_name = $METADATA_TABLE->findNames($S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES'));

     $item_count = sizeof($temp_name);
     $rows = $FORMATTER->bestSplit($item_count);
     $columns = $FORMATTER->bestSplit($item_count,false);
     $body = null;
     for ($y = 0; $y < $rows; ++$y) {
          $body .= '<tr>';
          for ($x = 0; $x < $columns; ++$x) {
               $offset = $y + (int)($rows * $x);
               $body .= '<td>';
               if (array_key_exists($offset, $temp_name)) {
                    $body .= $FORMATTER->formatText($temp_name[$offset], null, 10);
               }
               $body .= '</td>';
          }
          $body .= '</tr>';
     }
     $items[] = '<table width="100%" cellpadding="2" cellspacing="0" border="1">' .
                '<tr align="center">' .
                '<td>' .
                $FORMATTER->formatBoldText('Columns to Choose From', null, 10) .
                '</td>' .
                '</tr>' .
                '<tr align="center">' .
                '<td>' .
                '<table cellpadding="5" cellspacing="0" border="0">' .
                $body .
                '</table>' .
                '</td>' .
                '</tr>' .
                '</table>';

//
// display instructions 
//
     $items[] = '<table width="100%" cellpadding="5" cellspacing="0" border="0">' .
                '<tr align="center">' .
                '<td>' .
                $FORMATTER->formatText('Enclose SOURCE field names inside of brackets to create a<br/>' .
                                       'definition that can be used when downloading to the TARGET.<br/>' .
                                       '<br/>Example: {STREET_NUMBER} {STREET_NAME}<br/>' .
                                       '<br/>What you put between the brackets comes from the list above!',
                                       'red', 10) .
                '</td>' .
                '</tr>' .
                '</table>';

//
// display metacolumns
//
     $previous = $CONFIGURATION->getVariable('METACOLUMN_MAP');
     $STYLIST = new Stylist();
     $body = null;
     foreach ($metaIndex as $key => $value) {
          $vName = 'METACOLUMN_MAP[' . $value . ']';
          if (array_key_exists($value, $previous)) {
               $vValue = $previous[$value];
          } else {
               $vValue = '{}';
          }
          $body .= '<tr align="center">' .
                   '<td>' .
                   $FORMATTER->formatText($value) .
                   '</td><td>' .
                   '<input type="text" name="' . $vName . '" value="' . $vValue . '" size="48" style="' . $STYLIST->createTextStyle() . '"/>' .
                   '</td>' .
                   '</tr>';
     }
     $items[] = '<table width="100%" cellpadding="2" cellspacing="0" border="1">' .
                '<tr align="center">' .
                '<td>' .
                $FORMATTER->formatBoldText('TARGET Column') .
                '</td>' .
                '<td>' .
                $FORMATTER->formatBoldText('Meta-Column Definition') .
                '</td>' .
                '</tr>' .
                $body .
                '</table>';

     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SETUP_INDEX');
     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'EXTRACT_MENU');

     $message = 'Definition for extract [' . $vars['ELEMENT'] . ']';

     $FORMATTER->printForm($items, 
                           $SCREEN['EXTRACT_META_COLUMN'], 
                           $message,
                           'Define Meta-Columns');

     $FORMATTER->finish();

     $HTML->finish();
} else {
     $url = $SCREEN['SITE_INDEX'];
     locate_next_screen($url);
}

//
//------------

?>
