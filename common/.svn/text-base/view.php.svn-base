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
// Debug Setting 
//

if (SCREEN_DEBUG_MODE) {
     echo '<br/><b>Input Arguments:</b><br/><br/>';
     if (sizeof($vars) == 0) {
          print('none<br/>');
     } else {
          foreach ($vars as $key => $value) {
               if (is_array($value)) {
                    print($key . '=');
                    print_r($value);
                    print('<br/>');
               } else {
                    echo $key . '=' . $value . '<br/>';
               }
          } 
     }
}

function locate_next_screen($url) {
     if (!SCREEN_DEBUG_MODE) {
          header('Location: ' . $url);
     } else {
          echo '<br/><b>Transparent Link:</b><br/><br/>' .
               '<a href="' . $url . '">' . $url . '</a>' .
               '<br/>';
     }
     exit;
}

function element_summaries($FORMATTER,
                           $list) {
     if ($list == null) {
          return null;
     }

     $buffer = '<table cellpadding="2" cellspacing="0" border="1">' .
               '<tr>' .
               '<td>' .
               '<table cellpadding="2" cellspacing="0" border="0">';
     foreach ($list as $name => $desc) 
     {
          $buffer .= '<tr>' .
                     '<td>' . 
                     $FORMATTER->formatText($name) . ' ' . 
                     '</td>' .
                     '<td>' .
                     $FORMATTER->formatText('(' . $desc . ')') . 
                     '</td>' .
                     '</tr>';
     }
     $buffer .= '</table>' .
                '</td>' .
                '</tr>' .
                '</table>';
     return $buffer;
}


function renderAboutTable() {
     $size = 12;
     $STYLIST = new Stylist();
     return '<!-- about -->' . CRLF .
            '<table border="0" cellspacing="0" cellpadding="5">' . CRLF .
            '  <tr align="left">' . CRLF .
            '    <td>' .
            '      <img src="' . RESOURCE_DIRECTORY . '/' . PROJECT_LOGO . '" width="150">' .
            '    </td>' . CRLF .
            '    <td>' .
            $STYLIST->formatText(PROJECT_NAME, PROJECT_FONT_COLOR, $size) . '<br/>' .
            $STYLIST->formatText('Version ' . PROJECT_VERSION . ' ' . PROJECT_STATUS, PROJECT_FONT_COLOR, $size) . '<br/>' .
            $STYLIST->formatText(PROJECT_COPYRIGHT, PROJECT_FONT_COLOR, $size) . '<br/>' .
            '    </td>' . CRLF .
            '  </tr>' . CRLF .
            '</table>' . CRLF .
            '<!-- about -->' . CRLF;
}

function displayNoServicePanel($location = null,
                               $message = null) {
     if ($message == null) {
          $message = NO_SERVICE_MESSAGE;
     }

//
// render
//
     $FORMATTER = new TableFormatter();
     $items[] = $FORMATTER->renderError($message);
     if ($location == null) {
          $location = './index.php';
     }
     $FORMATTER->printForm($items, 
                           $location, 
                           'Contact the service provider',
                           null);
     $FORMATTER->finish();
}

