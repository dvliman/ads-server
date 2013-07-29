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
$Id: adlog.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Figure out our location
define ('phpAds_path', '.');



/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php");
require_once (phpAds_path."/libraries/lib-io.inc.php");
require (phpAds_path."/libraries/lib-db.inc.php");
require (phpAds_path."/libraries/lib-remotehost.inc.php");
require (phpAds_path."/libraries/lib-log.inc.php");
require (phpAds_path."/libraries/lib-cache.inc.php");



/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal ('bannerid', 'zoneid', 'source',
					   'block', 'capping', 'session_capping');



/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Determine the user ID
$userid = phpAds_getUniqueUserID();

// Send the user ID
phpAds_setCookie("phpAds_id", $userid, time()+365*24*60*60);

if (isset($bannerid) && isset($zoneid))
{
	$source = phpAds_deriveSource($source);
	
	if (!phpAds_isViewBlocked($bannerid))
	{
		if ($phpAds_config['log_beacon'] && $phpAds_config['log_adviews'])
		{
			phpAds_logImpression ($userid, $bannerid, $zoneid, $source);
		}
		
		// Send block cookies
		phpAds_updateViewBlockTime($bannerid);
	}
	

	// Update the time which this ad can be seen again
	phpAds_updateAdBlockTime($bannerid, $block);
	// Update Capping information for this banner.
	phpAds_updateAdCapping($bannerid, $capping, $session_capping);
	// Update Geotracking information
	phpAds_updateGeoTracking($phpAds_geo);
	phpAds_flushCookie ();
}

header ("Content-Type: image/gif");
header ("Content-Length: 43");

// 1 x 1 gif
echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');

?>