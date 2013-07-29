<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: adconversionjs.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Figure out our location
define ('phpAds_path', '.');



/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php"); 
include_once phpAds_path . '/libraries/lib-view-main.inc.php';
include_once phpAds_path . '/libraries/lib-io.inc.php';
include_once phpAds_path . '/libraries/lib-log.inc.php';
include_once phpAds_path . '/libraries/lib-remotehost.inc.php';
include_once phpAds_path . '/libraries/lib-db.inc.php';


/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
	 'block'
	,'capping'
	,'session_capping'
	,'trackerid'
);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Determine the user ID
$userid = phpAds_getUniqueUserID(false);
$conversionsid = NULL;
$variables_script = '';

header("Content-type: application/x-javascript");

// Send the user ID
// phpAds_setCookie("phpAds_id", $userid, time()+365*24*60*60);

if (!phpAds_isConversionBlocked($trackerid))
{
	if ($phpAds_config['log_adconversions'])
	{
		//phpAds_dbConnect();
		$conversionInfo = phpAds_logConversion($userid, $trackerid);
	}
	
	// Handles variable retrieval from the page
	$variables_script = MAX_buildJavascriptVariablesScript($trackerid,$conversionInfo);
	
	// Send block cookies
	phpAds_updateConversionBlockTime($trackerid);
}

phpAds_updateGeotracking($phpAds_geo);

phpAds_flushCookie ();

echo (strlen($variables_script) > 0 ? $variables_script : "var phpads = '';\ndocument.write(phpads);\n");

?>
