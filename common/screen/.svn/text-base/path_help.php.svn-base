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
// if the QUIT button is hit
//
if (array_key_exists('CANCEL', $vars))
{
     $url = $SCREEN[$vars['PASSTHRU-LOCATION']] . 
            '?ELEMENT=' . $vars['ELEMENT'] . 
            '&MODE=' . $vars['MODE'] .
            '&MESSAGE=' . urlencode('Value not changed');
     locate_next_screen($url);
}

//
// conversion
//
$remote_context = $vars['CONTEXT'];    
$constants = get_defined_constants();
$local_context = $constants[$vars['CONTEXT-NAME']];    
if (SCREEN_DEBUG_MODE)
{
echo '<XMP>RC $remote_context LC $local_context</XMP>';
}

//
// return to the calling program if this is a submission
//
$error = null;
if (array_key_exists('SUBMIT', $vars))
{
     $path = convertToPackage($vars['CURRENT'],
                              $remote_context);
if (SCREEN_DEBUG_MODE)
{
echo '<XMP>P $path</XMP>';
}
     $args = '?ELEMENT=' . $vars['ELEMENT'] . 
             '&PATH_HELP=true' .
             '&FIELD=' . $vars['FIELD'] .
             '&LOCATION=' . $vars['PASSTHRU'] .
             '&VALUE=' . urlencode($path);
     if (array_key_exists('MESSAGE', $vars))
     {
          $args .= '&MESSAGE=' . urlencode($vars['MESSAGE']);
     }
     if (array_key_exists('MODE', $vars))
     {
          $args .= '&MODE=' . $vars['MODE'];
     }
     $url = $SCREEN[$vars['PASSTHRU-LOCATION']] . 
            $args;
     locate_next_screen($url);
}

//
// always convert to remote context, then local context 
//
if (array_key_exists('CURRENT', $vars))
{
     $remotePath = remoteFromCurrent($vars['CURRENT'],
                                     $remote_context);
}
else
{
     $remotePath = remoteFromValue($vars['VALUE'],
                                   $remote_context);
}
$localPath = localFromRemote($remotePath,
                             $remote_context,
                             $local_context);
if (SCREEN_DEBUG_MODE)
{
echo "<XMP>RP $remotePath LP $localPath</XMP>";
}

//
// determine the name of the top level to use for display
//
$path_array = null;
if ($remotePath == '')
{
     $currentPathDisplay = '{ROOT}'; 
}
else
{
//     $installationDirectory = '[' . PROJECT_NAME . ' Installation]';
     $installationDirectory = '{INSTALLATION}';
     if ($remotePath == $remote_context)
     {
          $currentPathDisplay = $installationDirectory;
     }
     else
     {
          $pos = strpos($remotePath, $remote_context);
          if ($pos === false)
          {
               $currentPathDisplay = $remotePath;
          }
          else
          {
               if ($pos == 0)
               {
                    $currentPathDisplay = $installationDirectory . '/' .
                                          substr($remotePath, 3, strlen($remotePath));
               }
//               else
//               {
//                    $currentPathDisplay = substr($currentPath, 0, $pos - 1);
//               }
          }
          $path_array[] = '..';
     }
}

//
// if the remotePath is null, you are at root 
//
if ($remotePath == '')
{
     $localPath = '/';
}

//
// read list of directories
//
$dh = opendir($localPath);
while ($file = readdir($dh)) 
{
     if ($file != '.' && $file != '..') 
     {
          $fullpath = $localPath . '/' . $file;
          if (is_dir($fullpath)) 
          {
               $fileName = basename($fullpath);
               if ($fileName != '.svn')
               {
                    $path_array[] = $fileName;
               }
          }
     }
} 
closedir($dh);

//
// display the list
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Administration Interface');

//
// using view.php 
//
$FORMATTER = new TableFormatter();

if ($error != null)
{
     $items[] = $FORMATTER->renderError($error);
}