class IndexForms 
     extends TableFormatter {
     function IndexForms() {
          parent::TableFormatter(false);
     }

     function checkSystemConfiguration($externalPackagesInstallErrors) {
          $installError = null;
//
// external packages
//
          if (is_array($externalPackagesInstallErrors)) {
               foreach ($externalPackagesInstallErrors as $key => $value) {
                    if ($value) {
                         $installError[] = $value;
                    }
               }
          }

//
// local directories 
//
          if (!file_exists(SOURCE_DIRECTORY)) {
               if (!@mkdir(SOURCE_DIRECTORY, 0777)) {
                    $installError[] = 'SOURCES directory (' . SOURCE_DIRECTORY . ')';
               }
          }

          if (!file_exists(TARGET_DIRECTORY)) {
               if (!@mkdir(TARGET_DIRECTORY, 0777)) {
                    $installError[] = 'TARGETS directory [' . TARGET_DIRECTORY . ']';
               }
          }

          if (!file_exists(EXTRACT_DIRECTORY)) {
               if (!@mkdir(EXTRACT_DIRECTORY, 0777)) {
                    $installError[] = 'EXTRACTS directory [' . EXTRACT_DIRECTORY . ']';
               }
          }

          if (!file_exists(BCF_DIRECTORY)) {
               if (!@mkdir(BCF_DIRECTORY, 0777)) {
                    $installError[] = 'Batch Control File directory [' . BCF_DIRECTORY . ']';
               }
          }

//
// print error 
//
          if ($installError != null) {
               print('<table align="center" cellspacing="0" cellpadding="10" border="1" bgcolor="white">' . CRLF .
                     '  <tr align="center">' . CRLF .
                     '    <td>' . CRLF .
                     renderAboutTable() .
                     '    </td>' . CRLF .
                     '  </tr>' . CRLF .
                     '  <tr align="center">' . CRLF .
                     '    <td>' . CRLF .
                     '<!-- errors -->' . CRLF .
                     '<table border="0" cellspacing="0" cellpadding="5">' . CRLF .
                     '  <tr align="center">' . CRLF .
                     '    <td>' . $this->formatBoldText('Permissions do not allow writing on:', HIGHLIGHT_FONT_COLOR) . '</td>' . CRLF .
                     '  </tr>' . CRLF .
                     '  <tr align="center">' . CRLF .
                     '    <td>' . $this->formatList($installError) . '</td>' . CRLF .
                     '  </tr>' . CRLF .
                     '  <tr align="center">' . CRLF .
                     '    <td>' . $this->formatBoldText('Please correct your configuration', HIGHLIGHT_FONT_COLOR) . '</td>' . CRLF .
                     '  </tr>' . CRLF .
                     '</table>' . CRLF .
                     '<!-- errors -->' . CRLF .
                     '    </td>' . CRLF .
                     '  </tr>' . CRLF .
                     '</table>' . CRLF);

               exit();
          }
     }
}

