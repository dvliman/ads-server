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
$Id: banner-delete.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");
require ("lib-statistics.inc.php");
require ("../libraries/lib-priority.inc.php");


// Register input variables
phpAds_registerGlobal ('returnurl');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

if (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT ".
		$phpAds_config['tbl_banners'].".bannerid as bannerid".
		" FROM ".$phpAds_config['tbl_clients'].
		",".$phpAds_config['tbl_campaigns'].
		",".$phpAds_config['tbl_banners'].
		" WHERE ".$phpAds_config['tbl_campaigns'].".clientid=".$clientid.
		" AND ".$phpAds_config['tbl_banners'].".campaignid=".$campaignid.
		" AND ".$phpAds_config['tbl_banners'].".bannerid=".$bannerid.
		" AND ".$phpAds_config['tbl_banners'].".campaignid=".$phpAds_config['tbl_campaigns'].".campaignid".
		" AND ".$phpAds_config['tbl_campaigns'].".clientid=".$phpAds_config['tbl_clients'].".clientid".
		" AND ".$phpAds_config['tbl_clients'].".agencyid=".phpAds_getUserID();
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}

$cache_string = array();

/*********************************************************/
/* Main code                                             */
/*********************************************************/

function phpAds_DeleteBanner($bannerid)
{
	global $phpAds_config;
	
	// Cleanup webserver stored image
	$res = phpAds_dbQuery("
		SELECT
			storagetype, filename
		FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			bannerid = '$bannerid'
	") or phpAds_sqlDie();
	
	if ($row = phpAds_dbFetchArray($res))
	{
		if (($row['storagetype'] == 'web' || $row['storagetype'] == 'sql') && $row['filename'] != '')
			phpAds_ImageDelete ($row['storagetype'], $row['filename']);
	}
	
	// Delete banner
	$res = phpAds_dbQuery("
		DELETE FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			bannerid = '$bannerid'
		") or phpAds_sqlDie();
	
	// Delete banner ACLs
	$res = phpAds_dbQuery("
		DELETE FROM
			".$phpAds_config['tbl_acls']."
		WHERE
			bannerid = '$bannerid'
		") or phpAds_sqlDie();
	
	// Delete statistics for this banner
	phpAds_deleteStatsByBannerID($bannerid);
}

if (isset($bannerid) && $bannerid != '')
{
	phpAds_DeleteBanner($bannerid);
	$cache_string[] = "bannerid:".$bannerid;
}
elseif (isset($campaignid) && $campaignid != '')
{
	$res = phpAds_dbQuery("
		SELECT
			bannerid
		FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			campaignid = '$campaignid'
	");
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_DeleteBanner($row['bannerid']);
		$cache_string[] = $row['bannerid'];
	}
}

$cache_string_implode = implode("|", $cache_string);
include_once('lib-instant-update.inc.php');
instant_update($cache_string_implode);	

if (!isset($returnurl) && $returnurl == '')
	$returnurl = 'campaign-banners.php';

Header("Location: ".$returnurl."?clientid=".$clientid."&campaignid=".$campaignid);

?>