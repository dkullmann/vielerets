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

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// read file
//
$SOURCE = new Source();
$CONFIGURATION = $SOURCE->getConfiguration($vars['ELEMENT']);
$names = $CONFIGURATION->getValue('QUERY_ITEMS');
$fields = explode(',', $names);

//
// using view.php 
//
$FORMATTER = new TableFormatter();

//$cName = $CONFIGURATION->getName();

//
// generate human friendly field translations
//
$resource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
$class = $CONFIGURATION->getValue('SELECTION_CLASS');
$METADATA_CLASS = new ClassMetadata($vars['ELEMENT'], 
                                    $resource);
$standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
$systemClass = $METADATA_CLASS->getSystemClass($class,
                                               $standardNames);

$METADATA = new TableMetadata($vars['ELEMENT'], 
                              $systemClass);
$translationTable = $METADATA->findNames($standardNames, true);


//
// print eligible fields 
//
$buffer = '<table width="100%" cellpadding="0" cellspacing="5" border="0">' . CRLF;

//
// read metadata
//
$METADATA->read();
foreach ($fields as $num => $visible_name) {
     $buffer .= '  <tr>' . CRLF .
                '    <td align="left">' . CRLF;

//
// arguments for links 
//
     $url = $SCREEN['FORM_HELP'] .
            '?FIELD=' . $visible_name . 
            '&ELEMENT=' . $vars['ELEMENT'] . 
            '&ELEMENT-TYPE=SOURCE' . 
            '&PASSTHRU=SOURCE_QUERY_FORMS';

//
// check if the field has definitions
//
     $variable_name = $visible_name . '_FORM';
     $field_array = $CONFIGURATION->getVariable($variable_name);

     if (is_array($field_array)) {
          if (sizeof($field_array) > 0) {
               $buffer .= $FORMATTER->createLink($url . '&ACTION=DELETE',
                                                 $FORMATTER->formatLink('clear definition'));
          }
     }

//
// render contant columns 
//
     $buffer .= $FORMATTER->formatColumnSeparation();
     $display_name = $visible_name;
     if (array_key_exists($visible_name, $translationTable)) {
          $display_name = $translationTable[$visible_name] . ' (' .$visible_name . ')';
     }
     $buffer .= $FORMATTER->formatBoldText($display_name);
     $buffer .= $FORMATTER->formatColumnSeparation();
     $buffer .= $FORMATTER->createLink($url . '&ACTION=UPDATE',
                                       $FORMATTER->formatLink('edit'));

//
// check if the field has server supplied lookups
//
     $buffer .= $FORMATTER->formatColumnSeparation();
     $lookupName = $METADATA->findLookupName($visible_name, 
                                             $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES'));
     if ($lookupName != null) {
          $buffer .= $FORMATTER->formatText('*', HIGHLIGHT_FONT_COLOR);
          $buffer .= $FORMATTER->createLink($url . '&ACTION=REFRESH',
                                            $FORMATTER->formatLink('refresh'));
     }

     $buffer .= '    </td>' . CRLF;
     $buffer .= '  </tr>' . CRLF;
}
$buffer .= '</table>';

//
// print legend
//
$buffer .= '<table width="100%" cellpadding="0" cellspacing="5" border="0">' . CRLF .
           '  <tr>' . CRLF .
           '    <td align="center">' . CRLF .
           $FORMATTER->formatText('* - Reset with server supplied values', 
                                  HIGHLIGHT_FONT_COLOR) .
           '    </td>' . CRLF .
           '  </tr>' . CRLF .
           '</table>';

//
// assign the rendering to the display
//
$items[] = $buffer;

$items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);

$FORMATTER->printMenu($items, 
                      $SCREEN['SOURCE_MENU'],
                      'Editing source [' . $vars['ELEMENT'] . ']',
                      'Values for Search Forms',
                      'left');

$FORMATTER->finish();
$HTML->finish();

//
//------------

?>
