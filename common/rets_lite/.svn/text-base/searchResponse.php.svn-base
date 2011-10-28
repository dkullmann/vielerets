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

class SearchResponse 
     extends AbstractRetsExchange {

     var $maxCols;
     var $maxRows;
     var $values;
     var $types;
     var $interpretations;
     var $queryCount;
     var $rawData;
     var $contentType;
     var $error;

     function SearchResponse($aDataStream,
                             $contentType = 'text/xml',
                             $types = null,
                             $interpretations = null) {
          parent::AbstractRetsExchange();
//
// use rets_lite/xml.php 
//
          $this->contentType = $contentType;
          $aParser = new CompactParser();

          $aParser->parse($aDataStream);
          $this->maxCols = sizeof($aParser->getColumns());
          if ($this->maxCols > 0) {
               $this->setValues($aParser->getData());
               $this->maxRows = sizeof($this->values);
          }

//
// check row count 
//
          if ($this->maxRows == 0) {
               $this->rawData = $aDataStream;
          }

//
// check RETS Count
//
          $this->queryCount = $aParser->getQueryCount();

//
// data types
//
          $this->setTypes($types);

//
// interpretations 
//
          $this->setInterpretations($interpretations);
     }

     function getRow($aRow) {
          return $this->values[$aRow];
     }

     function getRowCount() {
          if ($this->maxRows == null) {
               $this->maxRows = 0;
          }

          return $this->maxRows;
     }

     function getContentType() {
          return $this->contentType;
     }

     function getQueryCount() {
          return $this->queryCount;
     }

     function getColumnCount() {
          return $this->maxCols;
     }

     function getRawData() {
          return $this->rawData;
     }

     function getValues() {
          return $this->values;
     }

     function setValues($data) {
          $this->values = $data;
     }

     function getTypes() {
          return $this->types;
     }

     function setTypes($types = null) {
          $this->types = $types;
     }

     function getError() {
          return $this->error;
     }

     function setError($error) {
          $this->error = $error;
     }

     function setInterpretations($interpretations) {
          $this->interpretations = $interpretations;
     }

     function getInterpretations() {
          return $this->interpretations;
     }

     function getInterpretation($name) {
          return $this->interpretations[$name];
     }
}

//------------

?>
