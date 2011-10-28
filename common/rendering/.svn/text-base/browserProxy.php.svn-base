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

class BrowserProxy 
{
 
     var $head;
     var $body;
     var $contents;
     var $source;

     var $bgcolor;
     var $text;
     var $title;
     var $align;
     var $quiturl;

     var $message;
     var $application;

     var $STYLIST;

     function BrowserProxy($application = null)
     {
          $this->message = 'Browser Proxy';

          if ($application == null)
          {
               $this->application = 'Browser Proxy';
          }
          else
          {
               $this->application = $application;
          }

          $this->STYLIST = new Stylist();

     }

     function render($html,
                     $title = null,
                     $align = null,
                     $bgcolor = null,
                     $text = null,
                     $quiturl = null)
     {
          $this->parse($html);

//
// extract information
//
          $this->extract();

//
// override with passed parameters
//
          if ($align != null)
          {
               $this->setAlign($align);
          }
          if ($title != null)
          {
               $this->setTitle($title);
          }
          if (is_string($bgcolor))
          {
               $this->setBgcolor($bgcolor);
          }
          if ($text != null)
          {
               $this->setText($text);
          }
          if ($quiturl != null)
          {
               $this->setQuiturl($quiturl);
          }

//
// generate the browser
//
          $this->generate();

          return $this->getSource();
     }

     function stream()
     {
          $this->generate();
          print($this->getSource());
     }

     function getSource()
     {
          return $this->source;
     }

     function getContents()
     {
          return $this->contents;
     }

     function setContents($contents)
     {
          $this->contents = trim($contents);
     }

     function setAlign($align)
     {
          $this->align = $align;
     }

     function setTitle($title)
     {
          $title = trim($title);
          if (strlen($title) > 0)
          {
               $this->title = $title . ' - ' . $this->application;
          }
          else
          {
               $this->title = $this->application;
          }
     }

     function setBgcolor($bgcolor)
     {
          $this->bgcolor = trim($bgcolor);
     }

     function setText($text)
     {
          $this->text = trim($text);
     }

     function setQuiturl($quiturl)
     {
          $this->quiturl = trim($quiturl);
     }

     function reset()
     {
          $this->title = null;
          $this->bgcolor = null;
          $this->text = null;
          $this->align = null;
          $this->quiturl = null;
     }

     function parse($source)
     {
//
// initialize 
//
          $this->reset();

          $this->head = null;
          $this->body = null;

//
// strip off the <html> tag
//
          $start = $this->findPos($source,'<html');
          if ($start === false)
          {
               $html = $source;
          }
          else
          {
               $end = strpos($source, '>') + 1;
               $temp = substr($source, $end + 1, strlen($source));
               $temp = trim($temp);
               $start = $this->findPos($temp,'</html>');
               $temp = substr($temp, 0, $start);
               $html = trim($temp);
          }

//
// strip off the <head> tag
//
          $start = $this->findPos($html,'<head>');
          if ($start === false)
          {
               $body = $html;
          }
          else
          {
               $end = $this->findPos($html,'</head>') + 7;
               $temp = substr($html, $start, $end);
               $this->head = $temp;
               $body = substr($html, $end + 1, strlen($html));
               $body = trim($body);
          }

//
// strip off the leading <body> and trailing </body> tags
//
          $start = $this->findPos($body,'<body');
          if ($start === false)
          {
               $this->contents = $body; 
          }
          else
          {
               $end = strpos($body, '>') + 1;
               $this->body = substr($body, $start, $end);
               $temp = substr($body, $end + 1, strlen($body));
               $temp = trim($temp);
               $start = $this->findPos($temp,'</body>');
               $temp = substr($temp, 0, $start);
               $this->setContents($temp);
          }
     }

     function extract()
     {
//
// if there is a body tag, look for background color
//
          $this->setBgcolor($this->getParameter($this->body, 'bgcolor'));

//
// if there is a body tag, look for text color
//
          $this->setText($this->getParameter($this->body, 'text'));

//
// if there is a head tag, look for a title tag
//
          $titleTag = $this->getTag($this->head, 'title');

//
// if there is a title tag, get the CDATA 
//
          $this->setTitle($this->getValue($titleTag));
          
//
// if the first tag is table, see if it has an align parameter 
//
          $start = $this->findPos($this->contents, '<table');
          if ($start === false)
          {
          }
          else
          {
               $this->setAlign($this->getParameter($this->contents, 'align'));
          }

     }

     function getParameter($tag,
                           $name)
     {
          $start = $this->findPos($tag, $name);
          if ($start === false)
          {
               return null;
          }
          $temp = substr($tag, $start, strlen($tag));
          $temp = trim($temp);
          $start = strpos($temp, '"') + 1;
          $temp = substr($temp, $start, strlen($temp));
          $temp = trim($temp);
          $end = strpos($temp, '"');
          $temp = substr($temp, 0, $end);

          return $temp;
     }

     function getTag($tag,
                     $name)
     {
          $name = strtolower(trim($name));
          $beginTag = '<' . $name . '>';
          $endTag = '</' . $name . '>';

          $start = $this->findPos($tag, $beginTag);
          if ($start === false)
          {
               return null;
          }
          $temp = substr($tag, $start, strlen($tag));
          $temp = trim($temp);
          $end = $this->findPos($temp, $endTag);
          $temp = substr($temp, 0, $end + strlen($endTag));
          $temp = trim($temp);

          return $temp;
     }

