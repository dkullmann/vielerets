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

class AjaxFormatter {

    var $STYLIST;
    var $noValueIndicator = NO_VALUE_INDICATOR;
    var $metaColumnIndicator = META_COLUMN_INDICATOR;

    function AjaxFormatter() {
        $this->STYLIST = new Stylist();
    }

    function getNoValueIndicator() {
        return $this->noValueIndicator;
    }

    function getMetaColumnIndicator() {
        return $this->metaColumnIndicator;
    }

    function renderStartFrame($override_border = null,
                              $override_color = null,
                              $override_inset = null,
                              $override_width = null) {
//
// override if appropriate
//
        if ($override_border == null) {
            $border = FRAME_BORDER;
        } else {
            $border = $override_border;
        }

        if (is_string($override_color)) {
            $color = $override_color;
        } else {
            $color = FRAME_BACKGROUND_COLOR;
        }

        if ($override_inset == null) {
            $inset = FRAME_INSET;
        } else {
            $inset = $override_inset;
        }

        if ($override_width == null) {
            $width = null;
        } else {
            $width = ' width="' . $override_width . '"';
        }

//
// render
//
        return '<table align="center"' . $width . ' cellspacing="0" cellpadding="0" ' .
               'border="' . $border . '" bgcolor="' . $color . '">' .
               '<tr align="center">' .
               '<td>' .
               '<table align="center" cellspacing="' . $inset .
               '" cellpadding="0" border="0" width="100%">';
    }

    function renderStartFrameItem() {
        return '<tr align="center"><td>';
    }

    function renderEndFrameItem() {
        return '</td></tr>';
    }

    function renderEndFrame() {
        return '</table></td></tr></table>';
    }

    function formatBoldList($list) {
//
// if there is only one item in the list return it
//
        if (sizeof($list) == 1) {
            return $this->STYLIST->formatBoldText($list[0]);
        }

        $insert = null;
        foreach ($list as $key => $value) {
            $insert .= '<li>' . $this->STYLIST->formatBoldText($value) . '</li>'; 
        }

        return '<table border="0" cellpadding="3" cellspacing="0">' .
               '<tr><td><ul>' .  $insert .  '<ul></td></tr>' .
               '</table>';
    }

    function formatPseudoLink($message,
                              $override_color = null,
                              $override_points = null,
                              $override_face = null,
                              $with_underline = true) {
        $color = 'purple';
        if (is_string($override_color)) {
            $color = $override_color;
        }

        return '<font style="' .
               $this->STYLIST->createPseudoLinkStyle($override_face, 
                                                     $override_points,
                                                     $color,
                                                     $with_underline) .
               '">' .
               $message .
               '</font>';
    }

    function determineSelection($field_array,
                                $value) {
        if ($field_array != null) {
            foreach ($field_array as $key => $val) {
                if ($key == $value) {
                    return $value;
                    break;
                }
            }

//
// pick the first value
//
            foreach ($field_array as $key => $val) {
                return $key;
            }
        }

        return null;
    }

    function formatRadioField($title,
                              $variable,
                              $value, 
                              $field_array,
                              $override_color = null,
                              $withFullLabels = false,
                              $noValueFullLabel = false) {
        $buffer = null;
        $selection = $this->determineSelection($field_array, $value);
        foreach ($field_array as $key => $val) {
            $checked = null;
            if ($key == $selection) {
                $checked = ' checked="checked"';
            }

            if ($withFullLabels) {
                if ($noValueFullLabel) {
                    $label = $this->STYLIST->formatText($val);
                } else {
                    $label = $this->STYLIST->formatText($val) . ' (' . $this->STYLIST->formatText($key) . ')';
                }
            } else {
                $label =  $this->STYLIST->formatText($key);
            }

            $buffer .= '<tr><td><input type="radio" name="' .
                       $variable .
                       '" value="' . $key . 
                       '" style="' . $this->STYLIST->createTextStyle() . '" ' .
                       $checked .
                       '>' . $label . '</input></td></tr>';
        }

        return $this->STYLIST->formatBoldText($title, $override_color) .
               '</td><td><table border="0" cellpadding="0" cellspacing="0">' . $buffer . '</table>';
    }

    function formatButtonField($title,
                               $button_action,
                               $button_label,
                               $override_color = null) {
        $buffer = '<input type="button" name="' . $button_action . '" value="' . $button_label . '"/>';
        return $this->STYLIST->formatBoldText($title, $override_color) .
               '</td><td><table border="0" cellpadding="0" cellspacing="0">' . $buffer . '</table>';
    }

    function formatBinaryField($title,
                               $variable,
                               $value,
                               $override_color = null) {
        if (FORMAT_BINARY_AS_CHECKBOX) {
            return $this->formatCheckboxField($title, $value, $variable, $override_color);
        }

        $binary_values[] = 'true';
        $binary_values[] = 'false';

        return $this->formatRadioField($title, $variable, $value, $binary_values, $override_color);
    }
    
    function createLink($url, 
                        $visual) {
         if ($url == null) {
             $buffer = 'onclick="history.go(-1)"';
         } else {
             $buffer = 'href="' . $url . '"';
         }

         return '<a ' . $buffer . '>' . $visual . '</a>';
    }

    function formatSeparator($text = null) {
        return 'SEPARATOR//' .  $text;
    }

