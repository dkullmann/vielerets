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
include_once(COMMON_DIRECTORY . '/download.php');

if (!defined('STDIN')) {
/**
 *@const STDIN holds stdin information
 */
define('STDIN', fopen('php://stdin', 'r'));
}

//print('CD ' . COMMON_DIRECTORY);

// pre declare class 
//
class ProcessResults 
     extends AbstractProcessResults {

     function ProcessResults() {
          parent::AbstractProcessResults();
     }

     function printErrors() {
          $buffer = CRLF;
          $spacer = '  ';
          foreach ($this->errors as $key => $value) {
               $buffer .= $spacer . $value . CRLF;
          }
          flush_output($buffer);
     }

}

class Statistics
     extends AbstractStatistics {

     function Statistics($name) {
          parent::AbstractStatistics($name);
          $this->setSpacer('  ');
          $this->setEOL(CRLF);
     }

     function printErrors() {
          $buffer = CRLF;
          $spacer = '  ';
          foreach ($this->errors as $key => $value) {
               $buffer .= $spacer . $value . CRLF;
          }
          flush_output($buffer);
     }

     function printBatchStart() {
          flush_output(CRLF . $this->getStart() . CRLF);
     }

     function printBatchDetail($detail) {
          $spacer = '  ';
          $buffer = $spacer . $detail . CRLF;
          if ($this->hasErrors()) {
               foreach ($this->errors as $key => $value) {
                    $buffer .= $spacer . $value . CRLF;
               }
          }
          flush_output($buffer);
     }

     function printBatchNote() {
          flush_output('.');
     }

     function printBatchSummary() {
          flush_output(CRLF . $this->getSummary());
     }

     function getStart() {
          $this->markStart();
          return 'Starting ' . $this->name . 
                 ' at ' . date('F j, Y g:i:s A', $this->start) . ' (GMT' . 
                 CRLF;
     }

     function getSummary($RETRIEVER = null) {
          $this->markFinish();
          $spacer = '  ';
          $buffer = $this->name . ' Completed at ' . 
                    date('F j, Y g:i:s A', $this->finish) . ' (GMT)' .
                    CRLF;

          $buffer .= CRLF;

          $buffer .= 'Listings Processed: ' .
                     $this->getProcessed() .
                     CRLF;

          $buffer .= $spacer .
                     'Additions: ' .
                     $this->getAdditions() .
                     CRLF;

          $buffer .= $spacer .
                     'Duplicates found: ' .
                     $this->getDuplicates() .
                     CRLF;

          $buffer .= $spacer .
                     $spacer .
                     'Refreshed: ' .
                     $this->getRefreshed() .
                     CRLF;

          $buffer .= $spacer .
                     $spacer .
                     'Skipped: ' .
                     $this->getSkipped() .
                     CRLF;

          $buffer .= CRLF;

          $buffer .= 'Images Processed: ' .
                     $this->getImages() .
                     CRLF;

          $buffer .= $spacer .
                     'Full: ' .
                     $this->getRaws() .
                     CRLF;

          $buffer .= $spacer .
                     'Thumbnails: ' .
                     $this->getThumbs() .
                     CRLF;

          $buffer .= $spacer .
                     'Missing or Corrupt: ' .
                     $this->getMissing() .
                     CRLF;

          $buffer .= CRLF;

          if ($this->start != null) {
               $buffer .= 'Performance ';
               $elapsed = $this->getRunTime();
               if ($elapsed == 0) {
                    $elapsed = 1;
               }
               $count = $this->getProcessed();
               $rate = round($count / $elapsed, 4);
               if ($count == 0) {
                    $speed = 0;
               } else {
                    $speed = round($elapsed / $count, 4);
               }
               $buffer .= $spacer .
                          'Total Elapsed Seconds: ' .
                          number_format($elapsed, 0) .
                          CRLF;

               if ($RETRIEVER != null) {
                    $networkTime = $RETRIEVER->getNetworkTime();
                    $buffer .= $spacer .
                               $spacer .
                               'MLS Server: ' .
                               number_format($networkTime, 0) .
                               CRLF;

                    $buffer .= $spacer .
                               $spacer .
                               'VieleRETS: ' .
                               number_format($elapsed = $networkTime, 0) .
                               CRLF;
               }

               $buffer .= $spacer .
                          'Items per Second: ' .
                          number_format($rate, 4) .
                          CRLF;

               $buffer .= $spacer .
                          'Seconds per Item: ' .
                          number_format($speed, 4) .
                          CRLF;

          }

          return $buffer;
     }
}

