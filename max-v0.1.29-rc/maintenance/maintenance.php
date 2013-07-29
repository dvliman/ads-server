#!/usr/bin/php -q
<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// Uncomment the following to enable PHP warnings when
// developing, as some of the phpAdsNew code that hasn't
// been rewritten yet and still generates warnings...
// error_reporting(E_ALL ^ E_NOTICE);

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
$Id: maintenance.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Start timing job
$jobStartTime = time();

// Deal with older versions of PHP
if (!function_exists('version_compare') || version_compare(phpversion(), "4.3.0", 'lt')) {
    include_once 'libraries/bc.php';
}

// Deal with PHP5
if (function_exists('version_compare') && version_compare(phpversion(), "5.0.0", 'ge')) {
    include_once 'libraries/5.php';
}

// +---------------------------------------+
// | Define constants                      |
// +---------------------------------------+

define('maintenancePath', dirname(__FILE__));

// DB names
define('phpAds_adminDb', 1);
define('phpAds_rawDb', 2);

// Usertypes
define('phpAds_userDeliveryEngine', 1);
define('phpAds_userMaintenance', 2);
define('phpAds_userAdministrator', 3);
define('phpAds_userAdvertiser', 4);
define('phpAds_userPublisher', 5);

// Actions
define('phpAds_actionAdvertiserReportMailed', 0);
define('phpAds_actionPublisherReportMailed', 1);
define('phpAds_actionWarningMailed', 2);
define('phpAds_actionDeactivationMailed', 3);
define('phpAds_actionPriorityCalculation', 10);
define('phpAds_actionPriorityAutoTargeting', 11);
define('phpAds_actionDeactiveCampaign', 20);
define('phpAds_actionActiveCampaign', 21);
define('phpAds_actionAutoClean', 30);
define('phpAds_actionBatchStatistics', 40);
define('phpAds_installed', true);

// +---------------------------------------+
// | Configure PHP                         |
// +---------------------------------------+

// Set the error logging path
ini_set('error_log', maintenancePath . '/log.txt');

// Set time limit and ignore user abort
if (!ini_get('safe_mode')) {
    @set_time_limit(300);
    @ignore_user_abort(true);
}

// Disable magic_quotes_runtime
set_magic_quotes_runtime(0);

// Set the PEAR Date class location
ini_set('include_path', maintenancePath . '/libraries/pear');

// +---------------------------------------+
// | Load config and includes              |
// +---------------------------------------+

// Load config file
$conf = parse_ini_file(maintenancePath . '/default.conf.ini', true);

require_once maintenancePath . '/maintenance-common.php';
require_once maintenancePath . '/translationStrings.php';

debug('     ');
debug('================================');
debug('     ');
debug('Initial libs included correctly');

// +---------------------------------------+
// | Check for lockfile                    |
// +---------------------------------------+

if ($conf['split_tables']) {
    debug('Checking for lockfile');
    $attempt = 0;
    // Don't start if lockfile detected
    while (file_exists($conf['split_tables_lockfile'])) {
        // Pause for 30 secs
        sleep(30);
        debug('Lockfile exists, sleeping for 30 secs. Iteration ' . $attempt);
        $attempt ++;
        if ($attempt > 2) {
            debug('More than 3 attempts, admin should be email here');
            $message  = "Warning! Maintenance was unable to run - the lockfile was found\n";
            $message .= "in place while trying to start maintenance, even after 3 iterations.\n\n";
            $message .= "The lockfile used was: {$conf['split_tables_lockfile']}.\n\n";
            phpAds_sendMail($conf['split_tables_email'], '', 'Lockfile altert!', $message);
            exit;
        }
    }
    // Write lockfile so table splitting scripts will abort if they
    // attempt to start during a maintenance run
    debug('No lockfile exists, writing lockfile');
    $fh = fopen($conf['split_tables_lockfile'], 'w');
}

// +---------------------------------------+
// | DB connect                            |
// +---------------------------------------+

phpAds_dbConnect();
phpAds_LoadDbConfig();
debug('DB connection established, DB config loaded');

// +---------------------------------------+
// | Start maintenance                     |
// +---------------------------------------+

// Update the timestamp
$res = phpAds_dbQuery("UPDATE {$conf['table']['config']} SET maintenance_timestamp = UNIX_TIMESTAMP(NOW())");
debug('Timestamp updated correctly in config table = ' . $res);

// Run different maintenance tasks
debug('Starting mtce-statistics');
require_once maintenancePath . '/maintenance-statistics.php';

// +---------------------------------------+
// | If it's midnight, do additional tasks |
// +---------------------------------------+

if (date('H') == 0) {
    debug('Starting midnight stats run');
    include_once maintenancePath . '/maintenance-reports.php';
    include_once maintenancePath . '/maintenance-activation.php';
    include_once maintenancePath . '/maintenance-autotargeting.php';
    if (!$conf['split_tables']) {
        // Only run maintenance-cleantables when the raw logging
        // tables are not split - otherwise, raw data cleaning
        // should be done by dropping the expired raw data tables.
        include_once maintenancePath . '/maintenance-cleantables.php';
    }
    include_once maintenancePath . '/maintenance-optimization.php';
}

debug('Calculating priorities');
require_once maintenancePath . '/maintenance-priority.php';
phpAds_PriorityCalculate();

debug('Main script finished');

// +---------------------------------------+
// | Remove lockfile                       |
// +---------------------------------------+

if ($conf['split_tables']) {
    debug('Removing lockfile');
    unlink($conf['split_tables_lockfile']);
}

// +---------------------------------------+
// | Log results to mtce DB                |
// +---------------------------------------+

debug('Logging results to DB');
// Get total job execution time
$jobEndTime = time();
$elapsedTime = $jobEndTime - $jobStartTime;
$jobStartTime = date("Y-m-d H:i:s", $jobStartTime);
$jobEndTime = date("Y-m-d H:i:s", $jobEndTime);

// Open connection to admin DB
$query = "
    INSERT INTO {$conf['table']['log_maintenance']}
        (
            start_run,
            end_run,
            duration
        )
    VALUES
        (
            '$jobStartTime',
            '$jobEndTime',
            $elapsedTime
        )";
debug('Updating the maintenance log - started');
$result = phpAds_dbQuery($query)
    or die('Query failed : ' . mysql_error());
debug('Updating the maintenance log - ended');

?>
