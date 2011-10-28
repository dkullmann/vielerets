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

include_once(COMMON_DIRECTORY . '/model.php');

function determine_type($typeName) {
	if (!$typeName) {
		return null;
	}
	switch($typeName){
		case 'SOURCE':
			return new Source();
		case 'TARGET':
			return new Target();
		case 'EXTRACT':
			return new Extract();
	}
	return null;
}

function checkSourceDependents($configName,
                               $GENERIC_SOURCE)
{
     $errors = null;
 
     $SOURCE = new VieleSource($GENERIC_SOURCE->getDirectory()); 

//
// develop list of dependent extracts 
//
     $universe = $SOURCE->getDependentExtracts($configName);
     if (strlen($universe) > 0)
     {
          if (sizeof(explode(',', $universe)) == 1)
          {
               $errors[] = 'In use by extract ' .  $universe .  '.';
          }
          else
          {
               $errors[] = 'In use by extracts ' .  $universe .  '.';
          }
     }
  
     return $errors;
}

function moveSourceDependents($oldName, 
                              $newName,
                              $GENERIC_SOURCE)
{
     $SOURCE = new VieleSource($GENERIC_SOURCE->getDirectory()); 
     $universe = $SOURCE->getDependentExtractsList($oldName);
     if ($universe != null) 
     {
          foreach ($universe as $key => $value)
          {
               $EXTRACT = new Extract();
               $E_CONFIGURATION = $EXTRACT->getConfiguration($value);
               $oldTarget = $E_CONFIGURATION->getValue('SOURCE');
               $E_CONFIGURATION->setValue('SOURCE', $newName);
               $EXTRACT->saveConfiguration($E_CONFIGURATION, $value);
          }
     }
}

?>
