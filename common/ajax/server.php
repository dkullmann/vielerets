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
// Installation Settings (from ./controller.php) 
//
define('MAIN_DIRECTORY', '../..');
define('COMMON_DIRECTORY', MAIN_DIRECTORY . '/common');

//
// includes - controller contains the AJAX_DIRECTORY definition 
//
include_once(COMMON_DIRECTORY . '/controller.php');
include_once(COMMON_DIRECTORY . '/model.php');
include_once(AJAX_DIRECTORY . '/view.php');
include_once(COMMON_DIRECTORY . '/display.php');
include_once(AJAX_DIRECTORY . '/ajaxFormatter.php');

//print_r($_REQUEST);
//print_r($vars);
//print_r($_SERVER);
//print_r($_COOKIE);

$remote_address = 'localhost';
if (array_key_exists('REMOTE_ADDR',$_SERVER)) {
	$remote_address = $_SERVER['REMOTE_ADDR'];
}
//$remote_address = '192.168.1.1';

if (array_key_exists('REQUEST_TIME',$_SERVER)) {
	$local_time = $_SERVER['REQUEST_TIME'];
} else {
	$local_time = time();
}

$gesture = new Gesture($vars['viele_version'],
			$remote_address,
			$vars['viele_type'],
			$vars['viele_widget'],
			$vars['viele_name'],
			$vars['viele_value'],
			$vars['viele_page'],
			$vars['viele_nextFocus'],
			$vars['viele_time'],
			$local_time);

//
// handler
//
$HANDLER = null;
include_once(AJAX_DIRECTORY . '/handlers.php');

$handler = new PageHandler($gesture);
if (array_key_exists('viele_verbose',$vars)) {
	$handler->setVerbose(true);
}
if (array_key_exists('viele_env',$vars)) {
	$handler->setEnv($vars['viele_env']);
}
$initialPassEnv = false;
if (array_key_exists('viele_mode',$vars)) {
	$initialPassEnv = true;
}
$response = $handler->process($initialPassEnv);
if (strlen($response) > 0) {
	echo '<RESPONSE>' . $response . '</RESPONSE>';
}

//------------

class PageHandler {
	var $gesture;
	var $verbose = false;
	var $env = null;

	function PageHandler($aGesture) {
		$this->gesture = $aGesture;
	}

	function setVerbose($value) {
		$this->verbose = $value;
	}

	function setEnv($value) {
		$this->env = $value;
	}

	function process($initialPassEnv = false) {
		$screenName = $this->translate($this->gesture->getPage());
		$handler = $GLOBALS['HANDLER'];
		if (!array_key_exists($screenName, $handler)) {
			return null;
		}

//
// monitor response
//
		if ($this->verbose = 'true') {
			if ($this->gesture->getWidget() == 'PAGE') {
				$monitorResponse = '<MONITOR><![CDATA[ ' .
							$this->gesture->getGType() .  ' on ' .  $screenName .
							']]></MONITOR>';
			} else {
				$monitorResponse = '<MONITOR><![CDATA[ ' .
							$this->gesture->getGType() .  ' ' .  $this->gesture->getName() .  ' to ' .  $this->gesture->getValue() .
							']]></MONITOR>';
			}
		}

//
// call specific script for the html response
//
		include_once($handler[$screenName]);
		$htmlResponse = ajax_process($this->gesture, $this->env, $initialPassEnv);
//                $htmlResponse = '<HTML><![CDATA[hello]]></HTML>';

		return $htmlResponse . $monitorResponse;
	}

	function translate($path) {
		krsort($GLOBALS['SCREEN'], SORT_STRING);
		foreach ($GLOBALS['SCREEN'] as $key => $val) {
			$check = substr($val, strpos($val, MAIN_DIRECTORY) + strlen(MAIN_DIRECTORY), strlen($val));
			if (strpos($path,$check) === false) {
			} else {
				return $key;
			}
		}
		return null;
	}

}