    function is_separator($item, 
                          $override_color = null) {
         $pos = strpos($item,'SEPARATOR//');
         if ($pos === false) {
             return false;
         }

         if (strlen($item) == 11) {
//               return '<hr/>';
             return '<br/>';
         } else {
             $spacer = '';
             for ($i = 0; $i < 10; ++$i) {
                 $spacer .= '&#8211;';
             }
         }

         $text = substr($item, $pos + 11, strlen($item));

         $statusColor = 'red';
         if ($override_color != null) {
             $statusColor = $override_color;
         }

         return '<table width="100%" cellspacing="5" cellpadding="0"><tr>' .
                '<td>&#160;</td></tr><tr>' .
                '<td></td></tr><tr>' .
                '<td align="left">' . $spacer . '</td>' .
                '<td align="center">' . 
                $this->STYLIST->formatItalicText($text, $statusColor) .
                '</td>' .
                '<td align="right">' . $spacer . '</td></tr></table>';
    }

    function formatPage($destination,
                        $items,
                        $override_submit = null) {
        $response = '<form method="post" autoComplete="off" action="' . $destination . '">' .
                    '<table align="center" cellspacing="' .
                    DATA_SPACING .
                    '" cellpadding="' .
                    DATA_PADDING .
                    '" border="' .
                    DATA_BORDER .
                    '" bgcolor="' .
                    DATA_BACKGROUND_COLOR .
                    '">';

//
// determine total items that are visible 
//
        $item_count = sizeof($items);
        $visible_count = 0;
        $isVisible = Array();
        for ($i = 0; $i < $item_count; ++$i ) {
            if (strpos($items[$i],'hidden')) {
                $isVisible[] = false;
            } else {
                ++$visible_count;
                $isVisible[] = true;
            }
        }

//
// content table 
//
        $isShaded = false;
        for ($i = 0; $i < $item_count; ++$i ) {
            $isSeparator = $this->is_separator($items[$i]);
            if ($isVisible[$i]) {
                if ($isShaded) {
                    $aColor = null;
                    $isShaded = false;
                } else {
                    $aColor = ' bgcolor="white"';
                    if ($visible_count > 2) {
                        $isShaded = true;
                    }
                }
                if (!$isSeparator) {
                    $response .= '<tr align="left" valign="top"' . $aColor . '><td width="' . FIELD_DESCRIPTION_WIDTH . '">';
                } else {
                    $response .= '<tr' . $aColor . '><td colspan="2">';
                }
            }
            if (!$isSeparator) {
                $response .= $items[$i];
            } else {
                $response .= $isSeparator;
            }
            if ($isVisible[$i]) {
                $response .= '</td></tr>';
            }
        }

//
// create a fake button if required
//
        if ($override_submit != null) {
             $submitButton = $override_submit;
        }
        else {
             $submitButton = $this->formatPageSubmit();
        }

        return $response . '</table><table width="100%" border="0" cellspacing="0" cellpadding="' . NAVBAR_INSET . '"><tr><td align="center">' . 
               '<div id="' . AJAX_NAVBAR . '">' .
               '<table border="0" cellspacing="0" cellpadding="' . NAVBAR_SPACING . '"><tr>' .
               $submitButton .
               '<td align="center"><input type="submit" name="CANCEL" value="Quit"/></td>' .
               '</tr></table>' .
               '</div>' .
               '</td></tr></table></form>';
    }

    function formatPageSubmit($override_text = null,
                              $override_action = null) {
        if ($override_text != null) {
             return '<td align="center"><input type="button" name="' . $override_action . '" value="' . $override_text . '"/></td>';
        }

        return '<td align="center"><input type="submit" name="SUBMIT" value="Apply"/></td>';
    }

    function formatDisplayField($visible_name,
                                $value,
                                $override_color = null) {
        return $this->STYLIST->formatBoldText($visible_name) .
               $this->STYLIST->formatColumnSeparation() .
               $this->STYLIST->formatText($value, $override_color);
    }

    function formatTextField($visible_name,
                                $value,
                                $override_color = null) {
        return $this->STYLIST->formatBoldText($visible_name, $override_color) .
               $this->STYLIST->formatColumnSeparation() .
               $this->STYLIST->formatText($value, $override_color);
    }

    function formatHiddenField($variable_name,
                               $value) {
        return '<input type="hidden" name="' . $variable_name . '" value="' . $value . '"/>'; 
    }

