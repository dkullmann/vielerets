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

date_default_timezone_set('UTC');

/*
$test = strtotime('2008-08-13T23:08:11');
$display = date(DATE_W3C,$test);
echo ($display);
echo ('<br>');

$offset = strtotime('1 hour', $test);
$display = date(DATE_W3C,$offset);
echo ($display);
echo ('<br>');
*/

$value = '2008-08-13T11:08:11';

echo ($value);
echo ('<br>');

print(createDateQueryElement('LIST_87',
                             'DATETIME',
                             $value,
                             -5));

     function createDateQueryElement($fieldName,
                                     $fieldType,
                                     $fieldValue,
                                     $timeDifference = null)
     {
          $checkDate = null;
          switch (strToUpper($fieldType))
          {
               case 'DATE':
                    $pos = strpos($fieldValue, 'T');
                    if ($pos === false)
                    {
                    }
                    else
                    {
                         $checkDate = substr($fieldValue, 0, $pos);
                    }
                    break;

               case 'DATETIME':
                    $pos = strpos($fieldValue, 'T');
                    if ($pos === false)
                    {
                    }
                    else
                    {
                         if ($timeDifference == 0)
                         {
                              $checkDate = $fieldValue;
                         }
                         else
                         {
/*
                              $dateOnly = substr($fieldValue, 0, $pos);
                              $timeOnly = substr($fieldValue, $pos + 1, strlen($fieldValue));
                              $pos = strpos($timeOnly, ':');
                              if ($pos === false)
                              {
                              }
                              else
                              {
                                   $hours = substr($timeOnly, 0, $pos);
                                   $balance = substr($timeOnly, $pos + 1, strlen($timeOnly));
                              }
                              $hours = $hours + $timeDifference;
                              if ($hours > 23) {
                                   $hours = $hours - 23;
                              }
                              if (strlen($hours) == 1) {
                                   $hours = '0' . $hours;
                              }
                              $checkDate = $dateOnly . 'T' . $hours . ':' . $balance;
*/
$checkTime = strtotime($fieldValue);
$newTime = strtotime($timeDifference . ' hour', $checkTime);
$checkDate = date(DATE_W3C,$newTime);
$checkDate = substr($checkDate, 0, strpos($checkDate, '+'));

                         }
                    }
                    break;
          }

          if ($checkDate != null)
          {
               return '(' . $fieldName . '=' . $checkDate . '+)';
          }

          return null;
     }

?>
