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
// ------------

define('EXP_PAGINATION_THRESHOLD', 50);
define('EXP_PAGINATION_THRESHOLD_NULL_QUERY', 10);
define('EXP_SYNC_FLAG_ALL', false);
define('EXP_SYNC_SET_INACTIVE', true);
define('EXP_ALLOW_DUP_MAP_COLUMNS', true);

date_default_timezone_set('UTC');

class Downloader {

	var $tempFilePath;
	var $tempFileHandle;
	var $dataHandle;
	var $imageHandle;
        var $complexQueries = true;
	var $startTime;
	var $limit;
	var $withDebug = false;
	var $debugDevice;
	var $EXCHANGE;

	function Downloader() {

		// set defines

		define('NO_ITEMS_MESSAGE', 'No Listings were Found');
		define('PARTIAL_RESULT_MESSAGE',
			'All items not returned.  Try a refined search');
		define('DEFAULT_BATCH_SIZE', 10);
		define('EXP_MAX_ROWS', 0);
		define('EXP_THUMB_PREFIX', 'thumb_');
	}

	function setDebug($aValue,
			$aDevice) {
		$this->withDebug = $aValue;
		$this->debugDevice = $aDevice;
        }
         
	function prepare() {
		$this->tempFileHandle = fopen($this->tempFilePath, 'wb');
	}

	function handleStream($data) {
		fwrite($this->tempFileHandle, $data);
        }

	function finish() {
		fclose($this->tempFileHandle);
	}

	function processBatch($quiet,
		$extract,
		$query_values,
		$CONFIGURATION,
		$S_CONFIGURATION,
		$last_run,
                $limit_override,
                $advancedQuery,
		$syncFlag) {

		// set start time
		$this->startTime = getdate();

		// construct SOURCE context

		$SOURCE_CONTEXT = new SourceContext();
		$SOURCE_CONTEXT->readConfiguration($S_CONFIGURATION);

		// remove query items that are not defined

		$query_array = null;
		$args = explode(',', $SOURCE_CONTEXT->query_items);
		$query = '';
		foreach ($args as $key => $val) {
			if ($query_values != null) {
				if (array_key_exists($val, $query_values)) {
					$query_array[$val] = $query_values[$val];
				}
			}
		}

		// construct TARGET and EXTRACT contexts

		$EXTRACT_CONTEXT = new ExtractContext();
		$EXTRACT_CONTEXT->readConfiguration($CONFIGURATION);

		$TARGET = new Target();
		$T_CONFIGURATION = $TARGET->getConfiguration($EXTRACT_CONTEXT->target_name);
		$TARGET_CONTEXT = new TargetContext();
		$TARGET_CONTEXT->readConfiguration($T_CONFIGURATION);

		// create an Exchange object and set debugging

		$this->EXCHANGE = new Exchange($SOURCE_CONTEXT->name);
		if ($this->withDebug) {
			$this->EXCHANGE->setTraceDevice($this->debugDevice);
			$this->EXCHANGE->initializeTraceDevice();
			$this->EXCHANGE->setPayloadTrace(true);
//			$this->EXCHANGE->setStreamTrace(true);
			$this->EXCHANGE->setTransportTrace(true);
		}

		// check OR definition for completeness

		if ($TARGET_CONTEXT->target_type == 'OR') {
	                $unique_target_key = $this->getUniqueTargetKey($EXTRACT_CONTEXT,
						$SOURCE_CONTEXT);
			if ($unique_target_key == null) {
				$EXTRACT_RESULT = new Statistics('Download with [' . $extract . ']');
				$eol = $EXTRACT_RESULT->getEOL();
				print($eol . $EXTRACT_RESULT->getStart() . $eol);
				$EXTRACT_RESULT->addError('ERROR!');
				$EXTRACT_RESULT->addError('Mapping for this EXTRACT is not complete.');
				$EXTRACT_RESULT->addError('You need to map the UNIQUE_KEY from RETS to a TARGET field.');
				$EXTRACT_RESULT->addError('Use the Administration Interface to fix the map');
				$EXTRACT_RESULT->printErrors();
				if (!$quiet) {
					flush_output($EXTRACT_RESULT->getSummary($this->EXCHANGE));
					printBatchCompletion();
				}
				return;
			}
		}

		// create list of fields returned by the query

		$selections = $this->constructSelections($SOURCE_CONTEXT,
			$EXTRACT_CONTEXT,
			$TARGET_CONTEXT);

		// translate class name to system name if standardNames are present
//		$METADATA_CLASS = new ClassMetadata($SOURCE_CONTEXT->name,
//			$SOURCE_CONTEXT->resource);
//		$systemClass = $METADATA_CLASS->getSystemClass($SOURCE_CONTEXT->class_name,
//			$SOURCE_CONTEXT->detected_standard_names);
//		$METADATA_TABLE = new TableMetadata($SOURCE_CONTEXT->name,
//			$systemClass);
//		$METADATA_TABLE->read();

		// create a data request

		$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
			$query_array,
			$selections);

		// advanced query override

                if ($advancedQuery != null){
			$DATA_REQUEST->setQueryCriteria($advancedQuery);
		}

		// begin download

		$partial = false;
		$listingCount = 0;

		// check for update only mode 

		if ($last_run != null) {
			if ($SOURCE_CONTEXT->field_types != null) {
				$CONNECTION = verifyTransport($SOURCE_CONTEXT->url,
							$SOURCE_CONTEXT->application,
							$SOURCE_CONTEXT->version,
							$SOURCE_CONTEXT->detected_maximum_rets_version);
				$DATA_REQUEST->setUpdatesSinceList($SOURCE_CONTEXT->date_variables,
					$SOURCE_CONTEXT->field_types,
					$last_run,
					$CONNECTION->getServerTimeOffset());
			}
		}

		// create an object to carry statistics

		$EXTRACT_RESULT = new Statistics('Download with [' . $extract . ']');
		$spacer = $EXTRACT_RESULT->getSpacer();
		$eol = $EXTRACT_RESULT->getEOL();
		if (!$quiet) {
			$visibleBreak = $EXTRACT_RESULT->getVisibleBreak();

			// start

			print($eol . $EXTRACT_RESULT->getStart() . $eol);

			// trace

			if ($EXTRACT_CONTEXT->trace) {
				print($spacer . 'Trace Activating' . $eol);
			}

			// MLS information only

			if ($TARGET_CONTEXT->target_type == 'OR') { 
				if (!$EXTRACT_CONTEXT->mls_only) {
					$buffer = 'User supplied information will be retained';
				} else {
					$buffer = 'MLS data only, User supplied information will be overwritten';
				}
				print($spacer . $buffer . $eol);
			}

			// lastRun mode

			if ($last_run != null) {
				$buffer = 'Only MLS listings changed (or added) since ' . $last_run . ' (GMT) will be evaluated';
			} else {
				$buffer = 'All MLS listings will be evaluated by the server';
			}
			print($spacer . $buffer . $eol);

			// display fields

			if ($DATA_REQUEST->hasSelectionCriteria()) {
				print($spacer . 'Selection Fields: ' .
			              $DATA_REQUEST->getSelectionCriteria() .
                                             $eol);
			} else {
				print($spacer . 'Selection Fields: NULL' . $eol);
			}

			// display query

			if ($DATA_REQUEST->hasQueryCriteria()) {
				print($spacer . 'DMQL Query: ' .
                                             $DATA_REQUEST->getQueryCriteria() .
                                             $eol);
			} else {
				print($spacer . 'DMQL Query: NULL' . $eol);
			}

			// restricted indicator

			if ($DATA_REQUEST->hasRestrictedIndicator()) {
				print($spacer . 'Restricted Indicator: ' .
					$DATA_REQUEST->getRestrictedIndicator() .
					$eol);
			}

			if ($TARGET_CONTEXT->target_type == 'OR' ||
			    $TARGET_CONTEXT->target_type == 'RDB') {

				// refresh mode

				if ($EXTRACT_CONTEXT->refresh) {
					$buffer = 'New MLS listings will be added and existing listings will be refreshed if updated';
				} else {
					$buffer = 'Only MLS listings that are NOT already downloaded will be added';
				}

				// synchronization feature

				if ($syncFlag) {
					flush_output($spacer . $buffer . $eol);
					$buffer = 'Synchronization will be run after the download';
				} 
			} else {
				$buffer = 'All MLS listings matching the query will be downloaded';
			}

			// proxy usage

			if ($TARGET_CONTEXT->include_images ||
				$TARGET_CONTEXT->target_type == 'OR') {
				print($spacer . $buffer . $eol);
				if ($SOURCE_CONTEXT->media_location) {
					$buffer = 'Images accessed with the Media Server capabilities of the MLS';
				} else {
					$buffer = 'Images accessed directly via RETS';
				}
				$buffer .= $eol . 
                                           $spacer . 'Maximum Images per listing: ' . $EXTRACT_CONTEXT->max_images;
			}
			flush_output($spacer . $buffer . $eol);
		}

		// preprocessing

		switch ($TARGET_CONTEXT->target_type) {
			case 'CSV':
				$this->dataHandle = fopen($TARGET_CONTEXT->data_download_path . '/' . $TARGET_CONTEXT->data_file_name, 'wb');

				// data headers

				$csv = null;
				$aMap = explode(',', $selections);
				foreach ($aMap as $key => $value) {
					$csv .= '"' . $value . '",';
				}
				if ($this->dataHandle != null) {
					fwrite($this->dataHandle, 
						substr($csv, 0, strlen($csv) - 1) . CRLF);
				}

				if ($TARGET_CONTEXT->include_images) {

					// image headers

					$this->imageHandle = fopen($TARGET_CONTEXT->data_download_path . '/' . $TARGET_CONTEXT->image_file_name, 'wb');
					if ($this->imageHandle != null) {
						fwrite($this->imageHandle,
							'"' . $SOURCE_CONTEXT->unique_key . '","INDEX","URL","PATH"' . CRLF);
					}
				}
				break;

			case 'OR':
// TODO
				break;

			case 'RDB':

				// check for mapping

				$found = false;
				if ($EXTRACT_CONTEXT->data_maps != null) {
					if (array_key_exists('SOURCE', $EXTRACT_CONTEXT->data_maps)) {
						$dataInputFilter = $EXTRACT_CONTEXT->data_maps['SOURCE'];
						foreach ($dataInputFilter as $key => $val) {
							if ($val == $SOURCE_CONTEXT->unique_key) {
								$found = true;
							}
						}
					}
				        if (!$found) {
					          $buffer = 'UNIQUE_KEY not found in the EXTRACT data map, check configuration';
					          print($spacer . $buffer . $eol);
                                        }
				} else {
					$buffer = 'No data map for the EXTRACT, check configuration';
					print($spacer . $buffer . $eol);
				}
				if (!$found) {
					$buffer = ' - Listing replacement disabled';
					if ($TARGET_CONTEXT->include_images) {
						print($spacer . $buffer . $eol);
						$buffer = ' - Image processing disabled';
					}
					flush_output($spacer . $buffer . $eol);
				}

				// auto create tables

				if ($TARGET_CONTEXT->auto_create) {

					// get list of tables already created

					$conn = $this->createConnection($TARGET_CONTEXT);
					$tables = $conn->MetaTables('TABLES');

					// data table

//-----------------
/*
					$typeList = $METADATA_TABLE->findDataTypes($SOURCE_CONTEXT->detected_standard_names);
					$interpretList = $METADATA_TABLE->findInterpretations($SOURCE_CONTEXT->detected_standard_names);
					$lengthList = $METADATA_TABLE->findMaximumLengths($SOURCE_CONTEXT->detected_standard_names);
               				foreach ($typeList as $key => $type) 
               				{
						$pos = strpos($interpretList[$key], 'Lookup');
						if ($pos === false)
						{
							switch($type)
							{
								case 'Character':
									$sqlType = 'varchar(' .
										$lengthList[$key] .
										')';
									break;
								case 'Int':
									$sqlType = 'int';
									break;
								case 'Long':
									$sqlType = 'long';
									break;
								case 'DateTime':
									$sqlType = 'dateTime';
									break;
								case 'Date':
									$sqlType = 'date';
									break;
							}
						}
						else
						{
							$sqlType = 'varchar(255)';
						}
print($key . " " . $sqlType . "<br>");
					}
*/
//-----------------
					$metaFound = false;
					foreach ($tables as $key => $value) {
						if ($value == $TARGET_CONTEXT->data_table) {
							$metaFound = true;
						}
					}
					if ($metaFound) {
						$buffer = 'Table [' . $TARGET_CONTEXT->data_table .
							'] in database [' . $TARGET_CONTEXT->database . '] does not need to be created';
						flush_output($spacer . $buffer . $eol);
					} else {
						$buffer = 'Creating table [' . $TARGET_CONTEXT->data_table .
							'] in database [' . $TARGET_CONTEXT->database . ']';
						flush_output($spacer . $buffer . $eol);
						$this->autoCreate_table($conn,
								$TARGET_CONTEXT->brand,
								$tables,
								$TARGET_CONTEXT->data_table,
								$TARGET_CONTEXT->data_column_list);
					}
					if ($TARGET_CONTEXT->include_images) {

						// image table

						$metaFound = false;
						foreach ($tables as $key => $value) {
							if ($value == $TARGET_CONTEXT->image_table) {
								$metaFound = true;
							}
						}
						if ($metaFound) {
							$buffer = 'Table [' . $TARGET_CONTEXT->image_table .
								'] in database [' . $TARGET_CONTEXT->database . '] does not need to be created';
							flush_output($spacer . $buffer . $eol);
						} else {
							$buffer = 'Creating table [' . $TARGET_CONTEXT->image_table .
								'] in database [' . $TARGET_CONTEXT->database . ']';
							flush_output($spacer . $buffer . $eol);
							$this->autoCreate_table($conn,
									$TARGET_CONTEXT->brand,
									$tables,
									$TARGET_CONTEXT->image_table,
									$TARGET_CONTEXT->image_column_list);
						}
					}

					// disconnect

					$conn->Close();
				}
				break;

			case 'XML':
				$this->dataHandle = fopen($TARGET_CONTEXT->data_download_path . '/' . $TARGET_CONTEXT->data_file_name, 'wb');

				// start containing tag

				fwrite($this->dataHandle,
					'<?xml version="1.0"?>' . CRLF . '<' . $TARGET_CONTEXT->container_name . '>' . CRLF);
				break;
		}

		// set limits - EXP_MAX_ROWS overrides user's wishes

		if ($EXTRACT_CONTEXT->limit > 0) {
			$DATA_REQUEST->setLimit($EXTRACT_CONTEXT->limit);
			if (!$quiet) {
				flush_output( $spacer .
					'Limit Governor set by EXTRACT to ' . $EXTRACT_CONTEXT->limit . ' listings' .
					$eol);
			}
		}
		if ($limit_override > 0) {
			$DATA_REQUEST->setLimit($limit_override);
			if (!$quiet) {
				flush_output( $spacer .
					'Limit Governor set at RUNTIME to ' . $limit_override . ' listings' .
					$eol);
			}
		}
		if (EXP_MAX_ROWS > 0) {
			$DATA_REQUEST->setLimit(EXP_MAX_ROWS);
			if (!$quiet) {
				flush_output( $spacer .
					'Limit Governor reset by PACKAGE to ' . $EXTRACT_CONTEXT->limit . ' listings' .
					$eol);
			}
		}
		$this->limit = $DATA_REQUEST->getLimit();
		if (!$quiet) {
			if ($this->limit > 0) {
				flush_output( $spacer .
					'Only ' . $this->limit . ' listings will be processed' .
					$eol);
			} else {
				if ($EXTRACT_CONTEXT->trace) {
					flush_output( $spacer .
						'Limit Governor not Activated' .
						$eol);
				}
			}
		}

		// special single column (index only) processing