    function formatPathField($visible_name,
                             $variable_name,
                             $value,
                             $override_size = null,
                             $read_only = false,
                             $max_columns = 3,
                             $override_color = null) {
        $dirs = $this->directoryList($value);

        if ($dirs == null) {
            $statusColor = $override_color;
            if (!is_writable($value) && !$read_only) {
                $statusColor = 'red';
            }
            return $this->formatSingleEntryField($visible_name,
                                                 $variable_name,
                                                 $value,
                                                 $override_size,
                                                 $statusColor);
        }

//
// identify parent and home directories 
//
        $offset = 0;
        $relativeDirs = null;
        $realDirs = null;
        if ($value != '/') {
            $parentDir = $dirs[0];
            $homeDir = $dirs[1];
            $offset = 1;
            $realDirs[] = $parentDir;
            $relativeDirs[] = '.. (up one level)';
        } else {
            $parentDir = $dirs[0];
            $homeDir = $parentDir;
        }
          
//
// convert to relative
//
        $showRelative = true;
        foreach ($dirs as $key => $val) {
            if ($key > $offset) {
                $realDirs[] = $val;
                $relativeDirs[] = substr($val, strlen($homeDir) + $offset, strlen($val));
            }
        }

//
// create entry field for path name 
//
        if ($override_size == null) {
            $size = strlen($value);
        } else {
            $size = $override_size;
        }
        if ($size > BUILTIN_MAX_TEXT_FIELD) {
            $size = BUILTIN_MAX_TEXT_FIELD;
        }

        $statusColor = $override_color;
        if (!is_writable($homeDir) && !$read_only) {
            $statusColor = 'red';
        }
        $universe = '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white"><tr><td>' . 
                    '<input type="text" name="' . $variable_name . '" value="' . $homeDir . '" size="' . $size . '" style="' . $this->STYLIST->createTextStyle(null, $statusColor) . '"/>' .
                    '</td></tr><tr><td><table cellpadding="2" cellspacing="0" border="0">';

//
// display relative
//
        $basis = $realDirs;
        if( $showRelative) {
            $basis = $relativeDirs;
        }

        $itemCount = sizeof($basis);
//foreach ($basis as $key => $val) {
//}
        $rows = $this->bestSplit($itemCount);

        if ($rows == $itemCount) {
            foreach ($basis as $key => $val) {
                $elementColor = $override_color;
                if (!is_writable($realDirs[$key]) && !$read_only) {
                    $elementColor = 'red';
                }
                $universe .= '<tr><td>' .
                             $this->formatPathElement($variable_name,
                                                      $realDirs[$key],
                                                      $val,
                                                      $elementColor,
                                                      DATA_POINT_SIZE - 1) .
                             '</td></tr>';
            }
        } else {
            $columns = $this->bestSplit($itemCount, false);
            if ($columns > $max_columns) {
                $columns = $max_columns;
                $rows = round($itemCount / $columns);
            }
            $temp = $rows * $columns;
            if ($temp < $itemCount) {
                ++$rows;
                ++$columns;
            }
            for ($y = 0; $y < $rows; ++$y) {
                $universe .= '<tr>';
                for ($x = 0; $x < $columns; ++$x) {
                    $offset = $y + (int)($rows * $x);
                    $universe .= '<td>';
                    if (array_key_exists($offset, $basis)) {
                        $elementColor = $override_color;
                        if (!is_writable($realDirs[$offset]) && !$read_only) {
                            $elementColor = 'red';
                        }
                        $universe .= $this->formatPathElement($variable_name,
                                                              $realDirs[$offset],
                                                              $basis[$offset],
                                                              $elementColor,
                                                              DATA_POINT_SIZE - 1);
                    }
                    $universe .= '</td>';
                }
                $universe .= '</tr>';
            }
        }

        $universe .= '</table></td></tr></table>';

        return $this->STYLIST->formatBoldText($visible_name, $statusColor) .
               $this->STYLIST->formatColumnSeparation() .
               $universe;
    }

    function formatPathElement($variableName,
                               $realValue,
                               $displayValue,
                               $override_color,
                               $override_size = null) {
        return '<input type="radio" name="' . $variableName . '" value="' . $realValue . 
               '" style="' . $this->STYLIST->createTextStyle($override_size) . '">' .
               $this->STYLIST->formatText($displayValue, $override_color, $override_size) .
               '</input>';
    }

    function formatSingleEntryField($visible_name,
                                    $variable_name,
                                    $value,
                                    $override_size = null,
                                    $override_color = null,
                                    $highlightText = false) {
//
// determine the field size
//
        if ($override_size == null) {
            $size = strlen($value);
        } else {
            $size = $override_size;
        }
        if ($size > BUILTIN_MAX_TEXT_FIELD) {
            $size = BUILTIN_MAX_TEXT_FIELD;
        }

        $statusColor = $override_color;
        if ($highlightText) {
            $statusColor = 'red';
        }

        return $this->STYLIST->formatBoldText($visible_name, $statusColor) .
               $this->STYLIST->formatColumnSeparation() .
               '<input type="text" name="' .
               $variable_name .
               '" value="' .
               $value .
               '" size="' .
               $size .
               '" style="' .
               $this->STYLIST->createTextStyle(null, $override_color) .
               '"/>';
    }

    function formatSingleSelectField($visible_name,
                                     $variable_name,
                                     $value,
                                     $field_array = null,
                                     $override_color = null,
                                     $max_columns = 4) {
         if ($field_array == null) {
             return $this->formatSingleEntryField($visible_name,
                                                  $variable_name,
                                                  $value,
                                                  null,
                                                  $override_color);
         }
         if (!is_array($field_array)) {
             return $this->formatSingleEntryField($visible_name,
                                                  $variable_name,
                                                  $value,
                                                  null,
                                                  $override_color);
         }
         if (sizeof($field_array) == 1) {
             foreach ($field_array as $key => $val) {
                 return $this->formatSingleEntryField($visible_name,
                                                      $variable_name,
                                                      $val,
                                                      null,
                                                      $override_color);
             }
         }

//
// split into separate name and value arrays
//
         $realValue = null;
         $displayValue = null;
         foreach ($field_array as $key => $val) {
             $realValue[] = $val;
             $displayValue[] = $key;
         }

//
// display options 
//
         $universe = '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white"><tr><td><table>';
         $checkValue = explode(',', $value);
         $displayChecked = Array();
         foreach ($field_array as $key => $val) {
             foreach ($checkValue as $key2 => $val2) {
                 if ($val == $val2) {
                     $displayChecked[$val] = true;
                 }
             }
         }

         $itemCount = sizeof($field_array);
         $rows = $this->bestSplit($itemCount);

         if ($rows == $itemCount) {
             foreach ($realValue as $key => $val) {
                 $checked = false;
                 if (array_key_exists($val, $displayChecked)) {
                     $checked = true;
                 }
                 $universe .= '<tr><td>' .
                              $this->formatChecklistElement($variable_name,
                                                            $val,
                                                            $displayValue[$key],
                                                            $checked,
                                                            $override_color,
                                                            DATA_POINT_SIZE - 1) .
                              '</td></tr>';
             }
         } else {
             $columns = $this->bestSplit($itemCount, false);
             if ($columns > $max_columns) {
                 $columns = $max_columns;
                 $rows = round($itemCount / $columns);
             }
             $temp = $rows * $columns;
             if ($temp < $itemCount) {
                 ++$rows;
             }
             for ($y = 0; $y < $rows; ++$y) {
                 $universe .= '<tr>';
                 for ($x = 0; $x < $columns; ++$x) {
                     $offset = $y + (int)($rows * $x);
                     $universe .= '<td>';
                     if (array_key_exists($offset, $realValue)) {
                         $checked = false;
                         if (array_key_exists($realValue[$offset], $displayChecked)) {
                             $checked = true;
                         }
                         $universe .= $this->formatChecklistElement($variable_name,
                                                                    $realValue[$offset],
                                                                    $displayValue[$offset],
                                                                    $checked,
                                                                    $override_color,
                                                                    DATA_POINT_SIZE - 1);
                     }
                     $universe .= '</td>';
                 }
                 $universe .= '</tr>';
             }
         }

         return $this->STYLIST->formatBoldText($visible_name, $override_color) .
                $this->STYLIST->formatColumnSeparation() .  
                $universe . '</table></td></tr></table>';
    }

