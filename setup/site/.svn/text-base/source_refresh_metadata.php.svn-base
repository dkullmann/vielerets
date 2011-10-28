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

//
// create a default return location
//
if (array_key_exists('CANCEL', $vars))
{
     $url = $SCREEN[$vars['PASSTHRU-LOCATION']] . 
            '?ELEMENT=' . $vars['ELEMENT'];
     locate_next_screen($url);
}

$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// read file
//
$SOURCE = new Source();
$CONFIGURATION = $SOURCE->getConfiguration($vars['ELEMENT']);

//
// refresh metadata
//
$EXCHANGE = new Exchange($CONFIGURATION->getName());
if ($EXCHANGE->loginDirect($CONFIGURATION->getValue('RETS_SERVER_ACCOUNT'),
                           $CONFIGURATION->getValue('RETS_SERVER_PASSWORD'),
                           $CONFIGURATION->getValue('RETS_SERVER_URL'),
                           $CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION'),
                           $CONFIGURATION->getValue('APPLICATION'),
                           $CONFIGURATION->getValue('VERSION'),
                           $CONFIGURATION->getValue('RETS_CLIENT_PASSWORD'),
                           $CONFIGURATION->getBooleanValue('POST_REQUESTS')))
{
     $EXCHANGE->refreshMetadata($CONFIGURATION->getName(),
                                $CONFIGURATION->getValue('SELECTION_RESOURCE'),
                                $CONFIGURATION->getValue('SELECTION_CLASS'),
                                $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES'));
     $EXCHANGE->logoutDirect();
}

//
// using view.php 
//
$FORMATTER = new TableFormatter();

//
// using model.php
//
$url = $CONFIGURATION->getValue('RETS_SERVER_URL');

//
// check RETS capabilities
//
$err_number = 0;
$capabilities_text = null;
checkResult($CONFIGURATION,
            $err_number,
            $capabilities_text);
//echo '<XMP>NUM $err_number TEXT $err_text</XMP>';

$STYLIST = new Stylist();
$items[] = $STYLIST->formatBoldText($url, 'green');
if ($err_number == 0)
{
     $err_text = 'Success<br/>' . $capabilities_text;
     $items[] = $STYLIST->formatText($err_text, 'green');
}
else
{
     $err_text = 'Failure (detail below)<br/>' . $capabilities_text;
     $items[] = $STYLIST->formatText($err_text, 'red');
}

$items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
$items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'SOURCE');
$items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', 'SOURCE_MENU');
$items[] = $FORMATTER->formatHiddenField('PASSTHRU', 'SOURCE_AUTO_DETECT');

$message = 'Results of refreshing metadata from [' . $vars['ELEMENT'] . ']';

$FORMATTER->printForm($items, 
                      $SCREEN['SOURCE_MENU'], 
                      $message,
                      'Refresh Metadata');

$FORMATTER->finish();

function checkResult($CONFIGURATION,
                     &$err_number,
                     &$err_text)
{

// all is well
//     
     $err_number = 0;

//---------------
     $cName = $CONFIGURATION->getName();
     $resource = $CONFIGURATION->getValue('SELECTION_RESOURCE');
     $class = $CONFIGURATION->getValue('SELECTION_CLASS');
     $METADATA_CLASS = new ClassMetadata($cName, $resource);
     $standardNames = $CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
     $systemClass = $METADATA_CLASS->getSystemClass($class,
                                                    $standardNames);
//print('resource ' . $resource . ' class ' . $class);

     $METADATA = new TableMetadata($cName, $systemClass);
     $field_array = $METADATA->findNames($standardNames);
//print_r($field_array);

//
// check unique key 
//
     $err_text = '<br/>Unique Key:';
     $problem = false;
     $value = $CONFIGURATION->getValue('UNIQUE_KEY');
     $found = false;
     if ($field_array != null)
     {
          foreach ($field_array as $l_key => $l_value) 
          {
               if ($value == $l_value)
               {
                    $found = true;
               }
          }
     }
     if (!$found)
     {
          $err_text .= '<br/>&nbsp;&nbsp;Item [' . $value . '] no longer available';
          $err_number = 901;
     }
     else
     {
          $err_text .= ' Still Available';
     }

//
// check summary fields
//
     $err_text .= '<br/>Summary View:';
     $problem = false;
     $metadata = explode(',', $CONFIGURATION->getValue('SUMMARY_ITEMS'));
     foreach ($metadata as $key => $value) 
     {
          $found = false;
          if ($field_array != null)
          {
               foreach ($field_array as $l_key => $l_value) 
               {
                    if ($value == $l_value)
                    {
                         $found = true;
                    }
               }
          }
          if (!$found)
          {
               $problem = true;
               $err_text .= '<br/>&nbsp;&nbsp;Item [' . $value . '] no longer available';
          }
     }
     if ($problem)
     {
          $err_number = 901;
     }
     else
     {
          $err_text .= ' Still Available';
     }

//
// check ownership 
//
     $err_text .= '<br/>Ownership Variable:';
     $problem = false;
     $value = $CONFIGURATION->getValue('OWNERSHIP_VARIABLE');
     $found = false;
     if ($field_array != null)
     {
          foreach ($field_array as $l_key => $l_value) 
          {
               if ($value == $l_value)
               {
                    $found = true;
               }
          }
     }
     if (!$found)
     {
          $err_text .= '<br/>&nbsp;&nbsp;Item [' . $value . '] no longer available';
          $err_text .= '<br/>&nbsp;&nbsp;Owned Listings is affected';
          $err_number = 902;
     }
     else
     {
          $err_text .= ' Still Available';
     }

//
// check date  
//
     $err_text .= '<br/>Date Variables:';
     $problem = false;
     $metadata = explode(',', $CONFIGURATION->getValue('DATE_VARIABLE'));
     foreach ($metadata as $key => $value) 
     {
          $found = false;
          if ($field_array != null)
          {
               foreach ($field_array as $l_key => $l_value) 
               {
                    if ($value == $l_value)
                    {
                         $found = true;
                    }
               }
          }
          if (!$found)
          {
               $problem = true;
               $err_text .= '<br/>&nbsp;&nbsp;Item [' . $value . '] no longer available';
          }
     }
     if ($problem)
     {
          $err_text .= '<br/>&nbsp;&nbsp;Hot Sheet is affected';
          $err_number = 903;
     }
     else
     {
          $err_text .= ' Still Available';
     }

//
// check query form 
//
     $err_text .= '<br/>Query Form:';
     $problem = false;
     $metadata = explode(',', $CONFIGURATION->getValue('QUERY_ITEMS'));
     foreach ($metadata as $key => $value) 
     {
          $found = false;
          if ($field_array != null)
          {
               foreach ($field_array as $l_key => $l_value) 
               {
                    if ($value == $l_value)
                    {
                         $found = true;
                    }
               }
          }
          if (!$found)
          {
               $problem = true;
               $err_text .= '<br/>&nbsp;&nbsp;Item [' . $value . '] no longer available';
          }
     }
     if ($problem)
     {
          $err_number = 904;
          $err_text .= '<br/>&nbsp;&nbsp;Open Search is affected';
     }
     else
     {
          $err_text .= ' Still Available';
     }

//
// finish up
//
     if ($err_number != 0)
     {
          $err_text .= '<br/><br/>You can repair these problems from Source->Text Query';
     }

}

$HTML->finish();

//------------

?>
