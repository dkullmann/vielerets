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

class MediaContainer {

     var $name;
     var $url;
     var $desc;

     function MediaContainer($aName,
                             $aURL = null,
                             $aDesc = null) {
          $this->name = $aName;
          $this->url = urldecode($aURL);
          $this->desc = $aDesc;
     }

     function getEncodedURL() {
          $pos = strpos($this->url, 'ID=');
          if ($pos > 0) {

               // strip out the tag is it exists

               return substr($this->url, 0, $pos + 3) .
                             urlencode(substr($this->url, 
                                              $pos + 3, 
                                              strlen($this->url)));
          }
          return urlencode($this->url);
     }

     function getURL() {
          return $this->url;
     }

     function getName() {
          return $this->name;
     }

     function getDescription() {
          return $this->desc;
     }

}

?>