		$selectedFields = sizeof(explode(',', $DATA_REQUEST->getSelectionCriteria()));
		if ($selectedFields == 1) {

			// runtime information

			if (!$quiet) {
				flush_output($spacer . 'Special optimization to retrieve indexes ' .
						'is being used' .  $eol);
			}

			$this->getBatch($quiet,
					$this->limit,
					1,
					$DATA_REQUEST,
					$SOURCE_CONTEXT,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT,
					$EXTRACT_RESULT);
		} else {

			// batch logic

			$result = $this->EXCHANGE->loginDirect($SOURCE_CONTEXT->account,
						$SOURCE_CONTEXT->password,
						$SOURCE_CONTEXT->url,
						$SOURCE_CONTEXT->detected_maximum_rets_version,
						$SOURCE_CONTEXT->application,
						$SOURCE_CONTEXT->version,
						$SOURCE_CONTEXT->clientPassword,
						$SOURCE_CONTEXT->postRequests);
			if ($result) {
				if (!$DATA_REQUEST->isNullQuery() &&
					$selectedFields < EXP_PAGINATION_THRESHOLD) {

					// if not a NULL query, try to get it all in a single batch bypassing pagination

					if (!$quiet) {
//						flush_output($spacer . 'Optimization for queries returning limited number of fields ' .
//								'is being used because this is not a NULL query' .
//								$eol);
						flush_output($spacer . 'Optimization for user defined queries returning fewer than ' .
								EXP_PAGINATION_THRESHOLD . ' fields (configurable) is being used' .
								$eol);
					}

					$this->priv_getBatch($quiet,
							$this->limit,
							1,
							$DATA_REQUEST,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT,
							$EXTRACT_RESULT);

					$this->EXCHANGE->logoutDirect();
				} else {

					if ($selectedFields < EXP_PAGINATION_THRESHOLD_NULL_QUERY) {
						if (!$quiet) {

							flush_output($spacer . 'Optimization for queries returning fewer than ' .
									EXP_PAGINATION_THRESHOLD_NULL_QUERY . ' fields (configurable) is being used' .
									$eol);
						}
	
						$this->priv_getBatch($quiet,
								$this->limit,
								1,
								$DATA_REQUEST,
								$SOURCE_CONTEXT,
								$TARGET_CONTEXT,
								$EXTRACT_CONTEXT,
								$EXTRACT_RESULT);
						$this->EXCHANGE->logoutDirect();
					} else {

					// this is a NULL query.  Try to paginate

					if ($SOURCE_CONTEXT->pagination) {

						// execute batch with server help

						$this->getBatchWithServerHelp($DATA_REQUEST,
								$SOURCE_CONTEXT,
								$TARGET_CONTEXT,
								$EXTRACT_CONTEXT,
								$quiet,
								$query_array,
								$EXTRACT_RESULT);
					} else {

						// runtime information

						if (!$quiet) {
							flush_output($spacer . 'Pagination not supported by the server' .
									$eol);
						}

						// execute batch without server help

						$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
										$query_array,
										$SOURCE_CONTEXT->unique_key);
		                                $DATA_REQUEST->setQueryCriteria($DATA_REQUEST->getQueryCriteria());
				                $DATA_REQUEST->setLimit($this->limit);

						// determine the method

						$diskMethod = false;
						if (strlen($EXTRACT_CONTEXT->working_file_path) != null) {
							if (strlen($EXTRACT_CONTEXT->working_file_path) > 0) {
								if (file_exists($EXTRACT_CONTEXT->working_file_path)) {
									$diskMethod = true;
								}
							}
                                       		}

						if (!$diskMethod) {
							if (!$quiet) {
								flush_output($spacer . 'Working file definition problem, problem with EXTRACT definition' .
										$eol);
							}

							// execute batch with memory method

							$this->getBatchWithMemoryMethod($DATA_REQUEST,
									$SOURCE_CONTEXT,
									$TARGET_CONTEXT,
									$EXTRACT_CONTEXT,
									$quiet,
									$EXTRACT_RESULT);
						} else {

							// execute batch with disk method

							$this->getBatchWithDiskMethod($DATA_REQUEST,
									$SOURCE_CONTEXT,
									$TARGET_CONTEXT,
									$EXTRACT_CONTEXT,
									$quiet,
									$EXTRACT_RESULT);
	                                	}
					}
					}
				}
			} else {
				$EXTRACT_RESULT->addError(NO_SERVICE_MESSAGE);
			}
		}

		// check batch errors

		if ($EXTRACT_RESULT->hasErrors()) {
			$EXTRACT_RESULT->printErrors();
		}

		// post processing

		$listingCount = $EXTRACT_RESULT->getProcessed();
		switch ($TARGET_CONTEXT->target_type) {
			case 'CSV':

				// close image file

				if ($TARGET_CONTEXT->include_images) {
					if ($this->imageHandle != null) {
						fclose($this->imageHandle);
					}
				}
				if ($this->dataHandle != null) {
					fclose($this->dataHandle);
				}
				break;

			case 'OR':
// TODO
				break;

			case 'XML':

				// close containing tag

				fwrite($this->dataHandle, '</' . $TARGET_CONTEXT->container_name . '>');
				fclose($this->dataHandle);
				break;
		}

		// print batch summary

		if (!$quiet) {
			$buffer = $eol;
			if ($EXTRACT_CONTEXT->trace) {
				$buffer .= $visibleBreak . $spacer . 'Trace Deactivated' . $eol;
			}
			$buffer .= $eol;
			flush_output($buffer);

			flush_output($EXTRACT_RESULT->getSummary($this->EXCHANGE));

			// gather RETS statistics

			$this->EXCHANGE->finish();
			postExecuteStatistics($S_CONFIGURATION, $this->EXCHANGE);
		}

		// check governor

		if (!$SOURCE_CONTEXT->pagination) {
			if (EXP_MAX_ROWS > 0) {
				if ($listingCount > EXP_MAX_ROWS) {
					$partial = true;
				}
			}
		}

		// print errors

		printBatchErrors($partial, $listingCount);

		// completion

		if (!$syncFlag) {
			if (!$quiet) {
				printBatchCompletion();
			}
		}
	}

	function processSynchronization($quiet,
		$extract,
		$query_values,
		$CONFIGURATION,
		$S_CONFIGURATION,
		$advancedQuery,
		$reuse = true) {

		// set start time
		$this->startTime = getdate();

		// construct SOURCE context

		$SOURCE_CONTEXT = new SourceContext();
		$SOURCE_CONTEXT->readConfiguration($S_CONFIGURATION);

		// remove query items that are not defined

		$query_array = null;
		$args = explode(',', $SOURCE_CONTEXT->query_items);
		$query = '';
		foreach ($args as $key => $val) {
			if ($query_values != null) {
				if (array_key_exists($val, $query_values)) {
					$query_array[$val] = $query_values[$val];
				}
			}
		}

		// construct TARGET and EXTRACT contexts

		$EXTRACT_CONTEXT = new ExtractContext();
		$EXTRACT_CONTEXT->readConfiguration($CONFIGURATION);

		$TARGET = new Target();
		$T_CONFIGURATION = $TARGET->getConfiguration($EXTRACT_CONTEXT->target_name);
		$TARGET_CONTEXT = new TargetContext();
		$TARGET_CONTEXT->readConfiguration($T_CONFIGURATION);

		// create an Exchange object and set debugging

		$this->EXCHANGE = new Exchange($SOURCE_CONTEXT->name);
		if ($this->withDebug) {
			$this->EXCHANGE->setTraceDevice($this->debugDevice);
			$this->EXCHANGE->initializeTraceDevice($reuse);
			$this->EXCHANGE->setPayloadTrace(true);
//			$this->EXCHANGE->setStreamTrace(true);
			$this->EXCHANGE->setTransportTrace(true);
		}

		if ($TARGET_CONTEXT->target_type == 'OR') {

			// mark certain listings as inactive - optional 

			if (strlen($EXTRACT_CONTEXT->status_variable) != 0 ) {

				// create a data request

				$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
					$query_array,
				        $SOURCE_CONTEXT->unique_key);
				$DATA_REQUEST->setQueryCriteria('(' . $EXTRACT_CONTEXT->status_variable . '=|' . 
							$EXTRACT_CONTEXT->status_variable_value . ')');

				// create an object to carry statistics
	
				$EXTRACT_RESULT = new InactiveStatistics('Marking of inactive listings for [' . $extract . ']');
				$spacer = $EXTRACT_RESULT->getSpacer();
				$eol = $EXTRACT_RESULT->getEOL();
				if (!$quiet) {

					// start

					print($eol . $EXTRACT_RESULT->getStart() . $eol);

					// trace

					if ($EXTRACT_CONTEXT->trace) {
						print($spacer . 'Trace Activating' . $eol);
					}

					// display query

					print($spacer . 'DMQL Query: ' .
                       		                     $DATA_REQUEST->getQueryCriteria() .
                               		             $eol);

					// which listings will be turned to inactive

					if($EXTRACT_CONTEXT->status_variable != null) {
						$buffer = 'Listings with the value(s) ' . 
							$EXTRACT_CONTEXT->status_variable_value . ' in the ' . 
							$EXTRACT_CONTEXT->status_variable . 
							' field will be shown as "inactive" listings';
						flush_output($spacer . $buffer . $eol);
					}
	
				}

				// set inactive 

				$PROCESS_RESULTS = new ProcessResults();
				$result = $this->EXCHANGE->loginDirect($SOURCE_CONTEXT->account,
					$SOURCE_CONTEXT->password,
					$SOURCE_CONTEXT->url,
					$SOURCE_CONTEXT->detected_maximum_rets_version,
					$SOURCE_CONTEXT->application,
					$SOURCE_CONTEXT->version,
					$SOURCE_CONTEXT->clientPassword,
					$SOURCE_CONTEXT->postRequests);
				if ($result) {
					$DATA_RESPONSE = $this->EXCHANGE->searchDataDirect($DATA_REQUEST,
						$SOURCE_CONTEXT->detected_standard_names,
					        1,
						$SOURCE_CONTEXT->compact_decoded_format);

					if ($this->EXCHANGE->hasErrors()) {
						if (!$quiet) {
							$this->printQueryError($EXTRACT_RESULT->getSpacer(),
								$EXTRACT_RESULT->getEOL(),
								$this->EXCHANGE->getLastError(),
			        				$DATA_REQUEST->getQueryCriteria());
			          		}
						$this->EXCHANGE->resetErrors();
	        		  	}
					else {

						// process results 

						if ($DATA_RESPONSE->getRowCount() > 0) {
							$BATCH_RESULT = $this->reconcileInactive($quiet,
								$DATA_RESPONSE,
								$SOURCE_CONTEXT,
								$TARGET_CONTEXT,
								$EXTRACT_CONTEXT);
							$EXTRACT_RESULT->summarizeBatch($BATCH_RESULT);
						} else {
							$errorParser = new ErrorParser();
							$errorParser->parse($DATA_RESPONSE->getRawData());
							if ($errorParser->error_check) {
								if (!$quiet) {
									$this->printQueryError($EXTRACT_RESULT->getSpacer(),
										$EXTRACT_RESULT->getEOL(),
										$errorParser->getErrorText(),
						        			$DATA_REQUEST->getQueryCriteria());
								}
							}
						}
					}

					$this->EXCHANGE->logoutDirect();

				} else {
					$PROCESS_RESULTS->addError(NO_SERVICE_MESSAGE);
				}

				if ($PROCESS_RESULTS->hasErrors()) {
					$PROCESS_RESULTS->printErrors();
				}

				// check batch errors
	
				if ($EXTRACT_RESULT->hasErrors()) {
					$EXTRACT_RESULT->printErrors();
				}

				// post processing

				$listingCount = $EXTRACT_RESULT->getProcessed();

				// print batch summary

				if (!$quiet) {
					$buffer = $eol;
					if ($EXTRACT_CONTEXT->trace) {
						$buffer .= $visibleBreak . $spacer . 'Trace Deactivated' . $eol;
					}
					$buffer .= $eol;
					flush_output($buffer);

					flush_output($EXTRACT_RESULT->getSummary($this->EXCHANGE));

					// gather RETS statistics

					$this->EXCHANGE->finish();
					postExecuteStatistics($S_CONFIGURATION, $this->EXCHANGE);
				}

				// print errors

				printBatchErrors(false, $listingCount);

                        }
		}

		// create a data request

		$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
			$query_array,
		        $SOURCE_CONTEXT->unique_key);

		// advanced query override

                if ($advancedQuery != null){
			$DATA_REQUEST->setQueryCriteria($advancedQuery);
		}

		// create an object to carry statistics

		$EXTRACT_RESULT = new SynchronizationStatistics('Processing of missing listings for [' . $extract . ']');
		$spacer = $EXTRACT_RESULT->getSpacer();
		$eol = $EXTRACT_RESULT->getEOL();
		if (!$quiet) {

			// start

			print($eol . $EXTRACT_RESULT->getStart() . $eol);

			// trace

			if ($EXTRACT_CONTEXT->trace) {
				print($spacer . 'Trace Activating' . $eol);
			}

			// display query

			print($spacer . 'DMQL Query: ' .
				$DATA_REQUEST->getQueryCriteria() .
				$eol);

			flush_output($spacer . 
				'Only listings found on the server will be left locally' .
				$eol);

		}

		// preprocessing

		if ($TARGET_CONTEXT->target_type == 'RDB') {

			// check for mapping

			$found = false;
			if ($EXTRACT_CONTEXT->data_maps != null) {
				if (array_key_exists('SOURCE', $EXTRACT_CONTEXT->data_maps)) {
					$dataInputFilter = $EXTRACT_CONTEXT->data_maps['SOURCE'];
					foreach ($dataInputFilter as $key => $val) {
						if ($val == $SOURCE_CONTEXT->unique_key) {
							$found = true;
						}
					}
				}
			}
			if (!$found) {
				flush_output($spacer . 
					'No data map invalid for this EXTRACT, check configuration' .
					$eol);
			}
		}

		// collect lint 

		$PROCESS_RESULTS = new ProcessResults();
		$result = $this->EXCHANGE->loginDirect($SOURCE_CONTEXT->account,
			$SOURCE_CONTEXT->password,
			$SOURCE_CONTEXT->url,
			$SOURCE_CONTEXT->detected_maximum_rets_version,
			$SOURCE_CONTEXT->application,
			$SOURCE_CONTEXT->version,
			$SOURCE_CONTEXT->clientPassword,
			$SOURCE_CONTEXT->postRequests);
		if ($result) {
			$DATA_RESPONSE = $this->EXCHANGE->searchDataDirect($DATA_REQUEST,
				$SOURCE_CONTEXT->detected_standard_names,
			        1,
				$SOURCE_CONTEXT->compact_decoded_format);

			if ($this->EXCHANGE->hasErrors()) {
				if (!$quiet) {
					$this->printQueryError($EXTRACT_RESULT->getSpacer(),
						$EXTRACT_RESULT->getEOL(),
						$this->EXCHANGE->getLastError(),
			        		$DATA_REQUEST->getQueryCriteria());
	          		}
				$this->EXCHANGE->resetErrors();
	          	}
			else {
				// process results 

				if ($DATA_RESPONSE->getRowCount() > 0) {
					$BATCH_RESULT = $this->reconcile($quiet,
						$DATA_RESPONSE,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT);
					$EXTRACT_RESULT->summarizeBatch($BATCH_RESULT);
				} else {
					$errorParser = new ErrorParser();
					$errorParser->parse($DATA_RESPONSE->getRawData());
					if ($errorParser->error_check) {
						if (!$quiet) {
							$this->printQueryError($EXTRACT_RESULT->getSpacer(),
								$EXTRACT_RESULT->getEOL(),
								$errorParser->getErrorText(),
					        		$DATA_REQUEST->getQueryCriteria());
						}
					}
				}
			}

			$this->EXCHANGE->logoutDirect();

		} else {
			$PROCESS_RESULTS->addError(NO_SERVICE_MESSAGE);
		}

		if ($PROCESS_RESULTS->hasErrors()) {
			$PROCESS_RESULTS->printErrors();
		}

		// check batch errors

		if ($EXTRACT_RESULT->hasErrors()) {
			$EXTRACT_RESULT->printErrors();
		}

		// post processing

		$listingCount = $EXTRACT_RESULT->getProcessed();

		// print batch summary

		if (!$quiet) {
			$buffer = $eol;
			if ($EXTRACT_CONTEXT->trace) {
				$buffer .= $visibleBreak . $spacer . 'Trace Deactivated' . $eol;
			}
			$buffer .= $eol;
			flush_output($buffer);

			flush_output($EXTRACT_RESULT->getSummary($this->EXCHANGE));

			// gather RETS statistics

			$this->EXCHANGE->finish();
			postExecuteStatistics($S_CONFIGURATION, $this->EXCHANGE);
		}

		// print errors

		printBatchErrors(false, $listingCount);

//----------------------

		// completion with button

		if (!$quiet) {
			printBatchCompletion();
		}
	}

