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
$Id: stats-advertiser-daily.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Include required files
require ("config.php");
require ("lib-statistics.inc.php");

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

/*********************************************************/
/* Client interface security                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Client)) {
	$clientid = phpAds_getUserID();
} elseif (phpAds_isUser(phpAds_Agency)) {
	if (isset($clientid) && ($clientid != '')) {
		$query = "SELECT clientid".
			" FROM ".$phpAds_config['tbl_clients'].
			" WHERE clientid=".$clientid.
			" AND agencyid=".phpAds_getUserID();

		$res = phpAds_dbQuery($query) or phpAds_sqlDie();
		if (phpAds_dbNumRows($res) == 0) {
			phpAds_PageHeader("2");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
}

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

$bannerids = array();

$idresult = phpAds_dbQuery (
	"SELECT b.bannerid".
	" FROM ".$phpAds_config['tbl_banners']." AS b".
	",".$phpAds_config['tbl_campaigns']." AS c".
	" WHERE c.clientid=".$clientid.
	" AND c.campaignid=b.campaignid"
) or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($idresult)) {
	$bannerids[] = "bannerid=".$row['bannerid'];
}


$res = phpAds_dbQuery(
	"SELECT DATE_FORMAT(day, '%Y%m%d') as date".
	",DATE_FORMAT(day, '".$date_format."') as date_formatted".
	" FROM ".$phpAds_config['tbl_adstats'].
	" WHERE (".implode(' OR ', $bannerids).")".
	" GROUP BY day".
	" ORDER BY day DESC".
	" LIMIT 7"
) or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res)) {
	phpAds_PageContext (
		$row['date_formatted'],
		"stats-advertiser-daily.php?day=".$row['date']."&clientid=".$clientid,
		$day == $row['date']
	);
}

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	
	phpAds_PageHeader("2.1.1.1");
	echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getClientName($clientid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-date.gif' align='absmiddle'>&nbsp;<b>".date(str_replace('%', '', $date_format), mktime(0, 0, 0, substr($day, 4, 2), substr($day, 6, 2), substr($day, 0, 4)))."</b><br><br><br>";
		
	$sections[] = "2.1.1.1";
	//if (!$phpAds_config['compact_stats']) $sections[] = "2.1.1.2";
	phpAds_ShowSections($sections);
}

if (phpAds_isUser(phpAds_Client)) {
	phpAds_PageShortcut($strCampaignOverview, 'advertiser-campaigns.php?clientid='.$clientid, 'images/icon-campaign.gif');
	phpAds_PageHeader("1.1.1");
	echo "<img src='images/icon-date.gif' align='absmiddle'>&nbsp;<b>".date(str_replace('%', '', $date_format), mktime(0, 0, 0, substr($day, 4, 2), substr($day, 6, 2), substr($day, 0, 4)))."</b><br><br>";
		
	$sections[] = "1.1.1";
	//if (!$phpAds_config['compact_stats']) $sections[] = "1.1.2";
	phpAds_ShowSections($sections);
}

/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_hourly_where = "(".implode(' OR ', $bannerids).")";

include ("lib-hourly.inc.php");

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>