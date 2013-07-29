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
$Id: stats-banner-history.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Register input variables
phpAds_registerGlobal ('period', 'start', 'limit','clientid','campaignid','bannerid');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);



/*********************************************************/
/* Client interface security                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Client))
{
	$clientid = phpAds_getUserID();
	
	$query = "SELECT b.campaignid as campaignid".
		" FROM ".$phpAds_config['tbl_banners']." AS b".
		",".$phpAds_config['tbl_campaigns']." AS m".
		" WHERE b.campaignid=m.campaignid".
		" AND m.clientid=".$clientid.
		" AND b.bannerid=".$bannerid;
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$row = phpAds_dbFetchArray($res);
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
	else
	{
		$campaignid = $row["campaignid"];
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT b.campaignid as campaignid".
		",m.clientid as clientid".
		" FROM ".$phpAds_config['tbl_banners']." AS b".
		",".$phpAds_config['tbl_campaigns']." AS m".
		",".$phpAds_config['tbl_clients']." AS c".
		" WHERE b.campaignid=m.campaignid".
		" AND m.clientid=c.clientid".
		" AND m.clientid=".$clientid.
		" AND b.bannerid=".$bannerid;
		
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$row = phpAds_dbFetchArray($res);
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
	else
	{
		$campaignid = $row["campaignid"];
	}
}


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['stats-campaign-banners.php']['listorder']))
	$navorder = $Session['prefs']['stats-campaign-banners.php']['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['stats-campaign-banners.php']['orderdirection']))
	$navdirection = $Session['prefs']['stats-campaign-banners.php']['orderdirection'];
else
	$navdirection = '';


$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_banners']."
	WHERE
		campaignid = '$campaignid'
	".phpAds_getBannerListOrder($navorder, $navdirection)."
") or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildBannerName ($row['bannerid'], $row['description'], $row['alt']),
		"stats-banner-history.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row['bannerid'],
		$bannerid == $row['bannerid']
	);
}

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
	phpAds_PageShortcut($strBannerProperties, 'banner-edit.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-banner-stored.gif');
	phpAds_PageShortcut($strModifyBannerAcl, 'banner-acl.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-acl.gif');

	phpAds_PageHeader("2.1.2.2.1", $extra);
		echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
		echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
		phpAds_ShowSections(array("2.1.2.2.1", "2.1.2.2.2"));
}
elseif (phpAds_isUser(phpAds_Client))
{
    phpAds_PageShortcut($strBannerProperties, 'banner-edit.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-banner-stored.gif');
	
	$sections[] = "1.2.2.1";
	$sections[] = "1.2.2.4";
	
	phpAds_PageHeader("1.2.2.1");
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
		echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
		phpAds_ShowSections($sections);
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_history_hourlyurl = "stats-banner-daily.php";

$lib_history_where     = $phpAds_config['tbl_adstats'].".bannerid = ".$bannerid;
$lib_history_params    = array ('clientid' => $clientid,
								'campaignid' => $campaignid,
								'bannerid' => $bannerid
						 );
include ("lib-history.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>