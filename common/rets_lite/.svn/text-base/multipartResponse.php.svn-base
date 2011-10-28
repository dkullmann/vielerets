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
// defines
//
define('INTERREALTY_MULTIPART_HACK', false);	// change separators in the middle of a payload 

class MultipartResponse {

     var $boundary;
     var $terminator = "\r\n";
     var $contents;
     var $imageThreshold = 1000;

     function MultipartResponse() {
     }

     function setImageThreshold($value) {
          $this->imageThreshold = $value;
     }

     function isMultipart($header = null) {

          if ($header == null) {
               return false;
          }

          $pos = strpos($header, 'multipart/');
          if ($pos === false) {
               return false;
          }

//
// determine the boundary
//

          $pos = strpos($header, '=');
          $boundary = substr($header, $pos + 1, strlen($header));
          $boundary = trim($boundary, '"');
          $pos = strpos($boundary, '"');
          if ($pos > 0) {
               $boundary = substr($boundary, 0, $pos);
          }
          $this->boundary = trim($boundary);
     
          return true;
     }

     function determineSeparator($body) {

          if (INTERREALTY_MULTIPART_HACK) {
//echo "<XMP>BOUNDARY $boundary</XMP>";
               $this->terminator = null;
               $first = strpos($body, $this->boundary) + 
                        strlen($this->boundary);

//
// test for LF (10 or \n)
//
               $item = substr($body, $first, 1);
               if (ord($item) == 10) {
                    $this->terminator = "\n";
               } else {
//
// test for CR (13 or \r)
//
                    if (ord($item) == 13) {
                         $this->terminator = "\r\n";
                    }
               }
          }
     }

     function parseBody($body) {

          $this->contents = null;

          $this->determineSeparator($body);
          $big_buf = $body;
          $boundary_length = strlen($this->boundary);
          $terminator_length = strlen($this->terminator);
          $end_target = $boundary_length + 
                        2 + 
                        $terminator_length +
                        $terminator_length;
          while (strlen($big_buf) > $end_target) {
               $first = strpos($big_buf, $this->boundary) +
                        $boundary_length + $terminator_length;
               $second = strpos($big_buf, 
                                $this->boundary, 
                                $first);

//
// parse a part
               $part = substr($big_buf, 
                              $first, 
                              $second - $first - $terminator_length - 1);
//echo "<XMP>PART $part</XMP>";
               $anImage = null;
               $headers = null;
               $content = null;
               $work = $part;
               while (strlen($work) > 0) {
                    $pos = strpos($work, $this->terminator);
                    if ($pos === false) {
                         $anImage = $work;
                         break;
                    } else {
                         $candidate = trim(substr($work, 0, $pos));
                         $pos2 = strpos($candidate, ':');
                         if ($pos2 === false) {
                              $anImage = $work;
                              break;
                         } else {
                              $work = substr($work, 
                                             $pos + $terminator_length, 
                                             strlen($work));
                              $length = strlen($work);
                              if ($length < $terminator_length) {
                                   $anImage = $candidate;
                                   break;
                              }
                              $temp = explode(":", $candidate);
                              $hpos = strpos($candidate, ':');
                              $value = trim(substr($candidate, $hpos + 1, strlen($candidate)));
                              $key = trim($temp[0]);
                              $headers[strtoupper($key)] = $value;
                         }
                    }
               }
               $content['HEADERS'] = $headers; 

//
// assign image  
//
               if (strlen($anImage) > $this->imageThreshold) {
                    $content['IMAGE'] = trim($anImage); 
               } else {
                    $content['IMAGE'] = null; 
               }
               $this->contents[] = $content;

//
// prepare for next interation 
//
               $big_buf = substr($big_buf, 
                                 $second - $terminator_length, 
                                 strlen($big_buf));
          }

          return $this->contents;
     }

     function getHeader($item,
                        $needle) {

          if ($this->contents == null) {
               return null;
          } 

          if (array_key_exists($item, $this->contents)) { 
               $part = $this->contents[$item]; 
               if (array_key_exists('HEADERS', $part)) { 
                    $headers = $part['HEADERS']; 
                    if ($headers != null) {
                         if (array_key_exists(strtoupper($needle), $headers)) { 
                              $value = $headers[strtoupper($needle)];
                              if (is_array($value)) {
                                   return $value;
                              } 
                              return trim($value);
                         }
                    }
               }
          }

          return null;
     }

     function getImages() {
          foreach ($this->contents as $key => $value) {
               $part = $this->contents[$key]; 
               if (array_key_exists('IMAGE', $part)) {
                    $images[] = trim($part['IMAGE']); 
               } 
          }
          return $images;
     }

}

//------------

?>
