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

function ajax_processValue($aName,
                           $aValue,
                           $env = null)
{

     $trace = null;

//     $trace = print_r($env, true);
//     $trace = $aName . ' ' . $aValue;

//
// set up defaults
//
     $displayPerformance = 'false';
     $displayRets = 'false';
     $displayProviderNotice = 'false';
     $displayAccount = 'false';

     if (array_key_exists('viele_mode',$env))
     {
          $LOCATION = determine_type($env['ELEMENT-TYPE']);
          $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
          $displayPerformance = $CONFIGURATION->getValue('DISPLAY_PERFORMANCE');
          $displayRets = $CONFIGURATION->getValue('DISPLAY_RETS');
          $displayProviderNotice = $CONFIGURATION->getValue('DISPLAY_PROVIDER_NOTICE');
          $displayAccount = $CONFIGURATION->getValue('DISPLAY_ACCOUNT');
     }

//
// weigh input
//
     if (array_key_exists('DISPLAY_PERFORMANCE',$env))
     {
          $displayPerformance = $env['DISPLAY_PERFORMANCE'];
     }

     if (array_key_exists('DISPLAY_RETS',$env))
     {
          $displayRets = $env['DISPLAY_RETS'];
     }

     if (array_key_exists('DISPLAY_PROVIDER_NOTICE',$env))
     {
          $displayProviderNotice = $env['DISPLAY_PROVIDER_NOTICE'];
     }

     if (array_key_exists('DISPLAY_ACCOUNT',$env))
     {
          $displayAccount = $env['DISPLAY_ACCOUNT'];
     }

     $FORMATTER = new AjaxFormatter();

     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatBinaryField('Performance Statistics?',
                                              'DISPLAY_PERFORMANCE',
                                              $displayPerformance);

     $items[] = $FORMATTER->formatBinaryField('RETS Details?',
                                              'DISPLAY_RETS',
                                              $displayRets);

     $items[] = $FORMATTER->formatBinaryField('Provider Notification?',
                                              'DISPLAY_PROVIDER_NOTICE',
                                              $displayProviderNotice);

     $items[] = $FORMATTER->formatBinaryField('Account Details?',
                                              'DISPLAY_ACCOUNT',
                                              $displayAccount);

//-------------------

     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', $env['ELEMENT-TYPE']);
     $items[] = $FORMATTER->formatHiddenField('ELEMENT', $env['ELEMENT']);

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('SOURCE_INFORMATION_DISPLAY'), $items) .
            ']]></HTML>';
}

//
//------------

?>
