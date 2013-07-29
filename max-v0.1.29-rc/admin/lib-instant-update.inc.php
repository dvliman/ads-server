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
$Id: lib-instant-update.inc.php 1273 2005-01-27 10:0:00Z chris $
*/

/*********************************************************/
/* Update the priorities and cache files in real-time    */
/*********************************************************/

/**
 * Updates the priorities (global) and deletes any affected cache files
 * $wht    The cache file what parameter to be updated
 * 
 * @return void
 */
 
function instant_update($what) {
    global $phpAds_config;
    
    // Return if the admin instant update setting is turned off
    if ($phpAds_config['admin_instant_update'] == false) { return false; }
    
    // Recalculate Priorities if required, this is currently global
    if ($phpAds_config['instant_update_priority']) {
        require_once(phpAds_path . '/libraries/' . 'lib-priority.inc.php');
        phpAds_PriorityCalculate();
    }
    
    if ($phpAds_config['instant_update_cache']) {
        require_once(phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
        
        $linkedZones = _getLinkedZones($what);
        
        foreach ($linkedZones as $thisZone) {
            phpAds_cacheDelete('what=zone:'.$thisZone);
            phpAds_cacheDelete('zoneid:'.$thisZone);
        }
        phpAds_cacheDelete($what);
        // Old format what string: what=[campaign|zone|banner]:[id]
        $parts = explode(':', $what);
        $parts[0] = substr($parts[0], 0, (strlen($parts[0]) - 2));
        phpAds_cacheDelete('what='.$parts[0].':'.$parts[1]);
    }
}

/*********************************************************/
/* Get all zones that a banner/campaign is linked to     */
/*********************************************************/

/**
 * Finds zones affected by changing an item
 *
 * $what The what string for the specified item
 *
 * @return array Returns an array of affected zoneid's
 */
 
function _getLinkedZones($what) {
    // This function receives a $what value and returns a list of zoneid's which would need to be rebuilt
    // upon changing an item in the $what value
    if (!preg_match('/^[\w]*:[0-9]+$|^[0-9]+$/i', $what)) {return array(); }
    $parts = explode(':', $what);
    $linkedZones = array();
    $linkedZonesWhere = "";
    
    switch ($parts[0]) {
        case 'bannerid': 
            // Passed a banner id, so find any zones that this banner or parent campaign id are linked to
            $campaignRes = phpAds_dbQuery("SELECT campaignid FROM banners WHERE bannerid={$parts[1]}");
        break;
        
        case 'campaignid': 
            // Passed a campaign id, so find any zones that this campaign or child banners are linked to
                    
            $campaignbanners[] = "what LIKE '%campaignid:{$parts[1]}%'";
            
            $campaignbanners_res = phpAds_dbQuery('SELECT bannerid from banners where campaignid = '.$parts[1]);
            
            while ($bannerrow = phpAds_dbFetchArray($campaignbanners_res)) {
                $campaignbanners[] = "what LIKE '%bannerid:{$bannerrow['bannerid']}%'";
            }    
            $linkedZonesWhere = implode(' OR ', $campaignbanners);
        
        break;
        
        case 'zoneid':
            // Passed a zoneid... not sure if any other zones would need to be rebuilt, for now, pass back the zoneid
            
        break;
    }
    
    $linkedZones_res = phpAds_dbQuery("SELECT zoneid FROM zones WHERE " . $linkedZonesWhere);
    while ($row = phpAds_dbFetchArray($linkedZones_res)) {
        $linkedZones[] = $row['zoneid'];
    }
    
    return $linkedZones;
}

?>