class InactiveStatistics
     extends AbstractStatistics {

     function InactiveStatistics($name) {
          parent::AbstractStatistics($name);
          $this->setSpacer('  ');
          $this->setEOL(CRLF);
     }

     function printErrors() {
          $buffer = CRLF;
          $spacer = '  ';
          foreach ($this->errors as $key => $value) {
               $buffer .= $spacer . $value . CRLF;
          }
          flush_output($buffer);
     }

     function printBatchStart() {
          flush_output(CRLF . $this->getStart() . CRLF);
     }

     function printBatchDetail($detail) {
          $spacer = '  ';
          $buffer = $spacer . $detail . CRLF;
          if ($this->hasErrors()) {
               foreach ($this->errors as $key => $value) {
                    $buffer .= $spacer . $value . CRLF;
               }
          }
          flush_output($buffer);
     }

     function printBatchNote() {
          flush_output('.');
     }

     function printBatchSummary() {
          flush_output(CRLF . $this->getSummary());
     }

     function getStart() {
          $this->markStart();
          return 'Starting ' . $this->name . 
                 ' at ' . date('F j, Y g:i:s A', $this->start) . ' (GMT' . 
                 CRLF;
     }

     function getSummary($RETRIEVER = null) {
          $this->markFinish();
          $spacer = '  ';
          $buffer = $this->name . ' Completed at ' . 
                    date('F j, Y g:i:s A', $this->finish) . ' (GMT)' .
                    CRLF;

          $buffer .= CRLF;

          $buffer .= 'Listings Processed: ' .
                     $this->getProcessed() .
                     CRLF;

          $buffer .= $spacer .
                     'Changes made: ' .
                     $this->getOrphans() .
                     CRLF;

          $buffer .= CRLF;

          if ($this->start != null) {
               $buffer .= 'Performance ';
               $elapsed = $this->getRunTime();
               if ($elapsed == 0) {
                    $elapsed = 1;
               }
               $count = $this->getProcessed();
               $rate = round($count / $elapsed, 4);
               if ($count == 0) {
                    $speed = 0;
               } else {
                    $speed = round($elapsed / $count, 4);
               }
               $buffer .= $spacer .
                          'Total Elapsed Seconds: ' .
                          number_format($elapsed, 0) .
                          CRLF;

               if ($RETRIEVER != null) {
                    $networkTime = $RETRIEVER->getNetworkTime();
                    $buffer .= $spacer .
                               $spacer .
                               'MLS Server: ' .
                               number_format($networkTime, 0) .
                               CRLF;

                    $buffer .= $spacer .
                               $spacer .
                               'VieleRETS: ' .
                               number_format($elapsed = $networkTime, 0) .
                               CRLF;
               }

               $buffer .= $spacer .
                          'Items per Second: ' .
                          number_format($rate, 4) .
                          CRLF;

               $buffer .= $spacer .
                          'Seconds per Item: ' .
                          number_format($speed, 4) .
                          CRLF;

          }

          return $buffer;
     }
}

