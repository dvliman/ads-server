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
$Id: affiliatehistory.plugin.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Public name of the plugin info function
$plugin_info_function		= "Plugin_AffiliatehistoryInfo";


// Public info function
function Plugin_AffiliatehistoryInfo()
{
	global $strAffiliateHistory, $strAffiliate, $strPluginAffiliate, $strDelimiter;
	
	$plugininfo = array (
		"plugin-name"			=> $strAffiliateHistory,
		"plugin-description"	=> $strPluginAffiliate,
		"plugin-author"			=> "Niels Leenheer",
		"plugin-export"			=> "csv",
		"plugin-authorize"		=> phpAds_Admin+phpAds_Agency+phpAds_Affiliate,
		"plugin-execute"		=> "Plugin_AffiliatehistoryExecute",
		"plugin-import"			=> array (
			"campaignid"			=> array (
				"title"					=> $strAffiliate,
				"type"					=> "affiliateid-dropdown" ),
			"delimiter"		=> array (
				"title"					=> $strDelimiter,
				"type"					=> "edit",
				"size"					=> 1,
				"default"				=> "," ) )
	);
	
	return ($plugininfo);
}



/*********************************************************/
/* Private plugin function                               */
/*********************************************************/

function Plugin_AffiliatehistoryExecute($affiliateid, $delimiter=",")
{
	global $phpAds_config, $date_format;
	global $strAffiliate, $strTotal, $strDay, $strViews, $strClicks, $strCTRShort;
	
	header("Content-type: application/csv\nContent-Disposition: inline; filename=\"affiliatehistory.csv\"");
	
	$idresult = phpAds_dbQuery ("
		SELECT
			zoneid
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			affiliateid = '".$affiliateid."'
	");
	
	while ($row = phpAds_dbFetchArray($idresult))
	{
		$zoneids[] = "zoneid = ".$row['zoneid'];
	}
	
	
	$res_query = "
		SELECT
			DATE_FORMAT(day, '".$date_format."') as day,
			SUM(views) AS adviews,
			SUM(clicks) AS adclicks
		FROM
			".$phpAds_config['tbl_adstats']."
		WHERE
			(".implode(' OR ', $zoneids).")
		GROUP BY
			day
	    ORDER BY
	        DATE_FORMAT(day, '%Y%m%d')
	";
	
	$res_banners = phpAds_dbQuery($res_query) or phpAds_sqlDie();
	
	while ($row_banners = phpAds_dbFetchArray($res_banners))
	{
		$stats [$row_banners['day']]['views'] = $row_banners['adviews'];
		$stats [$row_banners['day']]['clicks'] = $row_banners['adclicks'];
	}
	
	echo $strAffiliate.": ".strip_tags(phpAds_getAffiliateName ($affiliateid))."\n\n";
	echo $strDay.$delimiter.$strViews.$delimiter.$strClicks.$delimiter.$strCTRShort."\n";
	
	$totalclicks = 0;
	$totalviews = 0;
	
	if (isset($stats) && is_array($stats))
	{
		for (reset($stats);$key=key($stats);next($stats))
		{
			$row = array();
			
//			$key = implode('/',array_reverse(split('[-]',$key)));
			
			$row[] = $key;
			$row[] = $stats[$key]['views'];
			$row[] = $stats[$key]['clicks'];
			$row[] = phpAds_buildCTR ($stats[$key]['views'], $stats[$key]['clicks']);
			
			echo implode ($delimiter, $row)."\n";
			
			$totalclicks += $stats[$key]['clicks'];
			$totalviews += $stats[$key]['views'];
		}
	}
	
	echo "\n";
	echo $strTotal.$delimiter.$totalviews.$delimiter.$totalclicks.$delimiter.phpAds_buildCTR ($totalviews, $totalclicks)."\n";
}

?>
