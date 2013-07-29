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
$Id: stats-reset.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Register input variables
phpAds_registerGlobal ('all');


// Security check
phpAds_checkAccess(phpAds_Admin);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Banner
if (isset($bannerid) && $bannerid != '')
{
    // Delete stats for this banner
	phpAds_deleteStatsByBannerID($bannerid);
	
	// Return to campaign statistics
	Header("Location: stats-campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid);
}


// Campaign
elseif (isset($campaignid) && $campaignid != '')
{
	// Get all banners for this client
	$idresult = phpAds_dbQuery(" SELECT
								bannerid
							  FROM
							  	".$phpAds_config['tbl_banners']."
							  WHERE
								campaignid = '$campaignid'
		  				 ");
	
	// Loop to all banners for this client
	while ($row = phpAds_dbFetchArray($idresult))
	{
		// Delete stats for the banner
		phpAds_deleteStatsByBannerID($row['bannerid']);
	}
	
	// Return to campaign statistics
	Header("Location: stats-advertiser-campaigns.php?clientid=".$clientid);
}


// Client
elseif (isset($clientid) && $clientid != '')
{
	// Get all banners for this client
	$idresult = phpAds_dbQuery("
		SELECT
			b.bannerid
		FROM
			".$phpAds_config['tbl_banners']." AS b,
			".$phpAds_config['tbl_campaigns']." AS c
		WHERE
			c.clientid = $clientid AND
			c.campaignid = b.campaignid
	");
	
	// Loop to all banners for this client
	while ($row = phpAds_dbFetchArray($idresult))
	{
		// Delete stats for the banner
		phpAds_deleteStatsByBannerID($row['bannerid']);
	}
	
	// Return to campaign statistics
	Header("Location: stats-global-advertiser.php");
}


// All
elseif (isset($all) && $all == 'tr'.'ue')
{
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adviews']) or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adclicks']) or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adstats']) or phpAds_sqlDie();
	
	// Return to campaign statistics
	Header("Location: stats-global-advertiser.php");
}
?>