class SynchronizationStatistics
     extends AbstractStatistics {

     function SynchronizationStatistics($name) {
          parent::AbstractStatistics($name);
          $this->setSpacer('  ');
          $this->setEOL(CRLF);
     }

     function printErrors() {
          $buffer = CRLF;
          $spacer = '  ';
          foreach ($this->errors as $key => $value) {
               $buffer .= $spacer . $value . CRLF;
          }
          flush_output($buffer);
     }

     function printBatchStart() {
          flush_output(CRLF . $this->getStart() . CRLF);
     }

     function printBatchDetail($detail) {
          $spacer = '  ';
          $buffer = $spacer . $detail . CRLF;
          if ($this->hasErrors()) {
               foreach ($this->errors as $key => $value) {
                    $buffer .= $spacer . $value . CRLF;
               }
          }
          flush_output($buffer);
     }

     function printBatchNote() {
          flush_output('.');
     }

     function printBatchSummary() {
          flush_output(CRLF . $this->getSummary());
     }

     function getStart() {
          $this->markStart();
          return 'Starting ' . $this->name . 
                 ' at ' . date('F j, Y g:i:s A', $this->start) . ' (GMT' . 
                 CRLF;
     }

     function getSummary($RETRIEVER = null) {
          $this->markFinish();
          $spacer = '  ';
          $buffer = $this->name . ' Completed at ' . 
                    date('F j, Y g:i:s A', $this->finish) . ' (GMT)' .
                    CRLF;

          $buffer .= CRLF;

          $buffer .= 'Listings Processed: ' .
                     $this->getProcessed() .
                     CRLF;

          $buffer .= $spacer .
                     'Orphans found: ' .
                     $this->getOrphans() .
                     CRLF;

          $buffer .= CRLF;

          $buffer .= 'Images Processed: ' .
                     $this->getImages() .
                     CRLF;

          $buffer .= $spacer .
                     'Full: ' .
                     $this->getRaws() .
                     CRLF;

          $buffer .= $spacer .
                     'Thumbnails: ' .
                     $this->getThumbs() .
                     CRLF;

          $buffer .= CRLF;

          if ($this->start != null) {
               $buffer .= 'Performance ';
               $elapsed = $this->getRunTime();
               if ($elapsed == 0) {
                    $elapsed = 1;
               }
               $count = $this->getProcessed();
               $rate = round($count / $elapsed, 4);
               if ($count == 0) {
                    $speed = 0;
               } else {
                    $speed = round($elapsed / $count, 4);
               }
               $buffer .= $spacer .
                          'Total Elapsed Seconds: ' .
                          number_format($elapsed, 0) .
                          CRLF;

               if ($RETRIEVER != null) {
                    $networkTime = $RETRIEVER->getNetworkTime();
                    $buffer .= $spacer .
                               $spacer .
                               'MLS Server: ' .
                               number_format($networkTime, 0) .
                               CRLF;

                    $buffer .= $spacer .
                               $spacer .
                               'VieleRETS: ' .
                               number_format($elapsed = $networkTime, 0) .
                               CRLF;
               }

               $buffer .= $spacer .
                          'Items per Second: ' .
                          number_format($rate, 4) .
                          CRLF;

               $buffer .= $spacer .
                          'Seconds per Item: ' .
                          number_format($speed, 4) .
                          CRLF;

          }

          return $buffer;
     }
}

set_time_limit(0);
$old_value = ini_set('default_socket_timeout','6000');
$quiet = false;
$updateBCFsetting = true;
$syncFlag = false;
$lastRun = null;
$isAdvancedQuery = false;
$limit = 0;

//
// lookup configurations 
//
$EXTRACT = new Extract();
$result = $EXTRACT->getExistingNames();

if ($argc > 1) {
//
// read from XML file if specified
//
     $batchPath = $argv[1];
     if (!file_exists($batchPath)) {
          print('Batch file ' . $batchPath . ' not found.' . CRLF);
          exit;
     }

//
// parse the contents
//
     $PARSER = new BCFParser();
     $PARSER->parse(file_get_contents($batchPath));
     $isAdvancedQuery = $PARSER->hasAdvancedQuery();

// read from XML file and check the extract 
//
     $extract = $PARSER->getName();
     $found = false;
     foreach ($result as $config => $target) {
          if ($target == $extract) {
               $found = true;
          }
     }
     if (!$found) {
          print('Specified Extract [' . $extract . '] not found.' . CRLF);
          exit;
     }

//
// check the quiet setting 
//
     $quiet = $PARSER->isQuiet();
     if (!$quiet) {
          printBatchBanner();
          print('Reading arguments from Batch Control file [' . 
                $batchPath . 
                '].' . CRLF . CRLF);
     }

//
// check the lastRun setting if the last run was an update 
//
     if ($PARSER->isUpdateOnly()) {
          $lastRun = $PARSER->getLastRun();
     }

//
// check the updateBCF setting
//
     $updateBCFsetting = $PARSER->isUpdateBCF();

//
// check synchronization setting
//
     $syncFlag = $PARSER->isRunSync();

//
// set the limit
//
     $limit = $PARSER->getLimit();
} else {
     printBatchBanner();
//
// interactive mode 
//
     print('Interactive Mode' . CRLF);
}

//
// obtain an extract name from the user
//
if (!$quiet) {
     print('-----------------------' . CRLF); 
}
if ($argc == 1) {
//
// interactive mode 
//
     $extract = null;
     if (sizeof($result) == 1) {
          foreach ($result as $config => $target) {
               $extract = $target;
          }
     } else {
          displayList('Choose an Extract to Run:', $result);
          $extract = collectInput('ENTER Extract');
          if ($extract == 'CANCEL') {
               exit;
          }
     }
}

