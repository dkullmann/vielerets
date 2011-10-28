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
$SOURCE = new Source();

if (array_key_exists('REMOVE', $vars)) {
//
// check dependencies 
//
     $error = checkSourceDependents($vars['ELEMENT'], $SOURCE);
     if ($error != null) {
//
// render dependencies
//
          $FORMATTER = new TableFormatter(false);
          $items[] = $FORMATTER->formatBoldList($error);
          $message = 'Source [' . $vars['ELEMENT'] . '] cannot be removed.';
     } else {
//
// proceed with delete of metadata 
//
          $METADATA = new Metadata($vars['ELEMENT']);
          $METADATA->remove();

//
// proceed with delete of source
//
          $SOURCE->removeConfiguration($vars['ELEMENT']);

//
// return to main menu
//
          locate_next_screen($quitURL . '?MESSAGE=' . urlencode('Source [' . $vars['ELEMENT'] . '] removed'));
     }
} else {
     if (array_key_exists('RENAME', $vars)) {
//
// guard against illegal names 
//
          $newName = preg_replace("/[^a-zA-Z0-9\-_\.]+/", '_', $vars['NEW_NAME']);
//
// check if the name already exists
//
          if ($SOURCE->exists($newName)) {
               $message = 'Rename failed.  A Source named [' .
                          $newName . 
                          '] already exists, remove it first';
          } else {
               $oldName = $vars['ELEMENT'];
               if ($newName == $oldName) {
//
// check if renaming to same name 
//
                    $message = 'Rename failed because the new Source name</br>' .
                               '[' . $newName . '] is the same as the old one [' . $oldName . ']';
               } else {
//
// proceed with move 
//
                    moveSourceDependents($oldName, $newName, $SOURCE);
                    $SOURCE->moveConfiguration($oldName, $newName);
                    $message = 'The Source [' . $oldName . '] is now called [' . $newName . ']';
               }
          }

//
// return to main menu
//
          locate_next_screen($SCREEN['SETUP_INDEX'] .  '?MESSAGE=' . urlencode($message));
     } else {
          if (array_key_exists('COPY', $vars)) {
//
// guard against illegal names 
//
               $newName = preg_replace("/[^a-zA-Z0-9\-_\.]+/", '_', $vars['NEW_NAME']);
               $oldName = $vars['ELEMENT'];
               if ($newName == $oldName) {
//
// check if copying to same name 
//
                    $nextURL = $SCREEN['SETUP_INDEX'];
                    $args = '?MESSAGE=' . urlencode('Copy failed because the new Source name</br>' .
                                                    '[' . $newName . '] is the same as the old one [' . $oldName . ']');
               } else {
//
// proceed with copy 
//
                    $nextURL = $SCREEN['SOURCE_MENU'];
                    $SOURCE->copyMetadata($oldName, $newName);
                    $SOURCE->copyConfiguration($oldName, $newName);

                    $args = '?ELEMENT=' . $newName .
                            '&MESSAGE=' . urlencode('The Source [' .  $oldName . '] has a copy called [' . $newName . ']');
               }
//
// return to main menu
//
               locate_next_screen($nextURL . $args); 
          }
     }
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();

if ($error == null) {
     $args = '?ELEMENT=' . $vars['ELEMENT'] . '&MODE=UPDATE';

//
// multi screen
//
     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_CONNECTION'] . $args . '&ELEMENT-TYPE=SOURCE' , 
                                       $FORMATTER->formatBoldText('Connection Settings'));

     $items[] = $FORMATTER->createLink($SCREEN['CONFIRM_ACTION'] . $args .
                                       '&TARGET=SOURCE_DEFINE_RESOURCE' . 
                                       '&ELEMENT-TYPE=SOURCE' . 
                                       '&PASSTHRU-LOCATION=SOURCE_MENU' . 
                                       '&MESSAGE=' . urlencode('Auto-Detect and reset fields'),
                                       $FORMATTER->formatBoldText('Auto-Detect Server Capabilities'));


//
// single screen
//
     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_OVERRIDE'] . 
                                       '?ELEMENT=' . $vars['ELEMENT'] .
                                       '&ELEMENT-TYPE=SOURCE' . 
                                       '&MODE=OVERRIDE' .
                                       '&PASSTHRU-LOCATION=SOURCE_MENU',
                                       $FORMATTER->formatBoldText('Override Auto-Detected Settings'));

     $items[] = $FORMATTER->createLink($SCREEN['CONFIRM_ACTION'] . $args .
                                       '&TARGET=SOURCE_REFRESH_METADATA' . 
                                       '&PASSTHRU-LOCATION=SOURCE_MENU' . 
                                       '&MESSAGE=' . urlencode('Refresh Metadata'),
                                       $FORMATTER->formatBoldText('Refresh Metadata'));

     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_INFORMATION_DISPLAY'] . $args . '&ELEMENT-TYPE=SOURCE', 
                                       $FORMATTER->formatBoldText('Informational Displays'));

//-------------
//     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_REALTIME'] . $args . '&ELEMENT-TYPE=SOURCE', 
//                                       $FORMATTER->formatBoldText('Realtime Processing'));
//-------------

     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_DEPENDENTS'] . $args . '&ELEMENT-TYPE=SOURCE', 
                                       $FORMATTER->formatBoldText('Dependent Settings'));

     $items[] = $FORMATTER->createLink($SCREEN['SOURCE_QUERY_FORMS'] . $args, 
                                       $FORMATTER->formatBoldText('Search Forms'));

     $message = 'Editing source [' . $vars['ELEMENT'] . ']';
     if (array_key_exists('MESSAGE', $vars)) {
          $message = $vars['MESSAGE'];
     }

     $items[] = $FORMATTER->formatHiddenField('MESSAGE', 
                                              'Source [' . $vars['ELEMENT'] . '] closed');

}

$FORMATTER->printMenu($items, 
                      $quitURL, 
                      $message,
                      'Source');

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
