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
$Id: campaignhistory.plugin.php 746 2004-10-07 09:37:35Z andrew $
*/


// Public name of the plugin info function
$plugin_info_function		= "Plugin_UnderdeliveringCampaignsInfo";


// Public info function
function Plugin_UnderdeliveringCampaignsInfo()
{
	global $strPluginUnderdeliveringCampaigns, $strUnderdeliveringCampaigns, $strDelimiter, $strDate;
	
	$plugininfo = array (
		"plugin-name"			=> $strUnderdeliveringCampaigns,
		"plugin-description"		=> $strPluginUnderdeliveringCampaigns,
		"plugin-author"			=> "Chris Nutting",
		"plugin-export"			=> "csv",
		"plugin-authorize"		=> phpAds_Admin+phpAds_Agency+phpAds_Client,
		"plugin-execute"		=> "Plugin_UnderdeliveringCampaignsExecute",
		"plugin-import"			=> array (
			"delimiter"		=> array (
				"title"					=> $strDelimiter,
				"type"					=> "edit",
				"size"					=> 1,
				"default"				=> "," 
				),
			"stats_date"		=> array (
				"title"					=> $strDate,
				"type"					=> "edit",
				"size"					=> 10,
				"default"				=> date("Y/m/d", (time() - (24*60*60))) 
			)
		)
	);
		
	
	return ($plugininfo);
}



/*********************************************************/
/* Private plugin function                               */
/*********************************************************/

function Plugin_UnderdeliveringCampaignsExecute($delimiter=",", $stats_date)
{
	global $phpAds_config, $date_format;
	global $strCampaign, $strTotal, $strDay, $strViews, $strClicks, $strCTRShort, $strConversions, $strCNVRShort;
	
	// Format the start and end dates
    $dbStart = date("Y-m-d", strtotime($start));
    $dbEnd   = date("Y-m-d", strtotime($end));
    $start = date("Y/m/d", strtotime($start));
    $end   = date("Y/m/d", strtotime($end));
	
    $stats_date = str_replace("/", "", $stats_date);
    
	header ("Content-type: application/csv\nContent-Disposition: inline; filename=\"underdelivering_".$stats_date.".csv\"");
	
	$clientid = phpAds_getUserID();
	$yesterday = date("Ymd", (time() - 24*60*60));
	
	$res_query = "
    SELECT 
       a.name AS agency,
	   cl.clientname AS advertiser,
	   c.campaignname AS campaign,
	   t.campaignid AS campaignid,
	   t.target AS target,
	   t.views AS views
    FROM
	   ".$phpAds_config['tbl_targetstats']." AS t,
	   ".$phpAds_config['tbl_campaigns']." AS c,
	   ".$phpAds_config['tbl_clients']." AS cl,
	   ".$phpAds_config['tbl_agency']." AS a
    WHERE
	   a.agencyid=cl.agencyid
	       AND
	   c.clientid=cl.clientid
	       AND
	   t.campaignid=c.campaignid
	       AND
	   t.views < t.target";
	
	$res_query .= (phpAds_isUser(phpAds_Client)) ? " AND c.clientid = ".$clientid : '';
	$res_query .= (phpAds_isUser(phpAds_Agency)) ? " AND a.agencyid = ".$clientid : '';
	
	$res_query .= " AND t.day = $stats_date
    ORDER BY
	   a.agencyid,
	   cl.clientid
	";
	
	$res_banners = phpAds_dbQuery($res_query) or phpAds_sqlDie();

	echo "Agency".$delimiter."Advertiser".$delimiter."Campaign".$delimiter."Campaign ID".$delimiter."Target".$delimiter."Views".$delimiter."Percentage\n";
	
	while ($row_banners = phpAds_dbFetchArray($res_banners))
	{
		echo $row_banners['agency'].$delimiter;
		echo $row_banners['advertiser'].$delimiter;
		echo $row_banners['campaign'].$delimiter;
		echo $row_banners['campaignid'].$delimiter;
		echo $row_banners['target'].$delimiter;
        echo $row_banners['views'].$delimiter;
        printf ('%0.2f', (($row_banners['views'] / $row_banners['target']) * 100));
        echo "%"."\n";
	}
}

?>
