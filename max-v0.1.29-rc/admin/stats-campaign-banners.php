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
$Id: stats-campaign-banners.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-expiration.inc.php");
require ("lib-gd.inc.php");

include_once '../libraries/stats.php';
include_once '../libraries/html.php';

// Register input variables
phpAds_registerGlobal ('view', 'compact', 'listorder', 'orderdirection', 'range');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (phpAds_isUser(phpAds_Agency))
{
	if (isset($campaignid) && $campaignid != '')
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			",".$phpAds_config['tbl_campaigns']." AS m".
			" WHERE c.clientid=m.clientid".
			" AND c.clientid=".$clientid.
			" AND m.campaignid=".$campaignid.
			" AND agencyid=".phpAds_getUserID();
	}
	else
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			" WHERE c.clientid=".$clientid.
			" AND agencyid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Client))
{
	$clientid = phpAds_getUserID();
	if (isset($campaignid) && $campaignid != '')
	{
		$query = "SELECT clientid ".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".$clientid.
			" AND campaignid=".$campaignid;
	}
	else
	{
		$query = "SELECT clientid".
			"FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".$clientid;
	}
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}

/*********************************************************/
/* Get preferences                                       */
/*********************************************************/

if (!isset($compact))
{
	if (isset($Session['prefs']['stats-campaign-banners.php']['compact']))
		$compact = $Session['prefs']['stats-campaign-banners.php']['compact'];
	else
		$compact = 't';
}

if (!isset($view))
{
	if (isset($Session['prefs']['stats-campaign-banners.php']['view']))
		$view = $Session['prefs']['stats-campaign-banners.php']['view'];
	else
		$view = 'all';
}

if (!isset($listorder))
{
	if (isset($Session['prefs']['stats-campaign-banners.php']['listorder']))
		$listorder = $Session['prefs']['stats-campaign-banners.php']['listorder'];
	else
		$listorder = 'name';
}

if (!isset($orderdirection))
{
	if (isset($Session['prefs']['stats-campaign-banners.php']['orderdirection']))
		$orderdirection = $Session['prefs']['stats-campaign-banners.php']['orderdirection'];
	else
		$orderdirection = '';
}

if (!isset($range)) {
    if (isset($Session['prefs']['stats-campaign-banners.php']['range']))
        $range = $Session['prefs']['stats-campaign-banners.php']['range'];
    else
        $range = 'today';
}
$rangeDates = MAX_getDatesByPeriod($range);

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['stats-advertiser-campaigns.php']['listorder']))
	$navorder = $Session['prefs']['stats-advertiser-campaigns.php']['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['stats-advertiser-campaigns.php']['orderdirection']))
	$navdirection = $Session['prefs']['stats-advertiser-campaigns.php']['orderdirection'];
else
	$navdirection = '';


if (phpAds_isUser(phpAds_Client))
{
	if (phpAds_getUserID() == phpAds_getCampaignParentClientID ($campaignid))
	{
		$res = phpAds_dbQuery(
			"SELECT *".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".phpAds_getUserID().
			phpAds_getCampaignListOrder ($navorder, $navdirection)
		) or phpAds_sqlDie();
		
		while ($row = phpAds_dbFetchArray($res))
		{
			phpAds_PageContext (
				phpAds_buildName ($row['campaignid'], $row['campaignname']),
				"stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$row['campaignid'],
				$campaignid == $row['campaignid']
			);
		}
		
		phpAds_PageShortcut($strBannerOverview, "campaign-banners.php?clientid=$clientid&campaignid=$campaignid", 'images/icon-campaign.gif');
	    
		phpAds_PageHeader("1.2.2");

		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
			
		if (phpAds_isAllowed(phpAds_ViewTargetingStats)) 
			phpAds_ShowSections(array("1.2.1", "1.2.2", "1.2.3", "1.2.4"));
		else 
			phpAds_ShowSections(array("1.2.1", "1.2.2", "1.2.3"));
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT campaignid,campaignname".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".$clientid.
			phpAds_getCampaignListOrder ($navorder, $navdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT m.campaignid as campaignid".
			",m.campaignname as campaignname".
			" FROM ".$phpAds_config['tbl_campaigns']." AS m".
			",".$phpAds_config['tbl_clients']." AS c".
			" WHERE m.clientid=c.clientid".
			" AND m.clientid=".$clientid.
			" AND c.agencyid=".phpAds_getUserID().
			phpAds_getCampaignListOrder ($navorder, $navdirection);
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_buildName ($row['campaignid'], $row['campaignname']),
			"stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$row['campaignid'],
			$campaignid == $row['campaignid']
		);
	}
	
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
	
	phpAds_PageHeader("2.1.2.2");
		echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
		phpAds_ShowSections(array("2.1.2.1", "2.1.2.2", "2.1.2.3", "2.1.2.4"));
}



/*********************************************************/
/* Define sections                                       */
/*********************************************************/

// Define defaults
$i = 0;


/*********************************************************/
/* Get statistics                                        */
/*********************************************************/

$banners = array();
$order_array = array();

$query = "SELECT b.bannerid AS bannerid".
	",b.description AS description".
	",b.alt AS alt".
	",SUM(s.views) AS adviews".
	",SUM(s.clicks) AS adclicks".
	",SUM(s.conversions) AS adsales".
	" FROM ".$phpAds_config['tbl_banners']." AS b".
	",".$phpAds_config['tbl_adstats']." AS s".
	" WHERE b.bannerid=s.bannerid".
	" AND b.campaignid=".$campaignid;
	
	if (!empty($rangeDates['day_begin'])) { $query .= " AND s.day >= '".$rangeDates['day_begin']."'"; }
	if (!empty($rangeDates['day_end']))   { $query .= " AND s.day <= '".$rangeDates['day_end']."'"; }
	
    $query .= " GROUP BY bannerid";

$res = phpAds_dbQuery($query)
	or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	$banners[$row['bannerid']]['id'] = $row['bannerid'];
	$banners[$row['bannerid']]['name'] = phpAds_buildBannerName ('', $row['description'], $row['alt']);
	$banners[$row['bannerid']]['adviews'] = $row['adviews'];
	$banners[$row['bannerid']]['adclicks'] = $row['adclicks'];
	$banners[$row['bannerid']]['adsales'] = $row['adsales'];
}

