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

class StatsFormatter 
{

     var $STYLIST;

     function StatsFormatter()
     {
          $this->STYLIST = new Stylist();
     }

     function render($model,
                     $CONFIGURATION)
     {
          $perf_table = null;
          if ($CONFIGURATION->getBooleanValue("DISPLAY_PERFORMANCE"))
          {
               $perf_table = "\r\n" .
                             "<!-- Performance Table -->\r\n" .
                             $this->startTable("Performance Analysis") .
                             "  <tr align=\"center\">\r\n" .
                             "    <td>\r\n" .
                             $this->renderRuntimeIdentification($CONFIGURATION) .
                             "\r\n" .
                             $this->renderRuntimePerformance($model) .
                             "\r\n" .
                             $this->STYLIST->formatColumnSeparation() .
                             $this->renderSourceSettings($CONFIGURATION) .
                             "\r\n" .
                             $this->renderQuerySettings($CONFIGURATION) .
                             "\r\n" .
                             "    </td>\r\n" .
                             "  </tr>\r\n" .
                             $this->endTable() .
                             "<!-- Performance Table -->\r\n";
          }

          $display_table = null;
          if ($CONFIGURATION->getBooleanValue("DISPLAY_RETS"))
          {
               $display_table = "\r\n" .
                                "<!-- RETS Table -->\r\n" .
                                $this->startTable("RETS Transactions") .
                                $this->renderRETS("LOGIN", $model) .
                                $this->renderRETS("ACTION", $model) .
                                $this->renderRETS("SEARCH", $model) .
                                $this->renderRETS("GETMETADATA", $model) .
                                $this->renderRETS("GETOBJECT", $model) .
                                $this->renderRETS("LOGOUT", $model) .
                                $this->endTable() .
                                "<!-- RETS Table -->\r\n";
          }

          $provider_table = null;
          if ($CONFIGURATION->getBooleanValue("DISPLAY_PROVIDER_NOTICE"))
          {
               $notice = $model->member->getNotice();
               if (strlen($notice) > 0)
               {
                    $BROWSER = new BrowserProxy();
                    $page = $BROWSER->render($notice);
               }
               else
               {
                    $page = $this->STYLIST->formatBoldBuiltinText("No Provder Notice was Sent", 
                                                                  BUILTIN_TABLE_TEXT_COLOR);
               }

               $provider_table = "\r\n" .
                                 "<!-- Provider Notice Table -->\r\n" .
                                 $this->startTable("Provider Notice") .
                                 "  <tr align=\"center\">\r\n" .
                                 "    <td>\r\n" .
                                 $page .
                                 "    </td>\r\n" .
                                 "  </tr>\r\n" .
                                 $this->endTable() .
                                 "<!-- Provider Notice Table -->\r\n";
          }

          $account_table = null;
          if ($CONFIGURATION->getBooleanValue("DISPLAY_ACCOUNT"))
          {
               $account_table = "\r\n" .
                                "<!-- Account Table -->\r\n" .
                                $this->startTable("Provider Information") .
                                "  <tr align=\"center\">\r\n" .
                                "    <td>\r\n" .
                                $this->startSubTable("Account Information") .
                                $this->renderCellHeadings("Type", "Value") .
                                $this->renderRETSCell("Account Number", 
                                                      $model->member->account,
                                                      true) .
                                $this->renderRETSCell("Member Name", 
                                                      $model->member->name,
                                                      true) .
                                $this->renderRETSCell("Agent ID", 
                                                      $model->member->agentID,
                                                      true) .
                                $this->renderRETSCell("Broker ID", 
                                                      $model->member->brokerID,
                                                      true) .
                                $this->renderRETSCell("User Agent", 
                                                      $model->getUserAgent(),
                                                      true) .
                                $this->endSubTable() .
                                "    </td>\r\n" .
                                "  </tr>\r\n" .
                                $this->endTable() .
                                "<!-- Account Table -->\r\n";
          }

          return $perf_table .
                 $display_table .
                 $provider_table .
                 $account_table;
     }

     function startTable($name)
     {
          return "<br/>\r\n" .
                 "<table align=\"center\" cellspacing=\"" .
                 BUILTIN_SPACING .
                 "\" cellpadding=\"" .
                 BUILTIN_PADDING .
                 "\" border=\"" .
                 BUILTIN_BORDER .
                 "\" bgcolor=\"" .
                 BUILTIN_BACK_COLOR .
                 "\">\r\n" .
                 "  <tr align=\"center\">\r\n" .
                 "    <td colspan=\"2\" bgcolor=\"" .
                 BUILTIN_HEADER_BACK_COLOR .
                 "\">\r\n" .
                 $this->STYLIST->formatBoldBuiltinText($name, 
                                                       BUILTIN_HEADER_TEXT_COLOR) .
                 "\r\n" .
                 "    </td>\r\n" .
                 "  </tr>\r\n";
     }

