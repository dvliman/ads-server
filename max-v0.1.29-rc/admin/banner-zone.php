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
$Id: banner-zone.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");
require ("lib-size.inc.php");


// Register input variables
phpAds_registerGlobal ('submit', 'includezone', 'listorder', 'orderdirection');


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


/*********************************************************/
/* Process submitted form                                */
/*********************************************************/

if (isset($submit))
{
	$previouszone = array();
	
	// Get all zones
	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT zoneid,what".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE zonetype=".phpAds_ZoneBanners;
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT zoneid,what".
			" FROM ".$phpAds_config['tbl_zones'].
			",".$phpAds_config['tbl_affiliates'].
			" WHERE ".$phpAds_config['tbl_zones'].".affiliateid=".$phpAds_config['tbl_affiliates'].".affiliateid".
			" AND ".$phpAds_config['tbl_affiliates'].".agencyid=".phpAds_getUserID().
			" AND zonetype=".phpAds_ZoneBanners;
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
		$previouszone[$row['zoneid']] = (phpAds_IsBannerInZone ($bannerid, $row['zoneid'], $row['what']));
	
	
	for (reset($previouszone);$key=key($previouszone);next($previouszone))
	{
		if (($previouszone[$key] == true && (!isset($includezone[$key]) || $includezone[$key] != 't')) ||
		    ($previouszone[$key] != true && (isset($includezone[$key]) && $includezone[$key] == 't')))
		{
			phpAds_ToggleBannerInZone ($bannerid, $key);
		}
	}
	// Rebuild priorities
	include_once('lib-instant-update.inc.php');
    instant_update($bannerid);
}



/*********************************************************/
/* Get preferences                                       */
/*********************************************************/

if (!isset($listorder))
{
	if (isset($Session['prefs']['banner-zone.php']['listorder']))
		$listorder = $Session['prefs']['banner-zone.php']['listorder'];
	else
		$listorder = '';
}

if (!isset($orderdirection))
{
	if (isset($Session['prefs']['banner-zone.php']['orderdirection']))
		$orderdirection = $Session['prefs']['banner-zone.php']['orderdirection'];
	else
		$orderdirection = '';
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['listorder']))
	$navorder = $Session['prefs']['campaign-banners.php'][$campaignid]['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection']))
	$navdirection = $Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'];
else
	$navdirection = '';


// Get other banners
$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_banners']."
	WHERE
		campaignid = '$campaignid'
	".phpAds_getBannerListOrder($navorder, $navdirection)."
");

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildBannerName ($row['bannerid'], $row['description'], $row['alt']),
		"banner-zone.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row['bannerid'],
		$bannerid == $row['bannerid']
	);
}

phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
phpAds_PageShortcut($strBannerHistory, 'stats-banner-history.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-statistics.gif');



$extra  = "<form action='banner-modify.php'>";
$extra .= "<input type='hidden' name='clientid' value='$clientid'>";
$extra .= "<input type='hidden' name='campaignid' value='$campaignid'>";
$extra .= "<input type='hidden' name='bannerid' value='$bannerid'>";
$extra .= "<input type='hidden' name='returnurl' value='banner-zone.php'>";
$extra .= "<br><br>";
$extra .= "<b>$strModifyBanner</b><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-duplicate-banner.gif' align='absmiddle'>&nbsp;<a href='banner-modify.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&duplicate=true&returnurl=banner-zone.php'>$strDuplicate</a><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-move-banner.gif' align='absmiddle'>&nbsp;$strMoveTo<br>";
$extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
$extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$extra .= "<select name='moveto' style='width: 110;'>";

if (phpAds_isUser(phpAds_Admin))
{
	$query = "SELECT campaignid,campaignname".
		" FROM ".$phpAds_config['tbl_campaigns'].
		" WHERE campaignid !=".$campaignid;
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT campaignid,campaignname".
		" FROM ".$phpAds_config['tbl_campaigns'].
		",".$phpAds_config['tbl_clients'].
		" WHERE ".$phpAds_config['tbl_campaigns'].".clientid=".$phpAds_config['tbl_clients'].".clientid".
		" AND ".$phpAds_config['tbl_clients'].".agencyid=".phpAds_getUserID().
		" AND campaignid !=".$campaignid;
}
$res = phpAds_dbQuery($query)
	or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
	$extra .= "<option value='".$row['campaignid']."'>".phpAds_buildName($row['campaignid'], $row['campaignname'])."</option>";

$extra .= "</select>&nbsp;<input type='image' name='moveto' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteBanner).">$strDelete</a><br>";
$extra .= "</form>";



$sections = array ("4.1.3.3.2", "4.1.3.3.3", "4.1.3.3.6", "4.1.3.3.4");

phpAds_PageHeader("4.1.3.3.4", $extra);
	echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
	echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
	phpAds_ShowSections($sections);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Get banner info
