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

$TARGET = new Target();
$result = $TARGET->getExistingNames();
if (sizeof($result) == 1) {
     $file = null;
     foreach ($result as $config => $target) {
          $file = $target;
     }
     $url = $SCREEN['TARGET_DEFINE'] . 
            '?ELEMENT=' . $file .
            '&ELEMENT-TYPE=TARGET' . 
            '&MODE=UPDATE';
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

$visualResult = null;
foreach ($result as $key => $value) {
     $T_CONFIGURATION = $TARGET->getConfiguration($key);
     $visualResult[$value . ' (' . $T_CONFIGURATION->getValue('DESCRIPTION') . ')'] = $key;
}
$field = $FORMATTER->formatRadioField('ELEMENT',
                                      DEFAULT_CONFIG_NAME,
                                      $visualResult,
                                      'Target to edit',
                                      true);
$nextScreen = $SCREEN['TARGET_DEFINE'];
$items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'TARGET');
$items[] = $FORMATTER->formatHiddenField('MODE', 'UPDATE');

$items[] = $field;
$valign[$field] = 'top';

$FORMATTER->printForm($items, 
                      $nextScreen, 
                      'Choose a Target to Edit',
                      'Open a Target',
                      false,
                      false,
                      false,
                      $valign);

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
