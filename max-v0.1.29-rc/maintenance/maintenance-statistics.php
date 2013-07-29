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
$Id: maintenance-statistics.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Maintenance Statistics requires the PEAR Date class
include_once 'Date.php';

// Consolidate the data from the adviews, adclicks and adconversions tables into the adstats table.

// The timeframes that are processed starts with the most recent hour processed from adstats,
// and ends with the last completed hour in adviews.

$report = '';

// Process Statistics...
$report .= "==================================================\n";
$report .= "BATCH STATISTICS STARTED\n";
$report .= "==================================================\n\n";
$report .= "--------------------------------------------------\n";

$done = false;

while (!$done) {
    $time_query = '
        SELECT  DATE_FORMAT(DATE_ADD(statslastday, INTERVAL statslasthour HOUR), "%Y%m%d%H%i%s") AS start_timestamp,
                DATE_FORMAT(DATE_ADD(statslastday, INTERVAL statslasthour+1 HOUR),"%Y%m%d%H%i%s") AS end_timestamp,
                statslastday AS start_day,
                statslasthour AS start_hour,
                NOW() AS exact_time,
                DATE_FORMAT(NOW(), "%Y%m%d%H%i%s") AS exact_timestamp
         FROM ' . $conf['table']['config'];
    $time_result = phpAds_dbQuery($time_query)
        or $report.= "Could not perform SQL: ".$time_query."\n"."SQL Error: ".mysql_error()."\n";

    debug('Start and end timestamps selected');

    if ($time_row = phpAds_dbFetchArray($time_result)) {
        $begin_timestamp = $time_row['start_timestamp'];
        $end_timestamp = $time_row['end_timestamp'];
        $day = $time_row['start_day'];
        $hour = $time_row['start_hour'];
        $exact_timestamp = $time_row['exact_timestamp'];
        $exact_time = $time_row['exact_time'];
        
        $report .= "Checking for statistics...  The current time is " . $exact_time . "\n";
        $report .= "\tThe last hour that statistics were compiled was up to " . $hour . " on " . $day . ".\n\n";

        // If Max is newly installed, need to set the start and end timestamps
        // based on the first impressions in the adviews table...
        if ($day == '0000-00-00') {
            // There is no code for the split tables case - you'll have to
            // setup config with the right date if you want to run split
            // tables...
            if (!$conf['split_tables']) {
                $query = "
                    SELECT
                        DATE_FORMAT(t_stamp, \"%Y%m%d%H%i%s\") as start_timestamp
                    FROM
                        {$conf['table']['adviews']}
                    LIMIT 1";
                $res = phpAds_dbQuery($query)
                    or $report .= "Could not perform SQL: ".$query."\n"."SQL Error: ".mysql_error()."\n";
                if ($time_row = phpAds_dbFetchArray($res)) {
                    $start_date = new Date($time_row['start_timestamp']);
                    $start_date->setMinute(00);
                    $start_date->setSecond(00);
                    $begin_timestamp = $start_date->format("%Y%m%d%H%M%S");
                    $end_date = $start_date;
                    $end_date->addSeconds(3600);
                    $end_timestamp = $end_date->format("%Y%m%d%H%M%S");
                    $day = $start_date->format("%Y-%m-%d");
                    $hour = $start_date->format("%H");
                } else {
                    $done = true;
                    $report .= "No entries in the adviews table, so cannot set the initial start time for statistics.\n";
                    debug("No entries in the adviews table, so cannot set the initial start time for statistics.");
                }
            }
        }
        
        if ((!$done) && ($exact_timestamp >= $end_timestamp)) {
            debug('Current time is at least one hour after last batch job');
            $report .= "Processing statistics for hour ".$hour." on ".$day."...\n\n";
            if (!phpAds_checkStatsExist($day, $hour, $report)) {
                phpAds_processStats($begin_timestamp, $end_timestamp, $day, $hour, $report);
            } else {
                $report .= "Statistics already exist for hour " . $hour . " on " . $day . ".  Please delete the statistics if you would like to regenerate.\n";
            }
            // Now that everything is done, update the stats generation date/time.
            phpAds_logStatsDate($end_timestamp);
        } else {
            $done = true;
            debug('Current time is not at least one hour after last batch job');
        }
    } else {
        $done = true;
    }
}

$report .= "No more statistics to compile.\n";

// Write the output to the user log.
phpAds_userlogAdd(phpAds_actionBatchStatistics, 0, $report);

function phpAds_processStats($begin_timestamp, $end_timestamp, $day, $hour)
{
    global $report;
    debug('We are running stats functions in phpAds_processStats()');
    // If we are rebuilding a particular hour,
    // then increment back any inventory to campaigns and delete the stats for this hour
    $regen = false; // Will build later...
    if ($regen) {
        $report .= "\t REGENERATING!!/n/n";
        phpAds_undoInventory($day, $hour);
        phpAds_deleteCompactStats($day, $hour);
    }
    // Count the total views for this hour
    phpAds_countViews($begin_timestamp, $end_timestamp);
    // Count the total clicks for this hour
    phpAds_countClicks($begin_timestamp, $end_timestamp);
    // Count the total conversions for this hour
    phpAds_countConversions($begin_timestamp, $end_timestamp, $day, $hour);
    // Decrement the campaigns with our new statistics
    phpAds_decrementCampaigns($day, $hour);
    // Clean up (if user wants...)
    phpAds_deleteVerboseStats($begin_timestamp, $end_timestamp);
    
}

function phpAds_countViews($begin_timestamp, $end_timestamp)
{
    global $conf, $report;
    
    $time = time();
    debug('Running the 1st job: phpAds_countViews()');
    debug('Counting the views between ' . $begin_timestamp . ' and ' . $end_timestamp);
    $report .= "\tCounting the views between ".$begin_timestamp." and ".$end_timestamp."...\n";
    if ($conf['split_tables']) {
        
        // The adviews table is actually split into multiple daily tables
        // Create a temporary adviews table to select adviews into
        $query = "
            CREATE TEMPORARY TABLE
                tmp_adviews
                (
                    day date,
                    hour int,
                    bannerid int,
                    zoneid int,
                    views int
                )";
        debug(' - phpAds_countViews() - Create temporary table tmp_adviews: Started');
        $res = phpAds_dbQuery($query)
            or $report .= "Could not perform SQL: ".$query."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countViews() - Create temporary table tmp_adviews: Ended');  
        
        // For each day that the views may be in, select adviews into the temporary 
        // table from the start time to the end time, aggregating on the day, hour,
        // bannerid and zoneid
        $startDate = new Date($begin_timestamp);
        $endDate = new Date($end_timestamp);
        $days = Date_Calc::dateDiff($startDate->getDay(), $startDate->getMonth(), $startDate->getYear(), $endDate->getDay(), $endDate->getMonth(), $endDate->getYear());
        $daysCounter = 0;
        $currentDate = $startDate;
        while ($daysCounter <= $days) {
            
            $adviews_table = $conf['table']['adviews'] . '_' . $currentDate->format('%Y%m%d');
            $query = "
                INSERT INTO tmp_adviews
                    (
                        day,
                        hour,
                        bannerid,
                        zoneid,
                        views
                    )
                SELECT
                    DATE_FORMAT(t_stamp, '%Y-%m-%d') as day,
                    DATE_FORMAT(t_stamp, '%k') as hour,
                    bannerid,
                    zoneid,
                    COUNT(*) as views
                FROM
                    $adviews_table
                WHERE
                    t_stamp >= $begin_timestamp
                    AND t_stamp < $end_timestamp
                GROUP BY
                    day, hour, bannerid, zoneid";
            debug(" - phpAds_countViews() - Insert into temporary table tmp_adviews from table $adviews_table: Started");
            $res = phpAds_dbQuery($query)
                or $report .= "Could not perform SQL: ".$query."\n"."SQL Error: ".mysql_error()."\n";
            debug(" - phpAds_countViews() - Insert into temporary table tmp_adviews from table $adviews_table: Ended");
            
            // Update the day counter, and the "current" date
            $daysCounter++;
            $currentDate = $currentDate->getNextDay();
            
        }
        
        $view_query = "
            SELECT
                day,
                hour,
                bannerid,
                zoneid,
                views
            FROM
                tmp_adviews";
        debug(" - phpAds_countViews() - Selecting views from the temporary table tmp_adviews: Started");
        $view_result = phpAds_dbQuery($view_query)
            or $report.= "Could not perform SQL: ".$view_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(" - phpAds_countViews() - Selecting views from the temporary table tmp_adviews: Ended");
        
    } else {
        
        // No splitting, just one big adviews table
        // Select adviews from the start time to the end time, aggregating on 
        // the day, hour, bannerid and zoneid
        $view_query = "
            SELECT
                DATE_FORMAT(t_stamp, '%Y-%m-%d') as day,
                DATE_FORMAT(t_stamp, '%k') as hour,
                bannerid,
                zoneid,
                COUNT(*) AS views
            FROM
                {$conf['table']['adviews']}
            WHERE
                t_stamp >= $begin_timestamp
                AND t_stamp < $end_timestamp
            GROUP BY
                day, hour, bannerid, zoneid";
        debug(" - phpAds_countViews() - Selecting views from the adviews table: Started");
        $view_result = phpAds_dbQuery($view_query)
            or $report.= "Could not perform SQL: ".$view_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(" - phpAds_countViews() - Selecting views from the adviews table: Ended");
        
    }
    
    // Insert the selected, aggregated views
    $num_views = 0;
    debug(" - phpAds_countViews() - Inserting aggregate views into the adstats table: Started");
    while ($view_row = phpAds_dbFetchArray($view_result)) {
        $stat_query = 'INSERT INTO ' . $conf['table']['adstats'] ." SET
                        day = '" . $view_row['day'] . "'," . '
                        hour = ' . $view_row['hour'] . ',
                        bannerid = ' . $view_row['bannerid'] . ',
                        zoneid = ' . $view_row['zoneid'] . ',
                        views = ' . $view_row['views'];
        $stat_result = phpAds_dbQuery($stat_query)
            or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
        if (phpAds_dbAffectedRows($stat_result) < 1) {
            debug("   - phpAds_countViews() - Unable to insert - using update instead.");
            $stat_query = "UPDATE " . $conf['table']['adstats'].
                            " SET views=views+".$view_row['views'].
                            " WHERE day='".$view_row['day']."'".
                            " AND hour=".$view_row['hour'].
                            " AND bannerid=".$view_row['bannerid'].
                            " AND zoneid=".$view_row['zoneid'];
            $stat_result = phpAds_dbQuery($stat_query)
                or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
        }
        $num_views += $view_row['views'];
    }
    debug(" - phpAds_countViews() - Inserting aggregate views into the adstats table: Ended");            
    $report .= "\tCounted ".$num_views." views in ".(time()-$time)." seconds.\n";

    if ($conf['split_tables']) {

        // Drop the temporary table
        $sql = "DROP TEMPORARY TABLE tmp_adviews";
        debug(' - phpAds_countViews() - Drop temporary table tmp_adviews: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countViews() - Drop temporary table tmp_adviews: Ended');
        
    }
    
}

function phpAds_countClicks($begin_timestamp, $end_timestamp)
{
    global $conf, $report;
    
    $time = time();
    debug('Running the 2nd job: phpAds_countClicks()');
    debug('Counting the clicks between ' . $begin_timestamp . ' and ' . $end_timestamp);
    $report .= "\tCounting the clicks between ".$begin_timestamp." and ".$end_timestamp."...\n";
    if ($conf['split_tables']) {
        
        // The adclicks table is actually split into multiple daily tables
        // Create a temporary adclicks table to select adclicks into
        $query = "
            CREATE TEMPORARY TABLE
                tmp_adclicks
                (
                    day date,
                    hour int,
                    bannerid int,
                    zoneid int,
                    clicks int
                )";
        debug(' - phpAds_countClicks() - Create temporary table tmp_adclicks: Started');
        $res = phpAds_dbQuery($query)
            or $report .= "Could not perform SQL: ".$query."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countClicks() - Create temporary table tmp_adclicks: Ended');  
        
        // For each day that the clicks may be in, select adclicks into the temporary 
        // table from the start time to the end time, aggregating on the day, hour,
        // bannerid and zoneid
        $startDate = new Date($begin_timestamp);
        $endDate = new Date($end_timestamp);
        $days = Date_Calc::dateDiff($startDate->getDay(), $startDate->getMonth(), $startDate->getYear(), $endDate->getDay(), $endDate->getMonth(), $endDate->getYear());
        $daysCounter = 0;
        $currentDate = $startDate;
        while ($daysCounter <= $days) {
            
            $adclicks_table = $conf['table']['adclicks'] . '_' . $currentDate->format('%Y%m%d');
            $query = "
                INSERT INTO tmp_adclicks
                    (
                        day,
                        hour,
                        bannerid,
                        zoneid,
                        clicks
                    )
                SELECT
                    DATE_FORMAT(t_stamp, '%Y-%m-%d') as day,
                    DATE_FORMAT(t_stamp, '%k') as hour,
                    bannerid,
                    zoneid,
                    COUNT(*) as clicks
                FROM
                    $adclicks_table
                WHERE
                    t_stamp >= $begin_timestamp
                    AND t_stamp < $end_timestamp
                GROUP BY
                    day, hour, bannerid, zoneid";
            debug(" - phpAds_countClicks() - Insert into temporary table tmp_adclicks from table $adclicks_table: Started");
            $res = phpAds_dbQuery($query)
                or $report .= "Could not perform SQL: ".$query."\n"."SQL Error: ".mysql_error()."\n";
            debug(" - phpAds_countClicks() - Insert into temporary table tmp_adclicks from table $adclicks_table: Ended");
            
            // Update the day counter, and the "current" date
            $daysCounter++;
            $currentDate = $currentDate->getNextDay();
            
        }
        
        $click_query = "
            SELECT
                day,
                hour,
                bannerid,
                zoneid,
                clicks
            FROM
                tmp_adclicks";
        debug(" - phpAds_countClicks() - Selecting clicks from the temporary table tmp_adclicks: Started");
        $click_result = phpAds_dbQuery($click_query)
            or $report.= "Could not perform SQL: ".$click_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(" - phpAds_countClicks() - Selecting clicks from the temporary table tmp_adclicks: Ended");
        
    } else {
        
        // No splitting, just one big adclicks table
        // Select adclicks from the start time to the end time, aggregating on 
        // the day, hour, bannerid and zoneid
        $click_query = "
            SELECT
                DATE_FORMAT(t_stamp, '%Y-%m-%d') as day,
                DATE_FORMAT(t_stamp, '%k') as hour,
                bannerid,
                zoneid,
                COUNT(*) AS clicks
            FROM
                {$conf['table']['adclicks']}
            WHERE
                t_stamp >= $begin_timestamp
                AND t_stamp < $end_timestamp
            GROUP BY
                day, hour, bannerid, zoneid";
        debug(" - phpAds_countClicks() - Selecting clicks from the adclicks table: Started");
        $click_result = phpAds_dbQuery($click_query)
            or $report.= "Could not perform SQL: ".$click_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(" - phpAds_countClicks() - Selecting clicks from the adclicks table: Ended");
        
    }
    
    // Insert the selected, aggregated clicks
    $num_clicks = 0;
    debug(" - phpAds_countClicks() - Updating aggregate clicks in the adstats table: Started");
    while ($click_row = phpAds_dbFetchArray($click_result)) {
        $stat_query = "UPDATE ".$conf['table']['adstats'].
                        " SET clicks=clicks+".$click_row['clicks'].
                        " WHERE day='".$click_row['day']."'".
                        " AND hour=".$click_row['hour'].
                        " AND bannerid=".$click_row['bannerid'].
                        " AND zoneid=".$click_row['zoneid'];
        $stat_result = phpAds_dbQuery($stat_query)
            or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
        if (phpAds_dbAffectedRows($stat_result) < 1) {
            debug("   - phpAds_countClicks() - Unable to update - using insert instead.");
            $stat_query = "INSERT INTO ".$conf['table']['adstats'].
                            " SET day='".$click_row['day']."'".
                            ",hour=".$click_row['hour'].
                            ",bannerid=".$click_row['bannerid'].
                            ",zoneid=".$click_row['zoneid'].
                            ",clicks=".$click_row['clicks'];
            $stat_result = phpAds_dbQuery($stat_query)
                or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
        }
        $num_clicks += $click_row['clicks'];
    }
    debug(" - phpAds_countClicks() - Updating aggregate clicks in the adstats table: Ended");
    $report .= "\tCounted ".$num_clicks." clicks in ".(time()-$time)." seconds.\n";
    
    if ($conf['split_tables']) {

        // Drop the temporary table
        $sql = "DROP TEMPORARY TABLE tmp_adclicks";
        debug(' - phpAds_countClicks() - Drop temporary table tmp_adclicks: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countClicks() - Drop temporary table tmp_adclicks: Ended');
        
    }
    
}

function phpAds_countConversions($begin_timestamp, $end_timestamp, $day, $hour)
{
    global $conf, $report;
    
    $time = time();
    debug('Running the 3rd job: phpAds_countConversions()');
    debug('Counting the conversions between ' . $begin_timestamp . ' and ' . $end_timestamp);
    $report .= "\tCounting the conversions between ".$begin_timestamp." and ".$end_timestamp."...\n";

    // Create temporary conversions table
    $sql = "
        CREATE TEMPORARY TABLE tmp_conversions
            (conversionlogid mediumint(9) NOT NULL auto_increment,
            conversionid bigint(20) unsigned NOT NULL default '0',
            campaignid mediumint(9) NOT NULL default '0',
            trackerid mediumint(9) NOT NULL default '0',
            userid varchar(32) NOT NULL default '',
            t_stamp timestamp(14) NOT NULL,
            host varchar(255) NOT NULL default '',
            country char(2) NOT NULL default '',
            cnv_logstats enum('y','n') default 'n',
            cnv_clickwindow mediumint(9) NOT NULL default '0',
            cnv_viewwindow mediumint(9) NOT NULL default '0',
            action enum('view','click') default NULL,
            action_bannerid mediumint(9) NOT NULL default '0',
            action_zoneid mediumint(9) NOT NULL default '0',
            action_t_stamp timestamp(14) NOT NULL,
            action_host varchar(255) NOT NULL default '',
            action_source varchar(50) NOT NULL default '',
            action_country char(2) NOT NULL default '',
            PRIMARY KEY (conversionlogid))
    ";
    debug(' - phpAds_countConversions() - Create temporary table: Started');
    $res = phpAds_dbQuery($sql)
        or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Create temporary table: Ended');    
    
    // Deal with view conversions
    if ($conf['split_tables']) {
        
        // Find out the value of the maximum view conversions window
        $sql = "SELECT MAX(viewwindow) AS MAX FROM {$conf['table']['campaigns_trackers']}";
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        $view_window_row = phpAds_dbFetchArray($res);
        $view_window = $view_window_row['MAX'];
        debug(" - phpAds_countConversions() - Found maximum view window of $view_window seconds");
        
        // For each day that the conversions may be in...
        $startDate = new Date($begin_timestamp);
        $endDate = new Date($end_timestamp);
        $days = Date_Calc::dateDiff($startDate->getDay(), $startDate->getMonth(), $startDate->getYear(), $endDate->getDay(), $endDate->getMonth(), $endDate->getYear());
        $daysCounter = 0;
        $currentDate = $startDate;
        while ($daysCounter <= $days) {
            
            // Create temporary table for storing potential view conversions
            $sql = "
                CREATE TEMPORARY TABLE tmp_conversions_views
                    (conversionid bigint(20) unsigned NOT NULL default '0',
                    campaignid mediumint(9) NOT NULL default '0',
                    bannerid mediumint(9) NOT NULL default '0',
                    trackerid mediumint(9) NOT NULL default '0',
                    userid varchar(32) NOT NULL default '',
                    t_stamp timestamp(14) NOT NULL,
                    host varchar(255) NOT NULL default '',
                    country char(2) NOT NULL default '',
                    logstats enum('y','n') default 'n',
                    clickwindow mediumint(9) NOT NULL default '0',
                    viewwindow mediumint(9) NOT NULL default '0')";
            debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_views: Started');
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
            debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_views: Ended');
            
            // Insert potential conversions into the temporary table
            $adconversions_table = $conf['table']['adconversions'] . '_' . $currentDate->format('%Y%m%d');
            $sql = "
                INSERT INTO tmp_conversions_views
                SELECT
                    cnv.conversionid AS conversionid,
                    b.campaignid AS campaignid,
                    b.bannerid AS bannerid,
                    cnv.trackerid AS trackerid,
                    cnv.userid AS userid,
                    cnv.t_stamp AS t_stamp,
                    cnv.host AS host,
                    cnv.country AS country,
                    t.logstats AS logstats,
                    t.clickwindow AS clickwindow,
                    t.viewwindow AS viewwindow
                FROM
                    $adconversions_table AS cnv,
                    {$conf['table']['campaigns_trackers']} AS t,
                    {$conf['table']['banners']} AS b
                WHERE
                    t.trackerid=cnv.trackerid
                    AND b.campaignid=t.campaignid
                    AND cnv.userid != ''
                    AND cnv.t_stamp >= $begin_timestamp
                    AND cnv.t_stamp < $end_timestamp
                    AND t.viewwindow > 0";
            debug(" - phpAds_countConversions() - Insert into temp table for view based conversions using adconversion table $adconversions_table: Started");
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
            debug(" - phpAds_countConversions() - Insert into temp table for view based conversions using adconversion table $adconversions_table: Ended");
            
            // For every day in the view_window...
            $startViewsDate = $startDate;
            $startViewsDate->subtractSeconds((int) $view_window); // Cast to int as Date class doesn't correctly
                                                                  // deal with integers as strings...
            $endViewsDate = $endDate;
            $viewsDays = Date_Calc::dateDiff($startViewsDate->getDay(), $startViewsDate->getMonth(), $startViewsDate->getYear(), $endViewsDate->getDay(), $endViewsDate->getMonth(), $endViewsDate->getYear());
            $viewsDaysCounter = 0;
            $viewsCurrentDate = $startViewsDate;
            debug(' - phpAds_countConversions() - Insertion of view conversions: Started');
            while ($viewsDaysCounter <= $viewsDays) {
                // Insert view conversions into main temporary conversions table
                $adviews_table = $conf['table']['adviews'] . '_' . $viewsCurrentDate->format('%Y%m%d');
                debug("   - phpAds_countConversions() - Using adviews table $adviews_table");
                $sql = "
                    INSERT INTO tmp_conversions
                        (conversionid,
                        campaignid,
                        trackerid,
                        userid,
                        t_stamp,
                        host,
                        country,
                        cnv_logstats,
                        cnv_clickwindow,
                        cnv_viewwindow,
                        action,
                        action_bannerid,
                        action_zoneid,
                        action_t_stamp,
                        action_host,
                        action_source,
                        action_country)
                    SELECT
                        tcv.conversionid AS conversionid,
                        tcv.campaignid AS campaignid,
                        tcv.trackerid AS trackerid,
                        tcv.userid AS userid,
                        tcv.t_stamp AS t_stamp,
                        tcv.host AS host,
                        tcv.country AS country,
                        tcv.logstats AS cnv_logstats,
                        tcv.clickwindow AS cnv_clickwindow,
                        tcv.viewwindow AS cnv_viewwindow,
                        'view' AS action,
                        v.bannerid AS action_bannerid,
                        v.zoneid AS action_zoneid,
                        v.t_stamp AS action_t_stamp,
                        v.host AS action_host,
                        v.source AS action_source,
                        v.country AS action_country
                    FROM
                        tmp_conversions_views AS tcv,
                        $adviews_table AS v
                    WHERE
                        tcv.userid=v.userid
                        AND tcv.bannerid=v.bannerid
                        AND tcv.t_stamp < DATE_ADD(v.t_stamp, INTERVAL tcv.viewwindow SECOND)
                        AND tcv.t_stamp >= v.t_stamp
                    ORDER BY
                        v.t_stamp DESC";
                $res = phpAds_dbQuery($sql);
                if (!$res) {
                    // Ignore missing tables - they may have legally been dropped
                    if (mysql_errno() != 1146) { // Table doesn't exist error
                        $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
                    }
                }
                // Update the views day counter, and the views "current" date
                $viewsDaysCounter++;
                $viewsCurrentDate = $viewsCurrentDate->getNextDay();
            }
            debug(' - phpAds_countConversions() - Insertion of view conversions: Ended');
                            
            // Drop the temporary table
            $sql = "DROP TEMPORARY TABLE tmp_conversions_views";
            debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_views: Started');
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
            debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_views: Ended');
            
            // Update the day counter, and the "current" date
            $daysCounter++;
            $currentDate = $currentDate->getNextDay();
            
        }
        
    } else {
        
        // Create temporary table for storing potential view conversions
        $sql = "
            CREATE TEMPORARY TABLE tmp_conversions_views
                (conversionid bigint(20) unsigned NOT NULL default '0',
                campaignid mediumint(9) NOT NULL default '0',
                bannerid mediumint(9) NOT NULL default '0',
                trackerid mediumint(9) NOT NULL default '0',
                userid varchar(32) NOT NULL default '',
                t_stamp timestamp(14) NOT NULL,
                host varchar(255) NOT NULL default '',
                country char(2) NOT NULL default '',
                logstats enum('y','n') default 'n',
                clickwindow mediumint(9) NOT NULL default '0',
                viewwindow mediumint(9) NOT NULL default '0')";
        debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_views: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_views: Ended');
        
        // Insert potential conversions into the temporary table
        $sql = "
            INSERT INTO tmp_conversions_views
            SELECT
                cnv.conversionid AS conversionid,
                b.campaignid AS campaignid,
                b.bannerid AS bannerid,
                cnv.trackerid AS trackerid,
                cnv.userid AS userid,
                cnv.t_stamp AS t_stamp,
                cnv.host AS host,
                cnv.country AS country,
                t.logstats AS logstats,
                t.clickwindow AS clickwindow,
                t.viewwindow AS viewwindow
            FROM
                {$conf['table']['adconversions']} AS cnv,
                {$conf['table']['campaigns_trackers']} AS t,
                {$conf['table']['banners']} AS b
            WHERE
                t.trackerid=cnv.trackerid
                AND b.campaignid=t.campaignid
                AND cnv.userid != ''
                AND cnv.t_stamp >= $begin_timestamp
                AND cnv.t_stamp < $end_timestamp
                AND t.viewwindow > 0";
        debug(' - phpAds_countConversions() - Insert into temp table for view based conversions: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Insert into temp table for view based conversions: Ended');

        // Insert view conversions into main temporary conversions table
        $sql = "
            INSERT INTO tmp_conversions
                (conversionid,
                campaignid,
                trackerid,
                userid,
                t_stamp,
                host,
                country,
                cnv_logstats,
                cnv_clickwindow,
                cnv_viewwindow,
                action,
                action_bannerid,
                action_zoneid,
                action_t_stamp,
                action_host,
                action_source,
                action_country)
            SELECT
                tcv.conversionid AS conversionid,
                tcv.campaignid AS campaignid,
                tcv.trackerid AS trackerid,
                tcv.userid AS userid,
                tcv.t_stamp AS t_stamp,
                tcv.host AS host,
                tcv.country AS country,
                tcv.logstats AS cnv_logstats,
                tcv.clickwindow AS cnv_clickwindow,
                tcv.viewwindow AS cnv_viewwindow,
                'view' AS action,
                v.bannerid AS action_bannerid,
                v.zoneid AS action_zoneid,
                v.t_stamp AS action_t_stamp,
                v.host AS action_host,
                v.source AS action_source,
                v.country AS action_country
            FROM
                tmp_conversions_views AS tcv,
                {$conf['table']['adviews']} AS v
            WHERE
                tcv.userid=v.userid
                AND tcv.bannerid=v.bannerid
                AND tcv.t_stamp < DATE_ADD(v.t_stamp, INTERVAL tcv.viewwindow SECOND)
                AND tcv.t_stamp >= v.t_stamp
            ORDER BY
                v.t_stamp DESC";
        debug(' - phpAds_countConversions() - Insertion of view conversions: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Insertion of view conversions: Ended');
        
        // Drop the temporary table
        $sql = "DROP TEMPORARY TABLE tmp_conversions_views";
        debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_views: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_views: Ended');
            
    }
    
    // Deal with click conversions
    if ($conf['split_tables']) {

        // Find out the value of the maximum click conversions window
        $sql = "SELECT MAX(clickwindow) AS MAX FROM {$conf['table']['campaigns_trackers']}";
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        $click_window_row = phpAds_dbFetchArray($res);
        $click_window = $click_window_row['MAX'];
        debug(" - phpAds_countConversions() - Found maximum click window of $click_window seconds");
        
        // For each day that the conversions may be in...
        $startDate = new Date($begin_timestamp);
        $endDate = new Date($end_timestamp);
        $days = Date_Calc::dateDiff($startDate->getDay(), $startDate->getMonth(), $startDate->getYear(), $endDate->getDay(), $endDate->getMonth(), $endDate->getYear());
        $daysCounter = 0;
        $currentDate = $startDate;
        while ($daysCounter <= $days) {
        
            // Create temporary table for storing potential click conversions
           $sql = "
                CREATE TEMPORARY TABLE tmp_conversions_clicks
                    (conversionid bigint(20) unsigned NOT NULL default '0',
                    campaignid mediumint(9) NOT NULL default '0',
                    bannerid mediumint(9) NOT NULL default '0',
                    trackerid mediumint(9) NOT NULL default '0',
                    userid varchar(32) NOT NULL default '',
                    t_stamp timestamp(14) NOT NULL,
                    host varchar(255) NOT NULL default '',
                    country char(2) NOT NULL default '',
                    logstats enum('y','n') default 'n',
                    clickwindow mediumint(9) NOT NULL default '0',
                    viewwindow mediumint(9) NOT NULL default '0')";
            debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_clicks: Started');
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
            debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_clicks: Ended');
            
            // Insert potential conversions into the temporary table
            $adconversions_table = $conf['table']['adconversions'] . '_' . $currentDate->format('%Y%m%d');
            $sql = "
                INSERT INTO tmp_conversions_clicks
                SELECT
                    cnv.conversionid AS conversionid,
                    b.campaignid AS campaignid,
                    b.bannerid AS bannerid,
                    cnv.trackerid AS trackerid,
                    cnv.userid AS userid,
                    cnv.t_stamp AS t_stamp,
                    cnv.host AS host,
                    cnv.country AS country,
                    t.logstats AS logstats,
                    t.clickwindow AS clickwindow,
                    t.viewwindow AS viewwindow
                FROM
                    $adconversions_table AS cnv,
                    {$conf['table']['campaigns_trackers']} AS t,
                    {$conf['table']['banners']} AS b
                WHERE
                    t.trackerid=cnv.trackerid
                    AND b.campaignid=t.campaignid
                    AND cnv.userid != ''
                    AND cnv.t_stamp >= $begin_timestamp
                    AND cnv.t_stamp < $end_timestamp
                    AND t.clickwindow > 0";
            debug(" - phpAds_countConversions() - Insert into temp table for click based conversions using adconversion table $adconversions_table: Started");
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
            debug(" - phpAds_countConversions() - Insert into temp table for click based conversions using adconversion table $adconversions_table: Ended");

            // For every day in the click_window...
            $startClicksDate = $startDate;
            $startClicksDate->subtractSeconds((int) $click_window); // Cast to int as Date class doesn't correctly
                                                                    // deal with integers as strings...
            $endClicksDate = $endDate;
            $clicksDays = Date_Calc::dateDiff($startClicksDate->getDay(), $startClicksDate->getMonth(), $startClicksDate->getYear(), $endClicksDate->getDay(), $endClicksDate->getMonth(), $endClicksDate->getYear());
            $clicksDaysCounter = 0;
            $clicksCurrentDate = $startClicksDate;
            debug(' - phpAds_countConversions() - Insertion of click conversions: Started');
            while ($clicksDaysCounter <= $clicksDays) {
                // Insert click conversions into main temporary conversions table
                $adclicks_table = $conf['table']['adclicks'] . '_' . $clicksCurrentDate->format('%Y%m%d');
                debug("   - phpAds_countConversions() - Using adclicks table $adclicks_table");
                $sql = "
                    INSERT INTO tmp_conversions
                        (conversionid,
                        campaignid,
                        trackerid,
                        userid,
                        t_stamp,
                        host,
                        country,
                        cnv_logstats,
                        cnv_clickwindow,
                        cnv_viewwindow,
                        action,
                        action_bannerid,
                        action_zoneid,
                        action_t_stamp,
                        action_host,
                        action_source,
                        action_country)
                    SELECT
                        tcc.conversionid AS conversionid,
                        tcc.campaignid AS campaignid,
                        tcc.trackerid AS trackerid,
                        tcc.userid AS userid,
                        tcc.t_stamp AS t_stamp,
                        tcc.host AS host,
                        tcc.country AS country,
                        tcc.logstats AS cnv_logstats,
                        tcc.clickwindow AS cnv_clickwindow,
                        tcc.viewwindow AS cnv_viewwindow,
                        'click' AS action,
                        c.bannerid AS action_bannerid,
                        c.zoneid AS action_zoneid,
                        c.t_stamp AS action_t_stamp,
                        c.host AS action_host,
                        c.source AS action_source,
                        c.country AS action_country
                    FROM
                        tmp_conversions_clicks AS tcc,
                        $adclicks_table AS c
                    WHERE
                        tcc.userid=c.userid
                        AND tcc.bannerid=c.bannerid
                        AND tcc.t_stamp < DATE_ADD(c.t_stamp, INTERVAL tcc.clickwindow SECOND)
                        AND tcc.t_stamp >= c.t_stamp
                    ORDER BY
                        c.t_stamp DESC";
                $res = phpAds_dbQuery($sql);
                if (!$res) {
                    // Ignore missing tables - they may have legally been dropped
                    if (mysql_errno() != 1146) { // Table doesn't exist error
                        $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
                    }
                }
                // Update the clicks day counter, and the clicks "current" date
                $clicksDaysCounter++;
                $clicksCurrentDate = $clicksCurrentDate->getNextDay();
            }
            debug(' - phpAds_countConversions() - Insertion of click conversions: Ended');
            
            // Drop the temporary table
            $sql = "DROP TEMPORARY TABLE tmp_conversions_clicks";
            debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_clicks: Started');
            $res = phpAds_dbQuery($sql)
                or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
            debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_clicks: Ended');
            
            // Update the day counter, and the "current" date
            $daysCounter++;
            $currentDate = $currentDate->getNextDay();
            
        }
        
    } else {
        
        // Create temporary table for storing potential click conversions
        $sql = "
            CREATE TEMPORARY TABLE tmp_conversions_clicks
                (conversionid bigint(20) unsigned NOT NULL default '0',
                campaignid mediumint(9) NOT NULL default '0',
                bannerid mediumint(9) NOT NULL default '0',
                trackerid mediumint(9) NOT NULL default '0',
                userid varchar(32) NOT NULL default '',
                t_stamp timestamp(14) NOT NULL,
                host varchar(255) NOT NULL default '',
                country char(2) NOT NULL default '',
                logstats enum('y','n') default 'n',
                clickwindow mediumint(9) NOT NULL default '0',
                viewwindow mediumint(9) NOT NULL default '0')";
        debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_clicks: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Create temporary table tmp_conversions_clicks: Ended');
    
        // Insert potential conversions into the temporary table
        $sql = "
            INSERT INTO tmp_conversions_clicks
            SELECT
                cnv.conversionid AS conversionid,
                b.campaignid AS campaignid,
                b.bannerid AS bannerid,
                cnv.trackerid AS trackerid,
                cnv.userid AS userid,
                cnv.t_stamp AS t_stamp,
                cnv.host AS host,
                cnv.country AS country,
                t.logstats AS logstats,
                t.clickwindow AS clickwindow,
                t.viewwindow AS viewwindow
            FROM
                {$conf['table']['adconversions']} AS cnv,
                {$conf['table']['campaigns_trackers']} AS t,
                {$conf['table']['banners']} AS b
            WHERE
                t.trackerid=cnv.trackerid
                AND b.campaignid=t.campaignid
                AND cnv.userid != ''
                AND cnv.t_stamp >= $begin_timestamp
                AND cnv.t_stamp < $end_timestamp
                AND t.clickwindow > 0";
        debug(' - phpAds_countConversions() - Insert into temp table for click based conversions: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Insert into temp table for click based conversions: Ended');

        // Insert click conversions into main temporary conversions table
        $sql = "
            INSERT INTO tmp_conversions
                (conversionid,
                campaignid,
                trackerid,
                userid,
                t_stamp,
                host,
                country,
                cnv_logstats,
                cnv_clickwindow,
                cnv_viewwindow,
                action,
                action_bannerid,
                action_zoneid,
                action_t_stamp,
                action_host,
                action_source,
                action_country)
            SELECT
                tcc.conversionid AS conversionid,
                tcc.campaignid AS campaignid,
                tcc.trackerid AS trackerid,
                tcc.userid AS userid,
                tcc.t_stamp AS t_stamp,
                tcc.host AS host,
                tcc.country AS country,
                tcc.logstats AS cnv_logstats,
                tcc.clickwindow AS cnv_clickwindow,
                tcc.viewwindow AS cnv_viewwindow,
                'click' AS action,
                c.bannerid AS action_bannerid,
                c.zoneid AS action_zoneid,
                c.t_stamp AS action_t_stamp,
                c.host AS action_host,
                c.source AS action_source,
                c.country AS action_country
            FROM
                tmp_conversions_clicks AS tcc,
                {$conf['table']['adclicks']} AS c
            WHERE
                tcc.userid=c.userid
                AND tcc.bannerid=c.bannerid
                AND tcc.t_stamp < DATE_ADD(c.t_stamp, INTERVAL tcc.clickwindow SECOND)
                AND tcc.t_stamp >= c.t_stamp
            ORDER BY
                c.t_stamp DESC";
        debug(' - phpAds_countConversions() - Insertion of click conversions: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Insertion of click conversions: Ended');
        
        // Drop the temporary table
        $sql = "DROP TEMPORARY TABLE tmp_conversions_clicks";
        debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_clicks: Started');
        $res = phpAds_dbQuery($sql)
            or $report .= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions_clicks: Ended');

    }
    
    // Insert the tmp_conversions table values into the conversionlog table
    $sql = "
        INSERT INTO {$conf['table']['conversionlog']}
            (conversionid,
            campaignid,
            trackerid,
            userid,
            t_stamp,
            host,
            country,
            cnv_logstats,
            cnv_clickwindow,
            cnv_viewwindow,
            action,
            action_bannerid,
            action_zoneid,
            action_t_stamp,
            action_host,    
            action_source,
            action_country)
        SELECT
            conversionid,
            campaignid,
            trackerid,
            userid,
            t_stamp,
            host,
            country,
            cnv_logstats,
            cnv_clickwindow,
            cnv_viewwindow,
            action,
            action_bannerid,
            action_zoneid,
            action_t_stamp,
            action_host,
            action_source,
            action_country
        FROM
            tmp_conversions
        GROUP BY
            conversionid,
            campaignid
        ORDER BY
            conversionlogid";
    debug(' - phpAds_countConversions() - Insertion of temporary table results into conversion log: Started');
    $res = phpAds_dbQuery($sql)
        or $report.= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Insertion of temporary table results into conversion log: Ended');

    // Drop the temporary table
	$sql = 'DROP TEMPORARY TABLE tmp_conversions';
    debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions: Started');
    $res = phpAds_dbQuery($sql)
        or $report.= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Drop temporary table tmp_conversions: Ended');

	// Flag the latest conversion for each action, so that we dont get more than
	// one conversion for each tracker hit
	
	// Start by getting all the conversionlogids with the latest timestamp for each conversionid
	$sql = 'CREATE TEMPORARY TABLE tmp_latest
                SELECT
                    conversionlogid, t_stamp
                FROM
                    '.$conf['table']['conversionlog'].'
                WHERE
                    t_stamp >= '.$begin_timestamp.'
                    AND t_stamp < '.$end_timestamp.'
                GROUP BY
                    conversionid
                HAVING
                    t_stamp = max(t_stamp)';
    debug(' - phpAds_countConversions() - Get conversionlogid of latest conversions: Started');
    $res = phpAds_dbQuery($sql)
        or $report.= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Get conversionlogid of latest conversions: Ended');

	// Update cnv_latest field on the conversionlog table for each conversionlogid in the tmp table
	$sql = 'UPDATE
            '.$conf['table']['conversionlog'].' as c,
                tmp_latest as t
            SET
                c.cnv_latest=1,
                c.t_stamp=c.t_stamp
            WHERE 
                c.conversionlogid=t.conversionlogid';
    debug(' - phpAds_countConversions() - Set \'latest conversion\' flag: Started');
    $res = phpAds_dbQuery($sql)
        or $report.= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Set \'latest conversion\' flag: Ended');

    // Drop the temporary table
	$sql = 'DROP TEMPORARY TABLE tmp_latest';
    debug(' - phpAds_countConversions() - Drop temporary table: Started');
    $res = phpAds_dbQuery($sql)
        or $report.= "Could not perform SQL: ".$sql."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Drop temporary table: Ended');

    // Now, add up all of the conversions that we just logged and put them into adstats.
    // Process conversions...
    $num_conversions    = 0;
    $total_conversions  = 0;

    $conversion_query = "
                SELECT action_bannerid,
                    action_zoneid,
                    count(*) as conversions
                FROM " . $conf['table']['conversionlog'] . "
                WHERE t_stamp >= " . $begin_timestamp . "
                    AND t_stamp < " . $end_timestamp . "
                    AND cnv_logstats = 'y'
					AND cnv_latest = 1
                GROUP BY action_bannerid, action_zoneid";
    debug(' - phpAds_countConversions() - Adstats conversions query: Started');
    $conversion_result = phpAds_dbQuery($conversion_query)
        or $report.= "Could not perform SQL: ".$conversion_query."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Adstats conversions query: Ended');

    $x = 1;
    while ($conversion_row = phpAds_dbFetchArray($conversion_result)) {
        $stat_query = " UPDATE " . $conf['table']['adstats'] . "
                        SET conversions = conversions + " . $conversion_row['conversions'] . "
                        WHERE day = '" . $day . "'" . "
                        AND hour = " . $hour . "
                        AND bannerid = " . $conversion_row['action_bannerid'] . "
                        AND zoneid = " . $conversion_row['action_zoneid'];
        debug(' - phpAds_countConversions() - Conversion query update: Started iteration ' . $x);
        $stat_result = phpAds_dbQuery($stat_query)
            or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Conversion query update: Ended iteration ' . $x);

        if (phpAds_dbAffectedRows($stat_result) < 1) {
            $stat_query = " INSERT INTO " . $conf['table']['adstats'] . "
                            SET day = '".$day."'". ",
                            hour = " . $hour . ",
                            bannerid = " . $conversion_row['action_bannerid']. ",
                            zoneid = " . $conversion_row['action_zoneid'] . ",
                            conversions = " . $conversion_row['conversions'];
            debug(' - phpAds_countConversions() - Conversion query insert: Started iteration ' . $x);
            $stat_result = phpAds_dbQuery($stat_query)
                or $report.= " Could not perform SQL: ".$stat_query."\n"."SQL Error: ".mysql_error()."\n";
            debug(' - phpAds_countConversions() - Conversion query insert: Ended iteration ' . $x);
        }
        $num_conversions += $conversion_row['conversions'];
        $x++;
    }

    $conversion_query = "SELECT".
                    " COUNT(*) as conversions".
                    " FROM ".$conf['table']['conversionlog'].
                    " WHERE t_stamp>=".$begin_timestamp.
                    " AND t_stamp<".$end_timestamp;
    debug(' - phpAds_countConversions() - Select conversions: Started');
    $total_conversions = phpAds_dbQuery($conversion_query)
        or $report.= "Could not perform SQL: ".$conversion_query."\n"."SQL Error: ".mysql_error()."\n";
    debug(' - phpAds_countConversions() - Select conversions: Ended');

    $conversions = phpAds_dbFetchArray($total_conversions);

    // Update the variablevalues table with the conversionid of the
    // conversion associated with the variable
    if ($conf['split_tables']) {
        
        // For each day that the conversions may be in...
        $startDate = new Date($begin_timestamp);
        $endDate = new Date($end_timestamp);
        $days = Date_Calc::dateDiff($startDate->getDay(), $startDate->getMonth(), $startDate->getYear(), $endDate->getDay(), $endDate->getMonth(), $endDate->getYear());
        $daysCounter = 0;
        $currentDate = $startDate;
        while ($daysCounter <= $days) {
            
            $adconversions_table = $conf['table']['adconversions'] . '_' . $currentDate->format('%Y%m%d');
            
            // For the current and next variablevalues tables
            $variablevaluesDate = $currentDate;
            for ($variablevaluesCounter = 0; $variablevaluesCounter < 2; $variablevaluesCounter++) {
                
                $variablevalues_table = $conf['table']['variablevalues'] . '_' . $variablevaluesDate->format('%Y%m%d');
                $variables_query = 'UPDATE 
                                       '.$adconversions_table.' as c
                                        ,'.$variablevalues_table.' as v
                                    SET
                                        v.conversionid=c.conversionid
                                    WHERE
                                        v.local_conversionid=c.local_conversionid 
                                        AND v.dbserver_ip=c.dbserver_ip 
                                        AND c.t_stamp >= \''.$begin_timestamp.'\' 
                                        AND c.t_stamp <  \''.$end_timestamp.'\' 
                                        AND v.t_stamp >= \''.$begin_timestamp.'\' 
                                        AND v.t_stamp <  \''.$end_timestamp.'\'';
                $variables_result = phpAds_dbQuery($variables_query);
                if (!$variables_result) {
                    // Ignore missing tables - they may have legally been dropped, or
                    // may not yet exist
                    if (mysql_errno() != 1146) { // Table doesn't exist error
                        $report .= "Could not perform SQL: $sql\n"."SQL Error: ".mysql_error()."\n";
                    }
                }  else {
                    debug(" - phpAds_countConversions() - Updated $variablevalues_table with conversionid from $adconversions_table");
                }
                
                // Update the variablevalues table date
                $variablevaluesDate = $variablevaluesDate->getNextDay();
                
            }
            
            // Update the day counter, and the "current" date
            $daysCounter++;
            $currentDate = $currentDate->getNextDay();
            
        }
            
    } else {
    
        $variables_query = 'UPDATE 
                               '.$conf['table']['adconversions'].' as c
                                ,'.$conf['table']['variablevalues'].' as v
                            SET
                                v.conversionid=c.conversionid
                            WHERE
                                v.local_conversionid=c.local_conversionid 
                                AND v.dbserver_ip=c.dbserver_ip 
                                AND c.t_stamp >= \''.$begin_timestamp.'\' 
                                AND c.t_stamp <  \''.$end_timestamp.'\' 
                                AND v.t_stamp >= \''.$begin_timestamp.'\' 
                                AND v.t_stamp <  \''.$end_timestamp.'\'';
        debug(' - phpAds_countConversions() - Update variablevalues with conversionid from adconversions: Started');
        $variables_result = phpAds_dbQuery($variables_query)
            or $report.= "Could not perform SQL: ".$variables_query."\n"."SQL Error: ".mysql_error()."\n";
        debug(' - phpAds_countConversions() - Update variablevalues with conversionid from adconversions: Ended');
        
    }
                        
    $report .= "\tLogged ".$num_conversions." conversions out of ".$conversions['conversions']." in ".(time()-$time)." seconds.\n\n";
    
}

