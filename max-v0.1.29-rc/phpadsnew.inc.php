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
$Id: phpadsnew.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


if (!defined('PHPADSNEW_INCLUDED'))
{
	// Figure out our location
	if (strlen(__FILE__) > strlen(basename(__FILE__)))
	    define ('phpAds_path', substr(__FILE__, 0, strlen(__FILE__) - strlen(basename(__FILE__)) - 1));
	else
	    define ('phpAds_path', '.');
	
	// If this path doesn't work for you, customize it here like this
	// Note: no trailing backslash
	// define ('phpAds_path', "/home/myname/www/phpAdsNew");
	
	
	// Globalize settings and IO
	// (just in case phpadsnew.inc.php is called from a function)
	global $phpAds_config, $HTTP_SERVER_VARS;
	
	
	// Include required files
	require	(phpAds_path."/config.inc.php"); 
	require_once (phpAds_path."/libraries/lib-io.inc.php");
	require (phpAds_path."/libraries/lib-db.inc.php");
	
	
	if (($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon']) || $phpAds_config['acl'])
	{
		require (phpAds_path."/libraries/lib-remotehost.inc.php");
		
		if ($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon'])
			require (phpAds_path."/libraries/lib-log.inc.php");
		
		if ($phpAds_config['acl'])
			require (phpAds_path."/libraries/lib-limitations.inc.php");
	}
	
	require	(phpAds_path."/libraries/lib-view-main.inc.php");
	
	// This function is a wrapper to view raw, this allows for future migration 
	function view_local($what, $zoneid = 0, $campaignid = 0, $bannerid = 0, $target = '', $source = '', $withtext = '', $context = '') {
	    if (!((strstr($what, 'zone')) or (strstr($what, 'campaign')) or (strstr($what, 'banner')))) { 
	        if ($zoneid)       $what = "zone:".$zoneid;
	        if ($campaignid)   $what = "campaignid:".$campaignid;
	        if ($bannerid)     $what = "bannerid:".$bannerid;
	    }

	    return view_raw($what, $target, $source, $withtext, $context);
	}
	// Prevent duplicate includes
	define ('PHPADSNEW_INCLUDED', true);
}

?>
