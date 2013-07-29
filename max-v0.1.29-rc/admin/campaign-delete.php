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
$Id: campaign-delete.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Include required files
require ("config.php");
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");
require ("lib-statistics.inc.php");
require_once("../libraries/lib-priority.inc.php");

// Register input variables
phpAds_registerGlobal ('returnurl');

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

if (phpAds_isUser(phpAds_Agency)) {
    if (isset($campaignid) && $campaignid != '') {
        $query = "SELECT c.clientid".
        " FROM ".$phpAds_config['tbl_clients']." AS c".
        ",".$phpAds_config['tbl_campaigns']." AS m".
        " WHERE c.clientid=m.clientid".
        " AND c.clientid=".$clientid.
        " AND m.campaignid=".$campaignid.
        " AND agencyid=".phpAds_getUserID();
    } else {
        $query = "SELECT c.clientid".
        " FROM ".$phpAds_config['tbl_clients']." AS c".
        " WHERE c.clientid=".$clientid.
        " AND agencyid=".phpAds_getUserID();
    }
    $res = phpAds_dbQuery($query) or phpAds_sqlDie();
    if (phpAds_dbNumRows($res) == 0) {
        phpAds_PageHeader("2");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
}

/*********************************************************/
/* Restore cache of $node_array, if it exists            */
/*********************************************************/

if (isset($Session['prefs']['advertiser-index.php']['nodes'])) {
    $node_array = $Session['prefs']['advertiser-index.php']['nodes'];
}

/*********************************************************/
/* Main code                                             */
/*********************************************************/

function phpAds_DeleteCampaign($campaignid) {
    global $phpAds_config;
    
    // Delete Campaign
    $res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_campaigns'].
    " WHERE campaignid=".$campaignid
    ) or phpAds_sqlDie();
    
    // Delete Campaign/Tracker links
    $res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_campaigns_trackers'].
    " WHERE campaignid=".$campaignid
    ) or phpAds_sqlDie();
    
    // Delete Conversions Logged to this Campaign
    $res = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_conversionlog'].
    " WHERE campaignid=".$campaignid
    ) or phpAds_sqlDie();
    
    // Loop through each banner
    $res_banners = phpAds_dbQuery("
		SELECT
			bannerid,
			storagetype,
			filename
		FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			campaignid = '$campaignid'
	") or phpAds_sqlDie();
    
    while ($row = phpAds_dbFetchArray($res_banners)) {
        // Cleanup stored images for each banner
        if (($row['storagetype'] == 'web' || $row['storagetype'] == 'sql') && $row['filename'] != '') {
            phpAds_ImageDelete ($row['storagetype'], $row['filename']);
        }
        
        // Delete Banner ACLs
        phpAds_dbQuery("
			DELETE FROM
				".$phpAds_config['tbl_acls']."
			WHERE
				bannerid = ".$row['bannerid']."
		") or phpAds_sqlDie();
        
        // Delete stats for each banner
        phpAds_deleteStatsByBannerID($row['bannerid']);
    }
    
    // Delete Banners
    phpAds_dbQuery("
		DELETE FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			campaignid = '$campaignid'
	") or phpAds_sqlDie();
}


if (isset($campaignid) && $campaignid != '') {
    // Campaign is specified, delete only this campaign
    phpAds_DeleteCampaign($campaignid);
    // Find and delete the campains from $node_array, if
    // necessary. (Later, it would be better to have 
    // links to this file pass in the clientid as well,
    // to facilitate the process below.
    if (isset($node_array)) {
        foreach ($node_array['clients'] as $key => $val) {
            if (isset($node_array['clients'][$key]['campaigns'])) {
                unset($node_array['clients'][$key]['campaigns'][$campaignid]);
            }
        }
    }
} elseif (isset($clientid) && $clientid != '') {
    /****************************************************************/
    /* The following code is suspected to be no longer needed, remove 
     * in later release?
     * 
     * Andrew Hill
     * 2004-05-18
     */
    // No campaign specified, delete all campaigns for this client
    $res_campaigns = phpAds_dbQuery("
		SELECT
			campaignid
		FROM
			".$phpAds_config['tbl_campaigns']."
		WHERE
			clientid = ".$clientid."
	");
    
    while ($row = phpAds_dbFetchArray($res_campaigns)) {
        phpAds_DeleteCampaign($row['campaignid']);
    }
    /****************************************************************/
}

include_once('lib-instant-update.inc.php');
instant_update("campaignid:".$campaignid);

/*********************************************************/
/* Save the $node_array, if necessary                    */
/*********************************************************/

if (isset($node_array)) {
    $Session['prefs']['advertiser-index.php']['nodes'] = $node_array;
    phpAds_SessionDataStore();
}

/*********************************************************/
/* Return to the index page                              */
/*********************************************************/

if (!isset($returnurl) && $returnurl == '') {
    $returnurl = 'advertiser-campaigns.php';
}

header ("Location: ".$returnurl."?clientid=".$clientid);

?>