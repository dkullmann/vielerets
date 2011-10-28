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

class Configuration
{

     var $contents;
     var $valueIndex;
     var $variableIndex;
     var $fileName;

     function Configuration($fileName = null)
     {
          if ($fileName != null)
          {
               $this->fileName = $fileName;
               if (file_exists($fileName))
               {
                    $this->contents = file($fileName);
               }
               $this->buildIndex();
          }
     }

     function buildIndex()
     {
          if ($this->contents != null)
          {
//print("BUILD INDEX\r\n");
               $this->valueIndex = null;
               $searchKey = 'define("';
               foreach ($this->contents as $line_num => $line) 
               {
                    $pos = strpos($line, $searchKey);
                    if ($pos === false)
                    {
                    }
                    else
                    {
//
// find comma 
//
                         $pos = strpos($line, ',');
                         $source = substr($line, 0, $pos);
                         $source = trim($source);

//
// find left paren 
//
                         $pos = strpos($source, '(');
                         $source = substr($source, $pos + 1, strlen($source));
                         $source = trim($source);

//
// remove single and double quotes
//
                         $source =  trim($source, "\x22\x27");

//
// enter into index
//
                         $this->valueIndex[$source] = $line_num;
                    }
               }
//print_r($this->valueIndex);
//print("VALUE\r\n");

               $source = null;
               $this->variableIndex = null;
               $searchKey = '=array(';
               foreach ($this->contents as $line_num => $line) 
               {
                    $pos = strpos($line, $searchKey);
                    if ($pos === false)
                    {
                    }
                    else
                    {
//
// find equals 
//
                         $pos = strpos($line, '=');
                         $source = substr($line, 0, $pos);
                         $source = trim($source);

//
// remove $
//
                         $pos = strpos($source, '$');
                         $source = substr($source, $pos + 1, strlen($source));
                         $source = trim($source);

//
// enter into index
//
                         $this->variableIndex[$source] = $line_num;
                    }
               }
//print_r($this->variableIndex);
//print("VARIABLE\r\n");
          }
     }

     function getName()
     {
          return baseName($this->fileName);
     }

     function getListValue($key)
     {
          $list = null;

          $temp = explode(',', $this->getValue($key));
          foreach ($temp as $num => $item) 
          {
               $list[$item] = true;
          }

          return $list;
     }

     function setBooleanFromArg($name, $vars)
     {
          $this->setValue($name, booleanFromArg($name,$vars));
     }

     function getBooleanValue($key)
     {
          $value = $this->getValue($key);

          if (is_bool($value))
          {
               return $value;
          }

          if (is_string($value))
          {
               $value = strtolower($value);
               if ($value == 'true')
               {
                    return true;
               }
               if ($value == '1')
               {
                    return true;
               }
               return false;
          }
          settype($value, 'boolean');
          return $value;
     }

     function getValue($key)
     {
          if ($this->valueIndex == null)
          {
               return null;
          }
          if (!array_key_exists($key, $this->valueIndex))
          {
               return null;
          }
          $line = $this->valueIndex[$key];
          if ($line === false)
          {
               return null;
          }

//
// get the line
//
          $source = $this->contents[$line]; 

//
// find comma 
//
          $first = strpos($source, ',');
          $temp = substr($source, $first + 1, strlen($source));
          $temp = trim($temp);

//
// find right paren 
//
          $first = strrpos($temp, ')');
          $temp = substr($temp, 0, $first);
          $temp = trim($temp);

//
// remove escaped $
//
          $pos = strpos($temp, "\\$");
          if ($pos === false)
          {
          }
          else
          {
               $piece = substr($temp, 0, $pos);
               $piece .= substr($temp, $pos + 1, strlen($temp));
               $temp = $piece;
          }
            
//
// remove single and double quotes
//
          return trim($temp, "\x22\x27");

     }

     function setBooleanValue($name, $val)
     {
          if (is_bool($val))
          {
               $value = $val;
          }

          if (is_string($val))
          {
               $check = strtolower($val);
               if ($check == 'true')
               {
                    $value = true;
               }
               if ($check == '1')
               {
                    $value = true;
               }
               $value = false;
          }
          $this->setValue($name, $value);
     }