$items[] = $FORMATTER->formatBoldText($currentPathDisplay);
$items[] = $FORMATTER->formatHiddenField('CURRENT', $remotePath);

$message = null;
if (array_key_exists('MESSAGE', $vars))
{
     $message = urlencode($vars['MESSAGE']);
}

$base_url = $SCREEN['PATH_HELP'] . 
            '?ELEMENT=' . $vars['ELEMENT'] . 
            '&FIELD=' . $vars['FIELD'] .
            '&PASSTHRU-LOCATION=' . $vars['PASSTHRU-LOCATION'] .
            '&PASSTHRU=' . $vars['PASSTHRU'] .
            '&MESSAGE=' . $message .
            '&CONTEXT=' . $vars['CONTEXT'] .
            '&CONTEXT-NAME=' . $vars['CONTEXT-NAME'];
if (array_key_exists('MODE', $vars))
{
     $base_url .= '&MODE=' . $vars['MODE'];
}

foreach ($path_array as $key => $val) 
{
     $url = $base_url .
            '&CURRENT=' .  $remotePath . '/' . $val;
     $items[] = $FORMATTER->createLink($url,
                                       $FORMATTER->formatLink($val));
}

$items[] = $FORMATTER->formatHiddenField('FIELD', $vars['FIELD']);
$items[] = $FORMATTER->formatHiddenField('ELEMENT', $vars['ELEMENT']);
$items[] = $FORMATTER->formatHiddenField('PASSTHRU-LOCATION', $vars['PASSTHRU-LOCATION']);
$items[] = $FORMATTER->formatHiddenField('PASSTHRU', $vars['PASSTHRU']);
$items[] = $FORMATTER->formatHiddenField('CONTEXT', $vars['CONTEXT']);
$items[] = $FORMATTER->formatHiddenField('CONTEXT-NAME', $vars['CONTEXT-NAME']);
if (array_key_exists('MODE', $vars))
{
     $items[] = $FORMATTER->formatHiddenField('MODE', $vars['MODE']);
}

$FORMATTER->printForm($items, 
                      $SCREEN['PATH_HELP'], 
                      'Select a Directory',
                      urldecode($message));

$FORMATTER->finish();

$HTML->finish();

function convertToPackage($path,
                          $context)
{
     if ($path == $context)
     {
          return '.';
     }

     $value = $path;
     $pos = strpos($path, $context);
     if ($pos === false)
     {
     }
     else
     {
          if ($pos == 0)
          {
               $value = './' . 
                        substr($path, 
                               $pos + strlen($context) + 1, 
                               strlen($path));
          }
     }

     return $value;
}

function remoteFromCurrent($path,
                           $context)
{
     $newPath = $path;
     $pos = strrpos($path, $context);
     $context_len = strlen($context);
     if (strlen($path) == $pos + $context_len)
     {
          $newPath = substr($path, 
                            0, 
                            $pos - $context_len + 1);
          $newPath = substr($newPath, 
                            0, 
                            strrpos($newPath, '/'));
     }

     return $newPath;
}

function remoteFromValue($path,
                         $context)
{
     $newPath = $path;
     $pos = strpos($path, './');
     if ($pos === false)
     {
          $pos = strpos($path, '/');
          if ($pos === false)
          {
               $newPath = $context;
          }
          else
          {
               if ($pos != 0)
               {
                    $newPath = $context;
               }
          }
          
     }
     else
     {
          if ($pos == 0)
          {
               $newPath = $context . '/' . 
                          substr($path, 2, strlen($path));
          }
     }

     return $newPath;
}

function localFromRemote($path,
                         $remote_context,
                         $local_context)
{
     $newPath = $path;
     $pos = strpos($path, $remote_context);
     if ($pos === false)
     {
//
// should be absolute not relative
//
     }
     else
     {
          if ($pos == 0)
          {
               $newPath = $local_context .  
                          substr($path, 
                                 $pos + strlen($remote_context), 
                                 strlen($path));
          }
     }

     return $newPath;
}

//
//------------

?>
