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
                           $env = null) {

     $trace = null;

//     $trace = print_r($env, true);
//     $trace .= $aName . ' ' . $aValue;

//
// set up defaults
//
     $TARGET = new Target();
     $existing_targets = sizeof($TARGET->getExisting());
     if ($existing_targets == 0) {
          $element = DEFAULT_CONFIG_NAME;
     } else {
          $element = DEFAULT_CONFIG_NAME . '_' . $existing_targets;
     }
     $description = '';
     $aType = '';
     $dataDownloadPath = realpath('../..');
     $fileName = '';
     $includeImages = 'true';
     $imageReferenceOnly = 'false';
     $imageEncodedURL = 'false';
     $imageDownloadPath = realpath('../..');
     $imageFileName = 'download_images.csv';
     $openRealtyInstallPath = '/';
     $brand = '';
     $format = 'RSS_2.0';
     $server = 'localhost';
     $account = 'joe';
     $password = 'schmoe';
     $database = 'viele';
     $dataTable = '';
     $columnList = '';
     $dataTableKey = '';
     $imageTable = '';
     $imageColumnList = '';
     $imageTableKey = '';
     $autoCreate = 'false';
     $containerName = 'VIELE';
     $metadataField = 'listingsformelements_field_name';
     $metadataType = 'listingsformelements_field_type';
     $metadataRequired = 'listingsformelements_required';
     $listingDescriptionTemplate = 'Listing {mls}';
     if ($env == null) {
          $env['ELEMENT'] = $element;
          $env['TYPE'] = 'CSV';
          $env['DESCRIPTION'] = 'Comma-Separated Value File';
          $env['DATA_DOWNLOAD_PATH'] = $dataDownloadPath;
          $env['FILE_NAME'] = 'download_text.csv';
          $env['INCLUDE_IMAGES'] = $includeImages;
          $env['IMAGE_FILE_NAME'] = $imageFileName;
          $env['IMAGE_REFERENCE_ONLY'] = $imageReferenceOnly;
          $env['IMAGE_ENCODED_URL'] = $imageEncodedURL;
          $env['IMAGE_DOWNLOAD_PATH'] = $imageDownloadPath;
          $env['OPEN_REALTY_INSTALL_PATH'] = $openRealtyInstallPath;
          $env['METADATA_FIELD'] = $metadataField;
          $env['METADATA_TYPE'] = $metadataType;
          $env['METADATA_REQUIRED'] = $metadataRequired;
          $env['LISTING_DESCRIPTION_TEMPLATE'] = $listingDescriptionTemplate;
          $env['CONTAINER_NAME'] = $containerName;
     } else {
          if (array_key_exists('viele_mode',$env)) {
               $LOCATION = determine_type($env['ELEMENT-TYPE']);
               $CONFIGURATION = $LOCATION->getConfiguration($env['ELEMENT']);
               $aType = $CONFIGURATION->getValue('TYPE');
               $description = $CONFIGURATION->getValue('DESCRIPTION');
               $dataDownloadPath = $CONFIGURATION->getValue('DATA_DOWNLOAD_PATH');
               $fileName = $CONFIGURATION->getValue('FILE_NAME');
               $includeImages = $CONFIGURATION->getBooleanValue('INCLUDE_IMAGES');
               if (!$includeImages) {
                    $includeImages = 'false';
               } else {
                    $includeImages = 'true';
               }
               $imageReferenceOnly = $CONFIGURATION->getBooleanValue('IMAGE_REFERENCE_ONLY');
               if (!$imageReferenceOnly) {
                    $imageReferenceOnly = 'false';
               } else {
                    $imageReferenceOnly = 'true';
               }
               $imageEncodedURL = $CONFIGURATION->getBooleanValue('IMAGE_ENCODED_URL');
               if (!$imageEncodedURL) {
                    $imageEncodedURL = 'false';
               } else {
                    $imageEncodedURL = 'true';
               }
               $imageDownloadPath = $CONFIGURATION->getValue('IMAGE_DOWNLOAD_PATH');
               $imageFileName = $CONFIGURATION->getValue('IMAGE_FILE_NAME');
               $openRealtyInstallPath = $CONFIGURATION->getValue('OPEN_REALTY_INSTALL_PATH');
               $brand = $CONFIGURATION->getValue('BRAND');
               $server = $CONFIGURATION->getValue('SERVER');
               $account = $CONFIGURATION->getValue('ACCOUNT');
               $password = $CONFIGURATION->getValue('PASSWORD');
               $database = $CONFIGURATION->getValue('DATABASE');
               $dataTable = $CONFIGURATION->getValue('DATA_TABLE');
               $columnList = $CONFIGURATION->getValue('COLUMN_LIST');
               $dataTableKey = $CONFIGURATION->getValue('DATA_TABLE_KEY');
               $imageTable = $CONFIGURATION->getValue('IMAGE_TABLE');
               $imageColumnList = $CONFIGURATION->getValue('IMAGE_COLUMN_LIST');
               $imageTableKey = $CONFIGURATION->getValue('IMAGE_TABLE_KEY');
               $autoCreate = $CONFIGURATION->getBooleanValue('AUTO_CREATE');
               if (!$autoCreate) {
                    $autoCreate = 'false';
               } else {
                    $autoCreate = 'true';
               }
               $metadataField = $CONFIGURATION->getValue('METADATA_FIELD');
               $metadataType = $CONFIGURATION->getValue('METADATA_TYPE');
               $metadataRequired = $CONFIGURATION->getValue('METADATA_REQUIRED');
          }
     }

//
// weight input
//
     if (array_key_exists('TYPE',$env)) {
          $aType = $env['TYPE'];
     }

     if (array_key_exists('AUTO_CREATE',$env)) {
          $autoCreate = $env['AUTO_CREATE'];
     }

     if (array_key_exists('COLUMN_LIST',$env)) {
          if (is_array($env['COLUMN_LIST'])) {
               $columnList = implode(',',$env['COLUMN_LIST']);
          } else {
               $columnList = $env['COLUMN_LIST'];
          }
     }

     if (array_key_exists('INCLUDE_IMAGES',$env)) {
          $includeImages = $env['INCLUDE_IMAGES'];
     }
     if ($includeImages == 'true') {
          if (array_key_exists('IMAGE_REFERENCE_ONLY',$env)) {
               $imageReferenceOnly = $env['IMAGE_REFERENCE_ONLY'];
          }
          if (array_key_exists('IMAGE_ENCODED_URL',$env)) {
               $imageEncodedURL = $env['IMAGE_ENCODED_URL'];
          }
          if (array_key_exists('IMAGE_FILE_NAME',$env)) {
               $imageFileName = $env['IMAGE_FILE_NAME'];
          }
          if (array_key_exists('IMAGE_DOWNLOAD_PATH',$env)) {
               $imageDownloadPath = $env['IMAGE_DOWNLOAD_PATH'];
          }     
          if (array_key_exists('IMAGE_TABLE',$env)) {
               $imageTable = $env['IMAGE_TABLE'];
          }
          if (array_key_exists('IMAGE_TABLE_KEY',$env)) {
               $imageTableKey = $env['IMAGE_TABLE_KEY'];
          }
          if (array_key_exists('IMAGE_COLUMN_LIST',$env)) {
               if (is_array($env['IMAGE_COLUMN_LIST'])) {
                    $imageColumnList = implode(',',$env['IMAGE_COLUMN_LIST']);
               } else {
                    $imageColumnList = $env['IMAGE_COLUMN_LIST'];
               }
          }
     }

     if (array_key_exists('DATA_DOWNLOAD_PATH',$env)) {
          $dataDownloadPath = $env['DATA_DOWNLOAD_PATH'];
     }

     if ($aName == 'TYPE') {
          switch ($aValue) {
               case 'BASE':
                    $description = 'GoogleBase Update File';
                    break;

               case 'CSV':
                    $description = 'Comma-Separated Value File';
                    $fileName = 'download_text.csv';
                    break;

               case 'OR':
                    $description = 'Open-Realty V2.1';
                    break;

               case 'RDB':
                    $description = 'Relational Database';
                    break;

               case 'XML':
                    $description = 'XML Formatted File';
                    $fileName = 'download.xml';
                    break;
          }
     } else {
          if (array_key_exists('DESCRIPTION',$env)) {
               $description = $env['DESCRIPTION'];
          }
          if (array_key_exists('FILE_NAME',$env)) {
               $fileName = $env['FILE_NAME'];
          }
     }

     if (array_key_exists('OPEN_REALTY_INSTALL_PATH',$env)) {
          $openRealtyInstallPath = $env['OPEN_REALTY_INSTALL_PATH'];
     }

     if (array_key_exists('LISTING_DESCRIPTION_TEMPLATE',$env)) {
          $listingDescriptionTemplate = $env['LISTING_DESCRIPTION_TEMPLATE'];
     }

     if (array_key_exists('FORMAT',$env)) {
          $format = $env['FORMAT'];
     }

     if (array_key_exists('BRAND',$env)) {
          $brand = $env['BRAND'];
     }

     if (array_key_exists('SERVER',$env)) {
          $server = $env['SERVER'];
     }

     if (array_key_exists('ACCOUNT',$env)) {
          $account = $env['ACCOUNT'];
     }

     if (array_key_exists('PASSWORD',$env)) {
          $password = $env['PASSWORD'];
     }

     if (array_key_exists('DATABASE',$env)) {
          $database = $env['DATABASE'];
     }

     if (array_key_exists('DATA_TABLE',$env)) {
          $dataTable = $env['DATA_TABLE'];
     }

     if (array_key_exists('DATA_TABLE_KEY',$env)) {
          $dataTableKey = $env['DATA_TABLE_KEY'];
     }

     if (array_key_exists('CONTAINER_NAME',$env)) {
          $containerName = $env['CONTAINER_NAME'];
     }

     $FORMATTER = new AjaxFormatter();

     $blockSubmit = false;
     $items = null;

     $items[] = $FORMATTER->formatSeparator();

     $items[] = $FORMATTER->formatSingleEntryField('Name',
                                                   'ELEMENT',
                                                   $env['ELEMENT'],
                                                   32);

     $options = null;
//     $options['GoogleBase Upload'] = 'BASE';
     $options['CSV'] = 'CSV Formatted File';
     $options['OR'] = 'Open-Realty';
     $options['RDB'] = 'Relational Database';
     $options['XML'] = 'XML Formatted File';
     $items[] = $FORMATTER->formatRadioField('Type of TARGET',
                                             'TYPE',
                                             $aType,
                                             $options,
                                             null,
                                             true,
                                             true);

     switch ($aType) {
          case 'BASE':
               $items[] = $FORMATTER->formatSingleEntryField('Description',
                                                             'DESCRIPTION',
                                                             $description,
                                                             32);

               $items[] = $FORMATTER->formatPathField('Text download directory',
                                                      'DATA_DOWNLOAD_PATH',
                                                      $dataDownloadPath,
                                                      32);

               $items[] = $FORMATTER->formatSingleEntryField('Name of the file for text data',
                                                             'FILE_NAME',
                                                             $fileName,
                                                             32);

               $options = null;
               $options['RSS Version 2.0'] = 'RSS_2.0';
               $options['RSS Version 1.0'] = 'RSS Version 1.0';
               $options['Atom Version 0.3'] = 'Atom Version 0.3';
               $options['Tab Seperated Value'] = 'Tab Seperated Value';
               $items[] = $FORMATTER->formatSelectField('Format of the File',
                                                        'FORMAT',
                                                        $format,
                                                        $options);

               $items[] = $FORMATTER->formatSelectField('Columns to use for text data',
                                                        'COLUMN_LIST',
                                                        $columnList,
                                                        null,
                                                        null,
                                                        true,
                                                        false,
                                                        null);

               $items[] = $FORMATTER->formatBinaryField('Include Images',
                                                        'INCLUDE_IMAGES',
                                                        $includeImages);
               if ($includeImages == 'true') {
                    $items[] = $FORMATTER->formatHiddenField('IMAGE_REFERENCE_ONLY', 'true');

                    $items[] = $FORMATTER->formatPathField('Image download directory',
                                                           'IMAGE_DOWNLOAD_PATH',
                                                           $imageDownloadPath,
                                                           32);
               }

               $items[] = $FORMATTER->formatHiddenField('ACCOUNT', $account);
               $items[] = $FORMATTER->formatHiddenField('AUTO_CREATE', $autoCreate);
               $items[] = $FORMATTER->formatHiddenField('BRAND', $brand);
               $items[] = $FORMATTER->formatHiddenField('CONTAINER_NAME', $containerName);
               $items[] = $FORMATTER->formatHiddenField('DATA_TABLE', $dataTable);
               $items[] = $FORMATTER->formatHiddenField('DATABASE', $database);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_COLUMN_LIST', $imageColumnList);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_ENCODED_URL', $imageEncodedURL);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_FILE_NAME', $imageFileName);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE', $imageTable);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE_KEY', $imageTableKey);
               $items[] = $FORMATTER->formatHiddenField('LISTING_DESCRIPTION_TEMPLATE', $listingDescriptionTemplate);
               $items[] = $FORMATTER->formatHiddenField('METADATA_FIELD', $metadataField);
               $items[] = $FORMATTER->formatHiddenField('METADATA_REQUIRED', $metadataRequired);
               $items[] = $FORMATTER->formatHiddenField('METADATA_TYPE', $metadataType);
               $items[] = $FORMATTER->formatHiddenField('OPEN_REALTY_INSTALL_PATH', $openRealtyInstallPath);
               $items[] = $FORMATTER->formatHiddenField('PASSWORD', $password);
               $items[] = $FORMATTER->formatHiddenField('SERVER', $server);

               break;

          case 'CSV':

               $items[] = $FORMATTER->formatSingleEntryField('Description',
                                                             'DESCRIPTION',
                                                             $description,
                                                             32);

               $items[] = $FORMATTER->formatSeparator('Where to Write Text Data');

               $items[] = $FORMATTER->formatPathField('Text download directory',
                                                      'DATA_DOWNLOAD_PATH',
                                                       $dataDownloadPath,
                                                       32);
               if (!is_writable($dataDownloadPath)) {
                    $blockSubmit = true;
               }

               $statusColor = null;
               if (strlen($fileName) == 0) {
                    $statusColor = 'red';
                    $blockSubmit = true;
               }
               $items[] = $FORMATTER->formatSingleEntryField('Name of the file for text data',
                                                             'FILE_NAME',
                                                             $fileName,
                                                             32,
                                                             $statusColor);

               $items[] = $FORMATTER->formatSeparator('Where to Write Image Data');

               $items[] = $FORMATTER->formatBinaryField('Include Images',
                                                        'INCLUDE_IMAGES',
                                                        $includeImages);
               if ($includeImages == 'true') {
                    $items[] = $FORMATTER->formatBinaryField('Encode Image URLs before storing',
                                                             'IMAGE_ENCODED_URL',
                                                             $imageEncodedURL);

                    $items[] = $FORMATTER->formatBinaryField('Use Image References, not Images',
                                                            'IMAGE_REFERENCE_ONLY',
                                                             $imageReferenceOnly);

                    if ($imageReferenceOnly == 'false') {
                         $items[] = $FORMATTER->formatPathField('Image download directory',
                                                                'IMAGE_DOWNLOAD_PATH',
                                                                $imageDownloadPath,
                                                                32);
                         if (!is_writable($imageDownloadPath)) {
                              $blockSubmit = true;
                         }
                    } 

                    $statusColor = null;
                    if (strlen($imageFileName) == 0) {
                         $statusColor = 'red';
                         $blockSubmit = true;
                    }
                    $items[] = $FORMATTER->formatSingleEntryField('Name of the file for images',
                                                                  'IMAGE_FILE_NAME',
                                                                  $imageFileName,
                                                                  32,
                                                                  $statusColor);
               }

               $items[] = $FORMATTER->formatHiddenField('ACCOUNT', $account);
               $items[] = $FORMATTER->formatHiddenField('AUTO_CREATE', $autoCreate);
               $items[] = $FORMATTER->formatHiddenField('BRAND', $brand);
               $items[] = $FORMATTER->formatHiddenField('COLUMN_LIST', $columnList);
               $items[] = $FORMATTER->formatHiddenField('CONTAINER_NAME', $containerName);
               $items[] = $FORMATTER->formatHiddenField('DATA_TABLE', $dataTable);
               $items[] = $FORMATTER->formatHiddenField('DATABASE', $database);
               $items[] = $FORMATTER->formatHiddenField('FORMAT', $format);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_COLUMN_LIST', $imageColumnList);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE', $imageTable);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE_KEY', $imageTableKey);
               $items[] = $FORMATTER->formatHiddenField('LISTING_DESCRIPTION_TEMPLATE', $listingDescriptionTemplate);
               $items[] = $FORMATTER->formatHiddenField('METADATA_FIELD', $metadataField);
               $items[] = $FORMATTER->formatHiddenField('METADATA_REQUIRED', $metadataRequired);
               $items[] = $FORMATTER->formatHiddenField('METADATA_TYPE', $metadataType);
               $items[] = $FORMATTER->formatHiddenField('OPEN_REALTY_INSTALL_PATH', $openRealtyInstallPath);
               $items[] = $FORMATTER->formatHiddenField('PASSWORD', $password);
               $items[] = $FORMATTER->formatHiddenField('SERVER', $server);

               break;

          case 'OR':
