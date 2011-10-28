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
// check for port 80
//
     $address = "crt.realtors.org";
     $port = 80;

echo "<XMP>OPENING ADDRESS $address PORT $port</XMP>";
     set_time_limit(0);
     $socket = @fsockopen($address, $port, $errno, $errstr);
     if (!$socket) 
     {
//
// posix codes - linux /usr/include/asm/errno.h
//
// ETIMEDOUT 	110	Connection timed out (firewall blocked?)
// ECONNREFUSED	111	Connection refused
// EHOSTDOWN	112	Host is down
// EHOSTUNREACH	113	No route to host
//
echo "<XMP>  FAILURE ERRNO $errno ERRSTR $errstr</XMP>";
     }
     else
     {
echo "<XMP>  SUCCESS</XMP>";
          fclose($socket);
     }

//
// check for port 6103 
//
     $address = "demo.crt.realtors.org";
     $port = 6103;

echo "<XMP>OPENING ADDRESS $address PORT $port</XMP>";
     set_time_limit(0);
     $socket = @fsockopen($address, $port, $errno, $errstr);
     if (!$socket) 
     {
//
// posix codes - linux /usr/include/asm/errno.h
//
// ETIMEDOUT 	110	Connection timed out (firewall blocked?)
// ECONNREFUSED	111	Connection refused
// EHOSTDOWN	112	Host is down
// EHOSTUNREACH	113	No route to host
//
echo "<XMP>  FAILURE ERRNO $errno ERRSTR $errstr</XMP>";
     }
     else
     {
echo "<XMP>  SUCCESS</XMP>";
          fclose($socket);
     }

?>
