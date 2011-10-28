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
define('COMMON_DIRECTORY', MAIN_DIRECTORY . '/common');

define('ADODB_ABSTRACTION', COMMON_DIRECTORY . '/adodb/adodb.inc.php');

include_once(ADODB_ABSTRACTION);

//-------------------------
// MODIFY HERE 
//
//define('CRLF', "\r\n");
define('CRLF', '</br>');
//$brand = 'mysql';
$brand = 'mssql';
$server = 'localhost';
$account = 'joe';
$password = 'schmoe';
$database = 'viele';
//-------------------------

$conn = ADONewConnection($brand);
print('Attempting connection' . CRLF);
$conn->PConnect($server,
		$account,
		$password,
		$database);
print_r($conn);

if ($conn->isConnected()) {
     print(CRLF . 'Connected to ' . $brand . ' located on ' . $server . CRLF);
     print('Inspecting database ' . $database . ' as ' . $account . CRLF);
     $tables = $conn->MetaTables('TABLES');
     foreach ($tables as $key => $tname) {
          print(CRLF . 'Found table ' . $tname . CRLF);
          print('---------' . CRLF);
          $cols = $conn->MetaColumns($tname);
          foreach ($cols as $key2 => $cobj) {
               print('Found ' . $cobj->type . ' column called ' . $cobj->name . CRLF);
//print_r($cobj);
          }
          print('---------' . CRLF);
     }
     $conn->Close();
     print('Connection closed' . CRLF);
} else {
     print('COULD NOT MAKE A CONNECTION!');
}

?>
