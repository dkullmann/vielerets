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
// dependent locations 
//
define('RESOURCE_DIRECTORY', MAIN_DIRECTORY . '/resources');
define('SETUP_DIRECTORY', MAIN_DIRECTORY . '/setup');
define('TEMPLATE_DIRECTORY',SETUP_DIRECTORY . '/templates');
define('SOURCE_DIRECTORY', MAIN_DIRECTORY . '/sources');
define('TARGET_DIRECTORY', MAIN_DIRECTORY . '/targets');
define('EXTRACT_DIRECTORY', MAIN_DIRECTORY . '/extracts');
define('BCF_DIRECTORY', MAIN_DIRECTORY . '/batch_control_files');
define('DEFAULT_CONFIG_NAME','Default');
define('SOURCE_TEMPLATE',TEMPLATE_DIRECTORY . '/default_source');
define('SITE_SETUP_DIRECTORY', SETUP_DIRECTORY . '/site');
define('COMMON_SCREEN_DIRECTORY', COMMON_DIRECTORY . '/screen');
define('DOCUMENTATION_DIRECTORY', MAIN_DIRECTORY . '/doc');
define('LOG_DIRECTORY', MAIN_DIRECTORY . '/logs');
define('AJAX_DIRECTORY', COMMON_DIRECTORY . '/ajax');
define('CRLF', "\r\n");

//
// display properties
//
include_once(COMMON_DIRECTORY . '/display.php');

//
// screen registry
//
$SCREEN = null;
include_once(MAIN_DIRECTORY . '/screen_list.php');
include_once(SETUP_DIRECTORY . '/screen_list.php');
include_once(SITE_SETUP_DIRECTORY . '/screen_list.php');
include_once(COMMON_SCREEN_DIRECTORY . '/screen_list.php');

//
// passed arguments 
//
$vars = $_REQUEST;

?>
