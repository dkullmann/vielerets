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

 
include_once(COMMON_DIRECTORY . "/site/source.php");

class VieleSource 
     extends Source 
{

     function VieleSource($directory = null)
     {
          parent::Source($directory);
     }

     function getDependentExtracts($element)
     {
//
// develop list of extracts
//
          $EXTRACT = new Extract();
          $result = $EXTRACT->getExisting();

//
// check if this source is in use
//
          $universe = "";
          if ($result != null)
          {
               foreach ($result as $config => $target)
               {
                    if (file_exists($target))
                    {
                         $CONFIGURATION = $EXTRACT->getConfiguration($target);
                         $temp = $CONFIGURATION->getValue("SOURCE");
                         $temp = basename($temp);
                         if ($temp == $element)
                         {
                              $universe .= "[" . $config . "],";
                         }
                    }
               }
               $universe = substr($universe, 0, strlen($universe) - 1);
          }

          return $universe;
     }

     function getDependentExtractsList($element)
     {
//
// develop list of extracts
//
          $EXTRACT = new Extract();
          $result = $EXTRACT->getExisting();

//
// check if this source is in use
//
          $universe = null;
          if ($result != null)
          {
               foreach ($result as $config => $target)
               {
                    if (file_exists($target))
                    {
                         $CONFIGURATION = $EXTRACT->getConfiguration($target);
                         $temp = $CONFIGURATION->getValue("SOURCE");
                         $temp = basename($temp);
                         if ($temp == $element)
                         {
                              $universe[] = $config;
                         }
                    }
               }
          }

          return $universe;
     }
}

?>