//               $items[] = $FORMATTER->formatSingleEntryField('Description',
//                                                             'DESCRIPTION',
//                                                             $description,
//                                                             32);

               $items[] = $FORMATTER->formatSeparator('Installation of Open-Realty');

               $or_common_file_name = '/include/common.php';
               $file_name = $openRealtyInstallPath . $or_common_file_name;
               if (file_exists($file_name)) {
                    $items[] = $FORMATTER->formatPathField('Full path to Open-Realty install',
                                                           'OPEN_REALTY_INSTALL_PATH',
                                                           $openRealtyInstallPath,
                                                           32,
                                                           true);
                    if (function_exists('mysql_pconnect')) {

//------------------
     // get contents of a file into a string
     $fd = fopen ($file_name, 'r');
     $contents = fread ($fd, filesize ($file_name));
     fclose ($fd);
     preg_match('/db_server = "(.*?)"/is',$contents,$matches);
     $db_server = $matches[1];
     preg_match('/db_user = "(.*?)"/is',$contents,$matches);
     $db_user = $matches[1];
     preg_match('/db_password = "(.*?)"/is',$contents,$matches);
     $db_password = $matches[1];
     preg_match('/db_database = "(.*?)"/is',$contents,$matches);
     $db_database = $matches[1];
     preg_match('/db_type = "(.*?)"/is',$contents,$matches);
     $db_type = $matches[1];
     preg_match('/config\["table_prefix_no_lang"\] = "(.*?)"/is',$contents,$matches);
     $table_prefix_no_lang = $matches[1];
//------------------
                         $conn = ADONewConnection($db_type);
                         @$conn->PConnect($db_server, $db_user, $db_password, $db_database);
                         if ($conn->isConnected()) {
                              $items[] = $FORMATTER->formatHiddenField('BRAND', $db_type);

                              $items[] = $FORMATTER->formatHiddenField('SERVER', $db_server);

                              $items[] = $FORMATTER->formatHiddenField('ACCOUNT', $db_user);

                              $items[] = $FORMATTER->formatHiddenField('PASSWORD', $db_password);

                              $items[] = $FORMATTER->formatHiddenField('DATABASE', $db_database);

//------------------
//
// Open open-Realty Configuration Table
// Loop throught Control Panel and save to Array
//

     $sql = 'SELECT * FROM '.$table_prefix_no_lang.'controlpanel';
     $recordSet = $conn->Execute($sql);
     $found = false;
     if ($recordSet) {
          $config['version'] = $recordSet->fields['controlpanel_version'];
          $config['basepath'] = $recordSet->fields['controlpanel_basepath'];
//          $config['baseurl'] = $recordSet->fields['controlpanel_baseurl'];
//          $config['admin_name'] = $recordSet->fields['controlpanel_admin_name'];
//          $config['admin_email'] = $recordSet->fields['controlpanel_admin_email'];
//          $config['site_title'] = $recordSet->fields['controlpanel_site_title'];
//          $config['company_name'] = $recordSet->fields['controlpanel_company_name'];
//          $config['company_location'] = $recordSet->fields['controlpanel_company_location'];
//          $config['company_logo'] = $recordSet->fields['controlpanel_company_logo'];
//          $config['automatic_update_check'] = $recordSet->fields['controlpanel_automatic_update_check'];
//          $config['url_style'] = $recordSet->fields['controlpanel_url_style'];
//          $config['template'] = $recordSet->fields['controlpanel_template'];
//          $config['admin_template'] = $recordSet->fields['controlpanel_admin_template'];
//          $config['listing_template'] = $recordSet->fields['controlpanel_listing_template'];
//          $config['search_result_template'] = $recordSet->fields['controlpanel_search_result_template'];
          $config['lang'] = $recordSet->fields['controlpanel_lang'];
//          $config['listings_per_page'] = $recordSet->fields['controlpanel_listings_per_page'];
//          $config['add_linefeeds'] = $recordSet->fields['controlpanel_add_linefeeds'];
//          $config['strip_html'] = $recordSet->fields['controlpanel_strip_html'];
//          $config['allowed_html_tags'] = $recordSet->fields['controlpanel_allowed_html_tags'];
//          $config['money_sign'] = $recordSet->fields['controlpanel_money_sign'];
//          $config['show_no_photo'] = $recordSet->fields['controlpanel_show_no_photo'];
//          $config['number_format_style'] = $recordSet->fields['controlpanel_number_format_style'];
//          $config['number_decimals_number_fields'] = $recordSet->fields['controlpanel_number_decimals_number_fields'];
//          $config['number_decimals_price_fields'] = $recordSet->fields['controlpanel_number_decimals_price_fields'];
//          $config['money_format'] = $recordSet->fields['controlpanel_money_format'];
//          $config['date_format'] = $recordSet->fields['controlpanel_date_format'];
//          $date_format[1] = 'mm/dd/yyyy';
//          $date_format[2] = 'yyyy/dd/mm';
//          $date_format[3] = 'dd/mm/yyyy';
//          $date_format_timestamp[1] = 'm/d/Y';
//          $date_format_timestamp[2] = 'Y/d/m';
//          $date_format_timestamp[3] = 'd/m/Y';
//          $config['date_format_long'] = $date_format[$config['date_format']];
//          $config['date_format_timestamp'] = $date_format_timestamp[$config['date_format']];
//          $config['max_listings_uploads'] = $recordSet->fields['controlpanel_max_listings_uploads'];
//          $config['max_listings_upload_size'] = $recordSet->fields['controlpanel_max_listings_upload_size'];
//          $config['max_listings_upload_width'] = $recordSet->fields['controlpanel_max_listings_upload_width'];
//          $config['max_user_uploads'] = $recordSet->fields['controlpanel_max_user_uploads'];
//          $config['max_user_upload_size'] = $recordSet->fields['controlpanel_max_user_upload_size'];
//          $config['max_user_upload_width'] = $recordSet->fields['controlpanel_max_user_upload_width'];
//          $config['max_vtour_uploads'] = $recordSet->fields['controlpanel_max_vtour_uploads'];
//          $config['max_vtour_upload_size'] = $recordSet->fields['controlpanel_max_vtour_upload_size'];
//          $config['max_vtour_upload_width'] = $recordSet->fields['controlpanel_max_vtour_upload_width'];
//          $config['allowed_upload_extensions'] = $recordSet->fields['controlpanel_allowed_upload_extensions'];
          $config['make_thumbnail'] = $recordSet->fields['controlpanel_make_thumbnail'];
          $config['thumbnail_width'] = $recordSet->fields['controlpanel_thumbnail_width'];
//          $config['gdversion2'] = $recordSet->fields['controlpanel_gd_version'];
          $config['thumbnail_prog'] = $recordSet->fields['controlpanel_thumbnail_prog'];
//          $config['path_to_imagemagick'] = $recordSet->fields['controlpanel_path_to_imagemagick'];
//          $config['resize_img'] = $recordSet->fields['controlpanel_resize_img'];
          $config['jpeg_quality'] = $recordSet->fields['controlpanel_jpeg_quality'];
//          $config['use_expiration'] = $recordSet->fields['controlpanel_use_expiration'];
          $config['days_until_listings_expire'] = $recordSet->fields['controlpanel_days_until_listings_expire'];
//          $config['allow_member_signup'] = $recordSet->fields['controlpanel_allow_member_signup'];
//          $config['allow_agent_signup'] = $recordSet->fields['controlpanel_allow_agent_signup'];
//          $config['agent_default_active'] = $recordSet->fields['controlpanel_agent_default_active'];
//          $config['agent_default_admin'] = $recordSet->fields['controlpanel_agent_default_admin'];
//          $config['agent_default_feature'] = $recordSet->fields['controlpanel_agent_default_feature'];
//          $config['agent_default_moderate'] = $recordSet->fields['controlpanel_agent_default_moderate'];
//          $config['agent_default_logview'] = $recordSet->fields['controlpanel_agent_default_logview'];
//          $config['agent_default_canChangeExpirations'] = $recordSet->fields['controlpanel_agent_default_canchangeexpirations'];
//          $config['agent_default_editpages'] = $recordSet->fields['controlpanel_agent_default_editpages'];
//          $config['agent_default_havevtours'] = $recordSet->fields['controlpanel_agent_default_havevtours'];
//          $config['agent_default_num_listings'] = $recordSet->fields['controlpanel_agent_default_num_listings'];
//          $config['moderate_agents'] = $recordSet->fields['controlpanel_moderate_agents'];
//          $config['moderate_members'] = $recordSet->fields['controlpanel_moderate_members'];
//          $config['moderate_listings'] = $recordSet->fields['controlpanel_moderate_listings'];
//          $config['email_notification_of_new_users'] = $recordSet->fields['controlpanel_email_notification_of_new_users'];
//          $config['email_notification_of_new_listings'] = $recordSet->fields['controlpanel_email_notification_of_new_listings'];
//          $config['allowed_upload_types'] = $recordSet->fields['controlpanel_allowed_upload_types'];
//          $config['configured_langs'] = $recordSet->fields['controlpanel_configured_langs'];
//          $config['configured_show_count'] = $recordSet->fields['controlpanel_configured_show_count'];
//          $config['sortby'] = $recordSet->fields['controlpanel_search_sortby'];
//          $config['sorttype'] = $recordSet->fields['controlpanel_search_sorttype'];
//          $config['email_users_notification_of_new_listings'] = $recordSet->fields['controlpanel_email_users_notification_of_new_listings'];
//          $config['num_featured_listings'] = $recordSet->fields['controlpanel_num_featured_listings'];
//          $config['map_type'] = $recordSet->fields['controlpanel_map_type'];
//          $config['map_address'] =$recordSet->fields['controlpanel_map_address'];
//          $config['map_city'] = $recordSet->fields['controlpanel_map_city'];
//          $config['map_state'] = $recordSet->fields['controlpanel_map_state'];
//          $config['map_zip'] = $recordSet->fields['controlpanel_map_zip'];
//          $config['wysiwyg_editor'] = $recordSet->fields['controlpanel_wysiwyg_editor'];
//          $config['wysiwyg_execute_php'] = $recordSet->fields['controlpanel_wysiwyg_execute_php'];
//Determine which table to use based on language
          $config['table_prefix'] = $table_prefix_no_lang . '$config[lang]_';
          if (!isset($_SESSION['users_lang'])) {
               $config['lang_table_prefix'] = $table_prefix_no_lang."$config[lang]_";
          } else {
               $config['lang_table_prefix'] = $table_prefix_no_lang."$_SESSION[users_lang]_";
          }
///////////////////////////////////////////////////
// Path Settings
// These Paths are set based on setting in the control panel
//          $config['path_to_thumbnailer'] = $config['basepath'].'/include/thumbnail'.$config['thumbnail_prog'].'.php'; // path to the thumnailing tool
//          $config['template_path'] = $config['basepath'].'/template/'.$config['template']; // leave off the trailing slashes
//          $config['template_url'] = $config['baseurl'].'/template/'.$config['template']; // leave off the trailing slashes
//          $config['admin_template_path'] = $config['basepath'].'/admin/template/'.$config['admin_template']; // leave off the trailing slashes
//          $config['admin_template_url'] = $config['baseurl'].'/admin/template/'.$config['admin_template']; // leave off the trailing slashes
///////////////////////////////////////////////////
// MISCELLENEOUS SETTINGS
// you shouldn't have to mess with these things unless you rename a folder, etc...
          $config['listings_upload_path'] = $config['basepath'].'/images/listing_photos';
//          $config['listings_view_images_path'] = $config['baseurl'].'/images/listing_photos';
//          $config['user_upload_path'] = $config['basepath'].'/images/user_photos';
//          $config['user_view_images_path'] = $config['baseurl'].'/images/user_photos';
//          $config['vtour_upload_path'] = $config['basepath'].'/images/vtour_photos';
//          $config['vtour_view_images_path'] = $config['baseurl'].'/images/vtour_photos';
//
// version
//
          $items[] = $FORMATTER->formatHiddenField('DESCRIPTION', 
                                                   'Open-Realty V' . $config['version']);
          $items[] = $FORMATTER->formatDisplayField('Installed Version',
                                                    $config['version'],
                                                    'green');

//
// discover metadata 
//
          $dbPrefix = $config['lang_table_prefix'];
//     $trace = 'dbprefix ' . $dbPrefix;

//
// user table
//
          $userTable = $dbPrefix . 'userdb';
          $items[] = $FORMATTER->formatHiddenField('USER_TABLE', $userTable);

//
// class table
//
          $classTable = $dbPrefix . 'class';

//
// RETS account table
//
          $userElementTable = $dbPrefix . 'userdbelements';

//
// form table
//
          $formTable = $dbPrefix . 'listingsformelements';
          $items[] = $FORMATTER->formatHiddenField('METADATA_TABLE', $formTable);

//
// dependent metadata
//
          $discoveredColumns = null;
          $discoveredTypes = null;
          $discoveredRequired = null;
          $tables = $conn->MetaTables('TABLES');
          foreach ($tables as $key => $value) {
               switch ($value) {
                    case $classTable:
//
// OR Version 2.1 only
//
                         $items[] = $FORMATTER->formatHiddenField('CLASS_TABLE', $classTable);
                         $items[] = $FORMATTER->formatHiddenField('CLASS_LISTING_TABLE',
                                                                  $table_prefix_no_lang . 'classlistingsdb');
                         break;

                    case $userElementTable:
//
// OR Version 2.1 only
//
                         $items[] = $FORMATTER->formatHiddenField('USER_ELEMENT_TABLE', $userElementTable);
                         break;

                    case $formTable:
                         $found = true;
                         $sql = 'SELECT ' .
                                $metadataField . ',' .
                                $metadataType . ',' .
                                $metadataRequired .
                                ' FROM ' .
                                $formTable;
//$trace .= 'SQL: ' . $sql;
                         $recordSet = $conn->Execute($sql);
                         if ($recordSet == null) {
$trace .= ' ERROR: Trouble talking to the OR database.  SQL: ' . $sql;
                         } else {
                              if ($recordSet === false) {
$trace .= ' ERROR: No fields defined in OR database.  SQL: ' . $sql;
                              }
                              while (!$recordSet->EOF) {
                                   $discoveredColumns .= $recordSet->fields[$metadataField] . ',';
                                   $discoveredTypes .= $recordSet->fields[$metadataType] . ',';
                                   $discoveredRequired .= $recordSet->fields[$metadataRequired] . ',';
                                   $recordSet->MoveNext();
                              }
                              if (strlen($discoveredColumns) > 0) {
                                   $discoveredColumns = substr($discoveredColumns, 0, strlen($discoveredColumns)-1);
                                   $discoveredTypes = substr($discoveredTypes, 0, strlen($discoveredTypes)-1);
                                   $discoveredRequired = substr($discoveredRequired, 0, strlen($discoveredRequired)-1);
                              }
                         }
               }
          }

//
// metadata processing
//
          if ($found) {
               if (is_writable($config['listings_upload_path'])) {
                    $items[] = specialDisplay($FORMATTER, 
                                              $discoveredColumns,
                                              'Template for Listing Description',
                                              'LISTING_DESCRIPTION_TEMPLATE',
                                              $listingDescriptionTemplate);

//
// note fields that have been added or removed
//
                    if ($columnList != '') {
                         $existingColumns = explode(',', $columnList);
                         $availableColumns = explode(',', $discoveredColumns);
                         if (sizeof($existingColumns) != sizeof($availableColumns) ) {
                              $addedColumns = '';
                              foreach ($availableColumns as $key => $value) {
                                   $found = false;
                                   foreach ($existingColumns as $key1 => $value1) {
                                        if ($value == $value1) {
                                             $found = true;
                                             break;
                                        }
                                   }
                                   if (!$found) {
                                        $addedColumns .= $value . ',';
                                   }
                              }
                              if ($addedColumns != '') {
                                   $addedColumns = substr($addedColumns, 0 , strlen($addedColumns)-1);
                                   $items[] = $FORMATTER->formatDisplayField('Columns now available',
                                                                             $addedColumns,
                                                                             'green');
                              }
                              $removedColumns = '';
                              foreach ($existingColumns as $key => $value) {
                                   $found = false;
                                   foreach ($availableColumns as $key1 => $value1) {
                                        if ($value == $value1) {
                                             $found = true;
                                             break;
                                        }
                                   }
                                   if (!$found) {
                                        $removedColumns .= $value . ',';
                                   }
                              }
                              if ($removedColumns != '') {
                                   $removedColumns = substr($removedColumns, 0 , strlen($removedColumns)-1);
                                   $items[] = $FORMATTER->formatDisplayField('Columns no longer available',
                                                                             $removedColumns,
                                                                             'red');
                              }
                         }
                    }

                    $items[] = $FORMATTER->formatHiddenField('COLUMN_LIST', $discoveredColumns);

                    $items[] = $FORMATTER->formatHiddenField('TYPE_LIST', $discoveredTypes);

//
// required fields 
//
                    $temp_required = explode(',',$discoveredRequired);
                    $is_required = null;
                    $temp_name = explode(',',$discoveredColumns);
                    foreach ($temp_name as $key => $value) {
                         if ($temp_required[$key] == 'Yes') {
                              $is_required[] = $value;
                         }
                    }
                    if ($is_required != null) {
                         $list = implode(',', $is_required);
                         $items[] = $FORMATTER->formatHiddenField('REQUIRED_LIST', $list);
                    }

//
// metadata
//
                    $items[] = $FORMATTER->formatHiddenField('METADATA_FIELD', $metadataField);
                    $items[] = $FORMATTER->formatHiddenField('METADATA_TYPE', $metadataType);
                    $items[] = $FORMATTER->formatHiddenField('METADATA_REQUIRED', $metadataRequired);

//
// data
//
                    $items[] = $FORMATTER->formatHiddenField('INDEX_TABLE', $dbPrefix . 'listingsdb');
                    $items[] = $FORMATTER->formatHiddenField('DATA_TABLE', $dbPrefix . 'listingsdbelements');

//
// images
//
                    $items[] = $FORMATTER->formatHiddenField('INCLUDE_IMAGES', 'false');
                    $items[] = $FORMATTER->formatHiddenField('DAYS_UNTIL_EXPIRATION', $config['days_until_listings_expire']);
                    $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE', $dbPrefix . 'listingsimages');
                    $items[] = $FORMATTER->formatHiddenField('IMAGE_UPLOAD_PATH', $config['listings_upload_path']);

                    if ($config['make_thumbnail']) {
                         $items[] = $FORMATTER->formatHiddenField('THUMBNAILS', 'true');
                         $items[] = $FORMATTER->formatHiddenField('THUMBNAIL_PROGRAM', $config['thumbnail_prog']);

                         if ($config['thumbnail_prog'] == 'gd') {
//
// an alternative to config['gdversion2']
//
                              $ver = gdVersion();
                              if ($ver == 2) {
                                   $items[] = $FORMATTER->formatHiddenField('GD_VERSION_2', 'true');
                              } else {
                                  $items[] = $FORMATTER->formatHiddenField('GD_VERSION_2', 'false');
                              }
                              $items[] = $FORMATTER->formatHiddenField('THUMBNAIL_QUALITY', $config['jpeg_quality']);
                         } else {
//
// an alternative to config['path_to_imagemagick']
//
                              $cmd = 'which convert';
                              $path = syscall($cmd);
                              $items[] = $FORMATTER->formatHiddenField('PATH_TO_IMAGEMAGICK', $path);
                         }
                         $items[] = $FORMATTER->formatHiddenField('THUMBNAIL_WIDTH', $config['thumbnail_width']);
                    } else {
                         $items[] = $FORMATTER->formatHiddenField('THUMBNAILS', 'false');
                    }
               } else {
                    $items[] = $FORMATTER->formatDisplayField('Open-Realty Installation',
                                                              'Cannot write to [' . $config['listings_upload_path'] .  '].<br/>File permissions for Open-Realty may be blocking write.', 
                                                              'red');
                    $blockSubmit = true;
               }
          } else {
               $items[] = $FORMATTER->formatDisplayField('Open-Realty Installation',
                                                         'Cannot read metadata.<br/>Maybe you have an unsupported version',
                                                         'red');
               $blockSubmit = true;
          }
     } else {
          $items[] = $FORMATTER->formatDisplayField('Open-Realty Installation',
                                                    'Cannot read control panel.<br/>Maybe you have an unsupported version',
                                                    'red');
          $blockSubmit = true;
     }
//------------------
                         }

                         $conn->Close();
                    } else {
                         $items[] = $FORMATTER->formatDisplayField('PHP Installation',
                                                                   'You need the php_mysql package installed for this function',
                                                                   'red');
                         $blockSubmit = true;
                    }
               } else {
//$trace .= ' Cannot find the OR installation file.  File: ' . $file_name;
                    $items[] = $FORMATTER->formatPathField('Full path to Open-Realty install. ' .
                                                           'If the file<br/><br/>' .
                                                           '[PATH]' .
                                                           $or_common_file_name . '<br/><br/> exists' .
                                                           ' and is readable, you have' .
                                                           ' identified the Open-Realty' .
                                                           ' installation.',
                                                           'OPEN_REALTY_INSTALL_PATH',
                                                           $openRealtyInstallPath,
                                                           32,
                                                           true,
                                                           3,
                                                           'red');
                    $blockSubmit = true;
               }

               $items[] = $FORMATTER->formatHiddenField('AUTO_CREATE', $autoCreate);
               $items[] = $FORMATTER->formatHiddenField('CONTAINER_NAME', $containerName);
               $items[] = $FORMATTER->formatHiddenField('DATA_DOWNLOAD_PATH', $dataDownloadPath);
               $items[] = $FORMATTER->formatHiddenField('FILE_NAME', $fileName);
               $items[] = $FORMATTER->formatHiddenField('FORMAT', $format);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_COLUMN_LIST', $imageColumnList);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_DOWNLOAD_PATH', $imageDownloadPath);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_ENCODED_URL', $imageEncodedURL);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_FILE_NAME', $imageFileName);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_REFERENCE_ONLY', $imageReferenceOnly);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE_KEY', $imageTableKey);

               break;

          case 'RDB':
               $items[] = $FORMATTER->formatSingleEntryField('Description',
                                                             'DESCRIPTION',
                                                             $description,
                                                             32);

               $items[] = $FORMATTER->formatSeparator('Database Server');

               $options = null;
               $options['mysql'] = 'MySQL';
               $options['postgres'] = 'Postgres';
               $options['oracle'] = 'Oracle';
               $options['access'] = 'MS Access';
               $options['mssql'] = 'MS SQLServer';
               $items[] = $FORMATTER->formatSelectField('Database "Brand"',
                                                        'BRAND',
                                                        $brand,
                                                        $options);
               if($brand != null) {
                    if (function_exists('mysql_pconnect')) {
                         $conn = ADONewConnection($brand);
                         @$conn->PConnect($server, $account, $password, $database);
//$trace = print_r($conn, true);
//$trace .= 'server ' . $junk;
                         if ($conn->isConnected()) {
//                              $temp = $conn->MetaTables('TABLES');
//                              if ($temp == null) {
                              $items[] = $FORMATTER->formatSingleEntryField('Server Name',
                                                                            'SERVER',
                                                                            $server,
                                                                            20);

                              $items[] = $FORMATTER->formatSingleEntryField('Account Name',
                                                                            'ACCOUNT',
                                                                            $account,
                                                                            20);

                              $items[] = $FORMATTER->formatSingleEntryField('Password',
                                                                            'PASSWORD',
                                                                            $password,
                                                                            20);

                              $items[] = $FORMATTER->formatSingleEntryField('Database',
                                                                            'DATABASE',
                                                                            $database,
                                                                            20);

                              $tables = null;
                              $temp = $conn->MetaTables('TABLES');
                              foreach ($temp as $key => $value) {
                                   $tables[$value] = $value;
                              }
                              if ($tables == null) {
                                   $autoCreate = 'true';
                              }

                              $items[] = $FORMATTER->formatBinaryField("Create Tables if they don't exist",
                                                                       'AUTO_CREATE',
                                                                       $autoCreate);

                              $items[] = $FORMATTER->formatSeparator('Where to Write Text Data');

                              if ($autoCreate == 'false') {
                                   $items[] = $FORMATTER->formatSelectField('Table name for text data',
                                                                            'DATA_TABLE',
                                                                            $dataTable,
                                                                            $tables);
                                   if ($dataTable != '') {
                                        $temp = $conn->MetaColumns($dataTable);
                                        $notational = null;
                                        foreach ($temp as $key => $value) {
                                             $visibleType = strtoupper($value->type);
                                             if ($visibleType == 'VARCHAR' || $visibleType == 'CHAR') {
                                                  $visibleType .= '[' . 
                                                                  $value->max_length . 
                                                                  ']';
                                             } else {
                                                  $notational[$value->name] = true;
                                             }
                                             $columns[$value->name] = $value->name . 
                                                                      ' ' . 
                                                                      $visibleType;
                                             if (!empty($value->primary_key)) {
                                                  if ($value->primary_key) {
                                                       $columns[$value->name] .= ' (primary key)';
                                                  }
                                             }
                                        }
                                        $notation = 'Not an ideal column to write to';

                                        $statusColor = null;
                                        if (strlen($columnList) == 0) {
                                             $statusColor = 'red';
                                             $blockSubmit = true;
                                        }

                                        $items[] = $FORMATTER->formatMultiSelectField('Primary Key columns are allowed for write because there will be one row per listing.<br/><br/>VieleRETS is designed to write to columns defined as "VARCHAR".',
                                                                                      'COLUMN_LIST',
                                                                                      $columnList,
                                                                                      $columns,
                                                                                      $statusColor,
                                                                                      $notational,
                                                                                      $notation,
                                                                                      false,
                                                                                      false);
                                        if ($columnList != '') {
                                             $temp = explode(',',$columnList);
                                             $dataKeyList = null;
                                             foreach ($temp as $key => $value) {
                                                  $dataKeyList[$value] = $value;
                                             }

                                             if ($dataTableKey == '') {
                                                  $iMetaKeys = $conn->MetaPrimaryKeys($dataTable);
                                                  $dataTableKey = $iMetaKeys[0];
                                             }

                                             $items[] = $FORMATTER->formatSelectField('Data table column containing the RETS server unique key',
                                                                                      'DATA_TABLE_KEY',
                                                                                      $dataTableKey,
                                                                                      $dataKeyList,
                                                                                      null,
                                                                                      false);
                                        }
                                   }
                              } else {
                                   $statusColor = null;
                                   if (strlen($dataTable) == 0) {
                                        $statusColor = 'red';
                                        $blockSubmit = true;
                                   }
                                   $items[] = $FORMATTER->formatHiddenField('COLUMN_LIST', $columnList);
                                   $items[] = $FORMATTER->formatSingleEntryField('Table name for text data',
                                                                                 'DATA_TABLE',
                                                                                 $dataTable,
                                                                                 20,
                                                                                 $statusColor);
                                   $statusColor = null;
                              }

                              $items[] = $FORMATTER->formatSeparator('Where to Write Image Data');
 
                              $items[] = $FORMATTER->formatBinaryField('Include Images',
                                                                       'INCLUDE_IMAGES',
                                                                       $includeImages);

                              if ($includeImages == 'true') {
                                   $items[] = $FORMATTER->formatBinaryField('Encode Image URLs before storing',
                                                                            'IMAGE_ENCODE_URL',
                                                                            $imageEncodedURL);

                                   $items[] = $FORMATTER->formatBinaryField('Use Image References, not Images',
                                                                            'IMAGE_REFERENCE_ONLY',
                                                                            $imageReferenceOnly);
                                   if ($imageReferenceOnly != 'true') {
                                        $items[] = $FORMATTER->formatPathField('Image download directory',
                                                                               'IMAGE_DOWNLOAD_PATH',
                                                                               $imageDownloadPath,
                                                                               32);
                                   }
                                   if ($autoCreate == 'false') {
                                        $items[] = $FORMATTER->formatSelectField('Table name for images',
                                                                                 'IMAGE_TABLE',
                                                                                 $imageTable,
                                                                                 $tables);

                                        if ($imageTable != '') {
                                             $notational = null;
                                             $temp = $conn->MetaColumns($imageTable);
                                             foreach ($temp as $key => $value) {
                                                  $visibleType = strtoupper($value->type);
                                                  if ($visibleType == 'VARCHAR' || $visibleType == 'CHAR') {
                                                       $visibleType .= '[' . $value->max_length . ']';
                                                  } else {
                                                       $notational[$value->name] = true;
                                                  }
                                                  $imageColumns[$value->name] = $value->name . ' ' . $visibleType;
                                                  if ($value->primary_key) {
                                                       $imageColumns[$value->name] .= ' (primary key)';
                                                       $notational[$value->name] = true;
                                                  }
                                             }

                                             $notation = 'Not an ideal column to write to';

                                             $statusColor = null;
                                             if (strlen($imageColumnList) == 0) {
                                                  $statusColor = 'red';
                                                  $blockSubmit = true;
                                             }

                                             $items[] = $FORMATTER->formatMultiSelectField('Your table should not contain a primary key field because there can be more than one image per listing.<br/><br/>VieleRETS is designed to write to columns defined as "VARCHAR"',
                                                                                           'IMAGE_COLUMN_LIST',
                                                                                           $imageColumnList,
                                                                                           $imageColumns,
                                                                                           $statusColor,
                                                                                           $notational,
                                                                                           $notation,
                                                                                           false);
 
                                             if ($imageColumnList != '') {
                                                  $temp = explode(',',$imageColumnList);
                                                  $imageKeyList = null;
                                                  foreach ($temp as $key => $value) {
                                                       $imageKeyList[$value] = $value;
                                                  }

                                                  if ($imageTableKey == '') {
                                                       $iMetaKeys = $conn->MetaPrimaryKeys($imageTable);
                                                       $imageTableKey = $iMetaKeys[0];
                                                  }

                                                  $items[] = $FORMATTER->formatSelectField('Image table column containing the RETS server unique key used as a foreign key.',
                                                                                           'IMAGE_TABLE_KEY',
                                                                                           $imageTableKey,
                                                                                           $imageKeyList,
                                                                                           null,
                                                                                           false);
                                             }
                                        }
                                   } else {
                                        $statusColor = null;
                                        if (strlen($imageTable) == 0) {
                                             $statusColor = 'red';
                                             $blockSubmit = true;
                                        }
                                        $items[] = $FORMATTER->formatHiddenField('IMAGE_COLUMN_LIST', $imageColumnList);
                                        $items[] = $FORMATTER->formatSingleEntryField('Table name for Images',
                                                                                      'IMAGE_TABLE',
                                                                                      $imageTable,
                                                                                      20,
                                                                                      $statusColor);
                                        $statusColor = null;
                                   }
                              }
                         } else {
                              $items[] = $FORMATTER->formatSingleEntryField('Server Name',
                                                                            'SERVER',
                                                                            $server,
                                                                            20,
                                                                            null,
                                                                            true);

                              $items[] = $FORMATTER->formatSingleEntryField('Account Name',
                                                                            'ACCOUNT',
                                                                            $account,
                                                                            20,
                                                                            null,
                                                                            true);

                              $items[] = $FORMATTER->formatSingleEntryField('Password',
                                                                            'PASSWORD',
                                                                            $password,
                                                                            20,
                                                                            null,
                                                                            true);

                              $items[] = $FORMATTER->formatSingleEntryField('Database',
                                                                            'DATABASE',
                                                                            $database,
                                                                            20,
                                                                            null,
                                                                            true);

                              $blockSubmit = true;
                         }
                         $conn->Close();
                    } else {
                         $items[] = $FORMATTER->formatDisplayField('PHP Installation',
                                                                   'You need the php_mysql package installed for this function',
                                                                   'red');
                         $blockSubmit = true;
                    }
               } else {
                    $blockSubmit = true;
               }

               $items[] = $FORMATTER->formatHiddenField('CONTAINER_NAME', $containerName);
               $items[] = $FORMATTER->formatHiddenField('DATA_DOWNLOAD_PATH', $dataDownloadPath);
               $items[] = $FORMATTER->formatHiddenField('FILE_NAME', $fileName);
               $items[] = $FORMATTER->formatHiddenField('FORMAT', $format);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_FILE_NAME', $imageFileName);
               $items[] = $FORMATTER->formatHiddenField('LISTING_DESCRIPTION_TEMPLATE', $listingDescriptionTemplate);
               $items[] = $FORMATTER->formatHiddenField('METADATA_FIELD', $metadataField);
               $items[] = $FORMATTER->formatHiddenField('METADATA_REQUIRED', $metadataRequired);
               $items[] = $FORMATTER->formatHiddenField('METADATA_TYPE', $metadataType);
               $items[] = $FORMATTER->formatHiddenField('OPEN_REALTY_INSTALL_PATH', $openRealtyInstallPath);

               break;

          case 'XML':
               $items[] = $FORMATTER->formatSingleEntryField('Description',
                                                             'DESCRIPTION',
                                                             $description,
                                                             32);

               $items[] = $FORMATTER->formatSeparator('Text Data');

               $items[] = $FORMATTER->formatPathField('Text download directory',
                                                      'DATA_DOWNLOAD_PATH',
                                                      $dataDownloadPath,
                                                      32);

               $items[] = $FORMATTER->formatSingleEntryField('Name of the file for text data',
                                                             'FILE_NAME',
                                                             $fileName,
                                                             32);

               $items[] = $FORMATTER->formatSingleEntryField('Container Name',
                                                             'CONTAINER_NAME',
                                                             $containerName);

               $items[] = $FORMATTER->formatSeparator('Image Data');

               $items[] = $FORMATTER->formatBinaryField('Include Images',
                                                        'INCLUDE_IMAGES',
                                                        $includeImages);

               if ($includeImages == 'true')
               {
                    $items[] = $FORMATTER->formatBinaryField('Encode Image URLs before storing',
                                                             'IMAGE_ENCODE_URL',
                                                             $imageEncodedURL);

                    $items[] = $FORMATTER->formatBinaryField('Use Image References, not Images',
                                                            'IMAGE_REFERENCE_ONLY',
                                                             $imageReferenceOnly);
                    if ($imageReferenceOnly != 'true')
                    {
                         $items[] = $FORMATTER->formatPathField('Image download directory',
                                                                'IMAGE_DOWNLOAD_PATH',
                                                                $imageDownloadPath,
                                                                32);
                    }
               }

               $items[] = $FORMATTER->formatHiddenField('ACCOUNT', $account);
               $items[] = $FORMATTER->formatHiddenField('AUTO_CREATE', $autoCreate);
               $items[] = $FORMATTER->formatHiddenField('BRAND', $brand);
               $items[] = $FORMATTER->formatHiddenField('COLUMN_LIST', $columnList);
               $items[] = $FORMATTER->formatHiddenField('DATA_TABLE', $dataTable);
               $items[] = $FORMATTER->formatHiddenField('DATABASE', $database);
               $items[] = $FORMATTER->formatHiddenField('FORMAT', $format);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_COLUMN_LIST', $imageColumnList);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_FILE_NAME', $imageFileName);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE', $imageTable);
               $items[] = $FORMATTER->formatHiddenField('IMAGE_TABLE_KEY', $imageTableKey);
               $items[] = $FORMATTER->formatHiddenField('LISTING_DESCRIPTION_TEMPLATE', $listingDescriptionTemplate);
               $items[] = $FORMATTER->formatHiddenField('METADATA_FIELD', $metadataField);
               $items[] = $FORMATTER->formatHiddenField('METADATA_REQUIRED', $metadataRequired);
               $items[] = $FORMATTER->formatHiddenField('METADATA_TYPE', $metadataType);
               $items[] = $FORMATTER->formatHiddenField('OPEN_REALTY_INSTALL_PATH', $openRealtyInstallPath);
               $items[] = $FORMATTER->formatHiddenField('PASSWORD', $password);
               $items[] = $FORMATTER->formatHiddenField('SERVER', $server);

               break;

     }

     $overrideSubmit = null;
     if ($blockSubmit)
     {
          $overrideSubmit = $FORMATTER->formatPageSubmit('Connect', 'SOA_CONNECT');
     }

     $items[] = $FORMATTER->formatHiddenField('CACHE_PATH', $GLOBALS['ADODB_CACHE_DIR']);

     $items[] = $FORMATTER->formatHiddenField('SELECT-ONLY', 'true');
     $items[] = $FORMATTER->formatHiddenField('ELEMENT-TYPE', 'TARGET');

