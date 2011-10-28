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

class SearchRequest {

     var $selectionCriteria;
     var $selectionTypes;
     var $selectionInterpretations;
     var $queryCriteria;
     var $source;
     var $retsVersion;
     var $resource;
     var $class;
     var $restricted_indicator;
     var $limit;
     var $usingNullQuery;

     function SearchRequest($retsVersion,
                            $resource,
                            $class,
                            $selections,
                            $source,
                            $restrictedIndicator = null) {
          $this->selectionCriteria = $selections;
          $this->queryCriteria = false;
          $this->source = $source;
          $this->retsVersion = $retsVersion;
          $this->resource = $resource;
          $this->class = $class;
          $this->setLimit('NONE');
          $this->usingNullQuery = false;
          $this->setRestrictedIndicator($restrictedIndicator);
          date_default_timezone_set('UTC');
     }

     function getSource() {
          return $this->source;
     }

     function getSelectClause() {
          return 'SearchType=' . $this->resource .
                 '&Class=' . $this->class .
                 '&Select=' . urlencode($this->selectionCriteria);
     }

     function getQueryClause() {
          return 'Query=' . urlencode($this->getQueryCriteria());
     }

     function getTypeClause($compact_decoded_format,
                            $standard_names,
                            $startingWith,
                            $withCount = false) {
          $restrictedIndicator = null;
          if ($this->hasRestrictedIndicator()) {
               $restrictedIndicator = '&RestrictedIndicator=' .
                                      $this->restricted_indicator;
          }

          if ($this->retsVersion == '1.0') {
               $language = 'DMQL';
          } else {
               $language = 'DMQL2';
          }

          $offset = null;
          if ($startingWith != 1) {
               $offset = '&Offset=' . $startingWith;
          }

          if (!$withCount) {
               return 'QueryType=' . $language .
                      '&Format=' . $this->getFormatType($compact_decoded_format) .
                      '&Limit=' . $this->limit .
                      $offset .
                      '&StandardNames=' . $this->getNameType($standard_names) .
                      '&Count=0' .
                      $restrictedIndicator;
          }
          return 'QueryType=' . $language .
                 '&Format=' . $this->getFormatType($compact_decoded_format) .
                 $offset .
                 '&StandardNames=' . $this->getNameType($standard_names) .
                 '&Count=2' .
                 $restrictedIndicator;
     }

     function getFormatType($compact_decoded_format) {
          if ($compact_decoded_format) {
               return 'COMPACT-DECODED';
          }
          return 'COMPACT';
     }

     function getNameType($standard_names) {
          if ($standard_names) {
               return 1;
          }
          return 0;
     }

     function getLimit() {
          return $this->limit;
     }

     function setLimit($aValue) {
          $this->limit = $aValue;
     }

     function hasRestrictedIndicator() {
          if ($this->restricted_indicator == null) {
               return false;
          }
        
          return true;
     }

     function getRestrictedIndicator() {
          return $this->restricted_indicator;
     }

     function setRestrictedIndicator($aValue) {
          $this->restricted_indicator = $aValue;
     }

     function hasSelectionCriteria() {
          if ($this->selectionCriteria == null) {
               return false;
          }
          if (strlen($this->selectionCriteria) == 0) {
               return false;
          }
        
          return true;
     }

     function getSelectionCriteria() {
          return $this->selectionCriteria;
     }

     function setSelectionTypes($types) {
          $this->selectionTypes = $types;
     }

     function getSelectionTypes() {
          return $this->selectionTypes;
     }

     function setSelectionInterpretations($interpretations) {
          $this->selectionInterpretations = $interpretations;
     }

     function getSelectionInterpretations() {
          return $this->selectionInterpretations;
     }

     function hasQueryCriteria() {
          $criteria = $this->getQueryCriteria();
          if ($criteria == null) {
               return false;
          }
          if (strlen($criteria) == 0) {
               return false;
          }
        
          return true;
     }

     function isNullQuery() {
          return $this->usingNullQuery;
     }

     function setQueryCriteria($criteria,
                               $usingNullQuery = false) {
          $this->queryCriteria = $criteria;
          $this->usingNullQuery = $usingNullQuery;
     }

     function getQueryCriteria() {
          if ($this->queryCriteria) {
               return $this->queryCriteria;
          }

//          return '()';
          return '';
     }

     function setUpdatesSinceList($fields,
                                  $fieldTypes,
                                  $fieldValue,
                                  $timeDifference = null) {
//
// determine what to keep from the existing query
//
//          if (!$this->usingNullQuery) {
//               $newQuery = $this->queryCriteria . ',';
//          } else {
//               $newQuery = null;
//          }
          $newQuery = null;

//
// check each defined field
//
          $metadata = explode(',', $fields);
          foreach ($metadata as $key => $fieldName) {
//
// check if variable is already defined 
//
               $pos = strpos($this->queryCriteria, $fieldName);
               if ($pos > -1) {
                    return;
               }

//
// append to new query
//
               $newQuery .= $this->createDateQueryElement($fieldName,
                                                          $fieldTypes[$fieldName],
                                                          $fieldValue,
                                                          $timeDifference) .
                            '|';
          }
          $newQuery = rtrim($newQuery,'|');

//
// encapsulate in parenthesis if more than two elements are present
//
          if (sizeOf($metadata) > 1) {
               $newQuery = '(' . $newQuery . ')';
          }

//
// encapsulate in parenthesis if a query was present originally 
//
          if ($this->queryCriteria == null) {
               $this->queryCriteria = '';
          }
          if ($this->queryCriteria != '') {
               $newQuery = '(' . $this->queryCriteria . ',' . $newQuery . ')';
          }

//
// replace existing query
//
          $this->queryCriteria = $newQuery;
     }

     function createDateQueryElement($fieldName,
                                     $fieldType,
                                     $fieldValue,
                                     $timeDifference = null) {
          $checkDate = null;
          switch (strToUpper($fieldType)) {
               case 'DATE':
                    $pos = strpos($fieldValue, 'T');
                    if ($pos === false) {
                    } else {
                         $checkDate = substr($fieldValue, 0, $pos);
                    }
                    break;

               case 'DATETIME':
                    $pos = strpos($fieldValue, 'T');
                    if ($pos === false) {
                    } else {
                         if ($timeDifference == 0) {
                              $checkDate = $fieldValue;
                         } else {
                              $newTime = strtotime($timeDifference . ' hour', strtotime($fieldValue));
                              $checkDate = date(DATE_W3C,$newTime);
                              $checkDate = substr($checkDate, 0, strpos($checkDate, '+'));
                         }
                    }
                    break;
          }

          if ($checkDate != null) {
               return '(' . $fieldName . '=' . $checkDate . '+)';
          }

          return null;
     }

}

//------------

?>
