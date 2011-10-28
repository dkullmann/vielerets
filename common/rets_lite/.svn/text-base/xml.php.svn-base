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

define('TRANSLATION_PARSER_INTERPRET_MISSING', false);

//------------
//
// XML functions
//

//------------------

class RETSParser 
{

     var $reading_type;
     var $collected_data;
     var $collection_count;
     var $delimiter;

     function RETSParser()
     {
          $this->delimiter = 9;
     }

     function accumulateData($parser, 
                             $data) 
     {
          if ($this->reading_type)
          {
               $this->collected_data[++$this->collection_count] = $data;
          }
     }

     function splitData() 
     {
          $separator = chr($this->delimiter);
          $whole = implode(null, $this->collected_data);
//          return array_slice(explode($separator, $whole), 1, -1);

//
// remove first and last separator
// separator is "\x09"
//          $whole = trim($whole, "\t");
          $pos = strpos($whole, $separator); 
          $whole = substr($whole, $pos + 1, strlen($whole) - $pos);
          $pos = strrpos($whole, $separator);
          if ($pos > 0)
          { 
               $whole = substr($whole, 0, $pos);
          }

//
// return an array 
//
          return explode($separator, $whole);
     }

}

class CompactParser 
     extends RETSParser
{

     var $value_array;
     var $column_array;
     var $data_array;
     var $data;
     var $queryCount;
     var $xml_parser;

     function CompactParser()
     {
          $this->RETSParser();
          $this->column_array = null;
          $this->queryCount = null;
          $this->xml_parser = xml_parser_create();
     }

     function getColumns()
     {
          return $this->column_array;
     }

     function getData()
     {
          return $this->data;
     }

     function getQueryCount()
     {
          return $this->queryCount;
     }

     function parse($buffer,
                    $isFinal = true) 
     {
//
// initialize variables
//
          $this->value_array = null;
          $this->data_array = null;
          $this->collection_count = 0;
          $this->collected_data = null;
          $this->reading_type = true;
//
// parse 
//
          xml_set_object($this->xml_parser, $this);
          xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($this->xml_parser, 
                                  'startElement', 
                                  'endElement');
          xml_set_character_data_handler($this->xml_parser, 'accumulateData');
          $start = $this->findPos($buffer, '<?');
          if ($start === false)
          {
          }
          else
          {
               $end = strpos($buffer, '>');
               $buffer = substr($buffer, $end + 1, strlen($buffer));
          }

//echo "<XMP>B $buffer</XMP>";
          xml_parse($this->xml_parser, $buffer, $isFinal);
//
// check for errors
//
          $perror_code =  xml_get_error_code($this->xml_parser);
          if ($perror_code == 4) 
          {
               $perror_desc = ' Server response contains ' .
                              xml_error_string($perror_code) .
                              '.'; 
write_error_message($perror_desc,
                    '20513',
                    'SOURCE does not return well formed XML.');
          } 
//
// finish with parser
//
          if ($isFinal)
          {
               xml_parser_free($this->xml_parser);
          }
          else
          {
               $this->data = null;
          }

//
// break down data into a nice array
//
          $maxCols = sizeof($this->column_array);
          if ($maxCols > 0 )
          {
               $maxRows = (sizeof($this->data_array) - 1) / $maxCols;
          }

          for ($i = 0; $i < $maxCols; ++$i) 
          {
               $column_value = $this->column_array[$i];
               for ($j = 0; $j <= $maxRows; ++$j) 
               {
                    $offset = ($maxCols * $j) + $i;
                    if (array_key_exists($offset, $this->data_array))
                    {
                         $this->data[$j][$column_value] = $this->data_array[$offset];
                    }
                    else
                    {
                         $this->data[$j][$column_value] = 'X';
                    }
               }
          }
     }

     function startElement($parser, 
                           $name, 
                           $attrs) 
     {

          $this->collection_count = 0;
          $this->collected_data = null;

          switch ($name)
          {
               case 'COUNT':
                    $this->queryCount = $attrs['RECORDS'];
                    break;

               case 'DELIMITER':
                    $this->delimiter = $attrs['VALUE'];
                    break;

          }

     }

     function endElement($parser, 
                         $name) 
     {
          if ($this->collected_data) 
          {
               $temp = $this->splitData();
               switch ($name)
               {
                    case 'COLUMNS':
                         foreach ($temp as $key => $val)
                         {
                              $this->column_array[] = $val;
                         }
                         break;

                    case 'DATA':
                         foreach ($temp as $key => $val)
                         {
                              $this->data_array[] = $val;
                         }
                         break;

               }
               $this->collection_count = 0;
               $this->collected_data = null;
          }
     }

     function findPos($haystack, 
                      $needle)
     {
          $pos = strpos($haystack, $needle);
          if ($pos === false)
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
          }
          return $pos;
     }

}

