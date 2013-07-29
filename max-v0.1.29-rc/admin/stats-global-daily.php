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
$Id: stats-global-daily.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

$res = phpAds_dbQuery("
	SELECT
		DATE_FORMAT(day, '%Y%m%d') as date,
		DATE_FORMAT(day, '$date_format') as date_formatted
	FROM
		".$phpAds_config['tbl_adstats']."
	GROUP BY
		day
	ORDER BY
		day DESC
	LIMIT 7
") or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		$row['date_formatted'],
		"stats-global-daily.php?day=".$row['date'],
		$day == $row['date']
	);
}

phpAds_PageHeader("2.2.1");
	
	$sections[] = "2.2.1";
	//if (!$phpAds_config['compact_stats']) $sections[] = "2.2.2";
	phpAds_ShowSections($sections);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Agency)) {
    $zoneids = array();
    
    $idresult = phpAds_dbQuery ("
    	SELECT
    		a.zoneid
    	FROM
    		".$phpAds_config['tbl_zones']." AS a,
            ".$phpAds_config['tbl_affiliates']." AS b
    	WHERE
    		a.affiliateid = b.affiliateid
        AND
            b.agencyid = ".phpAds_getAgencyID()."
    ");
    
    while ($row = phpAds_dbFetchArray($idresult)) {
    	$zoneids[] = "zoneid = ".$row['zoneid'];
    }    
    $lib_hourly_where = "(".implode(' OR ', $zoneids).")";
}
include ("lib-hourly.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>