//------------

class Gesture {
	var $version; 
	var $address; 
	var $gtype; 
	var $widget; 
	var $name;
	var $value;
	var $page;
	var $nextFocus;
	var $remote_time;
	var $local_time;

	function Gesture($version,
			$address,
			$type,
			$widget,
			$name,
			$value,
			$page,
			$nextFocus,
			$remote_time,
			$local_time) {
		$this->version = $version;
		$this->address = $address;
		$this->gtype = $type;
		$this->widget = $widget;
		$this->name = $name;
		$this->value = $value;
		$this->page = $page;
		$this->nextFocus = $nextFocus;
		$this->setRemoteTime($remote_time);
		$this->local_time = $local_time;
	}

	function getUser() {
		return $this->user;
	}
 
	function getVersion() {
		return $this->version;
	}
 
	function getAddress() {
		return $this->address;
	}
 
	function getGType() {
		return $this->gtype;
	}
 
	function getWidget() {
		return $this->widget;
	}
 
	function getName() {
		return $this->name;
	}
 
	function getValue() {
		return $this->value;
	}
 
	function getPage() {
		return $this->page;
	}
 
	function getNextFocus() {
		return $this->nextFocus;
	}
 
	function setRemoteTime($remote_time) {
		$f_time = $remote_time;
		$len = strlen($f_time);
		if ($len > 10) {
			$f_time = substr($f_time, 0, 10);
		}
		$this->remote_time = $f_time;
	}
 
	function getRemoteTime() {
		return $this->remote_time;
	}
 
	function getLocalTime() {
		return $this->local_time;
	}
 
}

//------------

function ajax_process($aGesture, 
                      $env,
                      $initialPassEnv = false) {
//     $aGesture->getWidget(); INPUT,PAGE
//     $aGesture->getGType(); CHANGE, FOCUS
//     $aGesture->getPage(); a name 
//     $aGesture->getName(); a widget name 
//     $aGesture->getValue(); a widget value 
if( $aGesture->getName() == 'SUBMIT') {
print($aGesture->getWidget());
}
	switch($aGesture->getWidget()) {
		case 'BUTTON':
			$aName = $aGesture->getName();
			return '<UPDATE>' . ajax_setValue(AJAX_LAST,$aName) . '</UPDATE>' .
				ajax_processValue($aName, $aGesture->getValue(), $env);

		case 'INPUT':
			$aName = $aGesture->getName();
			$aName = $aGesture->getNextFocus();
			return '<UPDATE>' . ajax_setValue(AJAX_LAST,$aName) . '</UPDATE>' .
				ajax_processValue($aName, $aGesture->getValue(), $env);

		case 'SELECT':
			$aName = $aGesture->getName();
			return '<UPDATE>' . ajax_setValue(AJAX_LAST,$aName) . '</UPDATE>' .
				ajax_processValue($aName, $aGesture->getValue(), $env);

		case 'PAGE':
			if ($initialPassEnv) {
	   			return ajax_processValue('JUNK', 'NOTHING', $env);
			} else {
				return '<UPDATE>' . ajax_setValue('MODE','PASSTHRU') . '</UPDATE>' .
					ajax_processValue('MODE', 'PASSTHRU');
			}
	}
	return null;
}

function ajax_setValue($aName, 
                       $aValue) {
	return '<WIDGET name="' . $aName . '" value="' . $aValue . '"/>';
}

function localize($screen) {
	$SCREEN = $GLOBALS['SCREEN'];
	$junk = $SCREEN[$screen];
	return './' . substr($junk,strrpos($junk, '/') + 1, strlen($junk));
}

function determine_type($typeName) {
	if (!$typeName) {
		return null;
	}
	switch($typeName){
		case 'SOURCE':
			return new Source();
		case 'TARGET':
			return new Target();
		case 'EXTRACT':
			return new Extract();
	}
	return null;
}

?>