/*
	function createLocalList($SOURCE_CONTEXT,
			$TARGET_CONTEXT,
			$EXTRACT_CONTEXT) {

		$BATCH_RESULT = new SynchronizationStatistics('Create Local List');

		// initialize

		$unique_key = $SOURCE_CONTEXT->unique_key;
//print('key: ' . $unique_key . '</br>');
		foreach ($EXTRACT_CONTEXT->data_maps['SOURCE'] as $key => $value) {
			if ($value == $unique_key) {
				$unique_column = $EXTRACT_CONTEXT->data_maps['TARGET'][$key];
			}
		}
//print('column: ' . $unique_column . '</br>');

		// create an connection to the RDB 

		$conn = $this->createConnection($TARGET_CONTEXT);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

		// create a list of local listings

		$aList = null;
		switch ($TARGET_CONTEXT->target_type) {
			case 'RDB':
	                        $aList = $this->local_rdb_list(
						$conn,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_column);
				break;

			case 'OR':

				// clear ADBDB cache

				$this->clearCache($TARGET_CONTEXT->cache_path);

				// if this is OpenRealty V 2.1 or greater, find the classID 

				$classID = -1;
				$TARGET = new Target();
				if ($TARGET->requiresClassesPrimitive($TARGET_CONTEXT->class_table)) {
					$classID = $this->lookupClassTypeOR($conn,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT);
				}
//print('clean classID: ' . $classID . '</br>');

				// create a local list

	                        $aList = $this->local_or_list(
						$conn,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_column,
						$classID);

				break;
		}
//print_r($aList);
//print('</br>');

		// close database

		$conn->Close();

		// print errors

		if ($BATCH_RESULT->hasErrors()) {
			$BATCH_RESULT->printErrors();
		}

		return $aList;
	}
*/

	function reconcileInactive($quiet,
		$DATA_RESPONSE,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT) {

		$BATCH_RESULT = new SynchronizationStatistics('Reconcile Inactivity');

		// initialize

		$unique_key = $SOURCE_CONTEXT->unique_key;
		foreach ($EXTRACT_CONTEXT->data_maps['SOURCE'] as $key => $value) {
			if ($value == $unique_key) {
				$unique_column = $EXTRACT_CONTEXT->data_maps['TARGET'][$key];
			}
		}

		// create a list of MLS listings

		$mlsList = Array();
		$rowCount = $DATA_RESPONSE->getRowCount();
		for ($i = 0; $i < $rowCount; ++$i) {
			$data_row = $DATA_RESPONSE->getRow($i);
                       	$mlsList[$data_row[$unique_key]] = true;
		}

		// create an connection to the RDB 

		$conn = $this->createConnection($TARGET_CONTEXT);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

		// clear ADBDB cache

		$this->clearCache($TARGET_CONTEXT->cache_path);

		// if this is OpenRealty V 2.1 or greater, find the classID 

		$classID = -1;
		$TARGET = new Target();
		if ($TARGET->requiresClassesPrimitive($TARGET_CONTEXT->class_table)) {
			$classID = $this->lookupClassTypeOR($conn,
					$SOURCE_CONTEXT,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT);
		}
//print('clean classID: ' . $classID . '</br>');

		// compare lists 

		foreach ($mlsList as $key => $val) {
			$buffer = null;

			// find the internal OpenRealty listing ID 

			$sql = 'SELECT listingsdb_id FROM ' . $TARGET_CONTEXT->data_table . " WHERE listingsdbelements_field_value = '" . $key . "'";
//print('DATA FIND SQL ' . $sql . '</br>');
			$recordSet = $conn->Execute($sql);
			if ($recordSet === false) {
				$BATCH_RESULT->addError('get random ERROR ' . $sql);
			}
			$anID = $recordSet->fields['listingsdb_id'];

			// find current status setting 

			$sql = 'SELECT listingsdb_active FROM ' . $TARGET_CONTEXT->index_table . 
				" WHERE listingsdb_id = '" . $anID . "'";
//print('DATA FIND SQL ' . $sql . '</br>');
			$recordSet = $conn->Execute($sql);
			if ($recordSet === false) {
				$BATCH_RESULT->addError('get random ERROR ' . $sql);
			}
			$aStatus = $recordSet->fields['listingsdb_active'];

			if ($aStatus == 'yes') {
				// set the listing to inactive

print('MLS listing [' . $key . '] being set inactive as OpenRealty listing [' . $anID . ']</br>');
if ($key != 'localhost-141' && $key != 'localhost-144') {
				$BATCH_RESULT->addOrphanedItem();
				$sql = 'UPDATE ' . $TARGET_CONTEXT->index_table . 
					" SET listingsdb_active = 'no' " . 
					" WHERE listingsdb_id = '" . $anID . "'";
//print('DATA UPDATE INDEX SQL ' . $sql . '</br>');
				$recordSet = $conn->Execute($sql);
}
			}
			$BATCH_RESULT->addProcessedItem();

			if ($EXTRACT_CONTEXT->trace) {
				$BATCH_RESULT->printBatchDetail('RETS Listing: ' . $key . $buffer);
			} else {

				// print dots

				if (!$quiet) {
					$BATCH_RESULT->printBatchNote();
				}
			}
		}

		// close database

		$conn->Close();

		// print errors

		if ($BATCH_RESULT->hasErrors()) {
			$BATCH_RESULT->printErrors();
		}

		return $BATCH_RESULT;
	}

	function reconcile($quiet,
		$DATA_RESPONSE,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT) {

		$BATCH_RESULT = new SynchronizationStatistics('Reconcile Lists');

		// initialize

		$unique_key = $SOURCE_CONTEXT->unique_key;
//print('key: ' . $unique_key . '</br>');
		foreach ($EXTRACT_CONTEXT->data_maps['SOURCE'] as $key => $value) {
			if ($value == $unique_key) {
				$unique_column = $EXTRACT_CONTEXT->data_maps['TARGET'][$key];
			}
		}
//print('column: ' . $unique_column . '</br>');

		// create a list of MLS listings

		$mlsList = Array();
		if (!EXP_SYNC_FLAG_ALL) {
			$rowCount = $DATA_RESPONSE->getRowCount();
			for ($i = 0; $i < $rowCount; ++$i) {
				$data_row = $DATA_RESPONSE->getRow($i);
if ($data_row[$unique_key] != 'localhost-143') {
                        	$mlsList[$data_row[$unique_key]] = true;
}
			}
		}

		// create an connection to the RDB 

		$conn = $this->createConnection($TARGET_CONTEXT);
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

/*
		$localList = $this->createLocalList($SOURCE_CONTEXT,
				$TARGET_CONTEXT,
				$EXTRACT_CONTEXT);
		// TARGET dependent processing

		if ($TARGET_CONTEXT->target_type == 'OR') {
			$classID = -1;
			$TARGET = new Target();
			if ($TARGET->requiresClassesPrimitive($TARGET_CONTEXT->class_table)) {
				$classID = $this->lookupClassTypeOR($conn,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT);
						}
//print('clean classID: ' . $classID . '</br>');
		}
*/

		$localList = null;
		switch ($TARGET_CONTEXT->target_type) {
			case 'RDB':
	                        $localList = $this->local_rdb_list(
						$conn,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_column);
				break;

			case 'OR':

				// clear ADBDB cache

				$this->clearCache($TARGET_CONTEXT->cache_path);

				// if this is OpenRealty V 2.1 or greater, find the classID 

				$classID = -1;
				$TARGET = new Target();
				if ($TARGET->requiresClassesPrimitive($TARGET_CONTEXT->class_table)) {
					$classID = $this->lookupClassTypeOR($conn,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT);
				}
//print('clean classID: ' . $classID . '</br>');

				// create a local list

	                        $localList = $this->local_or_list(
						$conn,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_column,
						$classID);

				break;
		}

//print_r($localList);
//print('</br>');

		// compare lists 

		foreach ($localList as $key => $val) {
			$buffer = null;
			if (!array_key_exists($key, $mlsList)) {
print('</br>' . $key . ' does not exist in the MLS,');
//				$BATCH_RESULT->addError($key . ' does not exist in the MLS, local cleanup proceeding.');
				$BATCH_RESULT->addOrphanedItem();
				switch ($TARGET_CONTEXT->target_type) {
					case 'RDB':
						$buffer = $this->clean_rdb($conn,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT,
							$BATCH_RESULT,
							$key,
                                                	$unique_column);
						break;

					case 'OR':
						$buffer = $this->clean_or($conn,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT,
							$BATCH_RESULT,
							$key,
							$classID);
						break;
				}
			}
			$BATCH_RESULT->addProcessedItem();
			if ($EXTRACT_CONTEXT->trace) {
				$BATCH_RESULT->printBatchDetail('RETS Listing: ' . $key . $buffer);
			} else {

				// print dots

				if (!$quiet) {
					$BATCH_RESULT->printBatchNote();
				}
			}
		}

		// close database

		$conn->Close();

		// print errors

		if ($BATCH_RESULT->hasErrors()) {
			$BATCH_RESULT->printErrors();
		}

		return $BATCH_RESULT;
	}

	function lookupClassTypeOR($conn,
				$SOURCE_CONTEXT,
				$TARGET_CONTEXT,
				$EXTRACT_CONTEXT) {
		$METADATA_CLASS = new ClassMetadata($SOURCE_CONTEXT->name,
					$SOURCE_CONTEXT->resource);
		$METADATA_CLASS->read();
		$targetName = $METADATA_CLASS->findField($SOURCE_CONTEXT->class_name,
					$EXTRACT_CONTEXT->class_name_type);
		$sql = 'SELECT class_id FROM ' . $TARGET_CONTEXT->class_table .
			" WHERE class_name = '" . $targetName . "'";
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			return false;
		}
		return $recordSet->fields['class_id'];
	}

	function local_rdb_list($conn,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_column) {

		switch ($TARGET_CONTEXT->brand)
		{
			case 'mysql':
				$wrapper_begin = '`';
				$wrapper_end = '`';
				break;

			case 'mssql':
				$wrapper_begin = '[';
				$wrapper_end = ']';
				break;

			default:
				$wrapper_begin = null;
				$wrapper_end = null;
		}

		// check if listing exists

		$sql = 'SELECT ' . $wrapper_begin . $unique_column . $wrapper_end . ' FROM ' . $TARGET_CONTEXT->data_table;

		$recordSet = $conn->Execute($sql);
		if ($recordSet == null) {
//
// FIXME typically happens when UNIQUE_KEY is not set
//
		} else {
			if ($recordSet === false) {
				$BATCH_RESULT->addError('local_rdb get ERROR ' . $sql);
			}
                        $aList = null;
			while (!$recordSet->EOF) {
				$aList[$recordSet->fields[$unique_column]] = true;
				$recordSet->MoveNext();
			}
                        return $aList;
                }

                return null;
        }

	function clean_rdb($conn,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_value,
		$unique_column) {

		// wrapper depending on database type 

		switch ($TARGET_CONTEXT->brand)
		{
			case 'mysql':
				$wrapper_begin = '`';
				$wrapper_end = '`';
				break;

			case 'mssql':
				$wrapper_begin = '[';
				$wrapper_end = ']';
				break;

			default:
				$wrapper_begin = null;
				$wrapper_end = null;
		}

		if ($TARGET_CONTEXT->include_images) {

			// organize image data 

			$imageInputFilter = $EXTRACT_CONTEXT->image_maps['SOURCE'];
			$sqlImagePath = null;
			if ($imageInputFilter != null) {
				$imageOutputFilter = $EXTRACT_CONTEXT->image_maps['TARGET'];
				$index = 0;
				foreach ($imageOutputFilter as $key => $val) {
					if ($imageInputFilter[$index] != NO_VALUE_INDICATOR) {

					// look if the input filter signifies a path

						if ($imageInputFilter[$index] == 'PATH') {
							$sqlImagePath = $val;
						}

					// store info in arrays 

					}
					++$index;
				}
			}
		}

		// create a generic where clause

		$sqlWhere = ' WHERE ' . $wrapper_begin . $unique_column . 
			$wrapper_end . ' = ' . $conn->qstr($unique_value);

		// clean up data 

		$sql = 'DELETE FROM ' . $TARGET_CONTEXT->data_table . $sqlWhere;
//print('DATA SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('duplicate delete data ERROR ' . $sql);
		}

		// clean up images 

		$buffer = null;
		if ($TARGET_CONTEXT->include_images) {
			if ($sqlImagePath == null) {
print('IMAGE MAP is missing or the PATH element is not included in the map</br>');
			} else { 
				$sqlWhere = ' WHERE ' . $wrapper_begin . $TARGET_CONTEXT->image_table_key . 
				$wrapper_end . '=' . $conn->qstr($unique_value);
				$sql = 'SELECT ' . $sqlImagePath . ' FROM ' . $TARGET_CONTEXT->image_table . $sqlWhere;
//print('IMAGE SEARCH SQL ' . $sql . '</br>');
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
					$BATCH_RESULT->addError('duplicate select image ERROR ' . $sql);
				} else {
					if ($recordSet != null) {
                                	        $imageCount = 0;
						while (!$recordSet->EOF) {
					    	 	$dup_image = $recordSet->fields[$sqlImagePath];
							if (file_exists($dup_image)) {
								unlink($dup_image);
//print('IMAGE FILE UNLiNK ' . $dup_image . '</br>');
								++$imageCount;
							} else {
print('IMAGE FILE UNLINK ' . $dup_image . ' is listed in the RDB, but is not on the disk</br>');
							}
							$recordSet->MoveNext();
						}
						$sql = 'DELETE FROM ' . $TARGET_CONTEXT->image_table . $sqlWhere;
//print('IMAGE DELETE SQL ' . $sql . '</br>');
						$recordSet2 = $conn->Execute($sql);
						if ($recordSet2 === false) {
							$BATCH_RESULT->addError('duplicate delete image ERROR ' . $sql);
						}
						$buffer .= ', ' . $imageCount . ' images deleted.';
					}
				}
			}
		}

		return $buffer;
	}

	function local_or_list( $conn,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_target_key,
		$classID) {

		// lookup listings by class (OR Version 2.1 or above only)

		if ($classID != -1) {
			$sql = 'SELECT listingsdbelements_field_value FROM ' . $TARGET_CONTEXT->data_table . ',' . $TARGET_CONTEXT->class_listing_table . ' WHERE ' . $TARGET_CONTEXT->class_listing_table . '.class_id = ' . $classID . " AND listingsdbelements_field_name = '" . $unique_target_key . "' AND " . $TARGET_CONTEXT->data_table . '.listingsdb_id = ' . $TARGET_CONTEXT->class_listing_table . '.listingsdb_id AND ' . $TARGET_CONTEXT->data_table . '.userdb_id = ' . $EXTRACT_CONTEXT->user_ID;
//print($sql . '</br>');
			$recordSet = $conn->Execute($sql);
			if ($recordSet === false) {
				$BATCH_RESULT->addError('local_rdb get ERROR ' . $sql);
			}
               	        $aList = null;
			while (!$recordSet->EOF) {
				$aList[$recordSet->fields['listingsdbelements_field_value']] = true;
				$recordSet->MoveNext();
			}
                        return $aList;
		}

print('This version of OR is older than Version 2.1</br>');

		// look up listings 

		$sql = 'SELECT listingsdbelements_field_value FROM ' . $TARGET_CONTEXT->data_table . " WHERE listingsdbelements_field_name = '" . $unique_target_key . "' AND " . $TARGET_CONTEXT->data_table . '.userdb_id = ' . $EXTRACT_CONTEXT->user_ID;
//print($sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet == null) {
//
// FIXME typically happens when UNIQUE_KEY is not set
//
		} else {
			if ($recordSet === false) {
				$BATCH_RESULT->addError('local_rdb get ERROR ' . $sql);
			}
                        $aList = null;
			while (!$recordSet->EOF) {
				$aList[$recordSet->fields['listingsdbelements_field_value']] = true;
				$recordSet->MoveNext();
			}
                        return $aList;
                }

                return null;
	}

	function clean_or($conn,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_value,
		$classID) {

		$buffer = null;

		// find the internal OpenRealty listing ID 

		$sql = 'SELECT listingsdb_id FROM ' . $TARGET_CONTEXT->data_table . " WHERE listingsdbelements_field_value = '" . $unique_value . "'";
//print('DATA FIND SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('get random ERROR ' . $sql);
		}
		$anID = $recordSet->fields['listingsdb_id'];

		if (EXP_SYNC_SET_INACTIVE) {

			// set the listing to inactive

print('OpenRealty listing id [' . $anID . '] set to inactive</br>');

			$sql = 'UPDATE ' . $TARGET_CONTEXT->index_table . 
				" SET listingsdb_active = 'no' " . 
				" WHERE listingsdb_id = '" . $anID . "'";
//print('DATA UPDATE INDEX SQL ' . $sql . '</br>');
			$recordSet = $conn->Execute($sql);
		} else {
			$buffer .= $this->delete_or_listing($conn,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT,
					$BATCH_RESULT,
					$anID,
					$classID);
		}

		return $buffer;
	}

	function delete_or_listing($conn,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$listingID,
		$classID) {

print('OpenRealty listing id [' . $listingID . '] is being deleted</br>');
		$sqlWhere = " WHERE listingsdb_id = '" . $listingID . "'";

		// delete data index for the listing

		$index_table = $TARGET_CONTEXT->index_table;
		$sql = 'DELETE FROM ' . $index_table . $sqlWhere;
//print('DATA DELETE INDEX SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('delete data index ERROR ' . $sql);
		}

		// delete data elements from the listing

		$sql = 'DELETE FROM ' . $TARGET_CONTEXT->data_table . $sqlWhere;
//print('DATA DELETE SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('delete data ERROR ' . $sql);
		}

		// delete class information, OR Version 2.1 or higher

//print('class ID ' . $classID . '</br>');
		if ($classID != -1) {
			$sql = 'DELETE FROM ' . $TARGET_CONTEXT->class_listing_table . $sqlWhere;
//print($sql . '</br>');
			$recordSet = $conn->Execute($sql);
			if ($recordSet === false) {
				$BATCH_RESULT->addError('delete class info ERROR ' . $sql);
			}
		}

		// delete all images for this listing

		$sql = 'SELECT listingsimages_file_name, listingsimages_thumb_file_name FROM ' . $TARGET_CONTEXT->image_table . $sqlWhere; 
//print('IMAGE FIND SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('local_rdb get image ERROR ' . $sql);
		}
		while (!$recordSet->EOF) {
			$image_name = $recordSet->fields['listingsimages_file_name'];
			$image_path = $TARGET_CONTEXT->image_upload_path . '/' . $image_name;
			if (file_exists($image_path)) {
//print('delete image ' . $image_path . '</br>');
				unlink($image_path);
				$BATCH_RESULT->addRaw();
				$thumb_name = $recordSet->fields['listingsimages_thumb_file_name'];
				$thumb_path = $TARGET_CONTEXT->image_upload_path . '/' . $thumb_name;
				if (file_exists($thumb_path)) {
//print('delete thumb ' . $thumb_path . '</br>');
					$BATCH_RESULT->addThumb();
					unlink($thumb_path);
				}
			}
			$recordSet->MoveNext();
		}

		$sql = 'DELETE FROM ' . $TARGET_CONTEXT->image_table . $sqlWhere;
