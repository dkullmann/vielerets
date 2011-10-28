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
// includes 
//
include_once('./controller.php');
include_once(COMMON_DIRECTORY . '/download.php');

if (array_key_exists('CANCEL', $vars))
{
     header('Location: ' . $vars['LOCATION']);
     exit;
}

//
// pre declare classes 
//
class ProcessResults 
     extends AbstractProcessResults 
{
     var $FORMATTER;

     function ProcessResults()
     {
          parent::AbstractProcessResults();
          $this->FORMATTER = new TableFormatter(false);
     }

     function printErrors()
     {
          $buffer = null;
          foreach ($this->errors as $key => $value) 
          {
               $buffer .= '</br>&nbsp;&nbsp;' . 
                          $this->FORMATTER->renderError($value) .
                          '</br>'; 
          }
          flush_output($buffer);
     }

}

class InactiveStatistics
     extends AbstractStatistics {
     var $FORMATTER;

     function InactiveStatistics($name) {
          parent::AbstractStatistics($name);
          $this->FORMATTER = new TableFormatter(false);
          $this->setSpacer('&nbsp;&nbsp;');
          $this->setEOL('<br/>');
          $this->setVisibleBreak('<hr/>');
     }

     function printErrors() {
          $buffer = null;
          foreach ($this->errors as $key => $value) {
               $buffer .= '</br>&nbsp;&nbsp;' . 
                          $this->FORMATTER->renderError($value); 
          }
          flush_output($buffer);
     }

     function printBatchStart() {
          flush_output('<hr/>' . $this->getStart() . '<br/>');
     }

     function printBatchDetail($detail) {
          $buffer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $detail . '<br/>';
          if ($this->hasErrors()) {
               foreach ($this->errors as $key => $value) {
                    $buffer .= '&nbsp;&nbsp;' .
                                $this->FORMATTER->renderError($value) .
                                '<br/>';
               }
          }
          flush_output($buffer);
     }

     function printBatchNote() {
          flush_output('.');
     }

     function printBatchSummary() {
          flush_output('<br/>' . $this->getSummary());
     }

     function getStart() {
          parent::markStart();
          return '<table align="center" border="1" cellspacing="0" cellpadding="5">' .
                 '  <tr align="center">' .
                 '    <td>' .
                 $this->FORMATTER->formatBoldText('Starting ' . $this->name, 'red') .
                 '<br/>' .
                 $this->FORMATTER->formatText(date('F j, Y g:i:s A', $this->start) . ' (GMT)', 'red') .
                 '    </td>' .
                 '  </tr>' .
                 '</table>'; 
     }

     function getSummary($RETRIEVER = null) {
          parent::markFinish();
          $spacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          $label = $this->name . ' Completed';
          $date = date('F j, Y g:i:s A', $this->finish) . ' (GMT)';
          $buffer = '<table align="center" border="1" cellspacing="0" cellpadding="5">' .
                    '  <tr align="center">' .
                    '    <td>' .
                    $this->FORMATTER->formatBoldText($label, 'red') .
                    '<br/>' .
                    $this->FORMATTER->formatText($date, 'red') .
                    '    </td>' .
                    '  </tr>' .
                    '  <tr>' .
                    '    <td>' .
                    '      <table align="center" border="0" cellspacing="0" cellpadding="5">';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $this->FORMATTER->formatBoldText('Listings Processed') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getProcessed()) .
                     '          </td>' .
                     '        </tr>';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $spacer .
                     $this->FORMATTER->formatText('Changes made') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getOrphans()) .
                     '          </td>' .
                     '        </tr>';

          if ($this->start != null) {
               $buffer .= '        <tr>' .
                          '          <td>' .
                          $this->FORMATTER->formatBoldText('Performance') .
                          '          </td>' .
                          '          <td align="right">' .
                          '          </td>' .
                          '        </tr>' .
                          '        <tr>';
               $elapsed = $this->getRunTime();
               if ($elapsed == 0) {
                    $elapsed = 1;
               }
               $count = $this->getProcessed();
               $rate = round($count / $elapsed, 4);
               if ($count == 0) {
                    $speed = 0;
               } else {
                    $speed = round($elapsed / $count, 4);
               }
               $buffer .= '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Total Elapsed Seconds') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($elapsed, 0)) .
                          '          </td>' .
                          '        </tr>';
               if ($RETRIEVER != null) {
                    $networkTime = $RETRIEVER->getNetworkTime();
                    $buffer .= '        <tr valign="bottom">' .
                               '          <td>' .
                               $spacer .
                               $spacer .
                               $this->FORMATTER->formatText('MLS Server') .
                               '          </td>' .
                               '          <td align="right">' .
                               $this->FORMATTER->formatText(number_format($networkTime, 0)) .
                               '          </td>' .
                               '        </tr>' .
                               '        <tr valign="bottom">' .
                               '          <td>' .
                               $spacer .
                               $spacer .
                               $this->FORMATTER->formatText('VieleRETS') .
                               '          </td>' .
                               '          <td align="right">' .
                               $this->FORMATTER->formatText(number_format($elapsed - $networkTime, 0)) .
                               '          </td>' .
                               '        </tr>';
               }

               $buffer .= '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Items per Second') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($rate, 4)) .
                          '          </td>' .
                          '        </tr>' .
                          '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Seconds per Item') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($speed, 4)) .
                          '          </td>' .
                          '        </tr>';
          }

          $buffer .= '      </table>' .
                     '    </td>' .
                     '  </tr>' .
                     '</table>';

          return $buffer;
     }
}

