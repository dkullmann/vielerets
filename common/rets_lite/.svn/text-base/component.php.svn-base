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

//
// Set RETS_LITE_DIR to the directory where this file resides...
//
if (!defined('RETS_LITE_DIR')) {
     define('RETS_LITE_DIR',dirname(__FILE__) . '/');
}

//
// Set METADATA_DIRECTORY
//
if (!defined('METADATA_DIRECTORY')) {
//     define('METADATA_DIRECTORY',dirname(__FILE__) . '/../metadata');
     $path = dirname(__FILE__);
     define('METADATA_DIRECTORY', 
            substr($path, 0, strrpos($path, '/')) . '/metadata');
}
$retsLiteInstallError = false;
if (!file_exists(METADATA_DIRECTORY)) {
     if (!@mkdir(METADATA_DIRECTORY, 0777)) {
          $message = 'metadata directory [' . METADATA_DIRECTORY . ']';
          $retsLiteInstallError = $message;
     };
}

//
// Set RESOURCE_DIRECTORY
//
if (!defined('RESOURCE_DIRECTORY')) {
//     define('RESOURCE_DIRECTORY',dirname(__FILE__) . '/../resources');
     $path = dirname(__FILE__);
     define('RESOURCE_DIRECTORY', 
            substr($path, 0, strrpos($path, '/')) . '/resources');
}

//------------
//
// Exchange functions
//
include_once(RETS_LITE_DIR . 'abstractRetsExchange.php');
include_once(RETS_LITE_DIR . 'exchange.php');
include_once(RETS_LITE_DIR . 'member.php');
include_once(RETS_LITE_DIR . 'searchRequest.php');
include_once(RETS_LITE_DIR . 'searchResponse.php');
include_once(RETS_LITE_DIR . 'mediaRequest.php');
include_once(RETS_LITE_DIR . 'multipartResponse.php');
include_once(RETS_LITE_DIR . 'metadata.php');

//------------
//
// Auto-Detection functions
//
include_once(RETS_LITE_DIR . 'autoDetect.php');

//------------
//
// XML functions
//
include_once(RETS_LITE_DIR . 'xml.php');

//---------------------
//
// IO functions
//
include_once(RETS_LITE_DIR . 'common_io.php');
include_once(RETS_LITE_DIR . 'io.php');
include_once(RETS_LITE_DIR . 'net_io.php');

//---------------------
//
// MediaContainer functions
//
include_once(RETS_LITE_DIR . 'media_container.php');

//---------------------

?>