class TranslationParser 
     extends RETSParser
{

     var $control_buffer;
     var $metadata_type;
     var $control_map;
     var $n_column;
     var $t_column;

     function TranslationParser()
     {
          $this->RETSParser();
     }

     function parse($control_data,
                    $needle_column,
                    $translation_column,
                    $metadata_type_expected) 
     {
          $this->control_buffer = null;
          $this->reading_type = false;
          $this->collection_count = 0;
          $this->collected_data = null;
          $this->metadata_type = $metadata_type_expected;
//echo "<XMP>TYPE $this->metadata_type</XMP>";
          $this->control_map = null;
          $this->control_map['NEEDLE_COLUMN'] = strtoupper($needle_column);
          $this->control_map['TRANSLATION_COLUMN'] = strtoupper($translation_column);
//print_r($this->control_map);
          $xml_parser = xml_parser_create();
          xml_set_object($xml_parser, $this);
          xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($xml_parser, 
                                  'startElement', 
                                  'endElement');
          xml_set_character_data_handler($xml_parser, 'accumulateData');
          xml_parse($xml_parser, $control_data, true);
          xml_parser_free($xml_parser);
          return $this->control_buffer;
     }

     function startElement($parser, 
                           $name, 
                           $attrs) 
     {
          $this->collection_count = 0;
          $this->collected_data = null;
          if ($name == $this->metadata_type) 
          {
               $this->reading_type = true;
/*
//
// check for "*" option
//
               $bypass = false;
               if (isset($this->metadata_map)) 
               {
                    if (is_array($this->metadata_map)) 
                    {
                         reset ($this->metadata_map);
                         foreach ($this->metadata_map as $key => $val)
                         {
                              if ($val == '*')
                              {
                                   $bypass = true;
                                   break;
                              }
                         }
                    }
               }

               if ($bypass)
               { 
                    $this->reading_type = true;
               }
               else 
               {

//
// if no map, then match all 
//
                    if (!isset($this->metadata_map))
                    {
                         $this->reading_type = true;
                    }
                    else 
                    {
                         reset($this->metadata_map);
                         $map_size = sizeof($this->metadata_map);
                         $match = 0;
                         foreach ($this->metadata_map as $key => $val)
                         {
                              if (array_key_exists($key, $attrs)) 
                              {
                                   if ($val == $attrs[$key])
                                   {
                                        ++$match;
                                   }
                                   else 
                                   {

//
// check for "0" option
//
                                        if ($val == '0')
                                        {
                                             ++$match;
                                        }
                                   }
                              }
                         }
                         if ($map_size == $match)
                         {
                              $this->reading_type = true;
                         }
                    }
               }
*/
          }
     }

     function endElement($parser, 
                         $name) 
     {
          if ($this->reading_type) 
          {
               if ($this->collected_data) 
               {
                    switch ($name)
                    {
                         case 'COLUMNS':
                              $this->n_column = -1;
                              $this->t_column = -1;
                              if (array_key_exists('NEEDLE_COLUMN', $this->control_map) && 
                                  array_key_exists('TRANSLATION_COLUMN', $this->control_map)) 
                              { 
                                   $temp = $this->splitData();
                                   $count = 0;
                                   foreach ($temp as $key => $val)
                                   {
                                        $check = strtoupper($val);
                                        switch ($check)
                                        {
                                             case $this->control_map['NEEDLE_COLUMN']:
                                                  $this->n_column = $count;
//echo "<XMP>NEEDLE $check</XMP>";
                                                  if ($check == $this->control_map['TRANSLATION_COLUMN'])
                                                  {
                                                       $this->t_column = $count;
                                                  }
                                                  break;

                                             case $this->control_map['TRANSLATION_COLUMN']:
                                                  $this->t_column = $count;
//echo "<XMP>TRANSLATION $check</XMP>";
                                                  if ($check == $this->control_map['NEEDLE_COLUMN'])
                                                  {
                                                       $this->n_column = $count;
                                                  }
                                                  break;
                                        }
                                        if ($this->n_column != -1 && $this->t_column != -1)
                                        {
                                             break;
                                        }
                                        $count += 1;
                                   }
                              }
                              break;

                         case 'DATA':
                              if ($this->n_column != -1 && $this->t_column != -1) 
                              {
                                   $temp = $this->splitData();
                                   if (TRANSLATION_PARSER_INTERPRET_MISSING)
                                   {
                                   if (array_key_exists($this->n_column, $temp))
                                   {
                                        if (array_key_exists($this->t_column, $temp))
                                        {
                                             $this->control_buffer[$temp[$this->n_column]] = $temp[$this->t_column]; 
                                        }
                                        else
                                        {
                                             $this->control_buffer[$temp[$this->n_column]] = "NOT_DEFINED"; 
                                        }
                                   }
                                   }
                                   else 
                                   {
                                   if (array_key_exists($this->n_column, $temp) &&
                                       array_key_exists($this->t_column, $temp))
                                   {
                                        $this->control_buffer[$temp[$this->n_column]] = $temp[$this->t_column]; 
                                   }
                                   }
//                                   else
//                                   {
//print_r($this->control_buffer);
//                                   }
                              }
                              break;

                    }
                    $this->collection_count = 0;
                    $this->collected_data = null;
               }

               if ($name == $this->metadata_type)
               {
                    $this->reading_type = false;
               }
          }
     }

}

