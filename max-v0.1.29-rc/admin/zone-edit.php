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
$Id: zone-edit.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");
require ("lib-size.inc.php");


// Register input variables
phpAds_registerGlobal ('zonename', 'description', 'delivery', 'sizetype', 'size', 'width', 'height', 'submit');


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
		
		if ($row["affiliateid"] == '' || phpAds_getUserID() != $row["affiliateid"] || !phpAds_isAllowed(phpAds_EditZone))
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
		if (phpAds_isAllowed(phpAds_AddZone))
		{
			$affiliateid = phpAds_getUserID();
		}
		else
		{
			phpAds_PageHeader("1");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	if (isset($zoneid) && ($zoneid != ''))
	{
		$query = "SELECT z.zoneid as zoneid".
			" FROM ".$phpAds_config['tbl_affiliates']." AS a".
			",".$phpAds_config['tbl_zones']." AS z".
			" WHERE z.affiliateid=".$affiliateid.
			" AND z.zoneid=".$zoneid.
			" AND z.affiliateid=a.affiliateid".
			" AND a.agencyid=".phpAds_getUserID();
	}
	else
	{
		$query = "SELECT affiliateid".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".$affiliateid.
			" AND agencyid=".phpAds_getUserID();
	}
	
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
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
	if (isset($description)) $description = addslashes ($description);
	
	if ($delivery == phpAds_ZoneText)
	{
		$width = 0;
		$height = 0;
	}
	else
	{
		if ($sizetype == 'custom')
		{
			if (isset($width) && $width == '*') $width = -1;
			if (isset($height) && $height == '*') $height = -1;
		}
		else
		{
			list ($width, $height) = explode ('x', $size);
		}
	}
	
	
	// Edit
	if (isset($zoneid) && $zoneid != '')
	{
		$res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_zones']."
			SET
				zonename='".$zonename."',
				description='".$description."',
				width='".$width."',
				height='".$height."',
				delivery='".$delivery."'
				".($delivery != phpAds_ZoneText && $delivery != phpAds_ZoneBanner ? ", append = ''" : "")."
				".($delivery != phpAds_ZoneText ? ", prepend = ''" : "")."
			WHERE
				zoneid=".$zoneid."
			") or phpAds_sqlDie();
		
		
		// Reset append codes which called this zone
		if (phpAds_isUser(phpAds_Admin))
		{
			$query = "SELECT zoneid,append".
				" FROM ".$phpAds_config['tbl_zones'].
				" WHERE appendtype=".phpAds_ZoneAppendZone;
		}
		elseif (phpAds_isUser(phpAds_Agency))
		{
			$query = "SELECT z.zoneid as zoneid,z.append as append".
				" FROM ".$phpAds_config['tbl_zones']." AS z".
				",".$phpAds_config['tbl_affiliates']." AS a".
				" WHERE z.affiliateid=a.affiliateid".
				" AND a.agencyid=".phpAds_getUserID().
				" AND z.appendtype=".phpAds_ZoneAppendZone;
		}
		elseif (phpAds_isUser(phpAds_Affiliate))
		{
			$query = "SELECT zoneid,append".
				" FROM ".$phpAds_config['tbl_zones'].
				" WHERE affiliateid=".phpAds_getUserID().
				" AND appendtype=".phpAds_ZoneAppendZone;
		}
		$res = phpAds_dbQuery($query);
		
		while ($row = phpAds_dbFetchArray($res))
		{
			$append = phpAds_ZoneParseAppendCode($row['append']);

			if ($append[0]['zoneid'] == $zoneid)
			{
				phpAds_dbQuery("
						UPDATE
							".$phpAds_config['tbl_zones']."
						SET
							appendtype = ".phpAds_ZoneAppendRaw.",
							append = ''
						WHERE
							zoneid=".$row['zoneid']."
					");
			}
		}
		
		// Rebuild priorities
	   include_once('lib-instant-update.inc.php');
        instant_update($bannerid);
    	  
		header ("Location: zone-advanced.php?affiliateid=".$affiliateid."&zoneid=".$zoneid);
		exit;
	}
	
	
	// Add
	else
	{
		$res = phpAds_dbQuery("
			INSERT INTO
				".$phpAds_config['tbl_zones']."
				(
				affiliateid,
				zonename,
				zonetype,
				description,
				width,
				height,
				delivery
				)
			 VALUES (
			 	'".$affiliateid."',
				'".$zonename."',
				'".phpAds_ZoneCampaign."',
				'".$description."',
				'".$width."',
				'".$height."',
				'".$delivery."'
				)
			") or phpAds_sqlDie();
		
		$zoneid = phpAds_dbInsertID();
		
		header ("Location: zone-advanced.php?affiliateid=".$affiliateid."&zoneid=".$zoneid);
		exit;
	}
}


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if ($zoneid != "")
{
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
			zoneid,zonename
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			affiliateid=".$affiliateid."
			".phpAds_getZoneListOrder ($navorder, $navdirection)."
	");
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_buildZoneName ($row['zoneid'], $row['zonename']),
			"zone-edit.php?affiliateid=".$affiliateid."&zoneid=".$row['zoneid'],
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
		$extra .= "<input type='hidden' name='returnurl' value='zone-edit.php'>";
		$extra .= "<br><br>";
		$extra .= "<b>$strModifyZone</b><br>";
		$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
		$extra .= "<img src='images/icon-duplicate-zone.gif' align='absmiddle'>&nbsp;<a href='zone-modify.php?affiliateid=".$affiliateid."&zoneid=".$zoneid."&duplicate=true&returnurl=zone-edit.php'>$strDuplicate</a><br>";
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
				" AND agencyid = ".phpAds_getUserID();
		}
		$res = phpAds_dbQuery($query)
			or phpAds_sqlDie();
		while ($row = phpAds_dbFetchArray($res))
			$extra .= "<option value='".$row['affiliateid']."'>".phpAds_buildAffiliateName($row['affiliateid'], $row['name'])."</option>";
		
		$extra .= "</select>&nbsp;<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
		$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
		$extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='zone-delete.php?affiliateid=$affiliateid&zoneid=$zoneid&returnurl=affiliate-zones.php'".phpAds_DelConfirm($strConfirmDeleteZone).">$strDelete</a><br>";
		$extra .= "</form>";
		
		
		phpAds_PageHeader("4.2.3.2", $extra);
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
			echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
			echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
			phpAds_ShowSections(array("4.2.3.2", "4.2.3.6", "4.2.3.3", "4.2.3.4", "4.2.3.5"));
	}
	else
	{
		$sections[] = "2.1.2";
		$sections[] = "2.1.6";
		if (phpAds_isAllowed(phpAds_LinkBanners)) $sections[] = "2.1.3";
		$sections[] = "2.1.4";
		$sections[] = "2.1.5";
		
		phpAds_PageHeader("2.1.2");
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
			echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
			echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
			phpAds_ShowSections($sections);
	}
}
else
{
	if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
	{
		phpAds_PageHeader("4.2.3.1");
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
			echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
			echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
			phpAds_ShowSections(array("4.2.3.1"));
	}
	else
	{
		phpAds_PageHeader("2.1.1");
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
			echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
			echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
			phpAds_ShowSections(array("2.1.1"));
	}
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($zoneid) && $zoneid != '')
{
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			zoneid=".$zoneid."
		") or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res))
	{
		$zone = phpAds_dbFetchArray($res);
	}
	
	if ($zone['width'] == -1) $zone['width'] = '*';
	if ($zone['height'] == -1) $zone['height'] = '*';
}
else
{
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_affiliates']."
		WHERE
			affiliateid=".$affiliateid."
	");
	
	if ($affiliate = phpAds_dbFetchArray($res))
		$zone["zonename"] = $affiliate['name'].' - ';
	else
		$zone["zonename"] = '';
	
	$zone['zonename'] 	   .= $strDefault;
	$zone['description'] 	= '';
	$zone['width'] 			= '468';
	$zone['height'] 		= '60';
	$zone['delivery']		= phpAds_ZoneBanner;
}

