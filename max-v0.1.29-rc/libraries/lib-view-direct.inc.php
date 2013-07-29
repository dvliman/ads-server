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
$Id: lib-view-direct.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


require_once('lib-io.inc.php');

// Set define to prevent duplicate include
define ('LIBVIEWDIRECT_INCLUDED', true);


/*********************************************************/
/* Get a banner                                          */
/*********************************************************/

function phpAds_fetchBannerDirect($remaining, $context = 0, $source = '', $richmedia = true)
{
	global $phpAds_config;

	// Get first part, store second part
	$what = strtok($remaining, '|');
	$remaining = strtok ('');
	
	// Expand paths to regular statements
	if (strpos($what, '/') > 0) {
		if (strpos($what, '@') > 0) {
		    list ($what, $append) = explode ('@', $what);
		} else {
			$append = '';
		}
		
		$seperate  = explode ('/', $what);
		$expanded  = '';
		$collected = array();
		
		while (list(,$v) = each($seperate)) {
			$expanded   .= ($expanded != '' ? ',+' : '') . $v;
			$collected[] = $expanded . ($append != '' ? ',+'.$append : '');
		}
		
		$what      = strtok(implode('|', array_reverse ($collected)), '|');
		$remaining = strtok('').($remaining != '' ? '|'.$remaining : '');
	}

	
	// Get cache
	if (!defined('LIBVIEWCACHE_INCLUDED')) {
	     include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
	}
	
	$cacheId = "what=$what&remaining=".($remaining == '' ? 'true' : 'false');
	$cache   = phpAds_cacheFetch ($cacheId);
	
    // Unpack cache
    if ($cache) {
	   list ($rows, $what, $prioritysum, $cacheCreationTime) = $cache;
    }
    
	if ((!$cache || $cacheCreationTime < time() - $phpAds_config['cacheExpire']) && phpAds_dbConnect()) {

			if (!defined('LIBVIEWQUERY_INCLUDED')) {
			    include (phpAds_path.'/libraries/lib-view-query.inc.php');
			}
			
			// prevent random banner to show when no client, campaign or banner id is specified
            if (!$phpAds_config['use_keywords'] && (!preg_match('/^[\w]*:[0-9]+$|^[0-9]+$/i', $what))) {
			    return;
			}
			
			$select = phpAds_buildQuery ($what, $remaining == '', $precondition);
			$res    = phpAds_dbQuery($select);
			
			// Build array for further processing...
			$rows = array();
			$prioritysum = 0;

			while ($tmprow = phpAds_dbFetchArray($res))	{
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
				                $rows,
				                $what,
				                $prioritysum,
				                time()
			               );
			
			phpAds_cacheStore ($cacheId, $cache);
			
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
			        case '!=': $excludeCampaignID[$value] = true;
			                   break;
			        case '==': $includeCampaignID[$value] = true;
			                   break;
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
						$rows[$i]     = '';
						
						// Break out of the for loop to try again
						break;
					}
					
					// Banner was not on exclude list and was on include list (if one existed)
					// Now continue with ACL check
					if ($phpAds_config['acl']) {
						if (phpAds_aclCheck($rows[$i], $source)) {
							$rows[$i]['zoneid'] = 0;
							return ($rows[$i]);
						}
						
						// Matched, but phpAds_aclCheck failed.
						// Delete this row and adjust $prioritysum
						$prioritysum -= $rows[$i]['priority'];
						$rows[$i]     = '';
						
						// Break out of the for loop to try again
						break;
					} else {
						// Don't check ACLs, found banner!
						$rows[$i]['zoneid'] = 0;
						return ($rows[$i]);
					}
				}
			}
		}
	}
	
	return ($remaining);
}

?>
