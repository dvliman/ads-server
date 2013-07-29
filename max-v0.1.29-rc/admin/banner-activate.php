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
$Id: banner-activate.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");
require ("../libraries/lib-priority.inc.php");


// Register input variables
phpAds_registerGlobal ('value');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

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
/* Main code                                             */
/*********************************************************/

if ($value == "t")
	$value = "f";
else
	$value = "t";

if (phpAds_isUser(phpAds_Client))
{
	if (($value == 'f' && phpAds_isAllowed(phpAds_DisableBanner)) || 
	    ($value == 't' && phpAds_isAllowed(phpAds_ActivateBanner)))
	{
		$result = phpAds_dbQuery("
			SELECT
				campaignid
			FROM
				".$phpAds_config['tbl_banners']."
			WHERE
				bannerid = '$bannerid'
			") or phpAds_sqlDie();
		$row = phpAds_dbFetchArray($result);
		
		if ($row["campaignid"] == '' || phpAds_getUserID() != phpAds_getCampaignParentClientID ($row["campaignid"]))
		{
			phpAds_PageHeader("1");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
		else
		{
			$campaignid = $row["campaignid"];
			
			$res = phpAds_dbQuery("
				UPDATE
					".$phpAds_config['tbl_banners']."
				SET
					active = '$value'
				WHERE
					bannerid = '$bannerid'
				") or phpAds_sqlDie();
			
            include_once('lib-instant-update.inc.php');
            instant_update("bannerid:".$bannerid);			
			
			Header("Location: campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid);
		}
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	if (isset($bannerid) && $bannerid != '')
	{
		$res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_banners']."
			SET
				active = '$value'
			WHERE
				bannerid = '$bannerid'
		") or phpAds_sqlDie();
	}
	elseif (isset($campaignid) && $campaignid != '')
	{
		$res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_banners']."
			SET
				active = '$value'
			WHERE
				campaignid = '$campaignid'
		") or phpAds_sqlDie();
	}
	
    // Rebuild priorities
    include_once('lib-instant-update.inc.php');
    instant_update("bannerid:".$bannerid."|campaignid:".$campaignid);
	
	Header("Location: campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid);
}


?>