//print('IMAGE DELETE SQL ' . $sql . '</br>');
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('delete from image ERROR ' . $sql);
		}
	}

	function calculateLastBatch($totalItems,
					$batchSize) {
		$last = ($totalItems / $batchSize) + 1;
		$temp = round($last);
		if ($last > $temp) {
			return $temp + 1;
		}

		return $temp;
	}

	function getBatchWithServerHelp($DATA_REQUEST,
			$SOURCE_CONTEXT,
			$TARGET_CONTEXT,
			$EXTRACT_CONTEXT,
			$quiet,
                        $query_array,
			&$EXTRACT_RESULT) {

		// calculate the total number of expected results

		$COUNT_DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
					 			$query_array,
								$SOURCE_CONTEXT->unique_key);
		$COUNT_DATA_REQUEST->setQueryCriteria($DATA_REQUEST->getQueryCriteria());
		$COUNT_DATA_REQUEST->setLimit($this->limit);
		$DATA_RESPONSE = $this->EXCHANGE->countDataDirect($COUNT_DATA_REQUEST,
			$SOURCE_CONTEXT->detected_standard_names,
			$SOURCE_CONTEXT->compact_decoded_format);
		$totalItems = $DATA_RESPONSE->getQueryCount();

		// calculate the batch parameters

		$batch_size = $EXTRACT_CONTEXT->getBatchSize();
		$last = $this->calculateLastBatch($totalItems,
							$batch_size);
		$totalBatches = $last - 1;

		// runtime information

		$spacer = $EXTRACT_RESULT->getSpacer();
		$eol = $EXTRACT_RESULT->getEOL();
		if (!$quiet) {
			flush_output($spacer . 'Pagination supported by the server' .
					$eol .
					$spacer . 'Available Listings: ' . $totalItems .
					$eol .
					$spacer . 'Batches required: ' . $totalBatches .
					$eol .
					$spacer . 'Batch size: ' . $batch_size .
					$eol);
		}

		if ($totalBatches == 1) {
			flush_output($spacer . 'Optimization for single batches being used ' .
				$eol);
			$this->priv_getBatch($quiet,
				$this->limit,
				1,
				$DATA_REQUEST,
				$SOURCE_CONTEXT,
				$TARGET_CONTEXT,
				$EXTRACT_CONTEXT,
				$EXTRACT_RESULT);
			$this->EXCHANGE->logoutDirect();

                } else {
			$this->EXCHANGE->logoutDirect();

			// begin batch

			$checkBigLimit = true;
			$bigLimit = $DATA_REQUEST->getLimit();
			if ($bigLimit == 'NONE'){
				$checkBigLimit = false;
			}
			$DATA_REQUEST->setLimit($batch_size);
			for ($i = 1; $i < $last; $i++) {
				if ($checkBigLimit) {
					$pending = (($i - 1 ) * $batch_size) + $batch_size;
					if ($pending > $bigLimit)
        		                {
						$newLimit = $batch_size - ($pending - $bigLimit);
						$DATA_REQUEST->setLimit($newLimit);
						$this->getBatch($quiet,
							$newLimit,
							$i,
							$DATA_REQUEST,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT,
							$EXTRACT_RESULT);
        		                        break;
                		        } else {
						$this->getBatch($quiet,
							$batch_size,
							$i,
							$DATA_REQUEST,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT,
							$EXTRACT_RESULT);
                        		}
				} else {
					$this->getBatch($quiet,
						$batch_size,
						$i,
						$DATA_REQUEST,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$EXTRACT_RESULT);
                	        }
			}
		}
	}

	function getBatchWithMemoryMethod($DATA_REQUEST,
			$SOURCE_CONTEXT,
			$TARGET_CONTEXT,
			$EXTRACT_CONTEXT,
			$quiet,
			&$EXTRACT_RESULT) {

		// runtime information

		$spacer = $EXTRACT_RESULT->getSpacer();
		$eol = $EXTRACT_RESULT->getEOL();
		if (!$quiet) {
			flush_output($spacer . 'Using in-memory workaround' .
					$eol);
		}

		// pull the relevant keys from the server

		$DATA_RESPONSE = $this->EXCHANGE->searchDataDirect($DATA_REQUEST,
					$SOURCE_CONTEXT->detected_standard_names,
					1,
					$SOURCE_CONTEXT->compact_decoded_format);
		$this->EXCHANGE->logoutDirect();

		// determine total items

		$rowCount = $DATA_RESPONSE->getRowCount();
		$items = null;
		for ($i = 0; $i < $rowCount; ++$i) {
			$row = $DATA_RESPONSE->getRow($i);
			$items[] = $row[$SOURCE_CONTEXT->unique_key];
		}
		$totalItems = sizeof($items);

		// calculate the batch parameters

		$batch_size = $EXTRACT_CONTEXT->getBatchSize();
		$last = $this->calculateLastBatch($totalItems, $batch_size);

		// runtime information

		if (!$quiet) {
			flush_output($spacer . 'Items to be processed: ' . $totalItems .
					$eol .
					$spacer . 'Batchs to be processed: ' . ($last - 1) .
					$eol .
					$spacer . 'Batch size: ' . $batch_size .
					$eol);
		}

		// begin batch

		$itemCount = 0;
		if ($items != null) {
			$count = 0;
			$batchCount = 0;
			$key_array = null;
			foreach ($items as $key => $value) {
				++$itemCount;
				$key_array[] = $value;
				++$count;
				if ($itemCount == $totalItems) {
					$count = $batch_size;
				}
				if ($count == $batch_size) {
					++$batchCount;
					$this->getSpecificRecords($quiet,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$key_array,
						$batchCount,
						$EXTRACT_RESULT);
					$key_array = null;
					$count = 0;
				}
			}
		}
	}

	function getBatchWithDiskMethod($DATA_REQUEST,
			$SOURCE_CONTEXT,
			$TARGET_CONTEXT,
			$EXTRACT_CONTEXT,
			$quiet,
			&$EXTRACT_RESULT) {

		// runtime information

		$spacer = $EXTRACT_RESULT->getSpacer();
		$eol = $EXTRACT_RESULT->getEOL();
		if (!$quiet) {
			flush_output($spacer . 'Using disk caching workaround' .
					$eol .
					$spacer . 'Memory cache buffer size (bytes): ' .
					$EXTRACT_CONTEXT->cache_size .
					$eol);
		}

		// pull the relevant keys from the server and write them to disk

		$this->tempFilePath = $EXTRACT_CONTEXT->working_file_path .
					'/temp-' .  $EXTRACT_CONTEXT->name;
		$this->EXCHANGE->searchDataTextStream($DATA_REQUEST,
				$SOURCE_CONTEXT->compact_decoded_format,
				$SOURCE_CONTEXT->detected_standard_names,
				$this,
				1);
		$this->EXCHANGE->logoutDirect();

		// check if results were returned

		if (!file_exists($this->tempFilePath)) {
			$buffer = $spacer . 'No results were found' . $eol;
			flush_output($buffer);
			return 0;
		}

		// calculate the batch parameters

		$batch_size = $EXTRACT_CONTEXT->getBatchSize();

		// runtime information

		if (!$quiet) {
			flush_output($spacer . 'Batchs to be processed: N/A' .
					$eol .
					$spacer . 'Batch size: ' . $batch_size .
					$eol);
		}

		// set up parser

		$aParser = new CompactParser();

		// process temp file

		$tempFileHandle = fopen($this->tempFilePath, 'r');
		while (!feof($tempFileHandle)) {
			$contents = fread($tempFileHandle, $EXTRACT_CONTEXT->cache_size);
			$aParser->parse($contents, false);
			$maxCols = sizeof($aParser->getColumns());
			if ($maxCols > 0) {
				$temp = $aParser->getData();
				$items = null;
				foreach ($temp as $key => $value) {
					$items[] = $value[$SOURCE_CONTEXT->unique_key];
				}

				if ($items != null) {
					$totalItems = sizeof($items);
					$key_array = null;
					$count = 0;
					$batchCount = 0;
					foreach ($items as $key => $value) {
						$key_array[] = $value;
						++$count;
						if ($count == $totalItems) {
							$count = $batch_size;
						}
						if ($count == $batch_size) {
							++$batchCount;
							$this->getSpecificRecords($quiet,
								$SOURCE_CONTEXT,
								$TARGET_CONTEXT,
								$EXTRACT_CONTEXT,
								$key_array,
								$batchCount,
								$EXTRACT_RESULT);
							$key_array = null;
							$totalItems = $totalItems - $count;
							$count = 0;
						}
					}
				}
			}
		}
		fclose($tempFileHandle);
		unlink($this->tempFilePath);

		// close parser

		$aParser->parse('', true);
	}

	function getSpecificRecords($quiet,
			$SOURCE_CONTEXT,
			$TARGET_CONTEXT,
			$EXTRACT_CONTEXT,
			$key_array,
			$batchCount,
			&$EXTRACT_RESULT) {

		// degine selection criteria

		$selections = $this->constructSelections($SOURCE_CONTEXT,
					$EXTRACT_CONTEXT,
					$TARGET_CONTEXT);
                if ($this->complexQueries) {

			// grouping of specifics are allowed

			$temp_array[$SOURCE_CONTEXT->unique_key] = $key_array;
			$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
						$temp_array,
						$selections);
			$count = $this->getBatch($quiet,
					$DATA_REQUEST->getLimit(),
					$batchCount,
					$DATA_REQUEST,
					$SOURCE_CONTEXT,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT,
					$EXTRACT_RESULT);
	                if ($count != sizeof($key_array)) {
				$this->complexQueries = false;
				if (!$quiet) {
					$spacer = $EXTRACT_RESULT->getSpacer();
					$eol = $EXTRACT_RESULT->getEOL();
					$buffer = $spacer . 
//---------
//						'As a workaround, a single transaction will be used for each transaction (no batching) resulting in an extremely slow download.' .
						'As a workaround, a single transaction will be used for each transaction in this batch.' .
						$eol;
					flush_output($buffer);
				}
                	}
                }

                if (!$this->complexQueries) {
                        $count = 0;
			foreach ($key_array as $key => $value) {
				$temp_array[$SOURCE_CONTEXT->unique_key] = $value;
				$DATA_REQUEST = $this->constructDataRequest($SOURCE_CONTEXT,
							$temp_array,
							$selections);
				$count += $this->getBatch($quiet,
						$DATA_REQUEST->getLimit(),
						$batchCount,
						$DATA_REQUEST,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$EXTRACT_RESULT,
						false);
			}
//---------
			$this->complexQueries = true;
//---------
/*
	                if ($count != sizeof($key_array)) {
				if (!$quiet) {
					$spacer = $EXTRACT_RESULT->getSpacer();
					$eol = $EXTRACT_RESULT->getEOL();
					$buffer = $spacer . 'Workaround was not completely successful.' .
							$eol;
					flush_output($buffer);
				}
				return;
			}
*/
			if (!$quiet) {
				flush_output($buffer);
			}
                }
	}

	function getBatch($quiet,
		$batch_size,
		$batch_number,
		$DATA_REQUEST,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$EXTRACT_RESULT,
		$withDetail = true) {

		// iterate listings

		$size = 0;
		$result = $this->EXCHANGE->loginDirect($SOURCE_CONTEXT->account,
			$SOURCE_CONTEXT->password,
			$SOURCE_CONTEXT->url,
			$SOURCE_CONTEXT->detected_maximum_rets_version,
			$SOURCE_CONTEXT->application,
			$SOURCE_CONTEXT->version,
			$SOURCE_CONTEXT->clientPassword,
			$SOURCE_CONTEXT->postRequests);
		$PROCESS_RESULTS = new ProcessResults();
		if ($result) {
			$size = $this->priv_getBatch($quiet,
					$batch_size,
					$batch_number,
					$DATA_REQUEST,
					$SOURCE_CONTEXT,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT,
					$EXTRACT_RESULT,
					$withDetail);
			$this->EXCHANGE->logoutDirect();

		} else {
			$PROCESS_RESULTS->addError(NO_SERVICE_MESSAGE);
		}

		if ($PROCESS_RESULTS->hasErrors()) {
			$PROCESS_RESULTS->printErrors();
		}

		return $size;
	}

	function priv_getBatch($quiet,
		$batch_size,
		$batch_number,
		$DATA_REQUEST,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$EXTRACT_RESULT,
		$withDetail = true) {

		$size = 0;

		// lookup text

//print_r($DATA_REQUEST);
		$DATA_RESPONSE = $this->EXCHANGE->searchDataDirect($DATA_REQUEST,
			$SOURCE_CONTEXT->detected_standard_names,
		        (($batch_number - 1) * $batch_size) + 1 + $SOURCE_CONTEXT->offset_adjustment,
			$SOURCE_CONTEXT->compact_decoded_format);
//print_r($DATA_RESPONSE);
/*
$rowCount = $DATA_RESPONSE->getRowCount();
if ($rowCount > 1) {
$EXCHANGE->setError("Simulated error");
}
*/
		if ($this->EXCHANGE->hasErrors()) {
			if (!$quiet) {
				$this->printQueryError($EXTRACT_RESULT->getSpacer(),
					$EXTRACT_RESULT->getEOL(),
					$this->EXCHANGE->getLastError(),
			        	$DATA_REQUEST->getQueryCriteria());
          		}
			$this->EXCHANGE->resetErrors();
          	}
		else {
			// bind text and images

			if ($DATA_RESPONSE->getRowCount() > 0) {
				$BATCH_RESULT = $this->processBatchResponse($quiet,
					$batch_number,
					$DATA_RESPONSE,
					$SOURCE_CONTEXT,
					$TARGET_CONTEXT,
					$EXTRACT_CONTEXT,
					$withDetail);

				$EXTRACT_RESULT->summarizeBatch($BATCH_RESULT);
				$size = $BATCH_RESULT->getProcessed();
			}
               	        else
                       	{
				$errorParser = new ErrorParser();
				$errorParser->parse($DATA_RESPONSE->getRawData());
				if ($errorParser->error_check) {
					if (!$quiet) {
						$this->printQueryError($EXTRACT_RESULT->getSpacer(),
							$EXTRACT_RESULT->getEOL(),
							$errorParser->getErrorText(),
				        		$DATA_REQUEST->getQueryCriteria());
					}
				}
			}
		}

		return $size;
	}

	function getUniqueTargetKey($EXTRACT_CONTEXT,
			$SOURCE_CONTEXT) {

		// find correct source map

		if (array_key_exists('SOURCE', $EXTRACT_CONTEXT->data_maps)) {
			$sourceMap = $EXTRACT_CONTEXT->data_maps['SOURCE'];
		} else {
			$sourceMap = explode(',',
				$EXTRACT_CONTEXT->extract_column_list);
		}

		// find position of the unique key in the source map

		$pos = -1;
		foreach ($sourceMap as $key => $value) {
			if ($value == $SOURCE_CONTEXT->unique_key) {
				$pos = $key;
			}
		}

		// find unique key in the target list

		$unique_target_key = null;
		if ($pos > -1) {
			if (array_key_exists('TARGET', $EXTRACT_CONTEXT->data_maps)) {
				$targetMap = $EXTRACT_CONTEXT->data_maps['TARGET'];
				$unique_target_key = $targetMap[$pos];
			} else {
				$unique_target_key = $sourceMap[$pos];
			}
		}

		return $unique_target_key;
	}

	function printQueryError($spacer,
		$eol,
		$text,
		$criteria) {
		flush_output( $eol .
			$spacer . 'While processing DMQL Query: "' .
			$criteria . '", ' .
			$eol .
			$spacer . 'the server returned the message: "' .
			$text . '"' .
			$eol .
			$eol);
        }

        function processImages($TARGET_CONTEXT) {
		switch ($TARGET_CONTEXT->target_type) {
			case 'CSV':
				if (!$TARGET_CONTEXT->include_images) {
					return false;
				}
				break;
			case 'XML':
				if (!$TARGET_CONTEXT->include_images) {
					return false;
				}
				break;
		}

		return true;
	}

	function processBatchResponse($quiet,
		$batch_number,
		$DATA_RESPONSE,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		$withDetail) {

		$BATCH_RESULT = new Statistics('Batch #' . $batch_number);
		if ($EXTRACT_CONTEXT->trace) {
			if ($withDetail) {
				$BATCH_RESULT->printBatchStart();
			}
		}

		// if RDB-based

          	$dataTableKey = null; 
		switch ($TARGET_CONTEXT->target_type) {
			case 'RDB':
				$conn = $this->createConnection($TARGET_CONTEXT);
				if (EXP_ALLOW_DUP_MAP_COLUMNS) {
          				$dataTableKey = $TARGET_CONTEXT->data_table_key; 
				}
				break;

			case 'OR':
				$conn = $this->createConnection($TARGET_CONTEXT);

				// note added to downloaded items

				$notes = 'Downloaded with vieleRETS from RETS server ' . $SOURCE_CONTEXT->detected_server_name . ' on ' . date('F j, Y') . ' at ' . date('g:i:s A');

				// check for class requirement

				$classID = -1;
				$TARGET = new Target();

				// OR Version 2.1 or higher only

				if ($TARGET->requiresClassesPrimitive($TARGET_CONTEXT->class_table)) {

			// +------------+-------------+------+-----+---------+----------------+
			// | Field      | Type        | Null | Key | Default | Extra          |
			// +------------+-------------+------+-----+---------+----------------+
			// | class_id   | int(11)     |      | PRI | NULL    | auto_increment |
			// | class_name | varchar(80) |      |     |         |                |
			// | class_rank | smallint(6) |      | MUL | 0       |                |
			// +------------+-------------+------+-----+---------+----------------+

					// check if class exists

					$classID = $this->lookupClassTypeOR($conn,
							$SOURCE_CONTEXT,
							$TARGET_CONTEXT,
							$EXTRACT_CONTEXT);
//print('classID: ' . $classID . '</br>');

					if ($classID == -1) {
						$sql = 'SELECT MAX(class_rank) AS max_rank, MAX(class_id) AS max_id FROM ' . $TARGET_CONTEXT->class_table;
						$recordSet = $conn->Execute($sql);
						if ($recordSet === false) {
							$BATCH_RESULT->addError('class rank ERROR ' . $sql);
						}
						$classRank = $recordSet->fields['max_rank'];
						$classRank++;
						$classID = $recordSet->fields['max_id'];
						$classID++;
						$sql = 'INSERT INTO ' . $TARGET_CONTEXT->class_table . ' (class_name,class_rank) VALUES(' . "'" . $targetName . "'," . $classRank . ');';
						$recordSet = $conn->Execute($sql);
						if ($recordSet === false) {
							$BATCH_RESULT->addError('Class creation ERROR ' . $sql);
						}
					}
				}

				// external program setup

				if ($TARGET_CONTEXT->make_thumbnail) {
					if ($TARGET_CONTEXT->thumbnail_program == 'gd') {
						$thumb_prog = 'make_thumb_gd';
					} else {
						$thumb_prog = 'make_thumb_imagemagick';
					}
				}

				// one time initializations

				$timestamp = $conn->DBTimeStamp(time());
				$expiration_date_base = mktime(0,
					0,
					0,
					date('m'),
					date('d') + $TARGET_CONTEXT->days_until_listings_expire,
					date('Y'));
				$expiration_date = $conn->DBDate($expiration_date_base);

				// clear ADBDB cache

				$this->clearCache($TARGET_CONTEXT->cache_path);

				// lookup unique key

	                	$unique_target_key = $this->getUniqueTargetKey($EXTRACT_CONTEXT,
							$SOURCE_CONTEXT);
				break;
		}

		// initialize

		$unique_key = $SOURCE_CONTEXT->unique_key;
		$ownership_field = $SOURCE_CONTEXT->ownership;
		$types = $DATA_RESPONSE->getTypes();
		$interpretations = $DATA_RESPONSE->getInterpretations();

		// create listing objects

		$rowCount = $DATA_RESPONSE->getRowCount();
		for ($i = 0; $i < $rowCount; ++$i) {
			$data_row = $DATA_RESPONSE->getRow($i);
			$LISTING_OBJECT = new ListingObject();

			if ($EXTRACT_CONTEXT->image_maps == null) {
				if ($EXTRACT_CONTEXT->data_maps == null) {
					$tempMap = explode(',', $EXTRACT_CONTEXT->extract_column_list);
					$LISTING_OBJECT->setData($tempMap,
						$data_row,
						$types,
						$interpretations,
						$unique_key,
						$tempMap,
					        $EXTRACT_CONTEXT->metacolumn_map,
						$dataTableKey);
				} else {
					if (array_key_exists('SOURCE', $EXTRACT_CONTEXT->data_maps)) {
						$LISTING_OBJECT->setData($EXTRACT_CONTEXT->data_maps['SOURCE'],
							$data_row,
							$types,
							$interpretations,
							$unique_key,
							$EXTRACT_CONTEXT->data_maps['TARGET'],
					                $EXTRACT_CONTEXT->metacolumn_map,
							$dataTableKey);
					} else {
						$tempMap = explode(',', $EXTRACT_CONTEXT->extract_column_list);
						$LISTING_OBJECT->setData($tempMap,
							$data_row,
							$types,
							$interpretations,
							$unique_key,
							$tempMap,
					                $EXTRACT_CONTEXT->metacolumn_map,
							$dataTableKey);
					}
				}
			} else {
				$LISTING_OBJECT->setData($EXTRACT_CONTEXT->data_maps['SOURCE'],
					$data_row,
					$types,
					$interpretations,
					$unique_key,
					$EXTRACT_CONTEXT->data_maps['TARGET'],
					$EXTRACT_CONTEXT->metacolumn_map,
					$dataTableKey,
					$EXTRACT_CONTEXT->image_maps['SOURCE'],
					$EXTRACT_CONTEXT->image_maps['TARGET']);
			}

			if (array_key_exists($ownership_field, $data_row)) {
				$LISTING_OBJECT->setOwner($data_row[$ownership_field]);
			}

			$unique_value = $LISTING_OBJECT->getUniqueValue();
			$buffer = ' UNKNOWN TARGET TYPE';

			// if the RETS Server supports location, use proxies

                        if ($this->processImages($TARGET_CONTEXT)) {
				if ($SOURCE_CONTEXT->media_location) {
					if ($this->EXCHANGE->getTransportTrace()) {
						$this->EXCHANGE->trace('Downloading images via Media Server because the RETS server supports Location');
					}
					$this->bindImages($LISTING_OBJECT,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT);
				} else {
					if ($this->EXCHANGE->getTransportTrace()) {
						$this->EXCHANGE->trace('Downloading images directly because the RETS server does not support Location');
					}
				}
			}
			switch ($TARGET_CONTEXT->target_type) {
				case 'CSV':
					$buffer = $this->write_csv($LISTING_OBJECT,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_value);
					break;

				case 'XML':
					$buffer = $this->write_xml($LISTING_OBJECT,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_value);
					break;

				case 'RDB':
					$buffer = $this->write_rdb($LISTING_OBJECT,
						$conn,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$unique_value);
					break;

				case 'OR':
					$buffer = $this->write_or($LISTING_OBJECT,
						$conn,
						$SOURCE_CONTEXT,
						$TARGET_CONTEXT,
						$EXTRACT_CONTEXT,
						$BATCH_RESULT,
						$classID,
						$unique_target_key,
						$unique_value,
						$expiration_date,
						$notes,
						$timestamp,
						$thumb_prog);
					break;
			}

			$BATCH_RESULT->addProcessedItem();
			if ($EXTRACT_CONTEXT->trace) {
				$BATCH_RESULT->printBatchDetail('RETS Listing: ' . $unique_value . $buffer);
			} else {
				if (!$quiet) {
					$BATCH_RESULT->printBatchNote();
				}
			}
		}

		// close database

		if ($TARGET_CONTEXT->target_type == 'RDB' ||
			$TARGET_CONTEXT->target_type == 'OR') {
			$conn->Close();
		}

		// print errors

		if ($BATCH_RESULT->hasErrors()) {
			$BATCH_RESULT->printErrors();
		}

		// summary

		if ($EXTRACT_CONTEXT->trace) {
			if ($withDetail) {
				$BATCH_RESULT->printBatchSummary();
			}
		}

		return $BATCH_RESULT;
	}

	function write_csv($LISTING_OBJECT,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_value)
	{
		$buffer = null;
		$csv = null;
		$data = $LISTING_OBJECT->getData();
		foreach ($data as $key => $value) {
			$csv .= '"' . str_replace('"', '""', $value) . '",';
		}
		if ($this->dataHandle != null) {
			fwrite($this->dataHandle, substr($csv, 0, strlen($csv) - 1) . "\r\n");
		}
		$BATCH_RESULT->addAdditionalItem();

		// images

		if ($TARGET_CONTEXT->include_images) {
			if ($TARGET_CONTEXT->image_reference_only) {
				if ($SOURCE_CONTEXT->media_location) {
					$images = $LISTING_OBJECT->getImages();
					if ($images != null) {
						foreach ($images as $key => $aURL) {
	
							// generate CSV
	
							fwrite($this->imageHandle,
								'"' . $unique_value . '",' .
								'"' . ($key + 1) . '",' .
								'"' . $aURL . '",' .
								'"REFERENCE_ONLY"' . CRLF);
						}
					}
				} else {
					$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
					$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
					for ($i = 1; $i <= $mediaEnd; $i++) {
						if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
									$i,
									$this->EXCHANGE,
									$unique_value)) {
							if ($this->EXCHANGE->getTransportTrace()) {
								$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
							}
							break;
						}

						// generate CSV

						fwrite($this->imageHandle,
							'"' . $unique_value . '",' .
							'"' . $i . '",' .
							'"DIRECT",' .
							'"REFERENCE_ONLY"' . CRLF);
					}
				}
			} else {
				if ($SOURCE_CONTEXT->media_location) {
					$images = $LISTING_OBJECT->getImages();
					if ($images != null) {
                				$userAgent = buildUserAgent($SOURCE_CONTEXT->application, 
                                            				$SOURCE_CONTEXT->version);
                                                $mediaCount = 0;
						foreach ($images as $key => $aURL) {
							$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
								$unique_value,
								$key + 1);
							if( !$this->downloadImage($save_path,
								$aURL,
								$userAgent,
								$EXTRACT_CONTEXT->trace,
								$BATCH_RESULT)) {
								break;
							}
							if ($this->imageHandle != null) {
								fwrite($this->imageHandle,
									'"' . $unique_value . '",' .
									'"' . ($key + 1 ) . '",' .
									'"' . $aURL . '",' .
									'"' . realpath($save_path) . "\"\r\n");
                                                                              ++$mediaCount;
							}
						}
					}
				} else {
					$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
					$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
					for ($i = $DIRECT_CONTROL->getMediaStart(); $i <= $mediaEnd; $i++) {
						if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
									$i,
									$this->EXCHANGE,
									$unique_value)) {
							if ($this->EXCHANGE->getTransportTrace()) {
								$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
							}
							break;
						}
						$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
							$unique_value,
							$i + 1);
						if ($DIRECT_CONTROL->storeImage($save_path,
								$EXTRACT_CONTEXT->trace,
								$BATCH_RESULT)) {

							// generate CSV

							fwrite($this->imageHandle,
								'"' . $unique_value . '",' .
								'"' . ($i + 1) . '",' .
								'"DIRECT",' .
								'"' . realpath($save_path) . '"' . "\r\n");
						}
					}
				}
			}
		}

		return $buffer;
	}

	function write_xml($LISTING_OBJECT,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_value)
	{
		$buffer = null;
		$xml = "\t<LISTING>\r\n";
		$data = $LISTING_OBJECT->getData();
		foreach ($data as $key => $value) {
			if ($value != null) {
				$xml .= "\t\t<" . $key . '>' .
				htmlentities($value) . '</' . $key . ">\r\n";
			}
		}
		$BATCH_RESULT->addAdditionalItem();

		// images

		if ($TARGET_CONTEXT->include_images) {
			if ($TARGET_CONTEXT->image_reference_only) {
				if ($SOURCE_CONTEXT->media_location) {
					$images = $LISTING_OBJECT->getImages();
					if ($images != null) {
						foreach ($images as $key => $aURL) {

							// generate XML

							$xml .= "\t\t<IMAGE>\r\n" . "\t\t\t<INDEX>" .
							($key + 1) . "</INDEX>\r\n" . "\t\t\t<URL>" .
							htmlentities($aURL) . "</URL>\r\n" . "\t\t\t<PATH>" .
							"REFERENCE_ONLY</PATH>\r\n" . "\t\t</IMAGE>\r\n";
						}
					}
				} else {
					$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
					$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
					for ($i = 1; $i <= $mediaEnd; $i++) {
						if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
										$i,
										$this->EXCHANGE,
										$unique_value)) {
							if ($this->EXCHANGE->getTransportTrace()) {
								$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
							}
							break;
						}

						// generate XML

						$xml .= "\t\t<IMAGE>\r\n" . "\t\t\t<INDEX>" .
						$i . "</INDEX>\r\n" . "\t\t\t<URL>" . "DIRECT" . "</URL>\r\n" . "\t\t\t<PATH>" .
						"REFERENCE_ONLY</PATH>\r\n" . "\t\t</IMAGE>\r\n";
					}
				}
			} else {
				if ($SOURCE_CONTEXT->media_location) {
					$images = $LISTING_OBJECT->getImages();
					if ($images != null) {
                				$userAgent = buildUserAgent($SOURCE_CONTEXT->application, 
                                            				$SOURCE_CONTEXT->version);
						foreach ($images as $key => $aURL) {
							$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
								$unique_value,
								$key + 1);
							if( !$this->downloadImage($save_path,
								$aURL,
								$userAgent,
								$EXTRACT_CONTEXT->trace,
								$BATCH_RESULT)) {
								break;
							}
							$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
								$unique_value,
								$key + 1);

							$xml .= "\t\t<IMAGE>\r\n" . "\t\t\t<INDEX>" .
							($key + 1) . "</INDEX>\r\n" . "\t\t\t<URL>" .
							htmlentities($aURL) . "</URL>\r\n" . "\t\t\t<PATH>" .
							htmlentities(realpath($save_path)) . "</PATH>\r\n" . "\t\t</IMAGE>\r\n";
						}
					}
				} else {
					$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
					$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
					for ($i = $DIRECT_CONTROL->getMediaStart(); $i <= $mediaEnd; $i++) {
						if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
										$i,
										$this->EXCHANGE,
										$unique_value)) {
							if ($this->EXCHANGE->getTransportTrace()) {
								$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
							}
							break;
						}
						$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
							$unique_value,
							$i + 1);
						if ($DIRECT_CONTROL->storeImage($save_path,
								$EXTRACT_CONTEXT->trace,
								$BATCH_RESULT)) {

							// generate XML

							$xml .= "\t\t<IMAGE>\r\n" . "\t\t\t<INDEX>" .
							($i + 1) . "</INDEX>\r\n" . "\t\t\t<URL>" . "DIRECT" . "</URL>\r\n" . "\t\t\t<PATH>" .
							htmlentities(realpath($save_path)) . "</PATH>\r\n" . "\t\t</IMAGE>\r\n";
						}
					}
				}
			}
		}
		$xml .= "\t</LISTING>\r\n";
		fwrite($this->dataHandle, $xml);

		return $buffer;
	}

	function write_rdb($LISTING_OBJECT,
		$conn,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$unique_value) {

		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

		// wrapper depending on database type 

		switch ($TARGET_CONTEXT->brand) {
			case 'mysql':
				$wrapper_begin = '`';
				$wrapper_end = '`';
				break;

			case 'mssql':
				$wrapper_begin = '[';
				$wrapper_end = ']';
				break;

			default:
				$wrapper_begin = null;
				$wrapper_end = null;
		}

		if ($TARGET_CONTEXT->include_images) {

			// organize image data 

			$imageInputFilter = $LISTING_OBJECT->getImageInputFilter();
			$sqlImagePath = null;
			if ($imageInputFilter != null) {
			        $imageOutputFilter = $LISTING_OBJECT->getImageOutputFilter();
			        if ($imageOutputFilter != null) {
					$f_sql = null;
					$v_sql = null;
					$index = 0;
					foreach ($imageOutputFilter as $key => $val) {
						if ($imageInputFilter[$index] != NO_VALUE_INDICATOR) {

							// look if the input filter signifies a path

							if ($imageInputFilter[$index] == 'PATH') {
								$sqlImagePath = $val;
							}

							// store info in arrays 

							$f_sql[] = $val;
							$v_sql[] = $imageInputFilter[$index];
						}
						++$index;
					}
					$sqlImageFields = $f_sql;
					$sqlImageValues = $v_sql;
				}
			}
		}

		// create a generic where clause

		$sqlWhere = ' WHERE ' . $wrapper_begin . $LISTING_OBJECT->getUniqueColumn() . 
			$wrapper_end . ' = ' . $conn->qstr($unique_value);

		// check if listing exists

		$sql = 'SELECT * FROM ' . $TARGET_CONTEXT->data_table . $sqlWhere;
		$recordSet = $conn->Execute($sql);
		$duplicateRemoved = false;
	        $clearToInsert = true;
		if ($recordSet == null) {
//
// FIXME typically happens when UNIQUE_KEY is not set
//
		}
		else {
			if ($recordSet === false) {
				$BATCH_RESULT->addError('duplicate get ERROR ' . $sql);
			}
			while (!$recordSet->EOF) {
				if ($EXTRACT_CONTEXT->refresh) {
	
					// delete existing record
	
					$sql = 'DELETE FROM ' . $TARGET_CONTEXT->data_table . $sqlWhere;
					$recordSet2 = $conn->Execute($sql);
					if ($recordSet2 === false) {
						$BATCH_RESULT->addError('duplicate delete data ERROR ' . $sql);
					}

					if ($TARGET_CONTEXT->include_images) {
						$detectWhere = ' WHERE ' . $wrapper_begin . $TARGET_CONTEXT->image_table_key . 
                                                              $wrapper_end . '=' . $conn->qstr($unique_value);
						$sql = 'SELECT ' . $sqlImagePath . ' FROM ' . $TARGET_CONTEXT->image_table . $detectWhere;
						$recordSet2 = $conn->Execute($sql);
						if ($recordSet2 === false) {
							$BATCH_RESULT->addError('duplicate select image ERROR ' . $sql);
						} else {
							if ($recordSet2 != null) {
								while (!$recordSet2->EOF) {
							    	 	$dup_image = $recordSet2->fields[$sqlImagePath];
									if (file_exists($dup_image)) {
										unlink($dup_image);
									}
									$recordSet2->MoveNext();
								}
							}
						}
	
						$sql = 'DELETE FROM ' . $TARGET_CONTEXT->image_table . $detectWhere;
						$recordSet2 = $conn->Execute($sql);
						if ($recordSet2 === false) {
							$BATCH_RESULT->addError('duplicate delete image ERROR ' . $sql);
						}
					}
					$duplicateRemoved = true;
				} else {
					$clearToInsert = false;
				}
				$recordSet->MoveNext();
			}
		}

		// insert into database

		$buffer = null;
		if ($clearToInsert) {
		// prepare text data into lists

			$data = $LISTING_OBJECT->getData();
//print_r($data);
			$sqlDataFields = null;
			$sqlDataValues = null;
			foreach ($data as $key => $value) {
				if ($value != null) {
					$sqlDataFields .= $wrapper_begin . $key . $wrapper_end . ',';
					$sqlDataValues .= $conn->qstr($value) . ',';
				}
			}
			$sqlDataFields = substr($sqlDataFields,
				0,
				strlen($sqlDataFields) - 1);
			$sqlDataValues = substr($sqlDataValues,
				0,
				strlen($sqlDataValues) - 1);

		// insert text data into database 

			$sql = 'INSERT INTO ' . $TARGET_CONTEXT->data_table . ' (' . $sqlDataFields . ')' . ' VALUES(' . $sqlDataValues . ')';
//print('DATA SQL ' . $sql . '<br>');
			$recordSet = $conn->Execute($sql);
			if ($recordSet === false) {
				$BATCH_RESULT->addError('data insert ERROR ' . $sql);
			}

		// insert image data into database 

			$userImageSize = 0;
			if ($TARGET_CONTEXT->include_images) {
				if ($TARGET_CONTEXT->image_reference_only) {
				        if ($SOURCE_CONTEXT->media_location) {
						$images = $LISTING_OBJECT->getImages();
						if ($images != null) {
							foreach ($images as $key => $aURL) {

								// note in the database

								$sql = 'INSERT INTO ' . $TARGET_CONTEXT->image_table . ' (';
								foreach ($sqlImageFields as $key2 => $val) {
									$sql .= $val . ',';
								}
								$sql = substr($sql, 0, strlen($sql) - 1) . ')' . " VALUES('";
								foreach ($sqlImageValues as $key2 => $val) {
									$value = '';
									switch ($val) {
										case 'ID':
											$value = $unique_value;
											break;
	
										case 'INDEX':
											$value = $key + 1;
											break;

										case 'URL':
											$value = $aURL;
											break;

										case 'PATH':
											$value = 'REFERENCE_ONLY';
											break;
									}
									$sql .= $value . "','";
								}
								$sql = substr($sql, 0, strlen($sql) - 3) . "')";
								$recordSet = $conn->Execute($sql);
								if ($recordSet === false) {
									$BATCH_RESULT->addError('image insert ERROR ' . $sql);
								}
							}
						}
					} else {
						$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
						$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
						for ($i = 1; $i <= $mediaEnd; $i++) {
							if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
										$i,
										$this->EXCHANGE,
										$unique_value)) {
								if ($this->EXCHANGE->getTransportTrace()) {
									$this->EXCHANGE->trace('No images for listing ' . $unique_value . ' found in position ' . $i . ', stopping search');
								}
								break;
							}

							// note in the database

							$sql = 'INSERT INTO ' . $TARGET_CONTEXT->image_table . ' (';
							foreach ($sqlImageFields as $key2 => $val) {
								$sql .= $val . ',';
							}
							$sql = substr($sql, 0, strlen($sql) - 1) . ')' . " VALUES('";
							foreach ($sqlImageValues as $key2 => $val) {
								$value = '';
								switch ($val) {
									case 'ID':
										$value = $unique_value;
										break;

									case 'INDEX':
										$value = $i;
										break;

									case 'URL':
										$value = 'DIRECT';
										break;

									case 'PATH':
										$value = 'REFERENCE_ONLY';
										break;
								}
								$sql .= $value . "','";
							}
							$sql = substr($sql, 0, strlen($sql) - 3) . "')";
							$recordSet = $conn->Execute($sql);
							if ($recordSet === false) {
								$BATCH_RESULT->addError('image insert ERROR ' . $sql);
							}
						}
					}
				} else {
				        if ($SOURCE_CONTEXT->media_location) {
						$images = $LISTING_OBJECT->getImages();
						if ($images != null) {
                					$userAgent = buildUserAgent($SOURCE_CONTEXT->application, 
                                            					$SOURCE_CONTEXT->version);
							foreach ($images as $key => $aURL) {
								$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
									$unique_value,
									$key + 1);
								if( !$this->downloadImage($save_path,
									$aURL,
									$userAgent,
									$EXTRACT_CONTEXT->trace,
									$BATCH_RESULT)) {
									break;
								}

								// note in the database
								$sql = 'INSERT INTO ' . $TARGET_CONTEXT->image_table . ' (';
								foreach ($sqlImageFields as $key2 => $val) {
									$sql .= $val . ',';
								}
								$sql = substr($sql, 0, strlen($sql) - 1) . ')' . " VALUES('";
								foreach ($sqlImageValues as $key2 => $val) {
									$value = '';
									switch ($val) {
										case 'ID':
											$value = $unique_value;
											break;

										case 'INDEX':
											$value = $key + 1;
											break;

										case 'URL':
											$value = $aURL;
											break;

										case 'PATH':
											$value = realpath($save_path);
											break;
									}
									$sql .= $value . "','";
								}
								$sql = substr($sql, 0, strlen($sql) - 3) . "')";
//print("IMAGE SQL " . $sql . "<br>");
								$recordSet = $conn->Execute($sql);
								if ($recordSet === false) {
									$BATCH_RESULT->addError('image insert ERROR ' . $sql);
								}
							}
							$userImageSize = sizeof($images);
						}
					} else {
						$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
									$EXTRACT_CONTEXT,
									$this->EXCHANGE,
									$unique_value);
						$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
						for ($i = $DIRECT_CONTROL->getMediaStart(); $i <= $mediaEnd; $i++) {
							if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
										$i,
										$this->EXCHANGE,
										$unique_value)) {
								if ($this->EXCHANGE->getTransportTrace()) {
									$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
								}
								break;
							}
							$save_path = $this->createImagePath($TARGET_CONTEXT->image_download_path,
								$unique_value,
								$i + 1);
							if ($DIRECT_CONTROL->storeImage($save_path,
									$EXTRACT_CONTEXT->trace,
									$BATCH_RESULT)) {

								// note in the database

								$sql = 'INSERT INTO ' . $TARGET_CONTEXT->image_table . ' (';
								foreach ($sqlImageFields as $key2 => $val) {
									$sql .= $val . ',';
								}
								$sql = substr($sql, 0, strlen($sql) - 1) . ')' . " VALUES('";
								foreach ($sqlImageValues as $key2 => $val) {
									$value = '';
									switch ($val) {
										case 'ID':
											$value = $unique_value;
											break;

										case 'INDEX':
											$value = $i + 1;
											break;

										case 'URL':
											$value = 'DIRECT';
											break;

										case 'PATH':
											$value = realpath($save_path);
											break;
									}
									$sql .= $value . "','";
								}
								$sql = substr($sql, 0, strlen($sql) - 3) . "')";
								$recordSet = $conn->Execute($sql);
								if ($recordSet === false) {
									$BATCH_RESULT->addError('image insert ERROR ' . $sql);
								}
								++$userImageSize;
							}
						}
					}
				}
			}

			// user notification

			if ($userImageSize == 1) {
				$buffer .= ', 1 image downloaded.';
			} else {
				$buffer .= ', ' . $userImageSize . ' images downloaded.';
			}

			if ($duplicateRemoved) {
				$BATCH_RESULT->addDuplicatedItem();
				$BATCH_RESULT->addRefreshedItem();
			} else {
				$BATCH_RESULT->addAdditionalItem();
			}
		} else {
			$BATCH_RESULT->addSkippedItem();
			$buffer .= ' not downloaded, already exists in the database';
		}

		return $buffer;
	}

	function write_or($LISTING_OBJECT,
		$conn,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT,
		&$BATCH_RESULT,
		$classID,
		$unique_target_key,
		$unique_value,
		$expiration_date,
		$notes,
		$timestamp,
		$thumb_prog) {

		$buffer = null;

		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	// +--------------------------+---------------+------+-----+------------+----------------+
	// | Field                    | Type          | Null | Key | Default    | Extra          |
	// +--------------------------+---------------+------+-----+------------+----------------+
	// | listingsdb_id            | int(11)       |      | PRI | NULL       | auto_increment |
	// | userdb_id                | int(11)       |      | MUL | 0          |                |
	// | listingsdb_title         | varchar(80)   |      |     |            |                |
	// | listingsdb_expiration    | date          |      |     | 0000-00-00 |                |
	// | listingsdb_notes         | text          |      |     |            |                |
	// | listingsdb_creation_date | date          |      |     | 0000-00-00 |                |
	// | listingsdb_last_modified | timestamp(14) | YES  |     | NULL       |                |
	// | listingsdb_hit_count     | int(11)       |      |     | 0          |                |
	// | listingsdb_featured      | char(3)       |      |     |            |                |
	// | listingsdb_active        | char(3)       |      | MUL |            |                |
	// | listingsdb_mlsexport     | char(3)       |      | MUL |            |                |
	// +--------------------------+---------------+------+-----+------------+----------------+
	// +--------------------------------+-------------+------+-----+---------+----------------+
	// | Field                          | Type        | Null | Key | Default | Extra          |
	// +--------------------------------+-------------+------+-----+---------+----------------+
	// | listingsdbelements_id          | int(11)     |      | PRI | NULL    | auto_increment |
	// | listingsdbelements_field_name  | varchar(20) |      | MUL |         |                |
	// | listingsdbelements_field_value | text        |      | MUL |         |                |
	// | listingsdb_id                  | int(11)     |      | MUL | 0       |                |
	// | userdb_id                      | int(11)     |      |     | 0       |                |
	// +--------------------------------+-------------+------+-----+---------+----------------+
	// +--------------------------------+--------------+------+-----+---------+----------------+
	// | Field                          | Type         | Null | Key | Default | Extra          |
	// +--------------------------------+--------------+------+-----+---------+----------------+
	// | listingsimages_id              | int(11)      |      | PRI | NULL    | auto_increment |
	// | userdb_id                      | int(11)      |      |     | 0       |                |
	// | listingsimages_caption         | varchar(255) |      |     |         |                |
	// | listingsimages_file_name       | varchar(80)  |      |     |         |                |
	// | listingsimages_thumb_file_name | varchar(80)  |      |     |         |                |
	// | listingsimages_description     | text         |      |     |         |                |
	// | listingsdb_id                  | int(11)      |      | MUL | 0       |                |
	// | listingsimages_rank            | int(11)      |      | MUL | 0       |                |
	// | listingsimages_active          | char(3)      |      |     |         |                |
	// +--------------------------------+--------------+------+-----+---------+----------------+
	// +--------------------+---------+------+-----+---------+----------------+
	// | Field              | Type    | Null | Key | Default | Extra          |
	// +--------------------+---------+------+-----+---------+----------------+
	// | classlistingsdb_id | int(11) |      | PRI | NULL    | auto_increment |
	// | class_id           | int(11) |      | MUL | 0       |                |
	// | listingsdb_id      | int(11) |      | MUL | 0       |                |
	// +--------------------+---------+------+-----+---------+----------------+

		// check if listing exists

		$check = 'listing_id';
		$sql = 'SELECT listingsdb_id FROM ' . $TARGET_CONTEXT->data_table . " WHERE listingsdbelements_field_name = '" . $unique_target_key . "'" . " AND listingsdbelements_field_value = '" . $unique_value . "'";
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			$BATCH_RESULT->addError('get random ERROR ' . $sql);
		}
		$userSuppliedData = array();
		$userSuppliedImages = 0;
		$clearToInsert = true;
		$data = $LISTING_OBJECT->getData();

		// write to OR

		$duplicateRemoved = false;
		$dup_record = null;
		while (!$recordSet->EOF) {
			$dup_record = $recordSet->fields['listingsdb_id'];
			$dupWhere = " WHERE listingsdb_id = '" . $dup_record . "'";
			$BATCH_RESULT->addDuplicatedItem();
			if ($EXTRACT_CONTEXT->refresh) {
				if (!$EXTRACT_CONTEXT->mls_only) {

					// retain non-MLS data

					$sql = 'SELECT * FROM ' . $TARGET_CONTEXT->data_table . $dupWhere;
					$recordSet2 = $conn->Execute($sql);
					if ($recordSet2 === false) {
//						$BATCH_RESULT->addError("user data bypass ERROR " . $sql);
					} else {
						if ($recordSet2 != null) {
							while (!$recordSet2->EOF) {
								$temp = $recordSet2->fields['listingsdbelements_field_name'];
								if (!array_key_exists($temp, $data)) {
//print('<br/>Saving value for field [' . $temp . '] from record ' . $dup_record . '<br/>');
									$userSuppliedData[$temp] = $recordSet2->fields['listingsdbelements_field_value'];
								}
								$recordSet2->MoveNext();
							}
						}
					}
				}

				// delete all data for this listing

				$sql = 'DELETE FROM ' . $TARGET_CONTEXT->data_table . $dupWhere;
				$recordSet2 = $conn->Execute($sql);
				if ($recordSet2 === false) {
					$BATCH_RESULT->addError('delete data ERROR ' . $sql);
				}

				if (!$EXTRACT_CONTEXT->mls_only) {

					// delete only MLS images for this listing

					$imagesPrior = 0;
					$sql = 'select * FROM ' . $TARGET_CONTEXT->image_table . $dupWhere;
					$recordSet2 = $conn->Execute($sql);
					if ($recordSet2 === false) {
						$BATCH_RESULT->addError('select count from image ERROR ' . $sql);
					}
					if ($recordSet2 != null) {
						$imagesPrior = $recordSet2->NumRows();
					}
					$imagesDeleted = 0;
					for ($i = 0; $i < ($EXTRACT_CONTEXT->max_images + 1); ++$i) {
						$image_name = $dup_record . '_' . $i . '.jpg';
						$image_path = $TARGET_CONTEXT->image_upload_path . '/' . $image_name;
						if (file_exists($image_path)) {
							unlink($image_path);
							$thumb_path = $TARGET_CONTEXT->image_upload_path . '/' .
							EXP_THUMB_PREFIX . $image_name;
							if (file_exists($thumb_path)) {
								unlink($thumb_path);
							}
							$imagesDeleted++;
							$sql = 'DELETE FROM ' . $TARGET_CONTEXT->image_table . $dupWhere . " AND listingsimages_file_name = '" . $image_name . "'";
							$recordSet2 = $conn->Execute($sql);
							if ($recordSet2 === false) {
								$BATCH_RESULT->addError('delete from image ERROR ' . $sql);
							}
						}
					}
					$userSuppliedImages = $imagesPrior - $imagesDeleted;
				} else {

					// delete all images for this listing

					$sql = 'DELETE FROM ' . $TARGET_CONTEXT->image_table . $dupWhere;
					$recordSet2 = $conn->Execute($sql);
					if ($recordSet2 === false) {
						$BATCH_RESULT->addError('delete from image ERROR ' . $sql);
					}
					for ($i = 0; $i < ($EXTRACT_CONTEXT->max_images + 1); ++$i) {
						$image_name = $dup_record . '_' . $i . '.jpg';
						$image_path = $TARGET_CONTEXT->image_upload_path . '/' . $image_name;
						if (file_exists($image_path)) {
							unlink($image_path);
							$thumb_path = $TARGET_CONTEXT->image_upload_path . '/' .
							EXP_THUMB_PREFIX . $image_name;
							if (file_exists($thumb_path)) {
								unlink($thumb_path);
							}
						}
					}
				}
				$duplicateRemoved = true;
			} else {
				$clearToInsert = false;
			}
			$recordSet->MoveNext();
		}

		if ($clearToInsert) {

			// determine the correct ownership id

			$user_element_table = $TARGET_CONTEXT->user_element_table;
			if (!empty($user_element_table)) {
				$user_field = $TARGET_CONTEXT->user_field;
				if (empty($user_field)) {
					$user_field = 'userdbelements_field_name';
				}
				$user_value = $TARGET_CONTEXT->user_value;
				if (empty($user_value)) {
					$user_value = 'userdbelements_field_value';
				}
				$rets_user_element = $TARGET_CONTEXT->rets_user_element;
				if (empty($rets_user_element)) {
					$rets_user_element = 'RETS_AGENT_ID';
				}
				// echo "<XMP>UET $user_element_table UF $user_field UV $user_value RUV $rets_user_element</XMP>";
				$owner_value = $LISTING_OBJECT->getOwner();
				$owner_id = $EXTRACT_CONTEXT->user_ID;
				$sql = 'SELECT userdb_id FROM ' . $user_element_table . ' WHERE ' . $user_field . " = '" . $rets_user_element . "' AND " . $user_value . " = '" . $conn->qstr($owner_value) . "'";
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
//					$BATCH_RESULT->addError('RETS Account ERROR ' . $sql);
				} else {
					while (!$recordSet->EOF) {
						$owner_id = $recordSet->fields['userdb_id'];
						$recordSet->MoveNext();
					}
				}
			} else {
				$owner_id = $EXTRACT_CONTEXT->user_ID;
			}

			// prepare the description

			$listing_description_template = $TARGET_CONTEXT->listing_description_template;
			if ($listing_description_template == null) {
				$listing_description_template = 'Listing {mls}';
			}
			$title = null;
			$loop = true;
			$source = $listing_description_template;
			while ($loop) {
				$pos = strpos($source, '{');
				if ($pos === false) {
					$title .= $source;
					$loop = false;
				} else {
					$piece = substr($source, 0, $pos);
					$title .= $piece;
					$source = substr($source, $pos, strlen($source));
					$pos = strpos($source, '}');
					$candidate = substr($source, 1, $pos - 1);
					$found = false;
					foreach ($data as $key => $value) {
						if ($value != null) {
							if ($key == $candidate) {
								$title .= $value;
								$found = true;
								break;
							}
						}
					}
					if (!$found) {
						foreach ($userSuppliedData as $key => $value) {
							if ($value != null) {
								if ($key == $candidate) {
									$title .= $value;
									break;
								}
							}
						}
					}
					$source = substr($source, $pos + 1, strlen($source));
				}
			}
			if ($title == null) {
				$title = 'Listing ' . $unique_value;
			}

			// extract configuration items

			if ($duplicateRemoved) {
				$new_listing_id = $dup_record;
				$sql = 'UPDATE ' . $TARGET_CONTEXT->index_table . ' SET listingsdb_title=' . $conn->qstr($title) . ", userdb_id = '" . $owner_id . "', listingsdb_last_modified=" . $timestamp . ' ' . $dupWhere;
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
					$BATCH_RESULT->addError('update ERROR ' . $sql);
				}
			} else {

				// create an index record for each listing

				$sql = 'INSERT INTO ' . $TARGET_CONTEXT->index_table . ' (userdb_id,listingsdb_title,listingsdb_expiration,listingsdb_notes,listingsdb_creation_date,listingsdb_featured,listingsdb_active,listingsdb_mlsexport)' . ' VALUES(' . $owner_id . ',' . $conn->qstr($title) . ',' . $expiration_date . ',' . "'" . $notes . "'," . $timestamp . ',' . "'no'," . "'yes'," . "'no'" . ')';
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
					$BATCH_RESULT->addError('index ERROR ' . $sql);
				} else {
					$new_listing_id = $conn->Insert_ID();
				}
			}

			// add data fields

			if ($new_listing_id != null) {
				if ($duplicateRemoved) {
					$BATCH_RESULT->addRefreshedItem();
					$buffer .= ' refreshed to Open-Realty as ID: ' . $new_listing_id;
				} else {
					$BATCH_RESULT->addAdditionalItem();
					$buffer .= ' downloaded to Open-Realty as ID: ' . $new_listing_id;
				}

				// report on retained items

				$userDataSize = sizeof($userSuppliedData);
				if ($userDataSize > 0 && $userSuppliedImages > 0) {
					$buffer .= ' non-MLS data in (' . $userDataSize . ') fields and (' . $userSuppliedImages . ') images retained, ';
				} else {
					if ($userDataSize > 0) {
						$buffer .= ' non-MLS data in (' . $userDataSize . ') fields retained, ';
					}
					if ($userSuppliedImages > 0) {
						$buffer .= ' (' . $userSuppliedImages . ') non-MLS images retained, ';
					}
				}

				// create a record for each MLS item
				$sql_string=array();
				foreach ($data as $key => $value) {
					if ($value != null) {
//$aType = $LISTING_OBJECT->getType($key);
//echo "<XMP>TYPE K $key T $aType</XMP>";
//$aType = $LISTING_OBJECT->getInterpretation($key);
//echo "<XMP>INTERPRETATION K $key T $aType</XMP>";
						if ($LISTING_OBJECT->getInterpretation($key) == 'LookupMulti') {
							$value = str_replace(',', '||', $value);
						}
						$db_value = $conn->qstr($value);
						$sql_string[] = '(' . "'" . $key . "'," . $db_value . ',' . $new_listing_id . ',' . $owner_id . ')';
					}
				}
				if(count($sql_string) > 0){
					$sql = 'INSERT INTO ' . $TARGET_CONTEXT->data_table . ' (listingsdbelements_field_name,listingsdbelements_field_value,listingsdb_id,userdb_id)' . ' VALUES '.implode(',',$sql_string);
					$recordSet = $conn->Execute($sql);
					if ($recordSet === false) {
						$BATCH_RESULT->addError('insert data ERROR ' . $sql);
					}
				}

				// create a record for each user supplied item
				$sql_string=array();
				foreach ($userSuppliedData as $key => $value) {
					if ($value != null) {
//print('Restoring value for field [' . $key . '] from record ' . $new_listing_id . '<br/>');
						$db_value = $conn->qstr($value);
						$sql_string[] = '(' . "'" . $key . "'," . $db_value . ',' . $new_listing_id . ',' . $owner_id . ')';
					}
				}
				if(count($sql_string) > 0){
					$sql = 'INSERT INTO ' . $TARGET_CONTEXT->data_table . ' (listingsdbelements_field_name,listingsdbelements_field_value,listingsdb_id,userdb_id)' . ' VALUES '.implode(',',$sql_string);
					$recordSet = $conn->Execute($sql);
					if ($recordSet === false) {
						$BATCH_RESULT->addError('insert data ERROR ' . $sql);
					}
				}
				// create entry in listing class table for OR 2.1

				if ($classID != -1 && !$duplicateRemoved ) {
					$sql = 'INSERT INTO ' . $TARGET_CONTEXT->class_listing_table . ' (class_id,listingsdb_id)' . ' VALUES(' . $classID . ',' . $new_listing_id . ')';
					$recordSet = $conn->Execute($sql);
					if ($recordSet === false) {
						$BATCH_RESULT->addError('class index ERROR ' . $sql);
					}
				}

				// process images

	// +--------------------------------+--------------+------+-----+---------+----------------+
	// | Field                          | Type         | Null | Key | Default | Extra          |
	// +--------------------------------+--------------+------+-----+---------+----------------+
	// | listingsimages_id              | int(11)      |      | PRI | NULL    | auto_increment |
	// | userdb_id                      | int(11)      |      |     | 0       |                |
	// | listingsimages_caption         | varchar(255) |      |     |         |                |
	// | listingsimages_file_name       | varchar(80)  |      |     |         |                |
	// | listingsimages_thumb_file_name | varchar(80)  |      |     |         |                |
	// | listingsimages_description     | text         |      |     |         |                |
	// | listingsdb_id                  | int(11)      |      | MUL | 0       |                |
	// | listingsimages_rank            | int(11)      |      | MUL | 0       |                |
	// | listingsimages_active          | char(3)      |      |     |         |                |
	// +--------------------------------+--------------+------+-----+---------+----------------+
				$userImageCount = 0;
				$sql = 'SELECT MAX(listingsimages_rank) AS max_rank FROM ' . $TARGET_CONTEXT->image_table . " WHERE (listingsdb_id = '" . $new_listing_id . "')";
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
					$BATCH_RESULT->addError('image rank ERROR ' . $sql);
				}
				$sql_string=array();
				$rank = $recordSet->fields['max_rank'];
				if ($SOURCE_CONTEXT->media_location) {
					$images = $LISTING_OBJECT->getImages();

					// look for images until there is a 'gap'

					if ($images != null)
					{
                				$userAgent = buildUserAgent($SOURCE_CONTEXT->application, 
                                            				$SOURCE_CONTEXT->version);
						foreach ($images as $key => $aURL) {
							$save_name = $new_listing_id . '_' .
									($key + 1) .  '.jpg';
							$save_path = $TARGET_CONTEXT->image_upload_path . '/' . $save_name;
							if( !$this->downloadImage($save_path,
								$aURL,
								$userAgent,
								$EXTRACT_CONTEXT->trace,
								$BATCH_RESULT)) {
								break;
							}

							// note in Open-Realty

							$thumb_name = $save_name;
							if ($TARGET_CONTEXT->make_thumbnail) {
								$thumb_name = $thumb_prog($save_name,
									$TARGET_CONTEXT->image_upload_path,
									$TARGET_CONTEXT->thumbnail_width,
									$TARGET_CONTEXT->thumbnail_quality,
									$TARGET_CONTEXT->path_to_imagemagick,
									$TARGET_CONTEXT->gd_version2);
								$BATCH_RESULT->addThumb();
							}
							$rank++;
							$thumb_name = EXP_THUMB_PREFIX . $save_name;
							$sql_string[] = '(' . "'" . $new_listing_id . "'," . "'" . $owner_id . "'," . "'" . $save_name . "'," . "'" . $thumb_name . "'" . ',' . $rank . ')';
							++$userImageCount;
						}
					}
				} else {
                                        if( $EXTRACT_CONTEXT->max_images == 0)
                                        {
                                             $EXTRACT_CONTEXT->max_images = 10;
                                        }
					$DIRECT_CONTROL = new DirectControl($SOURCE_CONTEXT,
								$EXTRACT_CONTEXT,
								$this->EXCHANGE,
								$unique_value);
					$mediaEnd = $DIRECT_CONTROL->getMediaEnd();
					$userImageCount = 0;
					for ($i = $DIRECT_CONTROL->getMediaStart(); $i <= $mediaEnd; $i++) {
						if (!$DIRECT_CONTROL->getImage($SOURCE_CONTEXT,
									$i,
									$this->EXCHANGE,
									$unique_value)) {
							if ($this->EXCHANGE->getTransportTrace()) {
								$this->EXCHANGE->trace('No images found in position ' . $i . ', stopping search');
							}
							break;
						}
						$save_name = $new_listing_id . '_' .
								($i + 1) . '.jpg';
						$save_path = $TARGET_CONTEXT->image_upload_path . '/' . $save_name;
						if($DIRECT_CONTROL->storeImage($save_path,
							$EXTRACT_CONTEXT->trace,
							$BATCH_RESULT) ) {

							// note in Open-Realty

							$thumb_name = $save_name;
							if ($TARGET_CONTEXT->make_thumbnail) {
								$thumb_name = $thumb_prog($save_name,
									$TARGET_CONTEXT->image_upload_path,
									$TARGET_CONTEXT->thumbnail_width,
									$TARGET_CONTEXT->thumbnail_quality,
									$TARGET_CONTEXT->path_to_imagemagick,
									$TARGET_CONTEXT->gd_version2);
								$BATCH_RESULT->addThumb();
							}
							$rank++;
							$thumb_name = EXP_THUMB_PREFIX . $save_name;
							$sql_string[] = '(' . "'" . $new_listing_id . "'," . "'" . $owner_id . "'," . "'" . $save_name . "'," . "'" . $thumb_name . "'" . ',' . $rank . ')';
							++$userImageCount;
						}
					}
				}
				if(count($sql_string) > 0){
					$sql = 'INSERT INTO ' . $TARGET_CONTEXT->image_table . '(listingsdb_id,userdb_id,listingsimages_file_name,listingsimages_thumb_file_name,listingsimages_rank) ' . 'VALUES '.implode(',',$sql_string);
					$recordSet = $conn->Execute($sql);
					if ($recordSet === false) {
						$BATCH_RESULT->addError('insert data ERROR ' . $sql);
					}
				}
				if ($userImageCount > 0) {
					if ($userImageCount == 1) {
						$units = 'image';
					} else {
						$units = 'images';
					}
					$buffer .= ' adding ' . $userImageCount . ' MLS ' . $units;
				}
			}
		} else {
			$BATCH_RESULT->addSkippedItem();
			$buffer .= ' not downloaded, already exists in Open-Realty';
		}

		return $buffer;
	}

	function createControlFile($S_CONFIGURATION,
		$query_values,
		$extract,
		$last_run,
		$updateBCF,
		$runSync,
                $withQuietMode = true,
                $advancedQuery = null)
	{

		// construct SOURCE context

		$SOURCE_CONTEXT = new SourceContext();
		$SOURCE_CONTEXT->readConfiguration($S_CONFIGURATION);

		// remove query items that are not defined

		$query_array = null;
		$args = explode(',', $SOURCE_CONTEXT->query_items);
		foreach ($args as $key => $val) {
			if ($query_values != null) {
				if (array_key_exists($val, $query_values)) {
					$query_array[$val] = $query_values[$val];
				}
			}
		}

		$EXTRACT = new Extract();
		if ($last_run == null) {
			$withLastRun = false;
		} else {
			$withLastRun = false;
			$withLastRun = true;
		}
		$EXTRACT->createControlFile($extract,
			$this->limit,
			$extract,
			$query_array,
			true,
                        $this->startTime,
			$withLastRun,
			$updateBCF,
			$runSync,
                        null,
                        $withQuietMode,
                        $advancedQuery);
	}

	function parseFilter($aFilter) {
		$running = true;
		$result = null;
		$buffer = $aFilter;
		while ($running)
		{
			$pos_s = strpos($buffer, '{');
			if ($pos_s === false) {
				$running = false;
			} else {
				$pos_e = strpos($buffer, '}');
				$junk = substr($buffer, $pos_s + 1, $pos_e - $pos_s - 1);
				$result[] = $junk;
				$buffer = substr($buffer, $pos_e + 1, strlen($buffer));
			}
		}
		return $result;
	}

	function constructSelections($SOURCE_CONTEXT,
		$EXTRACT_CONTEXT,
		$TARGET_CONTEXT)
	{
		$ownership_field = $SOURCE_CONTEXT->ownership;
		$selections = $EXTRACT_CONTEXT->extract_column_list;
		if (array_key_exists('SOURCE', $EXTRACT_CONTEXT->data_maps)) {
			$sourceMap = $EXTRACT_CONTEXT->data_maps['SOURCE'];
			$selections = null;
			$taken = Array();
			foreach ($sourceMap as $key => $value) {
				if ($value != NO_VALUE_INDICATOR && 
				    $value != META_COLUMN_INDICATOR) {
					if (!array_key_exists($value, $taken)) {
						$selections .= $value . ',';
						$taken[$value] = true;
					}
				}
			}
			if ($EXTRACT_CONTEXT->metacolumn_map != null) {
				foreach ($EXTRACT_CONTEXT->metacolumn_map as $key => $val) {
					$aList = $this->parseFilter($val);
					foreach ($aList as $key2 => $val2) {
						$selections .= $val2 . ',';
					}
				}
			}
			$pos = strrpos($selections, ',');
			$selections = substr($selections, 0, $pos);
		} else {
			$selections = $EXTRACT_CONTEXT->extract_column_list;
		}

		// if no items have been selected, use summary items from the SOURCE

		if ($selections == null) {
			$selections = $SOURCE_CONTEXT->summary_items;
		}

		// if this is OR, add the ownership field

		if ($TARGET_CONTEXT->target_type == 'OR') {
			return $selections . ',' . $ownership_field;
		}

		return $selections;
	}

	function constructDataRequest($SOURCE_CONTEXT,
		$queryValues,
		$selections)
	{

		// create data request

		$SEARCH_REQUEST = new SearchRequest($SOURCE_CONTEXT->detected_maximum_rets_version,
			$SOURCE_CONTEXT->resource,
			$SOURCE_CONTEXT->class_name,
			$selections,
			$SOURCE_CONTEXT->name,
                        $SOURCE_CONTEXT->restricted_indicator);

		// construct query

		$usingNullQuery = false;
		$query = '';
		if ($queryValues != null) {
			foreach ($queryValues as $key => $val) {
				if (is_array($val)) {

					// construct a multi-value query for a single field

                                        $dataType = $SOURCE_CONTEXT->field_types[$key];
//echo '<XMP>DT $dataType</XMP>';
					if ($dataType == 'Int' || $dataType == 'Long') {
						foreach ($val as $key2 => $val2) {
							$query .= '(' . $key . '=' . $val2 . ')|';
						}
						$query = trim($query, '|');
						$query .= ',';
					}
					else {
						$query = '(' . $key . '=';
						foreach ($val as $key2 => $val2) {
							$query .= $val2 . ',';
						}
						$query = trim($query, ',');
						$query .= '),';
					}

				} else {
					if (strlen($val) > 0) {
						if ($val != 'ANY') {
							$query .= '(' . $key . '=' . $val . '),';
						} else {
							$query .= $this->EXCHANGE->createAllQuery($SOURCE_CONTEXT->resource,
									$SOURCE_CONTEXT->class_name,
									$SOURCE_CONTEXT->detected_standard_names,
									$SOURCE_CONTEXT->detected_maximum_rets_version,
									$key) . ',';
							$usingNullQuery = true;
						}
					}
				}
			}
		}

		// check to see if we still need to create a null query

		if (strlen($query) == 0) {
			$query = $this->EXCHANGE->createNullQuery($SOURCE_CONTEXT->resource,
				$SOURCE_CONTEXT->class_name,
				$SOURCE_CONTEXT->detected_standard_names,
				$SOURCE_CONTEXT->detected_maximum_rets_version,
				$SOURCE_CONTEXT->unique_key,
                                $SOURCE_CONTEXT->null_query_option);
			$usingNullQuery = true;
		} else {
			$query = trim($query, ',');
			$query = trim($query);
		}

		// assign query to the data request

		$SEARCH_REQUEST->setQueryCriteria($query, $usingNullQuery);

		// data types

		$selectValues = explode(',',$selections);
                $selectTypes = null;
		foreach ($selectValues as $key => $val) {
                        $selectTypes[$val] = $SOURCE_CONTEXT->field_types[$val];
		}
		$SEARCH_REQUEST->setSelectionTypes($selectTypes);

		// interpretations

                $selectInterpretations = null;
		foreach ($selectValues as $key => $val) {
                        $selectInterpretations[$val] = $SOURCE_CONTEXT->field_interpretations[$val];
		}
		$SEARCH_REQUEST->setSelectionInterpretations($selectInterpretations);

		return $SEARCH_REQUEST;
	}

	function autoCreate_table($conn,
		$brand,
		$tables,
		$aTable,
		$columnList) {

		// if table exists, drop it

		foreach ($tables as $key => $value) {
			if ($value == $aTable) {
				$sql = 'DROP TABLE ' . $aTable;
				$recordSet = $conn->Execute($sql);
				if ($recordSet === false) {
					// $buffer .= 'drop table ERROR $sql<br/>';
					echo '<XMP>SQL $sql</XMP>';
				}
			}
		}

		// wrapper depending on database type 

		switch ($brand) {
			case 'mysql':
				$wrapper_begin = '`';
				$wrapper_end = '`';
				break;

			case 'mssql':
				$wrapper_begin = '[';
				$wrapper_end = ']';
				break;

			default:
				$wrapper_begin = null;
				$wrapper_end = null;
		}

		// create a table with the columns passed to the function

		$sql = 'CREATE TABLE ' . $aTable . '(';
		$aMap = explode(',', $columnList);
		foreach ($aMap as $key => $value) {
			$sql .= $wrapper_begin . $value . $wrapper_end . ' varchar(100),';
		}
		$sql = substr($sql, 0, strlen($sql) - 1) . ')';
		$recordSet = $conn->Execute($sql);
		if ($recordSet === false) {
			echo '<XMP>SQL ' . $sql . '</XMP>';
//			$buffer .= 'table create ERROR $sql<br/>';
		}
	}

	function createImagePath($imageDownloadPath,
		$listingID,
		$index)
	{
		return $imageDownloadPath . '/' . $listingID . '_' . $index . '.jpg';
	}

	function createConnection($TARGET_CONTEXT)
	{
		$conn = ADONewConnection($TARGET_CONTEXT->brand);
		$conn->PConnect($TARGET_CONTEXT->server,
			$TARGET_CONTEXT->db_account,
			$TARGET_CONTEXT->db_password,
			$TARGET_CONTEXT->database);
		return $conn;
	}

	function downloadImage($path,
		$url,
		$userAgent,
		$trace, 
		&$BATCH_RESULT)
	{
		if (file_exists($path)) {
			unlink($path);
		}

		if ($url == null) {
echo '<XMP>BAD URL writing ' . $path . '</XMP>';
			$BATCH_RESULT->addMissing();
			if ($trace) {
				$BATCH_RESULT->addError('BAD URL writing ' . $path);
			}
                        return false;
		}

		$REQUEST = new NetRequest($userAgent, true);
		if ($this->EXCHANGE->getTransportTrace()) {
			$REQUEST->setTransportTrace(true);
          		$REQUEST->setTraceDevice($this->EXCHANGE->traceDevice);
		}
		$content = $REQUEST->fetch($url);

		if ($content === false) {
			$BATCH_RESULT->addMissing();
			if ($trace) {
				$BATCH_RESULT->addError('bad url ERROR ' . $url);
			}
		} else {
			if ($content != null) {

				// write to disk

				$handle = fopen($path, 'wb');
				fwrite($handle, $content);
				fclose($handle);
				$BATCH_RESULT->addRaw();
				return true;
			}
		}
		return false;
	}

	function bindImages(&$LISTING_OBJECT,
		$SOURCE_CONTEXT,
		$TARGET_CONTEXT,
		$EXTRACT_CONTEXT) {
		$containers = null;
		$MEDIA_REQUEST = new MediaRequest($SOURCE_CONTEXT->resource,
			$LISTING_OBJECT->getUniqueValue(),
			$SOURCE_CONTEXT->media_type,
			$SOURCE_CONTEXT->media_multipart,
			$SOURCE_CONTEXT->media_location,
			$SOURCE_CONTEXT->name);
		if ($SOURCE_CONTEXT->simultaneous_logins) {
			$this->EXCHANGE->trace('Downloading images with an EMBEDDED transaction');
			$EMBEDDED = new Exchange($SOURCE_CONTEXT->name);
			$result = $EMBEDDED->loginDirect($SOURCE_CONTEXT->account,
				$SOURCE_CONTEXT->password,
				$SOURCE_CONTEXT->url,
				$SOURCE_CONTEXT->detected_maximum_rets_version,
				$SOURCE_CONTEXT->application,
				$SOURCE_CONTEXT->version,
				$SOURCE_CONTEXT->clientPassword,
				$SOURCE_CONTEXT->postRequests);
			if ($result) {
				$containers = $EMBEDDED->searchMediaDirect($MEDIA_REQUEST,
					$EXTRACT_CONTEXT->max_images);

				// do not logout!  this is an embedded transaction!

				$EMBEDDED->finish();
				$this->EXCHANGE->addSubtransaction($EMBEDDED);
			}
		} else {
			$containers = $this->EXCHANGE->searchMediaDirect($MEDIA_REQUEST,
				$EXTRACT_CONTEXT->max_images);
		}

		// if there are images, add to the listing

		if ($containers != null) {
			foreach ($containers as $key => $MEDIA_CONTAINER) {
				if ($TARGET_CONTEXT->image_encoded_url) {
					$LISTING_OBJECT->addImage($MEDIA_CONTAINER->getEncodedURL());
				} else {
					$LISTING_OBJECT->addImage($MEDIA_CONTAINER->getURL());
                                }
			}
                }
	}

	function clearCache($cache_path)
	{
		if (!file_exists($cache_path . '/adodb_*.cache')) {
			return true;
		}
		if (ini_get('safe_mode')) {

			// Delete Cache Files if safemode is on

			$cache_files = glob($cache_path . '/adodb_*.cache');
			if (is_array($cache_files)) {
				foreach ($cache_files as $filename) {
					unlink($filename);
				}
			}
		} else {

			// Delete Cache Files if safemode is off

			$dir = 0;
			$sub_dir = array();
			if ($handle = opendir($cache_path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
						$temp_dir = $cache_path . '/' . $file;
						if (is_dir($temp_dir)) {
							$cache_files = glob($temp_dir . '/adodb_*.cache');
							if (is_array($cache_files)) {
								foreach ($cache_files as $filename) {
									unlink($filename);
								}
							}
							rmdir($temp_dir);
						}
					}
				}
				closedir($handle);
			}
		}
	}
}

