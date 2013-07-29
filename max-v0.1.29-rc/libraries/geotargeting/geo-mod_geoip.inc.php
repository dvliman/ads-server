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
$Id: geo-mod_geoip.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


/* PUBLIC FUNCTIONS */

$phpAds_geoPluginID = 'mod_geoip';

function phpAds_mod_geoip_getInfo()
{
	return (array (
		'name'	    => 'MaxMind GeoIP (mod)',
		'db'	    => false,
		'country'   => true,
		'continent' => true,
		'region'	=> false
	));
}


function phpAds_mod_geoip_getGeo($addr, $db)
{
	// $addr and $db parameter is ignored and is here for API consistency only
	
	global $HTTP_SERVER_VARS;
	
	if (isset($HTTP_SERVER_VARS['GEOIP_COUNTRY_CODE']))
		$country = $HTTP_SERVER_VARS['GEOIP_COUNTRY_CODE'];
	else
		$country = '';
	
	
	if ($country != '' && $country != '--')
	{
		// Get continent code
		@include_once (phpAds_path.'/libraries/resources/res-continent.inc.php');
		$continent = $phpAds_continent[$country];
		
		return (array (
			'country' => $country,
			'continent' => $continent,
			'region' => false
		));
	}
	else
		return (false);
}

?>