     function setValue($key,
                       $val)
     {
          $line = false;
          if (array_key_exists($key, $this->valueIndex))
          {
               $line = $this->valueIndex[$key];
          }

//
// value not defined yet
//
          if ($line === false)
          {
              $line = sizeof($this->contents) - 1;
              $this->contents[$line + 1] = "\n";
              $this->contents[] = '?>';
          }

//
// identify BINARY
//
          $binary = false;
          if (is_bool($val))
          {
               $binary = true;
          }
          else
          {
               if (is_numeric($val))
               {
                    $binary = false;
               }
               else
               {
                    if ($val == 'true')
                    {
                         $binary = true;
                         $val = true;
                    }
                    else
                    {
                         if ($val == 'false')
                         {
                              $binary = true;
                              $val = false;
                         }
                    }
               }
          }

//
// generate output
//
          if ($binary)
          {
               if ($val === false)
               {
                    $this->contents[$line] = "define(\x22" . 
                                             $key . 
                                             "\x22,false);\n";
               }
               else
               {
                    if ($val == 0)
                    {
                         $this->contents[$line] = "define(\x22" . 
                                                  $key . 
                                                  "\x22,\x220\x22);\n";
                    }
                    else
                    {
                         $this->contents[$line] = "define(\x22" . 
                                                  $key . 
                                                  "\x22,true);\n";
                    }
               }
          }
          else
          {
               if (!is_numeric($val))
               {
//
// convert NULL
//
               if ($val == 'NULL')
               {
                    $val = '';
               }

               $pos = strpos($val, '$');
               if ($pos === false)
               {
               }
               else
               {
                    $piece = substr($val, 0, $pos);
                    $piece .= "\\$";
                    $piece .= substr($val, $pos + 1, strlen($val));
                    $val = $piece;
               }
               }
               $this->contents[$line] = "define(\x22" . 
                                        $key . 
                                        "\x22,\x22" . 
                                        $val . 
                                        "\x22);" .
                                        "\n";
          }
          $this->buildIndex();
     }

     function removeVariable($key)
     {
          $this->setVariable('$' . $key, null);
     }

     function setVariable($key,
                          $val)
     {
          $key = $this->ensureLegalVariableName($key);
//
// if this is an array, convert to a string
//
          if (is_array($val))
          {
               $setting = '$' . $key . '=array(';
               foreach ($val as $a_key => $value) 
               {
                    $setting .= '"' . 
                                $a_key . 
                                '"=>"' .
                                $value .
                                '",';
               }
               $setting = substr($setting, 0, strlen($setting) - 1);
               $setting .= ');';
          }
          else
          {
               $setting = $val;
          }

//
// write value
//
          $lookup = true;
          if ($this->variableIndex == null)
          {
               $lookup = false;
          }
          else
          {
               if (!array_key_exists($key, $this->variableIndex))
               {
                    $lookup = false;
               }
          }
          $line = false;
          if ($lookup)
          {
               $line = $this->variableIndex[$key];
          }

          if ($line === false)
          {
              if ($setting != null)
              {
                   $this->contents[sizeof($this->contents) - 1] = $setting . 
                                                                  "\n";
                   $this->contents[] = '?>';
              }
          }
          else
          {
              if ($setting != null)
              {
                   $this->contents[$line] = $setting . "\n";
              }
              else
              {
                   $this->contents[$line] = null;
              }
          }
          $this->buildIndex();
     }

     function ensureLegalVariableName($key)
     {
          $offset = 0;
          $check = substr($key, $offset, 1);
          if ($check == '$')
          {
               ++$offset;
               $check = substr($key, $offset, 1);
          }
          if (is_numeric($check))
          {
               if ($offset == 1)
               {
                    return 'x' . 
                           substr($key, 1, strlen($key));
               }
               else
               {
                    return 'x' . substr($key, 0, strlen($key));
               }
          }
        
          return substr($key, 1, strlen($key));
     }

     function getVariable($key)
     {
          if ($this->variableIndex == null)
          {
               return null;
          }

          $key = $this->ensureLegalVariableName('$' . $key);
//echo "<XMP>Get $key</XMP>";
          if (!array_key_exists($key, $this->variableIndex))
          {
               return null;
          }
          $line = $this->variableIndex[$key];
          if ($line === false)
          {
               return null;
          }
//               include_once($this->fileName);
          @include($this->fileName);
//          include($this->fileName);
//echo "<XMP>FOUND $key LINE $line FILE $this->fileName</XMP>";
          return $$key;
     }

     function write($fileName)
     {
          $fp = fopen($fileName, 'w');
          foreach ($this->contents as $line_num => $line) 
          {
               if ($line != null)
               {
                    fwrite($fp, $line);
               }
          }
          fclose($fp);
     }

/*
     function dump()
     {
          foreach ($this->contents as $line_num => $line) 
          {
echo "Line #<b>{$line_num}</b> : " . $line . "<br/>\n";
          }
     }
*/

}

?>