     function getValue($tag)
     {
          if ($tag == null)
          {
               return null;
          }

//
// determine the tag name
//
          $start = $this->findPos($tag, '<');
          $finish = $this->findPos($tag, '>');
          $space = $this->findPos($tag, ' ');
          if ($space != null)
          {
               if ($space < $finish)
               {
                    $end = $space;
               }
               else
               {
                    $end = $finish;
               }
          }
          else
          {
               $end = $finish;
          }
          $name = substr($tag, $start + 1, $end - 1);
          $name = strtolower(trim($name));

//
// strip value
//
          $beginTag = '<' . $name . '>';
          $endTag = '</' . $name . '>';
          $start = $this->findPos($tag, $beginTag);
          if ($start === false)
          {
               return null;
          }
          $temp = substr($tag, $start + strlen($beginTag), strlen($tag));
          $temp = trim($temp);
          $end = $this->findPos($temp, $endTag);
          $temp = substr($temp, 0, $end);

          return $temp;
          
     }

     function renderMessage()
     {
          return "\n" . '<!-- ' . $this->message . ' -->' . "\n"; 
     }

     function generate()
     {
//
// check for defaults 
//
          if ($this->align == null)
          {
               $this->setAlign('left');
          }
          if ($this->title == null)
          {
               $this->setTitle('');
          }
          if ($this->bgcolor == null)
          {
               $this->setBgcolor('white');
          }
          if ($this->text == null)
          {
               $this->setText('black');
          }

//
// begin drawing 
//
          $this->source = $this->renderMessage() .
                          '<table border="1" width="800" ' .
                          'cellspacing="0" cellpadding="0">' .
                          "\r\n" .
                          '<tr><td align="left">' .
                          $this->browserBar($this->title) .
                          '</td></tr>' . "\r\n" .
                          '<tr><td>' .
                          '<table bgcolor="' .
                          $this->bgcolor .
                          '" cellpadding="5" width="100%">' .
                          '<tr><td>' .
                          '<table align="' . $this->align . '">' .
                          '<tr><td>' .
                          '<font style="color:' . $this->text . ';">' .
                          $this->renderMessage() .
                          $this->contents .
                          $this->renderMessage() .
                          '</font>' .
                          '</td></tr>' .
                          '</table>' .
                          '</td></tr>' .
                          '</table>' . "\r\n" .
                          '</td></tr>' .
                          '</table>' .
                          $this->renderMessage(); 
     }

     function browserBar($title)
     {
//
// browser icon
//
          $browser_icon = null;
          $location_image = RESOURCE_DIRECTORY . '/' . 'browser_icon.gif';
          if (file_exists($location_image))
          {
               $browser_icon = '<td align="right" bgcolor="6666cc" width="25">' .
                               '<table width="100%" border="0" cellspacing="0" cellpadding="0">' .
                               '<tr>' .
                               '<td align="center" width="5">' .
                               '&nbsp;' .
                               '</td>' .
                               '<td align="center" width="15">' .
                               '<img border="0" src="' . $location_image . '">' .
                               '</td>' .
                               '<td align="center" width="5">' .
                               '&nbsp;' .
                               '</td>' .
                               '</tr>' .
                               '</table>' .
                               '</td>';
          }
 
//
// quit icon
//
          $quit_icon = null;
          $quit_image = RESOURCE_DIRECTORY . '/' . 'exit.gif';
          if (file_exists($quit_image))
          {
               $image = '<img border="0" src="' . $quit_image . '">';
               if ($this->quiturl != null)
               {
                    $quit_icon = '<a href="' . $this->quiturl . '">' .  
                                 $image .  '</a>';
               }
               else
               {
                    $quit_icon = $image;
               }
               $quit_icon = '<td align="center" width="15">' .
                            '<img src="' . $quit_icon . '">' .
                            '</td>';
          }

//
// dock icon
//
          $dock_icon = null;
          $dock_image = RESOURCE_DIRECTORY . '/' . 'dock.gif';
          if (file_exists($dock_image))
          {
               $dock_icon = '<td align="center" width="15">' .
                            '<img src="' . $dock_image . '">' .
                            '</td>';
          }

//
// expand icon
//
          $expand_icon = null;
          $expand_image = RESOURCE_DIRECTORY . '/' . 'expand.gif';
          if (file_exists($expand_image))
          {
               $expand_icon = '<td align="center" width="15">' .
                              '<img src="' . $expand_image . '">' .
                              '</td>';
          }

          if ($browser_icon == null && 
              $dock_icon == null &&
              $expand_icon == null &&
              $quit_icon == null) 
          {
               return '<table width="100%" border="0" cellspacing="0" ' .
                      'cellpadding="0"><tr>' . "\r\n" .
                      '<td align="center" bgcolor="#6666cc">' .
                      $this->STYLIST->formatBuiltinText($title, 'white') .
                      '</td>' . "\r\n" .
                      '</tr></table>';
          }
 
          return '<table width="100%" border="0" cellspacing="0" ' .
                 'cellpadding="0"><tr>' . "\r\n" .
                 $browser_icon . "\r\n" .
                 '<td align="left" bgcolor="#6666cc">' .
                 $this->STYLIST->formatBuiltinText($title, 'white') .
                 '</td>' . "\r\n" .
                 '<td align="right" bgcolor="lightgrey" width="45">' .
                 '<table width="100%" border="1" ' .
                 'cellspacing="0" cellpadding="0">' .
                 '<tr>' .
                 $dock_icon .
                 $expand_icon .
                 $quit_icon .
                 '</tr>' .
                 '</table>' .
                 '</td>' . "\r\n" .
                 '</tr></table>';
     }

     function findPos($haystack, $needle)
     {
          $pos = strpos($haystack, strtolower($needle));
          if ($pos === false)
          {
               $pos = strpos($haystack,strtoupper($needle));
               if ($pos === false)
               {
                    return false;
               }
          }
          return $pos;
     }

}

?>