// common download functions

function make_thumb_gd($input_file_name,
	$input_file_path,
	$thumbnail_width,
	$quality,
	$path = null,
	$gd_version2 = true) {

	// makes a thumbnail using the GD library

	// initialization

	$imagedata = GetImageSize($input_file_path . '/' . $input_file_name);
	$imagewidth = $imagedata[0];
	$imageheight = $imagedata[1];
	$imagetype = $imagedata[2];
	$thumb_name = $input_file_name;
	$shrinkage = 1;
	if ($imagewidth > $thumbnail_width) {
		$shrinkage = $thumbnail_width / $imagewidth;
	}
	$dest_height = $shrinkage * $imageheight;
	$dest_width = $thumbnail_width;

	// the GD library, which this uses, can only resize GIF, JPG and PNG

	// type definitions
	// 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP
	// 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
	// 9 = JPC, 10 = JP2, 11 = JPX

	if ($imagetype == 1) {
		// it's a GIF
		// see if GIF support is enabled
		if (imagetypes() &IMG_GIF) {
			$src_img = imagecreatefromgif($input_file_path. '/' . $input_file_name);
			$dst_img = imageCreate($dest_width, $dest_height);

			// copy the original image info into the new image with new dimensions
			// checking to see which function is available

			ImageCopyResized($dst_img,
				$src_img,

				0,
				0,
				0,
				0,
				$dest_width,
				$dest_height,
				$imagewidth,
				$imageheight);

			$thumb_name = EXP_THUMB_PREFIX . $input_file_name;
			imagegif($dst_img, $input_file_path . '/' . $thumb_name);
			imagedestroy($src_img);
			imagedestroy($dst_img);
		}
	} else {
		if ($imagetype == 2) {

			// it's a JPG

			$src_img = imagecreatefromjpeg($input_file_path . '/' . $input_file_name);
                        if ($src_img != null) {

			if (!$gd_version2) {
				$dst_img = imageCreate($dest_width, $dest_height);
				ImageCopyResized($dst_img,
					$src_img,
					0,
					0,
					0,
					0,
					$dest_width,
					$dest_height,
					$imagewidth,
					$imageheight);
			} else {
				$dst_img = imageCreateTrueColor($dest_width, $dest_height);
				ImageCopyResampled($dst_img,
					$src_img,
					0,
					0,
					0,
					0,
					$dest_width,
					$dest_height,
					$imagewidth,
					$imageheight);
			}
			$thumb_name = EXP_THUMB_PREFIX . $input_file_name;
			imagejpeg($dst_img, $input_file_path . '/' . $thumb_name, $quality);
			imagedestroy($src_img);
			imagedestroy($dst_img);
			}
		} else {
			if ($imagetype == 3) {
				// it's a PNG
				$src_img = imagecreatefrompng($input_file_path . '/' . $input_file_name);
				$dst_img = imagecreate($dest_width, $dest_height);
				ImageCopyResized($dst_img,
					$src_img,
					0,
					0,
					0,
					0,
					$dest_width,
					$dest_height,
					$imagewidth,
					$imageheight);
				$thumb_name = EXP_THUMB_PREFIX . $input_file_name;
				imagepng($dst_img, $input_file_path . '/' . $thumb_name);
				imagedestroy($src_img);
				imagedestroy($dst_img);
			}
		}
	}

	return $thumb_name;
}