$res = phpAds_dbQuery ("SELECT * FROM ".$phpAds_config['tbl_banners']." WHERE bannerid=".$bannerid)
	or phpAds_sqlDie();

$banner = phpAds_dbFetchArray($res);

// Get affiliates
if (phpAds_isUser(phpAds_Admin))
{
	$query = "SELECT affiliateid,name".
		" FROM ".$phpAds_config['tbl_affiliates'].
		phpAds_getAffiliateListOrder($listorder, $orderdirection);
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT affiliateid,name".
		" FROM ".$phpAds_config['tbl_affiliates'].
		" WHERE agencyid=".phpAds_getUserID().
		phpAds_getAffiliateListOrder($listorder, $orderdirection);
}
$res = phpAds_dbQuery ($query)
	or phpAds_sqlDie();

$affiliate_count = phpAds_dbNumRows($res);
while ($row = phpAds_dbFetchArray($res))
{
	$affiliates[$row['affiliateid']] = $row;
	$affiliates[$row['affiliateid']]['ZoneBanners'] = 0;
	$affiliates[$row['affiliateid']]['ZoneCampaigns'] = 0;
}


if ($banner['storagetype'] == 'txt')
{
	// Get banner zones
	if (phpAds_isUser(phpAds_Admin))
	{
		$query=" SELECT zoneid,affiliateid,zonename,description,width,height,what,zonetype,delivery".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE delivery=".phpAds_ZoneText.
			" AND zonetype = ".phpAds_ZoneBanners.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query=" SELECT zoneid,affiliateid,zonename,description,width,height,what,zonetype,delivery".
			" FROM ".$phpAds_config['tbl_zones'].
			",".$phpAds_config['tbl_affiliates'].
			" WHERE ".$phpAds_config['tbl_zones'].".affiliateid=".$phpAds_config['tbl_affiliates'].".affiliateid".
			" AND ".$phpAds_config['tbl_affiliates'].".agencyid=".phpAds_getUserID().
			" AND delivery=".phpAds_ZoneText.
			" AND zonetype = ".phpAds_ZoneBanners.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$zone_count = phpAds_dbNumRows($res);
	while ($row = phpAds_dbFetchArray($res))
	{
		if (isset($affiliates[$row['affiliateid']]))
		{
			$row['linked'] = (phpAds_IsBannerInZone ($bannerid, $row['zoneid'], $row['what']));
			$affiliates[$row['affiliateid']]['zones'][$row['zoneid']] = $row;
			$affiliates[$row['affiliateid']]['ZoneBanners']++;
		}
	}
	
	
	// Get campaign zones
	if (phpAds_isUser(phpAds_Admin))
	{
		$query=" SELECT zoneid,affiliateid,zonename,description,width,height,what,zonetype,delivery".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE delivery=".phpAds_ZoneText.
			" AND zonetype = ".phpAds_ZoneCampaign.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query=" SELECT zoneid,affiliateid,zonename,description,width,height,what,zonetype,delivery".
			" FROM ".$phpAds_config['tbl_zones'].
			",".$phpAds_config['tbl_affiliates'].
			" WHERE ".$phpAds_config['tbl_zones'].".affiliateid=".$phpAds_config['tbl_affiliates'].".affiliateid".
			" AND ".$phpAds_config['tbl_affiliates'].".agencyid=".phpAds_getUserID().
			" AND delivery=".phpAds_ZoneText.
			" AND zonetype = ".phpAds_ZoneCampaign.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		if (isset($affiliates[$row['affiliateid']]))
		{
			if (phpAds_IsCampaignInZone ($campaignid, $row['zoneid'], $row['what']))
			{
				$zone_count++;
				
				$row['linked'] = true;
				$affiliates[$row['affiliateid']]['zones'][$row['zoneid']] = $row;
				$affiliates[$row['affiliateid']]['ZoneCampaigns']++;
			}
		}
	}
}
else
{
	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT".
			" z.zoneid as zoneid".
			",z.affiliateid as affiliateid".
			",z.zonename as zonename".
			",z.description as description".
			",z.width as width".
			",z.height as height".
			",z.what as what".
			",z.zonetype as zonetype".
			",z.delivery as delivery".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_banners']." AS b".
			" WHERE b.bannerid=".$bannerid.
			" AND (z.width=b.width OR z.width=-1)".
			" AND (z.height=b.height OR z.height=-1)".
			" AND z.zonetype=".phpAds_ZoneBanners.
			" AND z.delivery != ".phpAds_ZoneText.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT".
			" z.zoneid as zoneid".
			",z.affiliateid as affiliateid".
			",z.zonename as zonename".
			",z.description as description".
			",z.width as width".
			",z.height as height".
			",z.what as what".
			",z.zonetype as zonetype".
			",z.delivery as delivery".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_banners']." AS b".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE a.affiliateid=z.affiliateid".
			" AND a.agencyid=".phpAds_getUserID().
			" AND b.bannerid=".$bannerid.
			" AND (z.width=b.width OR z.width=-1)".
			" AND (z.height=b.height OR z.height=-1)".
			" AND z.zonetype=".phpAds_ZoneBanners.
			" AND z.delivery != ".phpAds_ZoneText.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}

	// Get banner zones
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	$zone_count = phpAds_dbNumRows($res);
	while ($row = phpAds_dbFetchArray($res))
	{
		if (isset($affiliates[$row['affiliateid']]))
		{
			$row['linked'] = (phpAds_IsBannerInZone ($bannerid, $row['zoneid'], $row['what']));
			$affiliates[$row['affiliateid']]['zones'][$row['zoneid']] = $row;
			$affiliates[$row['affiliateid']]['ZoneBanners']++;
		}
	}
	
	if (phpAds_isUser(phpAds_Admin))
	{
		$query="SELECT zoneid,affiliateid,zonename,description,width,height,what,zonetype,delivery".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE zonetype=".phpAds_ZoneCampaign.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query="SELECT z.zoneid,z.affiliateid,z.zonename,z.description,z.width,z.height,z.what,z.zonetype,z.delivery".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE z.affiliateid=a.affiliateid".
			" AND a.agencyid=".phpAds_getUserID().
			" AND z.zonetype=".phpAds_ZoneCampaign.
			phpAds_getZoneListOrder ($listorder, $orderdirection);
	}
	// Get campaign zones
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		if (isset($affiliates[$row['affiliateid']]))
		{
			if (phpAds_IsCampaignInZone ($campaignid, $row['zoneid'], $row['what']))
			{
				$zone_count++;
				
				$row['linked'] = true;
				$affiliates[$row['affiliateid']]['zones'][$row['zoneid']] = $row;
				$affiliates[$row['affiliateid']]['ZoneCampaigns']++;
			}
		}
	}
}

