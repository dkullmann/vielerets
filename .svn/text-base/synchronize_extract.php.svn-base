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

if (array_key_exists('CANCEL', $vars)) {
     locate_next_screen($vars['LOCATION']);
}

$EXTRACT = new Extract();
$CONFIGURATION = $EXTRACT->getConfiguration($vars['ELEMENT']);

//
// look at the sanity of the EXTRACT
//
$aName = $CONFIGURATION->getName();
if (!$EXTRACT->isValidConfiguration($aName)) {
     $metadataStatus = 'EXTRACT [' . $aName . '] is not valid';
     $HTML = new HTMLPage();
     $HTML->start(PROJECT_NAME . ' Persistant Downloader');
     $FORMATTER = new TableFormatter();
     $items[] = $FORMATTER->renderError($metadataStatus);
     $FORMATTER->printNotice($items, 
                           './index.php',
                           'Correct the definition of this EXTRACT');
     $FORMATTER->finish();
     $HTML->finish();
     exit(0);
}

//
// determine source 
//
$SOURCE = new Source();
$S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));

//
// local definitions
//
$submit_url = './synchronize_results.php';

//
// determine if there are any items to show
//
$query_items = $S_CONFIGURATION->getValue('QUERY_ITEMS');
if (strlen(trim($query_items)) == 0) {
     $url = $submit_url . 
            '?LOCATION=./index.php' .
            '&ELEMENT=' . $vars['ELEMENT'];
     locate_next_screen($url);
}

//
// make sure metadata is valid
//
$aName = $S_CONFIGURATION->getName();
if (!$SOURCE->isValidConfiguration($aName)) {
     $metadataStatus = 'Metadata for SOURCE [' . $aName . '] is not valid';
     $HTML = new HTMLPage();
     $HTML->start(PROJECT_NAME . ' Persistant Downloader');
     $FORMATTER = new TableFormatter();
     $items[] = $FORMATTER->renderError($metadataStatus);
     $FORMATTER->printNotice($items, 
                           './index.php',
                           'Refresh the metadata for this SOURCE');
     $FORMATTER->finish();
     $HTML->finish();
     exit(0);
}

//
// make sure TARGET is sane 
//
$TARGET = new Target();
$T_CONFIGURATION = $TARGET->getConfiguration($CONFIGURATION->getValue('TARGET'));
$aName = $T_CONFIGURATION->getName();
if (!$TARGET->isValidConfiguration($aName)) {
     $metadataStatus = 'TARGET [' . $aName . '] is not writable';
     $HTML = new HTMLPage();
     $HTML->start(PROJECT_NAME . ' Persistant Downloader');
     $FORMATTER = new TableFormatter();
     $items[] = $FORMATTER->renderError($metadataStatus);
     $FORMATTER->printNotice($items, 
                           './index.php',
                           'Correct the definition for this TARGET');
     $FORMATTER->finish();
     $HTML->finish();
     exit(0);
}

//
// render a form 
//
define('ENTER_QUERY_MESSAGE','Enter Your Search Parameters Above');

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Persistant Downloader');

//
// using view.php 
//

$standardNames = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
$resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
$METADATA_CLASS = new ClassMetadata($CONFIGURATION->getValue('SOURCE'), 
                                    $resource);

$class = $S_CONFIGURATION->getValue('SELECTION_CLASS');
$systemClass = $METADATA_CLASS->getSystemClass($class,
                                               $standardNames);

$METADATA_TABLE = new TableMetadata($CONFIGURATION->getValue('SOURCE'), 
                                    $systemClass);
$name_translation = $METADATA_TABLE->findNames($standardNames, true);

//
// use query values from the BCF (last successful extract) 
//
$query_array = $EXTRACT->getLastQueryValues($vars['ELEMENT']);
//print_r($query_array);

//
// render the form 
//
$FORMATTER = new SynchronizeForms();
print($FORMATTER->renderSpecialForm($name_translation,
                                    $query_array,
                                    $submit_url,
                                    './index.php',
                                    $vars['ELEMENT'],
                                   'Synchronization Criteria',
                                    $query_items,
                                    $S_CONFIGURATION->getValue('DATE_VARIABLE'),
                                    $EXTRACT->getLastRun($vars['ELEMENT']) ));
$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