$tabindex = 1;


echo "<form name='zoneform' method='post' action='zone-edit.php' onSubmit='return phpAds_formCheck(this);'>";
echo "<input type='hidden' name='zoneid' value='".(isset($zoneid) && $zoneid != '' ? $zoneid : '')."'>";
echo "<input type='hidden' name='affiliateid' value='".(isset($affiliateid) && $affiliateid != '' ? $affiliateid : '')."'>";

echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25' colspan='3'><b>".$strBasicInformation."</b></td></tr>";
echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strName."</td><td>";
echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='zonename' size='35' style='width:350px;' value='".phpAds_htmlQuotes($zone['zonename'])."' tabindex='".($tabindex++)."'></td>";
echo "</tr><tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strDescription."</td><td>";
echo "<input class='flat' size='35' type='text' name='description' style='width:350px;' value='".phpAds_htmlQuotes($zone["description"])."' tabindex='".($tabindex++)."'></td>";
echo "</tr><tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'><br>".$strZoneType."</td><td><table>";
echo "<tr><td><input type='radio' name='delivery' value='".phpAds_ZoneBanner."'".($zone['delivery'] == phpAds_ZoneBanner ? ' CHECKED' : '')." onClick='phpAds_formEnableSize();' tabindex='".($tabindex++)."'>";
echo "&nbsp;<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;".$strBannerButtonRectangle."</td></tr>";

