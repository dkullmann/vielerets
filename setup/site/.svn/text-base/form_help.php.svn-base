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

//
// determine configuration type 
//
$LOCATION = determine_type($vars['ELEMENT-TYPE']);
if ($LOCATION == null)
{
     $LOCATION = new Location();
}
$CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);

//
// find definition
//
$key = $vars['FIELD'] . '_FORM';
$value = $CONFIGURATION->getVariable($key);

if ($vars['ACTION'] == 'UPDATE' ||
    $vars['ACTION'] == 'REFRESH')
{
//
// split array into components
//
     $text = null;
     $dmql = null;
     if ($value != null)
     {
          foreach ($value as $a_text => $a_dmql) 
          {
               $text[] = $a_text;
               $dmql[] = $a_dmql;
          }
     }

     $HTML = new HTMLPage();
     $HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
     $FORMATTER = new TableFormatter();
     if ($vars['ELEMENT-TYPE'] == 'SOURCE')
     {
          $items[] = form_table($FORMATTER,
                                $vars['FIELD'],
                                $text,
                                $dmql,
                                $CONFIGURATION,
                                $vars['ACTION']);
     }
     else
     {
          $items[] = form_table($FORMATTER,
                                $vars['FIELD'],
                                $text,
                                $dmql);
     }
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 
                                              $vars['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('FIELD', $vars['FIELD']);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU', $vars['PASSTHRU']);
     $items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $vars['PASSTHRU']);
     $message = 'Definition for ' . $vars['FIELD'];
     $FORMATTER->printForm($items, 
                           $SCREEN['SUBMIT_FORM'], 
                           $message,
                           $message);
     $FORMATTER->finish();
     $HTML->finish();
     exit;
}

if ($vars['ACTION'] == 'DELETE')
{
//
// delete the variable
//
     if ($value != null)
     {
          $CONFIGURATION->removeVariable($key);
          $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);
     }

//
// set up return URL
//
     $url = $SCREEN[$vars['PASSTHRU']] .
            '?ELEMENT=' . $vars['ELEMENT'] .
            '&PASSTHRU-LOCATION=SETUP_INDEX';
     locate_next_screen($url);
}

function form_table(&$FORMATTER,
                    $visible_name,
                    $text,
                    $dmql,
                    $CONFIGURATION = null,
                    $action = null)
{
     $lookupName = null; 
     $buffer = '<table width="100%" cellpadding="0" cellspacing="5" border="0">' .
               '<tr>' .
               '<td align="center">' .
               $FORMATTER->renderTitle($visible_name) .
               '</td>' .
               '</tr>';

//
// if a CONFIGURATION is available, LookupTypes may be available 
//
     if ($CONFIGURATION != null)
     {
          if ($action == 'REFRESH')
          {
               $cName = $CONFIGURATION->getName();

//
// find correct class name
//
               $resource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
               $class = $CONFIGURATION->getValue('SELECTION_CLASS');
               $standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
               $METADATA_CLASS = new ClassMetadata($cName, 
                                                   $resource);
               $systemClass = $METADATA_CLASS->getSystemClass($class,
                                                              $standardNames);

//
// read metadata
//
               $METADATA = new TableMetadata($cName, $systemClass);
               $METADATA->read();
               $lookupName = $METADATA->findLookupName($visible_name, 
                                                       $standardNames);
//
// fill in the form with server defaults
//
               if ($lookupName != null)
               {
                    $lookupType = $METADATA->findLookupType($visible_name, 
                                                            $standardNames);
                    $METADATA = new LookupTypeMetadata($cName, $lookupName);
                    if ($METADATA->exists())
                    {
                         $data = $METADATA->asArray($lookupType);
                         $text = array();
                         $dmql = array();
                         foreach ($data as $key => $value) 
                         {
                              $text[] = $key;
                              $dmql[] = $value;
                         }
                         $buffer .= availableMessage($FORMATTER, $lookupName);
                    }
               }
          }
     }

//
// render the list
//
     $buffer .= '<tr>' .
                '<td align="center">' .
                '<table width="100%" cellpadding="0" cellspacing="0" border="1">' .
                '<tr>' .
                '<th>' .
                $FORMATTER->formatBoldText('Display Text') .
                '</th>' .
                '<th>' .
                $FORMATTER->formatBoldText('DMQL') .
                '</th>' .
                '</tr>';

     $max_rows = 10;
     $newSize = sizeof($text);
     if ($newSize > 0)
     {
          $max_rows = $newSize;
     }
     for ($i = 0; $i < $max_rows; ++$i)
     {
          $buffer .= '<tr>';

//
// left column
//
          $buffer .= '<td align="left">';
          $text_name = 'TEXT[' . $i . ']';
          $text_value = '';
          if (sizeof($text) > $i)
          {
               $text_value = $text[$i];
          }
          $FIELD = new FieldFormatter($text_name, $text_value);
          $buffer .= $FIELD->render();
          $buffer .= '</td>';

//
// right column
//
          $buffer .= '<td align="left">';
          $dmql_name = 'DMQL[' . $i . ']';
          $dmql_value = '';
          if (sizeof($dmql) > $i)
          {
               $dmql_value = $dmql[$i];
          }
          $FIELD = new FieldFormatter($dmql_name, $dmql_value);
          $buffer .= $FIELD->render();
          $buffer .= '</td>';

          $buffer .= '</tr>';
     }

     $buffer .= '</table>' .
                '</td>' .
                '</tr>';

//
// display a message if values are from the server
//
     if ($lookupName != null)
     {
          $buffer .= availableMessage($FORMATTER, $lookupName);
     }

     $buffer .= '</table>';

     return $buffer;
}

function availableMessage($FORMATTER,
                          $lookupName)
{
     return '<tr>' .
            '<td align="center">' .
            '&nbsp' .
            $FORMATTER->formatText('Server supplied lookup values for field (', 
                                   'red') .
            $FORMATTER->formatText($lookupName, 'red') .
            $FORMATTER->formatText(')', 'red') .
            '</td>' .
            '</tr>';
}

//
//------------

?>
