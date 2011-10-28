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

set_time_limit(0);

//
// select only mode
//
if (array_key_exists('CANCEL', $vars))
{
     $args =  'ELEMENT=' . $vars['ELEMENT'];
     $url_direct = $SCREEN[$vars['CANCEL-LOCATION']];
     locate_next_screen($url_direct . '?' . $args);
}
else
{
     if (array_key_exists('SELECT-ONLY', $vars))
     {
          if (array_key_exists('SINGLE_VALUE', $vars))
          {
               if (array_key_exists($vars['FIELD'], $vars))
               {
//
// update configuration 
//
                    $LOCATION = determine_type($vars['ELEMENT-TYPE']);
                    $CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);
                    $CONFIGURATION->setValue($vars['FIELD'], $vars[$vars['FIELD']]);
                    $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

                    $url = $SCREEN[$vars['PASSTHRU']] . 
                                   '?ELEMENT=' . $vars['ELEMENT'] .
                                   '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                                   '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
                                   '&PURPOSE=' . $vars['PURPOSE'] .
                                   '&MODE=' . $vars['MODE'];
                    locate_next_screen($url);
               }
          }
          else
          {
               if (!array_key_exists('CLEAR', $vars) &&
                   !array_key_exists('ALL', $vars))
               {
                    if (array_key_exists('FIELD', $vars))
                    {
//
// pull together an array of arguments  
//
                         $selected = null;
                         $field_array = explode(',', $vars['UNIVERSE']);
                         foreach ($field_array as $num => $item) 
                         {
                              if (array_key_exists($item, $vars))
                              {
                                   $selected[] = $item;
                              }
                         }
                         if ($selected != null)
                         {
                              if (sizeof($selected) < MAX_QUERY_FIELDS)
                              {

//
// gather metadata  
//
                                   $LOCATION = determine_type($vars['ELEMENT-TYPE']);
                                   $CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);
                                   $METADATA_CLASS = new ClassMetadata($vars['ELEMENT'], 
                                                                       $CONFIGURATION->getValue('SELECTION_RESOURCE'));
                                   $standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
                                   $systemClass = $METADATA_CLASS->getSystemClass($CONFIGURATION->getValue('SELECTION_CLASS'),
                                                                                  $standardNames);
                                   $METADATA = new TableMetadata($vars['ELEMENT'], 
                                                                 $systemClass);
                                   $METADATA->read();

//
// for each item
//
                                   foreach ($selected as $key => $visible_name) 
                                   {
                                        $lookupName = $METADATA->findLookupName($visible_name, 
                                                                                $standardNames);
//
// fill in the form with server defaults
//
                                        if ($lookupName != null)
                                        {
                                             $L_METADATA = new LookupTypeMetadata($vars['ELEMENT'], 
                                                                                  $lookupName);
                                             $L_METADATA->read();
                                             if ($L_METADATA->exists())
                                             {
                                                  $lookupType = $METADATA->findLookupType($visible_name, 
                                                                                          $standardNames);
                                                  $CONFIGURATION->setVariable('$' . $visible_name . '_FORM',
                                                                              $L_METADATA->asArray($lookupType));
                                             }
                                        }
                                   }

//
// update configuration 
//
                                   $CONFIGURATION->setValue($vars['FIELD'], implode(',', $selected));
                                   $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);

                                   $url = $SCREEN[$vars['PASSTHRU']] . 
                                          '?ELEMENT=' . $vars['ELEMENT'] .
                                          '&ELEMENT-TYPE=' . $vars['ELEMENT-TYPE'] .
                                          '&PURPOSE=' . $vars['PURPOSE'] .
                                          '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
                                          '&MODE=' . $vars['MODE'];
                                   locate_next_screen($url);
                              }
                         }
                    }
               }
          }
     }
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();

//
// create message
//
if (array_key_exists('SINGLE_VALUE', $vars))
{
     if ($vars['PURPOSE'])
     {
          $message = 'Choose a Single ' . $vars['PURPOSE'] . ' Element';
     }
     else
     {
          $message = 'Pick a Single Element';
     }
}
else
{
     if ($vars['PURPOSE'])
     {
          $message = 'Choose ' . $vars['PURPOSE'] . ' Elements';
     }
     else
     {
          $message = 'Pick Elements';
     }
}

