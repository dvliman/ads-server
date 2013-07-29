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
$Id: lib-append.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBAPPEND_INCLUDED', true);


// Include zones library if needed
if (!defined('LIBZONES_INCLUDED'))
	require ("lib-zones.inc.php");


// Define appendtypes
define ("phpAds_AppendRaw", 0);
define ("phpAds_AppendZone", 1);
define ("phpAds_AppendBanner", 2);



/*********************************************************/
/* Fetch parameters from append code                     */
/*********************************************************/

function phpAds_ParseAppendCode ($append)
{
	global $phpAds_config;
	
	$ret = array(
		array('zoneid' => '', 'delivery' => phpAds_ZonePopup),
		array()
	);
	
	if (ereg("ad(popup|layer)\.php\?([^'\"]+)['\"]", $append, $match))
	{
		if (!empty($match[2]))
		{
			$ret[0]['delivery'] = ($match[1] == 'popup') ? phpAds_ZonePopup : phpAds_ZoneInterstitial;
			
			$append = str_replace('&amp;', '&', $match[2]);
			
			if (ereg('[\?\&]?what=zone:([0-9]+)(&|$)', $append, $match))
			{
				$ret[0]['zoneid'] = $match[1];
				
				$append = explode('&', $append);
				while (list(, $v) = each($append))
				{
					$v = explode('=', $v);
					if (count($v) == 2)
						$ret[1][urldecode($v[0])] = urldecode($v[1]);
				}
			}
		}
	}
	
	return $ret;
}

?>