//
// html response
//
     return '<HTML><![CDATA[' .
            $trace .
            $FORMATTER->formatPage(localize('NEW_TARGET'), $items, $overrideSubmit) .
            ']]></HTML>';
}

function specialDisplay($FORMATTER,
                        $discoveredColumns,
                        $visibleText,
                        $variableName,
                        $variableValue)
{
//
// list of fields
//
     $temp_name = explode(',',$discoveredColumns);
     $item_count = sizeof($temp_name);
     $rows = $FORMATTER->bestSplit($item_count);
     $columns = $FORMATTER->bestSplit($item_count,false);
     $fieldBody = null;
     for ($y = 0; $y < $rows; ++$y) 
     {
          $fieldBody .= '<tr>';
          for ($x = 0; $x < $columns; ++$x) 
          {
               $offset = $y + (int)($rows * $x);
               $fieldBody .= '<td>';
               if (array_key_exists($offset, $temp_name))
               {
                    $fieldBody .= $FORMATTER->STYLIST->formatText($temp_name[$offset], null, DATA_POINT_SIZE-1);
               }
               $fieldBody .= '</td>';
          }
          $fieldBody .= '</tr>';
     }
     $field = '<table cellpadding="0" cellspacing="0" border="1" bgcolor="white">' .
              '<tr align="center"><td>' .
              '<input type="text" name="' . $variableName . '" value="' . $variableValue . '" size="40" style="' . $FORMATTER->STYLIST->createTextStyle() . '"/>' .
              '</td></tr>' .
              '<tr align="center"><td>' .
              '<table cellpadding="5" cellspacing="0" border="0">' .
              $fieldBody .
              '</table>' .
              '</td></tr>' .
              '</table>';
     return $FORMATTER->STYLIST->formatBoldText($visibleText) .
            $FORMATTER->STYLIST->formatColumnSeparation() .
            $field;
}