$updatePull = false;
if (!$quiet) {
     print('EXTRACT ' . $extract . CRLF);
     print('-----------------------' . CRLF); 

     if ($argc == 1) {
          $lastRun = $EXTRACT->getLastRun($extract);
          $pos = strpos($lastRun, 'T');
          print('The last update was performed on ' . 
                substr($lastRun, 0, $pos) .
                ' at ' .  
                substr($lastRun, $pos + 1, strlen($lastRun)) .
                ' (GMT)' .  
                CRLF);

          $updatePull = true;
          $updateValue = collectInput('Only load updates (y)?');
          if ($updateValue != 'CANCEL') {
               if (strtoupper($updateValue) != 'Y') {
                    $updatePull = false;
                    $lastRun = null;
               }
          }
     }
} else {
     if ($argc == 1) {
          $lastRun = $EXTRACT->getLastRun($extract);
          $updatePull = true;
     }
}

$CONFIGURATION = $EXTRACT->getConfiguration($extract);

//
// determine source 
//
$SOURCE = new Source();
$S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));

//
// build list of query items 
//
$query_items = $S_CONFIGURATION->getValue('QUERY_ITEMS');
if (strlen(trim($query_items)) == 0) {
     print('No QUERY_ITEMS defined in the SOURCE.' . CRLF);
     exit;
}
//
// construct a single array with all values
//
$args = explode(',', $query_items);
$query_array = null;
foreach ($args as $key => $val) {
     if (strlen($val) > 0) {
          $fname = $val . '_FORM';
          $query_array[$val] = $S_CONFIGURATION->getVariable($fname);
     }
}

//
// gather user input
//
$query_values = null;
if (!$quiet) {
     print('------------' . CRLF); 
     print('QUERY' . CRLF); 
} 

if ($argc > 1) {
//
// check for advanced query
//
     if (!$isAdvancedQuery) {

//
// read from XML file and check query arguments 
//
          $bArgs = $PARSER->getQueryValues();
          if ($bArgs != null) {
               foreach ($bArgs as $key => $value) {
                    if (array_key_exists($key, $query_array)) {
                         if (is_array($value)) {
                              $query_values[$key] = $value;
                         } else {
                              if (strlen($value) > 0) {
                                   $query_values[$key] = $value;
                              }
                         }
                    } else {
                         print('  Value for [' . $key . '] ignored.' . CRLF);
                    }
               }
          }
     }
} else {
//
// interactive mode 
//
     if (!$updatePull) {
          foreach ($query_array as $key => $values) {
               if (sizeof($values) > 0) {
                    displayList('VALUES for ' . $key, $values);
               }
               $qValue = collectInput('ENTER value for ' . $key, false);
               if ($qValue != 'CANCEL') {
                    if ($qValue != null) {
                         $query_values[$key] = $qValue;
                    }
               }
          }
     } else {
          $bArgs = $EXTRACT->getLastQueryValues($extract);
          if ($bArgs != null) {
               foreach ($bArgs as $key => $value) {
                    if (array_key_exists($key, $query_array)) {
                         if (is_array($value)) {
                              $query_values[$key] = $value;
                         } else {
                              if (strlen($value) > 0) {
                                   $query_values[$key] = $value;
                              }
                         }
                    } else {
                         print('  Value for [' . $key . '] ignored.' . CRLF);
                    }
               }
          }
     }
//
// set the limit
//
     $limit = $EXTRACT->getLimitBCF($extract);
     $qValue = collectInput('Limit the number of records (' . $limit . ')?');
     if ($qValue != 'CANCEL') {
          if ($qValue != null) {
               $limit = $qValue;
          }
     }
}
if (!$quiet) {
     if ($isAdvancedQuery) {
          print('Advanced query processing will be used' . CRLF); 
     } else {
          if (sizeof($query_values) == 0) {
               if ($updatePull) {
                    print('Only updates will be processed' . CRLF); 
               } else {
                    print('No QUERY Values being used' . CRLF); 
               }
          } else {
               displayList('QUERY VALUES', $query_values, true);
               if ($updatePull) {
                    print('Only updates with these conditions be processed' . CRLF); 
               }
          }
     }
     print('------------' . CRLF); 
     print(CRLF);
} 

//
// advanced queries 
//
$advancedQuery = null;
if ($isAdvancedQuery) {
     $advancedQuery = $PARSER->getAdvancedQuery();
}

