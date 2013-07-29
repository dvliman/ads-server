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
$Id: graph-history.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");


// Register input variables
phpAds_registerGlobal ('period', 'start', 'limit', 'source');


/*********************************************************/
/* Prepare for current selection                         */
/*********************************************************/

// Bannerid
if ($bannerid != '')
{
	$where[] = 'bannerid = '.$bannerid;
	
	if ($zoneid != '')
	{
		$where[] = "zoneid = ".$zoneid;
	}
}

// Campaignid
elseif ($campaignid != '')
{
	$idresult = phpAds_dbQuery ("
		SELECT
			bannerid
		FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			campaignid = '$campaignid'
	");
	
	if (phpAds_dbNumRows($idresult) > 0)
		while ($row = phpAds_dbFetchArray($idresult))
			$bannerids[] = "bannerid = ".$row['bannerid'];
	
	$where[] = "(".implode(' OR ', $bannerids).")";
}

// Clientid
elseif ($clientid != '')
{
	$idresult = phpAds_dbQuery ("
		SELECT
			b.bannerid
		FROM
			".$phpAds_config['tbl_banners']." AS b,
			".$phpAds_config['tbl_campaigns']." AS c
		WHERE
			c.clientid = $clientid AND
			c.campaignid = b.campaignid
	");
	
	if (phpAds_dbNumRows($idresult) > 0)
		while ($row = phpAds_dbFetchArray($idresult))
			$bannerids[] = "bannerid = ".$row['bannerid'];
	
	$where[] = "(".implode(' OR ', $bannerids).")";
}

// Zoneid
elseif ($zoneid != '')
{
	$where[] = "zoneid = ".$zoneid;
}

// Affiliateid
elseif ($affiliateid != '')
{
	$idresult = phpAds_dbQuery (" 
		SELECT
			zoneid
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			affiliateid = '".$affiliateid."'
	");
	
	if (phpAds_dbNumRows($idresult) > 0)
		while ($row = phpAds_dbFetchArray($idresult))
			$zoneids[] = "zoneid = ".$row['zoneid'];
	
	$where[] = "(".implode(' OR ', $zoneids).")";
}

// Convert to SQL query
if (isset($where))
	$where = implode (' AND ', $where);



/*********************************************************/
/* Prepare for selected period                           */
/*********************************************************/

if ($period == 'd')
{
	$formatted   = $date_format;
	$unformatted = "%d%m%Y";
	$returnlimit = $limit;
	
	$begin_timestamp = mktime(0, 0, 0, date('m'), date('d') - $limit + 1 - $start, date('Y'));
	$end_timestamp	 = mktime(0, 0, 0, date('m'), date('d') + 1 - $start, date('Y'));
}

if ($period == 'w')
{
	$formatted   = $date_format;
	$unformatted = "%d%m%Y";
	$returnlimit = $limit * 7;
	
	$shift = date('w') - ($phpAds_config['begin_of_week'] ? 1 - (date('w') == 0 ? 7 : 0) : 0);
	$begin_timestamp = mktime(0, 0, 0, date('m'), date('d') - $shift + 7 - (7 * ($limit + $start)), date('Y'));
	$end_timestamp   = mktime(0, 0, 0, date('m'), date('d') - $shift + 7 - (7 * $start), date('Y'));
}

if ($period == 'm')
{
	$formatted   = $month_format;
	$unformatted = "%m%Y";
	$returnlimit = $limit;
	
	$begin_timestamp = mktime(0, 0, 0, date('m') - $limit + 1 - $start, 1, date('Y'));
	$end_timestamp   = mktime(0, 0, 0, date('m') + 1 - $start, 1, date('Y'));
}



/*********************************************************/
/* Get statistics for selected period                    */
/*********************************************************/

// Get stats for selected period
$begin = date('Ymd', $begin_timestamp);
$end   = date('Ymd', $end_timestamp);

$result = phpAds_dbQuery("
	SELECT
		sum(views) AS sum_views,
		sum(clicks) AS sum_clicks,
		DATE_FORMAT(day, '".$formatted."') AS date,
		DATE_FORMAT(day, '".$unformatted."') AS date_u
	FROM
		".$phpAds_config['tbl_adstats']."
	WHERE
		day >= $begin AND day < $end
		".(isset($where) ? 'AND '.$where : '')."
	GROUP BY
		date_u
	ORDER BY
		date_u DESC
	LIMIT 
		$returnlimit
");

while ($row = phpAds_dbFetchArray($result))
{
	$stats[$row['date']]['sum_views'] = $row['sum_views'];
	$stats[$row['date']]['sum_clicks'] = $row['sum_clicks'];
}

/*********************************************************/
/* Prepare data for graph                                */
/*********************************************************/

if (isset ($GLOBALS['phpAds_CharSet']) && $GLOBALS['phpAds_CharSet'] != '')
	$text=array(
	    'value1' => 'AdViews',
	    'value2' => 'AdClicks');
else
	$text=array(
	    'value1' => $GLOBALS['strViews'],
	    'value2' => $GLOBALS['strClicks']);

if ($period == 'd' || $period == 'm')
{
	for ($d=0;$d<$limit;$d++)
	{
		switch ($period)
		{
			case 'm':	$timestamp = mktime (0, 0, 0, date('m') - $d - $start, 1, date('Y'));
						break;
					
			case 'd':	$timestamp = mktime (0, 0, 0, date('m'), date('d') - $d - $start, date('Y'));
						break;
		}
		
		$key = strftime ($formatted, $timestamp);
		
		if (isset($stats[$key]))
		{
			$items[$d]['value1'] = isset($stats[$key]['sum_views']) ? $stats[$key]['sum_views'] : 0;
			$items[$d]['value2'] = isset($stats[$key]['sum_clicks']) ? $stats[$key]['sum_clicks'] : 0;
			$items[$d]['text']   = $key;
		}
		else
		{
			$items[$d]['value1'] = 0;
			$items[$d]['value2'] = 0;
			$items[$d]['text']   = $key;
		}
	}
}


if ($period == 'w')
{
	$days = 0;
	
	for ($d=0;$d<$limit;$d++)
	{
		$totalweekviews = 0;
		$totalweekclicks = 0;
		
		$shift = date('w') - ($phpAds_config['begin_of_week'] ? 1 - (date('w') == 0 ? 7 : 0) : 0);
		$week_timestamp = mktime(0, 0, 0, date('m'), date('d') - $shift - (7 * ($d + $start)), date('Y'));
		$week_formatted = strftime("%V") != '' ? strftime ($weekiso_format, $week_timestamp + ($phpAds_config['begin_of_week'] ? 0 : (60*60*24))) : 
						  						 strftime ($week_format, $week_timestamp + ($phpAds_config['begin_of_week'] ? 0 : (60*60*24)));
		
		for ($i = 0; $i < 7; $i++)
		{
			$day_timestamp = $week_timestamp + ($i * (60 * 60 * 24));
			$key = strftime ($formatted, $week_timestamp + ($i * (60 * 60 * 24)));
			
			if (isset($stats[$key]))
			{
				$totalweekviews  += isset($stats[$key]['sum_views']) ? $stats[$key]['sum_views'] : 0;
				$totalweekclicks += isset($stats[$key]['sum_clicks']) ? $stats[$key]['sum_clicks'] : 0;
				$days++;
			}
		}
		
		$items[$d]['value1'] = $totalweekviews;
		$items[$d]['value2'] = $totalweekclicks;
		$items[$d]['text']   = $week_formatted;
	}
}


// Build the graph
include('lib-graph.inc.php');

?>