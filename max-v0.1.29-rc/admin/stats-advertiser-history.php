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
$Id: stats-advertiser-history.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-data-statistics.inc.php");

// Register input variables
phpAds_registerGlobal('limit', 'period', 'start', 'hideinactive', 'listorder', 'orderdirection');

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (isset($Session['prefs']['stats-advertiser-history.php']['listorder']) && !isset($listorder)) {
	$listorder = $Session['prefs']['stats-advertiser-history.php']['listorder'];
}

if (isset($Session['prefs']['stats-advertiser-history.php']['orderdirection']) && !isset($orderdirection)) {
	$orderdirection = $Session['prefs']['stats-advertiser-history.php']['orderdirection'];
}

if (isset($Session['prefs']['stats-advertiser-history.php']['hide']) && !isset($hideinactive)) {
	$hideinactive = $Session['prefs']['stats-advertiser-history.php']['hide'];
}

if (isset($Session['prefs']['stats-advertiser-history.php']['limit']) && !isset($limit)) {
	$limit = $Session['prefs']['stats-advertiser-history.php']['limit'];
}

if (isset($Session['prefs']['stats-advertiser-history.php']['period']) && !isset($period)) {
	$period = $Session['prefs']['stats-advertiser-history.php']['period'];
}

if (isset($Session['prefs']['stats-advertiser-history.php']['start']) && !isset($start)) {
	$start = $Session['prefs']['stats-advertiser-history.php']['start'];
}

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

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
	if (phpAds_isUser(phpAds_Admin)) {
		$query = "SELECT clientid,clientname".
			" FROM ".$phpAds_config['tbl_clients'].
			phpAds_getClientListOrder ($listorder, $orderdirection);
	} elseif (phpAds_isUser(phpAds_Agency))	{
		$query = "SELECT clientid,clientname".
			" FROM ".$phpAds_config['tbl_clients'].
			" WHERE agencyid=".phpAds_getUserID().
			phpAds_getClientListOrder ($listorder, $orderdirection);
	}
	$res= phpAds_dbQuery($query)
		or phpAds_sqlDie();
	while ($row = phpAds_dbFetchArray($res)) {
		phpAds_PageContext (
			phpAds_buildName ($row['clientid'], $row['clientname']),
			"stats-advertiser-history.php?clientid=".$row['clientid'],
			$clientid == $row['clientid']
		);
	}
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	phpAds_PageHeader("2.1.1");
	echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;<b>".phpAds_getClientName($clientid)."</b><br><br><br>";
	phpAds_ShowSections(array("2.1.1", "2.1.2"));
}

if (phpAds_isUser(phpAds_Client)) {
	phpAds_PageShortcut($strCampaignOverview, 'advertiser-campaigns.php?clientid='.$clientid, 'images/icon-campaign.gif');
	phpAds_PageHeader("1.1");
	if ($phpAds_config['client_welcome']) {
		echo "<br><br>";
		// Show welcome message
		if (!empty($phpAds_client_welcome_msg)) {
			echo $phpAds_client_welcome_msg;
		} else {
			include('templates/welcome-advertiser.html');
		}
		echo "<br><br>";
	}
	phpAds_ShowSections(array("1.1", "1.2"));
}

/*********************************************************/
/* Main code                                             */
/*********************************************************/

$idresult = phpAds_dbQuery ("
    SELECT
        b.bannerid
    FROM
        {$phpAds_config['tbl_banners']} AS b,
        {$phpAds_config['tbl_campaigns']} AS m
    WHERE
        m.clientid=$clientid
        AND m.campaignid=b.campaignid
") or phpAds_sqlDie();

if (phpAds_dbNumRows($idresult) > 0) {
	while ($row = phpAds_dbFetchArray($idresult)) {
		$bannerids[] = $phpAds_config['tbl_adstats'].".bannerid=".$row['bannerid'];
	}
	$lib_history_where     = "(".implode(' OR ', $bannerids).")";
    $lib_history_params    = array ('clientid' => $clientid);
	$lib_history_hourlyurl = "stats-advertiser-daily.php";

	include ("lib-history.inc.php");

	/*********************************************************/
	/* Maintenance                                           */
	/*********************************************************/

	if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
		echo "<br><br><br>";
		echo "<table width='100%' border='0' align='center' cellspacing='0' cellpadding='0'>";
		echo "<tr><td height='25'><b>$strMaintenance</b></td></tr>";
	  	echo "<tr><td height='1' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		echo "<tr><td height='35'>";
		echo "<img src='images/icon-mail.gif' align='absmiddle'>&nbsp;";
		echo $strSendAdvertisingReport;
		echo "</td></tr>";

		echo "<tr><td height='25'>";
		echo "<form method='get' action='advertiser-mailreport.php'>";
		echo "<input type='hidden' name='clientid' value='$clientid'>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$strFrom&nbsp;&nbsp;";

		// Starting date
		echo "<select name='startday'>\n";
		echo "<option value='' selected>-</option>\n";
		for ($i=1;$i<=31;$i++) {
			echo "<option value='$i'>$i</option>\n";
		}
		echo "</select>&nbsp;\n";
		echo "<select name='startmonth'>\n";
		echo "<option value='' selected>-</option>\n";
		for ($i=1;$i<=12;$i++) {
			echo "<option value='$i'>".$strMonth[$i-1]."</option>\n";
		}
		echo "</select>&nbsp;\n";
		$end = date('Y');
		echo "<select name='startyear'>\n";
		echo "<option value='' selected>-</option>\n";
		for ($i=date('Y')-4;$i<=date('Y');$i++) {
			echo "<option value='$i'>$i</option>\n";
		}
		echo "</select>\n";	

		// To
		echo "&nbsp;$strTo&nbsp;&nbsp;";
		
		// End date
		echo "<select name='endday'>\n";
		for ($i=1;$i<=31;$i++) {
			echo "<option value='$i'".($i == date('d') ? ' selected' : '').">$i</option>\n";
		}
		echo "</select>&nbsp;\n";
		echo "<select name='endmonth'>\n";
		for ($i=1;$i<=12;$i++) {
			echo "<option value='$i'".($i == date('m') ? ' selected' : '').">".$strMonth[$i-1]."</option>\n";
		}
		echo "</select>&nbsp;\n";
		$end = date('Y');
		echo "<select name='endyear'>\n";
		for ($i=date('Y')-4;$i<=date('Y');$i++) {
			echo "<option value='$i'".($i == date('Y') ? ' selected' : '').">$i</option>\n";
		}
		echo "</select>\n";	
		echo "&nbsp;";
    	echo "<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif'>";
		echo "</form>";
		echo "</td></tr>";
		
	  	echo "<tr><td height='1' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		echo "</table>";
    }
} else {
	echo "<br><img src='images/info.gif' align='absmiddle'>&nbsp;";
	echo "<b>".$strNoStats."</b>";
    phpAds_ShowBreak();
}

/*********************************************************/
/* Store preferences                                     */
/*********************************************************/

$Session['prefs']['stats-advertiser-history.php']['listorder'] 		= $listorder;
$Session['prefs']['stats-advertiser-history.php']['orderdirection'] = $orderdirection;
$Session['prefs']['stats-advertiser-history.php']['hide'] 			= $hideinactive;
$Session['prefs']['stats-advertiser-history.php']['limit'] 			= $limit;
$Session['prefs']['stats-advertiser-history.php']['start'] 			= $start;
$Session['prefs']['stats-advertiser-history.php']['period'] 		= $period;
phpAds_SessionDataStore();

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>