$tabindex = 1;


echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<form name='zones' action='banner-zone.php' method='post'>";
echo "<input type='hidden' name='clientid' value='".$clientid."'>";
echo "<input type='hidden' name='campaignid' value='".$campaignid."'>";
echo "<input type='hidden' name='bannerid' value='".$bannerid."'>";

echo "<tr height='25'>";
echo '<td height="25" width="40%"><b>&nbsp;&nbsp;<a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&listorder=name">'.$GLOBALS['strName'].'</a>';

if (($listorder == "name") || ($listorder == ""))
{
	if  (($orderdirection == "") || ($orderdirection == "down"))
	{
		echo ' <a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&orderdirection=up">';
		echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
	}
	else
	{
		echo ' <a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&orderdirection=down">';
		echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
	}
	echo '</a>';
}

echo '</b></td>';
echo '<td height="25"><b><a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&listorder=id">'.$GLOBALS['strID'].'</a>';

if ($listorder == "id")
{
	if  (($orderdirection == "") || ($orderdirection == "down"))
	{
		echo ' <a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&orderdirection=up">';
		echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
	}
	else
	{
		echo ' <a href="banner-zone.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid.'&orderdirection=down">';
		echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
	}
	echo '</a>';
}

echo '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo "<td height='25'><b>".$GLOBALS['strDescription']."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
echo "</tr>";

echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";


