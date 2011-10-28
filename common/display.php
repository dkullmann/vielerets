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
// Debug Setting 
//
define('SCREEN_DEBUG_MODE', false);

//
// Font Settings 
//
define('FONT_COLOR','black');
define('DATA_POINT_SIZE','10pt');
define('HIGHLIGHT_FONT_COLOR','red');
define('SYSTEM_POINT_SIZE','12pt');
define('FONT_FACE','Verdana, Sans');

//
// Layout Settings 
//
define('FRAME_INSET','8');
define('FRAME_BORDER','0');
define('FRAME_BACKGROUND_COLOR','');

define('PANEL_INSET','6');
define('PANEL_SPACING','6');
define('PANEL_BORDER','4');
define('PANEL_BACKGROUND_COLOR','');

define('NAVBAR_INSET','6');
define('NAVBAR_SPACING','2');

define('DATA_SPACING','0');
define('DATA_PADDING','6');
define('DATA_BORDER','0');
//define('DATA_BACKGROUND_COLOR','#fffff0');
//define('DATA_BACKGROUND_COLOR','#eeeeee');
define('DATA_BACKGROUND_COLOR','#ffffd0');

define('HEADING_BACKGROUND_COLOR','yellow');

define('FIELD_DESCRIPTION_WIDTH','250');

//
// Built-in Settings 
//
define('BUILTIN_SPACING', 0);			// builtin spacing 
define('BUILTIN_PADDING', 5);			// builtin padding 
define('BUILTIN_BORDER', 2);			// builtin border 
define('BUILTIN_BACK_COLOR', 'lightGray');	// builtin background 
define('BUILTIN_TABLE_BACK_COLOR', 'white');	// builtin table background 
define('BUILTIN_TABLE_TEXT_COLOR', 'black');	// builtin table font color 
define('BUILTIN_FONT_COLOR', 'black');		// builtin font color 
define('BUILTIN_FONT_FACE', 'Verdana, Sans');	// builtin font face 
define('BUILTIN_HEADER_TEXT_COLOR', 'white');	// builtin header background 
define('BUILTIN_HEADER_BACK_COLOR', 'green');	// builtin header text 
define('BUILTIN_POINT_SIZE', '8pt');		// builtin point size 
define('BUILTIN_MAX_TEXT_FIELD', 64);		// builtin max text field 

//
// Experimental Settings 
//
define('EXP_CACHE_SIZE', 15);			// cache size for screen buffer 
define('EXP_NAV_BUTTONS', false);		// buttons navigating views 

//
// display settings
//
define('PROJECT_CSS', 'styles.css');
define('PROJECT_FONT_COLOR', 'black');
define('FORMAT_BINARY_AS_CHECKBOX', true);

//
// common includes
//
include_once(COMMON_DIRECTORY . '/rendering/component.php');

function booleanFromArg($name, $vars)
{
     if (array_key_exists($name, $vars))
     {
          if (FORMAT_BINARY_AS_CHECKBOX)
          {
               return true;
          }
          $value = $vars[$name];
          if ($value == 'false')
          {
               return false;
          }
          return true;
     }
     return false;
}

?>