//
// debug 
//
$debugBatch = false;
if (!$quiet) {
     $debugBatch = true;
     $debugDevice = './logs/batch.log';
     $debugValue = collectInput('Capture debug information (y)?');
     if ($debugValue != 'CANCEL') {
          if (strtoupper($debugValue) != 'Y') {
               $debugBatch = false;
          }
     }
     if ($debugBatch) {
          $debugValue = collectInput('Debug capture device (' . $debugDevice . ')');
          if ($debugValue != 'CANCEL') {
               $debugDevice = $debugValue;
          }
     }
}

//
// should synchronize be called 
//
$debugSyncDevice = './logs/synchronize_batch.log';
$checkSync = $EXTRACT->supportsSynchronization($extract);
if ($checkSync) {
     if ($argc == 1) {
          if (!$quiet) {
               $updateValue = collectInput('Do you want to run synchronization (n)?');
               if ($updateValue != 'CANCEL') {
                    if (strtoupper($updateValue) != 'N') {
                         $syncFlag = true;
                    }
               }
          }
     }
}

$DOWNLOADER = new Downloader();
if ($debugBatch) {
     $DOWNLOADER->setDebug($debugBatch,$debugDevice);
}

$DOWNLOADER->processBatch($quiet,
                          $extract,
                          $query_values,
                          $CONFIGURATION,
                          $S_CONFIGURATION,
                          $lastRun,
                          $limit,
                          $advancedQuery,
                          $syncFlag);

//
// update the Batch Control File
//
$updateBCF = true;
if (!$updateBCFsetting) {
     $updateBCF = false;
} else {
     if ($argc == 1) {
          if (!$quiet) {
               $updateValue = collectInput('Do you want to update the Batch Control File (y)?');
               if ($updateValue != 'CANCEL') {
                    if (strtoupper($updateValue) != 'Y') {
                         $updateBCF = false;
                    }
               }
          }
     }
}
if ($updateBCF) { 
     $DOWNLOADER->createControlFile($S_CONFIGURATION,
                                    $query_values,
                                    $extract,
                                    $lastRun,
                                    true,
                                    $syncFlag,
                                    $quiet,
                                    $advancedQuery);
}

//
// run synchronization if necessary
//
if ($syncFlag) {
     $DOWNLOADER->processSynchronization($quiet,
                                 $extract,
                                 $query_values,
                                 $CONFIGURATION,
                                 $S_CONFIGURATION,
                                 $advancedQuery);
}

function printBatchBanner() {
//
// print tool information
//
     print('--------------------------------------------------' . CRLF .
           PROJECT_NAME . ' ' . PROJECT_VERSION . CRLF .
           reverse_htmlentities(PROJECT_COPYRIGHT) . CRLF .
           '--------------------------------------------------' . CRLF .
           CRLF);
}

function collectInput($display = null,
                      $no_nulls = true) {
     if ($display != null) {
          print($display);
     } 
     print('>'); 
     $input = trim(fgets(STDIN)); 
     if ($no_nulls) {
          if (strlen($input) == 0) {
              return 'CANCEL';
          }
     }
     return $input;
}

function displayList($title,
                     $list,
                     $keys_too = false) {
     print($title . CRLF); 
     foreach ($list as $key => $value) {
          $display = $value;
          if (is_array($display)) {
               $display = null;
               foreach ($value as $key2 => $value2) {
                    $display .= '[' . $value2 . '] ';
               }
          } else {
               if (strlen($display) == 0) {
                    $display = '[NO VALUE]';
               }
          }
          if ($keys_too) {
               print('  ' . $key . '=' . $display . CRLF);
          } else {
               print('  ' . $display . CRLF);
          }
     }
}

function reverse_htmlentities($mixed) {
     $htmltable = Array();
     $htmltable['(C)'] = '&copy;';
     $htmltable['(R)'] = '&reg;';
//     $htmltable = get_html_translation_table(HTML_ENTITIES);
     foreach($htmltable as $key => $value) {
          $mixed = ereg_replace(addslashes($value),$key,$mixed);
     }
     return $mixed;
}

function flush_output($buffer) {
     print($buffer);
     flush();
}

function printBatchErrors($partial,
                          $listingCount) {
     if ($partial) {
          print(PARTIAL_RESULT_MESSAGE . CRLF);
     }
     if ($listingCount == 0) {
          print(NO_ITEMS_MESSAGE . CRLF);
     }
}

function printBatchCompletion() {
     print(CRLF . 'Processing Completed' . CRLF);
}

function postExecuteStatistics($S_CONFIGURATION,
                               $RETRIEVER) { 

//
// no stats
//

//$temp = $RETRIEVER->getNetworkTime();
//print('NetworkTime $temp' . CRLF);

}

//
//------------

?>
