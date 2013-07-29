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
$Id: campaign-modify.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Include required files
require ("config.php");
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");
include_once 'lib-statistics.inc.php';
require ("../libraries/lib-priority.inc.php");

// Register input variables
phpAds_registerGlobal ('moveto', 'returnurl', 'duplicate', 'clientid', 'campaignid');

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($campaignid) && $campaignid != '') {
    
    if (isset($duplicate) && $duplicate != '' && (phpAds_isUser(phpAds_Agency) || phpAds_isUser(phpAds_Admin))) {
        
        // ClientId
        $moveto = $clientid;
        $origCampaignid = $campaignid;

        // Build name of campaign
        $sql = 'SELECT
                    campaignname
                FROM 
                    '.$phpAds_config['tbl_campaigns'].'
                WHERE
                    campaignid='.$campaignid.'
                LIMIT 1';
        
         $result = phpAds_dbQuery($sql)
                    or phpAds_sqlDie();
                    
        if ($row = phpAds_dbFetchArray($result)) {
                   
            $campaignname = $row['campaignname'];
            
            $sql = 'SELECT
                        campaignname
                    FROM
                        '.$phpAds_config['tbl_campaigns'].'
                    WHERE
                        campaignname LIKE \''.$campaignname.'%\'
                    ORDER BY
                        campaignname DESC
                    LIMIT 1';

            
            $result = phpAds_dbQuery($sql)
                    or phpAds_sqlDie();

            $row = phpAds_dbFetchArray($result);
            
            // get current index
            preg_match("/^.*?\((.*?)\).*?$/",$row['campaignname'],$index);
            
            if (isset($index[1])) {
                $newCampaignname = str_replace('('.$index[1].')', '('.($index[1]+1).')', $row['campaignname']);
            } else {
                $newCampaignname = $campaignname.' (2)';   
            }
            
                $sql = 'INSERT INTO '
                    .$phpAds_config['tbl_campaigns'].
               '   (campaignname,
                    clientid,
                    views,
                    clicks,
                    conversions,
                    expire,
                    activate,
                    active,
                    priority,
                    weight,
                    target,
                    optimise,
                    anonymous)
                  SELECT
                    \''.$newCampaignname.'\',
                    clientid,
                    views,
                    clicks,
                    conversions,
                    expire,
                    activate,
                    active,
                    priority,
                    weight,
                    target,
                    optimise,
                    anonymous
                   FROM
                        '.$phpAds_config['tbl_campaigns'].'
                   WHERE
                        campaignid='.$campaignid;
               
        $result = phpAds_dbQuery($sql)
                    or phpAds_sqlDie();
                    
        $campaignid = phpAds_dbInsertID();

                           
         $bannersResult = phpAds_dbQuery("
			SELECT
		   		*
			FROM
				".$phpAds_config['tbl_banners']."
			WHERE
				campaignid = '".$origCampaignid."'
		") or phpAds_sqlDie();
		
         while ($row = phpAds_dbFetchArray($bannersResult)) {
			// Remove bannerid
			unset($row['bannerid']);
			$row['campaignid'] = $campaignid;
						
			// Duplicate stored banner
			if ($row['storagetype'] == 'web' || $row['storagetype'] == 'sql')
				$row['filename'] = phpAds_ImageDuplicate ($row['storagetype'], $row['filename']);
						
			// Clone banner
	   		$values_fields = '';
	   		$values = '';
			
			while (list($name, $value) = each($row)) {
				$values_fields .= "$name, ";
				$values .= "'".addslashes($value)."', ";
			}
			
			$values_fields = ereg_replace(", $", "", $values_fields);
			$values = ereg_replace(", $", "", $values);
			
	   		$res = phpAds_dbQuery("
		   		INSERT INTO
		   			".$phpAds_config['tbl_banners']."
		   			($values_fields)
		   		VALUES
		   			($values)
	   		") or phpAds_sqlDie();
			
			$new_bannerid = phpAds_dbInsertID();
			
		   	
			if ($phpAds_config['acl']) {
				// Clone display limitations
			   	$aclResult = phpAds_dbQuery("
			   	   SELECT
			   	      *
			   	   FROM
			   	      ".$phpAds_config['tbl_acls']."
			   	   WHERE
			   	      bannerid = '".$bannerid."'
		   	    ") or phpAds_sqlDie();
				
			   	while ($row = phpAds_dbFetchArray($aclResult)) {
			   		$values_fields = '';
			   		$values = '';
			   		
					$row['bannerid'] = $new_bannerid;
			   		
					while (list($name, $value) = each($row)) {
						$values_fields .= "$name, ";
						$values .= "'".addslashes($value)."', ";
					}
					
   					$values_fields = ereg_replace(", $", "", $values_fields);
					$values = ereg_replace(", $", "", $values);
					
					phpAds_dbQuery("
						INSERT INTO
							".$phpAds_config['tbl_acls']."
							($values_fields)
						VALUES
							($values)
					") or phpAds_sqlDie();
				}
			}
			
			// Duplicate linked trackers
			$sql = 'INSERT INTO
			             '.$phpAds_config['tbl_campaigns_trackers'].'
			         (campaignid,
			          trackerid,
			          logstats,
			          viewwindow,
			          clickwindow)
    		         SELECT
			             '.$campaignid.',
			             trackerid,
			             logstats,
			             viewwindow,
			             clickwindow
			         FROM
			             '.$phpAds_config['tbl_campaigns_trackers'].'
			         WHERE campaignid = '.$origCampaignid;
			
			phpAds_dbQuery($sql) 
			         or phpAds_sqlDie();
			
			
		}
		
		// Rebuild priorities
		phpAds_PriorityCalculate();
		
		//---------------------------
                    
                    
        }
    } else if (isset($moveto) && $moveto != '') {
        
        /*********************************************************/
        /* Restore cache of $node_array, if it exists            */
        /*********************************************************/

        if (isset($Session['prefs']['advertiser-index.php']['nodes'])) {
            $node_array = $Session['prefs']['advertiser-index.php']['nodes'];
        }
	    
        /*********************************************************/
        
		if (phpAds_isUser(phpAds_Agency)) {
			$query = "SELECT c.clientid".
				" FROM ".$phpAds_config['tbl_clients']." AS c".
				",".$phpAds_config['tbl_campaigns']." AS m".
				" WHERE c.clientid=m.clientid".
				" AND c.clientid=".$clientid.
				" AND m.campaignid=".$campaignid.
				" AND agencyid=".phpAds_getUserID();
			$res = phpAds_dbQuery($query) or phpAds_sqlDie();
			if (phpAds_dbNumRows($res) == 0) {
				phpAds_PageHeader("2");
				phpAds_Die ($strAccessDenied, $strNotAdmin);
			}
			$query = "SELECT c.clientid".
				" FROM ".$phpAds_config['tbl_clients']." AS c".
				" WHERE c.clientid=".$moveto.
				" AND agencyid=".phpAds_getUserID();
			$res = phpAds_dbQuery($query) or phpAds_sqlDie();
			if (phpAds_dbNumRows($res) == 0) {
				phpAds_PageHeader("2");
				phpAds_Die ($strAccessDenied, $strNotAdmin);
			}
		}
		
		// Delete any campaign-tracker links
		$res = phpAds_dbQuery(
			"DELETE FROM ".$phpAds_config['tbl_campaigns_trackers'].
			" WHERE campaignid=".$campaignid
		) or phpAds_sqlDie();

		// Move the campaign
		$res = phpAds_dbQuery(
			"UPDATE ".$phpAds_config['tbl_campaigns'].
			" SET clientid=".$moveto.
			" WHERE campaignid=".$campaignid
		) or phpAds_sqlDie();

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
		
		// Rebuild cache
		if (!defined('LIBVIEWCACHE_INCLUDED')) {
			include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
		}
		
		//phpAds_cacheDelete();
		
		/*********************************************************/
		/* Save the $node_array, if necessary                    */
		/*********************************************************/
		
		if (isset($node_array)) {
		    $Session['prefs']['advertiser-index.php']['nodes'] = $node_array;
		    phpAds_SessionDataStore();
		}
		
		/*********************************************************/		
		
	}
}

Header ("Location: ".$returnurl."?clientid=".(isset($moveto) ? $moveto : $clientid)."&campaignid=".$campaignid);
exit;
?>