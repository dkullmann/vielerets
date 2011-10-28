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

define('META_COLUMN_INDICATOR', '{META_COLUMN}');
define('META_COLUMN_SUPPORT', true);

class BCFParser {
     var $collected_data;
     var $collection_count;
     var $xml_parser;
     var $queryName;
     var $queryType;
     var $extractName;
     var $queryValues;
     var $advancedQuery;
     var $limit;
     var $last_run;
     var $quiet = false;
     var $updateBCF = false;
     var $runSync = false;
     var $updateOnly = false;

     function BCFParser() {
          $this->xml_parser = xml_parser_create();
     }

     function accumulateData($parser,
                             $data) {
          $this->collected_data[++$this->collection_count] = trim($data);
     }

     function parse($buffer,
                    $isFinal = true) {
//
// initialize variables
//
          $this->collection_count = 0;
          $this->collected_data = null;

//
// parse
//
          xml_set_object($this->xml_parser, $this);
          xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($this->xml_parser,
                                  'startElement',
                                  'endElement');
          xml_set_character_data_handler($this->xml_parser, 'accumulateData');
          xml_parse($this->xml_parser, $buffer, $isFinal);
//
// check for errors
//
          $perror_code =  xml_get_error_code($this->xml_parser);
          if ($perror_code > 0) {
               $message = 'Batch Control File error [' .
                           xml_error_string($perror_code) .
                           '].';
               $desc = 'Check the format of your Batch Control File.';
               print("\r\n" . $message . "\r\n" . $desc . "\r\n");
               exit;
          }
//
// finish with parser
//
          if ($isFinal) {
               xml_parser_free($this->xml_parser);
          }
     }

     function startElement($parser,
                           $name,
                           $attrs) {
          $this->collection_count = 0;
          $this->collected_data = null;
          switch ($name) {
               case 'QUERY_VALUE':
                    $this->queryName = null;
                    $this->queryType = null;
                    if (array_key_exists('NAME', $attrs)) {
                         $this->queryName = $attrs['NAME'];
                    }
                    if (array_key_exists('TYPE', $attrs)) {
                         $this->queryType = $attrs['TYPE'];
                    }
                    break;

               case 'QUIET':
                    $this->quiet = true;
                    break;

               case 'UPDATE_BCF':
                    $this->updateBCF = true;
                    break;

               case 'RUN_SYNC':
                    $this->runSync = true;
                    break;

               case 'UPDATE_ONLY':
                    $this->updateOnly = true;
                    break;
          }
     }

     function endElement($parser,
                         $name) {
          if ($this->collected_data) {
               switch($name) {
                    case 'EXTRACT':
                         $this->extractName = trim(implode($this->collected_data,' '));
                         break;

                    case 'QUERY_VALUE':
                         $value = trim(implode($this->collected_data,' '));
                         if ($this->queryType == 'ADVANCED') {
                              $this->advancedQuery = $value;
                         } else {
                              if ($this->queryName != null) {
                                   if ($this->queryValues == null) {
                                        $this->queryValues[$this->queryName] = $value;
                                   } else {
                                        if (array_key_exists($this->queryName, $this->queryValues)) {
                                             $temp = $this->queryValues[$this->queryName];
                                             if (is_array($temp)) {
                                                  $temp[] = $value;
                                                  $this->queryValues[$this->queryName] = $temp;
                                             } else {
                                                  $list[] = $temp;
                                                  $list[] = $value;
                                                  $this->queryValues[$this->queryName] = $list;
                                             }
                                        } else {
                                             $this->queryValues[$this->queryName] = $value;
                                        }
                                   }
                              }
                         }
                         break;

                    case 'LIMIT':
                         $this->limit = trim(implode($this->collected_data,' '));
                         break;

                    case 'QUIET':
                         $this->quiet = true;
                         break;

                    case 'UPDATE_BCF':
                         $this->updateBCF = true;
                         break;

                    case 'RUN_SYNC':
                         $this->runSync = true;
                         break;

                    case 'UPDATE_ONLY':
                         $this->updateOnly = true;
                         break;

                    case 'LAST_RUN':
                         $this->last_run = implode(null, $this->collected_data);
                         break;
               }
               $this->collection_count = 0;
               $this->collected_data = null;
          }
     }

     function getName() {
          return $this->extractName;
     }

     function getQueryValues() {
          return $this->queryValues;
     }

