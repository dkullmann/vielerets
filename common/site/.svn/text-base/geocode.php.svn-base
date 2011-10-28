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

class GeocodedAddress
{
     var $address;
     var $latitude;
     var $longitude;
     var $error;
     var $refinements;
     var $realAddress;

     function GeocodedAddress()
     {
     }

     function setAddress($aValue)
     {
          $this->address = $aValue;
     }

     function setGeocode($aLatitude,
                         $aLongitude)
     {
          $this->latitude = $aLatitude;
          $this->longitude = $aLongitude;
     }

     function setError($aValue)
     {
          $this->error = $aValue;
     }

     function addRefinement($aValue)
     {
          $this->refinements[] = $aValue;
     }

     function setRealAddress($aValue)
     {
          $this->realAddress = $aValue;
     }

     function getAddress()
     {
          return $this->address;
     }

     function getLatitude()
     {
          return $this->latitude;
     }

     function getLongitude()
     {
          return $this->longitude;
     }

     function getError()
     {
          return $this->error;
     }

     function hasError()
     {
          if ($this->error == null)
          {
               return false;
          }
          return true;
     }

     function getRefinement($index)
     {
          return $this->refinements[$index];
     }

     function hasRefinements()
     {
          if ($this->refinements == null)
          {
               return false;
          }
          return true;
     }

     function getRealAddress()
     {
          return $this->realAddress;
     }

     function isApproximation()
     {
          if ($this->realAddress == null)
          {
               return false;
          }
          return true;
     }

}

class Geocoder
{

     var $REQUESTOR;
     var $isSimulation;

     function Geocoder()
     {
          $this->REQUESTOR = new NetRequest("PushPin",
                                            "1.0",
                                            false,
                                            false,
                                            true);
     }

     function setSimulation($aValue)
     {
          $this->isSimulation = $aValue;
     }

     function asGeocode($address)
     {
          $GEOCODED_ADDRESS = new GeocodedAddress();
          $GEOCODED_ADDRESS->setAddress($address);

//
// handle simulation
//
          if ($this->isSimulation)
          {
               if ($address == "645 Main Street")
               {
//                    $body = "<center lat=\"47.53\" lng=\"121.45\"/><error><errortip>nope</errortip</error>";
                    $body = "<center lat=\"47.53\" lng=\"121.45\"/>";
               }
               else
               {
//                    $body = "<center lat=\"47.53\" lng=\"121.45\"/>";
                    $body = "<center lat=\"-47.53\" lng=\"-121.45\"/><refinements><refinement><query>645 Main Street</query></refinement><refinement><query>645 N. Main Street</query></refinement></refinements>";
//                    $body = "<center lat=\"-47.53\" lng=\"-121.45\"/><refinements><refinement><query>645 Main Street</query></refinement></refinements>";
               }
          }
          else
          {
               $url = "http://maps.google.com/maps?q=" . urlencode($address);
               $body = $this->REQUESTOR->fetch($url);
          }

//
// look for errors 
//
          preg_match("/<error>(.*?)<\/error>/", 
                     $body,
                     $errs);
          if (sizeof($errs) > 0)
          {
               $GEOCODED_ADDRESS->setError("Address format not recognized");
          }

//
// look for latitude and longitude
//
          preg_match("/<center lat=\"([0-9.-]{1,})\" lng=\"([0-9.-]{1,})\"\/>/", 
                     $body,
                     $regs);
          $GEOCODED_ADDRESS->setGeocode($regs[1], $regs[2]);

//
// look for refinements 
//
          preg_match_all("/<refinement><query>(.*?)<\/query>/", 
                         $body,
                         $refs);
          $matches = $refs[1];
          foreach ($matches as $key => $value) 
          {
               $GEOCODED_ADDRESS->addRefinement($value);
          }

          return $GEOCODED_ADDRESS;
     }

     function lookup($address)
     {
          $GEOCODED_ADDRESS = $this->asGeocode($address);
//
// if there are refinements, use them 
//
          if ($GEOCODED_ADDRESS->hasRefinements())
          {
               $GEOCODED_ADDRESS = $this->asGeocode($GEOCODED_ADDRESS->getRefinement(0));
               $GEOCODED_ADDRESS->setRealAddress($address);
          }

          return $GEOCODED_ADDRESS; 
     }

}

//
//------------

?>
