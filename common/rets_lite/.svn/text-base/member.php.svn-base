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
class Member 
{

     var $account;
     var $password;
     var $name = null;
     var $agentID;
     var $brokerID;
     var $notice;
     var $signoff;

     function Member($anAccount,
                     $aPassword)
     {
          $this->account = $anAccount;
          $this->password = $aPassword;
     }

     function setName($aName)
     {
          $this->name = $aName;
     }

     function getName()
     {
          if ($this->name == null)
          {
               return $this->getAgentID();
          }
          return $this->name;
     }

     function setAgentID($anID)
     {
          $this->agentID = $anID;
     }

     function getAgentID()
     {
          return $this->agentID;
     }

     function setBrokerID($anID)
     {
          $this->brokerID = $anID;
     }

     function setSignoff($text)
     {
          $this->signoff = $text;
     }

     function getSignoff()
     {
          return $this->signoff;
     }

     function setNotice($text)
     {
          $this->notice = $text;
     }

     function getNotice()
     {
          return $this->notice;
     }

}

//------------

?>