class VieleForms 
     extends TableFormatter {
     function VieleForms() {
          parent::TableFormatter(true);
     }
 
     function formatQueryFields($query_args,
                                $name_translation,
                                $query_array) { 
          $items = null;
          foreach ($query_args as $key => $val) {
               $visual = $this->translateName($name_translation,
                                              $val);
               $element = null;
               if (array_key_exists($val, $query_array)) {
                    $element = $query_array[$val];
               }
               $items[] = $this->formatEntryField($visual, 
                                                  $val, 
                                                  $element,
                                                  null,
                                                  true,
                                                  true,
                                                  false);
          }
          return $items;
     }

     function formatQueryTable($items) { 

          $item_count = sizeof($items);
          $visible_count = 0;
          for ($i = 0; $i < $item_count; ++$i ) 
          {
               if ($this->priv_is_item_visible($items[$i]))
               {
                    ++$visible_count;
               }
          }

          $buffer = null;
          $isShaded = false;
          for ($i = 0; $i < $item_count; ++$i ) {
               if ($this->priv_is_item_visible($items[$i])) {
                    if ($isShaded) {
                         $aColor = null;
                         $isShaded = false;
                    } else {
                         $aColor = 'bgcolor="white"';
                         if ($visible_count > 2) {
                              $isShaded = true;
                         }
                    }
                    $buffer .= '  <tr align="left" valign="middle" ' .
                               $aColor .
                               '>' . CRLF .
                               '    <td>' . CRLF;
               }
               $buffer .= $items[$i] . CRLF;
               if ($this->priv_is_item_visible($items[$i])) {
                    $buffer .= '    </td>' . CRLF .
                               '  </tr>' . CRLF;
               }
          }

          return '<!-- Content Table -->' . CRLF .
                 $this->renderStartInnerFrameItem() .
                 '<table align="center" cellspacing="' .
                 DATA_SPACING .
                 '" cellpadding="' .
                 DATA_PADDING .
                 '" border="' .
                 DATA_BORDER .
                 '" bgcolor="' .
                 DATA_BACKGROUND_COLOR .
                 '">' . CRLF .
                 $buffer .
                 '</table>' . CRLF .
                 $this->renderEndInnerFrameItem() .
                 '<!-- Content Table -->' . CRLF;
     }

     function formatTimeMessage($last_run) { 
          $pos = strpos($last_run, 'T');
          return '        <tr align="center" valign="middle">' . CRLF .
                 '          <td colspan="2">' . CRLF .
                 $this->formatText('The last update was performed on ') .
                 $this->formatBoldText(substr($last_run, 0, $pos)) .
                 $this->formatText(' at ') . 
                 $this->formatBoldText(substr($last_run, $pos + 1, strlen($last_run))) .
                 $this->formatText(' (GMT).') . CRLF .
                 '          </td>' . CRLF .
                 '        </tr>';
     }

     function formatDebugTable($logFile) {
          $deviceText = 'By default, the debug trace will be sent to {logs} directory.  To change<br/>' .
                        'this, enter the full path name for the debug to be written to.  Use SCREEN<br/>' .
			'to display the results to the browser';
          $debugTable = '<!-- Debug Table -->' . CRLF .
                        '<table align="center"' .
                        ' cellspacing="0"' .
                        ' cellpadding="0"' .
                        ' border="1">' . CRLF .
                        '  <tr align="left" valign="middle">' . CRLF .
                        '    <td>' . CRLF .
                        '      <table align="center" cellspacing="' .
                        DATA_SPACING .
                        '" cellpadding="' .
                        DATA_PADDING .
                        '" border="' .
                        DATA_BORDER .
                        '" bgcolor="' .
                        DATA_BACKGROUND_COLOR .
                        '">' . CRLF .
                        '        <tr align="center" valign="middle">' . CRLF .
                        '          <td colspan="2">' . CRLF .
                        $this->formatBoldText('Debug Settings') .
                        '<br/><br/>' .
                        $this->formatText('These settings only apply to this run and will not be saved.') . CRLF .
                        '          </td>' . CRLF .
                        '        </tr>' . CRLF .
                        '        <tr align="left" valign="middle">' . CRLF .
                        '          <td>' . CRLF .
                        $this->formatBinaryField('WITH_DEBUG',
                                                      'false',
                                                      'With debug on?') . CRLF .
                        '          </td>' . CRLF .
                        '        </tr>' . CRLF .
                        '        <tr align="center" valign="middle">' . CRLF .
                        '      <td colspan="2">' . CRLF .
                        $this->formatText($deviceText) . CRLF .
                        '            </td>' . CRLF .
                        '        </tr>' . CRLF .
                        '        <tr align="left" valign="middle">' . CRLF .
                        '          <td>' . CRLF .
                        $this->formatEntryField('Debug file name',
                                                     'DEBUG_DEVICE',
                                                     LOG_DIRECTORY . '/' . $logFile) . CRLF .
                        '          </td>' . CRLF .
                        '        </tr>' . CRLF .
                        '      </table>' . CRLF .
                        '    </td>' . CRLF .
                        '  </tr>' . CRLF .
                        '</table>' . CRLF .
                        '<!-- Debug Table -->' . CRLF;
          return $debugTable;
     }

}

