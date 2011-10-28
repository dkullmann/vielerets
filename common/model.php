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
// dependencies 
//
include_once(COMMON_DIRECTORY . '/externals.php');

//
// RETS Engine 
//
include_once(RETS_ENGINE);

//
// ADODB database connectivity
//
global $ADODB_CACHE_DIR;
$ADODB_CACHE_DIR = MAIN_DIRECTORY . '/cache';
include_once(ADODB_ABSTRACTION);


//
// configuration includes
//
include_once(COMMON_DIRECTORY . '/site/component.php');

//
// package includes
//
include_once(COMMON_DIRECTORY . '/extract.php');
include_once(COMMON_DIRECTORY . '/target.php');
include_once(COMMON_DIRECTORY . '/source.php');

//
// About Settings
//
define('PROJECT_LOGO', 'viele_logo.gif');
define('PROJECT_NAME', 'VieleRETS');
define('PROJECT_VERSION', '1.1.8');
define('PROJECT_STATUS', 'Production');
define('PROJECT_COPYRIGHT', 'Copyright&copy; 2005-2010 National Association of REALTORS&reg;');

//
// state settings
//
define('NO_SERVICE_MESSAGE','This service is temporarily not available');
define('NO_VALUE_INDICATOR', '--NOT_USED--');
define('MAX_QUERY_FIELDS', 15);
define('FAST_MAP_THRESHOLD', 5);

function getMapFromArgs($name,
                        $vars) {
	$newValue = Array();
	foreach ($vars as $key => $value) {
		if (strpos($key,'SOA_MAP__') === false) {
		} else {
			$tempName = substr($key,9,strlen($key));
			$mapVariable = substr($tempName, strpos($tempName,'__') + 2, strlen($tempName));
			$mapValue = substr($tempName, 0, strpos($tempName, $mapVariable) - 2);
			if ($mapVariable == $name) {
				$newValue[$mapValue]= $value;
			}
		}
	}

	return $newValue;
}

function getStringFromArgs($name,
                           $vars) {
	$newValue = '';
	foreach ($vars as $key => $value) {
		if (strpos($key,'SOA_ARRAY__') === false) {
			if (strpos($key,'SOA_MAP__') === false) {
			} else {
				$tempName = substr($key,9,strlen($key));
				$tempName = substr($tempName, strpos($tempName,'__') + 2, strlen($tempName));
				if ($tempName == $name) {
					$newValue .= $value . ',';
				}
			}
		} else {
			$tempName = substr($key,11,strlen($key));
			$tempName = substr($tempName, strpos($tempName,'__') + 2, strlen($tempName));
			if ($tempName == $name) {
				$newValue .= $value . ',';
			}
		}
	}

	if (strlen($newValue) == 0) {
		return '';
	}
	return substr($newValue,0,strlen($newValue)-1);
}

function getBooleanStringFromArg($aValue) {
     if ($aValue == '1') {
          return 'true';
     } else {
          if ($aValue == '0') {
               return 'false';
          }
     }

     return $aValue;
}

function setConfigurationFromArgs($CONFIGURATION,
                                  $name,
                                  $vars) {
	if (array_key_exists($name, $vars)) {
		$CONFIGURATION->setValue($name, $vars[$name]);
	} else {
		$CONFIGURATION->setValue($name, getStringFromArgs($name, $vars));
	}
}

?>
