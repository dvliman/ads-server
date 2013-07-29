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
$Id: maintenance-autotargeting.php 3145 2005-05-20 13:15:01Z andrew $
*/

if (!defined('phpAds_LastMidnight'))
	define('phpAds_LastMidnight', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
if (!defined('phpAds_DowToday'))
	define('phpAds_DowToday', date('w'));

// Set defaults
$report = '';

// Save views into targetstats table
phpAds_TargetStatsSaveViews();

// Get campaigns that need autotargeting
$res = phpAds_dbQuery(
	"SELECT".
	" campaignid".
	",campaignname".
	",views".
	",UNIX_TIMESTAMP(expire) AS expire".
	" FROM ".$conf['table']['campaigns'].
	" WHERE active='t'".
	" AND expire>NOW()".
	" AND views>0".
	" AND weight=0".
	" ORDER BY campaignid"
);

if (phpAds_dbNumRows($res)) {
	// Autotargeting needed
	$report .= "==================================================\n";
	$report .= "AUTOTARGETING STARTED\n";
	$report .= "==================================================\n\n";
	$report .= "--------------------------------------------------\n";
	$report .= "Smoothing factor calculation ";

	// Prepare the average view profile
	$profile = phpAds_AutoTargetingPrepareProfile();
	
	// Calculate the factor to use on sunday or if it's disabled
	if (!date('w') || !isset($conf['autotarget_factor']) || $conf['autotarget_factor'] == -1)
	{
		$report .= "started\n";
		$report .= "--------------------------------------------------\n";
		
		$conf['autotarget_factor'] = phpAds_AutoTargetingCaclulateFactor($profile, $report);
		
		// Save factor into db
		phpAds_dbQuery(
			"UPDATE ".$conf['table']['config'].
			" SET autotarget_factor = ".$conf['autotarget_factor'].
			" WHERE agencyid = 0"
		);
	} elseif (!phpAds_AutoTargetingProfileSum($profile)) {
		// Disable if a null profile was supplied
		$conf['autotarget_factor'] = -1;
		
		$report .= "skipped: supplied profile is null\n\n";
	} else {
		$report .= "skipped: already set\n\n";
    }

	$report .= "--------------------------------------------------\n";
	$report .= "Smoothing factor:               ".sprintf('%.2f', $conf['autotarget_factor'])."\n";
	$report .= "Today dow:                      ".phpAds_DowToday."\n";
	$report .= "Today profile value:            ".$profile[phpAds_DowToday]."\n";
	
	if ($conf['autotarget_factor'] != -1) {
		// Targets should not be fully satisfied if using plain autotargeting
		// Smoothing the view profile for later use
		$profile = phpAds_AutoTargetingSmoothProfile($profile, $conf['autotarget_factor']);

		$report .= "Today smoothed profile value:   ".$profile[phpAds_DowToday]."\n";
	}
	$report .= "--------------------------------------------------\n\n";
	while ($row = phpAds_dbFetchArray($res)) {
		$target = phpAds_AutoTargetingGetTarget($profile, $row['views'], $row['expire'], $conf['autotarget_factor']);
		
		if (is_array($target)) {
			list($target, $debuglog) = $target;
		} else {
			$debuglog = 'no debug info available';
		}
		phpAds_dbQuery(
			"UPDATE ".$conf['table']['campaigns'].
			" SET target=".$target.
			" WHERE campaignid=".$row['campaignid']
		);
		
		$report .= "\n<b>$row[campaignname] [id$row[clientid]]:</b> $target $debuglog\n\n";
	}
}

if ($report != '' && $conf['userlog_priority']) {
	phpAds_userlogAdd(phpAds_actionPriorityAutoTargeting, 0, $report);
}

function phpAds_AutoTargetingGetTarget($profile, $views, $expire, $factor)
{
	$days = round(($expire - phpAds_LastMidnight) / (double)(60*60*24));
	$daily_target = ceil($views / $days);

	if ($days == 1) {
		// Quite easy... use remaining views as last day target
		return array($views, '(last day, using remaining views)');
    }
	if ($days <= 0) {
		// Campaign expired
		return array(0, '- campaign is expired, is there a bug?');
    }
	if ($factor == -1) {
		// Use old style average targeting
		return array($daily_target, '(average targeting)');
    }
	$weekly_views = phpAds_AutoTargetingProfileSum($profile);
	$avg_views = round ($weekly_views / 7);
	$expire_dow = date('w', $expire);

	$debuglog = "\n--------------------------------------------------\n";
	$debuglog .= "  - Days remaining:             $days\n";
	$debuglog .= "  - Expire dow:                 $expire_dow\n";

	if ($days < 7) {
		$debuglog .= "  - Less the 7 days remaining...\n";
		if (phpAds_DowToday > $expire_dow) {
			// Expire date is after the week end
			$predicted_views = phpAds_AutoTargetingProfileSum($profile, 7, phpAds_DowToday) +
				phpAds_AutoTargetingProfileSum($profile, $expire_dow);
		} else {
			$predicted_views = phpAds_AutoTargetingProfileSum($profile, $expire_dow, phpAds_DowToday);
		}
		if ($predicted_views) {
			$boost = $profile[phpAds_DowToday] / $predicted_views;
			$debuglog .= "  - Remaining views assigned:   ".sprintf('%.2f', $boost*100)."%\n";

			$target = round($views * $boost);
		} else {
			$target = 0;
        }
	} elseif ($profile[phpAds_DowToday] && $avg_views > 0) {
		$boost = $profile[phpAds_DowToday] / $avg_views;

		$debuglog .= "  - Weekly views:               $weekly_views\n";
		$debuglog .= "  - Avg. views:                 $avg_views\n";
		$debuglog .= "  - Boost factor:               ".sprintf('%.2f', $boost*100-100)."%\n";
		$last_week_predicted = 0;
		for ($i = 0; $i < $expire_dow; $i++) {
			$last_week_predicted += round($daily_target * $profile[$i] / $avg_views);
        }
		$debuglog .= "  - Last week predicted:        $last_week_predicted\n";
		if ($last_week_predicted) {
			$lost_views = $daily_target * $expire_dow - $last_week_predicted;
			$debuglog .= "  - Views compensation:         $lost_views\n";
			$debuglog .= "  - Avg. compensation:          ".round($lost_views/$days)."\n";
		}
		$target = round($daily_target * $boost + $lost_views / $days);
	} else {
		$debuglog .= "  - Using default autotargeting\n";
		$target = $daily_target;
	}
	return array($target, $debuglog);
}

/*********************************************************/
/* Prepare the views profile for autotargeting           */
/*********************************************************/

function phpAds_AutoTargetingPrepareProfile($weeks = 2)
{
	global $conf, $phpAds_dbmsname;
	$profile = array(0, 0, 0, 0, 0, 0, 0);
	// Get the number of days running
	$res = phpAds_dbQuery("SELECT UNIX_TIMESTAMP(MIN(day)) AS days_running FROM ".$conf['table']['adstats']." WHERE day > 0 AND hour > 0 ORDER BY day LIMIT 1");
	$days_running = phpAds_dbResult($res, 0, 'days_running');

	if ($days_running > 0) {
		$now = mktime (0, 0, 0, date('m'), date('d'), date('Y'));
		$days_running = $now - $days_running + (date('I', $days_running) - date('I', $now)) * 60;
		$days_running = round ($days_running / (60 * 60 * 24)) - 1;
	}
	if ($days_running < $weeks * 7) {
		if ($days_running < 7)
			// Not enough stats
			return $profile;
		else
			// Use only the available weeks
			$weeks = floor($days_running / 7);
	}

	$begin   = date('Ymd', phpAds_makeTimestamp(phpAds_LastMidnight, - (60 * 60 * 24 * 7 * $weeks)));
	$end   = date('Ymd', phpAds_makeTimestamp(phpAds_LastMidnight));
	
	$res_views = phpAds_dbQuery("
		SELECT
			SUM(views) AS sum_views,
			DATE_FORMAT(day, '%w') AS dow
		FROM
			".$conf['table']['adstats']."
		WHERE
			day >= $begin AND
			day < $end
		GROUP BY
			dow
		ORDER BY
			dow
		");
	// Fill profile
	while ($row = phpAds_dbFetchArray($res_views)) {
		// PostgreSQL dow starts with 1 instead of 0
		if ($phpAds_dbmsname == "PostgreSQL") {
			$row['dow']--;
		}
		$profile[$row['dow']] =	round ($row['sum_views'] / $weeks);
	}
	// Use a 9 days profile to make calculations easier
	$profile[-1] = $profile[6]; // Saturday
	$profile[7]  = $profile[0]; // Monday
	
	return $profile;
}

/*********************************************************/
/* Smooth profile using weighted averages               */
/*********************************************************/

function phpAds_AutoTargetingSmoothProfile($profile, $factor)
{
	if ($factor < 0 || $factor > 1) {
		return $profile;
	}
	for($i = 0; $i < 7; $i++) {
		$smooth_profile[$i] = round($factor * $profile[$i] + (1 - $factor) * ($profile[$i-1]+$profile[$i+1]) / 2);
	}
	return $smooth_profile;
}

/*********************************************************/
/* Return total views for profile                        */
/*********************************************************/

function phpAds_AutoTargetingProfileSum($profile, $end = 7, $start = 0)
{
	$views = 0;
	for($i = $start; $i < $end; $i++) {
		$views += $profile[$i];
	}
	return $views;
}

/*********************************************************/
/* Return total actual views for profile                 */
/*********************************************************/

function phpAds_AutoTargetingActualViews($real_profile, $profile)
{
	$views = 0;

	for($i = 0; $i < 7; $i++) {
		$views += $real_profile[$i] < $profile[$i] ? $real_profile[$i] : $profile[$i];
	}
	return $views;
}

/*********************************************************/
/* Find best smoothing factor to use                     */
/*********************************************************/

function phpAds_AutoTargetingCaclulateFactor($profile, &$debuglog)
{
	global $conf;
	$debuglog .= "\n";
	
	// Use standard routine if the profile is empty
	if (!phpAds_AutoTargetingProfileSum($profile)) {
		$debuglog .= "Supplied profile is null, using default algorithm\n";
		return -1;
	}
	$target_profile = array(0, 0, 0, 0, 0, 0, 0);
	$autotarget_campaigns = 0;
	
	// Fetch all targeted campaigns and all which need autotargeting
	$res = phpAds_dbQuery(
		"SELECT campaignid".
		",views".
		",UNIX_TIMESTAMP(expire) AS expire".
		",target".
		" FROM ".$conf['table']['campaigns'].
		" WHERE active".
		" AND weight = 0".
		" AND ((expire > NOW() AND views > 0) OR target > 0)"
	);
	$debuglog .= "Targeted campaigns: ". phpAds_dbNumRows($res)."\n";
	
	while ($row = phpAds_dbFetchArray($res)) {
		$debuglog .= "\nCAMPAIGN ".$row['campaignid']." \n";
		$debuglog .= "--------------------------------------------------\n";
		if ($row['expire']) {
			// Expire date set, get remaining days
			$days = ($row['expire'] - phpAds_LastMidnight) / (double)(60*60*24);
			$debuglog .= "Days remaining: $days\n";
			
			if ($days <= 0) {
				continue;
			}
			if ($row['views']) {
				// Bought views set, use standard autotargeting
				$daily_target = ceil($row['views'] / $days);
				$autotarget_campaigns++;
				
				$debuglog .= "Campaign type: Autotargeting\n";
				$debuglog .= "Remaining AdViews: ".$row['views']."\n";
				$debuglog .= "Daily target: ".$daily_target."\n";
			} elseif ($row['target']) {
				// Use target field
				$daily_target = $row['target'];
				
				$debuglog .= "Campaign type: Expiring high-pri\n";
				$debuglog .= "Daily target: ".$daily_target."\n";
			} else {
				// Skip campaign
				continue;
            }
		} else {
			// Expire date not set, target field is needed
			if (!$row['target']) {
				// Skip campaign
				continue;
            }
			$daily_target = $row['target'];
			$debuglog .= "Daily target: ".$daily_target."\n";
			if ($row['views']) {
				$debuglog .= "Campaign type: AdView based high-pri\n";
				// Bought views set, get remaining days using the current target
				$days = ceil($row['views'] / $daily_target);
				
				$debuglog .= "Remaining AdViews: ".$row['views']."\n";
			} else {
				// Set target for the whole week
				$days = 7;
				$debuglog .= "Campaign type: Default high-pri\n";
			}
			$debuglog .= "Days remaining: ".$days."\n";
		}
		// Add this campaign to the targets profile
		for ($i = 0; $i < $days && $i < 7; $i++) {
			$target_profile[$i] += $daily_target;
        }
	}
	
	$debuglog .= "\nAutotargeted campaigns: ".$autotarget_campaigns."\n";
	$debuglog .= "--------------------------------------------------\n";

	if (!$autotarget_campaigns) {
		// No autotargeting is needed, we can use default autotargeting safely
		$debuglog .= "No autotargeting needed, using default algorithm\n";
		return -1;
	}
	
	// Get the daily target
	$daily_target = phpAds_AutoTargetingProfileSum($target_profile);
	
	// Set default routine as best match
	$best = array(
			'factor' => array(-1),
			'views' => phpAds_AutoTargetingActualViews($profile, $target_profile)
		);
	// Try factors - don't know why, but on my setup $x <= 1 skips $x == 1...
	for ($x = 0; $x <= 1.01; $x += .05) {
		// Smooth profile using the current factor and get the view sum
		$smooth_profile = phpAds_AutoTargetingSmoothProfile($profile, $x);
		$views = phpAds_AutoTargetingProfileSum($smooth_profile);
		
		// Skip if the profile is empty
		if (!$views)
			continue;
		// Fill the whieighted profile
		$new_profile = array();
		for ($i = 0; $i < 7; $i++) {
			$new_profile[$i] =  round($daily_target / $views * $smooth_profile[$i]);
		}
		// Get actual views that could be reached using this profile
		$act_views = phpAds_AutoTargetingActualViews($profile, $new_profile);
		
		if ($act_views > $best['views']) {
			// Best factor found til now
			$best['factor'] = array($x);
			$best['views'] = $act_views;
		} elseif ($act_views == $best['views']) {
			// Add x to the best factors found til now
			$best['factor'][] = $x;
		}
	}
	if (is_array($best['factor'])) {
		// Get average factor
		$factor = 0;
		while (list(, $v) = each($best['factor'])) {
			$factor += $v;
        }
		$best['factor'] = round($factor*100/count($best['factor']))/100;
	}
	$debuglog .= "\n";
	return $best['factor'];
}

/*********************************************************/
/* Save views in targetstats table                       */
/*********************************************************/

function phpAds_TargetStatsSaveViews()
{
	global $conf;
	$campaigns	= array();

	// Get total views
	$res = phpAds_dbQuery(
		"SELECT SUM(views) AS sum_views".
		",day".
		" FROM ".$conf['table']['adstats'].
		" WHERE day=DATE_SUB(NOW(), INTERVAL 1 DAY)".
		" GROUP BY day"
	);
	$sum_views = phpAds_dbResult($res, 0, 'sum_views');
	$day = phpAds_dbResult($res, 0, 'day');
	$totalviews = 0;
	$res = phpAds_dbQuery(
		"SELECT campaignid".
		" FROM ".$conf['table']['targetstats'].
		" WHERE day='".$day."'".
		" AND campaignid>0"
	);
	while ($row = phpAds_dbFetchArray($res)) {
		$query =
			"SELECT SUM(views) AS sum_views".
			" FROM ".$conf['table']['adstats']." AS v".
			",".$conf['table']['banners']." AS b".
			" WHERE v.day='".$day."'".
			" AND b.bannerid = v.bannerid".
			" AND b.campaignid = ".$row['campaignid']
		;
		$views = (int)phpAds_dbResult(phpAds_dbQuery($query), 0, 'sum_views');
		$totalviews += $views;
		$campaigns[$row['campaignid']] = $views;
	}
	$campaigns[0] = $sum_views - $totalviews;
	while (list($campaignid, $views) = each($campaigns)) {
		if ($campaignid) {
			phpAds_dbQuery(
				"UPDATE ".$conf['table']['targetstats'].
				" SET views=".$views.
				" WHERE campaignid=".$campaignid.
				" AND day='".$day."'"
			);
		} else {
			phpAds_dbQuery(
				"INSERT INTO ".$conf['table']['targetstats'].
				" (day, campaignid, target, views)".
				" VALUES ('".$day."',".$campaignid.",0,".$views.")"
			);
		}
	}
}
?>