class ExtractForms 
     extends VieleForms {
     function ExtractForms() {
          parent::VieleForms();
     }

     function renderSpecialForm($name_translation,
                                $query_array,
                                $submit_url = null,
                                $return_url = null,
                                $element_name = null,
                                $top_message = null,
                                $query_items,
                                $date_items,
                                $last_run,
                                $limit,
                                $limit_bcf,
                                $with_updates,
                                $update_bcf,
                                $sync_flag,
				$sync_bcf) {

//
// setup query fields 
//
          $query_args = explode(',', $query_items);
          $items = $this->formatQueryFields($query_args, 
                                            $name_translation,
                                            $query_array);
          $items[] = $this->formatHiddenField('LOCATION', $return_url);
          $items[] = $this->formatHiddenField('ELEMENT', $element_name);
          if ($last_run != null) {
               $items[] = $this->formatHiddenField('LAST_RUN', $last_run);
          }

//
// limit element
//
          $limit_message = 'You can limit the number of listings returned with the next field.<br/>' .
                           'Zero means &quot;all listings&quot;.  The BCF has the limit set to [ ' .
                           $limit_bcf . 
                           ' ] and</br>' .
                           'the EXTRACT has it set to [ ' .
                           $limit .
                           ' ]. The BCF value is the default';
          $limit_element = '        <tr align="center" valign="middle">' . CRLF .
                           '      <td colspan="2">' . CRLF .
                           $this->formatText($limit_message) . CRLF .
                           '           </td>' . CRLF .
                           '        </tr>' . CRLF .
                           '        <tr align="left" valign="middle">' . CRLF .
                           '          <td>' . CRLF .
                           $this->formatEntryField('Set Download Limit',
                                                        'LIMIT',
                                                        $limit_bcf) . CRLF .
                           '          </td>' . CRLF .
                           '        </tr>' . CRLF;

//
// auto synchronize 
//
          if ($sync_flag) {	          
               $sync_message = 'The synchronization process can be run automatically after<br/>' .
                               'the download.<br/>';
               $logFile = 'extract_runtime.log';
               $sync_element = '        <tr align="center" valign="middle" ' .
                               '>' . CRLF .
                               '      <td colspan="2">' . CRLF .
                               $this->formatText($sync_message) . CRLF .
                               '            </td>' . CRLF .
                               '        </tr>' . CRLF .
                               '        <tr align="left" valign="middle" ' .
                               '>' . CRLF .
                               '          <td>' . CRLF .
                               $this->formatBinaryField('AUTO_SYNC',
                                                        $sync_bcf,
                                                        'Run synchronization automatically?') . CRLF .
                               '          </td>' . CRLF .
                               '        </tr>' . CRLF;
          } else {
               $sync_element = null; 
          }

//
//
// update bcf?
//
          $update_message = 'You can have the query and limit settings of this execution<br/>' .
                            'updated in the Batch Control File (BCF).<br/>';
          $update_element = '        <tr align="center" valign="middle" ' .
                            '>' . CRLF .
                            '      <td colspan="2">' . CRLF .
                            $this->formatText($update_message) . CRLF .
                            '            </td>' . CRLF .
                            '        </tr>' . CRLF .
                            '        <tr align="left" valign="middle" ' .
                            '>' . CRLF .
                            '          <td>' . CRLF .
                            $this->formatBinaryField('UPDATE_BCF',
                                                     $update_bcf,
                                                     'Update the Batch Control File?') . CRLF .
                            '          </td>' . CRLF .
                            '        </tr>' . CRLF;

//
// update table
//
          $runtimeTable = '<!-- Runtime Table -->' . CRLF .
                           $this->renderStartInnerFrameItem() .
                           '<!-- BCF Table -->' . CRLF .
                           '<table align="center"' .
                           ' cellspacing="0"' .
                           ' cellpadding="0"' .
                           ' border="1">' . CRLF .
                           '  <tr align="left" valign="middle">' . CRLF .
                           '    <td>' . CRLF .
                           '      <table align="center" cellspacing="' .
                           DATA_SPACING .
                           '" cellpadding="' .
                           DATA_PADDING .
                           '" border="' .
                           DATA_BORDER .
                           '" bgcolor="' .
                           DATA_BACKGROUND_COLOR .
                           '">' . CRLF;
          if ($last_run != null) {

//
// allow DELTA processing is available
//
               $delta_element = null;
               if ($date_items != null) {

//
// should a warning be generated?
//
                    $foundFields = null;
                    $date_args = explode(',', $date_items);
                    foreach ($date_args as $key => $val) {
                         foreach ($query_args as $q_key => $q_val) {
                              if ($q_val == $val) {
                                   $foundFields[] = $val;
                              }
                         }
                    }
                    $date_message  = null;
                    if ($foundFields != null) {
                         $date_message .= '        <tr align="center" valign="middle" ' .
                                          '          <td colspan="2">' . CRLF .
                                          $this->formatText('Entering values into any of the following field(s):') . CRLF .
                                          '          </td>' . CRLF .
                                          '        </tr>' . CRLF;
                                          '        <tr align="left" valign="middle" ' .
                                          '          <td colspan="2">' . CRLF .
                                          '</ul>';
                         foreach ($foundFields as $key => $val) {
                              $date_message .= '<li>' . 
                                          $this->formatText($this->translateName($name_translation, $val)) .
                                          '</li>';
                         }
                         $date_message .= 
//$message . 
                                          '</ul>' . CRLF .
                                          '          </td>' . CRLF .
                                          '         </tr>' . CRLF .
                                          '         <tr align="center" valign="middle" ' .
                                          '          <td colspan="2">' . CRLF .
                                          $this->formatText('will override all updating capabilities of the package.') . CRLF .
                                          '          </td>' . CRLF .
                                          '         </tr>' . CRLF;
                    }
                    $choice_message = "If you ask for updates, only changes since the last update will<br/>" .
                                      "be loaded.  Otherwise, all listings from the server will be loaded.<br/>";
                    $delta_element = '        <tr align="center" valign="middle">' . CRLF .
                                     '          <td colspan="2">' . CRLF .
                                     $this->formatText($choice_message) . CRLF .
                                     '          </td>' . CRLF .
                                     '        </tr>' . CRLF .
                                     $date_message .
                                     '        <tr align="left" valign="middle">' . CRLF .
                                     '          <td>' . CRLF .
                                     $this->formatBinaryField('UPDATE_ONLY',
                                                              $with_updates,
                                                             'Only load updates?') . CRLF .
                                     '          </td>' . CRLF .
                                     '        </tr>' . CRLF;
               }

//
// create table
//
               $runtimeTable .= $this->formatTimeMessage($last_run) . CRLF .
                                $delta_element;
          }
          $runtimeTable .= $limit_element .
                           $sync_element .
                           $update_element .
                           '      </table>' . CRLF .
                           '    </td>' . CRLF .
                           '  </tr>' . CRLF .
                           '</table>' . CRLF .
                           '<!-- BCF Table -->' . CRLF .
                           '<br/>' . CRLF .
                           $this->formatDebugTable('extract_runtime.log') .
                           $this->renderEndInnerFrameItem() .
                           '<!-- Runtime Table -->' . CRLF;

          return '<!-- Form -->' . CRLF .
                 $this->renderStartFrameItem() .
                 $this->renderStartPanel($top_message) .
                 $this->renderStartInnerFrame() .
                 '<form action="' .
                 $submit_url .
                 '" method="POST">' . CRLF .
                 $this->formatQueryTable($items) .
                 $runtimeTable .
                 $this->renderFormButtons() .
                 '</form>' . CRLF .
                 $this->renderEndInnerFrame() .
                 $this->renderEndPanel(ENTER_QUERY_MESSAGE) .
                 $this->renderEndFrameItem() .
                 '<!-- Form -->' . CRLF;
     }
}