     function hasAdvancedQuery() {
          if ($this->advancedQuery == null) {
               return false;
          }

          if (strlen($this->advancedQuery) == 0) {
               return false;
          }

          return true;
     }

     function getAdvancedQuery() {
          return $this->advancedQuery;
     }

     function getLimit() {
          if ($this->limit == null) {
               return 0;
          }

          return $this->limit;
     }

     function isUpdateOnly() {
          return $this->updateOnly;
     }

     function isUpdateBCF() {
          return $this->updateBCF;
     }

     function isRunSync() {
          return $this->runSync;
     }

     function getLastRun() {
          return $this->last_run;
     }

     function isQuiet() {
          return $this->quiet;
     }
}

class Extract
     extends Location {
     var $bcf_directory;
     var $name;
     var $metaColumnIndicator = META_COLUMN_INDICATOR;
     var $supportsMetaColumns = META_COLUMN_SUPPORT;

     function Extract($directory = null) {
          if ($directory == null) {
               parent::Location(EXTRACT_DIRECTORY);
          } else {
               parent::Location($directory);
          }
          $this->bcf_directory = BCF_DIRECTORY;
     }

     function synchronizationAvailable() {
          $names = $this->getExistingNames();
          if ($names != null) {
               foreach ($names as $file => $path) {
                    if ($this->supportsSynchronization($file)) {
                         return true;
                    }
               }
          }

          return false;
     }

     function supportsSynchronization($configName) {
          $CONFIGURATION = $this->getConfiguration($configName);
          $TARGET = new Target();
          if ($TARGET->supportsSynchronization($CONFIGURATION->getValue('TARGET'))) {
               if ($CONFIGURATION->getBooleanValue('MLS_ONLY')) {
                    return true;
               }
          }
          return false;
     }

     function getSynchronizableNames() {
          $names = $this->getExistingNames();
          if ($names == null) {
               return null;
          }
          $result = null;
          foreach ($names as $file => $nothing) {
               if ($this->supportsSynchronization($file)) {
                    $result[$file] = $file;
               }
          }
          return $result;
     }

     function isValidConfiguration($aName) {
          $CONFIGURATION = $this->getConfiguration($aName);
          $isValid = true;
          $arrayExists = array_key_exists('SOURCE', $CONFIGURATION->getVariable('MAP'));
          $listExists = false;
          if ($CONFIGURATION->getValue('COLUMN_LIST') != null) {
               $listExists = true;
          }
          if ($arrayExists && $listExists) {
               $isValid = false;
          }
          if (!$isValid) {
               return false;
          }
          return true;
     }

     function moveConfiguration($oldName,
                                $newName) {
//
// BCF rename 
//
          $this->copyControlFile($oldName, $newName, true);
          $this->removeControlFile($oldName);

//
// rename BCFs that reference this extract 
//
          $list = $this->getExistingControlFiles($oldName);
          if ($list != null) {
               foreach ($list as $key => $value) {
                    $this->copyControlFile($value, $newName, true, false);
               }
          }

//
// move the configuration
//
          $this->copyConfiguration($oldName, $newName);
          $this->removeConfiguration($oldName);

     }

     function detectMetaColumns($map) {
          if (!$this->supportsMetaColumns()) {
               return null;
          }
          if ($map == null) {
               return null;
          }
          $sMap = $map['SOURCE'];
          $tMap = $map['TARGET'];
          $metaColumn = null;
          foreach ($sMap as $key => $value) {
               if ($value == $this->metaColumnIndicator) {
                    $metaColumn[$key] = $tMap[$key];
               }
          }

          return $metaColumn;
     }

     function supportsMetaColumns() {
          return $this->supportsMetaColumns;
     }

     function getMetaColumnIndicator() {
          return $this->metaColumnIndicator;
     }

     function getControlFilePath($configName) {
          return $this->bcf_directory . '/' . $configName;
     }

     function getExistingControlFiles($aName) {
          $result = null;
          $fp = @opendir($this->bcf_directory);
          if (!$fp) {
               return -1;
          }
          while (false !== ($file=readdir($fp))) {
               if ($file != '.' && $file != '..') {
                    if ($file != 'CVS' && $file != '.svn') {
                         $PARSER = new BCFParser();
                         $aBCF = $this->bcf_directory . '/' . $file;
                         $PARSER->parse(file_get_contents($aBCF));
                         if ($aName == $PARSER->getName()) {
                              $result[] = $file;
                         }
                    }
               }
          }
          closedir($fp);
        
          return $result;
     }

     function removeControlFile($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               unlink($bcfPath);
          }
     }

     function copyControlFile($oldName,
                              $newName,
                              $changeExtractName = false,
                              $changeFileName = true) {
          $oldBCF = $this->bcf_directory . '/' . $oldName;
          if (file_exists($oldBCF)) {

//
// read old BCF
//
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($oldBCF));

//
// check if extract name should change 
//
               if ($changeExtractName) {
                    $extractName = $newName;
               } else {
                    $extractName = $PARSER->getName();
               }

//
// check timestamp 
//
               $withTimestamp = true;
               $lastRun = $PARSER->getLastRun();
               if ($lastRun == null) {
                    $withTimestamp = false;
               }

//
// check if file name should change 
//
               if ($changeFileName) {
                    $fileName = $newName;
               } else {
                    $fileName = $oldName;
               }

//
// advanced query
//
               $advancedQuery = null;
               if ($PARSER->hasAdvancedQuery()) {
                    $advancedQuery = $PARSER->getAdvancedQuery();
               }

//
// check limit 
//
               $limit = $PARSER->getLastRun();

//
// write file
//
               $this->createControlFile($fileName,
                                        $limit,
                                        $extractName,
                                        $PARSER->getQueryValues(),
                                        $withTimestamp,
                                        $today = null,
                                        $PARSER->isUpdateOnly(),
                                        $PARSER->isUpdateBCF(),
                                        $PARSER->isRunSync(),
                                        $lastRun,
                                        $PARSER->isQuiet(),
                                        $advancedQuery);
               return true;
          }

          return false;
     }

     function renameControlFile($oldName,
                                $newName) {
          $oldBCF = $this->bcf_directory . '/' . $oldName;
          if (file_exists($oldBCF)) {
               if ($this->copyControlFile($oldName, $newName, true)) {
                    unlink($oldBCF);
                    return true;
               }
          }

          return false;
     }

     function createControlFile($configName,
                                $limit,
                                $extractName = null,
                                $queryValues = false,
                                $withTimestamp = false,
                                $today = null,
                                $withUpdateOnly = false,
                                $withUpdateBCF = false,
                                $withSynchronize = false,
                                $timestampValue = null,
                                $withQuietMode = false,
                                $advancedQuery = null) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               unlink($bcfPath);
          }
          $dh = fopen($bcfPath, 'wb');

