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

class FieldFormatter {

     var $style;
     var $variableName;
     var $data;
     var $override_size;

     function FieldFormatter($variableName,
                             $data,
                             $override_color = null,
                             $override_size = null) {
          $this->variableName = $variableName;
          $this->data = $data;
          $STYLIST = new Stylist();
          $this->style = $STYLIST->createTextStyle(null, $override_color);
          $this->override_size = $override_size;
     }

     function render($select_value = null, 
                     $any_option = true,
                     $noValueIndicator = null,
                     $dataType = null,
                     $lookupType = null,
                     $metaColumnIndicator = null,
                     $is_sorted = true) {
          if (is_array($this->data)) {
               if (sizeof($this->data) > 1) {
//
// create the HTML select container
//
                    $buffer = '<select '; 
                    if ($lookupType == 'LookupMulti') {
                         $buffer .= 'multiple size="5" ';
                    }
                    $buffer .= 'name="' .
                               $this->variableName .
                               '" style="' .
                               $this->style .
                               '">'; 

//
// add ANY if a default is specified
//
                    if ($lookupType != 'LookupMulti' && $any_option) {
                         if ($noValueIndicator != null) {
                              if ($select_value == '') {
                                   $buffer .= '<option value="" selected="selected">' .
                                              'Choose an Option' .
                                              '</option>';
                              } else {
                                   $buffer .= '<option value="">' .
                                              'Choose an Option' .
                                              '</option>';
                              }
                         } else {
                              $buffer .= '<option value="ANY" selected="selected">' .
                                         'Any' .
                                         '</option>';
                         }
                    } else {
                         if ($noValueIndicator != null) {
                              $buffer .= '<option value="' .
                                         $noValueIndicator .
                                         '">' .
                                         '** Not Used **' .
                                         '</option>';
                         }
                    }

//
// add MetaColumn if specified
//
                    if ($metaColumnIndicator != null) {
                         $buffer .= '<option value="' .
                                    $metaColumnIndicator .
                                    '"';
                         if ($select_value == $metaColumnIndicator) {
                              $buffer .= ' selected="selected"';
                         }
                         $buffer .= '>' .
                                    '** MetaColumn **' .
                                    '</option>';
                    }

//
// sort list
//
                    $field_array = $this->data;
                    if ($is_sorted) {
 	                asort($field_array);
                    }

//
// add other options
//
                    foreach ($field_array as $key => $val) {
                         $buffer .= '<option value="' .
                                    $val .
                                    '"';
//                         if (!$any_option && $val == $select_value)
                         if ($val == $select_value) {
                              $buffer .= ' selected="selected"';
                         }
                         $buffer .= '>' . $key . '</option>';
                    }
                    $buffer .= '</select>';
                    return $buffer;
               } else {
                    foreach ($this->data as $key => $val) {
                         return $this->singleValue($this->variableName, 
                                                   $val); 
                    }
               }
          }

          if ($noValueIndicator == null) {
               return $this->singleValue($this->variableName, $this->data);
          }

//
// if using "no value indicator", substitute it for null data
//
          if ($this->data == null) {
               return $this->singleValue($this->variableName, $noValueIndicator);
          }           
          return $this->singleValue($this->variableName, $this->data);
     }

     function singleValue($variableName,
                          $data) {
          if ($this->override_size == null) {
               $size = strlen($data);
          } else {
               $size = $this->override_size;
          }
          if ($size > 0) {
               if ($size > BUILTIN_MAX_TEXT_FIELD) {
                    $size = BUILTIN_MAX_TEXT_FIELD;
               }
               return '<input type="text" name="' .
                      $variableName .
                      '" value="' .
                      $data .
                      '" size="' .
                      $size .
                      '" style="' .
                      $this->style .
                      '"/>';
          }

          return '<input type="text" name="' .
                 $variableName .
                 '" value="' .
                 $data .
                 '" style="' .
                 $this->style .
                 '"/>';
     }

}

?>
