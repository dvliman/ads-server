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
$Id: trackerhistory.plugin.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Public name of the plugin info function 
$plugin_info_function		= "Plugin_TrackerHistoryInfo";


// Public info function
function Plugin_TrackerHistoryInfo()
{
	global $strCampaignHistory, $strClient, $strCampaign, $strPluginCampaign, $strDelimiter, $strStartDate, $strEndDate;
	
	$plugininfo = array (
		"plugin-name"			=> "Tracker History",
		"plugin-description"	=> $strPluginCampaign,
		"plugin-author"			=> "Luis",
		"plugin-export"			=> "csv",
		"plugin-authorize"		=> phpAds_Admin+phpAds_Agency,
		"plugin-execute"		=> "Plugin_TrackerHistoryExecute",
		"plugin-import"			=> array (
			"clientid"			=> array (
				"title"					=> $strClient,
				"type"					=> "clientid-dropdown" ),
			"start"		=> array (
				"title"					=> $strStartDate,
				"type"					=> "edit",
				"size"					=> 10,
				"default"				=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-7,  date("Y")))),
			"end"		=> array (
				"title"					=> $strEndDate,
				"type"					=> "edit",
				"size"					=> 10,
				"default"				=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-1,  date("Y")))),
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

function Plugin_TrackerHistoryExecute($clientid, $start, $end, $delimiter=",")
{
	global $phpAds_config, $date_format;
	global $strCampaign, $strTotal, $strDay, $strViews, $strClicks, $strCTRShort;
	
    // Format the start and end dates
    $dbStart = date("Ymd000000", strtotime($start));
    $dbEnd   = date("Ymd235959", strtotime($end));
	
	header ("Content-type: application/csv\nContent-Disposition: inline; filename=\"trackerhistory.csv\"");
	
	
	// get all trackers and group them by advertiser and campaign
	
		$res_trackers = phpAds_dbQuery("SELECT
										trackers.trackerid,
										trackers.trackername
									FROM
										".$phpAds_config['tbl_trackers']." as trackers
									WHERE
										trackers.clientid = ".$clientid."
									");

		$trackers = array();

		while ($row = phpAds_dbFetchArray($res_trackers)) {
			$trackers[$row['trackerid']] = array();
			$trackers[$row['trackerid']]['name'] = $row['trackername'];
		}

		$res_total_conversions = phpAds_dbQuery("SELECT
											trackers.trackerid,
											count(conversions.conversionid) as hits
										FROM
											".$phpAds_config['tbl_adconversions']." as conversions,
											".$phpAds_config['tbl_trackers']." as trackers
										WHERE
											trackers.trackerid = conversions.trackerid
											AND trackers.clientid = ".$clientid."
											AND conversions.t_stamp >= '".$dbStart."'
											AND conversions.t_stamp <= '".$dbEnd."'
										GROUP BY
											conversions.trackerid
								");

		while ($row = phpAds_dbFetchArray($res_total_conversions))
			$trackers[$row['trackerid']]['total_conversions'] = $row['hits'];

		$res_conversions = phpAds_dbQuery("SELECT
											trackers.trackerid,
											count(*) as hits
										FROM
											".$phpAds_config['tbl_conversionlog']." as conversions,
											".$phpAds_config['tbl_trackers']." as trackers
										WHERE
											trackers.trackerid = conversions.trackerid
											AND trackers.clientid = ".$clientid."
											AND conversions.t_stamp >= '".$dbStart."'
											AND conversions.t_stamp <= '".$dbEnd."'
											AND cnv_latest = 1
											
										GROUP BY
											conversions.trackerid
								");

		while ($row = phpAds_dbFetchArray($res_conversions))
			$trackers[$row['trackerid']]['conversions'] = $row['hits'];

	//echo "<pre>";
	//print_r($trackers);
	//echo "</pre>";

	echo "Client: ".strip_tags(phpAds_getClientName($clientid))." - ".$start." - ".$end."\n\n";
	

	echo 	$GLOBALS['strName'].$delimiter.
			$GLOBALS['strID'].$delimiter.
			"Conversions".$delimiter.
			"Total Hits"."\n";									
	echo "\n";


	foreach($trackers as $id=>$tracker)
	{
			echo 	$tracker['name'].$delimiter.
					$id.$delimiter.
					$tracker['conversions'].$delimiter.
					$tracker['total_conversions'].$delimiter."\n";

	}
	
	
	
}

?>
