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
$Id: banner-append.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-invocation.inc.php");
require ("lib-size.inc.php");
require ("lib-append.inc.php");
require ("lib-banner.inc.php");


// Register input variables
phpAds_registerGlobal ('append', 'submitbutton');
phpAds_registerGlobal ('appendtype', 'appendid', 'appenddelivery', 'appendsave');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (phpAds_isUser(phpAds_Agency) || phpAds_isUser(phpAds_Client))
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
		" AND ".$phpAds_config['tbl_clients'].".agencyid=".phpAds_getAgencyID();
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

if (isset($submitbutton))
{
    if (phpAds_isUser(phpAds_Client) && ! phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("1");
        phpAds_Die ($strAccessDenied, $strNotAdmin);    
    }
    
	if (isset($bannerid) && $bannerid != '')
	{
		// Do not save append until not finished with appending, if present
		if (isset($appendsave) && $appendsave)
		{
			// Determine append type
			if (!isset($append)) $append = '';
			if (!isset($appendtype)) $appendtype = phpAds_ZoneAppendZone;
			if (!isset($appenddelivery)) $appenddelivery = phpAds_ZonePopup;
			
			
			// Generate invocation code
			if ($appendtype == phpAds_ZoneAppendZone)
			{
				$what = 'zone:'.(isset($appendid) ? $appendid : 0);
				
				if ($appenddelivery == phpAds_ZonePopup)
					$codetype = 'popup';
				else
				{
					$codetype = 'adlayer';
					if (!isset($layerstyle)) $layerstyle = 'geocities';
					include ('../libraries/layerstyles/'.$layerstyle.'/invocation.inc.php');
				}
				
				$append = addslashes(phpAds_GenerateInvocationCode());
			}
			
			
			// Update banner
			$sqlupdate = array();
			$sqlupdate[] = "append='".$append."'";
			$sqlupdate[] = "appendtype='".$appendtype."'";
			
            if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ActivateBanner)) {
                $sqlupdate[] = "active = 'f' ";
            }
			
			$res = phpAds_dbQuery("
				UPDATE
					".$phpAds_config['tbl_banners']."
				SET
					".join(', ', $sqlupdate)."
				WHERE
					bannerid='".$bannerid."'
			") or phpAds_sqlDie();
		}
		
		
		
		// Rebuild Banner cache
		phpAds_rebuildBannerCache($bannerid);
		
		
		// Rebuild Cache
		if (!defined('LIBVIEWCACHE_INCLUDED'))  include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
		
		phpAds_cacheDelete();
		
		
		
		// Do not redirect until not finished with zone appending, if present
		if (!isset($appendsave) || $appendsave)
		{
			if (phpAds_isUser(phpAds_Client)) { $nextPage = "Location: banner-append.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid; }
			else { $nextPage = "Location: banner-zone.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid; }
			
		    header ($nextPage);
			exit;
		}
	}
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

if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
    $input_disable = ' DISABLED ';
}

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
		"banner-append.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row['bannerid'],
		$bannerid == $row['bannerid']
	);
}

if (!phpAds_isUser(phpAds_Client)) {
    phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
    phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
}
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
		" WHERE campaignid!=".$campaignid;
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT m.campaignid AS campaignid".
		",m.campaignname AS campaignname".
		" FROM ".$phpAds_config['tbl_campaigns']." AS m".
		",".$phpAds_config['tbl_clients']." AS c".
		" WHERE m.clientid=c.clientid".
		" AND m.campaignid!=".$campaignid.
		" AND c.agencyid=".phpAds_getAgencyID();
} elseif (phpAds_isUser(phpAds_Client)) {
	$query = "SELECT m.campaignid AS campaignid".
    	",m.campaignname AS campaignname".
    	" FROM ".$phpAds_config['tbl_campaigns']." AS m".
    	" WHERE m.clientid=".phpAds_getUserID();
}
$res = phpAds_dbQuery($query)
	or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
	$extra .= "<option value='".$row['campaignid']."'>".phpAds_buildName($row['campaignid'], $row['campaignname'])."</option>";

$extra .= "</select>&nbsp;<input type='image' name='moveto' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
if (!phpAds_isUser(phpAds_Client)) $extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteBanner).">$strDelete</a><br>";
$extra .= "</form>";

if (phpAds_isUser(phpAds_Client)) {
    $sections = array ("2.1.1.1", "2.1.1.2", "2.1.1.3");
    if (phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("2.1.1.3", $extra);
    } else {
        phpAds_PageHeader("2.1.1.3");
    }
} else {
    $sections = array ("4.1.3.3.2", "4.1.3.3.3", "4.1.3.3.6", "4.1.3.3.4");
    phpAds_PageHeader("4.1.3.3.6", $extra);
}

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

$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_banners']."
	WHERE
		bannerid = '".$bannerid."'
") or phpAds_sqlDie();

if (phpAds_dbNumRows($res))
	$banner = phpAds_dbFetchArray($res);

$tabindex = 1;