if ($phpAds_config['allow_invocation_interstitial'] || $zone['delivery'] == phpAds_ZoneInterstitial) 
{
	echo "<tr><td><input type='radio' name='delivery' value='".phpAds_ZoneInterstitial."'".($zone['delivery'] == phpAds_ZoneInterstitial ? ' CHECKED' : '')." onClick='phpAds_formEnableSize();' tabindex='".($tabindex++)."'>";
	echo "&nbsp;<img src='images/icon-interstitial.gif' align='absmiddle'>&nbsp;".$strInterstitial."</td></tr>";
}

if ($phpAds_config['allow_invocation_popup'] || $zone['delivery'] == phpAds_ZonePopup) 
{
	echo "<tr><td><input type='radio' name='delivery' value='".phpAds_ZonePopup."'".($zone['delivery'] == phpAds_ZonePopup ? ' CHECKED' : '')." onClick='phpAds_formEnableSize();' tabindex='".($tabindex++)."'>";
	echo "&nbsp;<img src='images/icon-popup.gif' align='absmiddle'>&nbsp;".$strPopup."</td></tr>";
}

echo "<tr><td><input type='radio' name='delivery' value='".phpAds_ZoneText."'".($zone['delivery'] == phpAds_ZoneText ? ' CHECKED' : '')." onClick='phpAds_formDisableSize();' tabindex='".($tabindex++)."'>";
echo "&nbsp;<img src='images/icon-textzone.gif' align='absmiddle'>&nbsp;".$strTextAdZone."</td></tr>";


echo "</table></td></tr>";


if ($zone['delivery'] == phpAds_ZoneText)
{
	$sizedisabled = ' disabled';
	$zone['width'] = '*';
	$zone['height'] = '*';
}
else
	$sizedisabled = '';

echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'><br>".$strSize."</td><td>";

$exists = phpAds_sizeExists ($zone['width'], $zone['height']);

echo "<table><tr><td>";
echo "<input type='radio' name='sizetype' value='default'".($exists ? ' CHECKED' : '').$sizedisabled." tabindex='".($tabindex++)."'>&nbsp;";
echo "<select name='size' onchange='phpAds_formSelectSize(this)'".$sizedisabled." tabindex='".($tabindex++)."'>"; 

