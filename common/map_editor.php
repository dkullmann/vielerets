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

class MapEditor {

     var $fieldName;
     var $banner;
     var $temporaryStateText;
     var $expertLevel;
     var $keyColumnName;
     var $valueColumnName;
     var $displaySpacer = '&nbsp;&nbsp;&nbsp;';

     function MapEditor($fieldName = null) {
          $this->fieldName = $fieldName;
          $this->banner = '<!-- Map Editor -->' . CRLF;
          $this->setStateText('* Denotes Required Fields');
     }

     function getBanner() {
          return $this->banner;
     }

     function getStateText() {
          return $this->stateText;
     }

     function setStateText($aValue) {
          $this->stateText = $aValue;;
     }

     function setExpertLevel($value) {
          $this->expertLevel = $value;
     }

     function getExpertLevel() {
          return $this->expertLevel;
     }

     function setKeyColumnName($name) {
          $this->keyColumnName = $name;
     }

     function getKeyColumnName() {
          return $this->keyColumnName;
     }

     function setValueColumnName($name) {
          $this->valueColumnName = $name;
     }

     function getValueColumnName() {
          return $this->valueColumnName;
     }

     function setAliasColumn(&$source,
                             $list) {
          $source[$this->getAliasColumnName()] = $list;
     }

     function nullVariable() {
          return '$' . $this->fieldName . '=array();';
     }

     function asVariable($source) {
          if ($source == null) {
               return null;
          }

          $result = '$';
          $result .= $this->fieldName;
          $result .= '=array(';
          foreach ($source as $item => $list) {
               if (is_array($list)) {
                    $result .= '"' .  $item . 
                               '"=> array(';
                    foreach ($list as $key => $value) {
                         $result .= '"' .  $key . 
                                    '"=>"' .
                                    $value .  '",';
                    }
                    $result = substr($result, 0, strlen($result) - 1) . '),';
               } else {
                    $result .= '"' .  $item . 
                               '"=> "$list",';
               }
          }

          return substr($result, 0, strlen($result) - 1) . ');';
     }

     function bestSplit($itemCount,
                        $returnRows = true) {
          if ($itemCount < 5) {
               return $itemCount;
          }

          $split = $itemCount;
          $columns = 0;
          $rows = $itemCount;
          $loop = true;
          while ($loop) {
               $columns = round($itemCount / $split);
               $rows = round($itemCount / $columns);
               if ($columns > $rows) {
                    $loop = false;
                    ++$split; 
               } else {
                    --$split; 
               }
          }

          $columns = round($itemCount / $split);
          $rows = round($itemCount / $columns);

          if ($returnRows) {
               return $rows;
          }

          return $columns;
     }