function make_thumb_imagemagick($input_file_name,
	$input_file_path,
	$thumbnail_width,
	$quality = null,
	$path,
	$gd_version2 = true) {

	// makes a thumbnail using ImageMagick

	// initialization

	$current_file = $input_file_path . '/' . $input_file_name;
	$max_width = $thumbnail_width;

	// Get the current info on the file

	$current_size = GetImageSize($current_file);
	$current_img_width = $current_size[0];
	$current_img_height = $current_size[1];
	$image_base = explode('.', $current_file);

	// This part gets the new thumbnail name

	$image_basename = $image_base[0];
	$image_ext = $image_base[1];
	$thumb_name2 = EXP_THUMB_PREFIX . $input_file_name;
	$thumb_name = $input_file_path . '/' . $thumb_name2;

	$too_big_diff_ratio = $current_img_width / $max_width;
	if ($too_big_diff_ratio == 0) {
		$new_img_width = $current_img_width;
		$new_img_height = $current_img_heigh;
	} else {
		$new_img_width = $max_width;
		$new_img_height = round($current_img_height / $too_big_diff_ratio);
	}

	// Convert the file

	$make_magick = $path . ' -geometry ' . $new_img_width . ' x ' . $new_img_height . ' ' . $current_file . ' ' . $thumb_name;
	$result = system($make_magick);

	return $thumb_name2;
}

