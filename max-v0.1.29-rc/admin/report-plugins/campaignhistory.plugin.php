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
$Id: campaignhistory.plugin.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Public name of the plugin info function
$plugin_info_function		= "Plugin_CampaignhistoryInfo";


// Public info function
function Plugin_CampaignhistoryInfo()
{
	global $strCampaignHistory, $strCampaign, $strPluginCampaign, $strDelimiter, $strStartDate, $strEndDate;
	
	$plugininfo = array (
		"plugin-name"			=> $strCampaignHistory,
		"plugin-description"	=> $strPluginCampaign,
		"plugin-author"			=> "Niels Leenheer",
		"plugin-export"			=> "csv",
		"plugin-authorize"		=> phpAds_Admin+phpAds_Agency+phpAds_Client,
		"plugin-execute"		=> "Plugin_CampaignhistoryExecute",
		"plugin-import"			=> array (
			"campaignid"			=> array (
				"title"				=> $strCampaign,
				"type"				=> "campaignid-dropdown"
			),

			"start"				=> array (
				"title"				=> $strStartDate,
				"type"				=> "edit",
				"size"				=> 10,
				"default"			=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-7,  date("Y")))
			),

			"end"				=> array (
				"title"					=> $strEndDate,
				"type"					=> "edit",
				"size"					=> 10,
				"default"				=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-1,  date("Y")))
			),

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

function Plugin_CampaignhistoryExecute($campaignid, $start, $end, $delimiter=",")
{
	global $phpAds_config, $date_format;
	global $strCampaign, $strTotal, $strDay, $strViews, $strClicks, $strCTRShort, $strConversions, $strCNVRShort;
	
	// Format the start and end dates
    $dbStart = date("Y-m-d", strtotime($start));
    $dbEnd   = date("Y-m-d", strtotime($end));
    $start = date("Y/m/d", strtotime($start));
    $end   = date("Y/m/d", strtotime($end));
	
	header("Content-type: application/csv\nContent-Disposition: inline; filename=\"campaignhistory.csv\"");
	
	$res_query = "  SELECT
						s.bannerid,
						DATE_FORMAT(s.day, '".$date_format."') as day,
						SUM(s.views) AS adviews,
						SUM(s.clicks) AS adclicks,
						SUM(s.conversions) AS adconversions
					FROM
						".$phpAds_config['tbl_adstats']." as s,
						".$phpAds_config['tbl_banners']." as b
					WHERE
						s.bannerid=b.bannerid
						AND b.campaignid='".$campaignid."'
						AND s.day >= '".$dbStart."' 
						AND s.day <  '".$dbEnd."' 
					GROUP BY
						day
                    ORDER BY
                        DATE_FORMAT(day, '%Y%m%d')
				";
	
	$res_banners = phpAds_dbQuery($res_query) or phpAds_sqlDie();
	
	while ($row_banners = phpAds_dbFetchArray($res_banners))
	{
		$stats[$row_banners['day']]['views'] 		= $row_banners['adviews'];
		$stats[$row_banners['day']]['clicks'] 		= $row_banners['adclicks'];
		$stats[$row_banners['day']]['conversions'] 	= $row_banners['adconversions'];		
	}

	echo $strCampaign.": ".strip_tags(phpAds_getCampaignName($campaignid, true))." - ".$start." - ".$end."\n\n";
	echo $strDay.$delimiter.$strViews.$delimiter.$strClicks.$delimiter.$strCTRShort.$delimiter.$strConversions.$delimiter.$strCNVRShort."\n";
	
	$totalclicks      = 0;
	$totalviews       = 0;
	$totalconversions = 0;
	
	if (isset($stats) && is_array($stats))
	{
		for (reset($stats);$key=key($stats);next($stats))
		{
			echo $key.
				 $delimiter.
				 $stats[$key]['views'].
				 $delimiter.
				 $stats[$key]['clicks'].
				 $delimiter.
				 str_replace(',','.',phpAds_buildCTR($stats[$key]['views'], $stats[$key]['clicks'])).
				 $delimiter.
				 $stats[$key]['conversions'].
				 $delimiter.
				 str_replace(',','.',phpAds_buildCTR($stats[$key]['clicks'], $stats[$key]['conversions'])).
				 "\n";

			$totalclicks 		+= $stats[$key]['clicks'];
			$totalviews			+= $stats[$key]['views'];
			$totalconversions 	+= $stats[$key]['conversions'];
		}
	}
	
	echo "\n";
	echo $strTotal.
		 $delimiter.
		 $totalviews.
		 $delimiter.
		 $totalclicks.
		 $delimiter.
		 str_replace(',','.',phpAds_buildCTR ($totalviews, $totalclicks)).
		 $delimiter.
		 $totalconversions.
		 $delimiter.
		 str_replace(',','.',phpAds_buildCTR ($totalclicks, $totalconversions)).
		 "\n";
}

?>
