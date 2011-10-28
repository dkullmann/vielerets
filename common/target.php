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

class Target
     extends Location {

     function Target($directory = null) {
          if ($directory == null) {
               parent::Location(MAIN_DIRECTORY . "/targets");
               parent::Location(TARGET_DIRECTORY);
          } else {
               parent::Location($directory);
          }
     }

     function requiresClasses($CONFIGURATION) {
          return $this->requiresClassesPrimitive($CONFIGURATION->getValue("CLASS_TABLE"));
     }

     function requiresClassesPrimitive($classTable) {
          if ($classTable != null) {
               if (strlen($classTable) > 0) {
                    return true;
               }
          }

          return false;
     }

     function supportsSynchronization($configName) {
          $T_CONFIGURATION = $this->getConfiguration($configName);
          switch ($T_CONFIGURATION->getValue('TYPE')) {
               case 'BASE':
                    return false;

               case 'CSV':
                    return false;

               case 'OR':
                    return true;

               case 'RDB':
                    return true;

               case 'RDB':
                    return false;
          }

          return false;
     }

     function isValidConfiguration($aName) {
          $T_CONFIGURATION = $this->getConfiguration($aName);
          $isValid = true;
          switch ($T_CONFIGURATION->getValue('TYPE')) {
               case 'BASE':
                    $isValid = false;
                    break;

               case 'CSV':
                    if (!is_writable($T_CONFIGURATION->getValue('DATA_DOWNLOAD_PATH'))) {
                         $isValid = false;
                    }
                    if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES')) {
                         if (!$T_CONFIGURATION->getBooleanValue('IMAGE_REFERENCE_ONLY')) {
                              if (!is_writable($T_CONFIGURATION->getValue('IMAGE_DOWNLOAD_PATH'))) {
                                   $isValid = false;
                              }
                         }
                    }
                    break;

               case 'OR':
//
// open database 
//
                    $conn = ADONewConnection($T_CONFIGURATION->getValue('BRAND'));
                    @$conn->PConnect($T_CONFIGURATION->getValue('SERVER'),
                                     $T_CONFIGURATION->getValue('ACCOUNT'),
                                     $T_CONFIGURATION->getValue('PASSWORD'),
                                     $T_CONFIGURATION->getValue('DATABASE'));
                    if (!$conn->isConnected()) {
                         $isValid = false;
                    }

//
// read config file
//
                    $or_common_file_name = '/include/common.php';
                    $file_name = $T_CONFIGURATION->getValue('OPEN_REALTY_INSTALL_PATH') .
                                 $or_common_file_name;
                    if (!file_exists($file_name)) {
                         $isValid = false;
                    }
                    $fd = fopen ($file_name, 'r');
                    $contents = fread ($fd, filesize ($file_name));
                    fclose ($fd);

//
// read version info from the database 
//
                    preg_match('/config\["table_prefix_no_lang"\] = "(.*?)"/is',
                               $contents,
                               $matches);
                    $table_prefix_no_lang = $matches[1];
                    $sql = 'SELECT * FROM '.$table_prefix_no_lang.'controlpanel';
                    $recordSet = $conn->Execute($sql);
                    if ($recordSet == null) {
                         $isValid = false;
                    }
                    if (!$recordSet) {
                         $isValid = false;
                    }
                    if ($recordSet->fields['controlpanel_version'] == null) {
                         $isValid = false;
                    }

//
// make sure images can be written
//
                    if (!is_writable($recordSet->fields['controlpanel_basepath'] .
                                     '/images/listing_photos')) {
                         $isValid = false;
                    }
                    $conn->Close();

//
// make sure VieleRETS config matches OR
//
                    if ($recordSet->fields['controlpanel_basepath'] . '/images/listing_photos' !=
                        $T_CONFIGURATION->getValue('IMAGE_UPLOAD_PATH')) {
                         $isValid = false;
                    }

                    break;

               case 'RDB':
                    $conn = ADONewConnection($T_CONFIGURATION->getValue('BRAND'));
                    @$conn->PConnect($T_CONFIGURATION->getValue('SERVER'),
                                     $T_CONFIGURATION->getValue('ACCOUNT'),
                                     $T_CONFIGURATION->getValue('PASSWORD'),
                                     $T_CONFIGURATION->getValue('DATABASE'));
                    if (!$conn->isConnected()) {
                         $isValid = false;
                    }
                    $conn->Close();
                    if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES')) {
                         if (!$T_CONFIGURATION->getBooleanValue('IMAGE_REFERENCE_ONLY')) {
                              if (!is_writable($T_CONFIGURATION->getValue('IMAGE_DOWNLOAD_PATH'))) {
                                   $isValid = false;
                              }
                         }
                    }
                    break;

               case 'XML':
                    if (!is_writable($T_CONFIGURATION->getValue('DATA_DOWNLOAD_PATH'))) {
                         $isValid = false;
                    }
                    if ($T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES')) {
                         if (!$T_CONFIGURATION->getBooleanValue('IMAGE_REFERENCE_ONLY')) {
                              if (!is_writable($T_CONFIGURATION->getValue('IMAGE_DOWNLOAD_PATH'))) {
                                   $isValid = false;
                              }
                         }
                    }
                    break;
          }
          if (!$isValid) {
               return false;
          }
          return true;
     }

     function moveConfiguration($oldName,
                                $newName) {
//
// change extract references
//
          $universe = $this->getDependentExtractsList($oldName);
          if ($universe != null) {
               foreach ($universe as $key => $value) {
                    $EXTRACT = new Extract();
                    $E_CONFIGURATION = $EXTRACT->getConfiguration($value);
                    $oldTarget = $E_CONFIGURATION->getValue("TARGET");
                    $E_CONFIGURATION->setValue("TARGET", $newName);
                    $EXTRACT->saveConfiguration($E_CONFIGURATION, $value);
               }
          }

//
// move configuration
//
          $this->copyConfiguration($oldName, $newName);
          $this->removeConfiguration($oldName);

     }

     function getDependentExtracts($element) {
//
// develop list of extracts
//
          $EXTRACT = new Extract();
          $result = $EXTRACT->getExisting();

//
// check if this source is in use
//
          $universe = "";
          if ($result != null) {
               foreach ($result as $config => $target) {
                    if (file_exists($target)) {
                         $CONFIGURATION = $EXTRACT->getConfiguration($target);
                         $temp = $CONFIGURATION->getValue("TARGET");
                         $temp = basename($temp);
                         if ($temp == $element) {
                              $universe .= "[" . $config . "],";
                         }
                    }
               }
               $universe = substr($universe, 0, strlen($universe) - 1);
          }

          return $universe;
     }

     function getDependentExtractsList($element) {
//
// develop list of extracts
//
          $EXTRACT = new Extract();
          $result = $EXTRACT->getExisting();

//
// check if this source is in use
//
          $universe = null;
          if ($result != null) {
               foreach ($result as $config => $target) {
                    if (file_exists($target)) {
                         $CONFIGURATION = $EXTRACT->getConfiguration($target);
                         $temp = $CONFIGURATION->getValue("TARGET");
                         $temp = basename($temp);
                         if ($temp == $element) {
                              $universe[] = $config;
                         }
                    }
               }
          }

          return $universe;
     }

}

?>
