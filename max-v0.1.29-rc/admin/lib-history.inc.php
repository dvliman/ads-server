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
$Id: lib-history.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Register input variables
//phpAds_registerGlobal ('limit', 'period', 'start', 'hideinactive', 'listorder', 'orderdirection');
// Include required files
require ('lib-gd.inc.php');

if (!isset($period) || $period == '')
    $period = 'd';

if (isset($lib_history_params))
{
    for (reset($lib_history_params); $key = key($lib_history_params); next($lib_history_params))
        $params[] = $key.'='.$lib_history_params[$key];
    
    $params = '?'.implode ('&', $params).'&';
}
else
    $params = '?';

$tabindex = 1;


/*********************************************************/
/* Determine span of statistics                          */
/*********************************************************/
if (phpAds_isUser(phpAds_Admin)) {
    $query = "SELECT " .
                "UNIX_TIMESTAMP(MIN(".$phpAds_config['tbl_adstats'].".day)) AS span, " .
                "TO_DAYS(NOW()) - TO_DAYS(MIN(".$phpAds_config['tbl_adstats'].".day)) + 1 AS span_days " .
            "FROM " .
                $phpAds_config['tbl_adstats'] . " " .
           
                (isset($lib_history_where) ? "WHERE " . $lib_history_where : "");
                
} elseif (phpAds_isUser(phpAds_Client)) {
    $query = "SELECT " .
                "UNIX_TIMESTAMP(MIN(".$phpAds_config['tbl_adstats'].".day)) AS span, " .
                "TO_DAYS(NOW()) - TO_DAYS(MIN(".$phpAds_config['tbl_adstats'].".day)) + 1 AS span_days " .
            "FROM " .
                $phpAds_config['tbl_adstats'] . ", " .
                $phpAds_config['tbl_banners'] . ", " .
                $phpAds_config['tbl_campaigns'] . " " .
                (isset($lib_history_where) ? "WHERE " . $lib_history_where . " AND " : "WHERE ") .
                $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
                "AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
                "AND ".$phpAds_config['tbl_campaigns'].".clientid = ". phpAds_getUserID();
} elseif (phpAds_isUser(phpAds_Affiliate)) {                
                
    $query = "SELECT " .
                "UNIX_TIMESTAMP(MIN(".$phpAds_config['tbl_adstats'].".day)) AS span, " .
                "TO_DAYS(NOW()) - TO_DAYS(MIN(".$phpAds_config['tbl_adstats'].".day)) + 1 AS span_days " .
            "FROM " .
                $phpAds_config['tbl_adstats'] . ", " .
                $phpAds_config['tbl_zones'] . " " .
                (isset($lib_history_where) ? "WHERE " . $lib_history_where . " AND " : "WHERE ") .
                $phpAds_config['tbl_adstats'].".zoneid = ".$phpAds_config['tbl_zones'].".zoneid " .
                "AND ".$phpAds_config['tbl_zones'].".affiliateid = " . phpAds_getUserID();
                    
} elseif (phpAds_isUser(phpAds_Agency)) {
    $query = "SELECT " .
                "UNIX_TIMESTAMP(MIN(".$phpAds_config['tbl_adstats'].".day)) AS span, " .
                "TO_DAYS(NOW()) - TO_DAYS(MIN(".$phpAds_config['tbl_adstats'].".day)) + 1 AS span_days " .
            "FROM " .
                $phpAds_config['tbl_adstats'] . ", " .
                $phpAds_config['tbl_banners'] . ", " .
                $phpAds_config['tbl_campaigns'] . ", " .
                $phpAds_config['tbl_clients'] . " " .
                (isset($lib_history_where) ? "WHERE " . $lib_history_where . " AND " : "WHERE ") .
                $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
                "AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
                "AND ".$phpAds_config['tbl_campaigns'].".clientid = ".$phpAds_config['tbl_clients'].".clientid " .
                "AND ".$phpAds_config['tbl_clients'].".agencyid=" . phpAds_getUserID();
    
} else {
    global $strErrorBadUserType;
    echo "<div class='errormessage' style='width: 400px;'><img class='errormessage' src='images/errormessage.gif' align='absmiddle' alt=''>";
    echo "<span class='tab-r'>$strErrorBadUserType</span></div>";
}
            
$res = phpAds_dbQuery($query)
    or phpAds_sqlDie();

if ($row = phpAds_dbFetchArray($res))
{
    $span          = $row['span'];
    $span_days   = $row['span_days'];
    $span_months = ((date('Y') - date('Y', $span)) * 12) + (date('m') - date('m', $span)) + 1;
    $span_weeks  = (int)($span_days / 7) + ($span_days % 7 ? 1 : 0);
}