for (reset($phpAds_IAB);$key=key($phpAds_IAB);next($phpAds_IAB))
{
	if ($phpAds_IAB[$key]['width'] == $zone['width'] &&
		$phpAds_IAB[$key]['height'] == $zone['height'])
		echo "<option value='".$phpAds_IAB[$key]['width']."x".$phpAds_IAB[$key]['height']."' selected>".$key."</option>";
	else
		echo "<option value='".$phpAds_IAB[$key]['width']."x".$phpAds_IAB[$key]['height']."'>".$key."</option>";
}

echo "<option value='-'".(!$exists ? ' SELECTED' : '').">Custom</option>";
echo "</select>";

echo "</td></tr><tr><td>";

echo "<input type='radio' name='sizetype' value='custom'".(!$exists ? ' CHECKED' : '').$sizedisabled." onclick='phpAds_formEditSize()' tabindex='".($tabindex++)."'>&nbsp;";
echo $strWidth.": <input class='flat' size='5' type='text' name='width' value='".(isset($zone["width"]) ? $zone["width"] : '')."'".$sizedisabled." onkeydown='phpAds_formEditSize()' onBlur='phpAds_formUpdate(this);' tabindex='".($tabindex++)."'>";
echo "&nbsp;&nbsp;&nbsp;";
echo $strHeight.": <input class='flat' size='5' type='text' name='height' value='".(isset($zone["height"]) ? $zone["height"] : '')."'".$sizedisabled." onkeydown='phpAds_formEditSize()' onBlur='phpAds_formUpdate(this);' tabindex='".($tabindex++)."'>";
echo "</td></tr></table>";
echo "</td></tr>";


echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "</table>";

echo "<br><br>";
echo "<input type='submit' name='submit' value='".(isset($zoneid) && $zoneid != '' ? $strSaveChanges : $strNext.' >')."' tabindex='".($tabindex++)."'>";
echo "</form>";



/*********************************************************/
/* Form requirements                                     */
/*********************************************************/

// Get unique affiliate
$unique_names = array();

$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_zones']." WHERE affiliateid = '".$affiliateid."' AND zoneid != '".$zoneid."'");
while ($row = phpAds_dbFetchArray($res))
	$unique_names[] = $row['zonename'];

?>

<script language='JavaScript'>
<!--
	phpAds_formSetRequirements('zonename', '<?php echo addslashes($strName); ?>', true, 'unique');
	phpAds_formSetRequirements('width', '<?php echo addslashes($strWidth); ?>', true, 'number*');
	phpAds_formSetRequirements('height', '<?php echo addslashes($strHeight); ?>', true, 'number*');
	
	phpAds_formSetUnique('zonename', '|<?php echo addslashes(implode('|', $unique_names)); ?>|');


	function phpAds_formSelectSize(o)
	{
		// Get size from select
		size   = o.options[o.selectedIndex].value;

		if (size != '-')
		{
			// Get width and height
			sarray = size.split('x');
			height = sarray.pop();
			width  = sarray.pop();
		
			// Set width and height
			document.zoneform.width.value = width;
			document.zoneform.height.value = height;
		
			// Set radio
			document.zoneform.sizetype[0].checked = true;
			document.zoneform.sizetype[1].checked = false;
		}
		else
		{
			document.zoneform.sizetype[0].checked = false;
			document.zoneform.sizetype[1].checked = true;
		}
	}
	
	function phpAds_formEditSize()
	{
		document.zoneform.sizetype[0].checked = false;
		document.zoneform.sizetype[1].checked = true;
		document.zoneform.size.selectedIndex = document.zoneform.size.options.length - 1;
	}
	
	function phpAds_formDisableSize()
	{
		document.zoneform.sizetype[0].disabled = true;
		document.zoneform.sizetype[1].disabled = true;
		document.zoneform.width.disabled = true;
		document.zoneform.height.disabled = true;
		document.zoneform.size.disabled = true;
	}

	function phpAds_formEnableSize()
	{
		document.zoneform.sizetype[0].disabled = false;
		document.zoneform.sizetype[1].disabled = false;
		document.zoneform.width.disabled = false;
		document.zoneform.height.disabled = false;
		document.zoneform.size.disabled = false;
	}
//-->
</script>

<?php



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>