function buildUserAgent($application = null,
                        $version = null) {
     if ($version == null && $application != null) {
//
// Rappatoni allows a User-Agent that does not have a version component
//
          return $application;
     } else {
          if ($application == null) {
                $application = DEFAULT_APPLICATION;
                $version = DEFAULT_VERSION;
          }
          return $application . '/' .  $version;
     }
}

// ------------

class DirectControl {

	var $mediaStart;
	var $mediaEnd;
	var $images;
	var $content;

	function DirectControl($SOURCE_CONTEXT,
				$EXTRACT_CONTEXT,
				$EXCHANGE,
				$unique_value) {
		$this->mediaStart = 1;
		$this->mediaEnd = $EXTRACT_CONTEXT->max_images;
		if ($SOURCE_CONTEXT->media_multipart) {
			$this->images = $EXCHANGE->returnMediaObjects($SOURCE_CONTEXT->resource,
								$SOURCE_CONTEXT->media_type,
								$unique_value);
			$this->mediaStart = 0;
			--$this->mediaEnd;
		}
	}

	function getMediaStart() {
		return $this->mediaStart;
	}

	function getMediaEnd() {
		return $this->mediaEnd;
	}

	function getImage($SOURCE_CONTEXT,
			$imageIndex,
			$EXCHANGE,
			$unique_value) {
		$this->content = null;
		if ($SOURCE_CONTEXT->media_multipart) {
	                if ($imageIndex < sizeof($this->images)) {
				$this->content = $this->images[$imageIndex];
				return true;
			}
		} else {
			$this->content = $EXCHANGE->returnMediaObject($SOURCE_CONTEXT->resource,
						$SOURCE_CONTEXT->media_type,
						$unique_value,
						$imageIndex);
			return true;
		}
		return false;
	}

	function storeImage($path,
		$trace, 
		&$BATCH_RESULT) {

		if (file_exists($path)) {
			unlink($path);
		}

		if ($this->content != null) {

			// write to disk

			$handle = fopen($path, 'wb');
			fwrite($handle, $this->content);
			fclose($handle);
			$BATCH_RESULT->addRaw();
			return true;
		}

		return false;
	}

}

class ListingObject {
	var $images = null;
	var $text;
	var $types;
	var $interpretations;
	var $unique_value;
	var $unique_column;
	var $owner;
	var $imageInputFilter;
	var $imageOutputFilter;

	function ListingObject() {
	}

	function getUniqueValue() {
		return $this->unique_value;
	}

	function getUniqueColumn() {
		return $this->unique_column;
	}

	function getImages() {
		return $this->images;
	}

	function addImage($aURL) {
		$this->images[] = $aURL;
	}

	function getImageInputFilter() {
		return $this->imageInputFilter;
	}

	function getImageOutputFilter() {
		return $this->imageOutputFilter;
	}

	function getData() {
		return $this->text;
	}

	function setOwner($value) {
		$this->owner = $value;
	}

	function getOwner() {
		return $this->owner;
	}

	function parseFilter($aFilter) {
		$running = true;
		$result = null;
		$buffer = $aFilter;
		while ($running)
		{
			$pos_s = strpos($buffer, '{');
			if ($pos_s === false) {
				$running = false;
			} else {
				$pos_e = strpos($buffer, '}');
				$result[] = substr($buffer, $pos_s + 1, $pos_e - $pos_s - 1);
				$buffer = substr($buffer, $pos_e + 1, strlen($buffer));
			}
		}
		return $result;
	}

	function setData($dataInputFilter,
		$data,
		$types,
		$interpretations,
		$unique_key,
		$dataOutputFilter,
		$metaColumnOutputFilter,
                $dataTableKey = null,
		$imageInputFilter = null,
		$imageOutputFilter = null) {

		// allow for multiple entries (RDB)

		if ($dataTableKey != null) {
			$dataTableKeyPosition = null;
			foreach ($dataOutputFilter as $key => $value) {
				if ($value == $dataTableKey) {
					$dataTableKeyPosition = $key;
				}
			}
//print('dtkp: ' . $dataTableKeyPosition);
//print('<br/>'); 
		}

		// text
              
		foreach ($data as $key => $value) {
			if ($dataTableKey == null) {
				$pos = $this->array_position($key, $dataInputFilter);
				if ($key == $unique_key) {
					$this->unique_value = $value;
					$this->unique_column = $dataOutputFilter[$pos];
				}
				if ($pos > -1) {
//print('key: ' .$key.' pos: ' . $pos);
//print('<br/>'); 
					$this->text[$dataOutputFilter[$pos]] = html_entity_decode($value);
					$this->types[$dataOutputFilter[$pos]] = $types[$key];
					$this->interpretations[$dataOutputFilter[$pos]] = $interpretations[$key];
				}
			} else {
				$check = $this->array_position_all($key, $dataInputFilter);
				foreach ($check as $key1 => $value1) {
//print('key: ' .$key.' key1: ' . $key1. ' dataTableKeyPosition: ' . $dataTableKeyPosition);
//print('<br/>'); 
					$pos = $this->array_position($key, $dataInputFilter);
					if ($key == $unique_key) {
						$this->unique_value = $value;
						$this->unique_column = $dataOutputFilter[$dataTableKeyPosition];
					}
					if ($pos > -1) {
						$this->text[$dataOutputFilter[$key1]] = html_entity_decode($value);
						$this->types[$dataOutputFilter[$key1]] = $types[$key];
						$this->interpretations[$dataOutputFilter[$key1]] = $interpretations[$key];
					}
				}
			}
		}

//
// metaColumn
//
		if ($metaColumnOutputFilter != null) {
			foreach ($metaColumnOutputFilter as $key => $value) {
				$aList = $this->parseFilter($value);
				$ugc = $value;
				foreach ($aList as $key2 => $value2) {
					$old = '{' . $value2 . '}';
					$rep = $data[$value2]; 
					$ugc = str_replace($old, $rep, $ugc);
				}
				$pos = $this->array_position($key, $dataOutputFilter);
				$this->text[$dataOutputFilter[$pos]] = $ugc;
				$this->types[$dataOutputFilter[$pos]] = 'Character';
				$this->interpretations[$dataOutputFilter[$pos]] = 'UGC';
			}
		}

		// image

		$this->imageInputFilter = $imageInputFilter;
		$this->imageOutputFilter = $imageOutputFilter;

	}

