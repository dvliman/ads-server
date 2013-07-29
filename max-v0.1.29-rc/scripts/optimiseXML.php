<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
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
$Id: optimiseXML.php 3145 2005-05-20 13:15:01Z andrew $
*/

    define('phpAds_path', dirname(__FILE__));
    $conf = parse_ini_file('../../default.conf.ini', true);
    
    if (DEBUG) {
        ini_set('error_log', phpAds_path . '/log.txt');
        error_log('     '); error_log('================================');error_log('     ');
    }
    // Set time limit and ignore user abort
    if (!ini_get('safe_mode')) {
        @set_time_limit(600);
        @ignore_user_abort(true);
    }
    
    // Get the Username and password details - NOTE:  Passoword should be MD5'ed
    $username  = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : '';
    $password  = (isset($_REQUEST['password'])) ? $_REQUEST['password'] : '';
    $dayStart  = (isset($_REQUEST['day_start'])) ? $_REQUEST['day_start'] : '';
    $dayEnd    = (isset($_REQUEST['day_end'])) ? $_REQUEST['day_end'] : '';
    $blindOnly = (isset($_REQUEST['blind_only'])) ? ($_REQUEST['blind_only'] != 'false') : true;
    
    $dbConnection = mysql_connect($conf['dbhost'], $conf['dbuser'], $conf['dbpassword']);
    mysql_select_db($conf['dbname'], $dbConnection);
    
    $loggedIn = false;
    $password = md5($password);

    // Is the user an admin?
    if ($username == 'admin') {
        $query = "
        SELECT
            c.agencyid AS agencyid
        FROM
            {$conf['tbl_config']} AS c
        WHERE
            c.admin_pw='$password'
        ";
        $result = mysql_query ($query, $dbConnection)
            or die(mysql_error());
        if (mysql_num_rows($result) > 0) {
            $loggedIn = true;
            $agencyId = 0;
        }
    }
    // Otherwise, only allow agency...
    else {
        $query = "
        SELECT
            a.agencyid AS agencyid
        FROM
            {$conf['tbl_agency']} AS a
        WHERE
            a.username='$username'
            AND a.password='$password'
        ";
        $result = mysql_query ($query, $dbConnection)
            or die(mysql_error());
        if ($row = mysql_fetch_array($result)) {
            $loggedIn = true;
            $agencyId = $row['agencyid'];
        }
    }

    if ($loggedIn) {
        echo "<?xml version=\"1.0\"?>\n";
        
        $where = '';
        if ($username != 'admin') {
            $where = "
                AND a.agencyid=$agencyId
                AND p.agencyid=$agencyId
            ";
        }
        $where .= !empty($dayStart) ? " AND day >= '$dayStart'" : '';
        $where .= !empty($dayEnd) ? " AND day <= '$dayEnd'" : '';
        $where .= ($blindOnly) ? " AND c.anonymous = 't'" : '';
        
        $query = "
        SELECT
            s.day AS day,
            a.clientid AS advertiser_id,
            a.clientname AS advertiser_name,
            p.name AS publisher_name,
            c.campaignid AS campaign_id,
            LEFT(c.campaignname,4) AS campaign_name,
            b.bannerid AS banner_id,
            b.description AS banner_description,
            b.filename AS creative,
            z.zoneid AS zone_id,
            z.description AS zone_description,
            SUM(s.views) AS total_views,
            SUM(s.clicks) AS total_clicks,
            SUM(s.conversions) AS total_conversions
        
        
        FROM
            {$conf['tbl_adstats']} AS s,
            {$conf['tbl_banners']} AS b,
            {$conf['tbl_campaigns']} AS c,
            {$conf['tbl_clients']} AS a,
            {$conf['tbl_zones']} AS z,
            {$conf['tbl_affiliates']} AS p
        
        WHERE
            b.bannerid=s.bannerid
            AND b.campaignid=c.campaignid
            AND c.clientid=a.clientid
            AND s.zoneid = z.zoneid
            AND z.affiliateid = p.affiliateid
            $where
                
        GROUP BY
            day,
            creative,
            advertiser_id,
            zone_id
        ";
        
        $result = mysql_query ($query, $dbConnection)
            or die(mysql_error());
        echo "<creative_zones>\n";
        while ($row = mysql_fetch_assoc($result)) {
            echo "<creative_zone>\n";
            foreach($row as $name => $value) {
                echo "<$name>$value</$name>\n";
            }
            echo "</creative_zone>\n";
        }
        echo "</creative_zones>\n";
    }
    else {
        echo "Invalid Login Details.  Please log in as either administrator or agency.";
        error_log("Invalid Login: username - $username, password - $password");
    }
?>