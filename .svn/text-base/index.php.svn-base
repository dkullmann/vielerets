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

//
// uncoment the next include if you would like to use the 
// Open-Realty admin account to limit access
//
//include_once('./extras/auth.php');

include_once('./controller.php');

//
// make sure all directories are created
//
$externalPackagesInstallErrors = null;

global $ADODB_CACHE_DIR;
if (!file_exists($ADODB_CACHE_DIR)) {
     if (!@mkdir($ADODB_CACHE_DIR, 0777)) {
          $externalPackagesInstallErrors['ADODB'] = 'ADODB directory [' . $ADODB_CACHE_DIR . ']';
     }
} else {
     $externalPackagesInstallErrors['ADODB'] = false;
}
$externalPackagesInstallErrors['RETS_LITE'] = $retsLiteInstallError;
$FORMATTER = new IndexForms();
$FORMATTER->checkSystemConfiguration($externalPackagesInstallErrors);

//
// using view.php 
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Persistant Downloader');
$FORMATTER = new TableFormatter();
$EXTRACT = new Extract();
$extracts = $EXTRACT->existing();
if ($extracts) {
     $items[] = $FORMATTER->createLink($SCREEN['RUN_JOB'], 
                                       $FORMATTER->formatBoldText('Download Listings'));
     if ($EXTRACT->synchronizationAvailable()) {
          $items[] = $FORMATTER->createLink($SCREEN['RUN_SYNCHRONIZE'], 
                                            $FORMATTER->formatBoldText('Synchronize Listings'));
     }
}
$items[] = $FORMATTER->createLink($SCREEN['SETUP_INDEX'], 
                                  $FORMATTER->formatBoldText('Adminstration Interface'));
$items[] = $FORMATTER->createLink($SCREEN['ABOUT'] . '?PASSTHRU=MAIN_INDEX',
                                  $FORMATTER->formatBoldText('About'));
//$items[] = $FORMATTER->createLink($SCREEN['TEST'] . '?PASSTHRU=MAIN_INDEX',
//                                  $FORMATTER->formatBoldText('Asynchronous Job Submission'));

$message = 'Choose a Task';
if (array_key_exists('MESSAGE', $vars)) {
     $message = $vars['MESSAGE'];
}

$FORMATTER->printMenu($items,
                      null,
                      $message,
                      'Main Menu');
$FORMATTER->finish();

$HTML->finish();

//
//------------

?>