function syscall($command)
{
     if ($proc = popen('($command)2>&1', 'r'))
     {
          $result = null;
          while (!feof($proc))
          {
               $result .= fgets($proc, 1000);
          }
          pclose($proc);
          return $result;
     }
}

function gdVersion($user_ver = 0)
{
     if (!extension_loaded('gd'))
     {
          return;
     }

     static $gd_ver = 0;

//
// Just accept the specified setting if it's 1.
//
     if ($user_ver == 1)
     {
          $gd_ver = 1;
          return 1;
     }
//
// Use the static variable if function was called previously.
//
     if ($user_ver !=2 && $gd_ver > 0)
     {
          return $gd_ver;
     }

//
// Use the gd_info() function if possible.
//
     if (function_exists('gd_info'))
     {
          $ver_info = gd_info();
          preg_match("/\d/", $ver_info["GD Version"], $match);
          $gd_ver = $match[0];
          return $match[0];
      }
//
// If phpinfo() is disabled use a specified / fail-safe choice...
//
     if (preg_match("/phpinfo/", ini_get("disable_functions")))
     {
          if ($user_ver == 2)
          {
               $gd_ver = 2;
               return 2;
          }
          else
          {
               $gd_ver = 1;
               return 1;
          }
     }
//
// ...otherwise use phpinfo().
//
     ob_start();
     phpinfo(8);
     $info = ob_get_contents();
     ob_end_clean();
     $info = stristr($info, "gd version");
     preg_match("/\d/", $info, $match);
     $gd_ver = $match[0];
     return $match[0];
}

//
//------------

?>
