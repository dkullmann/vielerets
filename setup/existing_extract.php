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

$EXTRACT = new Extract();
$result = $EXTRACT->getExistingNames();
if (sizeof($result) == 1)
{
     $file = null;
     foreach ($result as $config => $target) 
     {
          $file = $target;
          $CONFIGURATION = $EXTRACT->getConfiguration($config);
     }
     $SOURCE = new Source();
     $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
     $options = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
     if (sizeof($options) < FAST_MAP_THRESHOLD) {
          $url = $SCREEN['EXTRACT_DEFINE'] . 
                 '?ELEMENT=' . $file .
                 '&ELEMENT-TYPE=EXTRACT' . 
                 '&MODE=UPDATE';
     } else {
          $url = $SCREEN['EXTRACT_MENU'] .  '?ELEMENT=' . $file;
     }
     locate_next_screen($url);
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();
$items = null;
$valign = null;

$field = $FORMATTER->formatRadioField('ELEMENT',
                                      DEFAULT_CONFIG_NAME,
                                      $result,
                                      'Extract to edit',
                                      true);

$items[] = $field;
$valign[$field] = 'top';

$FORMATTER->printForm($items, 
                      $SCREEN['EXTRACT_MENU'],
                      'Choose a Extract to Edit',
                      'Open an Extract',
                      false,
                      false,
                      false,
                      $valign);

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
