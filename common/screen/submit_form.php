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

if (!array_key_exists('CANCEL', $vars))
{

     if ($vars['FIELD'])
     {

//
// construct value as a text array 
//
          $LOCATION = new Location();
          $CONFIGURATION = $LOCATION->getConfiguration();
          $value = '$' . 
                   $CONFIGURATION->ensureLegalVariablename('$' . $vars['FIELD']) . 
                   '_FORM=array(';
          $text = $vars['TEXT'];
          $dmql = $vars['DMQL'];
          $empty = true;
          foreach ($text as $num => $val) 
          {
               if (strlen($val) > 0)
               {
                    $empty = false;
                    $value .= '"' .  $val . 
                              '"=>"' .
                              $dmql[$num] .  '",';
               }
          }
          $value = substr($value, 0, strlen($value) - 1) . ');';
          if ($empty)
          {
               $value = null;
          }

          if ($value != null)
          {
//
// determine configuration type 
//
               $LOCATION = null;
               if (array_key_exists('ELEMENT-TYPE', $vars))
               {
                    $LOCATION = determine_type($vars['ELEMENT-TYPE']);
               }
               if ($LOCATION == null)
               {
                    $LOCATION = new Location();
               }

//
// place the array in the config file
//
               $CONFIGURATION = $LOCATION->getConfiguration($vars['ELEMENT']);
               $CONFIGURATION->setVariable('$' . $vars['FIELD'] . '_FORM', $value);
               $LOCATION->saveConfiguration($CONFIGURATION, $vars['ELEMENT']);
          }
     }

}

$url = $SCREEN[$vars['PASSTHRU']] . 
       '?ELEMENT=' . $vars['ELEMENT'];
//
// redirect
//
locate_next_screen($url);

//

?>
