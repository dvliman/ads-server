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
$Id: lib-remotehost.inc.php 3495 2005-08-02 14:42:40Z andrew $
*/

require_once(phpAds_path.'/libraries/lib-io.inc.php');

// Check for proxyserver
if ($phpAds_config['proxy_lookup'])
{
	$proxy = false;
	if (isset ($HTTP_SERVER_VARS['HTTP_VIA']) && $HTTP_SERVER_VARS['HTTP_VIA'] != '') $proxy = true;
	
	if (isset ($HTTP_SERVER_VARS['REMOTE_HOST']))
	{
		if (is_int (strpos ('proxy',   $HTTP_SERVER_VARS['REMOTE_HOST']))) $proxy = true;
		if (is_int (strpos ('cache',   $HTTP_SERVER_VARS['REMOTE_HOST']))) $proxy = true;
		if (is_int (strpos ('inktomi', $HTTP_SERVER_VARS['REMOTE_HOST']))) $proxy = true;
	}
	
	if ($proxy)
	{
		// Overwrite host address if a suitable header is found
		if (isset($HTTP_SERVER_VARS['HTTP_FORWARDED']) && 		$HTTP_SERVER_VARS['HTTP_FORWARDED'] != '') 		 $IP = $HTTP_SERVER_VARS['HTTP_FORWARDED'];
		if (isset($HTTP_SERVER_VARS['HTTP_FORWARDED_FOR']) &&	$HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'] != '') 	 $IP = $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'];
		if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED']) &&		$HTTP_SERVER_VARS['HTTP_X_FORWARDED'] != '') 	 $IP = $HTTP_SERVER_VARS['HTTP_X_FORWARDED'];
		if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) &&	$HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'] != '') $IP = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
		if (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']) &&		$HTTP_SERVER_VARS['HTTP_CLIENT_IP'] != '') 		 $IP = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
		
		// Get last item from list
		$IP = explode (',', $IP);
		$IP = trim($IP[count($IP) - 1]);
		
		if ($IP && $IP != 'unknown' && !phpAds_PrivateSubnet($IP))
		{
			$HTTP_SERVER_VARS['REMOTE_ADDR'] = $IP;
			$HTTP_SERVER_VARS['REMOTE_HOST'] = '';
		}
	}
}


// Reverse lookup
if (!isset($HTTP_SERVER_VARS['REMOTE_HOST']) || $HTTP_SERVER_VARS['REMOTE_HOST'] == '')
{
	if ($phpAds_config['reverse_lookup'])
		$HTTP_SERVER_VARS['REMOTE_HOST'] = @gethostbyaddr ($HTTP_SERVER_VARS['REMOTE_ADDR']);
	else
		$HTTP_SERVER_VARS['REMOTE_HOST'] = $HTTP_SERVER_VARS['REMOTE_ADDR'];
}


// Geotracking
if ($phpAds_config['geotracking_type'] != '')
{
	$phpAds_geo = phpAds_getCachedGeoInformation();
	
	if (!isset($phpAds_geo))
	{
		// Determine from IP
		$phpAds_geoPlugin = phpAds_path."/libraries/geotargeting/geo-".$phpAds_config['geotracking_type'].".inc.php";
		
		if (@file_exists($phpAds_geoPlugin))
		{
			@include_once ($phpAds_geoPlugin);
			eval ('$'.'phpAds_geo = phpAds_'.$phpAds_geoPluginID.'_getGeo("'.
				  $HTTP_SERVER_VARS['REMOTE_ADDR'].'", "'.
				  addslashes($phpAds_config['geotracking_location']).'");');
		}
		else
			$phpAds_geo = false;
	}
}
else
	$phpAds_geo = false;



// Translate an IP address into a 32 bit integer
function phpAds_ipAddrToInt($ip)
{
	if (!preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', trim($ip), $match))
		return 0;
	
	return (intval($match[1]) << 24) | (intval($match[2]) << 16) | (intval($match[3]) << 8) | intval($match[4]);
}

// Match an IP address against a subnet
function phpAds_matchSubnet($ip, $net, $mask)
{
	if (!is_integer($ip)) $ip = phpAds_ipAddrToInt($ip);
	$net = phpAds_ipAddrToInt($net);
	
	if (!$ip || !$net)
		return false;
	
	if (is_integer($mask))
	{
		// Netmask notation x.x.x.x/y used
		
		if ($mask > 32 || $mask <= 0)
			return false;
		elseif ($mask == 32)
			$mask = ~0;
		else
			$mask = ~((1 << (32 - $mask)) - 1);
	}
	elseif (!($mask = phpAds_ipAddrToInt($mask)))
		return false;
	
	return ($ip & $mask) == ($net & $mask) ? true : false;
}

function phpAds_PrivateSubnet($ip)
{
	$ip = phpAds_ipAddrToInt($ip);
	
	if (!$ip) return false;
	
	return (phpAds_matchSubnet($ip, '10.0.0.0', 8) || 
		phpAds_matchSubnet($ip, '172.16.0.0', 12) ||
		phpAds_matchSubnet($ip, '192.168.0.0', 16) ||
		phpAds_matchSubnet($ip, '127.0.0.0', 24)
		);
}

?>