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

class MediaRequest 
{

     var $primaryOnly;
     var $source;
     var $resource;
     var $listingNumber;
     var $mediaType;
     var $multipart;
     var $location;

     function MediaRequest($resource,
                           $listing_number,
                           $media_type,
                           $multipart,
                           $location,
                           $source) {
          $this->primaryOnly = false;
          $this->source = $source;
          $this->resource = $resource;
          $this->listingNumber = $listing_number;
          $this->mediaType = $media_type;
          $this->multipart = $multipart;
          $this->location = $location;
     }

     function setPrimaryOnly($bool) {
          $this->primaryOnly = $bool;
     }

     function getObjectID() {
          if ($this->primaryOnly) {
               return '0';
          }
          return '*';
     }

     function getListingNumber() {
          return $this->listingNumber;
     }

     function getSource() {
          return $this->source;
     }

     function getResource() {
          return $this->resource;
     }

     function getMediaType() {
          return $this->mediaType;
     }

     function getMultipart() {
          return $this->multipart;
     }

     function getLocation() {
          return $this->location;
     }

     function getMediaID($objectEntity = null, 
                         $objectID = null) {
          if ($objectEntity != null) {
               $theEntity = $objectEntity;
               $theObject = $objectID;
          } else {
               $theEntity = $this->listingNumber;
               $theObject = $this->getObjectID();
          }

          return urlencode($theEntity . ':' . $theObject);
//          return $theEntity . ':' . $theObject;
     }
 
}

//------------

?>