//
// header
//
          fwrite($dh, '<VIELE_BATCH>' . "\r\n");

//
// extract
//
          $temp = $configName;
          if ($extractName != null) {
               $temp = $extractName;
          }
          fwrite($dh, "\t" . '<EXTRACT>' . "\r\n" . $temp . "\r\n\t" . '</EXTRACT>' . "\r\n");

//
// quiet mode
//
          if ($withQuietMode) {
               fwrite($dh, "\t<QUIET/>\r\n");
          } else {
               fwrite($dh, "<!--\r\n\t<QUIET/>\r\n-->\r\n");
          }

//
// update bcf mode
//
          if ($withUpdateBCF) {
               fwrite($dh, "\t<UPDATE_BCF/>\r\n");
          } else {
               fwrite($dh, "<!--\r\n\t<UPDATE_BCF/>\r\n-->\r\n");
          }

//
// run synchronize mode
//
          if ($withSynchronize) {
               fwrite($dh, "\t<RUN_SYNC/>\r\n");
          } else {
               fwrite($dh, "<!--\r\n\t<RUN_SYNC/>\r\n-->\r\n");
          }

//
// update only mode
//
          if ($withUpdateOnly) {
               fwrite($dh, "\t<UPDATE_ONLY/>\r\n");
          } else {
               fwrite($dh, "<!--\r\n\t<UPDATE_ONLY/>\r\n-->\r\n");
          }

//
// limit 
//
          fwrite($dh, "\t<LIMIT>\r\n" .
                      $limit . "\r\n" .
                      "\t</LIMIT>\r\n");

//
// advanced query 
//
          if ($advancedQuery != null) {
               fwrite($dh, "\t<QUERY_VALUE type=\"ADVANCED\">\r\n" .
                           $advancedQuery . "\r\n" .
                           "\t</QUERY_VALUE>\r\n");
          }

