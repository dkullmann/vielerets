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
//print_r($_SERVER);
//
// using model.php 
//
$quitURL = $SCREEN['MAIN_INDEX'];
$STYLIST = new Stylist();

$SOURCE = new Source();
$sources = $SOURCE->existing();
if ($sources === false) {
     locate_next_screen($quitURL);
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

// using view.php 
//
$FORMATTER = new TableFormatter();

//
// start frame
//
print($FORMATTER->renderStartFrameItem() .
      $FORMATTER->renderStartPanel('Administration Interface') .
      $FORMATTER->renderStartInnerFrame() .
      '<form action="' . $quitURL . '">' . "\r\n" .
      $FORMATTER->renderStartInnerFrameItem(null, null)); 

//
// sources
//
$items = null;
$order = null;
$items['Create'] = $SCREEN['NEW_SOURCE'];
$order[] = 'Create';
if ($sources) {
     $items['Edit'] = $SCREEN['EXISTING_SOURCE'];
     $order[] = 'Edit';
     $items['Copy'] = $SCREEN['COPY_SOURCE'];
     $order[] = 'Copy';
     $items['Rename'] = $SCREEN['RENAME_SOURCE'];
     $order[] = 'Rename';
     $items['Delete'] = $SCREEN['REMOVE_SOURCE'];
     $order[] = 'Delete';
}
print(choiceTable('SOURCE',
                  $order,
                  $items,
                  $FORMATTER,
                  $STYLIST,
                  $sources));
 
//
// targets 
//
$items = null;
$order = null;
$items['Create'] = $SCREEN['NEW_TARGET'];
$order[] = 'Create';
$TARGET = new Target();
$targets = $TARGET->existing();
if ($targets) {
     $items['Edit'] = $SCREEN['EXISTING_TARGET'];
     $order[] = 'Edit';
     $items['Copy'] = $SCREEN['COPY_TARGET'];
     $order[] = 'Copy';
     $items['Rename'] = $SCREEN['RENAME_TARGET'];
     $order[] = 'Rename';
     $items['Delete'] = $SCREEN['REMOVE_TARGET'];
     $order[] = 'Delete';
}
print(choiceTable('TARGET',
                  $order,
                  $items,
                  $FORMATTER,
                  $STYLIST,
                  $targets));

//
// extracts
//
if ($sources && $targets) {
     $items = null;
     $order = null;
     $items['Create'] = $SCREEN['NEW_EXTRACT'];
     $order[] = 'Create';
     $EXTRACT = new Extract();
     $extracts = $EXTRACT->existing();
     if ($extracts) {
          $items['Edit'] = $SCREEN['EXISTING_EXTRACT'];
          $order[] = 'Edit';
          $items['Copy'] = $SCREEN['COPY_EXTRACT'];
          $order[] = 'Copy';
          $items['Rename'] = $SCREEN['RENAME_EXTRACT'];
          $order[] = 'Rename';
          $items['Delete'] = $SCREEN['REMOVE_EXTRACT'];
          $order[] = 'Delete';
     }
     print(choiceTable('EXTRACT',
                       $order,
                       $items,
                       $FORMATTER,
                       $STYLIST,
                       $extracts));
}

//
// system 
//
$items = null;
$order = null;
$items['About'] = $SCREEN['ABOUT'] . '?PASSTHRU=SETUP_INDEX';
$order[] = 'About';
$items['Documentation'] = $SCREEN['DOCUMENTATION'];
$order[] = 'Documentation';
print(choiceTable('SYSTEM',
                  $order,
                  $items,
                  $FORMATTER,
                  $STYLIST));

//
// buttons
//
print($FORMATTER->renderStartInnerFrameItem(2, null) .
      '      <table align="center" cellspacing="0" cellpadding="' .
      PANEL_SPACING .
      '" border="0" width="100%">' . CRLF .
      '        <tr align="center">' . CRLF .
      '          <td>' . CRLF .
      '<input type="submit" value="Quit"/>' . CRLF .
      '          </td>' . CRLF .
      '        </tr>' . CRLF .
      '      </table>' . CRLF .
      $FORMATTER->renderEndInnerFrameItem());

//
// end frame 
//
$message = null;
if (array_key_exists('MESSAGE', $vars)) {
     $message = $vars['MESSAGE'];
}
print('</form>' . CRLF .
      $FORMATTER->renderEndInnerFrame() .
      $FORMATTER->renderEndPanel($message) .
      $FORMATTER->renderEndFrameItem());

$FORMATTER->finish();

$HTML->finish();

function choiceTable($title,
                     $order,
                     $items,
                     $FORMATTER,
                     $STYLIST,
                     $showItems = null) {
     $buffer = $FORMATTER->renderStartInnerFrameItem(null, 'left') .
               $STYLIST->formatBoldText($title) . CRLF .
               '  </td>' . CRLF .
               '  <td>' . CRLF .
               '<table align="left" cellspacing="0" cellpadding="' . PANEL_SPACING . '" border="0">' . CRLF .
               '  <tr>' . CRLF;

     foreach ($order as $key => $value) {
          $buffer .= '    <td>' . CRLF .
                     $FORMATTER->createLink($items[$value],$value) . CRLF .
                     '    </td>' . CRLF;
     }

     if ($showItems) {
          $buffer .= '    <td>' . CRLF .
                     $FORMATTER->formatText('(' . $showItems . ' defined)') . CRLF .
                     '    </td>' . CRLF;
     }

     $buffer .= '  </tr>' . CRLF .
                '</table>' . CRLF .
                $FORMATTER->renderEndInnerFrameItem();

     return $buffer;
}

//
//------------

?>
