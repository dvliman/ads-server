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
$Id: adcontent.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Figure out our location
define ('phpAds_path', '.');

// Set invocation type
define ('phpAds_invocationType', 'adcontent');

/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php");
require_once (phpAds_path."/libraries/lib-io.inc.php");
require_once (phpAds_path."/libraries/lib-db.inc.php");
require	(phpAds_path."/libraries/lib-view-main.inc.php");
require (phpAds_path."/libraries/lib-cache.inc.php");
include_once (phpAds_path . '/libraries/db.php');

/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
    'bannerid',
    'target',
    'ct0',
    'zoneid',
    'source',
    'timeout',
    'withtext');

/*********************************************************/
/* Main code                                             */
/*********************************************************/
$bannerid = !empty($bannerid) ? (int)$bannerid : 0;
$target = !empty($target) ? $target : '_blank';
$ct0 = !empty($ct0) ? $ct0 : '';
$zoneid = !empty($zoneid) ? (int)$zoneid : 0;
$source = !empty($source) ? $source : 0;
$timeout = !empty($timeout) ? $timeout : 0;
$withtext = !empty($withtext) ? $withtext : '';

if ($zoneid > 0) {
    // Get the zone from cache...
    $aZone = MAX_getCacheZoneByZoneId($zoneid);
} else {
    // Direct selection, or problem with admin DB
    $aZone = array();
    $aZone['zoneid'] = $zoneid;
    $aZone['append'] = '';
    $aZone['prepend'] = '';
}

// Get the banner from cache...
$aBanner = MAX_getCacheBannerByBannerId($bannerid);

$prepend = !empty($aZone['prepend']) ? $aZone['prepend'] : '';
$html = MAX_buildBannerHtml($aBanner, $zoneid, $source, $target, $ct0, $withtext);
$append = !empty($aZone['append']) ? $aZone['append'] : '';
$title = !empty($aBanner['alt']) ? $aBanner['alt'] : 'Advertisement';

echo "
<html>
<head>
<title>$title</title>";

if ($timeout > 0) {
    $timeoutMs = $timeout * 1000;
    echo "
<script language='JavaScript'>
<!--
  window.setTimeout(\"window.close()\",$timeoutMs);
// -->
</script>";			
}

echo "
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
{$prepend}{$html}{$append}
</body>
</html>";
?>