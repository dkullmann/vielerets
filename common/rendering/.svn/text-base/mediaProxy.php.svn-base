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

class MediaProxy 
{

     var $name;
     var $url;
     var $desc;

     function MediaProxy($aName,
                         $aURL,
                         $aDesc) 
     {
          $this->name = $aName;
          $this->url = $aURL;
          $this->desc = $aDesc;
     }

     function getName()
     {
          return $this->name;
     }

     function getDescription()
     {
          return $this->desc;
     }

     function getURL() 
     {
          $pos = strpos($this->url, ".");
          if ($pos === false)
          {
               return $this->url;
          }
          if ($pos != 0)
          {
               return $this->url;
          }
          $uri = urldecode($this->url);
          $uri = $this->url;
          return "http://" . 
                 $_SERVER["HTTP_HOST"] .  
                 dirname($_SERVER["PHP_SELF"]) .
                 substr($uri, $pos + 1, strlen($uri));
     }

     function generateLink($width = null) 
     {
          if ($width)
          {
               return sprintf("<img border=\"0\" src=\"%s\" width=\"%s\" alt=\"%s\"/>",
                              $this->url,
                              $width,
                              $this->desc);
          }
 
          return sprintf("<img border=\"0\" src=\"%s\" alt=\"%s\"/>",
                         $this->url,
                         $this->desc);
     }

     function generateLayout($width) 
     {
          if (MEDIA_DESCRIPTION)
          {
               if ($this->desc == null)
               {
                    $buffer = $this->generateLink($width);
               }
               else 
               {
                    $buffer = sprintf("<table align=\"left\" width=\"%s\" border=\"2\" cellpadding=\"5\">",
                                      "100%");
                    $buffer .= "<tr align=\"left\"><td>";
                    $buffer .= $this->generateLink($width);
                    $buffer .= "</td></tr>";
                    $buffer .= "<tr><td>";
                    $buffer .= $this->STYLIST->formatText($this->desc);
                    $buffer .= "</td></tr>";
                    $buffer .= "</table>";
               }
          }
          else
          {
               $buffer = $this->generateLink($width);
          }

          return $buffer;
     }

}

?>