    function formatChecklistElement($variableName,
                                    $realValue,
                                    $displayValue,
                                    $is_checked,
                                    $override_color,
                                    $override_size = null) {
        $checked = '';
        if ($is_checked) {
            $checked = ' checked="checked"';
        }
        return '<input type="radio" name="' . $variableName .'"' .  '" value="' .  $realValue .
               '" style="' . $this->STYLIST->createTextStyle($override_size) . '"' . $checked . '>' .
               $this->STYLIST->formatText($displayValue, $override_color, $override_size) .
               '</input>';
    }

    function formatTemplateField($visible_name,
                                 $leftTitle,
                                 $rightTitle,
                                 $metaIndex,
                                 $variable_name,
                                 $options,
                                 $override_color = null) {
//
// list of options
//
	$itemCount = sizeof($options);
	$rows = $this->bestSplit($itemCount);
	$columns = $this->bestSplit($itemCount,false);
	$universe = '<tr><td>';
	$universe .= '<table border="0" cellpadding="5" cellspacing="0">';
	for ($y = 0; $y < $rows; ++$y) {
		$universe .= '<tr>';
		for ($x = 0; $x < $columns; ++$x) {
			$offset = $y + (int)($rows * $x);
			$universe .= '<td>';
			if (array_key_exists($offset, $options)) {
				$universe .= $this->STYLIST->formatText($options[$offset], null, 10);
			}
			$universe .= '</td>';
		}
		$universe .= '</tr>';
	}
	$universe .= '</table>';
	$universe .= '</td></tr>';
 
//
// createmap 
//
	$body = '<tr><td>';
	$body .= '<table border="0" cellpadding="5" cellspacing="0">';
	$body .= '<tr align="center"><td>' .
		$this->STYLIST->formatBoldText($leftTitle, null, 10) .
		'</td><td>' .
		$this->STYLIST->formatBoldText($rightTitle, null, 10) .
		'</td></tr>';

	foreach ($metaIndex as $key => $value) {

		$body .= '<tr>' .
			'<td>' .
			$this->STYLIST->formatText($key, null, 10) .
			'</td>' .
			'<td>' .
                        '<input type="text" name="' .
	                'SOA_MAP__' . $key . '__' . $variable_name .
                        '" value="' .
			$value .
                        '" size="' .
                        '32' .
                        '" style="' .
                        $this->STYLIST->createTextStyle(10, $override_color) .
                        '"/>' .
			'</td>' .
			'</tr>';
	}
	$body .= '</table>';
	$body .= '</td></tr>';

	return $this->STYLIST->formatBoldText($visible_name, $override_color) .
		$this->STYLIST->formatColumnSeparation() .
	        '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white">' .
		$universe . 
		$body . 
                '</table>';
    }

    function formatSelectElement($variableName,
                                 $fieldTitle,
                                 $currentValue,
                                 $workOptions,
                                 $override_color = null) {

        $selection = $workOptions[$currentValue];

//
// create dropdown
//
	$universe = '<table border="0" cellpadding="5" cellspacing="0">' .
	            '<tr align="center">' .
		    '<td>' .
		    $this->STYLIST->formatBoldText('Value of [' . $fieldTitle . ']', null, 12) .
		    '</td></tr>';

	foreach ($workOptions as $key => $value) {
                $aColor = $override_color;
                $checked = null;
               	if ($value == $selection) {
                        $aColor = 'red';
                        $checked = ' checked="checked"';
		}
		$display = '<input type="radio" name="' . $variableName . '" value="' . $value . 
			'" style="' . $this->STYLIST->createTextStyle() . '"' . $checked . '>' .
			$this->STYLIST->formatText($value, $aColor, 10) .
			'</input>';
		$universe .= '<tr>' .
			     '<td>' . $display . '</td>' .
			     '</tr>';
	}

	return $universe . '</table>';
    }

