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
// AJAX configuration
//
define('AJAX_CLIENT', AJAX_DIRECTORY . '/client.js');
define('AJAX_SERVER', AJAX_DIRECTORY . '/server.php');
define('AJAX_DISPLAY', 'viele_screen');
define('AJAX_STATUS_FLOAT', 'viele_float');
define('AJAX_STATUS_MESSAGE', "Wait a moment ...<br/>Talkin' to the RETS Server");
define('AJAX_STATUS_IMAGE', RESOURCE_DIRECTORY . '/' . 'status_message.png');
define('AJAX_USE_STATUS_IMAGE', true);
define('AJAX_NAVBAR', 'viele_navbar');
define('AJAX_LAST', 'viele_last');
define('AJAX_BYPASS', 'viele_bypass');
define('AJAX_PROCESSING_MODE', 'viele_mode');
define('AJAX_MESSAGE_STYLE', 'POST');
define('AJAX_MONITORING', false);

function create_ajax_target($initialPassEnv = false) {
        $mode = null;
	if ($initialPassEnv) {
        	$mode = '<input type="hidden" name="' . AJAX_PROCESSING_MODE . '" value=""/>';
        }

//
// create HTML for the  the blanket and float elements
//
	if (AJAX_USE_STATUS_IMAGE) {
		$floats = '<div id="blanket"></div>' .
			'<div id="' . AJAX_STATUS_FLOAT . 
			'"><img src="' . AJAX_STATUS_IMAGE .
			'""/></div>'; 
//			'" width="410" height="180"/></div>'; 
//			'" style="width:410px;height:180px;"/></div>'; 
	} else {
		$floats = '<div id="blanket"></div>' .
			'<div id="' . AJAX_STATUS_FLOAT . '">' . AJAX_STATUS_MESSAGE . '</div>'; 
	}
	return '<input type="hidden" name="' . AJAX_LAST . '" value=""/>' .
		$mode .
		$floats .
		'<div id="' . AJAX_DISPLAY . '">&nbsp;</div>';
}

function create_ajax_script($trackStatus) {
	$useMonitor = '';
//        $useMonitor = '<script src="http://localhost/soa?file=api&v=usability_1&key=9d00cc80fbfc365e3d360c15c9962ffe"" type="text/javascript"></script>' .
//                       '<script type="text/javascript">' .
//                       'var useMonitor = new UseMonitor();' .
//                       '</script>';

//
// prepare to track status; create CSS for the blanket and float elements
//
	$style = '<style type="text/css">';
	if ($trackStatus) { 
		if (AJAX_USE_STATUS_IMAGE) {
		$style .= ' #blanket{' .
			'background-color:#111;' .
			'position:absolute;' .
			'z-index: 9001;' .
			'top:0px;' .
			'left:0px;' .
			'width:100%;' .
			'float:left;' .
			'display:none;' .
			'}' .
			' #' . AJAX_STATUS_FLOAT . '{' .
			'position:absolute;' .
			'z-index: 9002;' .
			'width:100%;' .
			'float:left;' .
			'display:none;' .
			'}';
		} else {
		$style .= ' #blanket{' .
			'background-color:#111;' .
			'position:absolute;' .
			'z-index: 9001;' .
			'top:0px;' .
			'left:0px;' .
			'width:100%;' .
			'float:left;' .
			'display:none;' .
			'}' .
			' #' . AJAX_STATUS_FLOAT . '{' .
			'position:absolute;' .
			'border-width:5px;' .
			'border-style:ridge;' .
			'text-align:center;' .
			'vertical-align:middle;' .
			'background-color:#eeeeee;' .
			'font-family:Verdana, Sans;' .
			'font-size:14pt;' .
			'font-weight:bold;' .
			'color:red;' .
			'z-index: 9002;' .
			'width:100%;' .
			'float:left;' .
			'display:none;' .
			'}';
		}
		$status = 'pageHandler.setTrackStatus(true,"' . AJAX_STATUS_FLOAT . '");' . "\r\n";
	} else {
		$style .= ' #blanket{' .
			'width:0;' .
			'height:0;' .
			'display:none;' .
			'}' .
			' #' . AJAX_STATUS_FLOAT . '{' .
			'width:0;' .
			'height:0;' .
			'display:none;' .
			'}';
		$status = null;
	}
	$style .= '</style>';

	return $style .
		$useMonitor .
		'<script src="' . AJAX_CLIENT . '" type="text/javascript"></script>' . "\r\n" .
		'<script type="text/javascript">' . "\r\n" .
		'var statusImage = "' . AJAX_STATUS_IMAGE . '";' . "\r\n" .
		'var operatingHandler = "' . AJAX_SERVER . '";' . "\r\n" .
                'var pageHandler = new PageHandler("' . AJAX_DISPLAY . '","' . AJAX_NAVBAR . '","' . AJAX_STATUS_FLOAT . '");' . "\r\n" .
		$status .
		'pageHandler.setMonitoring(' . AJAX_MONITORING . ');' . "\r\n" .
		'var monitor = new Monitor(pageHandler,"'. AJAX_LAST . '","' . AJAX_PROCESSING_MODE . '","' . AJAX_BYPASS . '");' . "\r\n" .
 
		'monitor.setMessageStyle("' . AJAX_MESSAGE_STYLE . '");' . "\r\n" .
		'</script>' . "\r\n";
}

?>