     function endTable()
     {
          return "</table>\r\n";
     }

     function renderRETS($trans_type, 
                         $model)
     {
          if ($model->urls[$trans_type] &&
              $model->rt[$trans_type] > 0)
          {

//
// get the arguments used for this call
//
               $args = $model->getArgs($trans_type);

               $body = null;
               if (is_array($args))
               {
                    foreach ($args as $key => $val) 
                    {
                         if (strlen($key) < 4)
                         {
                              $key = "ARGS CALL " . $key;
                         }
                         $body .= $this->renderRETSCell($key, urldecode($val));
                    }
               }
               else
               {
                    $body .= $this->renderRETSCell("ARGS", $args);
               }

               return "  <tr align=\"center\">\r\n" .
                      "    <td>\r\n" .
                      $this->startSubTable($trans_type) .
                      $this->renderCellHeadings("Type", "Value") .
                      $this->renderRETSCell("URL", 
                                            $model->urls[$trans_type]) .
                      $body .
                      $this->endSubTable() .
                      "    </td>\r\n" .
                      "  </tr>\r\n";
          }

          return null;
     }

     function renderRETSCell($name, 
                             $value,
                             $show_default = false)
     {
          if ($show_default & !$value)
          {
               $value = "n/a";
          }
          if ($value)
          {
               return "  <tr align=\"left\">\r\n" .
                      "    <td>\r\n" .
                      $this->formatDisplayField($name, 
                                                $value,
                                                BUILTIN_TABLE_TEXT_COLOR) .
                      "\r\n" .
                      "    </td>\r\n" .
                      "  </tr>\r\n";
          }

          return null;
     }

     function startSubTable($name)
     {
          return "<table align=\"center\" cellspacing=\"" .
                 BUILTIN_SPACING .
                 "\" cellpadding=\"" .
                 BUILTIN_PADDING .
                 "\">\r\n" .
                 "  <tr align=\"center\">\r\n" .
                 "    <td>\r\n" .
                 $this->STYLIST->formatBoldBuiltinText($name,
                                                       BUILTIN_TABLE_TEXT_COLOR) .
                 "\r\n" .
                 "    </td>\r\n" .
                 "  </tr>\r\n" .
                 "  <tr align=\"center\">\r\n" .
                 "    <td>\r\n" .
                 "<table align=\"center\" cellspacing=\"" .
                 BUILTIN_SPACING .
                 "\" cellpadding=\"" .
                 BUILTIN_PADDING .
                 "\" border=\"" .
                 BUILTIN_BORDER .
                 "\" bgcolor=\"" .
                 BUILTIN_TABLE_BACK_COLOR .
                 "\">\r\n";
     }

     function endSubTable()
     {
          return "</table>\r\n" .
                 "    </td>\r\n" .
                 "  </tr>\r\n" .
                 "</table>\r\n";
     }

     function renderSourceSettings($CONFIGURATION)
     {
          return $this->startSubTable("Source Parameters") .
                 $this->renderCellHeadings("Setting", "Value") .
                 $this->renderBooleanCell($CONFIGURATION,
                                          "COMPACT_DECODED_FORMAT") .
                 $this->renderBooleanCell($CONFIGURATION,
                                          "SIMULTANEOUS_LOGINS") .
                 $this->renderBooleanCell($CONFIGURATION,
                                          "MEDIA_LOCATION") .
                 $this->renderBooleanCell($CONFIGURATION,
                                          "MEDIA_MULTIPART") .
                 $this->renderBooleanCell($CONFIGURATION,
                                          "TRANSLATE_DESCRIPTIONS") .
                 $this->endSubTable();
     }

     function renderQuerySettings($CONFIGURATION)
     {
          return $this->startSubTable("Query Parameters") .
                 $this->renderCellHeadings("Setting", "Value") .
                 $this->renderStringCell($CONFIGURATION,
                                         "UNIQUE_KEY") .
                 $this->renderStringCell($CONFIGURATION,
                                         "DATE_VARIABLE") .
                 $this->renderStringCell($CONFIGURATION,
                                         "OWNERSHIP_VARIABLE") .
                 $this->renderStringCell($CONFIGURATION,
                                         "MEDIA_TYPE") .
                 $this->renderStringCell($CONFIGURATION,
                                         "SELECTION_RESOURCE") .
                 $this->renderStringCell($CONFIGURATION,
                                         "SELECTION_CLASS") .
                 $this->endSubTable();
     }

