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

//
// defines
//

class AbstractRetsExchange {

     var $DISPLAY_HANDLER = null;
     var $useDisplayHandler = false;
     var $payloadTrace = false;
     var $streamTrace = false;
     var $transportTrace = false;
     var $traceDevice = 'SCREEN';

     function AbstractRetsExchange() {
          date_default_timezone_set('UTC');
     }

     function initializeTraceDevice($reuse = false) {
          if ($this->traceDevice != 'SCREEN') {
               if (!$reuse) {
                    if (file_exists($this->traceDevice)) {
                         unlink($this->traceDevice);
                    }
               }
               $this->rawTrace('');
               $this->rawTrace('----------------------------');
               $this->rawTrace(' Debug Output for VieleRETS ');
               $this->rawTrace('----------------------------');
               $this->rawTrace('');
               $this->rawTrace('');

          }
     }

     function setTraceDevice($aValue) {
          if ($aValue != 'SCREEN') {
               if ($fd = @fopen($aValue, 'a')) {
                    fclose($fd); 
                    $this->traceDevice = $aValue;
               }
          }
     }

     function setPayloadTrace($value) {
          $this->payloadTrace = $value;
     }

     function setStreamTrace($value) {
          $this->streamTrace = $value;
     }

     function getTransportTrace() {
          return $this->transportTrace;
     }

     function setTransportTrace($value) {
          $this->transportTrace = $value;
     }

     function trace($text) {
          if (is_array($text)) {
               $buffer = 'Array ( ';
               foreach ($text as $key => $value) {
                    $buffer .= '[' . $key . '] => ' . $value . ' ';
               }
               $buffer .= ')';
          } else {
               $buffer = $text;
          }

          if ($this->traceDevice != 'SCREEN') {
               $buffer = '[' . date('F j, Y G:i:s T') . '] ' .$buffer; 
          }
          $this->rawTrace($buffer);
     }

     function rawTrace($text) {
          if (is_array($text)) {
               $buffer = 'Array ( ';
               foreach ($text as $key => $value) {
                    $buffer .= '[' . $key . '] => ' . $value . ' ';
               }
               $buffer .= ')';
          } else {
               $buffer = $text;
          }

          if ($this->traceDevice == 'SCREEN') {
               print('<xmp>' . $buffer . '</xmp>');
          } else {
               $fp = fopen($this->traceDevice,'a');
               fwrite($fp, $buffer . CRLF); 
               fclose($fp); 
          }
     }

     function registerDisplayHandler($handler) {
          $this->DISPLAY_HANDLER = $handler;
          $this->useDisplayHandler = true;
     }

}

?>
