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

/**
 * Community contributed script to use the Open-Realty admin account
 * to control VieleRETS access.
 *
 * In the root directory of VieleRETS, uncomment the include for this
 * file index.php.
 * 
 **/

error_reporting(0);

//
// provide the full path to the installation of Open-Realty
//
include_once('/work/projects/open-realty211/include/common.php');

$link = mysql_connect($db_server, $db_user,$db_password );
if (!$link) 
{
     die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db($db_database, $link);
if (!$db_selected) 
{
     die ('Can\'t use $db_database : ' . mysql_error());
}

//Make things righ and safe for the db
function safe_data($value) 
{
     if (get_magic_quotes_gpc()) 
     {
         $value = stripslashes($value);
     }
     $value = "'" . mysql_real_escape_string($value) . "'";
     return $value;
}

//First check for a logged in user
if (isset($_SESSION['id_name'])) 
{
 //let them pass
}
elseif (!isset($_SERVER['PHP_AUTH_USER'])) 
{
     header('WWW-Authenticate: Basic realm="' . 
            $config['site_title'] . 
            ' RETS Administration"');
     header('HTTP/1.0 401 Unauthorized');
     echo 'Sorry, You appear not to be authorized to be in this area';
     exit;
} 
else 
{
     $uname = safe_data($_SERVER['PHP_AUTH_USER']);
     $u_pw = safe_data(md5($_SERVER['PHP_AUTH_PW']));
     $sql_check_admin_user = 'SELECT `userdb_id`, `userdb_user_name` FROM `' . 
                             $config['table_prefix'] . 
                             'userdb` WHERE `userdb_user_name` = ' . 
                             $uname . 
                             ' AND `userdb_user_password` = ' . 
                             $u_pw . 
                             ' AND `userdb_is_admin` = \'yes\'';
     $check_admin_user = mysql_query($sql_check_admin_user);
     $admin_status = mysql_num_rows($check_admin_user);
     if ($admin_status > 0) 
     {
         $admin_user = mysql_fetch_row($check_admin_user);
         $admin_id = $admin_user[0];
         $admin_name = $admin_user[1];
         session_start();
         $_SESSION['id_name'] = $admin_name;
     }
     else 
     {
          header('WWW-Authenticate: Basic realm="' . 
                 $config['site_title'] . 
                 ' RETS Administration"');
          header('HTTP/1.0 401 Unauthorized');
          echo 'Sorry, You appear not to be authorized to be in this area ';
          exit;
     }
}

?>