     function renderStringCell($CONFIGURATION,
                               $name)
     {
          return "  <tr align=\"left\">\r\n" .
                 "    <td>\r\n" .
                 $this->formatDisplayField($name, 
                                           $CONFIGURATION->getValue($name),
                                           BUILTIN_TABLE_TEXT_COLOR) .
                 "    </td>\r\n" .
                 "  </tr>\r\n";
     }

     function renderBooleanCell($CONFIGURATION,
                                $name)
     {
          $v = "FALSE";
          if ($CONFIGURATION->getBooleanValue($name))
          {
               $v = "TRUE";
          }

          return "  <tr align=\"left\">\r\n" .
                 "    <td>\r\n" .
                 $this->formatDisplayField($name, 
                                           $v,
                                           BUILTIN_TABLE_TEXT_COLOR) .
                 "    </td>\r\n" .
                 "  </tr>\r\n";
     }

     function renderRuntimeIdentification($CONFIGURATION)
     {
          return $this->startSubTable("Server") .
                 $this->renderCellHeadings("Attribute", "Description") .
                 $this->renderStringCell($CONFIGURATION,
                                         "DETECTED_SERVER_NAME") .
                 $this->renderStringCell($CONFIGURATION,
                                         "DETECTED_DEFAULT_RETS_VERSION") .
                 $this->renderStringCell($CONFIGURATION,
                                         "DETECTED_MAXIMUM_RETS_VERSION") .
                 $this->renderStringCell($CONFIGURATION,
                                         "DETECTED_STANDARD_NAMES") .
                 $this->endSubTable();
     }

     function renderRuntimePerformance($model)
     {
          return $this->startSubTable("Timestamps") .
                 $this->renderCellHeadings("Function", "Time (ms)") .
                 $this->renderRuntimeCell("Login", $model->rt["LOGIN"]) .
                 $this->renderRuntimeCell("Action", $model->rt["ACTION"]) .
                 $this->renderRuntimeCell("Search", $model->rt["SEARCH"]) .
                 $this->renderRuntimeCell("GetMetadata", 
                                          $model->rt["GETMETADATA"]) .
                 $this->renderRuntimeCell("GetObject", 
                                          $model->rt["GETOBJECT"]) .
                 $this->renderRuntimeCell("Logout", $model->rt["LOGOUT"]) .
                 $this->renderRuntimeCell("Total Execution", 
                                          $model->rt["TOTAL"]) .
                 $this->endSubTable();
     }

     function renderRuntimeCell($name, 
                                $value)
     {
          if ($value > 0)
          {
               return "  <tr align=\"left\">\r\n" .
                      "    <td>\r\n" .
                      $this->formatDisplayField($name, 
                                                number_format($value, 3),
                                                BUILTIN_TABLE_TEXT_COLOR) .
                      "    </td>\r\n" .
                      "  </tr>\r\n";
          }

          return null;
     }

     function renderTextCell($name, 
                             $value)
     {
          return "  <tr align=\"center\">\r\n" .
                 "    <td>\r\n" .
                 $this->STYLIST->formatBuiltinText($name,
                                                   BUILTIN_TABLE_TEXT_COLOR) .
                 "\r\n" .
                 $this->STYLIST->formatColumnSeparation() .
                 $this->STYLIST->formatBuiltinText($value,
                                                   BUILTIN_TABLE_TEXT_COLOR) .
                 "\r\n" .
                 "    </td>\r\n" .
                 "  </tr>\r\n";
     }

     function renderCellHeadings($name, 
                                 $value)
     {
          return "  <tr align=\"center\">\r\n" .
                 "    <td>\r\n" .
                 $this->STYLIST->formatBoldBuiltinText($name,
                                                       BUILTIN_TABLE_TEXT_COLOR) .
                 "\r\n" .
                 $this->STYLIST->formatColumnSeparation() .
                 $this->STYLIST->formatBoldBuiltinText($value,
                                                       BUILTIN_TABLE_TEXT_COLOR) .
                 "\r\n" .
                 "    </td>\r\n" .
                 "  </tr>\r\n";
     }

     function formatDisplayField($visible_name,
                                 $value,
                                 $override_color = null) 
     {
          return $this->STYLIST->formatBoldBuiltinText($visible_name, 
                                                       $override_color) .
                 "\r\n" .
                 $this->STYLIST->formatColumnSeparation() .
                 $this->STYLIST->formatBuiltinText($value, $override_color);
     }

}

?>
