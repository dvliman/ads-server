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
$Id: tracker-modify.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-storage.inc.php");
require ("lib-zones.inc.php");


// Register input variables
phpAds_registerGlobal (
	 'duplicate'
	,'moveto'
	,'returnurl'
);


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

if (phpAds_isUser(phpAds_Agency))
{
    $query = "SELECT c.clientid".
		" FROM ".$phpAds_config['tbl_clients']." AS c".
		",".$phpAds_config['tbl_trackers']." AS t".
		" WHERE t.clientid=c.clientid".
		" AND t.trackerid=".$trackerid.
		" AND c.agencyid=".phpAds_getUserID();
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}


/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (isset($trackerid) && $trackerid != '')
{
	if (isset($moveto) && $moveto != '')
	{
		// Delete any campaign-tracker links
		$res = phpAds_dbQuery(
			"DELETE FROM ".$phpAds_config['tbl_campaigns_trackers'].
			" WHERE trackerid=".$trackerid
		) or phpAds_sqlDie();

		// Move the campaign
		$res = phpAds_dbQuery(
			"UPDATE ".$phpAds_config['tbl_trackers'].
			" SET clientid=".$moveto.
			" WHERE trackerid=".$trackerid
		) or phpAds_sqlDie();
		
		Header ("Location: ".$returnurl."?clientid=".$moveto."&trackerid=".$trackerid);
		exit;
	}
	elseif (isset($duplicate) && $duplicate == 'true')
	{
		// Duplicate the zone
		
		$res = phpAds_dbQuery(
			"SELECT *".
			" FROM ".$phpAds_config['tbl_trackers'].
			" WHERE trackerid=".$trackerid
		) or phpAds_sqlDie();
		
		
		if ($row = phpAds_dbFetchArray($res))
		{
			// Get names
			if (ereg("^(.*) \([0-9]+\)$", $row['trackername'], $regs))
				$basename = $regs[1];
			else
				$basename = $row['trackername'];
			
			$names = array();
			
			$res = phpAds_dbQuery(
				"SELECT *".
				" FROM ".$phpAds_config['tbl_trackers']
			) or phpAds_sqlDie();
			
			while ($name = phpAds_dbFetchArray($res))
				$names[] = $name['trackername'];
			
			
			// Get unique name
			$i = 2;
			
			while (in_array($basename.' ('.$i.')', $names))
				$i++;
			
			$row['trackername'] = $basename.' ('.$i.')';
			
			
			// Remove tracker
			unset($row['trackerid']);
	   		
			$values = array();
			
			while (list($name, $value) = each($row))
				$values[] = $name." = '".addslashes($value)."'";
			
	   		$res = phpAds_dbQuery("
		   		INSERT INTO
		   			".$phpAds_config['tbl_trackers']."
				SET
					".implode(", ", $values)."
	   		") or phpAds_sqlDie();
			
			$new_trackerid = phpAds_dbInsertID();
			
			// Copy any linked campaigns
			$res = phpAds_dbQuery(
				"SELECT".
				" campaignid".
				",trackerid".
				",logstats".
				",clickwindow".
				",viewwindow".
				" FROM ".$phpAds_config['tbl_campaigns_trackers'].
				" WHERE trackerid=".$trackerid
			) or phpAds_sqlDie();
			
			while($row = phpAds_dbFetchArray($res))
			{
				$res2 = phpAds_dbQuery(
					" INSERT INTO ".$phpAds_config['tbl_campaigns_trackers'].
					" SET campaignid=".$row['campaignid'].
					",trackerid=".$new_trackerid.
					",logstats='".$row['logstats']."'".
					",clickwindow=".$row['clickwindow'].
					",viewwindow=".$row['viewwindow']
				) or phpAds_sqlDie();
			}
			
			Header ("Location: ".$returnurl."?clientid=".$clientid."&trackerid=".$new_trackerid);
			exit;
		}
	}
}

Header ("Location: ".$returnurl."?clientid=".$clientid."&trackerid=".$trackerid);

?>