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
$Id: banner-modify.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");
require ("lib-statistics.inc.php");
require ("../libraries/lib-priority.inc.php");


// Register input variables
phpAds_registerGlobal ('returnurl', 'duplicate', 'moveto_x', 'moveto', 'applyto_x', 'applyto');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);


/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($bannerid) && $bannerid != '')
{
	if (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT ".
			$phpAds_config['tbl_banners'].".bannerid as bannerid".
			" FROM ".$phpAds_config['tbl_clients'].
			",".$phpAds_config['tbl_campaigns'].
			",".$phpAds_config['tbl_banners'].
			" WHERE ".$phpAds_config['tbl_banners'].".bannerid=".$bannerid.
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
	
	if (isset($moveto_x) && $moveto != '')
	{
		if (phpAds_isUser(phpAds_Agency))
		{
			$query = "SELECT ".
				$phpAds_config['tbl_campaigns'].".campaignid as campaignid".
				" FROM ".$phpAds_config['tbl_clients'].
				",".$phpAds_config['tbl_campaigns'].
				" WHERE ".$phpAds_config['tbl_campaigns'].".campaignid=".$moveto.
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
		// Move the banner
		$res = phpAds_dbQuery("UPDATE ".$phpAds_config['tbl_banners']." SET campaignid=".$moveto." WHERE bannerid=".$bannerid) or phpAds_sqlDie();
		
		// Rebuild priorities
		phpAds_PriorityCalculate();
		
		// Rebuild cache
		if (!defined('LIBVIEWCACHE_INCLUDED')) 
			include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
		
		phpAds_cacheDelete();
		
		// Get new clientid
		$clientid = phpAds_getCampaignParentClientID ($moveto);
		
		Header ("Location: ".$returnurl."?clientid=".$clientid."&campaignid=".$moveto."&bannerid=".$bannerid);
	}
	elseif (isset($applyto_x) && $applyto != '')
	{
		if (phpAds_isUser(phpAds_Agency))
		{
			$query = "SELECT ".
				$phpAds_config['tbl_banners'].".bannerid as bannerid".
				" FROM ".$phpAds_config['tbl_clients'].
				",".$phpAds_config['tbl_campaigns'].
				",".$phpAds_config['tbl_banners'].
				" WHERE ".$phpAds_config['tbl_banners'].".bannerid=".$applyto.
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
		
		// Delete old limitations
	   	$res = phpAds_dbQuery("
			DELETE FROM
				".$phpAds_config['tbl_acls']."
			WHERE
				bannerid = ".$applyto."
		") or phpAds_sqlDie();
		
		// Load source limitation
		$res = phpAds_dbQuery("
		   SELECT
	   	      *
	   	   FROM
	   	      ".$phpAds_config['tbl_acls']."
	   	   WHERE
	   	      bannerid = '".$bannerid."'
   	    ") or phpAds_sqlDie();
		
	   	while ($row = phpAds_dbFetchArray($res))
	   	{
	   		$values_fields = '';
	   		$values = '';
	   		
			$row['bannerid'] = $applyto;
	   		
			while (list($name, $value) = each($row))
			{
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
		
		// Get compiledlimitation from source
		$res = phpAds_dbQuery("
			SELECT 
				compiledlimitation
			FROM
				".$phpAds_config['tbl_banners']."
			WHERE
				bannerid = '".$bannerid."'
		") or phpAds_sqlDie();
		
	   	if ($row = phpAds_dbFetchArray($res))
		{
			$res = phpAds_dbQuery("
				UPDATE 
					".$phpAds_config['tbl_banners']."
				SET
					compiledlimitation = '".addslashes($row['compiledlimitation'])."'
				WHERE
					bannerid = '".$applyto."'
			") or phpAds_sqlDie();
		}
		
		// Rebuild cache
		if (!defined('LIBVIEWCACHE_INCLUDED')) 
			include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
		
		phpAds_cacheDelete();
		
		Header ("Location: ".$returnurl."?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$applyto);
	}
	elseif (isset($duplicate) && $duplicate == 'true')
	{
		// Duplicate the banner
		
		$res = phpAds_dbQuery("
			SELECT
		   		*
			FROM
				".$phpAds_config['tbl_banners']."
			WHERE
				bannerid = '".$bannerid."'
		") or phpAds_sqlDie();
		
		if ($row = phpAds_dbFetchArray($res))
		{
			// Remove bannerid
			unset($row['bannerid']);
			
			
			// Duplicate stored banner
			if ($row['storagetype'] == 'web' || $row['storagetype'] == 'sql')
				$row['filename'] = phpAds_ImageDuplicate ($row['storagetype'], $row['filename']);
			
			
			// Clone banner
	   		$values_fields = '';
	   		$values = '';
			
			while (list($name, $value) = each($row))
			{
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
			
		   	
			if ($phpAds_config['acl'])
			{
				// Clone display limitations
			   	$res = phpAds_dbQuery("
			   	   SELECT
			   	      *
			   	   FROM
			   	      ".$phpAds_config['tbl_acls']."
			   	   WHERE
			   	      bannerid = '".$bannerid."'
		   	    ") or phpAds_sqlDie();
				
			   	while ($row = phpAds_dbFetchArray($res))
			   	{
			   		$values_fields = '';
			   		$values = '';
			   		
					$row['bannerid'] = $new_bannerid;
			   		
					while (list($name, $value) = each($row))
					{
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
		}
		
		// Rebuild priorities
		phpAds_PriorityCalculate();
		
		
		// Rebuild cache
		if (!defined('LIBVIEWCACHE_INCLUDED')) 
			include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
		
		phpAds_cacheDelete();
		
		
		Header ("Location: ".$returnurl."?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$new_bannerid);
	}
	else
	{
		Header ("Location: ".$returnurl."?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid);
	}
}

?>