class SynchronizationStatistics
     extends AbstractStatistics {
     var $FORMATTER;

     function SynchronizationStatistics($name) {
          parent::AbstractStatistics($name);
          $this->FORMATTER = new TableFormatter(false);
          $this->setSpacer('&nbsp;&nbsp;');
          $this->setEOL('<br/>');
          $this->setVisibleBreak('<hr/>');
     }

     function printErrors() {
          $buffer = null;
          foreach ($this->errors as $key => $value) {
               $buffer .= '</br>&nbsp;&nbsp;' . 
                          $this->FORMATTER->renderError($value); 
          }
          flush_output($buffer);
     }

     function printBatchStart() {
          flush_output('<hr/>' . $this->getStart() . '<br/>');
     }

     function printBatchDetail($detail) {
          $buffer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $detail . '<br/>';
          if ($this->hasErrors()) {
               foreach ($this->errors as $key => $value) {
                    $buffer .= '&nbsp;&nbsp;' .
                                $this->FORMATTER->renderError($value) .
                                '<br/>';
               }
          }
          flush_output($buffer);
     }

     function printBatchNote() {
          flush_output('.');
     }

     function printBatchSummary() {
          flush_output('<br/>' . $this->getSummary());
     }

     function getStart() {
          parent::markStart();
          return '<table align="center" border="1" cellspacing="0" cellpadding="5">' .
                 '  <tr align="center">' .
                 '    <td>' .
                 $this->FORMATTER->formatBoldText('Starting ' . $this->name, 'red') .
                 '<br/>' .
                 $this->FORMATTER->formatText(date('F j, Y g:i:s A', $this->start) . ' (GMT)', 'red') .
                 '    </td>' .
                 '  </tr>' .
                 '</table>'; 
     }

     function getSummary($RETRIEVER = null) {
          parent::markFinish();
          $spacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          $label = $this->name . ' Completed';
          $date = date('F j, Y g:i:s A', $this->finish) . ' (GMT)';
          $buffer = '<table align="center" border="1" cellspacing="0" cellpadding="5">' .
                    '  <tr align="center">' .
                    '    <td>' .
                    $this->FORMATTER->formatBoldText($label, 'red') .
                    '<br/>' .
                    $this->FORMATTER->formatText($date, 'red') .
                    '    </td>' .
                    '  </tr>' .
                    '  <tr>' .
                    '    <td>' .
                    '      <table align="center" border="0" cellspacing="0" cellpadding="5">';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $this->FORMATTER->formatBoldText('Listings Processed') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getProcessed()) .
                     '          </td>' .
                     '        </tr>';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $spacer .
                     $this->FORMATTER->formatText('Orphans found') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getOrphans()) .
                     '          </td>' .
                     '        </tr>';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $this->FORMATTER->formatBoldText('Images Processed') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getImages()) .
                     '          </td>' .
                     '        </tr>';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $spacer .
                     $this->FORMATTER->formatText('Full') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getRaws()) .
                     '          </td>' .
                     '        </tr>';

          $buffer .= '        <tr>' .
                     '          <td>' .
                     $spacer .
                     $this->FORMATTER->formatText('Thumbnails') .
                     '          </td>' .
                     '          <td align="right">' .
                     $this->FORMATTER->formatText($this->getThumbs()) .
                     '          </td>' .
                     '        </tr>';

          if ($this->start != null) {
               $buffer .= '        <tr>' .
                          '          <td>' .
                          $this->FORMATTER->formatBoldText('Performance') .
                          '          </td>' .
                          '          <td align="right">' .
                          '          </td>' .
                          '        </tr>' .
                          '        <tr>';
               $elapsed = $this->getRunTime();
               if ($elapsed == 0) {
                    $elapsed = 1;
               }
               $count = $this->getProcessed();
               $rate = round($count / $elapsed, 4);
               if ($count == 0) {
                    $speed = 0;
               } else {
                    $speed = round($elapsed / $count, 4);
               }
               $buffer .= '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Total Elapsed Seconds') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($elapsed, 0)) .
                          '          </td>' .
                          '        </tr>';
               if ($RETRIEVER != null) {
                    $networkTime = $RETRIEVER->getNetworkTime();
                    $buffer .= '        <tr valign="bottom">' .
                               '          <td>' .
                               $spacer .
                               $spacer .
                               $this->FORMATTER->formatText('MLS Server') .
                               '          </td>' .
                               '          <td align="right">' .
                               $this->FORMATTER->formatText(number_format($networkTime, 0)) .
                               '          </td>' .
                               '        </tr>' .
                               '        <tr valign="bottom">' .
                               '          <td>' .
                               $spacer .
                               $spacer .
                               $this->FORMATTER->formatText('VieleRETS') .
                               '          </td>' .
                               '          <td align="right">' .
                               $this->FORMATTER->formatText(number_format($elapsed - $networkTime, 0)) .
                               '          </td>' .
                               '        </tr>';
               }

               $buffer .= '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Items per Second') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($rate, 4)) .
                          '          </td>' .
                          '        </tr>' .
                          '        <tr valign="bottom">' .
                          '          <td>' .
                          $spacer .
                          $this->FORMATTER->formatText('Seconds per Item') .
                          '          </td>' .
                          '          <td align="right">' .
                          $this->FORMATTER->formatText(number_format($speed, 4)) .
                          '          </td>' .
                          '        </tr>';
          }

          $buffer .= '      </table>' .
                     '    </td>' .
                     '  </tr>' .
                     '</table>';

          return $buffer;
     }
}

