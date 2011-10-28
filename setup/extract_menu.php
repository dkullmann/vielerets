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
// create a default return location
//
$quitURL = $SCREEN['SETUP_INDEX'];

if (array_key_exists('CANCEL', $vars)) {
     locate_next_screen($quitURL);
}

//
// read actions 
//
$error = false;
$EXTRACT = new Extract();

if (array_key_exists('REMOVE', $vars)) {
//
// proceed with delete of extract 
//
     $EXTRACT->removeConfiguration($vars['ELEMENT']);
     $EXTRACT->removeControlFile($vars['ELEMENT']);

//
// return to main menu
//
     $url = $quitURL . 
            '?MESSAGE=' . urlencode('Extract [' . $vars['ELEMENT'] . '] removed');
     locate_next_screen($url);
} else {
     if (array_key_exists('RENAME', $vars)) {
//
// guard against illegal names 
//
          $newName = preg_replace('/[^a-zA-Z0-9\-_\.]+/', '_', 
                                  $vars['NEW_NAME']);
//
// check if the name already exists
//
          if ($EXTRACT->exists($newName)) {
               $message = 'Rename failed.  An Extract named [' .
                          $newName . 
                          '] already exists, remove it first';
          } else {
               $oldName = $vars['ELEMENT'];
               if ($newName == $vars['ELEMENT']) {
//
// check if renaming to same name 
//
                    $message = 'Rename failed because the new Extract name</br>' .
                               '[' .
                               $newName . 
                               '] is the same as the old one [' .
                               $oldName .
                               ']';
               } else {

//
// proceed with move 
//
                    $EXTRACT->moveConfiguration($oldName, $newName);
                    $message = 'The Extract [' .
                               $oldName . 
                               '] is now called [' .
                               $newName .
                               ']';
               }
          }

//
// return to main menu
//
          $url = $SCREEN['SETUP_INDEX'] .
                 '?MESSAGE=' . urlencode($message);
          locate_next_screen($url);
     } else {
          if (array_key_exists('COPY', $vars)) {
//
// guard against illegal names 
//
               $newName = preg_replace('/[^a-zA-Z0-9\-_\.]+/', '_', 
                                       $vars['NEW_NAME']);
               $oldName = $vars['ELEMENT'];
               if ($newName == $oldName) {
//
// check if renaming to same name 
//
                    $nextURL = $SCREEN['SETUP_INDEX'];
                    $args = '?MESSAGE=' . urlencode('Copy failed because the new Extract name</br>' .
                                                    '[' .
                                                    $newName . 
                                                    '] is the same as the old one [' .
                                                    $oldName .
                                                    ']');
               } else {
//
// proceed with copy 
//
                    $nextURL = $SCREEN['EXTRACT_MENU'];
                    $EXTRACT->copyControlFile($oldName, $newName, true);
                    $EXTRACT->copyConfiguration($oldName, $newName);

                    $args = '?ELEMENT=' . $newName .
                            '&MESSAGE=' . urlencode('The Extract [' .
                                                    $oldName . 
                                                    '] has a copy called [' .
                                                    $newName .
                                                    ']');
               }

//
// return to main menu
//
               $url = $nextURL . $args;
               locate_next_screen($url);
          }
     }
}

//
// render
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();

if (!$error) {
     $CONFIGURATION = $EXTRACT->getConfiguration($vars['ELEMENT']);
     $TARGET = new Target();
     $T_CONFIGURATION = $TARGET->getConfiguration($CONFIGURATION->getValue('TARGET'));
     $type = $T_CONFIGURATION->getValue('TYPE');
     $args = '?ELEMENT=' . $vars['ELEMENT'];

     $items[] = $FORMATTER->createLink($SCREEN['EXTRACT_DEFINE'] . $args . 
                                       '&ELEMENT-TYPE=EXTRACT' .
                                       '&MODE=UPDATE',
                                       $FORMATTER->formatBoldText('Settings'));

//     if (!$T_CONFIGURATION->getBooleanValue('AUTO_CREATE')) {
          $SOURCE = new Source();
          $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
          $options = explode(',', $S_CONFIGURATION->getValue('SUMMARY_ITEMS'));
          if (sizeof($options) >= FAST_MAP_THRESHOLD) {
               if ($type == 'OR' || $type == 'RDB') {
                    $items[] = $FORMATTER->createLink($SCREEN['EXTRACT_MAP'] . $args . '&ELEMENT-TYPE=EXTRACT',
                                                      $FORMATTER->formatBoldText('Data Map'));
                    $items[] = $FORMATTER->createLink($SCREEN['EXTRACT_MAP_ORIG'] . $args .
                                                      '&LEVEL=EXPERT', 
                                                      $FORMATTER->formatBoldText('Data Map (Expert)'));
                    if ($EXTRACT->detectMetaColumns($CONFIGURATION->getVariable('MAP')) != null) {
                         $items[] = $FORMATTER->createLink($SCREEN['EXTRACT_META_COLUMN'] . $args . 
                                                           '&ELEMENT-TYPE=EXTRACT' .
                                                           '&FIELD=MAP',
                                                           $FORMATTER->formatBoldText('Meta Columns'));
                    }
               }
          }
//     }

     $message = 'Editing extract [' . $vars['ELEMENT'] . ']';
     if (array_key_exists('MESSAGE', $vars)) {
          $message = $vars['MESSAGE'];
     }

     $items[] = $FORMATTER->formatHiddenField('MESSAGE', 
                                              'Extract [' . $vars['ELEMENT'] . '] closed');

}

$FORMATTER->printMenu($items, 
                      $quitURL, 
                      $message,
                      'Extract');

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