	function array_position_all($needle,
		$haystack) {
                $result = Array();
		foreach ($haystack as $key => $val) {
			if ($val == $needle) {
				$result[$key] = true;
			}
		}

		return $result;
	}

	function array_position($needle,
		$haystack) {
		foreach ($haystack as $key => $val) {
			if ($val == $needle) {
				return $key;
			}
		}

		return -1;
	}

	function getTypes() {
		return $this->types;
	}

	function getType($name) {
		return $this->types[$name];
	}

	function getInterpretations() {
		return $this->interpretations;
	}

	function getInterpretation($name) {
		return $this->interpretations[$name];
	}
}

class SourceContext {
	var $name;
	var $media_type;
	var $media_multipart;
	var $media_location;
	var $simultaneous_logins;
	var $detected_server_name;
	var $resource;
	var $class_name;
	var $offset_adjustment;
	var $account;
	var $password;
	var $url;
	var $application;
	var $version;
	var $clientPassword;
	var $postRequests;
	var $detected_maximum_rets_version;
	var $compact_decoded_format;
	var $detected_standard_names;
	var $summary_items;
	var $unique_key;
	var $query_items;
	var $restricted_indicator;
	var $ownership;
	var $date_variables;
	var $pagination;
	var $null_query_option;
	var $field_types;
	var $field_interpretations;

	function SourceContext() {
	}

	function readConfiguration($S_CONFIGURATION) {
		$this->name = $S_CONFIGURATION->getName();
		$this->media_type = $S_CONFIGURATION->getValue('MEDIA_TYPE');
		$this->media_multipart = $S_CONFIGURATION->getBooleanValue('MEDIA_MULTIPART');
		$this->media_location = $S_CONFIGURATION->getBooleanValue('MEDIA_LOCATION');
		$this->simultaneous_logins = $S_CONFIGURATION->getBooleanValue('SIMULTANEOUS_LOGINS');
		$this->detected_server_name = $S_CONFIGURATION->getValue('DETECTED_SERVER_NAME');
		$this->resource = $S_CONFIGURATION->getValue('SELECTION_RESOURCE');
		$this->class_name = $S_CONFIGURATION->getValue('SELECTION_CLASS');
		$this->offset_adjustment = $S_CONFIGURATION->getValue('OFFSET_ADJUSTMENT');
		$this->account = $S_CONFIGURATION->getValue('RETS_SERVER_ACCOUNT');
		$this->password = $S_CONFIGURATION->getValue('RETS_SERVER_PASSWORD');
		$this->url = $S_CONFIGURATION->getValue('RETS_SERVER_URL');
		$this->application = $S_CONFIGURATION->getValue('APPLICATION');
		$this->version = $S_CONFIGURATION->getValue('VERSION');
		$this->clientPassword = $S_CONFIGURATION->getValue('RETS_CLIENT_PASSWORD');
		$this->postRequests = $S_CONFIGURATION->getBooleanValue('POST_REQUESTS');
		$this->detected_maximum_rets_version = $S_CONFIGURATION->getValue('DETECTED_MAXIMUM_RETS_VERSION');
		$this->compact_decoded_format = $S_CONFIGURATION->getBooleanValue('COMPACT_DECODED_FORMAT');
		$this->detected_standard_names = $S_CONFIGURATION->getBooleanValue('DETECTED_STANDARD_NAMES');
		$this->summary_items = $S_CONFIGURATION->getValue('SUMMARY_ITEMS');
		$this->unique_key = $S_CONFIGURATION->getValue('UNIQUE_KEY');
		$this->query_items = $S_CONFIGURATION->getValue('QUERY_ITEMS');
		$this->restricted_indicator = $S_CONFIGURATION->getValue('RESTRICTED_INDICATOR');
		$this->ownership = $S_CONFIGURATION->getValue('OWNERSHIP_VARIABLE');
		$this->date_variables = $S_CONFIGURATION->getValue('DATE_VARIABLE');
		$this->pagination = $S_CONFIGURATION->getBooleanValue('PAGINATION');
		$this->null_query_option = $S_CONFIGURATION->getValue('NULL_QUERY_OPTION');
//
// ADDED 1.1.8
//
                $migrated = $this->migrateConfiguration($S_CONFIGURATION);
                if (!$migrated) {
                $fields = explode(',', $S_CONFIGURATION->getValue('ALL_FIELDS'));
                if (sizeof($fields) == 1) {
print('SOURCE cannot be migrated and download will fail.  You should redefine the SOURCE</br>');
                }
                $types = explode(',', $S_CONFIGURATION->getValue('ALL_TYPES'));
                $interpretations = explode(',', $S_CONFIGURATION->getValue('ALL_INTERPRETATIONS'));
		$this->field_types = null;
		$this->interpretations = null;
                foreach ($fields as $num => $name) {
                        if ($types[$num] != 'NULL') {
				$this->field_types[$name] = $types[$num];
                        } else {
				$this->field_types[$name] = null;
                        }
                        if ($interpretations[$num] != 'NULL') {
				$this->field_interpretations[$name] = $interpretations[$num];
                        } else {
				$this->field_interpretations[$name] = null;
                        }
                } 
                } 
	}

	function migrateConfiguration($S_CONFIGURATION) {
		$all_fields_check = $S_CONFIGURATION->getValue('ALL_FIELDS');
		if ($all_fields_check == '') {
print('Note: SOURCE defined in a previous version of VieleRETS. Using a temporary workaround.</br>');
			$METADATA_CLASS = new ClassMetadata($this->name, 
							$this->resource);
			$systemClass = $METADATA_CLASS->getSystemClass($this->class_name,
							$this->detected_standard_names);
			$METADATA_TABLE = new TableMetadata($this->name, 
							$systemClass);
			$field_list = $METADATA_TABLE->findNames($this->detected_standard_names);
			$all_fields = implode(',', $field_list);

			$types = $METADATA_TABLE->findDataTypes($this->detected_standard_names);
			$type_List = null;
			foreach ($field_list as $key => $value) {
				if (array_key_exists($value, $types)) {
					$type_list[] = $types[$value];
				} else {
					$type_ist[] = 'NULL';
				}
			} 
			$all_types = implode(',', $type_list);

			$interpretations = $METADATA_TABLE->findInterpretations($this->detected_standard_names);
			$i_list = null;
			foreach ($field_list as $key => $value) {
				if (array_key_exists($value, $interpretations)) {
					$i_list[] = $interpretations[$value];
				} else {
					$i_list[] = 'NULL';
				}
			} 
			$all_interpretations = implode(',', $i_list);

			$this->field_types = null;
			$this->interpretations = null;
                	foreach ($field_list as $num => $name) {
	                        if ($type_list[$num] != 'NULL') {
					$this->field_types[$name] = $type_list[$num];
                	        } else {
					$this->field_types[$name] = null;
	                        }
        	                if ($i_list[$num] != 'NULL') {
					$this->field_interpretations[$name] = $i_list[$num];
                        	} else {
					$this->field_interpretations[$name] = null;
                        	}
                	}
 
			return true;
		}
		return false;
	}
}

class TargetContext {
	var $name;
	var $target_type;
	var $brand;
	var $server;
	var $db_account;
	var $db_password;
	var $database;
	var $data_table;
	var $data_table_key;
	var $image_table;
	var $index_table;
	var $image_upload_path;
	var $class_table;
	var $class_listing_table;
	var $user_element_table;
	var $user_field;
	var $user_value;
	var $rets_user_element;
	var $make_thumbnail;
	var $thumbnail_program;
	var $gd_version2;
	var $thumbnail_quality;
	var $path_to_imagemagick;
	var $thumbnail_width;
	var $days_until_listings_expire;
	var $cache_path;
	var $image_table_key;
	var $include_images;
	var $image_download_path;
	var $container_name;
	var $data_download_path;
	var $data_file_name;
	var $image_file_name;
	var $auto_create;
	var $data_column_list;
	var $image_column_list;
	var $listing_description_template;
	var $image_reference_only;
	var $image_encoded_url;

	function TargetContext() {
	}

	function readConfiguration($T_CONFIGURATION) {
		$this->name = $T_CONFIGURATION->getName();
		$this->target_type = $T_CONFIGURATION->getValue('TYPE');
		$this->brand = $T_CONFIGURATION->getValue('BRAND');
		$this->server = $T_CONFIGURATION->getValue('SERVER');
		$this->db_account = $T_CONFIGURATION->getValue('ACCOUNT');
		$this->db_password = $T_CONFIGURATION->getValue('PASSWORD');
		$this->database = $T_CONFIGURATION->getValue('DATABASE');
		$this->data_table = $T_CONFIGURATION->getValue('DATA_TABLE');
		$this->data_table_key = $T_CONFIGURATION->getValue('DATA_TABLE_KEY');
		$this->image_table = $T_CONFIGURATION->getValue('IMAGE_TABLE');
		$this->index_table = $T_CONFIGURATION->getValue('INDEX_TABLE');
		$this->image_upload_path = $T_CONFIGURATION->getValue('IMAGE_UPLOAD_PATH');
		$this->class_table = $T_CONFIGURATION->getValue('CLASS_TABLE');
		$this->class_listing_table = $T_CONFIGURATION->getValue('CLASS_LISTING_TABLE');
		$this->user_element_table = $T_CONFIGURATION->getValue('USER_ELEMENT_TABLE');
		$this->user_field = $T_CONFIGURATION->getValue('USER_FIELD');
		$this->user_value = $T_CONFIGURATION->getValue('USER_VALUE');
		$this->rets_user_element = $T_CONFIGURATION->getValue('RETS_USER_ELEMENT');
		$this->make_thumbnail = $T_CONFIGURATION->getBooleanValue('THUMBNAILS');
		$this->thumbnail_program = $T_CONFIGURATION->getValue('THUMBNAIL_PROGRAM');
		$this->gd_version2 = $T_CONFIGURATION->getBooleanValue('GD_VERSION_2');
		$this->thumbnail_quality = $T_CONFIGURATION->getValue('THUMBNAIL_QUALITY');
		$this->path_to_imagemagick = $T_CONFIGURATION->getValue('PATH_TO_IMAGEMAGICK');
		$this->thumbnail_width = $T_CONFIGURATION->getValue('THUMBNAIL_WIDTH');
		$this->days_until_listings_expire = $T_CONFIGURATION->getValue('DAYS_UNTIL_EXPIRATION');
		$this->clear_cache = $T_CONFIGURATION->getValue('CACHE_PATH');
		$this->image_table_key = $T_CONFIGURATION->getValue('IMAGE_TABLE_KEY');
		$this->include_images = $T_CONFIGURATION->getBooleanValue('INCLUDE_IMAGES');
		$this->image_download_path = $T_CONFIGURATION->getValue('IMAGE_DOWNLOAD_PATH');
		$this->container_name = $T_CONFIGURATION->getValue('CONTAINER_NAME');
		$this->data_download_path = $T_CONFIGURATION->getValue('DATA_DOWNLOAD_PATH');
		$this->data_file_name = $T_CONFIGURATION->getValue('FILE_NAME');
		$this->image_file_name = $T_CONFIGURATION->getValue('IMAGE_FILE_NAME');
		$this->auto_create = $T_CONFIGURATION->getBooleanValue('AUTO_CREATE');
		$this->data_column_list = $T_CONFIGURATION->getValue('COLUMN_LIST');
		$this->image_column_list = $T_CONFIGURATION->getValue('IMAGE_COLUMN_LIST');
		$this->listing_description_template = $T_CONFIGURATION->getValue('LISTING_DESCRIPTION_TEMPLATE');
		$this->image_reference_only = $T_CONFIGURATION->getBooleanValue('IMAGE_REFERENCE_ONLY');
		$this->image_encoded_url = $T_CONFIGURATION->getBooleanValue('IMAGE_ENCODED_URL');
	}
}

class ExtractContext {
	var $name;
	var $data_maps;
	var $image_maps;
	var $max_images;
	var $extract_column_list;
	var $refresh;
	var $mls_only;
	var $user_ID;
	var $class_name_type;
	var $source_name;
	var $target_name;
	var $batch_size;
	var $trace;
	var $working_file_path;
	var $cache_size;
	var $limit;
	var $status_variable;
	var $status_variable_value;

	function ExtractContext() {
	}

	function readConfiguration($CONFIGURATION) {
		$this->name = $CONFIGURATION->getName();
		$this->data_maps = $CONFIGURATION->getVariable('MAP');
		$this->image_maps = $CONFIGURATION->getVariable('IMAGE_MAP');
		$this->metacolumn_map = $CONFIGURATION->getVariable('METACOLUMN_MAP');
		$this->max_images = $CONFIGURATION->getValue('MAX_IMAGE_COUNT');
		$this->extract_column_list = $CONFIGURATION->getValue('COLUMN_LIST');
		$this->refresh = $CONFIGURATION->getBooleanValue('REFRESH');
		$this->mls_only = $CONFIGURATION->getBooleanValue('MLS_ONLY');
		$this->user_ID = $CONFIGURATION->getValue('USER');
		$this->class_name_type = $CONFIGURATION->getValue('CLASS_NAME_STYLE');
		$this->source_name = $CONFIGURATION->getValue('SOURCE');
		$this->target_name = $CONFIGURATION->getValue('TARGET');
		$this->batch_size = $CONFIGURATION->getValue('BATCH_SIZE');
		$this->trace = $CONFIGURATION->getBooleanValue('TRACE');
		$this->working_file_path = $CONFIGURATION->getValue('WORKING_FILE_PATH');
		$this->cache_size = $CONFIGURATION->getValue('CACHE_SIZE');
		$this->limit = $CONFIGURATION->getValue('LIMIT');
		$this->status_variable = $CONFIGURATION->getValue('STATUS_VARIABLE');
		$this->status_variable_value = $CONFIGURATION->getValue('STATUS_VARIABLE_VALUE');
	}

	function getBatchSize(){
		if (strlen($this->batch_size) == 0) {
			return DEFAULT_BATCH_SIZE;
		}
		return $this->batch_size;
	}
}

class AbstractProcessResults {
	var $listings;
	var $errors;

	function AbstractProcessResults() {
		$this->listings = null;
		$this->errors = null;
	}

	function hasErrors() {
		if ($this->errors == null) {
			return false;
		}
		return true;
	}

	function addError($text) {
		$this->errors[] = $text;
	}

	function addListing($listing) {
		$this->listings[] = $listing;
	}

	function getListings() {
		return $this->listings;
	}
}

class AbstractStatistics {
	var $name;
	var $processed;
	var $duplicates;
	var $refreshed;
	var $skipped;
	var $added;
	var $orphans;
	var $images;
	var $raws;
	var $thumbs;
	var $missing;
	var $start;
	var $finish;
	var $errors;
	var $visibleBreak = null;
	var $spacer = null;
	var $eol = null;

	function AbstractStatistics($name) {
		$this->name = $name;
		$this->processed = 0;
		$this->duplicates = 0;
		$this->refreshed = 0;
		$this->skipped = 0;
		$this->added = 0;
		$this->orphans = 0;
		$this->images = 0;
		$this->raws = 0;
		$this->thumbs = 0;
		$this->missing = 0;
		$this->start = null;
		$this->finish = null;
		$this->errors = null;
	}

	function hasErrors() {
		if ($this->errors == null) {
			return false;
		}
		return true;
	}

	function addError($text) {
		$this->errors[] = $text;
	}

	function summarizeBatch($BATCH_RESULT) {
		if ($BATCH_RESULT != null) {
			$this->addProcessedItems($BATCH_RESULT->getProcessed());
			$this->addDuplicatedItems($BATCH_RESULT->getDuplicates());
			$this->addRefreshedItems($BATCH_RESULT->getRefreshed());
			$this->addSkippedItems($BATCH_RESULT->getSkipped());
			$this->addAdditionalItems($BATCH_RESULT->getAdditions());
			$this->addOrphanedItems($BATCH_RESULT->getOrphans());
			$this->addImages($BATCH_RESULT->getImages());
			$this->addRaws($BATCH_RESULT->getRaws());
			$this->addThumbs($BATCH_RESULT->getThumbs());
			$this->addMissings($BATCH_RESULT->getMissing());
		}
	}

	function addProcessedItems($count) {
		$this->processed += $count;
	}

	function addDuplicatedItems($count) {
		$this->duplicates += $count;
	}

	function addRefreshedItems($count) {
		$this->refreshed += $count;
	}

	function addSkippedItems($count) {
		$this->skipped += $count;
	}

	function addAdditionalItems($count) {
		$this->added += $count;
	}

	function addOrphanedItems($count) {
		$this->orphans += $count;
	}

	function addImages($count) {
		$this->images += $count;
	}

	function addRaws($count) {
		$this->raws += $count;
	}

	function addThumbs($count) {
		$this->thumbs += $count;
	}

	function addMissings($count) {
		$this->missing += $count;
	}

	function addProcessedItem() {
		$this->addProcessedItems(1);
	}

	function addDuplicatedItem() {
		$this->addDuplicatedItems(1);
	}

	function addRefreshedItem() {
		$this->addRefreshedItems(1);
	}

	function addSkippedItem() {
		$this->addSkippedItems(1);
	}

	function addAdditionalItem() {
		$this->addAdditionalItems(1);
	}

	function addOrphanedItem() {
		$this->addOrphanedItems(1);
	}

	function addImage() {
		$this->addImages(1);
	}

	function addRaw() {
		$this->addImage();
		$this->addRaws(1);
	}

	function addThumb() {
		$this->addImage();
		$this->addThumbs(1);
	}

	function addMissing() {
		$this->addImage();
		$this->addMissings(1);
	}

	function getProcessed() {
		return $this->processed;
	}

	function getDuplicates() {
		return $this->duplicates;
	}

	function getRefreshed() {
		return $this->refreshed;
	}

	function getSkipped() {
		return $this->skipped;
	}

	function getAdditions() {
		return $this->added;
	}

	function getOrphans() {
		return $this->orphans;
	}

	function getImages() {
		return $this->images;
	}

	function getRaws() {
		return $this->raws;
	}

	function getThumbs() {
		return $this->thumbs;
	}

	function getMissing() {
		return $this->missing;
	}

	function markStart() {
		$this->errors = null;
		$this->start = time();
	}

	function markFinish() {
		$this->finish = time();
	}

	function getRunTime() {
		return ($this->finish - $this->start);
	}

	function getStart() {
		return 'ERROR in AbstractStatistics::getStart()';
	}

	function getSummary() {
		return 'ERROR in AbstractStatistics::getSummary()';
	}

	function setVisibleBreak($text) {
		$this->visibleBreak = $text;
	}

	function getVisibleBreak() {
		return $this->visibleBreak;
	}

	function setSpacer($text) {
		$this->spacer = $text;
	}

	function getSpacer() {
		return $this->spacer;
	}

	function setEOL($text) {
		$this->eol = $text;
	}

	function getEOL() {
		return $this->eol;
	}
}

?>