if ($banner['storagetype'] != 'txt')
{
	// Header
	echo "<form name='appendform' method='post' action='banner-append.php' onSubmit='return phpAds_formSubmit() && phpAds_formCheck(this);'>";
	echo "<input type='hidden' name='clientid' value='".(isset($clientid) && $clientid != '' ? $clientid : '')."'>";
	echo "<input type='hidden' name='campaignid' value='".(isset($campaignid) && $campaignid != '' ? $campaignid : '')."'>";
	echo "<input type='hidden' name='bannerid' value='".(isset($bannerid) && $bannerid != '' ? $bannerid : '')."'>";
	
	echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0'>";
	echo "<tr><td height='25' colspan='3'><b>".$strAppendSettings."</b></td></tr>";
	echo "<tr height='1'><td width='30'><img src='images/break.gif' height='1' width='30'></td>";
	echo "<td width='200'><img src='images/break.gif' height='1' width='200'></td>";
	echo "<td width='100%'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	
	
	// Get available zones
	$available = array();
	
	
	// Get list of public publishers
	$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_affiliates']." WHERE publiczones = 't'");
	while ($row = phpAds_dbFetchArray($res)) 
		$available[] = "affiliateid = '".$row['affiliateid']."'";
	
	$available = implode ($available, ' OR ');
	
	
	// Get public zones
	$res = phpAds_dbQuery("SELECT zoneid, zonename, delivery FROM ".$phpAds_config['tbl_zones']." WHERE ".
						  "(delivery = ".phpAds_ZonePopup." OR delivery = ".phpAds_ZoneInterstitial.
						  ") AND (".$available.") ORDER BY zoneid");
	
	$available = array(phpAds_ZonePopup => array(), phpAds_ZoneInterstitial => array());
	while ($row = phpAds_dbFetchArray($res))
		$available[$row['delivery']][$row['zoneid']] = phpAds_buildZoneName($row['zoneid'], $row['zonename']);
	
	
	// Determine appendtype
	if (isset($appendtype)) $banner['appendtype'] = $appendtype;
	
	
	// Appendtype choices
	echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'>".$GLOBALS['strZoneAppendType']."</td><td>";
	echo "<select name='appendtype' style='width: 200;' onchange='phpAds_formSelectAppendType()' tabindex='".($tabindex++)."' $input_disable>";
	echo "<option value='".phpAds_ZoneAppendRaw."'".($banner['appendtype'] == phpAds_ZoneAppendRaw ? ' selected' : '').">".$GLOBALS['strZoneAppendHTMLCode']."</option>";
	
	if (count($available[phpAds_ZonePopup]) || count($available[phpAds_ZoneInterstitial]))
		echo "<option value='".phpAds_ZoneAppendZone."'".($banner['appendtype'] == phpAds_ZoneAppendZone ? ' selected' : '').">".$GLOBALS['strZoneAppendZoneSelection']."</option>";
	else
		$banner['appendtype'] = phpAds_ZoneAppendRaw;
	
	echo "</select></td></tr>";
	
	
	// Line
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	
	
	
	if ($banner['appendtype'] == phpAds_ZoneAppendZone)
	{
		// Append zones
		
		// Read info from invocation code
		if (!isset($appendid) || empty($appendid))
		{
			$appendvars = phpAds_ParseAppendCode($banner['append']);
			
			$appendid 		= $appendvars[0]['zoneid'];
			$appenddelivery = $appendvars[0]['delivery'];
			
			if ($appenddelivery == phpAds_ZonePopup && 
				!count($available[phpAds_ZonePopup]))
			{
				$appenddelivery = phpAds_ZoneInterstitial;
			}
			elseif ($appenddelivery == phpAds_ZoneInterstitial && 
					!count($available[phpAds_ZoneInterstitial]))
			{
				$appenddelivery = phpAds_ZonePopup;
			}
			else
			{
				// Add globals for lib-invocation
				while (list($k, $v) = each($appendvars[1]))
				{
					if ($k != 'n' && $k != 'what')
						$GLOBALS[$k] = addslashes($v);
				}
			}
		}
		
		
		
		// Header
		echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'>".$GLOBALS['strZoneAppendSelectZone']."</td><td>";
		echo "<input type='hidden' name='appendsave' value='1'>";
		echo "<input type='hidden' name='appendid' value='".$appendid."'>";
		echo "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		
		
		
		// Popup
		echo "<tr><td><input type='radio' name='appenddelivery' value='".phpAds_ZonePopup."'";
		echo (count($available[phpAds_ZonePopup]) ? ' onClick="phpAds_formSelectAppendDelivery(0)"' : ' DISABLED');
		echo ($appenddelivery == phpAds_ZonePopup ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;</td><td>";
		echo $GLOBALS['strPopup'].":</td></tr>";
		echo "<tr><td>&nbsp;</td><td width='100%'><img src='images/spacer.gif' height='1' width='100%' align='absmiddle' vspace='1'>";
		
		if (count($available[phpAds_ZonePopup]))
			echo "<img src='images/icon-popup.gif' align='top'>";
		else
			echo "<img src='images/icon-popup-d.gif' align='top'>";
		
		echo "&nbsp;&nbsp;<select name='appendpopup' style='width: 200;' ";
		echo "onchange='phpAds_formSelectAppendZone(0)'";
		echo (count($available[phpAds_ZonePopup]) ? '' : ' DISABLED')." tabindex='".($tabindex++)."'>";
		
		while (list($k, $v) = each($available[phpAds_ZonePopup]))
		{
			if ($appendid == $k)
				echo "<option value='".$k."' selected>".$v."</option>";
			else
				echo "<option value='".$k."'>".$v."</option>";
		}
		
		echo "</select></td></tr>";
		
		
		
		// Interstitial
		echo "<tr><td><input type='radio' name='appenddelivery' value='".phpAds_ZoneInterstitial."'";
		echo (count($available[phpAds_ZoneInterstitial]) ? ' onClick="phpAds_formSelectAppendDelivery(1)"' : ' DISABLED');
		echo ($appenddelivery == phpAds_ZoneInterstitial ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;</td><td>";
		echo $GLOBALS['strInterstitial'].":</td></tr>";
		echo "<tr><td>&nbsp;</td><td width='100%'><img src='images/spacer.gif' height='1' width='100%' align='absmiddle' vspace='1'>";
		
		if (count($available[phpAds_ZoneInterstitial]))
			echo "<img src='images/icon-interstitial.gif' align='top'>";
		else
			echo "<img src='images/icon-interstitial-d.gif' align='top'>";
		
		echo "&nbsp;&nbsp;<select name='appendinterstitial' style='width: 200;' ";
		echo "onchange='phpAds_formSelectAppendZone(1)'";
		echo (count($available[phpAds_ZoneInterstitial]) ? '' : ' DISABLED')." tabindex='".($tabindex++)."'>";
		
		while (list($k, $v) = each($available[phpAds_ZoneInterstitial]))
		{
			if ($appendid == $k)
				echo "<option value='".$k."' selected>".$v."</option>";
			else
				echo "<option value='".$k."'>".$v."</option>";
		}
		
		echo "</select></td></tr>";
		
		
		
		// Line
		echo "</table></td></tr><tr><td height='10' colspan='3'>&nbsp;</td></tr>";
		echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
		echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
		
		
		// It shouldn't be necessary to load zone attributes from db
		$extra = array('what' => '',
					   //'width' => $zone['width'],
					   //'height' => $zone['height'],
					   'delivery' => $appenddelivery,
					   //'website' => $affiliate['website'],
					   'zoneadvanced' => true
		);
		
		
		// Invocation options
		$codetype = $appenddelivery == 'popup' ? 'popup' : 'adlayer';
		phpAds_placeInvocationForm($extra, true);
		
		echo "</td></tr>";
	}
	else
	{
		// Regular HTML append
		echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'>".$strZoneAppend."</td><td>";
		echo "<input type='hidden' name='appendsave' value='1'>";
		echo "<textarea name='append' rows='6' cols='55' style='width: 100%;' tabindex='".($tabindex++)."' $input_disable>".htmlspecialchars($banner['append'])."</textarea>";
		echo "</td></tr>";
	}
	
	
	// Footer
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	echo "</table><br><br>";
	if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
    	if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ActivateBanner) && ($banner['active'] == 't')) {
            $warn_deactivate = phpAds_DelConfirm($strConfirmDeactivate);
        } else { $warn_deactivate = ''; }
       echo "<input type='submit' name='submitbutton' value='".$strSaveChanges."' tabindex='".($tabindex++)."' $warn_deactivate>";
	}
	echo "</form>";
}
else
{
	echo "<br><br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>";
	echo $strAppendTextAdNotPossible;
	echo "</div>";
}



/*********************************************************/
/* Form requirements                                     */
/*********************************************************/

?>

<script language='JavaScript'>
<!--

	function phpAds_formSelectAppendType()
	{
		if (document.appendform.appendid)
			document.appendform.appendid.value = '-1';
		document.appendform.appendsave.value = '0';
		document.appendform.submit();
	}

	function phpAds_formSelectAppendDelivery(type)
	{
		document.appendform.appendid.value = '-1';
		document.appendform.appendsave.value = '0';
		document.appendform.submit();
	}
	

	function phpAds_formSelectAppendZone(type)
	{
		var x;

		if (document.appendform.appenddelivery[type] && 
			!document.appendform.appenddelivery[type].checked)
		{
			document.appendform.appendid.value = '-1';
			document.appendform.appendsave.value = '0';
			document.appendform.submit();
		}
	}

	function phpAds_formSubmit()
	{
		if (document.appendform.appenddelivery)
		{
			if (document.appendform.appenddelivery[0].checked)
				x = document.appendform.appendpopup;
			else
				x = document.appendform.appendinterstitial;
			
			document.appendform.appendid.value = x.options[x.selectedIndex].value;
		}

		return true;
	}

//-->
</script>

<?php



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>