//print_r($vars);
//
// guard against timeouts
//
set_time_limit(0);
//ignore_user_abort(TRUE);
$old_value = ini_set('default_socket_timeout','6000');
//echo '<XMP>J $old_value</XMP>';

//
// begin page
//
$HTML = new HTMLPage();
$HTML->start(PROJECT_NAME . ' Persistant Downloader');

//
// open extract configuration 
//
$EXTRACT = new Extract();
$CONFIGURATION = $EXTRACT->getConfiguration($vars['ELEMENT']);

//
// determine source 
//
$SOURCE = new Source();
$S_CONFIGURATION = $SOURCE->getConfiguration($CONFIGURATION->getValue('SOURCE'));

print('<!-- Extract Table -->' . CRLF);

//
// advanced query
//
$PARSER = null;
$advancedQuery = null;
$aList = $EXTRACT->getExistingControlFiles($vars['ELEMENT']);
if ($aList != null) {
     foreach ($aList as $key => $value) {
          if ($value == $vars['ELEMENT']) {
               $PARSER = new BCFParser();
               $path = $EXTRACT->getControlFilePath($value);
               $PARSER->parse(file_get_contents($path));
               if ($PARSER->hasAdvancedQuery()) {
                    $advancedQuery = $PARSER->getAdvancedQuery();
               }
          }
     }
}

//
// check debug setting 
//
$withDebug = booleanFromArg('WITH_DEBUG', $vars);
$debugDevice = null;
if ($withDebug) {
     $debugDevice = $vars['DEBUG_DEVICE'];
}

//--------------

$DOWNLOADER = new Downloader();
if ($withDebug) {
     $DOWNLOADER->setDebug($withDebug, $debugDevice);
}
//$HTTP_CONTEXT = 'http://' . 
//                $_SERVER['HTTP_HOST'] . 
//                dirname($_SERVER['PHP_SELF']);
$quiet = false;
$DOWNLOADER->processSynchronization($quiet,
                            $vars['ELEMENT'],
                            $vars,
                            $CONFIGURATION,
                            $S_CONFIGURATION,
                            $advancedQuery);

//
// using view.php 
//
print('<!-- Extract Table -->' . CRLF);

$HTML->finish();

function render_top_report()
{
     return '<br/>' .
            '<table align="center" border="1" cellspacing="0" cellpadding="5">' .
            '  <tr align="center">' .
            '    <td>';
}

function render_bottom_report()
{
     return '    </td>' .
            '  </tr>' .
            '</table>';
}

function flush_output($buffer)
{
     print($buffer);
     for($i = 0; $i < 40000; $i++)
     {
          print(' ');
     }
     flush();
}

function printBatchErrors($partial,
                          $listingCount)
{
     $FORMATTER = new TableFormatter(false);
     $error = null;
     if ($partial)
     {
          $error = $FORMATTER->renderError(PARTIAL_RESULT_MESSAGE);
     }
     if ($listingCount == 0)
     {
          $error = $FORMATTER->renderError(NO_ITEMS_MESSAGE);
     }
     if ($error != null)
     {
          print(render_top_report() .
                $error .
                render_bottom_report());
     }
}

function printBatchCompletion()
{
     $FORMATTER = new TableFormatter(false);
     print(render_top_report() .
           $FORMATTER->formatPseudoButton('Return', './index.php') .
           render_bottom_report() .
           '<br/>');
}

function postExecuteStatistics($S_CONFIGURATION,
                               $RETRIEVER)
{ 
//
// gather statistics
//
     $STATS = new StatsFormatter();
     $runtime= $STATS->render($RETRIEVER, $S_CONFIGURATION);
     if ($runtime != null)
     {
          $FORMATTER = new TableFormatter(false);
          print($FORMATTER->renderStats($runtime));
     }

}

//
//------------

?>