function phpAds_decrementCampaigns($day, $hour)
{
    global $conf, $report;
    debug('Running the 4th job: phpAds_decrementCampaigns()');
    //Next, Subtract the number of views for a particular banner...
    $report .= "\tDecrementing High Priority Campaigns...\n";
    $time = time();
    $num_views = 0;
    $num_clicks = 0;
    $num_conversions = 0;
    
    // Get campaign information
    $campaign_query ='
        SELECT
            campaignid,
            clientid,
            campaignname,
            active,
            views,
            clicks,
            conversions,
            UNIX_TIMESTAMP(expire) AS expire_st,
            UNIX_TIMESTAMP(activate) AS activate_st,
            UNIX_TIMESTAMP(NOW()) AS current_st
        FROM ' . $conf['table']['campaigns'];
    $campaign_result = phpAds_dbQuery($campaign_query)
        or $report.= "Could not perform SQL: ".$campaign_query."\n"."SQL Error: ".mysql_error()."\n";
                    
    while ($campaign_row = phpAds_dbFetchArray($campaign_result)) {
        $views = $campaign_row['views'];
        $clicks = $campaign_row['clicks'];
        $conversions = $campaign_row['conversions'];
        $active = $campaign_row['active'];

        if (($views > 0) || ($clicks > 0) || ($conversions > 0)) {
            $count_query = "
                SELECT
                    SUM(views) AS sum_views,
                    SUM(clicks) AS sum_clicks,
                    SUM(conversions) AS sum_conversions
                FROM " . $conf['table']['adstats'] . ', ' . $conf['table']['banners'] . "
                WHERE ". $conf['table']['banners'] . ".bannerid = " . $conf['table']['adstats'] . ".bannerid". "
                AND "  . $conf['table']['banners'] . ".campaignid = " . $campaign_row['campaignid'] . "
                AND day = '" . $day . "'". "
                AND hour = " . $hour;
            $count_result = phpAds_dbQuery($count_query)
                or $report.= "Could not perform SQL: ".$count_query."\n"."SQL Error: ".mysql_error()."\n";

            if ($count_row = phpAds_dbFetchArray($count_result)) {
                if ($views > 0) {
                    $views -= $count_row['sum_views'];
                    if ($views < 1) {
                        $views = 0;
                        $active = 'f';
                    }
                    $num_views += $count_row['sum_views'];
                }
                if ($clicks > 0) {
                    $clicks -= $count_row['sum_clicks'];
                    if ($clicks < 1) {
                        $clicks = 0;
                        $active = 'f';
                    }
                    $num_clicks += $count_row['sum_clicks'];
                }
                if ($conversions > 0) {
                    $conversions -= $count_row['sum_conversions'];
                    if ($conversions < 1) {
                        $conversions = 0;
                        $active = 'f';
                    }
                    $num_conversions += $count_row['sum_conversions'];
                }
            }
        }

        // Check time status...
        if ( ($campaign_row['current_st'] < $campaign_row['activate_st']) ||
             ($campaign_row['current_st'] > $campaign_row['expire_st'] && $campaign_row['expire_st'] != 0) )
        {
            $active = 'f';
        }
        // Check to see if we need to log a change in activation status...
        if ($campaign_row['active'] != $active) {
            $report.= "Sending an email to the owner of campaign ".$campaign_row['campaignid']."\n";
            if ($active == 'f') {
                // Send deactivation emails...
                phpAds_deactivateMail($campaign_row);
            }
        }
        // Update campaign
        if ( ($views        != $campaign_row['views']) ||
             ($clicks       != $campaign_row['clicks']) ||
             ($conversions  != $campaign_row['conversions']) ||
             ($active       != $campaign_row['active']) )
        {
            $update_query =
                "UPDATE " . $conf['table']['campaigns'].
                " SET views=" . $views.
                ",clicks=" . $clicks .
                ",conversions=" . $conversions .
                ",active='" . $active ."'".
                " WHERE campaignid=".$campaign_row['campaignid'];
            phpAds_dbQuery($update_query)
                or $report.= "Could not perform SQL: ".$update_query."\n"."SQL Error: ".mysql_error()."\n";
            $report .= "\tChanging campaign ".$campaign_row['campaignid'].":\n";
            $report .= "\t\tViews:  from ".$campaign_row['views']." to ".$views."\n";
            $report .= "\t\tClicks:  from ".$campaign_row['clicks']." to ".$clicks."\n";
            $report .= "\t\tConversions:  from ".$campaign_row['conversions']." to ".$conversions."\n";
            $report .= "\t\tActive Status:  from ".$campaign_row['active']." to ".$active."\n\n";
        }
    }   
    $report .= "\tDecremented a total of ".$num_views." views, ".$num_clicks." clicks, and ".$num_conversions." conversions in ".(time()-$time)." seconds.\n\n\n";
}

