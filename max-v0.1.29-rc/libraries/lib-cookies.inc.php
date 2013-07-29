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
$Id: lib-cookies.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


/*********************************************************/
/* Store cookies to be set in a cache                    */
/*********************************************************/

function phpAds_setCookie ($name, $value, $expire = 0)
{
	global $phpAds_cookieCache;
	
	if (!isset($phpAds_cookieCache)) $phpAds_cookieCache = array();
	
	$phpAds_cookieCache[] = array ($name, $value, $expire);
}



/*********************************************************/
/* Send all cookies to the browser and clear cache       */
/*********************************************************/

function phpAds_flushCookie()
{
	global $phpAds_config, $phpAds_cookieCache;
	
	if (isset($phpAds_cookieCache)) {
		// Send P3P headers
		if ($phpAds_config['p3p_policies']) {
			$p3p_header = '';
			if ($phpAds_config['p3p_policy_location'] != '') {
				$p3p_header .= " policyref=\"".$phpAds_config['p3p_policy_location']."\"";
			}
            if ($phpAds_config['p3p_policy_location'] != '' && $phpAds_config['p3p_compact_policy'] != '') {
                $p3p_header .= ", ";
            }
			if ($phpAds_config['p3p_compact_policy'] != '') {
				$p3p_header .= " CP=\"".$phpAds_config['p3p_compact_policy']."\"";
			}
			if ($p3p_header != '') {
				header("P3P: $p3p_header");
			}
		}
		// Get path
		$url_prefix = parse_url($phpAds_config['url_prefix']);
		// Set cookies
		while (list($k,$v) = each ($phpAds_cookieCache)) {
			list ($name, $value, $expire) = $v;
			setcookie($name, $value, $expire, '/');
		}
		// Clear cache
		$phpAds_cookieCache = array();
	}
}

?>
