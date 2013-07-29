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
$Id: affiliate-invocation.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-invocation.inc.php");
require ("lib-zones.inc.php");


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);



/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Agency)) {
    
	$result = phpAds_dbQuery("
        SELECT
	       affiliateid
        FROM 
	       ".$phpAds_config['tbl_affiliates']."
		WHERE 
		     agencyid=".phpAds_getUserID()) or phpAds_sqlDie();
	
	
	if (phpAds_dbNumRows($result) == 0) {
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


// Get other affiliates
if (phpAds_isUser(phpAds_Admin))
{
	$query="SELECT * FROM {$phpAds_config['tbl_affiliates']}" . phpAds_getAffiliateListOrder($navorder, $navdirection);
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query="SELECT * FROM {$phpAds_config['tbl_affiliates']} WHERE agencyid=$agencyid" . phpAds_getAffiliateListOrder($navorder, $navdirection);
}
elseif (phpAds_isUser(phpAds_Affiliate))
{
	$query="SELECT * FROM {$phpAds_config['tbl_affiliates']} WHERE affiliateid=$affiliateid" . phpAds_getAffiliateListOrder($navorder, $navdirection);
}
$res = phpAds_dbQuery($query)
	or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildAffiliateName ($row['affiliateid'], $row['name']),
		"affiliate-invocation.php?affiliateid=".$row['affiliateid'],
		$affiliateid == $row['affiliateid']
	);
}

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	phpAds_PageShortcut($strAffiliateProperties, 'affiliate-edit.php?affiliateid='.$affiliateid, 'images/icon-affiliate.gif');
	phpAds_PageShortcut($strZoneHistory, 'stats-zone-history.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-statistics.gif');
	
	phpAds_PageHeader("4.2.4");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br><br><br>";
		phpAds_ShowSections(array("4.2.2", "4.2.3","4.2.4"));
}
else
{
	if (phpAds_isAllowed(phpAds_EditZone)) $sections[] = "2.1.2";
	if (phpAds_isAllowed(phpAds_EditZone)) $sections[] = "2.1.6";
	if (phpAds_isAllowed(phpAds_LinkBanners)) $sections[] = "2.1.3";
	$sections[] = "2.1.4";
	$sections[] = "2.1.5";
		
	phpAds_PageHeader("2.1.5");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections($sections);
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

// hardcode invocation type
$codetype = "publisherJs";

echo "<table border='0' width='550' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25'><img src='images/icon-generatecode.gif' align='absmiddle'>&nbsp;<b>".$GLOBALS['strBannercode']."</b></td>";

// Show clipboard button only on IE
if (strpos ($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE') > 0 &&
strpos ($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'Opera') < 1)
{
    echo "<td height='25' align='right'><img src='images/icon-clipboard.gif' align='absmiddle'>&nbsp;";
    echo "<a href='javascript:phpAds_CopyClipboard(\"bannercode\");'>".$GLOBALS['strCopyToClipboard']."</a></td></tr>";
}
else
echo "<td>&nbsp;</td>";

echo "<tr height='1'><td colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "<tr><td colspan='2'><textarea name='bannercode' class='code-gray' rows='30' cols='80' style='width:800;' readonly>".htmlspecialchars(phpAds_GenerateInvocationCode())."</textarea></td></tr>";
echo "</table><br>";
phpAds_ShowBreak();
echo "<br>";

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();


?>