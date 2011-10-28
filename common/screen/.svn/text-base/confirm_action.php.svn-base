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
// using view.php 
//
$FORMATTER = new TableFormatter();

$items = null;
$items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
$items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $vars['PASSTHRU-LOCATION']);

if (array_key_exists('ELEMENT-TYPE', $vars))
{
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $vars['ELEMENT-TYPE']);
}

if (array_key_exists('MODE', $vars))
{
     $items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
}

//
//render form 
//
$FORMATTER->printForm($items, 
                      $SCREEN[$vars['TARGET']], 
                      'This could be dangerous.<br/>' .
                      'There is no recovery from this action.<br/>' .
                      'Do you really want to do this?',
                      'About to ' . $vars['MESSAGE']);

$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