     function render($FORMATTER,
                     $source,
                     $column_titles,
                     $required,
                     $universe,
                     $translationTable = null,
                     $metaColumnIndicator = null) {
//
// display the universe of names for experts
//
          $experts = null;
          if ($this->expertLevel) {
               $td = '<td align="center" colspan="2">';
               $experts .= '  <tr>' . CRLF .
                           '    ' . $td . CRLF .
                           $FORMATTER->formatBoldText('Available Field Names') .
                           '    </td>' . CRLF .
                           '  </tr>' . CRLF .
                           '  <tr>' . CRLF .
                           '    ' . $td . CRLF .
                           '<table width="100%" cellspacing="0" ' .
                           'cellpadding="6" cellspacing="0" border="1">' . CRLF;
//
// generate best rectangle table 
//
               $items = null;
               $temp = $universe;
               sort($temp);
               foreach ($temp as $key => $val) {
                    $items[] = $FORMATTER->formatText($val);
               }
               $item_count = sizeof($items);
               $rows = $this->bestSplit($item_count);

               $body = null;
               if ($rows == $item_count) {
                    foreach ($items as $key => $value) {
                         $body .= '<tr><td>' . $value . '</td></tr>';
                    }
               } else {
                    $columns = $this->bestSplit($item_count,
                                                false);
                    $temp = $rows * $columns;
                    if ($temp < $item_count) {
                         ++$rows;
                    }
                    for ($y = 0; $y < $rows; ++$y) {
                         $body .= '<tr>';
                         for ($x = 0; $x < $columns; ++$x) {
                              $offset = $y + (int)($rows * $x);
                              $body .= '<td>';
                              if (array_key_exists($offset, $items)) {
                                   $body .= $items[$offset];
                              }
                              $body .= '</td>';
                         }
                         $body .= '</tr>';
                    }
               }

               $experts .= $body;
               $experts .= '</table>' . CRLF .
                           '    </td>' . CRLF .
                           '  </tr>' . CRLF;
          }
//
// column headings
//
          $headings = null;
          $headings .= '  <tr>' . CRLF;
          foreach ($source as $item => $list) {
               $headings .= '    <td align="center" bgcolor="' .
                            HEADING_BACKGROUND_COLOR .
                            '">' . CRLF .
                            $FORMATTER->formatBoldText($column_titles[$item]) .
                            CRLF . '    </td>' . CRLF;
          }
          $headings .= '  </tr>' . CRLF;

//
// right column drop-down selections
//
          $dList = null;
          if (!$this->expertLevel) {
               foreach ($universe as $key => $val) {
                    if ($translationTable == null) {
                         $dList[$val] = $val;
                    } else {
                         if (array_key_exists($val, $translationTable)) {
                              $dList[$translationTable[$val] . ' (' .$val . ')'] = $val;
                         } else {
                              $dList[$val] = $val;
                         }
                    }
               }
          }

//
// prevent duplicates
//
          $max_rows = sizeof($source['TARGET']);
          $taken = null;
          for ($row = 0;$row < $max_rows; ++$row) {
               $check = $source['SOURCE'];
               if ($check[$row] != NO_VALUE_INDICATOR && $check[$row] != $metaColumnIndicator) {
                    $taken[$check[$row]] = true;
               }
          }

//
// rows
//
          $buffer = null;
          $isShaded = false;
          for ($row = 0;$row < $max_rows; ++$row) { 
               $buffer .= '  <tr>' . CRLF;

               $isLeft = true;
               foreach ($source as $item => $list) {
//
// required fields
//
                    $isRequired = false;
                    if ($required[$item] != null) {
                         if (array_key_exists($list[$row], $required[$item])) {
                              $isRequired = true;
                         }
                    }

//
// repositioning buttons
//
                    if ($isLeft) {
                         $leftList = $list;
                    }

//
// data cell
//
//$junk = $this->formatDataCell($FORMATTER,
//                              $list,
//                              $leftList,
//                              $row,
//                             $isLeft,
//                              $universe,
//                              $dList,
//                              $isRequired,
//                              $translationTable,
//                              $metaColumnIndicator);
//echo "<XMP>$junk</XMP>";
                    $buffer .= $this->beginCell($FORMATTER, $isShaded) .
                               $this->formatDataCell($FORMATTER,
                                                     $list,
                                                     $leftList,
                                                     $row,
                                                     $isLeft,
                                                     $universe,
                                                     $dList,
                                                     $isRequired,
                                                     $taken,
                                                     $translationTable,
                                                     $metaColumnIndicator) .
                               $this->endCell();
                    $isLeft = false;
               }
               if ($isShaded) {
                    $isShaded = false;
               } else {
                    $isShaded = true;
               }
               $buffer .= '  </tr>' . CRLF;
          }

//
// frame 
//
          return $FORMATTER->renderStartPanel(null, '0', 'white') .
                 $FORMATTER->renderStartInnerFrame() .
                 $FORMATTER->renderStartInnerFrameItem(null, 'left') .
                 $this->getBanner() .
                 '<table width="100%" cellspacing="0" ' .
                 'cellpadding="6" cellspacing="0" border="' .
                 DATA_BORDER .
                 '">' . CRLF .
                 $experts .
                 $headings .
                 $buffer .
                 '</table>' . CRLF .
                 $this->getBanner() .
                 $FORMATTER->renderEndInnerFrameItem() .
                 $FORMATTER->renderEndInnerFrame() .
                 $FORMATTER->renderEndPanel($this->getStateText());
     }

     function beginCell($FORMATTER,
                        $isShaded) {
          if ($isShaded) {
               $aColor = DATA_BACKGROUND_COLOR;
          } else {
               $aColor = 'white';
          }

          return '    <td align="left" bgcolor="' .
                 $aColor .
                 '">' . CRLF;
     }

     function endCell() {
          return '</td>' . CRLF;
     }

     function formatDataCell($FORMATTER,
                             $primary_list,
                             $secondary_list,
                             $row,
                             $isLeft,
                             $universe,
                             $dList,
                             $isRequired,
                             $taken,
                             $translationTable = null,
                             $metaColumnIndicator = null) {
          $buffer = null;

//
// pull the value for the position if it is available
//
          $value = null;
          if (sizeof($primary_list) > $row) {
               $value = $primary_list[$row];
          }

//
// denote required field
//
          if ($isRequired) {
               $buffer .= $FORMATTER->formatBoldText('*&nbsp;&nbsp;', HIGHLIGHT_FONT_COLOR);
          } else {
               $buffer .= $FORMATTER->formatBoldText($this->displaySpacer);
          }

//
// display the field
//
          if ($isLeft) {
               $buffer .= $FORMATTER->formatBoldText($value);
          } else {
//
// check for a value that has already been set
//
               $selected = null;
               foreach ($universe as $key => $val) {
                    if ($val == $value) {
                         $selected = $val;
                    }
               }

//
// check for a value from the left column to auto-populate 
//
               if ($selected == null) {
                    foreach ($universe as $key => $val) {
                         if (!array_key_exists($val, $taken)) {
                              $check = $secondary_list[$row];
                              if ($val == $check) {
                                   $selected = $val;
                              }
                         }
                    }
               }

               if ($metaColumnIndicator != null) {
                    if ($value == $metaColumnIndicator) {
                         $selected = $value;
                    }
               }

//
// render
//
               if ($this->expertLevel) {
                    $FIELD = new FieldFormatter('SOURCE[' . $row . ']', $selected);
                    $buffer .= $FIELD->render($selected, 
                                              false, 
                                              $FORMATTER->getNoValueIndicator(),
                                              null,
                                              null,
                                              $metaColumnIndicator);
               } else {
                    $FIELD = new FieldFormatter('SOURCE[' . $row . ']', $dList);
                    $buffer .= $FIELD->render($selected, 
                                              false, 
                                              $FORMATTER->getNoValueIndicator(),
                                              null,
                                              null,
                                              $metaColumnIndicator);
               }
          }

          return $buffer . CRLF;
     }

}

//
//------------

?>