    function formatMapElement($leftTitle,
                              $leftMap,
                              $rightTitle,
                              $rightMap,
                              $workOptions,
                              $selection,
                              $notational,
                              $notation,
                              $noted_data_required,
                              $override_color = null) {

	$universe = '<table border="0" cellpadding="5" cellspacing="0">';

//
// create heading 
//
	$firstColumn = '<td>';
	if ($notational != null) {
		$firstColumn = '<td colspan="2">';
	}

//
// set heading color to red if data is incomplete 
//
        $statusColor = $override_color;
        if ($noted_data_required) {
             $missingData = false;
             foreach ($notational as $key => $value) {
                  if ($rightMap[$key] == null) {
                       $missingData = true;
                  } else {
                       if ($rightMap[$key] == NO_VALUE_INDICATOR) {
                            $missingData = true;
                       }
                  }
             }
             if ($missingData) {
                  $statusColor = 'red';
             }
	}

	$universe .= '<tr align="center">' .
		$firstColumn .
		$this->STYLIST->formatBoldText($leftTitle, $statusColor) .
		'</td><td>' .
		$this->STYLIST->formatBoldText($rightTitle, $statusColor) .
		'</td></tr>';

//
// create the body
//
	$exceptionsFound = false;
	foreach ($leftMap as $key => $value) {
		$button_action = 'SOA_BUTTON__' . $key;
		$button_value = $workOptions[$rightMap[$key]];
		$aColor = $override_color;
		if ($selection != null && $selection == $key) {
			$aColor = 'red';
			$buffer ='<select name="' .
		                'SOA_MAP__' . $key . '__' . $button_action .
                	        '" style="' .
				$this->STYLIST->createTextStyle(12, $aColor) .
				'" size="1">';
			foreach ($workOptions as $key2 => $value2) {
				$buffer .= '<option value="' . $key2 . '"';
	                 	if (array_key_exists($key, $rightMap)) {
					if ($rightMap[$key] == $key2) {
						$buffer .= ' selected';
					}
				}
        	                $buffer .= '>' . $value2 . '</option>';
			}
			$buffer .= '</select>';
		} else {
			$buffer = '<input type="button" name="' . $button_action . 
        	                  '" value="' . $button_value . '" style="color:' . $aColor . '"/>';
		}

		$note = null;
		if ($notational != null) {
			$mark = null;
                 	if (array_key_exists($key, $notational)) {
				$mark = $this->STYLIST->formatText('*', 'red', 10);
				$exceptionsFound = true;
			}
			$note = '<td>' . $mark . '</td>';
		}

		$universe .= '<tr>' .
			$note.
			'<td>' .
			$this->STYLIST->formatText($value, $aColor, 10) .
			'</td><td>' .
			$buffer .
			'</td>' .
			'</tr>';
	}

//
// footer
//
	$note = null;
	if ($exceptionsFound) {
		$note = '<tr><td colspan="3">' . 
			$this->STYLIST->formatText('* - ' . $notation, 'red', 10) . 
			'</td></tr>';
	}

	return $universe .
		$note . 
		'</table>';
    }

    function formatMapField($visible_name,
                            $leftTitle,
                            $leftMap,
                            $rightTitle,
                            $rightMap,
                            $options,
                            $variable_name,
                            $notational = null,
                            $notation = null,
                            $metaColumn_option = false,
                            $override_color = null,
                            $any_option = false,
                            $noted_data_required = false) {

	$generateNotation = true;
	if ($notational == null) {
		$generateNotation = false;
	}

//
// create a list of options
//
	asort($options);
	$workOptions = null;
	if ($any_option) {
		$workOptions[''] = 'Choose an Option';
	} else {
		$workOptions[$this->noValueIndicator] = '** Not Used **';
		if ($metaColumn_option) {
			$workOptions[$this->metaColumnIndicator] = '** MetaColumn **';
		}
	}
	foreach ($options as $key => $value) {
		$workOptions[$key] = $value;
	}

//
// create dropdown
//
	$universe = '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white"><tr><td>';
	$universe .= '<table border="0" cellpadding="5" cellspacing="0">';
	if ($generateNotation) {
		$firstColumn = '<td colspan="2">';
	} else {
		$firstColumn = '<td>';
	}
	$universe .= '<tr align="center">' .
		$firstColumn .
		$this->STYLIST->formatBoldText($leftTitle, null, 10) .
		'</td><td>' .
		$this->STYLIST->formatBoldText($rightTitle, null, 10) .
		'</td></tr>';

	$exceptionsFound = false;
	foreach ($leftMap as $key => $value) {
		$buffer ='<select name="' .
//			$variable_name . '[' . $key . ']' .
	                'SOA_MAP__' . $key . '__' . $variable_name .
                        '" style="' .
			$this->STYLIST->createTextStyle(10, $override_color) .
			'" size="1">';
		$optionSet = '';
		foreach ($workOptions as $key2 => $value2) {
			$optionSet .= '<option value="' . $key2 . '"';
                 	if (array_key_exists($key, $rightMap)) {
				if ($rightMap[$key] == $key2) {
					$optionSet .= ' selected';
				}
			}
                        $optionSet .= '>' . $value2 . '</option>';
		}
		$buffer .= $optionSet;

		$buffer .= '</select>';

		$note = null;
		if ($generateNotation) {
			$mark = null;
                 	if (array_key_exists($key, $notational)) {
				$mark = $this->STYLIST->formatText('*', 'red', 10);
				$exceptionsFound = true;
			}
			$note = '<td>' . $mark . '</td>';
		}

		$universe .= '<tr>' .
			$note.
			'<td>' .
			$this->STYLIST->formatText($value, null, 10) .
			'</td><td>' .
			$buffer .
			'</td>' .
			'</tr>';
	}
	$note = null;
	if ($exceptionsFound) {
		$note = $this->STYLIST->formatText('* - ' . $notation, 'red', 10);
	}
	$universe .= '<tr>' .
		'<td colspan="3">' .
		$note .
		'</td>' .
		'</tr>';

//
// make sure noted data is required 
//
        $missingData = false;
        if ($noted_data_required) {
             foreach ($notational as $key => $value) {
                  if ($rightMap[$key] == null) {
                       $missingData = true;
                  } else {
                       if ($rightMap[$key] == NO_VALUE_INDICATOR) {
                            $missingData = true;
                       }
                  }
             }
        }

//
// set text color to red if data is in error 
//
        $statusColor = $override_color;
        if ($missingData) {
             $statusColor = 'red';
        }

	return $this->STYLIST->formatBoldText($visible_name, $statusColor) .
		$this->STYLIST->formatColumnSeparation() .
		$universe . '</table></td></tr></table>';
    }