//
// query items
//
          $buffer = null;
          if ($queryValues) {
               foreach ($queryValues as $key => $val) {
                    if (is_array($val)) {
                         foreach ($val as $key2 => $val2) {
                              $buffer .= $this->priv_createQueryValueTag($key,
                                                                         $val2);
                         }
                    } else {
                         $buffer .= $this->priv_createQueryValueTag($key,
                                                                    $val);
                    }
               }
          } else {
               $CONFIGURATION = $this->getConfiguration($configName);
               $SOURCE = new Source();
               $S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));
               $query_items = $S_CONFIGURATION->getValue('QUERY_ITEMS');
               $args = null;
               if (strlen(trim($query_items)) != 0) {
                    $args = explode(',', $query_items);
               }
               foreach ($args as $key => $val) {
                    $buffer .= $this->priv_createQueryValueTag($val);
               }
          }
          fwrite($dh, $buffer);

//
// last run
//
          if ($withTimestamp) {
               if ($timestampValue == null) {
                    $seconds = $today['seconds'];
                    $minutes = $today['minutes'];
                    $hours = $today['hours'];
                    $day = $today['mday'];
                    $month = $today['mon'];
                    $year = $today['year'];

                    --$seconds;
                    if ($seconds < 0) {
                         $seconds = 60 - $seconds;
                         if ($seconds > 59) {
                              $seconds = 59;
                         }
                         --$minutes;
                         if ($minutes < 0) {
                              $minutes = 60 - $minutes;
                              --$hours;
                              if ($hours < 0) {
                                   $hours = 24 - $hours;
                                   --$day;
                                   if ($day < 0) {
                                        switch ($month) {
                                             case '02':
                                                  $max_days = 28;
                                                  break;

                                             case '04':
                                                  $max_days = 31;
                                                  break;

                                             case '06':
                                                  $max_days = 31;
                                                  break;

                                             case '09':
                                                  $max_days = 31;
                                                  break;

                                             case '11':
                                                  $max_days = 31;
                                                  break;
   
                                             default:
                                                  $max_days = 31;
                                        }
                                        $day = $max_days - 1;
                                        --$month; 
                                        if ($month < 0) {
                                             $month = 12 - $month;
                                             --$year;
                                        }
                                   }
                              }
                         }
                    }
                    if (strlen($day) < 2) {
                         $day = '0' . $day;
                    }
                    if (strlen($month) < 2) {
                         $month = '0' . $month;
                    }
                    if (strlen($hours) < 2) {
                         $hours = '0' . $hours;
                    }
                    if (strlen($minutes) < 2) {
                         $minutes = '0' . $minutes;
                    }
                    if (strlen($seconds) < 2) {
                         $seconds = '0' . $seconds;
                    }
                    $lastRun = $year . '-' .
                               $month . '-' .
                               $day . 'T' .
                               $hours . ':' .
                               $minutes . ':' .
                               $seconds;
//echo "<XMP>is $lastRun</XMP>";
               } else {
                    $lastRun = $timestampValue;
               }
               fwrite($dh,
                      "\t" . '<LAST_RUN>' . "\r\n" . $lastRun . "\r\n\t" . '</LAST_RUN>' . "\r\n");
          }

//
// footer
//
          fwrite($dh, '</VIELE_BATCH>' . "\r\n");
          fclose($dh);

     }

     function priv_createQueryValueTag($key,
                                       $value = null) {
          $buffer = "\t" . '<QUERY_VALUE name="' . $key . '">' . "\r\n";
          if ($value != null) {
               $buffer .= $value . "\r\n";
          }
          $buffer .= "\t" . '</QUERY_VALUE>' . "\r\n";

          return $buffer;
     }

     function getLastQueryValues($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               return $PARSER->getQueryValues();
          }

          return null;
     }

     function getLastRun($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               return $PARSER->getLastRun();
          }

          return null;
     }

     function getWithUpdates($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               if ($PARSER->isUpdateOnly()) {
                    return 'true';
               }
          }

          return 'false';
     }

     function getUpdateBCF($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               if ($PARSER->isUpdateBCF()) {
                    return 'true';
               }
          }

          return 'false';
     }

     function getSyncBCF($configName) {

          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               if (!$PARSER->isRunSync()) {
                    return 'false';
               }
          }

          return 'true';
     }

     function getLimitBCF($configName) {
          $bcfPath = $this->bcf_directory . '/' . $configName;
          if (file_exists($bcfPath)) {
               $PARSER = new BCFParser();
               $PARSER->parse(file_get_contents($bcfPath));
               return $PARSER->getLimit();
          }

          return false;
     }

}

?>
