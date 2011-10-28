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
$TARGET = new Target();
$error = false;

if (array_key_exists('REMOVE', $vars)) {

//
// develop list of dependent extracts 
//
     $tail = null;
     $universe = $TARGET->getDependentExtracts($vars['ELEMENT']);
     if (strlen($universe) > 0) {
          $error = true;
          if (sizeof(explode(',', $universe)) == 1) {
               $tail[] = 'In use by extract ' .  $universe .  '.';
          } else {
               $tail[] = 'In use by extracts ' .  $universe .  '.';
          }
     }
     if ($error) {
//
// render dependencies
//
          $FORMATTER = new TableFormatter(false);
          $items[] = $FORMATTER->formatBoldList($tail);
          $message = 'Target [' . 
                     $vars['ELEMENT'] . 
                     '] cannot be removed.';
     } else {
//
// proceed with delete of target 
//
          $TARGET->removeConfiguration($vars['ELEMENT']);

//
// return to main menu
//
          $url = $quitURL . 
                 '?MESSAGE=' . urlencode('Target [' . $vars['ELEMENT'] . '] removed');
          locate_next_screen($url);
     }
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
          if ($TARGET->exists($newName)) {
               $message = 'Rename failed.  A Target named [' .
                          $newName . 
                          '] already exists, remove it first';
          } else {
               $oldName = $vars['ELEMENT'];
               if ($newName == $oldName) {
//
// check if renaming to same name 
//
                    $message = 'Rename failed because the new Target name</br>' .
                               '[' .
                               $newName . 
                               '] is the same as the old one [' .
                               $oldName .
                               ']';
               } else {

//
// proceed with move 
//
                    $TARGET->moveConfiguration($oldName, $newName);
                    $message = 'The Target [' .
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
                    $args = '?MESSAGE=' . urlencode('Copy failed because the new Target name</br>' .
                                                    '[' .
                                                    $newName . 
                                                    '] is the same as the old one [' .
                                                    $oldName .
                                                    ']');
               } else {
//
// proceed with copy 
//
                    $nextURL = $SCREEN['TARGET_MENU'];
                    $TARGET->copyConfiguration($oldName, $newName);
                    $args = '?ELEMENT=' . $newName .
                            '&MESSAGE=' . urlencode('The Target [' .
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

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();

if (!$error) {

     $items[] = $FORMATTER->createLink($SCREEN['TARGET_DEFINE'] . 
                                       '?ELEMENT=' . $vars['ELEMENT'] .
                                       '&ELEMENT-TYPE=TARGET' . 
                                       '&MODE=UPDATE' ,
                                       $FORMATTER->formatBoldText('Settings'));

     $message = 'Editing target [' . $vars['ELEMENT'] . ']';
     if (array_key_exists('MESSAGE', $vars)) {
          $message = $vars['MESSAGE'];
     }

     $items[] = $FORMATTER->formatHiddenField('MESSAGE', 
                                              'Target [' . $vars['ELEMENT'] . '] closed');

}

$FORMATTER->printMenu($items, 
                      $quitURL, 
                      $message,
                      'Target');

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
