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

class Metadata 
{

     var $path;
     var $target;
     var $parsed_contents;

     function Metadata($aName, 
                       $aRelationship = null,
                       $anExtension = null)
     {
          $this->path = METADATA_DIRECTORY .
                        '/' .
                        $aName; 
          if (!file_exists($this->path))
          {
               mkdir($this->path, 0777);
          }
          if ($anExtension != null)
          {
               $basePath = $this->path;
               $this->path .= '/' . 
                              $anExtension;
          }
          if (!file_exists($this->path))
          {
//
// create intermediate directories
//
               $temp = explode('/', $this->path);
               $found = false;
               $i = 0;
               while (!$found)
               {
                    if ($temp[$i] == 'metadata')
                    {
                         $found = true;
                    }
                    ++$i;
               }
               ++$i;
               $size = sizeof($temp) - 1;
               $tempPath = $basePath;
               while ($i < $size)
               {
                    $tempPath .= '/' . $temp[$i];
                    mkdir($tempPath, 0777);
                    ++$i;
               }
//
// create the directory we are interested in
//
               mkdir($this->path, 0777);
          }

//
// create a target
//
          if ($aRelationship != null)
          {
              $this->target = $aRelationship;
          }
          else
          {
              $this->target = $aName;
          }
          $this->target .= '.xml';
     }

     function isValid()
     {
          if ($this->contentsAsString() == null)
          {
               return false;
          }
          return true;
     }

     function contentsAsString()
     {
          if ($this->parsed_contents == null)
          {
               $this->read();
          }
          return $this->parsed_contents;
     }

     function read()
     {
          $this->parsed_contents = null;
          if (file_exists($this->fullPath()))
          {
               $contents = file($this->fullPath());
               if ($contents != null)
               {
                    foreach ($contents as $line_num => $line) 
                    {
                         if ($line != null)
                         {
                              $this->parsed_contents .= $line;
                         }
                    }
               }
          }
     }

     function remove()
     {
          if (file_exists($this->path))
          {
               $this->deldir($this->path);
               clearstatcache();
               $this->parsed_contents = null;
          }
     }

     function deldir($dir) 
     {
          $dh = opendir($dir);
          while ($file = readdir($dh)) 
          {
               if ($file != '.' && $file != '..') 
               {
                    $fullpath = $dir . '/' . $file;
                    if (is_dir($fullpath)) 
                    {
                         $this->deldir($fullpath);
                    } 
                    else 
                    {
                         unlink($fullpath);
                    }
               }
          }
          closedir($dh);
          if (rmdir($dir)) 
          {
               return true;
          } 
          return false;
     }

     function updateContents($contents)
     {
          if (!file_exists($this->path))
          {
               mkdir($this->path, 0777);
          }

          $path = $this->fullPath();
          $fp = fopen($path, 'w');
          $this->parsed_contents = $contents;
          fwrite($fp, $this->parsed_contents);
          fclose($fp);
     }

     function exists()
     {
          return file_exists($this->path);
     }

     function createSubdirectory($aName)
     {
          $lowerPath = $this->path . '/' . $aName;
          if (!file_exists($lowerPath))
          {
               mkdir($lowerPath, 0777);
          }
     }

     function fullPath()
     {
          return $this->path . '/' . basename($this->target);
     }

     function copyMetadata($newName)
     {
          $newPath = METADATA_DIRECTORY .
                     '/' .
                     $newName; 
          $this->copydir($this->path, $newPath);
     }

     function copyDir($dir,
                      $new_dir)
     {
          $dh = opendir($dir);
          if (!file_exists($new_dir))
          {
               mkdir($new_dir, 0777);
          }
          while ($file = readdir($dh)) 
          {
               if ($file != '.' && $file != '..') 
               {
                    $fullpath = $dir . '/' . $file;
                    $new_fullpath = $new_dir . '/' . $file;
                    if (is_dir($fullpath)) 
                    {
                         $this->copydir($fullpath,
                                        $new_fullpath);
                    } 
                    else 
                    {
                         copy($fullpath, $new_fullpath);
                    }
               }
          }
          closedir($dh);
     }

}