if ($zone_count > 0 && $affiliate_count > 0)
{
	$i=0;
	for (reset($affiliates); $akey = key($affiliates); next($affiliates))
	{
		$affiliate = $affiliates[$akey];
		
		if (isset($affiliate['zones']))
		{
			$zones 	   = $affiliate['zones'];
			
			if ($i > 0) echo "<td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td>";
			echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
			echo "<td height='25'>";
			
			$zoneslinked = 0;
			for (reset($zones); $zkey = key($zones); next($zones))
				if ($zones[$zkey]['linked']) $zoneslinked++;
			
			if ($affiliate['ZoneBanners'] > 0)
			{
				if (count($zones) == $zoneslinked)
					echo "&nbsp;&nbsp;<input name='affiliate[".$affiliate['affiliateid']."]' type='checkbox' value='t' checked ";
				else
					echo "&nbsp;&nbsp;<input name='affiliate[".$affiliate['affiliateid']."]' type='checkbox' value='t' ";
				
				echo "onClick='toggleZones(".$affiliate['affiliateid'].");' tabindex='".($tabindex++)."'>";
			}
			else
			{
				if (count($zones) == $zoneslinked)
					echo "&nbsp;&nbsp;<input name='' type='checkbox' value='t' checked disabled>";
				else
					echo "&nbsp;&nbsp;<input name='' type='checkbox' value='t' disabled>";
			}
			
			echo "&nbsp;&nbsp;<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;";
			echo "<a href='affiliate-edit.php?affiliateid=".$affiliate['affiliateid']."'>".$affiliate['name']."</a>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "</td>";
			
			// ID
			echo "<td height='25'>".$affiliate['affiliateid']."</td>";
			
			// Description
			echo "<td height='25'>&nbsp;</td>";
			echo "</tr>";
			
			
			for (reset($zones); $zkey = key($zones); next($zones))
			{
				$zone = $zones[$zkey];
				
				echo "<td ".($i%2==0?"bgcolor='#F6F6F6'":"")."><img src='images/spacer.gif' height=1'></td>";
				echo "<td colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>";
				echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
				
				echo "<td height='25'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				
			    if ($zone['zonetype'] == phpAds_ZoneBanners)
				{
					if ($zone['linked'])
						echo "&nbsp;&nbsp;<input name='includezone[".$zone['zoneid']."]' id='a".$affiliate['affiliateid']."' type='checkbox' value='t' checked ";
					else
						echo "&nbsp;&nbsp;<input name='includezone[".$zone['zoneid']."]' id='a".$affiliate['affiliateid']."' type='checkbox' value='t' ";
					
					echo "onClick='toggleAffiliate(".$affiliate['affiliateid'].");' tabindex='".($tabindex++)."'>&nbsp;&nbsp;";
				}
				else
				{
					if ($zone['linked'])
						echo "&nbsp;&nbsp;<input name='' id='' type='checkbox' value='t' checked disabled ";
					else
						echo "&nbsp;&nbsp;<input name='' id='' type='checkbox' value='t' disabled ";
					
					echo ">&nbsp;&nbsp;";
				}
				
				if ($zone['delivery'] == phpAds_ZoneBanner)
					echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;";
				elseif ($zone['delivery'] == phpAds_ZoneInterstitial)
					echo "<img src='images/icon-interstitial.gif' align='absmiddle'>&nbsp;";
				elseif ($zone['delivery'] == phpAds_ZonePopup)
					echo "<img src='images/icon-popup.gif' align='absmiddle'>&nbsp;";
				elseif ($zone['delivery'] == phpAds_ZoneText)
					echo "<img src='images/icon-textzone.gif' align='absmiddle'>&nbsp;";
				
				echo "<a href='zone-edit.php?affiliateid=".$affiliate['affiliateid']."&zoneid=".$zone['zoneid']."'>".$zone['zonename']."</a>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "</td>";
				
				// ID
				echo "<td height='25'>".$zone['zoneid']."</td>";
				
				// Description
				echo "<td height='25'>".stripslashes($zone['description'])."</td>";
				echo "</tr>";
			}
			
			$i++;
		}
	}
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
}
else
{
	echo "<tr height='25' bgcolor='#F6F6F6'>";
	echo "<td colspan='4'>";
	echo "&nbsp;&nbsp;".$strNoZonesToLink;
	echo "</td>";
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
}

echo "</table>";

if (isset($affiliates) && count($affiliates) > 0)
{
	echo "<br><br>";
	echo "<input type='submit' name='submit' value='$strSaveChanges' tabindex='".($tabindex++)."'>";
}

echo "</form>";



/*********************************************************/
/* Form requirements                                     */
/*********************************************************/

?>

<script language='Javascript'>
<!--
	affiliates = new Array();
<?php
	if (isset($affiliates) && is_array($affiliates) && count($affiliates))
		for (reset($affiliates); $akey = key($affiliates); next($affiliates))
			if (isset($affiliates[$akey]['zones']))
				echo "\taffiliates[".$akey."] = ".$affiliates[$akey]['ZoneBanners'].";\n";
?>
	
	function toggleAffiliate(affiliateid)
	{
		var count = 0;
		var affiliate;
		
		for (var i=0; i<document.zones.elements.length; i++)
		{
			if (document.zones.elements[i].name == 'affiliate[' + affiliateid + ']')
				affiliate = i;
			
			if (document.zones.elements[i].id == 'a' + affiliateid + '' &&
				document.zones.elements[i].checked)
				count++;
		}
		
		document.zones.elements[affiliate].checked = (count == affiliates[affiliateid]);
	}
	
	function toggleZones(affiliateid)
	{
		var checked
		
		for (var i=0; i<document.zones.elements.length; i++)
		{
			if (document.zones.elements[i].name == 'affiliate[' + affiliateid + ']')
				checked = document.zones.elements[i].checked;
			
			if (document.zones.elements[i].id == 'a' + affiliateid + '')
				document.zones.elements[i].checked = checked;
		}
	}

//-->
</script>


<?php



/*********************************************************/
/* Store preferences                                     */
/*********************************************************/

$Session['prefs']['banner-zone.php']['listorder'] = $listorder;
$Session['prefs']['banner-zone.php']['orderdirection'] = $orderdirection;

phpAds_SessionDataStore();



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>