class SynchronizeForms 
     extends VieleForms {
     function SynchronizeForms() {
          parent::VieleForms();
     }

     function renderSpecialForm($name_translation,
                                $query_array,
                                $submit_url = null,
                                $return_url = null,
                                $element_name = null,
                                $top_message = null,
                                $query_items,
                                $date_items,
                                $last_run) {

//
// if this has never been run
//
           if ($last_run == null) {
               return '<!-- Form -->' . CRLF .
                      $this->renderStartFrameItem() .
                      $this->renderStartPanel($top_message) .
                      $this->renderStartInnerFrame() .
                      '<form action="' .
                      $submit_url .
                      '" method="POST">' . CRLF .
                      '<!-- BCF Table -->' . CRLF .
                      $this->formatBoldText('An extract has never been run.') . CRLF .
                      '</br>' . CRLF .
                      $this->formatBoldText('You must run an extract before it can be synchronized.') . CRLF .
                      '<!-- BCF Table -->' . CRLF .
                      '<br/>' . CRLF .
                      $this->formatHiddenField('LOCATION', $return_url) .
                      $this->renderFormButtons() .
                      '</form>' . CRLF .
                      $this->renderEndInnerFrame() .
                      $this->renderEndPanel(ENTER_QUERY_MESSAGE) .
                      $this->renderEndFrameItem() .
                      '<!-- Form -->' . CRLF;
                      exit();
          }

//
// setup query fields 
//
          $items = $this->formatQueryFields(explode(',', $query_items),
                                            $name_translation,
                                            $query_array);
          $items[] = $this->formatHiddenField('LOCATION', $return_url);
          $items[] = $this->formatHiddenField('ELEMENT', $element_name);

//
// information about the last run 
//
          $runtimeTable = '<!-- Runtime Table -->' . CRLF .
                           $this->renderStartInnerFrameItem() .
                          '<!-- BCF Table -->' . CRLF .
                          '<table align="center"' .
                          ' cellspacing="0"' .
                          ' cellpadding="0"' .
                          ' border="1">' . CRLF .
                          '  <tr align="left" valign="middle">' . CRLF .
                          '    <td>' . CRLF .
                          '      <table align="center" cellspacing="' .
                          DATA_SPACING .
                          '" cellpadding="' .
                          DATA_PADDING .
                          '" border="' .
                          DATA_BORDER .
                          '" bgcolor="' .
                          DATA_BACKGROUND_COLOR .
                          '">' . CRLF .
                          $this->formatTimeMessage($last_run) . CRLF .
                          '        <tr align="center" valign="middle">' . CRLF .
                          '          <td>' . CRLF .
                          $this->formatText('The query values above were used in the last successful run') . CRLF .
                          '          </td>' . CRLF .
                          '        </tr>' . CRLF .
                          '        <tr align="center" valign="middle">' . CRLF .
                          '          <td>' . CRLF .
                          $this->formatText('Local listings that are NOT found will be REMOVED', 'red') . CRLF .
                          '          </td>' . CRLF .
                          '        </tr>' . CRLF .
                          '      </table>' . CRLF .
                          '    </td>' . CRLF .
                          '  </tr>' . CRLF .
                          '</table>' . CRLF .
                          '<!-- BCF Table -->' . CRLF .
                          '<br/>' . CRLF .
                          $this->formatDebugTable('synchronize_runtime.log') .
                          $this->renderEndInnerFrameItem() .
                          '<!-- Runtime Table -->' . CRLF;

          return '<!-- Form -->' . CRLF .
                 $this->renderStartFrameItem() .
                 $this->renderStartPanel($top_message) .
                 $this->renderStartInnerFrame() .
                 '<form action="' .
                 $submit_url .
                 '" method="POST">' . CRLF .
                 $this->formatQueryTable($items) .
                 $runtimeTable .
                 $this->renderFormButtons() .
                 '</form>' . CRLF .
                 $this->renderEndInnerFrame() .
                 $this->renderEndPanel(ENTER_QUERY_MESSAGE) .
                 $this->renderEndFrameItem() .
                 '<!-- Form -->' . CRLF;
     }
}

?>
