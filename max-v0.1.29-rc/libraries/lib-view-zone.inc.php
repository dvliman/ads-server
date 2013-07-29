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
$Id: lib-view-zone.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBVIEWZONE_INCLUDED', true);


/*********************************************************/
/* Get a banner                                          */
/*********************************************************/

function phpAds_fetchBannerZone($remaining, $context = 0, $source = '', $richmedia = true)
{
	global $phpAds_config;
	global $phpAds_followedChain;
    global $g_append, $g_prepend;
    
	// Get first part, store second part
	$what = strtok($remaining, '|');
	$remaining = strtok ('');
	$zoneid  = intval(substr($what,5));
	
	// Check if zone was already evaluated in the chain
	if (isset($phpAds_followedChain) && in_array($zoneid, $phpAds_followedChain)) {
		return ($remaining);
	} else {
		$phpAds_followedChain[] = $zoneid;
	}
	
	// Get cache
	if (!defined('LIBVIEWCACHE_INCLUDED')) {
	     include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
	}
	
	$cacheId = 'what=zone:'.$zoneid;
	$cache   = phpAds_cacheFetch ($cacheId);
	$appendedThisZone = false;
	    
    // Unpack cache
    if ($cache) {
	   list ($zoneid, $rows, $what, $prioritysum, $chain, $zone['prepend'], $zone['append'], $forceappend, $cacheCreationTime) = $cache;

        if ((time() - $cacheCreationTime) < $phpAds_config['cacheExpire']) {
            if ($forceappend == 't') {
            
                $g_prepend .= $zone['prepend'];
                $g_append = $zone['append'] . $g_append;
                $appendedThisZone = true;
            }
    	   
    	   if ($remaining == '') { $remaining = $chain; }
    	   if ($what == '') { return $remaining; }
	   }
    }
    
	if ((!$cache || $cacheCreationTime < time() - $phpAds_config['cacheExpire']) && phpAds_dbConnect()) {
        $zoneres = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_zones']." WHERE zoneid='".$zoneid."'");
	    
	    if ($zone = phpAds_dbFetchArray($zoneres)) {
	        // No linked banners
	        if ($remaining == '') {
	            $remaining = $zone['chain'];
	        }
	        
            if ($zone['forceappend'] == 't') {
                $g_prepend .= $zone['prepend'];
                $g_append = $zone['append'] . $g_append;
                $appendedThisZone = true;
            }

            if ($zone['what'] == '') {
	            return ($remaining);
            }            
            
	        if (!defined('LIBVIEWQUERY_INCLUDED')) {
	            include (phpAds_path.'/libraries/lib-view-query.inc.php');
	        }
	        
	        $precondition = '';
	        
	        // Size preconditions
	        if ($zone['width'] > -1) {
	            $precondition .= " AND b.width = ".$zone['width']." ";
	        }
	        if ($zone['height'] > -1) {
	            $precondition .= " AND b.height = ".$zone['height']." ";
	        }
	        
	        // Text Ads preconditions
	        // Matching against the value instead of the constant phpAds_ZoneText (3).
	        // Didn't want to include the whole lib-zones just for a constant
	        if ($zone['delivery'] == 3) {
	            $precondition .= " AND b.storagetype = 'txt' ";
	        } else {
	            $precondition .= " AND b.storagetype <> 'txt' ";
	        }
	        
	        $select = phpAds_buildQuery ($zone['what'], false, $precondition);
	        $res    = phpAds_dbQuery($select)
	           or die(mysql_error());
	        
	        // Build array for further processing...
	        $rows        = array();
	        $prioritysum = 0;
	        
	        while ($tmprow = phpAds_dbFetchArray($res)) {
	            // weight of 0 disables the banner
	            if ($tmprow['priority']) {
	                $prioritysum += $tmprow['priority'];
                    if ($richmedia == false && !($rows[$i]['contenttype'] == 'jpeg' || $rows[$i]['contenttype'] == 'gif' || $rows[$i]['contenttype'] == 'png') && $tmprow['alt_filename']) {
    				    $tmprow['filename'] = $tmprow['alt_filename'];
    				    $tmprow['contenttype'] = $tmprow['alt_contenttype'];
					}
					$rows[] = $tmprow;
	            }
	        }
	        
	        $cache = array (
	           $zone['zoneid'],
	           $rows,
	           $zone['what'],
	           $prioritysum,
	           $zone['chain'],
	           $zone['prepend'],
	           $zone['append'],
	           $zone['forceappend'],
	           time()
	        );
	        
	        phpAds_cacheStore ($cacheId, $cache);

            if ($zone['what'] == '') {
	            return ($remaining);
	        }
        }
		
	} elseif  (!$cache) {
	            // Zone not found and not able to connect to db!
				return ($remaining);
	} else {
	    
				
		if ($remaining == '') {
			$remaining = $chain;
		}
		if (count($rows) == 0) {
		    // No banners are linked to this zone
		    if ($forceappend == 't') {
                $g_prepend .= $zone['prepend'];
                $g_append = $zone['append'] . $g_append;
		    }
			return ($remaining);
		}

	}
	
	// Build preconditions
	$excludeBannerID   = array();
	$excludeCampaignID = array();
	$includeBannerID   = array();
	$includeCampaignID = array();
	
	if (is_array ($context)) {
		for ($i=0; $i < count($context); $i++) {
		    
			list ($key, $value) = each($context[$i]);
			
			$type = 'bannerid';
			$valueArray = explode(':', $value);
			
			if (count($valueArray) == 1) {
                list($value) = $valueArray;
			} else {
                list($type, $value) = $valueArray;
			}
			
			if ($type == 'bannerid') {
			    switch ($key) {
			        case '!=': $excludeBannerID[$value] = true;
			                   break;
			        case '==': $includeBannerID[$value] = true;
			                   break;
			    }
			}
			
			if ($type == 'campaignid') {
			    switch ($key) {
			        case '!=': $excludeCampaignID[$value] = true; break;
			        case '==': $includeCampaignID[$value] = true; break;
			    }
			}
		}
	}

	$maxindex = sizeof($rows);
	
	while ($prioritysum && sizeof($rows)) {
		
	    $low = 0;
		$high = 0;
		$ranweight = ($prioritysum > 1) ? mt_rand(0, $prioritysum - 1) : 0;
		
		for ($i=0; $i<$maxindex; $i++) {
			if (is_array($rows[$i])) {
			    
				$low = $high;
				$high += $rows[$i]['priority'];
				
				if ($high > $ranweight && $low <= $ranweight) {
					
				    $postconditionSucces = true;
					
					// Excludelist banners
					if (isset($excludeBannerID[$rows[$i]['bannerid']])) {
						$postconditionSucces = false;
					} elseif (isset($excludeCampaignID[$rows[$i]['campaignid']])) {
					    // Excludelist campaigns
						$postconditionSucces = false;
					} elseif (sizeof($includeBannerID) && !isset ($includeBannerID[$rows[$i]['bannerid']])) {
					    // Includelist banners
						$postconditionSucces = false;
					} elseif (sizeof($includeCampaignID) && !isset ($includeCampaignID[$rows[$i]['campaignid']])) {
					    // Includelist campaigns
						$postconditionSucces = false;
					} elseif ($richmedia == false && !($rows[$i]['contenttype'] == 'jpeg' || $rows[$i]['contenttype'] == 'gif' || $rows[$i]['contenttype'] == 'png') && !($rows[$i]['storagetype'] == 'url' && $rows[$i]['contenttype'] == '')) {
					    // HTML or Flash banners
    						$postconditionSucces = false;
					} elseif (phpAds_isAdBlocked($rows[$i]['bannerid'], $rows[$i]['block'])) {
					    // Blocked
						$postconditionSucces = false;
					} elseif (phpAds_isAdCapped($rows[$i]['bannerid'], $rows[$i]['capping'], $rows[$i]['session_capping'])) {
					    // Capped
						$postconditionSucces = false;
					} elseif ($_SERVER['SERVER_PORT'] == 443 && $rows[$i]['storagetype'] == 'html' && strpos($rows[$i]['htmlcache'],'http:') >= 0 ) {
					    // HTML Banners that contain 'http:' on SSL
						$postconditionSucces = false;
					} elseif ($_SERVER['SERVER_PORT'] == 443 && $rows[$i]['storagetype'] == 'url' && is_numeric(strpos($rows[$i]['imageurl'],'http:'))) {
					    // External Banners that contain 'http:' on SSL
					    // Note: Using "is_numeric" because usual value will be 0 (first character in string) but may be preceeded by spaces
						$postconditionSucces = false;
					}
					if ($postconditionSucces == false) {
						// Failed one of the postconditions
						// Delete this row and adjust $prioritysum
						$prioritysum -= $rows[$i]['priority'];
						$rows[$i] = '';
						
						// Break out of the for loop to try again
						break;
					}
					
					// Banner was not on exclude list and was on include list (if one existed)
					// Now continue with ACL check
					if ($phpAds_config['acl']) {
						if (phpAds_aclCheck($rows[$i], $source)) {
							$rows[$i]['zoneid'] = $zoneid;
                            
							if (!$appendedThisZone) {
                                $rows[$i]['append'] = $rows[$i]['append'] . $zone['append'] . $g_append; 
                                $rows[$i]['prepend'] .= $g_prepend . $zone['prepend'];
                            }
                            else {
                                $rows[$i]['append'] = $rows[$i]['append'] . $g_append;
                                 $rows[$i]['prepend'] = $g_prepend; 
                            }
                            
							return ($rows[$i]);
						}
						
						// Matched, but phpAds_aclCheck failed.
						// Delete this row and adjust $prioritysum
						$prioritysum -= $rows[$i]['priority'];
						$rows[$i] = '';
						
						// Break out of the for loop to try again
						break;
					} else {
						// Don't check ACLs, found banner!
						$rows[$i]['zoneid'] = $zoneid;
						if (!$appendedThisZone) {
                            $rows[$i]['append'] = $rows[$i]['append'] . $zone['append'] . $g_append; 
                            $rows[$i]['prepend'] .= $g_prepend . $zone['prepend'];
                        }
                        else {
                            $rows[$i]['append'] = $rows[$i]['append'] . $g_append;
                             $rows[$i]['prepend'] = $g_prepend; 
                        }
                        return ($rows[$i]);
					}
				}
			}
		}
	}
	
	return ($remaining);
}

?>