class FormatParser
     extends RETSParser 
{
     var $format_check;
     var $cached_value;

     function FormatParser()
     {
          $this->RETSParser();
     }

     function parse($buffer) 
     {

//
// initialize variables
//
          $this->format_check = false;
          $this->reading_type = true;
          $this->cached_value = null;

//
// parse 
//
          $xml_parser = xml_parser_create();
          xml_set_object($xml_parser, $this);
          xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($xml_parser, 
                                  'startElement', 
                                  'endElement');
          xml_set_character_data_handler($xml_parser, 'accumulateData');
          xml_parse($xml_parser, $buffer, true);
          xml_parser_free($xml_parser);
          if ($this->format_check)
          {
write_warning_message('Non-standard RETS Server detected.',
                      'Leading or Training tabs (0x09) missing from COLUMNS or DATA element(s).',
                      'Fixed by the package.');
          }

          return $this->cached_value;
     }

     function startElement($parser, 
                           $name, 
                           $attrs) 
     {
          $this->collection_count = 0;
          $this->collected_data = null;

//
// regenerate element tag
//
          $whole = null;
          foreach ($attrs as $key => $val)
          {
               $whole .= ' ' . $key . '="' . $attrs[$key] . '"';
          }
          $this->cached_value .= '<' . $name . $whole . '>';
     }

     function endElement($parser, 
                         $name) 
     {
          $special_processing = false;
          $whole = null;
          if ($this->collected_data) 
          {

//
// Special processing check.  If reading DATA or COLUMNS.
//
               if ($name == 'COLUMNS' || 
                   $name == 'DATA' )
               {
                   $special_processing = true;
               }

//
// Special processing.  Take out space, ascii 0, ascii newline
// and ascii return from both ends of the stream.
//
               $whole = implode(null, $this->collected_data);
               $this->collection_count = 0;
               $this->collected_data = null;
               if($special_processing) 
               {
                    $whole = "\t"
                           . htmlspecialchars(trim($whole))
                           . "\t";
               }
          }

//          $tag_trailer =  $whole . '</' . $name . '>';
//          if (!$special_processing)
//          { 
//               $tag_trailer .= "\n";
//          }
//          $this->cached_value .= $tag_trailer;
          $this->cached_value .= $whole . '</' . $name . '>';

     }

}

function write_error_message($message,
                             $code,
                             $desc) 
{
     trigger_error($message . ' ' . $desc, 
                   E_USER_ERROR);
}

function write_warning_message($message,
                               $desc,
                               $disp) 
{
     trigger_error($message . ' ' . $desc . ' ' . $disp, 
                   E_USER_WARNING);
}

class ErrorParser 
{

     var $error_code;
     var $error_text;
     var $error_check;
     var $reading_type;

     function ErrorParser()
     {
     }

     function getErrorText()
     {
          return $this->error_text;
     }

