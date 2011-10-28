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
// controller contains the AJAX_DIRECTORY definition
//

include_once(AJAX_DIRECTORY . '/controller.php');

class HTMLPage {
	var $logo;
	var $favicon;
	var $css;

	function HTMLPage() { 
		$this->favicon = RESOURCE_DIRECTORY . '/favicon.ico';
		$this->logo = RESOURCE_DIRECTORY . '/' . PROJECT_LOGO;
		$this->css = RESOURCE_DIRECTORY . '/' . PROJECT_CSS;
	}

	function beginPage() {
		return '<html>' . CRLF;
	}

	function head($title, $trackStatus) {
		return '<head>' . CRLF .
			'<title>' . $title . '</title>' . CRLF .
                        '<link rel="shortcut icon" type="image/x-icon" href="' . $this->favicon . '">' . CRLF .
			'<link rel="stylesheet" type="text/css" href="' .  $this->css . '">' . CRLF .
			create_ajax_script($trackStatus) .
			'</head>' . CRLF;
	}

	function beginBody() {
		return '<body bgcolor="white" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">' . CRLF;
	}

	function topBanner() {
		return '<!-- logo -->' . CRLF .
			'<table border="0" cellspacing="0" cellpadding="0">' . CRLF .
			'<tr align="left">' . CRLF .
			'<td><img src="' . $this->logo . '"></td>' . CRLF .
			'</tr>' . CRLF .
			'</table>' . CRLF .
			'<!-- logo -->' . CRLF;
	}

	function endBody() {
		return '</body>' . CRLF;
	}

	function endPage() {
		return '</html>';
	}

	function start($title,
			$withBanner = true,
			$trackStatus = false) {
		$banner = null;
		if ($withBanner) {
			$banner = $this->topBanner();
		}
		print($this->beginPage() .
			$this->head($title, $trackStatus) .
			$this->beginBody() .
			$banner);
	}

	function finish() {
		print($this->endBody() . $this->endPage());
	}
}

?>
