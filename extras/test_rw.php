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

define('MAIN_DIRECTORY', '..');
define('SOURCE_DIRECTORY', MAIN_DIRECTORY . '/sources');
define('TARGET_DIRECTORY', MAIN_DIRECTORY . '/targets');
define('EXTRACT_DIRECTORY', MAIN_DIRECTORY . '/extracts');

print('start<br/>');
$configType[] = 'SOURCE';
$configType[] = 'TARGET';
$configType[] = 'EXTRACT';
foreach ($configType as $key => $val) {
	print('create ' . $val . '<br/>');
	$LOCATION = new Location(determine_type($val));
	$CONFIGURATION = $LOCATION->getConfiguration('Test');
	$CONFIGURATION->setValue('FIRST_TEST', 'Test Write One');
	$firstTest = $CONFIGURATION->getValue('FIRST_TEST');
	print('first test result: ' . $firstTest . '<br/>');
        $data=null;
        $data[]='Test';
        $data[]='Write';
        $data[]='Two';
	$CONFIGURATION->setVariable('SECOND_TEST', $data);
        $LOCATION->saveConfiguration($CONFIGURATION,'Test');
	print('save ' . $val . '<br/>');
	$secondTest = $CONFIGURATION->getVariable('SECOND_TEST');
	print('second test result: ');
	print_r($secondTest);
	print('<br/>');
        $LOCATION->removeConfiguration('Test');
	print('remove ' . $val . '<br/>');
	print('<br/>');
}
print('stop<br/>');

class Configuration {

	var $contents;
	var $valueIndex;
	var $variableIndex;
	var $fileName;

	function Configuration($fileName = null) {
		if ($fileName != null) {
			$this->fileName = $fileName;
			if (file_exists($fileName)) {
				$this->contents = file($fileName);
			}
			$this->buildIndex();
		}
	}

	function buildIndex() {
		if ($this->contents != null) {
//print("BUILD INDEX\r\n");
			$this->valueIndex = null;
			$searchKey = 'define("';
			foreach ($this->contents as $line_num => $line) {
				$pos = strpos($line, $searchKey);
				if ($pos === false) {
				} else {
//
// find comma 
//
					$source = substr($line, 0, strpos($line, ','));
					$source = trim($source);

//
// find left paren 
//
					$source = substr($source, strpos($source, '(') + 1, strlen($source));
					$source = trim($source);

//
// remove single and double quotes
//
					$source =  trim($source, "\x22\x27");

//
// enter into index
//
					$this->valueIndex[$source] = $line_num;
				}
			}
			$source = null;
			$this->variableIndex = null;
			$searchKey = '=array(';
			foreach ($this->contents as $line_num => $line) {
				$pos = strpos($line, $searchKey);
				if ($pos === false) {
				} else {
//
// find equals 
//
					$source = substr($line, 0, strpos($line, '='));
					$source = trim($source);

//
// remove $
//
					$source = substr($source, strpos($source, '$') + 1, strlen($source));
					$source = trim($source);

//
// enter into index
//
					$this->variableIndex[$source] = $line_num;
				}
			}
		}
	}

	function getValue($key) {
		if ($this->valueIndex == null) {
			return null;
		}
		if (!array_key_exists($key, $this->valueIndex)) {
			return null;
		}
		$line = $this->valueIndex[$key];
		if ($line === false) {
			return null;
		}

//
// get the line
//
		$source = $this->contents[$line]; 

//
// find comma 
//
		$temp = substr($source, strpos($source, ',') + 1, strlen($source));
		$temp = trim($temp);

//
// find right paren 
//
		$temp = substr($temp, 0, strrpos($temp, ')'));
		$temp = trim($temp);

//
// remove single and double quotes
//
		return trim($temp, "\x22\x27");

	}

	function setValue($key,
				$val) {
		$line = false;
		if ($this->valueIndex == null) {
			$this->contents[] = '<?php';
			$this->contents[] = "\n";
			$this->contents[] = '?>';
		} else {
			if (array_key_exists($key, $this->valueIndex)) {
				$line = $this->valueIndex[$key];
			}
		}

//
// value not defined yet
//
		if ($line === false) {
			$line = sizeof($this->contents) -1;
			$this->contents[$line + 1] = "\n";
			$this->contents[] = '?>';
		}

		$this->contents[$line] = "define(\x22" . $key . "\x22,\x22" . $val . "\x22);" . "\n";
		$this->buildIndex();
	}

	function getVariable($key) {
		if ($this->variableIndex == null) {
			return null;
		}

		if (!array_key_exists($key, $this->variableIndex)) {
			return null;
		}
		$line = $this->variableIndex[$key];
		if ($line === false) {
			return null;
		}
//               include_once($this->fileName);
		@include($this->fileName);
//          include($this->fileName);
//echo "<XMP>FOUND $key LINE $line FILE $this->fileName</XMP>";
		return $$key;
	}

	function setVariable($key,
				$val) {
//
// if this is an array, convert to a string
//
		if (is_array($val)) {
			$setting = '$' . $key . '=array(';
			foreach ($val as $a_key => $value) {
				$setting .= '"' . $a_key . '"=>"' . $value . '",';
			}
			$setting = substr($setting, 0, strlen($setting) - 1);
			$setting .= ');';
		} else {
			$setting = $val;
		}

//
// write value
//
		$lookup = true;
		if ($this->variableIndex == null) {
			$lookup = false;
		} else {
			if (!array_key_exists($key, $this->variableIndex)) {
				$lookup = false;
			}
		}
		$line = false;
		if ($lookup) {
			$line = $this->variableIndex[$key];
		}

		if ($line === false) {
			if ($setting != null) {
				$this->contents[sizeof($this->contents) - 1] = $setting . "\n";
				$this->contents[] = '?>';
			}
		} else {
			if ($setting != null) {
				$this->contents[$line] = $setting . "\n";
			} else {
				$this->contents[$line] = null;
			}
		}
		$this->buildIndex();
	}

	function write($fileName) {
		$fp = fopen($fileName, 'w');
		foreach ($this->contents as $line_num => $line) {
			if ($line != null) {
				fwrite($fp, $line);
			}
		}
		fclose($fp);
	}

}


class Location {
     var $directory;

	function Location($directory = null) {
		$this->directory = $directory;
	}

	function getConfiguration($aName = null) {
		if ($aName == null) {
			return new Configuration();
		}

		if ($this->directory == null) {
			return new Configuration($aName);
		}

		return new Configuration($this->toPath($aName));
	}

	function removeConfiguration($aName) {
		unlink($this->toPath($aName));
	}

	function saveConfiguration($CONFIGURATION,
                                $aName) {
		$CONFIGURATION->write($this->toPath($aName));
	}

	function toPath($name) {
		return $this->directory . '/' .  basename($name);
	}

}

function determine_type($typeName) {
	if (!$typeName) {
		return null;
	}
	switch($typeName){
		case 'SOURCE':
			return SOURCE_DIRECTORY;
		case 'TARGET':
			return TARGET_DIRECTORY;
		case 'EXTRACT':
			return EXTRACT_DIRECTORY;
	}
	return null;
}

?>