    function formatMultiSelectField($visible_name,
                                    $variable_name,
                                    $value,
                                    $field_array = null,
                                    $override_color = null,
                                    $notational = null,
                                    $notation = null,
                                    $null_any = false,
                                    $is_sorted = true,
                                    $max_columns = 4,
                                    $allow_filtering = false) {
         if ($field_array == null) {
             return $this->formatSingleEntryField($visible_name,
                                                  $variable_name,
                                                  $value,
                                                  null,
                                                  $override_color);
         }
         if (!is_array($field_array)) {
             return $this->formatDisplayField($visible_name,
                                              $value,
                                              $override_color = null);
//             return $this->formatSingleEntryField($visible_name,
//                                                  $variable_name,
//                                                  $value,
//                                                  null,
//                                                  $override_color);
         }
         if (sizeof($field_array) == 1) {
             foreach ($field_array as $key => $val) {
                return $this->formatDisplayField($visible_name,
                                                 $val,
                                                 $override_color = null);
//                 return $this->formatSingleEntryField($visible_name,
//                                                      $variable_name,
//                                                      $val,
//                                                      null,
//                                                      $override_color);
             }
         }

//
// sort list
//
         if ($is_sorted) {
              asort($field_array);
         }

//
// split into separate name and value arrays
//
         $realValue = null;
         $displayValue = null;
         foreach ($field_array as $key => $val) {
             $realValue[] = $key;
             $displayValue[] = $val;
         }

//
// set color to red is null is not permitted, before looking for selects 
//
        $statusColor = $override_color;
        if (!$null_any) {
             $statusColor = 'red';
        }

//
// display options 
//
         $checkValue = explode(',', $value);
         $displayChecked = Array();
         $checkedCount = 0;
         foreach ($field_array as $key => $val) {
             foreach ($checkValue as $key2 => $val2) {
                 if ($key == $val2) {
                     $displayChecked[$key] = true;
                     ++$checkedCount;
                     $statusColor = $override_color;
                 }
             }
         }

         $itemCount = sizeof($field_array);
         $rows = $this->bestSplit($itemCount);
         $columns = 1;
         $notations = 0;
         $universe = '<table border="1" cellpadding="0" cellspacing="0" bgcolor="white"><tr><td><table>';
         if ($rows == $itemCount) {
             $universe .= $this->formatMultiHeader($variable_name, 
                                                   $columns, 
                                                   $itemCount, 
                                                   $checkedCount,
                                                   $allow_filtering);
             foreach ($realValue as $key => $val) {
                 $checked = false;
                 if (array_key_exists($val, $displayChecked)) {
                     $checked = true;
                 }
	         if ($notational != null) {
		      if (array_key_exists($val, $notational)) {
                           ++$notations;
                      }
                 } 
                 $universe .= '<tr><td>' .
                              $this->formatMultiElement($variable_name,
                                                        $val,
                                                        $displayValue[$key],
                                                        $checked,
                                                        $override_color,
                                                        DATA_POINT_SIZE - 1,
                                                        $notational) .
                              '</td></tr>';
             }
         } else {
             $columns = $this->bestSplit($itemCount, false);
             if ($columns > $max_columns) {
                  $columns = $max_columns;
                  $rows = round($itemCount / $columns);
             }
             $universe .= $this->formatMultiHeader($variable_name, 
                                                   $columns, 
                                                   $itemCount, 
                                                   $checkedCount,
                                                   $allow_filtering);
             $temp = $rows * $columns;
             if ($temp < $itemCount) {
                  ++$rows;
             }
             for ($y = 0; $y < $rows; ++$y) {
                 $universe .= '<tr>';
                 for ($x = 0; $x < $columns; ++$x) {
                     $offset = $y + (int)($rows * $x);
                     $universe .= '<td>';
                     if (array_key_exists($offset, $realValue)) {
                         $checked = false;
                         if (array_key_exists($realValue[$offset], $displayChecked)) {
                             $checked = true;
                         }
	                 if ($notational != null) {
		              if (array_key_exists($realValue[$offset], $notational)) {
                                  ++$notations;
                              }
                         } 
                         $universe .= $this->formatMultiElement($variable_name,
                                                                $realValue[$offset],
                                                                $displayValue[$offset],
                                                                $checked,
                                                                $override_color,
                                                                DATA_POINT_SIZE - 1,
                                                                $notational);
                     }
                     $universe .= '</td>';
                 }
                 $universe .= '</tr>';
             }
         }

//
// put a notation at the bottom  
//
	if ($notation != null) {
		$universe .= '<tr><td colspan="' . ($columns * 2) . '">';
		if ($notations != 0) {
			$universe .= $this->STYLIST->formatBoldText('* - ' . $notation, HIGHLIGHT_FONT_COLOR, DATA_POINT_SIZE-1);
                }
		$universe .= '</td></tr>';
	}
	$universe .= '</table></td></tr></table>';

	return $this->STYLIST->formatBoldText($visible_name, $statusColor) .
		$this->STYLIST->formatColumnSeparation() .
		$universe;

    }

