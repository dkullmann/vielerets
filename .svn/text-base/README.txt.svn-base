VieleRETS 
June 2007
 

Maintainer:	Mark Lesswing (info@crt.realtors.org)

License:	Lesser General Public License (LGPL)


1. Overview

VieleRETS is an open source RETS client intended to download listing
information from servers that support the Real Estate Transaction 
Specification published by the RETS Working Group.  

The project name was choosed as a loose derivation of the German word "many".
Thus VieleRETS gives you access to "many RETS servers".   

This package supports the 1.0 through 1.7 levels of the RETS Specification.  
More information about RETS can be found on the Working Group's website located 
at http://www.rets-wg.org.

VieleRETS is intended to provide internet-based listing support for REALTOR
websites by downloading the information to your hard drive.  It supports
user defined extracts.  This package is an example of a "persistent download"
as defined in the National Association of REALTORS Internet Data Excange (IDX)
policy.

The package supports "sources" which are RETS servers.  RETS servers are 
typically operated by an MLS or a Brokerage.  It is possible to configure 
VieleRETS to communicate with a local source such as the CRT Variman server.

VieleRETS supports "targets" which can be OpenRealty, RDB, XML or CVS.

VieleRETS supports "extracts" which are combinations of "sources" and
"targets".


2. Current Status

This is release 1.1.5 of the project.

This package uses the following targets; 

  A) CVS 

  B) RDB 

  C) XML 

  D) Open-Realty 

VieleRETS formulates queries to RETS servers using either Standard Names
(StandardName) or System Names (SystemName) as defined in the RETS
Specification.  Further, VieleRETS requests that the server supply either
COMPACT-DECODED or COMPACT response formats. 

The package optionally supports the multipart GetObject format as well
parameters that allow it to take advantage of servers that support the
Location option.  In order to support the greatest number of servers as
possible a parameter that controls array starting position is also included.  


3. Dependencies

VieleRETS is dependent on ADODB project, an abstration layer for Relational
Databases that allow VieleRETS to support many databases.

The source code from this projects has been included in the distribution 
have been included to reduce "dependency hell" or the process of finding
all of these pieces on your own before you can run.  Also, given the immature
nature of this package, this tactic ensures that newer version of of these
packages VieleRETS depends on don't change radically.


4. Installation

Although there is complete documentation available, this section is provided
for the impatient.  Here are the basic steps:

  A) Unpack the distribution file (either a .zip or a .tar.gz).

  B) Execute the install.sh (Mac OSX, Unix or Linux) or install.bat script found
in the directory created in Step A).

  C) Configure your web server (that already supports PHP4) to use the root
directory of the unpacked files as a virtual directoty.

  D) Restart the web server.

  E) Point you browser at the index.php file in the root directory of the
distribution (http://{virtual_address)/index.php.  This is the Administration
Interface.

  F) Configure a "source". You will need to know a URL, Account Name and a
Password to a server.  You can obtain these from your MLS.  VieleRETS provides
a default for these which point to the demonstration RETS server operated by
CRT.  The default name for the "source" is "Default", but you can change this.

  G) Configure a "target". This can be Open-Realty, RDB, XML or CSV. 

  H) Configure an "extract".  You will need to know the name of the "source" 
that you would like to extract from. 

  I) The firewall_check.php script is included in the "extras" directory to help
you determine if your firewall is open for the RETS port number 6103.


5. Architecture

VieleRETS is a two tier architecture.  The first tier interacts with the user
and is based on commercial browser technology.  The other tier is a RETS
client that streams information from the RETS server into that target format. 

The following browsers are known to support the code shipped as part of this
distribution:

  A) Microsoft Internet Explorer (Version 5.X and above)

  B) Mozilla and its derivatives (Galeon and Firefox)

  C) Konqueror (Version 2.X and above)

  D) Opera (5.0 and above)

  E) Netscape (4.X and above)

The browser does not need to support cookies, JavaScript or Java processing.
Also, VieleRETS does not utlize "pop-up" windows.  Not relying these options
allows the package to support many browsers and to avoid most common exploits
found on the Internet.

Inorder to run VieleRETS, you must have access to an HTTP server.  If you run 
your website from an ISP, they may support PHP.  The distribution is known to 
work with the following HTTP servers:

  A) Microsoft's IIS

  B) Apache and its derivatives (WebSphere)

  C) OmniHTTPd


Contact info@crt.realtors.org to report issues, comments or problems.