if (count($banners))
{
	// Calculate CTR
	for (reset($banners); $key = key($banners); next($banners))
		$banners[$key]['ctr'] = phpAds_buildCTR ($banners[$key]['adviews'], $banners[$key]['adclicks']);
	
	// Build order array
	for (reset($banners); $key = key($banners); next($banners))
	{
		$order_array[$key] = $banners[$key][$listorder];
	}
	
	// Sort order array
	if ($listorder == 'name')
	{
		if ($orderdirection == 'down')
			asort ($order_array, SORT_STRING);
		else
			arsort ($order_array, SORT_STRING);
	}
	else
	{
		if ($orderdirection == 'down')
			asort ($order_array, SORT_NUMERIC);
		else
			arsort ($order_array, SORT_NUMERIC);
	}
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$totaladviews = 0;
$totaladclicks = 0;


	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";

	echo "<tr><td>";

	echo "<form name='choose_view' action='stats-campaign-keywords.php'>";
	echo "<input type='hidden' name ='clientid' value='".$clientid."'>";
	echo "<input type='hidden' name ='campaignid' value='".$campaignid."'>";
	echo "<select name='period' onChange='this.form.submit();' accesskey='".$keyList."' tabindex='".($tabindex++)."'>";
	echo "<option value='banner'".($HTTP_SERVER_VARS['PHP_SELF'] == '/admin/stats-campaign-banners.php' ? ' selected' : '').">".$strBannerOverview."</option>";
	echo "<option value='keyword'".($HTTP_SERVER_VARS['PHP_SELF'] == '/admin/stats-campaign-keywords.php' ? ' selected' : '').">".$strKeywordStatistics."</option>";
	echo "</select>";
	echo "</td>";
	echo "</form>";
	echo "</tr>";
	
	echo "</table>";
	phpAds_ShowBreak();
	$entityIds = array('clientid'=>$clientid, 'campaignid'=>$campaignid);
	$pageName = basename($_SERVER['SCRIPT_FILENAME']);
    MAX_displayDateSelectionForm($range, $rangeDates, $pageName, $tabindex, $entityIds, 'range');
	
if (count($order_array) > 0)
{
	echo "<br><br>";
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
	
	if (isset($compact) && $compact == "t")
	{
		// Legend
		echo "<tr bgcolor='#FFFFFF' height='25'>";
		echo "<td>&nbsp;</td>";
		
		echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=name'>".$strName."</a>";
		
		if (($listorder == "name") || ($listorder == ""))
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b></td>";
		echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=id'>".$strID."</a>";
		
		if ($listorder == "id")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b></td>";
		echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=adviews'>".$strViews."</a>";
		
		if ($listorder == "adviews")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b></td>";
		echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=adclicks'>".$strClicks."</a>";
		
		if ($listorder == "adclicks")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b></td>";
		echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=ctr'>".$strCTRShort."</a>";
		
		if ($listorder == "ctr")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b></td>";
		
		echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=ctr'>".$strConversions."</a>";
		
		if ($listorder == "conversion")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b>&nbsp;&nbsp;</td>";

		echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b><a href='stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=ctr'>".$strCNVR."</a>";

		if ($listorder == "conversionratio")
		{
			if  (($orderdirection == "") || ($orderdirection == "down"))
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
				echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
			}
			else
			{
				echo ' <a href="stats-campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
				echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
			}
			echo '</a>';
		}
		
		echo "</b>&nbsp;&nbsp;</td>";
		
		
		echo "</tr>";
	}
	
	$where = "";
	
	reset ($order_array);
	while (list ($bannerid,) = each ($order_array)) 
	{
	    $adviews  = $banners[$bannerid]['adviews'];
	    $adclicks = $banners[$bannerid]['adclicks'];
	    $adsales =  $banners[$bannerid]['adsales'];
		
		if ($adclicks != 0 && $view == 'adclicks') continue;	// Don't show banners without adclicks
		if ($adviews != 0 && $view == 'adviews') continue;	// Don't show banners without adclicks
		
		
		$totaladviews += $adviews;
		$totaladclicks += $adclicks;
		$totaladsales += $adsales;
			
		$res_query = "
			SELECT
				bannerid,
				width,
				height,
				active,
				alt,
				description,
				bannertext,
				storagetype
			FROM
				".$phpAds_config['tbl_banners']."
			WHERE
				bannerid = '$bannerid'
			";
		
		$res_banners = phpAds_dbQuery($res_query) or phpAds_sqlDie();
		$row_banners = phpAds_dbFetchArray($res_banners);
		
		$where .= " bannerid = ".$row_banners['bannerid']." OR";
		
		
		// view verbose
		if (isset($compact) && $compact != "t")
		{
			// Background color
			$i % 2 ? $bgcolor="#F6F6F6": $bgcolor= "#F6F6F6";
			$i++;
			
			// Divider
			echo "<tr>";
			echo "<td height='25' colspan='4' align='".$phpAds_TextAlignLeft."'>";
			
			if ($row_banners['active'] == 't')
			{
				if ($row_banners['storagetype'] == 'html')
					echo "<img src='images/icon-banner-html.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'txt')
					echo "<img src='images/icon-banner-text.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'url')
					echo "<img src='images/icon-banner-url.gif' align='absmiddle'>";
				else
					echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>";
			}
			else
			{
				if ($row_banners['storagetype'] == 'html')
					echo "<img src='images/icon-banner-html-d.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'txt')
					echo "<img src='images/icon-banner-text-d.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'url')
					echo "<img src='images/icon-banner-url-d.gif' align='absmiddle'>";
				else
					echo "<img src='images/icon-banner-stored-d.gif' align='absmiddle'>";
			}
			
			echo "&nbsp;<b>".phpAds_buildBannerName ($row_banners['bannerid'], $row_banners['description'], $row_banners['alt'])."</b>";
			
			echo "</td></tr>";
			
			echo "<tr><td height='1' colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
			
			
			// Banner
			echo "<tr><td height='10' colspan='4' bgcolor='$bgcolor'>&nbsp;</td></tr>";
			echo "<tr bgcolor='$bgcolor'>";
			echo "<td height='25' align='".$phpAds_TextAlignLeft."' nowrap>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		   	echo "<td colspan='3' align='".$phpAds_TextAlignLeft."'>";
			echo phpAds_buildBannerCode ($row_banners['bannerid'], true);
			echo "</td></tr>";
			echo "<tr><td height='10' colspan='4' bgcolor='$bgcolor'>&nbsp;</td></tr>";
		  	
			
		    if ($adclicks > 0 || $adviews > 0)
		    {
				// Stats
				echo "<tr bgcolor='$bgcolor'>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>&nbsp;</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>$strViews: <b>".phpAds_formatNumber($adviews)."</b></td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>$strClicks: <b>".phpAds_formatNumber($adclicks)."</b></td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>$strCTR: <b>".phpAds_buildCTR($adviews, $adclicks)."<b></td>";
				echo "</tr>";
			}
			else
			{
				echo "<tr bgcolor='$bgcolor'>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>&nbsp;</td>";
				echo "<td height='25' bgcolor='$bgcolor' colspan='3'>$strBannerNoStats</td>";
				echo "</tr>";
			}
			
			
			// Divider
			echo "<tr><td height='1' colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
			
			
			// Buttons
			echo "<tr><td colspan='4' height='25' align='".$phpAds_TextAlignRight."'>";
			
			if (phpAds_isUser(phpAds_Client) && phpAds_isAllowed(phpAds_DisableBanner) && $row_banners['active'] == 't') // only for the client if allowed
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<img src='images/icon-deactivate.gif' align='absmiddle'>&nbsp;";
				echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."&value=t'>$strDeActivate</a>";
			}
			if (phpAds_isUser(phpAds_Client) && phpAds_isAllowed(phpAds_ActivateBanner) && $row_banners['active'] != 't') // only for the client if allowed
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<img src='images/icon-activate.gif' align='absmiddle'>&nbsp;";
				echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."&value=f'>$strActivate</a>";
			}
			if ($adclicks > 0 || $adviews > 0)
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<img src='images/icon-statistics.gif' align='absmiddle'>&nbsp;";
				echo "<a href='stats-banner-history.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."'>$strBannerHistory</a>";
			}
			
			/*
			  Deactivated for now because of security reasons -- Niels
			  if (phpAds_isUser(phpAds_Admin) || (phpAds_isUser(phpAds_Client) && phpAds_isAllowed(phpAds_ModifyBanner))) // only for the admin
			*/
			
			if (phpAds_isUser(phpAds_Admin))
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<img src='images/icon-edit.gif' align='absmiddle'>&nbsp;";
				echo "<a href='banner-edit.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."'>$strBannerProperties</a>";
			}
			echo "</td></tr>";
			
			echo "<tr><td height='35' colspan='4' bgcolor='#FFFFFF'>&nbsp;</td></tr>";
		} 
		else // view compact
		{
			// Background color
			$i % 2 ? $bgcolor="#FFFFFF": $bgcolor= "#F6F6F6";
			$i++;
			
			echo "<tr><td height='1' colspan='8' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
			echo "<tr bgcolor='$bgcolor'>";
			
			echo "<td height='25' width='30' align='".$phpAds_TextAlignLeft."'>&nbsp;";
			if (ereg ("Mozilla/6", $HTTP_SERVER_VARS['HTTP_USER_AGENT']) || ereg ("IE", $HTTP_SERVER_VARS['HTTP_USER_AGENT']))
				echo "<img name='caret".$row_banners['bannerid']."' src='images/".$phpAds_TextDirection."/triangle-l.gif' align='absmiddle' onClick=\"showHideLayers('".$row_banners['bannerid']."');\">";
			echo "</td>";
			
			
			echo "<td height='25' align='".$phpAds_TextAlignLeft."' nowrap>";
			
			
			if ($row_banners['active'] == 't')
			{
				if ($row_banners['storagetype'] == 'html')
					echo "<img src='images/icon-banner-html.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'txt')
					echo "<img src='images/icon-banner-text.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'url')
					echo "<img src='images/icon-banner-url.gif' align='absmiddle'>";
				else
					echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>";
			}
			else
			{
				if ($row_banners['storagetype'] == 'html')
					echo "<img src='images/icon-banner-html-d.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'txt')
					echo "<img src='images/icon-banner-text-d.gif' align='absmiddle'>";
				elseif ($row_banners['storagetype'] == 'url')
					echo "<img src='images/icon-banner-url-d.gif' align='absmiddle'>";
				else
					echo "<img src='images/icon-banner-stored-d.gif' align='absmiddle'>";
			}
			
			echo "&nbsp;";
			echo "<a height='25' href='stats-banner-history.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."'>";
			
			if ($row_banners['description'] != '')	$name = $row_banners['description'];
			elseif ($row_banners['alt'] != '')		$name = $row_banners['alt'];
			else									$name = $strUntitled;
			
			echo phpAds_breakString ($name, '30');
			
			echo "</a>";
			echo "</td>";
			
			echo "<td height='25' align='".$phpAds_TextAlignLeft."' nowrap>".$row_banners['bannerid']."</td>";
			
		    if ($adclicks > 0 || $adviews > 0)
		    {
				// Stats
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>".phpAds_formatNumber($adviews)."</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>".phpAds_formatNumber($adclicks)."</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>".phpAds_buildCTR($adviews, $adclicks)."&nbsp;&nbsp;</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>".phpAds_formatNumber($adsales)."&nbsp;&nbsp;</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>".number_format(($adclicks ? $adsales / $adclicks * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%&nbsp;&nbsp;</td>";								
			}
			else
			{
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>-</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>-</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>-&nbsp;&nbsp;</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>"."-"."&nbsp;&nbsp;</td>";
				echo "<td height='25' align='".$phpAds_TextAlignRight."' nowrap>"."-"."&nbsp;&nbsp;</td>";								
			}
			
			echo "</tr>";
			
			echo "<tr bgcolor='$bgcolor'>";
			echo "<td height='1' width='30'><img src='images/spacer.gif' width='1' height='1'></td>";
			echo "<td colspan='7'>";
			
			if (ereg ("Mozilla/6", $HTTP_SERVER_VARS['HTTP_USER_AGENT']) || ereg ("IE", $HTTP_SERVER_VARS['HTTP_USER_AGENT']))
			{
				echo "<div id='banner".$row_banners['bannerid']."' style='display: none;'>";
				echo "<table width='100%' cellpadding=0 cellspacing=0 border=0><tr><td align='".$phpAds_TextAlignLeft."'>";
				echo "<tr><td height='1'><img src='images/break-l.gif' height='1' width='100%' vspace='0'></tr><td>";
				echo "<tr><td height='10'>&nbsp;</tr><td>";
				echo "<tr><td>";
					echo phpAds_buildBannerCode ($row_banners['bannerid'], true);
				echo "</tr><td>";
				echo "<tr><td height='10'>&nbsp;</tr><td>";
				echo "<tr><td height='1'><img src='images/break-l.gif' height='1' width='100%' vspace='0'></tr><td>";
				echo "<tr><td height='25'>";
				
				/*
				  Deactivated for now because of security reasons -- Niels
				  if (phpAds_isUser(phpAds_Admin) || (phpAds_isUser(phpAds_Client) && phpAds_isAllowed(phpAds_ModifyBanner))) // only for the admin
				*/
				
				if (phpAds_isUser(phpAds_Admin))
				{
					echo "<a href='banner-edit.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row_banners['bannerid']."'>";
					echo "<img src='images/icon-edit.gif' align='absmiddle' border='0'>&nbsp;$strBannerProperties</a>";
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				echo "</tr><td>";
				echo "</table>";
				
				echo "</div>";
			}
			
			echo "</td></tr>";
		}
	}
	
	
	echo "<tr><td height='1' colspan='8' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	
	echo "<tr>";
	echo "<form action='stats-campaign-banners.php'>";
	echo "<td colspan='8' height='35' align='right'>";
	echo "<input type='hidden' name='clientid' value='$clientid'>";
	echo "<input type='hidden' name='campaignid' value='$campaignid'>";
	echo "<select name='view' onChange='this.form.submit();'>";
		echo "<option value='all'".($view=='all' ? " selected" : "").">$strShowAllBanners</option>";
		echo "<option value='adclicks'".($view=='adclicks' ? " selected" : "").">$strShowBannersNoAdClicks</option>";
		echo "<option value='adviews'".($view=='adviews' ? " selected" : "").">$strShowBannersNoAdViews</option>";
	echo "</select>";
	echo "&nbsp;";
	echo "<select name='compact' onChange='this.form.submit();'>";
		echo "<option value='f'".($compact!='t' ? " selected" : "").">$strVerbose</option>";
		echo "<option value='t'".($compact=='t' ? " selected" : "").">$strCompact</option>";
	echo "</select>";
	echo "&nbsp;";
	echo "<input type='image' border='0' name='submit' src='images/".$phpAds_TextDirection."/go_blue.gif'>";
	echo "</td>";
	echo "</form>";
	echo "</tr>";
	
	
	echo "</table>";
	echo "<br><br>";
	echo "<br><br>";
	
	
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
	echo "<tr><td height='25' colspan='2'><b>".$strCreditStats."</b></td></tr>";
	echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	if (phpAds_GDImageFormat() != "none" && $totaladviews > 0 && !$phpAds_config['compact_stats'])
	{
		$where = ereg_replace("OR$", "", $where);
		echo "<tr><td height='20' colspan='2'>&nbsp;</td></tr>";
		echo "<tr><td bgcolor='#FFFFFF' colspan='2'><img src='graph-hourly.php?where=".urlencode($where)."' border='0' width='385' height='150'></td></tr>";
		echo "<tr><td height='10' colspan='2'>&nbsp;</td></tr>";
		echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	}
	
	list($desc,$enddate,$daysleft) = phpAds_getDaysLeft($campaignid);
	$adclicksleft = phpAds_getAdClicksLeft($campaignid);
	$adviewsleft  = phpAds_getAdViewsLeft($campaignid);
	$adsalesleft  = phpAds_getAdConversionsLeft($campaign);
	
	echo "<tr><td height='25'>".$strTotalViews.": <b>".phpAds_formatNumber($totaladviews)."</b></td>";
	echo "<td height='25'>".$strViewCredits.": <b>".$adviewsleft."</b></td></tr>";
	echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
	
	echo "<tr><td height='25'>".$strTotalClicks.": <b>".phpAds_formatNumber($totaladclicks)."</b></td>";
	echo "<td height='25'>".$strClickCredits.": <b>".$adclicksleft."</b></td></tr>";
	echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
	
	echo "<tr><td height='25'>".$strTotalConversions.": <b>".phpAds_formatNumber($totaladsales)."</b></td>";
	echo "<td height='25'>".$strConversionCredits.": <b>".$adclicksleft."</b></td></tr>";
	echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
	
	
	echo "<tr><td height='25' colspan='2'>".$desc."</td></tr>";
	
	if ($adviewsleft != $strUnlimited || $adclicksleft != $strUnlimited) 
	{
		echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		echo "<tr><td height='60' align='left'>";
		
		if ($adviewsleft == $strUnlimited)
			echo "&nbsp;";
		else
			echo "<img src='graph-daily.php?width=200&data=Views^".$totaladviews."^^Credits^".$adviewsleft."^^'></td>";
		
		echo "<td height='60'>";
		
		if ($adclicksleft == $strUnlimited)
			echo "&nbsp;";
		else
			echo "<img src='graph-daily.php?width=200&data=Clicks^".$totaladclicks."^^Credits^".$adclicksleft."^^'></td>";
		
		echo "</tr>";
	}
	
	echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	echo "</table>";
}
else
{

	echo "<br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>";
	echo $strNoStats.'</div>';

//	echo "<br><img src='images/info.gif' align='absmiddle'>&nbsp;";
//	echo "<b>".$strNoStats."</b>";
//	phpAds_ShowBreak();
}



/*********************************************************/
/* Store preferences                                     */
/*********************************************************/

$Session['prefs']['stats-campaign-banners.php']['compact'] = $compact;
$Session['prefs']['stats-campaign-banners.php']['view'] = $view;
$Session['prefs']['stats-campaign-banners.php']['listorder'] = $listorder;
$Session['prefs']['stats-campaign-banners.php']['orderdirection'] = $orderdirection;
$Session['prefs']['stats-campaign-banners.php']['range'] = $range;


phpAds_SessionDataStore();



/*********************************************************/
/* Custom JavaScript code                                */
/*********************************************************/

?><script language="JavaScript">
<!--
function findObj(n, d) { 
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function showHideLayers(obj) { 
	bannerobj = findObj('banner'+obj);
	caretobj = findObj('caret'+obj);

	if (bannerobj.style)
	{
		if (bannerobj.style.display=='none')
		{
			bannerobj.style.display='';
			if (caretobj) caretobj.src = 'images/triangle-d.gif'
		}
		else
		{
			bannerobj.style.display='none';
			if (caretobj) caretobj.src = 'images/<?php echo $phpAds_TextDirection; ?>/triangle-l.gif'
		}	
	}
}
//-->
</script><?php



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>