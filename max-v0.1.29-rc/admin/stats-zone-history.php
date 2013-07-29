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
$Id: stats-zone-history.php 3145 2005-05-20 13:15:01Z andrew $
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
	$query = "SELECT affiliateid".
		" FROM ".$phpAds_config['tbl_zones'].
		" WHERE zoneid=".$zoneid.
		" AND affiliateid=".phpAds_getUserID();
			
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT a.affiliateid AS affiliateid".
		" FROM ".$phpAds_config['tbl_zones']." as z".
		",".$phpAds_config['tbl_affiliates']." as a".
		//" WHERE a.zoneid=z.zoneid".
		" WHERE a.affiliateid=z.affiliateid".		
		" AND z.zoneid=".$zoneid.
		" AND a.agencyid=".phpAds_getUserID();
			
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res) == 0)
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
	$query = "SELECT zoneid,zonename".
		" FROM ".$phpAds_config['tbl_zones'].
		" WHERE affiliateid=".$affiliateid;

	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_buildZoneName ($row['zoneid'], $row['zonename']),
			"stats-zone-history.php?affiliateid=".$affiliateid."&zoneid=".$row['zoneid'],
			$zoneid == $row['zoneid']
		);
	}
	
	phpAds_PageShortcut($strAffiliateProperties, 'affiliate-edit.php?affiliateid='.$affiliateid, 'images/icon-affiliate.gif');	
	phpAds_PageShortcut($strZoneProperties, 'zone-edit.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone.gif');	
	phpAds_PageShortcut($strIncludedBanners, 'zone-include.php?affiliateid='.$affiliateid.'&zoneid='.$zoneid, 'images/icon-zone-linked.gif');	
	
	
	phpAds_PageHeader("2.4.2.1");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("2.4.2.1","2.4.2.2"));
}
else
{
	phpAds_PageHeader("1.1.1");
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("1.1.1"));
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_history_where     = $phpAds_config['tbl_adstats'].".zoneid = ".$zoneid." ";
$lib_history_params    = array ('affiliateid' => $affiliateid,
								'zoneid' => $zoneid
						 );
$lib_history_hourlyurl = "stats-zone-daily.php";

include ("lib-history.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>