class LookupTypeMetadata 
     extends Metadata 
{
     function LookupTypeMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'Lookup/LookupType');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function asArray($lookupType = null)
     {
          $table = $this->contentsAsString();
          $valueParser = new TranslationParser();
          $values = $valueParser->parse($table,
                                        'LongValue',
                                        'Value',
                                        'METADATA-LOOKUP_TYPE');
          if ($lookupType == 'Lookup' || 
              $lookupType == 'LookupMulti' ||
              $lookupType == 'LookupBitmask')
          {
               $dmql = null; 
               foreach ($values as $key => $value) 
               {
                    $dmql[$key] = '|' . $value;
               } 
               return $dmql;
          }
         
          return $values; 
     }
}

class TableMetadata 
     extends Metadata 
{
     function TableMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'Class/Table');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function findInterpretation($fieldName,
                                 $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $nameParser = new TranslationParser();
               $table = $this->contentsAsString();
               $names = $nameParser->parse($table,
                                           $basis,
                                           'Interpretation',
                                           'METADATA-TABLE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         return $value;
                    }
               }
          }

          return null;
     }

     function findMaximumLength($fieldName,
                                $standardNames)
     {
          if ($this->exists())
          {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'MaximumLength',
                                           'METADATA-TABLE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         return $value;
                    }
               }
          }
          return null;
     }

     function findDataType($fieldName,
                           $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $nameParser = new TranslationParser();
               $table = $this->contentsAsString();
               $names = $nameParser->parse($table,
                                           $basis,
                                           'DataType',
                                           'METADATA-TABLE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         return $value;
                    }
               }
          }

          return null;
     }

     function isDisplayable($fieldName,
                            $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Default',
                                           'METADATA-TABLE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         if ($value != -1)
                         {
                              return true;
                         }
                         else
                         {
                              return false;
                         }
                    }
               }
          }

          return false;
     }

     function isSearchable($fieldName,
                           $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Searchable',
                                           'METADATA-TABLE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         if ($this->metadataBoolean($value))
                         {
                              return true;
                         }
                         else
                         {
                              return false;
                         }
                    }
               }
          }

          return false;
     }

     function metadataBoolean($value)
     {
          if ($value == 'Y' || 
              $value == 'True' || 
              $value == 'true' || 
              $value == 'TRUE')
          {
               return true;
          }
          else
          {
               if ($value != 'N' && 
                   $value != 'False' && 
                   $value != 'false' && 
                   $value != 'FALSE')
               {
                    if ($value == true)
                    {
                         return true;
                    }
               }
          }

          return false;
     }

     function findLookupType($fieldName,
                             $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $table = $this->contentsAsString();
               $metadata_type = 'METADATA-TABLE';
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($table,
                                           $basis,
                                           'LookupName',
                                           $metadata_type);
               $lookupParser = new TranslationParser();
               $lookups = $lookupParser->parse($table,
                                               'LookupName',
                                               'Interpretation',
                                               $metadata_type);
               $lookupName = null;
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         if (array_key_exists($value, $lookups))
                         {
                              $type = $lookups[$value];
                              if ($type == 'Lookup' || 
                                  $type == 'LookupMulti' ||
                                  $type == 'LookupBitmask')
                              {
                                   return $type;
                              }
                         }
                    }
               }
          }

          return null;
     }
 
     function findLookupName($fieldName,
                             $standardNames)
     {
          if ($this->exists())
          {
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $table = $this->contentsAsString();
               $metadata_type = 'METADATA-TABLE';
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($table,
                                           $basis,
                                           'LookupName',
                                           $metadata_type);
               $lookupParser = new TranslationParser();
               $lookups = $lookupParser->parse($table,
                                               'LookupName',
                                               'Interpretation',
                                               $metadata_type);
               $lookupName = null;
               foreach ($names as $key => $value) 
               {
                    if ($key == $fieldName)
                    {
                         if (array_key_exists($value, $lookups))
                         {
                              $type = $lookups[$value];
                              if ($type == 'Lookup' || 
                                  $type == 'LookupMulti' ||
                                  $type == 'LookupBitmask')
                              {
                                   return $value;
                              }
                         }
                    }
               }
          }

          return null;
     }
 
     function findTranslations() {
          if ($this->exists()) {
               $this->read();
               $nameParser = new TranslationParser();
               return $nameParser->parse($this->contentsAsString(),
                                         'SystemName',
                                         'StandardName',
                                         'METADATA-TABLE');
          }
          return null;
     }

     function findFields($standardNames)
     {
          return $this->findNames($standardNames, false);
     }

     function findNames($standardNames,
                        $asAssociation = false) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'LongName',
                                           'METADATA-TABLE');
               if ($asAssociation) {
                    return $names;
               }
               $list = null;
               foreach ($names as $key => $value) {
                    $list[] = $key;
               }
               return $list;
          }
          return null;
     }

     function findDBNames($standardNames,
                          $asAssociation = false)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'DBName',
                                           'METADATA-TABLE');
               if ($asAssociation)
               {
                    return $names;
               }
               $list = null;
               foreach ($names as $key => $value) 
               {
                    $list[] = $key;
               }
               return $list;
          }
          return null;
     }

     function findDisplayFields($standardNames,
                                $showHidden = false) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
