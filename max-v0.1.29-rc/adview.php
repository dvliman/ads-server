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
$Id: adview.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Figure out our location
define ('phpAds_path', '.');



/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php"); 
require_once (phpAds_path."/libraries/lib-io.inc.php");
require (phpAds_path."/libraries/lib-db.inc.php");
include_once (phpAds_path . '/libraries/lib-view-main.inc.php');

if ($phpAds_config['log_adviews'] || $phpAds_config['acl'])
{
	require (phpAds_path."/libraries/lib-remotehost.inc.php");
	
	if ($phpAds_config['log_adviews'])
		require (phpAds_path."/libraries/lib-log.inc.php");
	
	if ($phpAds_config['acl'])
		require (phpAds_path."/libraries/lib-limitations.inc.php");
}

require (phpAds_path."/libraries/lib-cache.inc.php");



/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
	'source'
	,'n'
	,'what'
	,'referer'
	,'zoneid'
	,'campaignid'
	,'bannerid'
	,'ct0'
);



/*********************************************************/
/* Main code                                             */
/*********************************************************/
if (!isset($n)) $n = 'default';
if (!isset($source)) $source = '';
if (!isset($what)) {
    if ($zoneid)     { $what = 'zone:'.$zoneid; }
    if ($campaignid) { $what = 'campaignid:'.$campaignid; }
    if ($bannerid)   { $what = 'bannerid:'.$bannerid; }
    
    if (!isset($what)) { $what = ''; }
}
if ((!isset($zoneid) or $zoneid == 0)) $zoneid = (substr($what,0,5) == 'zone:') ? intval(substr($what,5)) : 0;

$source = phpAds_deriveSource($source);

// Remove referer, to be sure it doesn't cause problems with limitations
if (isset($HTTP_SERVER_VARS['HTTP_REFERER'])) unset($HTTP_SERVER_VARS['HTTP_REFERER']);
if (isset($HTTP_REFERER)) unset($HTTP_REFERER);

$richMedia = false;  // Adview is an image tag - we only need the filename of the image...
$target = '';  // Target cannot be dynamically set in basic tags.
$context = ''; // I am not sure what context does...
$withText = 0; // Cannot write text using a simple tag...
$row = view_raw($what, $target, $source, $withText, $context, $richMedia, $ct0);

if (!empty($row['html'])) {
    // Send bannerid headers
    $cookie = array();
    $cookie['bannerid'] = $row['bannerid'];
    
    // Send zoneid headers
    if ($zoneid != 0)
    	$cookie['zoneid'] = $zoneid;
    
    // Send source headers
    if (!empty($source))
    	$cookie['source'] = $source;
    		
    // Store destination URL
    
    /* Added code to update the destination URL stored in the cookie to hold the correct random value (Bug # 88) */
    $cookie['maxdest'] = $GLOBALS['adview_clickurl'];
    
    // view_raw logs traffic if beacons are NOT being used.  But, since we cannot deliver
    //  image beacons with this type of tag, we need to log the traffic here:
    if ($phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) {
        $userid = phpAds_getUniqueUserID();
        phpAds_setCookie("phpAds_id", $userid, time()+365*24*60*60);
        phpAds_logImpression ($userid, $row['bannerid'], $zoneid, $source);
    }
    // Redirect to the banner
    phpAds_setCookie ("phpAds_banner[$n]", serialize($cookie), 0);
    phpAds_flushCookie ();
	header ("Location: {$row['html']}");
}
else
{
	phpAds_setCookie ("phpAds_banner[$n]", 'DEFAULT', 0);
	phpAds_flushCookie ();
	
	if ($phpAds_config['default_banner_url'] != '')
		header ("Location: {$phpAds_config['default_banner_url']}");
	else
	{
		// Show 1x1 Gif, to ensure not broken image icon is shown.
		header ('Content-type: image/gif');
		
		echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');
	}
}
?>