     function parse($buffer) 
     {
          $this->error_code = 0;
          $this->error_text = null;
          $this->error_check = false;
          $this->reading_type = false;
          $xml_parser = xml_parser_create();
          xml_set_object($xml_parser, $this);
          xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($xml_parser, 
                                  'startElement', 
                                  'endElement');
          xml_parse($xml_parser, $buffer, true);
          xml_parser_free($xml_parser);
          if ($this->error_code > 0) 
          {
/*
write_error_message("RETS Server generated an error.",
                    $this->error_code,
                    $this->error_text);
*/
               $this->error_check = true; 
          }

          return $buffer;
     }

     function startElement($parser, 
                           $name, 
                           $attrs) 
     {
          if ($name == 'RETS') 
          {
               $this->reading_type = true;
               $this->error_code = $attrs['REPLYCODE'];
               $this->error_text = $attrs['REPLYTEXT'];
          }
     }

     function endElement($parser, 
                         $name) 
     {
          if ($this->reading_type)
          {
               $reading_type = false;
          }
     }
}

class EnvelopeParser 
{

     var $envelope_check;
     var $reading_type;

     function EnvelopeParser()
     {
     }

     function parse($buffer,
                    $content_type,
                    $metadata_container = null) 
     {
//
// initialize
//
          $perror_code = 0;
          $this->envelope_check = false;

//
// choose the right type of parser
//
          if ($content_type == 'text/plain')
          {
               $pos = strpos($buffer, '/RETS');
               if ($pos === false)
               {
               }
               else
               {
                    $this->envelope_check = true;
               }
          }
          else
          {
               $xml_parser = xml_parser_create();
               xml_set_object($xml_parser, $this);
               xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
               xml_set_element_handler($xml_parser, 
                                       'startElement', 
                                       'endElement');
               $presult = xml_parse($xml_parser, $buffer, true);
               $perror_code =  xml_get_error_code($xml_parser);
               $perror_line = xml_get_current_line_number($xml_parser);
               xml_parser_free($xml_parser);

//
// Parser Error Codes
//  3 - data after the root document (probably missing a RETS element)
//  4 - not well formed (probably has &, < or > characters)
//
               if ($perror_code == 4) 
               {
                    $perror_desc = ' Server response contains ' .
                                   xml_error_string($perror_code) .
                                   ' at line r '.
                                   $perror_line .
                                   '.'; 
write_error_message($perror_desc,
                    '20513',
                    'SOURCE does not return well formed XML.');
                    $buffer = sprintf("<RETS ReplyCode=\"%s\" ReplyText=\"%s\">\r\n</RETS>",
                                      "20513",
                                      "RETS Server does not produce well-formed XML.  Possible inclusion of illegal characters. ");
               }
          }

//
// check for crazy errors on packaging
//
          if ($perror_code == 0) 
          {
               if (!$this->envelope_check) 
               {
//write_warning_message('Non-standard RETS Server detected.',
//                      '0',
//                      'No root node.  Fixed by the package');
                    if ($metadata_container == null)
                    {
                         $wrapper = "<RETS ReplyCode=\"%s\" ReplyText=\"%s\">\r\n%s\r\n</RETS>";
                    }
                    else
                    {
                         $pos = strpos($metadata_container, ' ');
                         if ($pos === false)
                         {
                              $metadata_base = $metadata_container;
                         }
                         else
                         {
                              $metadata_base = substr($metadata_container, 0, $pos);
                         }

                         $pos = strpos($buffer, $metadata_base);
                         if ($pos > strlen($metadata_base))
                         {
                              $wrapper = "<RETS ReplyCode=\"%s\" ReplyText=\"%s\">\r\n" .
                                         '<' . $metadata_container . ">\r\n" .
                                         "%s\r\n" .
//                                         "</" . $metadata_base . ">\r\n" .
                                         '</RETS>';
                         }
                         else
                         {
                              $wrapper = "<RETS ReplyCode=\"%s\" ReplyText=\"%s\">\r\n%s\r\n</RETS>";
                         }
                    }
                    $buffer = sprintf($wrapper,
                                      '0',
                                      'Non-compliant RETS Server. Fixed by the package',
                                      $buffer);
               }
          }
 
          return $buffer;
     }

     function startElement($parser, 
                           $name, 
                           $attrs) 
     {
          if ($name == 'RETS')
          {
               $this->reading_type = true;
          }
     }

     function endElement($parser, 
                         $name) 
     {
          if ($this->reading_type) 
          {
               $this->reading_type = false;
               $this->envelope_check = true;
          }
     }
}

?>