//print("EXISTS<br>");
//print("<XMP>" . $this->contentsAsString() . "</XMP>");
//print("<br>");
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Default',
                                           'METADATA-TABLE');
               $list = null;
               $hidden_array = array();
               foreach ($names as $key => $value) {
                    if ($value != -1) {
                         $list[] = $key;
                    } else {
                         $hidden_array[$key] = true;
                    }
               }
               if ($showHidden) {
                    return $hidden_array;
               }
               return $list;
          }
          return null;
     }

     function findQueryFields($standardNames,
                              $showHidden = false) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Searchable',
                                           'METADATA-TABLE');
               $list = null;
               $hidden_array = array();
               foreach ($names as $key => $value) {
                    if ($this->metadataBoolean($value)) {
                         $list[] = $key;
                    } else {
                         $hidden_array[$key] = true;
                    }
               }
               if ($showHidden) {
                    return $hidden_array;
               }
               return $list;
          }
          return null;
     }

     function findUniqueFields($standardNames) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Unique',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) {
                    if ($this->metadataBoolean($value)) {
                         $list[] = $key;
                    }
               }
               return $list;
          }
          return null;
     }

     function findSearchableFields($standardNames) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Searchable',
                                           'METADATA-TABLE');
               $list = null;
               $hidden_array = array();
               foreach ($names as $key => $value) {
                    if ($this->metadataBoolean($value)) {
                         $list[$key] = true;
                    }
               }
               return $list;
          }
          return null;
     }

     function findCurrencyFields($standardNames) {
          if ($this->exists()) {
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Interpretation',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) {
                    if ($value == 'Currency') {
                         $list[$key] = true;
                    }
               }
               return $list;
          }
          return null;
     }

     function findUnitsFields($unitName, $standardNames)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Units',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) 
               {
                    if (strtoupper($value) == $unitName)
                    {
                         $list[$key] = true;
                    }
               }
               return $list;
          }
          return null;
     }

     function findDateFields($standardNames)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'DataType',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) 
               {
                    if ($value != null)
                    {
                         $aType = strToUpper($value);
                         if ($aType == 'DATE' || $aType == 'DATETIME')
                         {
                              $list[] = $key;
                         }
                    }
               }
               return $list;
          }
          return null;
     }

     function findDataTypes($standardNames) {
          if ($this->exists()) {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'DataType',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) {
                    if ($value != null) {
                         $list[$key] = $value;
                    }
               }
               return $list;
          }
          return null;
     }

     function findRequiredFields($standardNames) {
          if ($this->exists()) {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Required',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) {
                    if ($value != null) {
                         $list[$key] = $value;
                    }
               }
               return $list;
          }
          return null;
     }

     function findInterpretations($standardNames) {
          if ($this->exists()) {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames) {
                    $basis = 'StandardName';
               } else {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Interpretation',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) {
                    if ($value != null) {
                         $list[$key] = $value;
                    }
               }
               return $list;
          }
          return null;
     }

     function findMaximumLengths($standardNames)
     {
          if ($this->exists())
          {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'MaximumLength',
                                           'METADATA-TABLE');
               $list = null;
               foreach ($names as $key => $value) 
               {
                    if ($value != null)
                    {
                         $list[$key] = $value;
                    }
               }
               return $list;
          }
          return null;
     }

     function findDataLookupTypes($standardNames,
                                  $asAssociation = true)
     {
          if ($this->exists())
          {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'SystemName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'Interpretation',
                                           'METADATA-TABLE');

//
// create a return array containing only lookup types 
//
               $list = null;
               foreach ($names as $key => $value) 
               {
                    if ($value == 'Lookup' || 
                        $value == 'LookupMulti' ||
                        $value == 'LookupBitmask')
                    {
                         if ($asAssociation)
                         {
                              $list[$key] = $value;
                         }
                         else
                         {
                              $list[$key] = true;
                         }
                    }
               }
               return $list;
          }
          return null;
     }

     function findDataLookupNames()
     {
          if ($this->exists())
          {
               $this->read();
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           'LookupName',
                                           'Interpretation',
                                           'METADATA-TABLE');

//
// create a return array containing only lookup types 
//
               $list = null;
               foreach ($names as $key => $value) 
               {
                    if ($value == 'Lookup' || 
                        $value == 'LookupMulti' ||
                        $value == 'LookupBitmask')
                    {
                         $list[] = $key;
                    }
               }
               return $list;
          }
          return null;
     }

}