    function formatMultiHeader($variableName,
                               $columns, 
                               $itemCount, 
                               $checkedCount,
                               $allow_filtering = false) {
        if ($itemCount == $checkedCount) {
            $body = '<td align="center">' . '<input type="button" name="SOA_MULTI__NONE__' . $variableName . '" value="Select None"/>' . '</td>';
        } else {
            if ($checkedCount == 0) {
                $body = '<td align="center">' . '<input type="button" name="SOA_MULTI__ALL__' . $variableName . '" value="Select All"/>' . '</td>';
            } else {
                $body = '<td align="center">' . '<input type="button" name="SOA_MULTI__ALL__' . $variableName . '" value="Select All"/>' . '</td>' .
                        '<td align="center">' . '<input type="button" name="SOA_MULTI__NONE__' . $variableName . '" value="Select None"/>' . '</td>';
            }
        }
        if ($allow_filtering) {
             $body .= '<td align="center">' . '<input type="button" name="SOA_MULTI__FILTER__' . $variableName . '" value="Remove Highlighted"/>' . '</td>';
        }

        return '<tr><td colspan="' . ($columns * 2) . '"><table border="0" align="center"><tr>' .
               $body . 
               '</tr></table></td></tr>' .
               '<tr><td>';
    }

    function formatMultiElement($variableName,
                                $realValue,
                                $displayValue,
                                $is_checked,
                                $override_color,
                                $override_size = null,
                                $notational = null) {
        $checked = '';
        if ($is_checked) {
            $checked = ' checked="checked"';
        }
//
// notational
//
	$note = null;
	if ($notational != null) {
		if (array_key_exists($realValue, $notational)) {
			$note = $this->STYLIST->formatBoldText('* ', HIGHLIGHT_FONT_COLOR);
		}
	}
        return $note . '</td><td><input type="checkbox" name="SOA_ARRAY__' . $realValue . '__' . $variableName .'"' .  '" value="' .  $realValue .
               '" style="' . $this->STYLIST->createTextStyle($override_size) . '"' . $checked . '>' .
               $this->STYLIST->formatText($displayValue, $override_color, $override_size) .
               '</input>';
    }

    function formatSelectField($visible_name,
                               $variable_name,
                               $value,
                               $field_array = null,
                               $override_color = null,
                               $any_option = true,
                               $null_any = false,
                               $is_sorted = true) {
        if ($field_array == null) {
            return $this->formatSingleEntryField($visible_name,
                                                 $variable_name,
                                                 $value,
                                                 null,
                                                 $override_color);
        }
        if (!is_array($field_array)) {
            return $this->formatSingleEntryField($visible_name,
                                                 $variable_name,
                                                 $value,
                                                 null,
                                                 $override_color);
        }
        if (sizeof($field_array) == 1) {
            foreach ($field_array as $key => $val) {
                return $this->formatSingleEntryField($visible_name,
                                                     $variable_name,
                                                     $val,
                                                     null,
                                                     $override_color);
             }
        }

//
// sort list
//
        if ($is_sorted) {
	     asort($field_array);
        }

//
// determine if field is hidden 
//
        if (sizeof($field_array) == 1) {
            foreach ($field_array as $key => $val) {
                if ($key != 'VISIBLE') {
                    return $this->formatHiddenField($variable_name, $key);
                }
            }
        }

//
// create the HTML select container
//
        $buffer = '<select name="' .
                  $variable_name . '" style="' .
                  $this->STYLIST->createTextStyle(null, $override_color) .
                  '">'; 

//
// add ANY if a default is specified
//
        $statusColor = $override_color;
        if ($any_option) {
            if ($null_any) {
                $buffer .= '<option value="ANY" selected="selected">Any</option>';
            } else {
                if ($value == '') {
                    $buffer .= '<option value="" selected="selected">Choose an Option</option>';
                } else {
                    $buffer .= '<option value="">Choose an Option</option>';
                }
                $statusColor = 'red';
            }
        } else {
            if ($null_any) {
                $buffer .= '<option value="' . $this->noValueIndicator . '">** Not Used **</option>';
            }
        }

//
// add other options
//
        foreach ($field_array as $key => $val) {
            $buffer .= '<option value="' . $key . '"';
            if ($key == $value) {
                $buffer .= ' selected="selected"';
                $statusColor = $override_color;
            }
            $buffer .= '>' . $val . '</option>';
        }
        $buffer .= '</select>';

        return $this->STYLIST->formatBoldText($visible_name, $statusColor) .
               $this->STYLIST->formatColumnSeparation() .
               $buffer;
    }

    function formatCheckboxField($title,
                                 $value, 
                                 $variable,
                                 $override_color = null) {
        $checked = null;
        if ($value == 'true') {
            $checked = ' checked="checked"';
        }

        return $this->STYLIST->formatBoldText($title, $override_color) .
               '</td><td><input type="checkbox" name="' . $variable . '" value="' . $value .  '"' . $checked . '/>';
//               '</td><td><div style=' . $this->STYLIST->formatCheckbox($override_color) . '>' .
//               '</td><td>' .
//               '<div style="foreground-color:red;"' . '>' .
//              '<input type="checkbox" style="background-color:red;" name="' . $variable . '" value="' . $value .  '"' . $checked . '/>' .
//               '</div>';
    }