//
// read file
//
$SOURCE = new Source();
$CONFIGURATION = $SOURCE->getConfiguration($vars['ELEMENT']);

//
// lookup query elements using COMMON/model.php 
//
$resource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
$class = $CONFIGURATION->getValue('SELECTION_CLASS');
$METADATA_CLASS = new ClassMetadata($vars['ELEMENT'], 
                                    $resource);
$standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
$systemClass = $METADATA_CLASS->getSystemClass($class,
                                               $standardNames);
$METADATA_TABLE = new TableMetadata($vars['ELEMENT'], 
                                    $systemClass);
$field_array = $METADATA_TABLE->findNames($standardNames);
$trans = $METADATA_TABLE->findNames($standardNames, true);

//
// check existing selection
//
$selected = null;
if (array_key_exists('VALUE', $vars))
{
     $old = explode(',', $vars['VALUE']);
     foreach ($old as $num => $item) 
     {
          if (array_key_exists('PRESELECT', $vars))
          {
               if ($vars['PRESELECT'] == $item)
               {
                    $selected[$item] = true;
               }
          }
          else
          {
               $selected[$item] = true;
          }
     }
}
else
{
     if (array_key_exists('PRESELECT', $vars))
     {
          $selected[$vars['PRESELECT']] = true;
     }
}

//
// add notation for searchable 
// 
$notation = 'Not Searchable';
$notational = false;
foreach ($field_array as $key => $value) 
{
     if (!$METADATA_TABLE->isSearchable($value, $standardNames))
     { 
          $notational[$value] = true;
     }
}

//
// render items
//
if (array_key_exists('SINGLE_VALUE', $vars))
{
     $items[] = $FORMATTER->formatCheckList($field_array,
                                            $selected,
                                            $vars['FIELD'],
                                            $trans,
                                            true,
                                            $notational,
                                            $notation);
     $items[] = $FORMATTER->formatHiddenField('SINGLE_VALUE', 
                                              $vars['PURPOSE']);
}
else
{
//
// mark selected fields 
//
     $fieldValue = $CONFIGURATION->getValue($vars['FIELD']);
     $universe = explode(',', $fieldValue);
//     $universe = explode(',', $vars['UNIVERSE']);
     if (array_key_exists('CLEAR', $vars))
     {
          $selected = null;
     }
     if (array_key_exists('ALL', $vars))
     {
          $selected = null;
          foreach ($universe as $key => $value) 
          {
               $selected[$value] = true;
          }
     }

//
// generate display field
//
     $items = null;
     $items[] = $FORMATTER->formatCheckList($field_array,
                                            $selected,
                                            false,
                                            $trans,
                                            true,
                                            $notational,
                                            $notation);
}

$items[] = $FORMATTER->formatHiddenField('ELEMENT', 
                                         $SOURCE->toName($vars['ELEMENT']));
$items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');
$items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
$items[] = $FORMATTER->formatHiddenField('FIELD', $vars['FIELD']);
$items[] = $FORMATTER->formatHiddenField('PURPOSE', $vars['PURPOSE']);
$items[] = $FORMATTER->formatHiddenField('PASSTHRU', $vars['PASSTHRU']);
$items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 
                                         $vars['PASSTHRU-LOCATION']);
$items[] = $FORMATTER->formatHiddenField('CANCEL-LOCATION', $vars['PASSTHRU-LOCATION']);
$items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', TRUE);

if (array_key_exists('SINGLE_VALUE', $vars))
{
     $FORMATTER->printForm($items, 
                           $SCREEN['SOURCE_QUERY_HELP'],
                           $message,
                           $message,
                           false,
                           false,
                           false,
                           null,
                           null,
                           true);
}
else
{
     $FORMATTER->printForm($items, 
                           $SCREEN['SOURCE_QUERY_HELP'],
                           $message,
                           $message,
                           false,
                           true,
                           true,
                           null,
                           null,
                           true);
}

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
