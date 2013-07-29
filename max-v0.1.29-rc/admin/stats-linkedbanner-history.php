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
$Id: stats-linkedbanner-history.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-expiration.inc.php");


// Register input variables
phpAds_registerGlobal ('period', 'start', 'limit');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Affiliate);



/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Affiliate))
{
	if (isset($zoneid) && $zoneid > 0)
	{
		$result = phpAds_dbQuery("
			SELECT
				affiliateid
			FROM
				".$phpAds_config['tbl_zones']."
			WHERE
				zoneid = '$zoneid'
			") or phpAds_sqlDie();
		$row = phpAds_dbFetchArray($result);
		
		if ($row["affiliateid"] == '' || phpAds_getUserID() != $row["affiliateid"])
		{
			phpAds_PageHeader("1");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
		else
		{
			$affiliateid = phpAds_getUserID();
		}
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	if (isset($zoneid) && $zoneid > 0)
	{
		$result = phpAds_dbQuery(
			"SELECT z.affiliateid AS affiliateid".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE zoneid='$zoneid'".
			" AND a.affiliateid=z.affiliateid".
			" AND a.agencyid=".phpAds_getUserID()
		) or phpAds_sqlDie();
		$row = phpAds_dbFetchArray($result);
		
		if ($row["affiliateid"] == '')
		{
			phpAds_PageHeader("1");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	$res = phpAds_dbQuery("
		SELECT
			DISTINCT bannerid
		FROM
			".$phpAds_config['tbl_adstats']."
		WHERE
			zoneid=".$zoneid."
	") or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_getBannerName ($row['bannerid']),
			"stats-linkedbanner-history.php?affiliateid=".$affiliateid."&zoneid=".$zoneid."&bannerid=".$row['bannerid'],
			$bannerid == $row['bannerid']
		);
	}

		// Get the adviews/clicks for each banner
		$res_anonymous = phpAds_dbQuery("
			SELECT
				b.bannerid,
				b.campaignid,
				c.campaignid,
				anonymous
			FROM 
				".$phpAds_config['tbl_banners']." as b,
				".$phpAds_config['tbl_campaigns']." as c
			WHERE
				b.bannerid = ".$bannerid." AND
				b.campaignid = c.campaignid
			") or phpAds_sqlDie();
		
		$row_anonymous = phpAds_dbFetchArray($res_anonymous);
		
		$anonymous 	= $row_anonymous['anonymous'];


	phpAds_PageShortcut($strAffiliateProperties, 'affiliate-edit.php?affiliateid='.$affiliateid, 'images/icon-affiliate.gif');	
	phpAds_PageShortcut($strZoneProperties, 'zone-edit.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone.gif');	
	phpAds_PageShortcut($strIncludedBanners, 'zone-include.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone-linked.gif');	
	
	
	phpAds_PageHeader("2.4.2.2.1");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;".phpAds_getZoneName($zoneid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone-linked.gif' align='absmiddle'>&nbsp;<b>" . phpAds_getBannerName($bannerid) . ($anonymous == 't' ? " - This is a blind campaign!" : "") . "</b><br><br><br>";
	phpAds_ShowSections(array("2.4.2.2.1"));
}
else
{
		// Get the adviews/clicks for each banner
		$res_anonymous = phpAds_dbQuery("
			SELECT
				b.bannerid,
				b.campaignid,
				c.campaignid,
				anonymous
			FROM 
				".$phpAds_config['tbl_banners']." as b,
				".$phpAds_config['tbl_campaigns']." as c
			WHERE
				b.bannerid = ".$bannerid." AND
				b.campaignid = c.campaignid
			") or phpAds_sqlDie();
		
		$row_anonymous = phpAds_dbFetchArray($res_anonymous);
		
		$anonymous 	= $row_anonymous['anonymous'];

	phpAds_PageHeader("1.1.2.1");
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;".phpAds_getZoneName($zoneid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone-linked.gif' align='absmiddle'>&nbsp;<b>".($anonymous == "f" ? phpAds_getBannerName($bannerid) : "(Hidden Banner)")."</b><br><br><br>";
	phpAds_ShowSections(array("1.1.2.1"));
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_history_where     = $phpAds_config['tbl_adstats'].".zoneid = ".$zoneid." AND .".$phpAds_config['tbl_adstats'].".bannerid = ".$bannerid;
$lib_history_params    = array ('affiliateid' => $affiliateid,
								'zoneid' => $zoneid,
								'bannerid' => $bannerid
						 );

include ("lib-history.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>