    function bestSplit2($itemCount,
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

        if ( ($rows * $columns) < $itemCount) {
             ++$rows;
        }

        if ($returnRows) {
            return $rows;
        }

        return $columns;
    }

    function bestSplit($itemCount,
                       $returnRows = true) {
        if ($itemCount < 5) {
            return $itemCount;
        }

        $rows = 2;
        $columns = 0;
        $loop = true;
        while ($loop) {
             $columns = $itemCount / $rows;
             if ($rows >= $columns) {
                  $loop = false;
             } else {
                  ++$rows;
             }
        }
        $columns = round($columns);
        if (($rows * $columns) < $itemCount) {
             ++$columns;
        }

        if ($returnRows) {
             return $rows + 1;
        }

        return $columns - 1;
    }

    function renderError($message) {
        return $this->STYLIST->formatBoldSystemText($message, HIGHLIGHT_FONT_COLOR);
    }

    function renderTitle($message) {
        return $this->STYLIST->formatBoldSystemText($message, PROJECT_FONT_COLOR);
    }

    function formatPseudoButton($name,
                                $url = null,
                                $font_color = null,
                                $background_color = null) {
        $style = $this->STYLIST->createButtonStyle($font_color, $background_color);
        if ($url == null) {
            $item = '<a onclick="history.go(-1)"><font style="' . $style . '">' . $name . '</font></a>'; 
        } else {
            $item = '<a href="' . $url . '" style="' . $style . '">' . $name . '</a>';
        }

        return '<table border="0" cellspacing="0" cellpadding="2"><tr><td align="center">' . $item . '</td></tr></table>';
    }

    function renderStartPanel($message = null,
                              $aBorder = null,
                              $aBackground = null) {
        if ($aBorder == null) {
            $border = PANEL_BORDER;
        } else {
            $border = $aBorder;
        }

        if ($aBackground == null) {
            $background = PANEL_BACKGROUND_COLOR;
        } else {
            $background = $aBackground;
        }

        $buffer = null;
        if ($message != null) {
            $buffer = '<tr align="center"><td>' . $this->renderError($message) . '</td></tr>';
        }

        return '<table align="center" cellspacing="0" cellpadding="' .
               PANEL_INSET .
               '" border="' .
               $border .
               '" bgcolor="' .
               $background .
               '" width="100%">' .
               $buffer;
    }

    function renderStartPanelItem($colspan = null) {
        $span = null;
        if ($colspan != null) {
            $span = ' colspan="' . $colspan . '"';
        }
        return '<tr align="center"><td' . $span . '>';
    }

    function renderStartInnerFrame() {
        return $this->renderStartPanelItem() .
               '<table align="center" cellspacing="0" cellpadding="' . PANEL_SPACING . '" border="0" width="100%">';
    }

    function renderStartInnerFrameItem($colspan = null,
                                       $align = null) {
        $type = 'center';
        if ($align != null) {
            $type = $align;
        }

        $span = null;
        if ($colspan != null) {
            $span = ' colspan="' . $colspan . '"';
        }

        return '<tr align="' . $type . '"><td' . $span . '>';
    }

    function renderEndInnerFrameItem() {
        return '</td></tr>';
    }

    function renderEndInnerFrame() {
        return '</table>' . $this->renderEndPanelItem();
    }

    function renderEndPanelItem() {
        return '</td></tr>';
    }

    function renderEndPanel($message = null) {
        $text = null;
        if ($message != null) {
            $text = '<tr align="center"><td>' . $this->renderError($message) . '</td></tr>';
        }

        return $text . '</table>';
    }

    function directoryList($localPath) {
        if (!is_dir($localPath)) {
           return null;
        }

//
// create filters
//
        $filter['.'] = true;
        $filter['..'] = true;
        $filter['.cvs'] = true;
        $filter['.svn'] = true;
        $filter['lost+found'] = true;

//
// read directory
//
        $dh = opendir($localPath);

        $temp = null;
        while ($file = readdir($dh)) {

//
// create a full path name
//
            if (strlen($localPath) == 1) {
                $fullpath = $localPath . $file;
            } else {
                $fullpath = $localPath . '/' . $file;
            }

//
// process if this is a directory 
//
            if (is_dir($fullpath)) {
                $fileName = basename($fullpath);
                if (!array_key_exists($fileName, $filter)) {
                    $pos = strpos($fileName,'.');
                    if ($pos === false) {
                    } else {
                        if ($pos == 0 && strlen($fileName) > 1) {
                            $fullpath = null;
                        }
                    }
                    if ($fullpath != null) {
                        $temp[] = $fullpath;
                    }
                }
            }
        }
        closedir($dh);

        $path_array = null;
        $pos = strrpos($localPath,'/');
        if (strlen($localPath) > 1) {
            $tempPath = substr($localPath, 0, $pos + 1);
            if (strlen($tempPath) > 1) {
                $pos = strrpos($localPath,'/');
                $tempPath = substr($localPath, 0, $pos);
            }
            $path_array[] = $tempPath;
        }
        $path_array[] = $localPath;
        if ($temp != null) {
            sort($temp);
            foreach ($temp as $key => $value) {
                $path_array[] = $value;
            }
        }

        return $path_array;
     }

}
?>