class ClassMetadata 
     extends Metadata 
{
     function ClassMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'Class');
          $this->createSubdirectory('Table');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function findDescription($className)
     {
          return findField($className, 'Description');
     }

     function findNames($standardNames,
                        $asAssociation = false)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'ClassName';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'VisibleName',
                                           'METADATA-CLASS');
               if ($asAssociation)
               {
                    return $names;
               }
               if($names != null)
               {
                    $list = null;
                    foreach ($names as $key => $value) 
                    {
                         $list[] = $key;
                    }
                    return $list;
               }
          }
          return null;
     }

     function findField($className,
                        $fieldName)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           'ClassName',
                                           $fieldName,
                                           'METADATA-CLASS');
               foreach ($names as $key => $value) 
               {
                    if ($key == $className)
                    {
                         return $value;
                    }
               }
          }
          return null;
     }

     function getClassTranslations()
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               return $nameParser->parse($this->contentsAsString(),
                                         'StandardName',
                                         'ClassName',
                                         'METADATA-CLASS');
          }
          return null;
     }

     function getStandardClass($class,
                               $standardNames)
     {
          if ($standardNames)
          {
               return $class;
          }

          $classTable = $this->getClassTranslations();
          foreach ($classTable as $key => $value) 
          {
               if ($class == $value)
               {
                    return $key;
               }
          }

          return $class;
     }

     function getSystemClass($class,
                             $standardNames)
     {
          if (!$standardNames)
          {
               return $class;
          }

          $classTable = $this->getClassTranslations();
          return $classTable[$class];
     }
}

class ObjectMetadata 
     extends Metadata 
{
     function ObjectMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'Object');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function findNames()
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           'ObjectType',
                                           'MIMEType',
                                           'METADATA-OBJECT');
               $list = null;
               foreach ($names as $key => $value) 
               {
                    $list[] = $key;
               }
               return $list;
          }
          return null;
     }
}

class SearchHelpMetadata 
     extends Metadata 
{
     function SearchHelpMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'SearchHelp');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function findNames()
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               $names = $nameParser->parse($this->contentsAsString(),
                                           'SearchHelpID',
                                           'Value',
                                           'METADATA-SEARCH_HELP');
               $list = null;
               foreach ($names as $key => $value) 
               {
                    $list[] = $key;
               }
               return $list;
          }
          return null;
     }
}

class LookupMetadata 
     extends Metadata 
{
     function LookupMetadata($aName, $aRelationship)
     {
          parent::Metadata($aName, $aRelationship, 'Lookup');
          $this->createSubdirectory('LookupType');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }
}

class ResourceMetadata 
     extends Metadata 
{
     function ResourceMetadata($aName)
     {
          parent::Metadata($aName, 'Resources');
     }

     function exists()
     {
          return file_exists($this->fullPath());
     }

     function findNames($standardNames,
                        $asAssociation = false)
     {
          if ($this->exists())
          {
               $this->read();
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'ResourceID';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'VisibleName',
                                           'METADATA-RESOURCE');
               if ($asAssociation)
               {
                    return $names;
               }
               $list = null;
               foreach ($names as $key => $value) 
               {
                    $list[] = $key;
               }
               return $list;
          }
          return null;
     }

     function keyField($resource,
                       $standardNames)
     {
          if ($this->exists())
          {
               $nameParser = new TranslationParser();
               if ($standardNames)
               {
                    $basis = 'StandardName';
               }
               else
               {
                    $basis = 'ResourceID';
               }
               $names = $nameParser->parse($this->contentsAsString(),
                                           $basis,
                                           'KeyField',
                                           'METADATA-RESOURCE');
               foreach ($names as $key => $value) 
               {
                    if ($key == $resource)
                    {
                         return $value;
                    }
               }
          }
          return null;
     }

}

?>