function phpAds_deleteVerboseStats($begin_timestamp, $end_timestamp)
{
    global $conf, $report;
    // Only run if not using split tables
    if (!$conf['split_tables']) {
        debug('Running the 5th job: phpAds_deleteVerboseStats()');
        if ($conf['compact_stats']) {
            $time = time();
            $delete_query = "DELETE".
                            " FROM ".$conf['table']['adviews'].
                            " WHERE t_stamp>=".$begin_timestamp.
                            " AND t_stamp<".$end_timestamp;
            phpAds_dbQuery($delete_query)
                or $report.= "Could not perform SQL: ".$delete_query."\n"."SQL Error: ".mysql_error()."\n";    
            $delete_query = "DELETE".
                            " FROM ".$conf['table']['adclicks'].
                            " WHERE t_stamp>=".$begin_timestamp.
                            " AND t_stamp<".$end_timestamp;
            phpAds_dbQuery($delete_query)
                or $report.= "Could not perform SQL: ".$delete_query."\n";
            $report .= "Deleted verbose stats in ".(time()-$time)." seconds.\n";
        }
    }
}

function phpAds_checkStatsExist($day, $hour)
{
    global $conf, $report;
    $exists = false;
    $stats_query =
        "SELECT COUNT(*) AS stat_count".
        " FROM ".$conf['table']['adstats'].
        " WHERE day=".$day.
        " AND hour=".$hour;
    $stats_result = phpAds_dbQuery($stats_query)
        or $report .= "Could not perform SQL: ".$stats_query."\n"."SQL Error: ".mysql_error()."\n";

    if ($stats_row = phpAds_dbFetchArray($stats_result)) {
        $count = $stats_row['stat_count'];
        if ($count > 0) {
            $exists = true;
            debug(' - phpAds_checkStatsExist() - Stats exist for ' . $day . ' at ' . $hour . ' o\'clock');
        } else {
            debug(' - phpAds_checkStatsExist() - Stats do not exist for ' . $day . ' at ' . $hour . ' o\'clock');
        }
    }
    return $exists;
}

function phpAds_logStatsDate($end_timestamp)
{
    global $conf;
    
    $time_query =
        "UPDATE ".$conf['table']['config'].
        " SET statslastday=".$end_timestamp.
        ",statslasthour=HOUR(".$end_timestamp.")"
    ;
    $time_result = phpAds_dbQuery($time_query);
}

?>
