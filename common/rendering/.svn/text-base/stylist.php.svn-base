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

class Stylist 
{

     function Stylist()
     {
     }

     function displayMessage($text)
     {
          echo $this->formatText($text, PROJECT_FONT_COLOR) . '<br/>';
          $this->flush_to_browser();
     }

     function flush_to_browser()
     {
          for($i = 0; $i < 40000; $i++)
          {
               echo " ";
          }
          flush();
     }

     function createButtonStyle($font_color = null,
                                $background_color = null,
                                $override_face = null) 
     {
          if ($background_color == null)
          {
               $background = 'background-color: #e8e8e8;';
          }
          else
          {
               $background = 'background-color: ' . $background_color . ';';
          }

          if ($font_color == null)
          {
               $color = 'color: #000000;';
          }
          else
          {
               $color = 'color: ' . $font_color . ';';
          }

          if ($override_face == null)
          {
               $family = 'font-family:' . FONT_FACE . ';';
          }
          else
          {
               $family = 'font-family:' . $override_face . ';';
          }

          return 'vertical-align:middle;' .
                 $family .
                 'font-size:10pt;' .
                 'text-decoration:none;' .
                 'border-left:2px solid #ffffff;' .
                 'border-top:2px solid #ffffff;' .
                 'border-right:2px solid #888888;' .
                 'border-bottom:2px solid #888888;' .
                 'padding: 1px 10px;' .
                 'margin-left: -4px;' .
                 'margin-right: -4px;' .
                 $background . 
                 $color;
     }

     function createPseudoLinkStyle($override_face = null,
                                    $override_points = null,
                                    $override_color = null,
                                    $with_underline = true) 
     {
          if ($with_underline)
          {
               $underline = 'text-decoration:underline;';
          }
          else
          {
               $underline = 'text-decoration:none;';
          }

          return $this->createLinkStyle($override_face,
                                        $override_points,
                                        $override_color) .
                 $underline;
     }

     function createLinkStyle($override_face = null,
                              $override_point = null,
                              $override_color = null)
     {

//          $valign = "vertical-align:middle;";

          if ($override_face == null)
          {
               $family = 'font-family:' . FONT_FACE . ';';
          }
          else
          {
               $family = 'font-family:' . $override_face . ';';
          }

          if ($override_point == null)
          {
               $size = 'font-size:' . DATA_POINT_SIZE . ';';
          }
          else
          {
               $size = 'font-size:' . $override_point . ';';
          }

          if (is_string($override_color))
          {
               $color = 'color:' . $override_color . ';';
          }
          else
          {
               $color = null;
          }

          return $family . $size . $color;
     }

     function createItalicTextStyle($override_point = null,
                                    $override_color = null,
                                    $override_face = null) 
     {
//          return 'font-weight:bold;' .
//                 'font-style:italic;' .
          return 'font-style:italic;' .
                 $this->createTextStyle($override_point,
                                        $override_color,
                                        $override_face); 
     }

     function createBoldTextStyle($override_point = null,
                                  $override_color = null,
                                  $override_face = null) 
     {
          return 'font-weight:bold;' .
                 $this->createTextStyle($override_point,
                                        $override_color,
                                        $override_face); 
     }

     function createTextStyle($override_point = null,
                              $override_color = null,
                              $override_face = null) 
     {
          $valign = 'vertical-align:middle;';
          if ($override_face == null)
          {
               $family = 'font-family:' . FONT_FACE . ';';
          }
          else
          {
               $family = 'font-family:' . $override_face . ';';
          }

          if ($override_point == null)
          {
               $size = 'font-size:' . DATA_POINT_SIZE . ';';
          }
          else
          {
               $size = 'font-size:' . $override_point . ';';
          }

          if (is_string($override_color))
          {
               $color = 'color:' . $override_color . ';';
          }
          else
          {
               $color = 'color:' . FONT_COLOR . ';';
          }

          return $valign . $family . $size . $color;
     }

/*
     function createCheckboxStyle($override_point = null,
                              $override_color = null,
                              $override_face = null) 
     {
          if ($override_face == null)
          {
               $family = 'font-family:' . FONT_FACE . ';';
          }
          else
          {
               $family = 'font-family:' . $override_face . ';';
          }

          if ($override_point == null)
          {
               $size = 'font-size:' . DATA_POINT_SIZE . ';';
          }
          else
          {
               $size = 'font-size:' . $override_point . ';';
          }

          if (is_string($override_color))
          {
               $color = 'color:' . $override_color . ';';
          }
          else
          {
               $color = 'color:' . FONT_COLOR . ';';
          }

          return $family . $size . $color;
     }

     function formatCheckbox($override_color = null) 
     {
          if (is_string($override_color))
          {
               $color = $override_color;
          }
          else
          {
               $color = BUILTIN_FONT_COLOR;
          }

          return 'style="' .
                 $this->createCheckboxStyle(BUILTIN_POINT_SIZE, $color) .
                 '"';
     }
*/

     function formatBuiltinText($message, 
                                $override_color = null) 
     {
          if (is_string($override_color))
          {
               $color = $override_color;
          }
          else
          {
               $color = BUILTIN_FONT_COLOR;
          }

          return '<font style="' .
                 $this->createTextStyle(BUILTIN_POINT_SIZE, $color) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatBoldBuiltinText($message, 
                                    $override_color = null)
     {
          if (is_string($override_color))
          {
               $color = $override_color;
          }
          else
          {
               $color = BUILTIN_FONT_COLOR;
          }

          return '<font style="' .
                 $this->createBoldTextStyle(BUILTIN_POINT_SIZE, $color) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatSystemText($message, 
                               $override_color = null) 
     {
          return '<font style="' .
                 $this->createTextStyle(SYSTEM_POINT_SIZE, $override_color) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatBoldSystemText($message, 
                                   $override_color = null)
     {
          return '<font style="' .
                 $this->createBoldTextStyle(SYSTEM_POINT_SIZE, 
                                            $override_color) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatItalicText($message, 
                               $override_color = null,
                               $override_point = null,
                               $override_face = null) 
     {
          return '<font style="' .
                 $this->createItalicTextStyle($override_point,
                                              $override_color,
                                              $override_face) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatText($message, 
                         $override_color = null,
                         $override_point = null,
                         $override_face = null) 
     {
          return '<font style="' .
                 $this->createTextStyle($override_point,
                                        $override_color,
                                        $override_face) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatBoldText($message, 
                             $override_color = null,
                             $override_point = null,
                             $override_face = null) 
     {
          return '<font style="' .
                 $this->createBoldTextStyle($override_point,
                                            $override_color,
                                            $override_face) .
                 '">' .
                 $message . 
                 '</font>';
     }

     function formatColumnSeparation()
     {
//          return '</td>' . CRLF . '<td>' . CRLF;
          return '</td><td>';
     }

}

?>
