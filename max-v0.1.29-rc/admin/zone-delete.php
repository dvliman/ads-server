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
$Id: zone-delete.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-zones.inc.php");


// Register input variables
phpAds_registerGlobal ('returnurl');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Affiliate);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($zoneid) && $zoneid != '')
{
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
		
		if ($row["affiliateid"] == '' || phpAds_getUserID() != $row["affiliateid"] || !phpAds_isAllowed(phpAds_DeleteZone))
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
		$query = "SELECT z.affiliateid AS affiliateid".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE z.affiliateid = a.affiliateid".
			" AND a.agencyid=".phpAds_getUserID();
	
		$res = phpAds_dbQuery($query) or phpAds_sqlDie();
		if (phpAds_dbNumRows($res) == 0)
		{
			phpAds_PageHeader("2");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
	
	
	// Reset append codes which called this zone
	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT zoneid,append".
			" FROM ".$phpAds_config['tbl_zones'].
			" WHERE appendtype=".phpAds_ZoneAppendZone;
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT z.zoneid AS zoneid".
			",z.append AS append".
			" FROM ".$phpAds_config['tbl_zones']." AS z".
			",".$phpAds_config['tbl_affiliates']." AS a".
			" WHERE z.affiliateid = a.affiliateid".
			" AND a.agencyid=".phpAds_getUserID().
			" AND appendtype=".phpAds_ZoneAppendZone;
	}
	
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
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
						zoneid =".$row['zoneid']."
				");
		}
	}
	
	
	// Delete zone
	$res = phpAds_dbQuery("
		DELETE FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			zoneid=$zoneid
		") or phpAds_sqlDie();
	
	// Rebuild priorities
	include_once('lib-instant-update.inc.php');
    instant_update($zoneid);
}

if (!isset($returnurl) && $returnurl == '')
	$returnurl = 'affiliate-zones.php';

Header("Location: ".$returnurl."?affiliateid=$affiliateid");

?>