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

class Location {

     var $directory;

     function Location($directory = null) {
          $this->directory = $directory;
     }

     function getDirectory() {
          return $this->directory;
     }

     function getConfiguration($aName = null) {
          if ($aName == null) {
               return new Configuration();
          }

          if ($this->directory == null) {
               return new Configuration($aName);
          }

          return new Configuration($this->toPath($aName));
     }

     function removeConfiguration($aName) {
          unlink($this->toPath($aName));
     }

     function saveConfiguration($CONFIGURATION,
                                $aName) {
          $CONFIGURATION->write($this->toPath($aName));
     }

     function copyConfiguration($oldName,
                                $newName) {
          $CONFIGURATION = $this->getConfiguration($oldName);
          $this->saveConfiguration($CONFIGURATION, $newName);
     }

     function existing() {
          $value = $this->getExisting();
          if ($value < 0) {
               return false;
          }

          return sizeOf($value);
     }

     function getExisting() {
          $result = null;
          $fp = @opendir($this->directory);
          if (!$fp) {
               return -1;
          }
          while (false !== ($file=readdir($fp))) {
               if ($file != '.' && $file != '..') {
                    if ($file != 'CVS' && $file != '.svn') {
                         $result[$file] = $this->directory . '/' . 
                                          $file;
                    }
               }
          }
          closedir($fp);
          if ($result != null) {
               ksort($result);
          }
          return $result;
     }

     function exists($name) {
/*
          $fp = opendir($this->directory);
          while (false !== ($file=readdir($fp)))
          {
               if ($file != '.' && $file != '..')
               {
                    if ($file != 'CVS' && $file != '.svn')
                    {
                         if ($file == $name)
                         {
                              closedir($fp);
                              return true;
                         }
                    }
               }
          }
          closedir($fp);
          return false;
*/
          return file_exists($this->toPath($name));
     }

     function getExistingNames() {
          $result = null;
          $names = $this->getExisting();
          if ($names != null) {
               foreach ($names as $file => $path) {
                    $result[$file] = $file;
               }
          }
          return $result;
     }

     function getExistingForSetup() {
          $result = null;
          $names = $this->getExisting();
          if ($names != null) {
               foreach ($names as $file => $path) {
                    $result[$file] = '../' . $path;
               }
          }
          return $result;
     }

     function toName($file) {
          return basename($file); 
     }

     function toPath($name) {
          return $this->directory . '/' .  basename($name);
     }

     function toConfigPath($name) {
          return $this->toPath($name);
     }

     function toPathForSetup($name) {
          return '../' . $this->toPath($name);
     }

}

?>