if (isset($row['span']) && $row['span'] > 0)
{
    /*********************************************************/
    /* Prepare for different periods                         */
    /*********************************************************/
    
    if ($period == 'd') //day
    {
        if (!isset($limit) || $limit=='') $limit = '7';
        if (!isset($start) || $start=='') $start = '0';
        
        $title = $strDays;
        $limits = array(7, 14, 21, 28);
        
        $formatted   = $date_format;
        $unformatted = "%d%m%Y";
        $returnlimit = $limit;
        $span_period = $span_days;
        
        $begin_timestamp = mktime(0, 0, 0, date('m'), date('d') - $limit + 1 - $start, date('Y'));
        $end_timestamp     = mktime(0, 0, 0, date('m'), date('d') + 1 - $start, date('Y'));
    }
    
    if ($period == 'w') //week
    {
        if (!isset($limit) || $limit=='') $limit = '4';
        if (!isset($start) || $start=='') $start = '0';
        
        $title = $strWeeks;
        $limits = array(4, 8, 12, 16);
        
        $formatted   = $date_format;
        $unformatted = "%d%m%Y";
        $returnlimit = $limit * 7;
        $span_period = $span_weeks;
        
        $shift = date('w') - ($phpAds_config['begin_of_week'] ? 1 - (date('w') == 0 ? 7 : 0) : 0);
        $begin_timestamp = mktime(0, 0, 0, date('m'), date('d') - $shift + 7 - (7 * ($limit + $start)), date('Y'));
        $end_timestamp   = mktime(0, 0, 0, date('m'), date('d') - $shift + 7 - (7 * $start), date('Y'));
    }
    
    if ($period == 'm') // month
    {
        if (!isset($limit) || $limit=='') $limit = '6';
        if (!isset($start) || $start=='') $start = '0';
        
        $title = $strMonths;
        $limits = array(6, 12);
        
        $formatted   = $month_format;
        $unformatted = "%m%Y";
        $returnlimit = $limit;
        $span_period = $span_months;
        
        $begin_timestamp = mktime(0, 0, 0, date('m') - $limit + 1 - $start, 1, date('Y'));
        $end_timestamp   = mktime(0, 0, 0, date('m') + 1 - $start, 1, date('Y'));
    }
    
    
    
    /*********************************************************/
    /* Get total statistics                                  */
    /*********************************************************/
    if (phpAds_isUser(phpAds_Admin)) {
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)             AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)            AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)       AS sum_conversions " .
                "FROM " .
                    $phpAds_config['tbl_adstats'] .
                    (isset($lib_history_where) ? " WHERE " . $lib_history_where : "");
            
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Be careful with the logic if you want to re-include the use of this variable - 
                    // you can't just un-comment the following code!
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (!isset($lib_history_where) && isset($lib_history_source) ? $lib_history_source : '') .
                    //
                    // (isset($lib_history_where) && isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
                    //
                    // (!isset($lib_history_where) && !isset($lib_history_source) ? $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " : "AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid ") .
    } elseif (phpAds_isUser(phpAds_Client)) {
    
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)             AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)            AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)       AS sum_conversions " .
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_banners'] . ", " .
            
                    $phpAds_config['tbl_campaigns'] . " " .
            
                "WHERE " .
                
                    (isset($lib_history_where) ? $lib_history_where . " AND " : "") .
            
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Be careful with the logic if you want to re-include the use of this variable - 
                    // you can't just un-comment the following code!
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (!isset($lib_history_where) && isset($lib_history_source) ? $lib_history_source : '') .
                    //
                    // (isset($lib_history_where) && isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
                    //
                    // (!isset($lib_history_where) && !isset($lib_history_source) ? $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " : "AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid ") .
                    $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
                
                    "AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
            
                    "AND ".$phpAds_config['tbl_campaigns'].".clientid = " . phpAds_getUserID();
    } elseif (phpAds_isUser(phpAds_Affiliate)) {
        
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)             AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)            AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)       AS sum_conversions " .
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_zones'] . " " .
            
                "WHERE " .
                
                    (isset($lib_history_where) ? $lib_history_where . " AND " : "") .
            
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Be careful with the logic if you want to re-include the use of this variable - 
                    // you can't just un-comment the following code!
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (!isset($lib_history_where) && isset($lib_history_source) ? $lib_history_source : '') .
                    //
                    // (isset($lib_history_where) && isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
                    //
                    // (!isset($lib_history_where) && !isset($lib_history_source) ? $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " : "AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid ") .
                    $phpAds_config['tbl_adstats'].".zoneid = ".$phpAds_config['tbl_zones'].".zoneid " .
                
                    "AND ".$phpAds_config['tbl_zones'].".affiliateid = " . phpAds_getUserID();                 
                    
    } elseif (phpAds_isUser(phpAds_Agency)) {

       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)             AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)            AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)       AS sum_conversions " .
                "FROM " .
                    $phpAds_config['tbl_adstats'] .
                    (isset($lib_history_where) ? " WHERE " . $lib_history_where : "");

       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)             AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)            AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)       AS sum_conversions " .
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_banners'] . ", " .
                    $phpAds_config['tbl_campaigns'] . ", " .
                    $phpAds_config['tbl_clients'] . " " .
                "WHERE " .
                    (isset($lib_history_where) ? $lib_history_where . " AND " : "") .
            
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Be careful with the logic if you want to re-include the use of this variable - 
                    // you can't just un-comment the following code!
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (!isset($lib_history_where) && isset($lib_history_source) ? $lib_history_source : '') .
                    //
                    // (isset($lib_history_where) && isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
                    //
                    // (!isset($lib_history_where) && !isset($lib_history_source) ? $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " : "AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid ") .
                    $phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
                    "AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
                    "AND ".$phpAds_config['tbl_campaigns'].".clientid = ".$phpAds_config['tbl_clients'].".clientid " .
                    "AND ".$phpAds_config['tbl_clients'].".agencyid=" . phpAds_getUserID();
    
    } else {
    
        global $strErrorBadUserType;
        echo "<div class='errormessage' style='width: 400px;'><img class='errormessage' src='images/errormessage.gif' align='absmiddle' alt=''>";
        echo "<span class='tab-r'>$strErrorBadUserType</span></div>";
        
    }
    $result = phpAds_dbQuery($query);
    if ($row = phpAds_dbFetchArray($result)) {
        $totals['views']        = $row['sum_views'];
        $totals['clicks']        = $row['sum_clicks'];
        $totals['conversions']    = $row['sum_conversions'];
    }
    
    /*********************************************************/
    /* Get statistics for selected period                    */
    /*********************************************************/
    $begin = date('Ymd', $begin_timestamp);
    $end   = date('Ymd', $end_timestamp);
    
    if (phpAds_isUser(phpAds_Admin)) {
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)                           AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)                          AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)                     AS sum_conversions, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$formatted."')   AS date, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$unformatted."') AS date_u " .
                
                "FROM " .
                    $phpAds_config['tbl_adstats'] . " " .
                "WHERE " .
                
                    $phpAds_config['tbl_adstats'].".day >= " . $begin . " AND ".$phpAds_config['tbl_adstats'].".day < " . $end . " " .
    
                    (isset($lib_history_where) ? 'AND ' . $lib_history_where . ' ': '') .
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
    
                "GROUP BY " .
                    "date_u " .
                "ORDER BY " .
                    "date_u DESC " .
                "LIMIT " .
                    $returnlimit;
    } elseif (phpAds_isUser(phpAds_Client)) {
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)                           AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)                          AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)                     AS sum_conversions, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$formatted."')   AS date, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$unformatted."') AS date_u " .
                
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_banners'] . ", " .
            
                    $phpAds_config['tbl_campaigns'] . " " .
            
                "WHERE " .
                
                    $phpAds_config['tbl_adstats'].".day >= " . $begin . " AND ".$phpAds_config['tbl_adstats'].".day < " . $end . " " .
    
                    (isset($lib_history_where) ? 'AND ' . $lib_history_where : '') .
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
    
                    " AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
            
                    " AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
            
                    " AND ".$phpAds_config['tbl_campaigns'].".clientid = " . phpAds_getUserID() . " " .
                "GROUP BY " .
                    "date_u " .
                "ORDER BY " .
                    "date_u DESC " .
                "LIMIT " .
                    $returnlimit;
    } elseif (phpAds_isUser(phpAds_Affiliate)) {                    
                    
       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)                           AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)                          AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)                     AS sum_conversions, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$formatted."')   AS date, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$unformatted."') AS date_u " .
                
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_zones'] . " " .
                
                "WHERE " .
                
                    $phpAds_config['tbl_adstats'].".day >= " . $begin . " AND ".$phpAds_config['tbl_adstats'].".day < " . $end . " " .
    
                    (isset($lib_history_where) ? 'AND ' . $lib_history_where . ' ' : '') .
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .
    
                    " AND ".$phpAds_config['tbl_adstats'].".zoneid = ".$phpAds_config['tbl_zones'].".zoneid " .
            
                    " AND ".$phpAds_config['tbl_zones'].".affiliateid=" . phpAds_getUserID() . " " .
                "GROUP BY " .
                    "date_u " .
                "ORDER BY " .
                    "date_u DESC " .
                "LIMIT " .
                    $returnlimit;                  
        
    } elseif (phpAds_isUser(phpAds_Agency)) {
    

       $query = "SELECT " .
                    "SUM(".$phpAds_config['tbl_adstats'].".views)                           AS sum_views, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".clicks)                          AS sum_clicks, " .
                    "SUM(".$phpAds_config['tbl_adstats'].".conversions)                     AS sum_conversions, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$formatted."')   AS date, " .
                    "DATE_FORMAT(".$phpAds_config['tbl_adstats'].".day, '".$unformatted."') AS date_u " .
                
                "FROM " .
                    $phpAds_config['tbl_adstats'] . ", " .
                    $phpAds_config['tbl_banners'] . ", " .
                    $phpAds_config['tbl_campaigns'] . ", " .
                    $phpAds_config['tbl_clients'] . " " .
                
                "WHERE " .
                
                    $phpAds_config['tbl_adstats'].".day >= " . $begin . " AND ".$phpAds_config['tbl_adstats'].".day < " . $end . " " .
                    (isset($lib_history_where) ? ' AND ' . $lib_history_where : '') .
                    // 2004-03-03
                    // Commented out by use of $lib_history_source as does not appear to be used anywhere
                    // Andrew Hill
                    // andrew@m3.net
                    //
                    // (isset($lib_history_source) ? 'AND ' . $lib_history_source : '') .

                    " AND ".$phpAds_config['tbl_adstats'].".bannerid = ".$phpAds_config['tbl_banners'].".bannerid " .
                    " AND ".$phpAds_config['tbl_banners'].".campaignid = ".$phpAds_config['tbl_campaigns'].".campaignid " .
                    " AND ".$phpAds_config['tbl_campaigns'].".clientid = ".$phpAds_config['tbl_clients'].".clientid " .
                    " AND ".$phpAds_config['tbl_clients'].".agencyid=" . phpAds_getUserID() . " " .
                "GROUP BY " .
                    "date_u " .
                "ORDER BY " .
                    "date_u DESC " .
                "LIMIT " .
                    $returnlimit;
    } else {
    
        global $strErrorBadUserType;
        echo "<div class='errormessage' style='width: 400px;'><img class='errormessage' src='images/errormessage.gif' align='absmiddle' alt=''>";
        echo "<span class='tab-r'>$strErrorBadUserType</span></div>";
        
    }
                
    $result = phpAds_dbQuery($query);
    
    while ($row = phpAds_dbFetchArray($result))    {
        $stats[$row['date']]['sum_views']             = $row['sum_views'];
        $stats[$row['date']]['sum_clicks']             = $row['sum_clicks'];
        $stats[$row['date']]['sum_conversions']     = $row['sum_conversions'];
    }
    
    

    /*********************************************************/
    /* Main code                                             */
    /*********************************************************/
    
    echo "<form action='".$HTTP_SERVER_VARS['PHP_SELF']."'>";
    
    if (isset($lib_history_params))
        for (reset($lib_history_params); $key = key($lib_history_params); next($lib_history_params))
            echo "<input type='hidden' name='".$key."' value='".$lib_history_params[$key]."'>";
    
    echo "<select name='period' onChange='this.form.submit();' accesskey='".$keyList."' tabindex='".($tabindex++)."'>";
        echo "<option value='d'".($period == 'd' ? ' selected' : '').">".$strDailyHistory."</option>";
        echo "<option value='w'".($period == 'w' ? ' selected' : '').">".$strWeeklyHistory."</option>";
        echo "<option value='m'".($period == 'm' ? ' selected' : '').">".$strMonthlyHistory."</option>";
    echo "</select>";
    
    echo "&nbsp;&nbsp;";
    echo "<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif' border='0' name='submit'>&nbsp;";
    
    phpAds_ShowBreak();
    echo "</form>";
    
    echo "<br><br>";
    
    
    //-----------------------------------------------------------------------------------------------------------------------
    // Output for day or month
    //-----------------------------------------------------------------------------------------------------------------------

    if ($period == 'm' || $period == 'd')
    {
    
        // Column delimiters. Prevents columns from randomly changing width
        echo "
        <table border='0' width='100%' cellpadding='0' cellspacing='0'>
        <tr height='25'>
            <td><img src='images/spacer.gif' width='200' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
        </tr>
        <tr height='25'>";

        $url = "{$HTTP_SERVER_VARS['PHP_SELF']}?period=$period";
        $url .= !empty($clientid) ? "&clientid=$clientid" : '';
        $url .= !empty($campaignid) ? "&campaignid=$campaignid" : '';

        // Title column
        echo "<td><b>&nbsp;&nbsp;<a href='$url&listorder=key'>$title</a>";
        if ($listorder == $title || $listorder == "")
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo '</b></td>';
        
        // Views column
        echo "<td align='$phpAds_TextAlignRight'><b><a href='$url&listorder=views'>{$GLOBALS['strViews']}</a>";
        if ($listorder == 'views')
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo '</b></td>';
        
        // Clicks column
        echo "<td align='$phpAds_TextAlignRight'><b><a href='$url&listorder=clicks'>{$GLOBALS['strClicks']}</a>";
        if ($listorder == 'clicks')
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo '</b></td>';
                
        // CTR column
        echo "<td align='$phpAds_TextAlignRight'><b><a href='$url&listorder=ctr'>{$GLOBALS['strCTRShort']}</a>";
        if ($listorder == 'ctr')
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo '</b></td>';
        
        // Conversion column
        echo "<td align='$phpAds_TextAlignRight'><b><a href='$url&listorder=conversions'>{$GLOBALS['strConversions']}</a>";
        if ($listorder == 'conversions')
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo '</b></td>';
        
        // Sales Ratio column
        echo "<td align='$phpAds_TextAlignRight'><b><a href='$url&listorder=cnvr'>{$GLOBALS['strCNVRShort']}</a>";
        if ($listorder == 'cnvr')
            echo $orderdirection == 'up'
                 ? "<a href='$url&orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>"
                 : "<a href='$url&orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
        echo "
            </b></td>
        </tr>
        <tr>
            <td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td>
        </tr>";

        
        //-----------------------------------------------------------------------------------------------------------------------
        //Column headers END
        //-----------------------------------------------------------------------------------------------------------------------


        //-----------------------------------------------------------------------------------------------------------------------
        //Calculate stats for day or month
        //-----------------------------------------------------------------------------------------------------------------------
        
        // total views, clicks and conversions for the period shown
        $totalviews          = 0;
        $totalclicks         = 0;
        $totalconversions    = 0;
        
        
        $today                  = time();
                
        $array = array();
        
        for ($d=0; $d < $limit; $d++)
        {
            switch ($period)
            {
                case 'm':    $timestamp = mktime (0, 0, 0, date('m') - $d - $start, 1, date('Y'));
                            $span      = mktime (0, 0, 0, date('m', $span), 1, date('Y', $span));
                            break;
                        
                case 'd':    $timestamp = mktime (0, 0, 0, date('m'), date('d') - $d - $start, date('Y'));
                            $span      = mktime (0, 0, 0, date('m', $span), date('d', $span), date('Y', $span));
                            break;
            }
            
            $key = strftime ($formatted, $timestamp);
            $array[$d]['key'] = $key;
            $array[$d]['timestamp'] = $timestamp;
            $array[$d]['span'] = $span;            
            
            if (isset($stats[$array[$d]['key']]))
            {
                $array[$d]['views']          = isset($stats[$key]['sum_views']) ? $stats[$key]['sum_views'] : 0;
                $array[$d]['clicks']         = isset($stats[$key]['sum_clicks']) ? $stats[$key]['sum_clicks'] : 0;
                $array[$d]['ctr']            = phpAds_buildRatioPercentage($array[$d]['clicks'], $array[$d]['views']);
                $array[$d]['conversions']    = isset($stats[$key]['sum_conversions']) ? $stats[$key]['sum_conversions'] : 0;
                $array[$d]['cnvr']            = phpAds_buildRatioPercentage($array[$d]['conversions'], $array[$d]['clicks']);
                
                $totalviews          += $array[$d]['views'];
                $totalclicks         += $array[$d]['clicks'];
                $totalconversions     += $array[$d]['conversions'];
                
                $array[$d]['available'] = true;
                
            }
            else
            {
                if ($timestamp >= $span)
                {
                    $array[$d]['views']          = 0;
                    $array[$d]['clicks']         = 0;
                    $array[$d]['ctr']            = phpAds_buildRatioPercentage(0, 0);
                    $array[$d]['conversions']    = 0;
                    $array[$d]['cnvr']            = phpAds_buildRatioPercentage(0, 0);
                    $array[$d]['available']     = true;
                }
                else
                {
                    $array[$d]['views']          = '-';
                    $array[$d]['clicks']         = '-';
                    $array[$d]['ctr']            = '-';
                    $array[$d]['conversions']    = '-';
                    $array[$d]['cnvr']            = '-';
                    $array[$d]['available']     =  false;
                }
            }
        }
        
    //-----------------------------------------------------------------------------------------------------------------------
    // Sort array according to selected column and direction
    //-----------------------------------------------------------------------------------------------------------------------
    switch ($listorder)
    {
        case 'days':        phpAds_sortArray($array,'days',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                
        case 'months':      phpAds_sortArray($array,'months',($orderdirection == 'up' ? TRUE : FALSE));
                            break;

        case 'views':       phpAds_sortArray($array,'views',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                        
                        
        case 'clicks':      phpAds_sortArray($array,'clicks',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                        
                        
        case 'ctr':         phpAds_sortArray($array,'ctr',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                        
                        
        case 'conversions': phpAds_sortArray($array,'conversions',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                        
                        
        case 'cnvr':        phpAds_sortArray($array,'cnvr',($orderdirection == 'up' ? TRUE : FALSE));
                            break;
                            
                        
        default:    break;
    
    }
    //-----------------------------------------------------------------------------------------------------------------------
    // Sort array according to selected column and direction END
    //-----------------------------------------------------------------------------------------------------------------------
        
        
        foreach ($array as $d=>$array)
        {
            $bgcolor="#FFFFFF";
            $d % 2 ? 0: $bgcolor= "#F6F6F6";
            
            echo "<tr>";
            
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;";
            echo "<img src='images/icon-date.gif' align='absmiddle' alt=''>&nbsp;";

    
            if (isset($lib_history_hourlyurl) && $period == 'd' && $array['available'])
                echo "<a href='".$lib_history_hourlyurl.$params."day=".strftime('%Y%m%d', $array['timestamp'])."'>".$array['key']."</a></td>";
            else
                echo $array['key']."</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' bgcolor='$bgcolor'>".phpAds_formatNumber($array['views'])."</td>";
            echo "<td align='".$phpAds_TextAlignRight."' bgcolor='$bgcolor'>".phpAds_formatNumber($array['clicks'])."</td>";
            echo "<td align='".$phpAds_TextAlignRight."' bgcolor='$bgcolor'>".$array['ctr']."</td>";
            echo "<td align='".$phpAds_TextAlignRight."' bgcolor='$bgcolor'>".phpAds_formatNumber($array['conversions'])."</td>";
            echo "<td align='".$phpAds_TextAlignRight."' bgcolor='$bgcolor'>".$array['cnvr']."</td>";
            echo "</tr>";
            
            echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        }
        
        
            
        $previous = $start < $limit ? 0 : $start - $limit;
        $next = $start + $limit;
        
        echo "<tr>";
        echo "<td height='35' colspan='1' align='".$phpAds_TextAlignLeft."'>";
            echo "&nbsp;".$title.":&nbsp;";
            for ($i = 0; $i < count($limits); $i++)
            {
                if ($limit == $limits[$i])
                    echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&start=".$start."&limit=".$limits[$i]."'><u>".$limits[$i]."</u></a>";
                else
                    echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&start=".$start."&limit=".$limits[$i]."'>".$limits[$i]."</a>";
                
                if ($i < count($limits) - 1) echo "&nbsp;|&nbsp;";
            }
        echo "</td>";
        echo "<td height='35' colspan='5' align='".$phpAds_TextAlignRight."'>";
            if ($start > 0)
            {
                echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&limit=".$limit."&start=".$previous."' accesskey='".$keyPrevious."'>";
                echo "<img src='images/arrow-l.gif' border='0' align='absmiddle' alt=''>".$strPrevious_Key."</a>";
            }
            if ($timestamp > $span)
            {
                if ($start > 0) echo "&nbsp;|&nbsp;";
                
                echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&limit=".$limit."&start=".$next."' accesskey='".$keyNext."'>";
                echo $strNext_Key."<img src='images/arrow-r.gif' border='0' align='absmiddle' alt=''></a>";
            }
        echo "</td>";
        echo "</tr>";
        
        echo "<tr><td colspan='6'>&nbsp;</td></tr>";
        $span_this = (($start + $limit < $span_period ? $start + $limit : $span_period) - $start);

        echo "<tr bgcolor='#FFFFFF' height='25'>";
        echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'>&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strViews."</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strClicks."</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strCTRShort."</b>&nbsp;&nbsp;</td>";
        echo "<td width='15%' align='".$phpAds_TextAlignRight."' nowrap height='25'><b>".$strConversions."</b>&nbsp;&nbsp;</td>";
        echo "<td width='15%' align='".$phpAds_TextAlignRight."' nowrap height='25'><b>".$strCNVR."</b>&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";

        // Total for this period
        echo "<tr>";
        echo "<td height='25'>&nbsp;<b>$strTotalThisPeriod</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>(".number_format(($totals['views'] ? $totalviews / $totals['views'] * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%)&nbsp;&nbsp;".phpAds_formatNumber($totalviews)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>(".number_format(($totals['clicks'] ? $totalclicks / $totals['clicks'] * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%)&nbsp;&nbsp;".phpAds_formatNumber($totalclicks)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totalclicks, $totalviews)."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalconversions)."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totalconversions, $totalclicks);
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // Average for this period
        echo "<tr>";
        echo "<td height='25'>&nbsp;$strAverageThisPeriod (".$span_this." ".$title.")</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalviews / $span_this)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalclicks / $span_this)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalconversions / $span_this)."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>"."&nbsp;&nbsp;</td>";
        
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // Total
        echo "<tr>";
        echo "<td height='25'>&nbsp;<b>$strTotal</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber((int)$totals['views'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber((int)$totals['clicks'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totals['clicks'], $totals['views'])."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber((int)$totals['conversions'])."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totals['conversions'], $totals['clicks']);
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // Average
        echo "<tr>";
        echo "<td height='25'>&nbsp;$strAverage (".$span_period." ".$title.")</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['views'] / $span_period : 0)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['clicks'] / $span_period : 0)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['conversions'] / $span_period : 0)."&nbsp;&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>"."&nbsp;&nbsp;</td>";
    
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        echo "</table>";
    }
    
    
    // Weekly View
    if ($period == 'w')
    {
        // Header
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        echo "<tr bgcolor='#FFFFFF' height='25'>";
        echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'>&nbsp;<b>$title</b></td>";
        echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'>&nbsp;</td>";
        
        for ($i=0; $i < 7; $i++)
            echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b>".$strDayShortCuts[($i + ($phpAds_config['begin_of_week'] ? 1 : 0)) % 7]."</b></td>";
        
        echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25' width='10%'><b>$strAvg</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' nowrap height='25'><b>$strTotal</b>&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='11' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // calculate totals
        $totalviews            = 0;
        $totalclicks        = 0;
        $totalconversions    = 0;
        
        $today = time();
        
        for ($d=0;$d<$limit;$d++)
        {
            $totalweekviews = 0;
            $totalweekclicks = 0;
            $totalweekconversions = 0;
    
            $bgcolor="#FFFFFF";
            $d % 2 ? 0: $bgcolor= "#F6F6F6";
            
            $shift = date('w') - ($phpAds_config['begin_of_week'] ? 1 - (date('w') == 0 ? 7 : 0) : 0);
            $week_timestamp = mktime(0, 0, 0, date('m'), date('d') - $shift - (7 * ($d + $start)), date('Y'));
            $week_formatted = strftime("%V") != '' ? strftime ($weekiso_format, $week_timestamp + ($phpAds_config['begin_of_week'] ? 0 : (60*60*24))) : 
                                                       strftime ($week_format, $week_timestamp + ($phpAds_config['begin_of_week'] ? 0 : (60*60*24)));
            
            $days = 0;
            
            for ($i = 0; $i < 7; $i++)
            {
                $day_timestamp = $week_timestamp + ($i * (60 * 60 * 24));
                $key = strftime ($formatted, $week_timestamp + ($i * (60 * 60 * 24)));
                
                if (isset($stats[$key]))
                {
                    $views[$i]          = isset($stats[$key]['sum_views']) ? $stats[$key]['sum_views'] : 0;
                    $clicks[$i]         = isset($stats[$key]['sum_clicks']) ? $stats[$key]['sum_clicks'] : 0;
                    $ctr[$i]            = phpAds_buildCTR($views[$i], $clicks[$i]);
                    $conversions[$i]    = isset($stats[$key]['sum_conversions']) ? $stats[$key]['sum_conversions'] : 0;
                    $sr[$i]                = $stats[$key]['sum_clicks'] >0 ? ($conversions[$i] / $clicks[$i]) * 100 : 0;

                    $totalweekviews      += $views[$i];
                    $totalweekclicks     += $clicks[$i];
                    $totalweekconversions += $conversions[$i];
                    $totalweeksr        += $sr[$i];
                    
                    $views[$i]             = phpAds_formatNumber($views[$i]);
                    $clicks[$i]         = phpAds_formatNumber($clicks[$i]);
                    $conversions[$i]    = phpAds_formatNumber($conversions[$i]);
                    $sr[$i]                = number_format($sr[$i], $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator) . "%";
                    $days++;
                }
                else
                {
                    if ($day_timestamp >= $span && $day_timestamp <= $today)
                    {
                        $views[$i]          = 0;
                        $clicks[$i]         = 0;
                        $ctr[$i]            = phpAds_buildRatioPercentage($clicks[$i], $views[$i]);
                        $conversions[$i]    = 0;
                        $sr[$i]                = "0.00%";
                        $days++;
                    }
                    else
                    {
                        $views[$i]          = '-';
                        $clicks[$i]         = '-';
                        $ctr[$i]            = '-';
                        $conversions[$i]    = '-';
                        $sr[$i]                = '-';
                    }
                }
            }
            
            $totalviews         += $totalweekviews;
            $totalclicks         += $totalweekclicks;
            $totalconversions     += $totalweekconversions;
            
            
            if ($days > 0)
            {
                $avgviews          = $totalweekviews / $days;
                $avgclicks         = $totalweekclicks / $days;
                $avgctr            = phpAds_buildRatioPercentage($avgclicks, $avgviews);
                $avgconversions = $totalweekconversions / $days;
                $avgsr            = phpAds_buildRatioPercentage($avgconversions, $avgclicks);
                
                $avgviews          = phpAds_formatNumber($avgviews);
                $avgclicks         = phpAds_formatNumber($avgclicks);
                $avgconversions    = phpAds_formatNumber($avgconversions);
                
                $totalweekctr     = phpAds_buildRatioPercentage($totalweekclicks, $totalweekviews);
                $totalweeksr    = phpAds_buildRatioPercentage($totalweekconversions, $totalweekclicks);
            }
            else
            {
                $avgviews          = '-';
                $avgclicks         = '-';
                $avgctr            = '-';
                $avgconversions    = '-';
                $avgsr            = '-';
                
                $totalweekviews         = '-';
                $totalweekclicks         = '-';
                $totalweekctr             = '-';
                $totalweekconversions     = '-';
                $totalweeksr            = '-';
            }
            
            echo "<tr>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$week_formatted."</td>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$strDate."</td>";
                
            for ($i = 0; $i < 7; $i++)
            {
                $day_timestamp = $week_timestamp + ($i * (60 * 60 * 24));
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>";
                
                $available = ($views[$i] && $views[$i] != '-') || ($clicks[$i] && $clicks[$i] != '-');
                
                if (isset($lib_history_hourlyurl) && $available)
                    echo "<a href='".$lib_history_hourlyurl.$params."day=".strftime('%Y%m%d', $day_timestamp)."'>".strftime($day_format, $day_timestamp)."</a>&nbsp;</td>";
                else
                    echo strftime($day_format, $day_timestamp)."&nbsp;</td>";
            }
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "</tr>";
            
            
            
            
            // Views
            echo "<tr>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$strViews."</td>";
            
            for ($i = 0; $i < 7; $i++)
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$views[$i]."&nbsp;</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$avgviews."&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".phpAds_formatNumber($totalweekviews)."&nbsp;</td>";
            echo "</tr>";
            
            
            // Clicks
            echo "<tr>";
            echo "<td height='15' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td height='15' bgcolor='$bgcolor'>&nbsp;".$strClicks."</td>";
            
            for ($i = 0; $i < 7; $i++)
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='15' bgcolor='$bgcolor'>".$clicks[$i]."&nbsp;</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='15' bgcolor='$bgcolor'>".$avgclicks."&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='15' bgcolor='$bgcolor'>".phpAds_formatNumber($totalweekclicks)."&nbsp;</td>";
            echo "</tr>";
            
            
            // CTR
            echo "<tr>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$strCTRShort."</td>";
            
            for ($i = 0; $i < 7; $i++)
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$ctr[$i]."&nbsp;</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$avgctr."&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$totalweekctr."&nbsp;</td>";
            echo "</tr>";
            
            // Ad Sales
            echo "<tr>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$strConversions."</td>";
            
            for ($i = 0; $i < 7; $i++)
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$conversions[$i]."&nbsp;</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$avgconversions."&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$totalweekconversions."&nbsp;</td>";
            echo "</tr>";

            // Ad Ratio
            echo "<tr>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;</td>";
            echo "<td height='25' bgcolor='$bgcolor'>&nbsp;".$strCNVR."</td>";
            
            for ($i = 0; $i < 7; $i++)
                echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$sr[$i]."&nbsp;</td>";
            
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$avgsr."&nbsp;</td>";
            echo "<td align='".$phpAds_TextAlignRight."' nowrap  height='25' bgcolor='$bgcolor'>".$totalweeksr."&nbsp;</td>";
            echo "</tr>";
            
            echo "<tr><td height='1' colspan='11' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
            
        }
        
        
        $previous = $start < $limit ? 0 : $start - $limit;
        $next = $start + $limit;
        
        echo "<tr>";
        echo "<td height='35' colspan='2' align='".$phpAds_TextAlignLeft."'>";
            echo "&nbsp;".$title.":&nbsp;";
            for ($i = 0; $i < count($limits); $i++)
            {
                if ($limit == $limits[$i])
                    echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&start=".$start."&limit=".$limits[$i]."'><u>".$limits[$i]."</u></a>";
                else
                    echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&start=".$start."&limit=".$limits[$i]."'>".$limits[$i]."</a>";
                
                if ($i < count($limits) - 1) echo "&nbsp;|&nbsp;";
            }
        echo "</td>";
        echo "<td height='35' colspan='9' align='".$phpAds_TextAlignRight."'>";
            if ($start > 0)
            {
                echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&limit=".$limit."&start=".$previous."'>";
                echo "<img src='images/arrow-l.gif' border='0' align='absmiddle' alt=''>".$strPrevious."</a>";
            }
            if ($day_timestamp > $span)
            {
                if ($start > 0) echo "&nbsp;|&nbsp;";
                
                echo "<a href='".$HTTP_SERVER_VARS['PHP_SELF'].$params."period=".$period."&limit=".$limit."&start=".$next."'>";
                echo $strNext."<img src='images/arrow-r.gif' border='0' align='absmiddle' alt=''></a>";
            }
        echo "</td>";
        echo "</tr>";
        
        echo "</table>";
    
        $span_this = (($start + $limit < $span_period ? $start + $limit : $span_period) - $start);

        //Totals
        echo "<br><br>";
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        echo "<tr bgcolor='#FFFFFF' height='25'>";
        echo "<td align='".$phpAds_TextAlignLeft."' nowrap height='25'>&nbsp;</td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strViews."</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strClicks."</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' width='15%' nowrap height='25'><b>".$strCTRShort."</b></td>";
        echo "<td width='15%' align='".$phpAds_TextAlignRight."' nowrap height='25'><b>".$strConversions."</b></td>";
        echo "<td width='15%' align='".$phpAds_TextAlignRight."' nowrap height='25'><b>".$strCNVR."</b>&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // Total this period
        echo "<tr>";
        echo "<td height='25'>&nbsp;<b>$strTotalThisPeriod</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>(".number_format(($totals['views'] ? $totalviews / $totals['views'] * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%)&nbsp;&nbsp;".phpAds_formatNumber($totalviews)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>(".number_format(($totals['clicks'] ? $totalclicks / $totals['clicks'] * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%)&nbsp;&nbsp;".phpAds_formatNumber($totalclicks)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totalclicks, $totalviews)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>(".number_format(($totals['conversions'] ? $totalconversions / $totals['conversions'] * 100 : 0), $phpAds_config['percentage_decimals'], $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)."%)&nbsp;&nbsp;".phpAds_formatNumber($totalconversions)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totalconversions, $totalclicks)."&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        //Average this period
        echo "<tr>";
        echo "<td height='25'>&nbsp;$strAverageThisPeriod (".$span_this." ".$title.")</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalviews / $span_this)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalclicks / $span_this)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totalconversions / $span_this)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        // Total
        echo "<tr>";
        echo "<td height='25'>&nbsp;<b>$strTotal</b></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totals['views'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totals['clicks'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totals['clicks'], $totals['views'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($totals['conversions'])."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_buildRatioPercentage($totals['conversions'], $totals['clicks'])."&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";

        // Average
        echo "<tr>";
        echo "<td height='25'>&nbsp;$strAverage (".$span_period." ".$title.")</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['views'] / $span_period : 0)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['clicks'] / $span_period : 0)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'></td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>".phpAds_formatNumber($span_period ? $totals['conversions'] / $span_period : 0)."</td>";
        echo "<td align='".$phpAds_TextAlignRight."' height='25'>&nbsp;&nbsp;</td>";
        echo "</tr>";
        echo "<tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        
        echo "</table>";
    }
    
    if (phpAds_GDImageFormat() != "none")
    {
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        echo "<tr><td height='20' colspan='1'>&nbsp;</td></tr>";
        echo "<tr><td bgcolor='#FFFFFF' colspan='1'>";
        echo "<img src='graph-history.php".$params."period=".$period."&start=".$start."&limit=".$limit."' border='0' alt=''>";
        echo "</td></tr><tr><td height='10' colspan='1'>&nbsp;</td></tr>";
        echo "<tr><td height='1' colspan='1' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
        echo "</table>";
    }
}
else
{
    echo "<br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle' alt=''>";
    echo $strNoStats.'</div>';
}

echo "<br><br>";
?>
