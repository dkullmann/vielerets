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
// includes
//
include_once("./controller.php");
include_once(COMMON_DIRECTORY . "/model.php");
include_once(COMMON_DIRECTORY . "/view.php");
include_once(COMMON_DIRECTORY . "/download.php");


//print_r($vars);
//
// guard against timeouts
//
set_time_limit(0);
//ignore_user_abort(TRUE);

if(!is_array($vars) || ! isset($vars["ELEMENT"]) || ! isset($vars["ID"]))
{
        print '-1';
        exit;
}

//
// open extract configuration
//
$EXTRACT = new Extract();
$configName = $EXTRACT->toName($vars["ELEMENT"]);
$configFile = $EXTRACT->toPath($vars["ELEMENT"]);
$CONFIGURATION = new Configuration($configFile);

if(! is_array($CONFIGURATION->contents))
{
        print '-1';
        exit;
}

//
// determine source
//
$source = $CONFIGURATION->getValue("SOURCE");
$SOURCE = new Source();
$configName = $SOURCE->toName($source);
$configFile = $SOURCE->toPath($source);
$S_CONFIGURATION = new Configuration($configFile);


// construct TARGET and EXTRACT contexts

$EXTRACT_CONTEXT = new ExtractContext();
$EXTRACT_CONTEXT->readConfiguration($CONFIGURATION);

$target = $EXTRACT_CONTEXT->target_name;
$TARGET = new Target();
$T_CONFIGURATION = new Configuration($TARGET->toPath($target));
$TARGET_CONTEXT = new TargetContext();
$TARGET_CONTEXT->readConfiguration($T_CONFIGURATION);

$SOURCE_CONTEXT = new SourceContext();
$SOURCE_CONTEXT->readConfiguration($S_CONFIGURATION);

$RETRIEVER = new Retriever($SOURCE_CONTEXT->name);
$RETRIEVER->login($S_CONFIGURATION);

$DOWNLOADER = new Downloader();

for ($i = 1; $i <= $EXTRACT_CONTEXT->max_images; $i++)
{

        // generate media object

        $anImage = $RETRIEVER->returnMediaObject($SOURCE_CONTEXT->resource,
                $SOURCE_CONTEXT->media_type,
                $vars["ID"],
                $i,
                false);

    if ($anImage != null)
    {
                $save_path = $DOWNLOADER->createImagePath($TARGET_CONTEXT->image_download_path,
                        $vars["ID"],
                        $i);

        if (file_exists($save_path))
        {
                unlink($save_path);
        }

        // write to disk

        $handle = fopen($save_path, "wb");
        fwrite($handle, $anImage);
        fclose($handle);
    }
        else
        {
                // Assume no more images
                break;
        }
}

if($i > $EXTRACT_CONTEXT->max_images)
{
        $i = $EXTRACT_CONTEXT->max_images;
}

print $i;


?>

