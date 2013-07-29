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
$Id: zone-probability.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Affiliate);

/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Affiliate))
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
		$affiliateid = $row["affiliateid"];
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT z.zoneid as zoneid".
		" FROM ".$phpAds_config['tbl_affiliates']." AS a".
		",".$phpAds_config['tbl_zones']." AS z".
		" WHERE z.affiliateid=".$affiliateid.
		" AND z.zoneid=".$zoneid.
		" AND z.affiliateid=a.affiliateid".
		" AND a.agencyid=".phpAds_getUserID();
	
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['affiliate-zones.php']['listorder']))
	$navorder = $Session['prefs']['affiliate-zones.php']['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['affiliate-zones.php']['orderdirection']))
	$navdirection = $Session['prefs']['affiliate-zones.php']['orderdirection'];
else
	$navdirection = '';


// Get other zones
$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_zones']."
	WHERE
		affiliateid = '".$affiliateid."'
		".phpAds_getZoneListOrder ($navorder, $navdirection)."
");

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildZoneName ($row['zoneid'], $row['zonename']),
		"zone-probability.php?affiliateid=".$affiliateid."&zoneid=".$row['zoneid'],
		$zoneid == $row['zoneid']
	);
}

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	phpAds_PageShortcut($strAffiliateProperties, 'affiliate-edit.php?affiliateid='.$affiliateid, 'images/icon-affiliate.gif');
	phpAds_PageShortcut($strZoneHistory, 'stats-zone-history.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-statistics.gif');
	
	
	$extra  = "<form action='zone-modify.php'>";
	$extra .= "<input type='hidden' name='zoneid' value='$zoneid'>";
	$extra .= "<input type='hidden' name='affiliateid' value='$affiliateid'>";
	$extra .= "<input type='hidden' name='returnurl' value='zone-probability.php'>";
	$extra .= "<br><br>";
	$extra .= "<b>$strModifyZone</b><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-duplicate-zone.gif' align='absmiddle'>&nbsp;<a href='zone-modify.php?affiliateid=".$affiliateid."&zoneid=".$zoneid."&duplicate=true&returnurl=zone-probability.php'>$strDuplicate</a><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-move-zone.gif' align='absmiddle'>&nbsp;$strMoveTo<br>";
	$extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
	$extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$extra .= "<select name='moveto' style='width: 110;'>";

	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid != ".$affiliateid;
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT affiliateid,name".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid != ".$affiliateid.
			" AND agencyid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
		$extra .= "<option value='".$row['affiliateid']."'>".phpAds_buildAffiliateName($row['affiliateid'], $row['name'])."</option>";
	
	$extra .= "</select>&nbsp;<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='zone-delete.php?affiliateid=$affiliateid&zoneid=$zoneid&returnurl=affiliate-zones.php'".phpAds_DelConfirm($strConfirmDeleteZone).">$strDelete</a><br>";
	$extra .= "</form>";
	
	
	phpAds_PageHeader("4.2.3.4", $extra);
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("4.2.3.2", "4.2.3.6", "4.2.3.3", "4.2.3.4", "4.2.3.5"));
}
else
{
	if (phpAds_isAllowed(phpAds_EditZone)) $sections[] = "2.1.2";
	if (phpAds_isAllowed(phpAds_EditZone)) $sections[] = "2.1.6";
	if (phpAds_isAllowed(phpAds_LinkBanners)) $sections[] = "2.1.3";
	$sections[] = "2.1.4";
	$sections[] = "2.1.5";
		
	phpAds_PageHeader("2.1.4");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections($sections);
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

function phpAds_showZoneBanners ($zoneid)
{
	global $phpAds_config, $phpAds_TextDirection;
	global $strUntitled, $strName, $strID, $strWeight, $strShowBanner;
	global $strCampaignWeight, $strBannerWeight, $strProbability, $phpAds_TextAlignRight;
	global $strRawQueryString, $strZoneProbListChain, $strZoneProbNullPri, $strZoneProbListChainLoop;
	
	$zonechain = array();
	$what = '';
	$infinite_loop = false;
	
	while ($zoneid || $what)
	{
		if ($zoneid)
		{
			// Get zone
			$zoneres = phpAds_dbQuery("SELECT what,width,height,delivery FROM ".$phpAds_config['tbl_zones']." WHERE zoneid='$zoneid' ");

			$precondition = '';
			
			if (phpAds_dbNumRows($zoneres) > 0)
			{
				$zone = phpAds_dbFetchArray($zoneres);
				
				// Set what parameter to zone settings
				if (isset($zone['what']) && $zone['what'] != '')
					$what = $zone['what'];
				else {
					$what = 'default';
					$precondition .= " AND b.keyword LIKE '%default%' ";
				}
			}
			else
				$what = '';
			
			// Size preconditions
			if ($zone['width'] > -1)
				$precondition .= " AND b.width = ".$zone['width']." ";
			
			if ($zone['height'] > -1)
				$precondition .= " AND b.height = ".$zone['height']." ";
							
			// Text Ads preconditions
			if ($zone['delivery'] == phpAds_ZoneText)
				$precondition .= " AND b.storagetype = 'txt' ";
			else
				$precondition .= " AND b.storagetype <> 'txt' ";
			
			
			if (!defined('LIBVIEWQUERY_INCLUDED'))  include (phpAds_path.'/libraries/lib-view-query.inc.php');
			$select = phpAds_buildQuery ($what, false, $precondition);
		}
		else
		{
			// Direct selection
			if (!defined('LIBVIEWQUERY_INCLUDED'))  include (phpAds_path.'/libraries/lib-view-query.inc.php');
			$select = phpAds_buildQuery ($what, false, '');
			$zone = array('what' => $what);
		}
		
		
		$res = phpAds_dbQuery($select);
		
		$rows = array();
		$prioritysum = 0;
		while ($tmprow = phpAds_dbFetchArray($res))
		{
			// weight of 0 disables the banner
			if ($tmprow['priority'])
			{
				$prioritysum += $tmprow['priority'];
				$rows[$tmprow['bannerid']] = $tmprow; 
			}
		}
		
		if (!count($rows) && isset($zone['chain']) && strlen($zone['chain']))
		{
			// Follow the chain if no banner was found
			
			if (ereg('^zone:([0-9]+)$', $zone['chain'], $match))
			{
				// Zone chain
				$zoneid = $match[1];
				$what = '';
			}
			else
			{
				// Raw querystring chain
				$zoneid = 0;
				$what = $zone['chain'];
			}
			
			if (in_array($zone, $zonechain))
			{
				// Chain already evaluated, exit
				$zoneid = 0;
				$what = '';
				$infinite_loop = true;
			}
			else
				$zonechain[] = $zone;
		}
		else
		{
			// No chain settings, exit loop
			$zoneid = 0;
			$what = '';
		}
	}
	
	if (isset($rows) && is_array($rows))
	{
		$i=0;
		
		if (count($zonechain))
		{
			// Zone Chain
			echo "<br><br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>";
			echo $infinite_loop ? $strZoneProbListChainLoop : $strZoneProbListChain;
			echo "<br><img src='images/break-el.gif' height='1' width='100%' vspace='6'><br>";
			// echo "</div>";
			
			/*
			echo "<br><br><table width='100% border='0' align='center' cellspacing='0' cellpadding='0'>";
			echo "<tr><td valign='top'><img src='images/info.gif' align='absmiddle'>&nbsp;</td>";
			echo "<td width='100%'><b>".($infinite_loop ? $strZoneProbListChainLoop : $strZoneProbListChain)."</b></td></tr>";
			echo "</table>";
			phpAds_ShowBreak();
			*/
			
			while (list(, $z) = each($zonechain))
			{
				echo "<nobr><img src='images/icon-zone-d.gif' align='absmiddle'>&nbsp;".phpAds_buildZoneName($z['zoneid'], $z['zonename']);
				echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'></nobr> ";
			}
			
			if (isset($zone['zoneid']))
				echo "<nobr><img src='images/".($infinite_loop ? 'errormessage.gif' : 'icon-zone.gif')."' align='absmiddle'>&nbsp;<b>".phpAds_buildZoneName($zone['zoneid'], $zone['zonename'])."</b></nobr><br>";
			else
				echo "<nobr><img src='images/icon-generatecode.gif' align='absmiddle'>&nbsp;<b>".$GLOBALS['strRawQueryString'].":</b> ".htmlentities($zone['what'])."</nobr><br>";
			
			echo "</div>";
		}
		
		
		// Header
		echo "<br><br>";
		echo "<table width='100%' border='0' align='center' cellspacing='0' cellpadding='0'>";
		echo "<tr height='25'>";
		echo "<td height='25' width='40%'><b>&nbsp;&nbsp;".$strName."</b></td>";
		echo "<td height='25'><b>".$strID."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>";
		echo "<td height='25'><b>".$strProbability."</b></td>";
		echo "<td height='25' align='".$phpAds_TextAlignRight."'>&nbsp;</td>";
		echo "</tr>";
		
		echo "<tr height='1'><td colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		
		// Banners
		for (reset($rows);$key=key($rows);next($rows))
		{
			$name = phpAds_getBannerName ($rows[$key]['bannerid'], 60, false);
			
			if ($i > 0) echo "<tr height='1'><td colspan='4' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
			
			echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
			
			echo "<td height='25'>";
			echo "&nbsp;&nbsp;";
			
			// Banner icon
			if ($rows[$key]['storagetype'] == 'html')
				echo "<img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;";
			elseif ($rows[$key]['storagetype'] == 'txt')
				echo "<img src='images/icon-banner-text.gif' align='absmiddle'>&nbsp;";
			elseif ($rows[$key]['storagetype'] == 'url')
				echo "<img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;";
			else
				echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
			
			// Name
			if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
				echo "<a href='banner-edit.php?clientid=".phpAds_getCampaignParentClientID($rows[$key]['campaignid'])."&campaignid=".$rows[$key]['campaignid']."&bannerid=".$rows[$key]['bannerid']."'>".$name."</a>";
			else
				echo $name;
			
			echo "</td>";
			
			echo "<td height='25'>".$rows[$key]['bannerid']."</td>";
			echo "<td height='25'>".number_format($rows[$key]['priority'] / $prioritysum * 100, $phpAds_config['percentage_decimals'])."%</td>";
			
			
			// Show banner
			if ($rows[$key]['storagetype'] == 'txt')
			{
				$width	= 300;
				$height = 200;
			}
			else
			{
				$width  = $rows[$key]['width'] + 64;
				$height = $rows[$key]['bannertext'] ? $rows[$key]['height'] + 90 : $rows[$key]['height'] + 64;
			}
			
			echo "<td height='25' align='".$phpAds_TextAlignRight."'>";
			echo "<a href='banner-htmlpreview.php?bannerid=".$rows[$key]['bannerid']."' target='_new' ";
			echo "onClick=\"return openWindow('banner-htmlpreview.php?bannerid=".$rows[$key]['bannerid']."', '', 'status=no,scrollbars=no,resizable=no,width=".$width.",height=".$height."');\">";
			echo "<img src='images/icon-zoom.gif' align='absmiddle' border='0'>&nbsp;".$strShowBanner."</a>&nbsp;&nbsp;";
			echo "</td>";
			
			echo "</tr>";
			$i++;
		}
		
		if (!$i)
		{
			echo "<tr height='25' bgcolor='#F6F6F6'>";
			echo "<td colspan='4'>&nbsp;&nbsp;".$strZoneProbNullPri."</td>";
			echo "</tr>";
		}
		
		// Footer
		echo "<tr height='1'><td colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
		echo "</table>";
	}
}





/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($zoneid) && $zoneid != '')
{
	phpAds_showZoneBanners($zoneid);
	echo "<br><br>";
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>