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
$Id: lib-statistics.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Define defaults
$clientCache = array();
$campaignCache = array();
$bannerCache = array();
$zoneCache = array();
$affiliateCache = array();



/*********************************************************/
/* Limit a string to a number of characters              */
/*********************************************************/

function phpAds_breakString ($str, $maxLen, $append = "...")
{
    return strlen($str) > $maxLen 
        ? rtrim(substr($str, 0, $maxLen-strlen($append))).$append 
        : $str;
}


/*********************************************************/
/* Build the client name from ID and name                */
/*********************************************************/

function phpAds_buildName ($id, $name)
{
    return ("<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$id]</span> ".$name);
}


/*********************************************************/
/* Fetch the client name from the database               */
/*********************************************************/

function phpAds_getClientName ($clientid)
{
    if ($clientid != '' && $clientid != 0)
    {
        $client_details = phpAds_getClientDetails($clientid);
        return (phpAds_BuildName ($clientid, $client_details['clientname']));
    }
    else
        return ($strUntitled);
}
/*********************************************************/
/* Fetch the agency name from the database               */
/*********************************************************/

function phpAds_getAgencyName ($id)
{
    global $phpAds_config;
            
        $res = phpAds_dbQuery("
            SELECT
                name
            FROM
                ".$phpAds_config['tbl_agency']."
            WHERE
                agencyid = ".$id
            ) or phpAds_sqlDie(); 
    
    $result = phpAds_dbFetchArray($res);
    return $result['name'];


}
/*********************************************************/
/* Fetch the campaign name from the database             */
/*********************************************************/

function phpAds_getCampaignName ($campaignid, $check_blind = false)
{
    // Andrew replaced $strUntitled with '' as $strUntitled 
    // doesn't appear to be defined anywhere!
    //$campaign_name = $strUntitled;
    $campaign_name = '';
    
    if ($campaignid != '' && $campaignid != 0)
    {
        $campaign_details = phpAds_getCampaignDetails($campaignid);
        if ($check_blind && ($campaign_details['anonymous'] == 't'))
            $campaign_name = $GLOBALS['strHiddenCampaign'] . ' ' . $campaign_details['campaignid'];
        else
            $campaign_name = phpAds_BuildName ($campaignid, $campaign_details['campaignname']);
    }
    
    return $campaign_name;

/*
    if ($campaignid != '' && $campaignid != 0)
    {
        $campaign_details = phpAds_getCampaignDetails($campaignid);
        return (phpAds_BuildName ($campaignid, $campaign_details['campaignname']));
    }
    else
        return ($strUntitled);
*/
}


/*********************************************************/
/* Fetch the tracker name from the database              */
/*********************************************************/

function phpAds_getTrackerName ($trackerid)
{
    if ($trackerid != '' && $trackerid != 0)
    {
        $tracker_details = phpAds_getTrackerDetails($trackerid);
        return (phpAds_BuildName ($trackerid, $tracker_details['trackername']));
    }
    else
        return ($strUntitled);
}

// Get any generic list order...
function phpAds_getListOrder ($listOrder='name',$orderDirection='up', $nameColumn='', $idColumn='', $viewsColumn='', $clicksColumn='', $conversionColumn='', $ctrColumn='', $srColumn='')
{
    $direction = ($orderDirection == 'down') ? ' DESC' : ' ASC';
    $sqlTableOrder = '';
    switch ($listOrder) {
        case 'name':
            if (is_array($nameColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $nameColumn) . $direction;
            else
                $sqlTableOrder = !empty($nameColumn) ? " ORDER BY $nameColumn $direction" : '';
            break;
        case 'id':
            if (is_array($idColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $idColumn) . $direction;
            else
                $sqlTableOrder = !empty($idColumn) ? " ORDER BY $idColumn $direction" : '';
            break;
        case 'views':
            if (is_array($viewsColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $viewsColumn) . $direction;
            else
                $sqlTableOrder = !empty($viewsColumn) ? " ORDER BY $viewsColumn $direction" : '';
            break;
        case 'clicks':
            if (is_array($clicksColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $clicksColumn) . $direction;
            else
                $sqlTableOrder = !empty($clicksColumn) ? " ORDER BY $clicksColumn $direction" : '';
            break;
        case 'conversions':
            if (is_array($conversionsColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $conversionsColumn) . $direction;
            else
                $sqlTableOrder = !empty($conversionsColumn) ? " ORDER BY $conversionsColumn $direction" : '';
            break;
        case 'ctr':
            if (is_array($ctrColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $ctrColumn) . $direction;
            else
                $sqlTableOrder = !empty($ctrColumn) ? " ORDER BY $ctrColumn $direction" : '';
            break;
        case 'cnvr':
            if (is_array($srColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $srColumn) . $direction;
            else
                $sqlTableOrder = !empty($srColumn) ? " ORDER BY $srColumn $direction" : '';
            break;
        default:
            if (is_array($nameColumn))
                $sqlTableOrder = ' ORDER BY ' . implode($direction . ',', $nameColumn) . $direction;
            else
                $sqlTableOrder = !empty($nameColumn) ? " ORDER BY $nameColumn $direction" : '';
            break;
    }
    return ($sqlTableOrder);
}


// Order for $phpAds_config['tbl_clients']
function phpAds_getClientListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'clientname','clientid');
}
// Order for $phpAds_config['tbl_campaigns']
function phpAds_getCampaignListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'campaignname',array('clientid','campaignid'));
}


// Order for $phpAds_config['tbl_banners']
function phpAds_getBannerListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'description','bannerid','views','clicks','conversions','ctr','cnvr');
}

function phpAds_getHourListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'hour','hour','views','clicks','conversions','ctr','cnvr');
}

// Order for $phpAds_config['tbl_trackers']
function phpAds_getTrackerListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'trackername',array('clientid','trackerid'));
}

// Order for $phpAds_config['tbl_zones']
function phpAds_getZoneListOrder ($listOrder, $orderDirection)
{
    $direction = ($orderDirection == 'down') ? 'DESC' : 'ASC'; 
    switch ($listOrder)
    {
        case 'name':
            $sqlTableOrder = " ORDER BY zonename $direction";
            break;
        case 'id':
            $sqlTableOrder = " ORDER BY zoneid $direction";
            break;
        case 'size':
            $sqlTableOrder = " ORDER BY width $direction";
            break;
        default:
            $sqlTableOrder = " ORDER BY zonename $direction";
    }
    return ($sqlTableOrder);
}

// Order for $phpAds_config['tbl_affiliates']
function phpAds_getAffiliateListOrder ($listOrder, $orderDirection)
{
    return phpAds_getListOrder($listOrder,$orderDirection,'name','affiliateid');
}


/*********************************************************/
/* Fetch the ID of the parent of a campaign              */
/*********************************************************/

function phpAds_getCampaignParentClientID ($campaignid)
{
    $campaign_details = phpAds_getCampaignDetails($campaignid);
    return $campaign_details['clientid'];
}

/*********************************************************/
/* Fetch the ID of the parent of a banner              */
/*********************************************************/

function phpAds_getBannerParentCampaignID ($bannerid)
{
    global $phpAds_config;

    $banner_result = phpAds_dbQuery(
                    "SELECT 
                        campaignid
                    FROM 
                        ".$phpAds_config['tbl_banners']."
                    WHERE
                        bannerid = ".$bannerid
                );
    
    $result = phpAds_dbFetchArray($banner_result);

    return $result['campaignid'];
}

/*********************************************************/
/* Fetch the ID of the parent of a tracker               */
/*********************************************************/

function phpAds_getTrackerParentClientID ($trackerid)
{
    $tracker_details = phpAds_getTrackerDetails($trackerid);
    return $tracker_details['clientid'];
}


/*********************************************************/
/* Fetch the name of the parent of a campaign            */
/*********************************************************/

function phpAds_getParentClientName ($campaignid)
{
    $campaign_details = phpAds_getCampaignDetails($campaignid);
    return phpAds_getClientName($campaign_details['clientid']);
}



/*********************************************************/
/* Build the banner name from ID, Description and Alt    */
/*********************************************************/

function phpAds_buildBannerName ($bannerid, $description = '', $alt = '', $limit = 30, $use_html = true)
{
    global $strUntitled;
    
    $name = '';
    
    if ($description != "")
        $name .= $description;
    elseif ($alt != "")
        $name .= $alt;
    else
        $name .= $strUntitled;
    
    
    if (strlen($name) > $limit)
        $name = phpAds_breakString ($name, $limit);
    
    if ($bannerid != '')
        $name = $use_html ? "<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$bannerid]</span> ".$name : "[id$bannerid] ".$name;
    
    return ($name);
}



/*********************************************************/
/* Fetch the banner name from the database               */
/*********************************************************/

function phpAds_getBannerName ($bannerid, $limit = 30, $id = true, $checkanonymous = false)
{
    global $phpAds_config;
    global $bannerCache;
    
    if (isset($bannerCache[$bannerid]) && is_array($bannerCache[$bannerid]))
    {
        $row = $bannerCache[$bannerid];
    }
    else
    {
        $res = phpAds_dbQuery("
            SELECT
                *
            FROM
                ".$phpAds_config['tbl_banners']."
            WHERE
                bannerid = '$bannerid'
        ") or phpAds_sqlDie();
        
        $row = phpAds_dbFetchArray($res);
        
        $bannerCache[$bannerid] = $row;
    }
    
    if ($checkanonymous)
    {
        if  (!isset($row['anonymous']) || $row['anonymous'] = '')
        {
            $anonres = phpAds_dbQuery(
                "SELECT anonymous".
                " FROM ".$phpAds_config['tbl_campaigns'].
                ",".$phpAds_config['tbl_banners'].
                " WHERE ".$phpAds_config['tbl_campaigns'].".campaignid=".$phpAds_config['tbl_banners'].".campaignid".
                " AND ".$phpAds_config['tbl_banners'].".bannerid=".$bannerid
            );
            
            $anonrow = phpAds_dbFetchArray($anonres);
            $anonymous = $anonrow['anonymous'];
            $bannerCache[$bannerid]['anonymous'] = $anonymous;
        }
        else 
            $anonymous = $row['anonymous'];
        
        
    }
    
    if ($id)
        return (phpAds_buildBannerName ($bannerid, $row['description'], $row['alt'], $limit));
    else
        return (phpAds_buildBannerName ('', $row['description'], $row['alt'], $limit));
}


/*********************************************************/
/* Build the zone name from ID and name                  */
/*********************************************************/

function phpAds_buildZoneName ($zoneid, $zonename)
{
    return ("<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$zoneid]</span> $zonename");
}


/*********************************************************/
/* Fetch the zone name from the database                 */
/*********************************************************/

function phpAds_getZoneName ($zoneid)
{
    global $phpAds_config;
    global $zoneCache;
    global $strUntitled;
    
    if ($zoneid != '' && $zoneid != 0)
    {
        if (isset($zoneCache[$zoneid]) && is_array($zoneCache[$zoneid]))
        {
            $row = $zoneCache[$zoneid];
        }
        else
        {
            $res = phpAds_dbQuery("
            SELECT
                *
            FROM
                ".$phpAds_config['tbl_zones']."
            WHERE
                zoneid = '$zoneid'
            ") or phpAds_sqlDie();
            
            $row = phpAds_dbFetchArray($res);
            
            $zoneCache[$zoneid] = $row;
        }
        
        return (phpAds_BuildZoneName ($zoneid, $row['zonename']));
    }
    else
        return ($strUntitled);
}



/*********************************************************/
/* Build the affiliate name from ID and name             */
/*********************************************************/

function phpAds_buildAffiliateName ($affiliateid, $name)
{
    return ("<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$affiliateid]</span> $name");
}



/*********************************************************/
/* Fetch the affiliate name from the database            */
/*********************************************************/

function phpAds_getAffiliateName ($affiliateid)
{
    global $phpAds_config;
    global $affiliateCache;
    global $strUntitled;
    
    if ($affiliateid != '' && $affiliateid != 0)
    {
        if (isset($affiliateCache[$affiliateid]) && is_array($affiliateCache[$affiliateid]))
        {
            $row = $affiliateCache[$affiliateid];
        }
        else
        {
            $res = phpAds_dbQuery("
                SELECT
                    *
                FROM
                    ".$phpAds_config['tbl_affiliates']."
                WHERE
                    affiliateid = '$affiliateid'
            ") or phpAds_sqlDie();
            
            $row = phpAds_dbFetchArray($res);
            
            $affiliateCache[$affiliateid] = $row;
        }
        
        return (phpAds_BuildAffiliateName ($affiliateid, $row['name']));
    }
    else
        return ($strUntitled);
}



/*********************************************************/
/* Replace variables in HTML or external banner          */
/*********************************************************/

function phpAds_replaceVariablesInBannerCode ($htmlcode)
{
    global $phpAds_config;
    
    // Parse for variables
    $htmlcode = str_replace ('{timestamp}',    time(), $htmlcode);
    $htmlcode = str_replace ('%7Btimestamp%7D',    time(), $htmlcode);
    
    while (preg_match ('#(%7B|\{)random((%3A|:)([0-9]+)){0,1}(%7D|})#i', $htmlcode, $matches))
    {
        if ($matches[4])
            $randomdigits = $matches[4];
        else
            $randomdigits = 8;
        
        if (isset($lastdigits) && $lastdigits == $randomdigits)
            $randomnumber = $lastrandom;
        else
        {
            $randomnumber = '';
            
            for ($r=0; $r<$randomdigits; $r=$r+9)
                $randomnumber .= (string)mt_rand (111111111, 999999999);
            
            $randomnumber  = substr($randomnumber, 0 - $randomdigits);
        }
        
        $htmlcode = str_replace ($matches[0], $randomnumber, $htmlcode);
        
        $lastdigits = $randomdigits;
        $lastrandom = $randomnumber;
    }
    
    
    // Parse PHP code
    if ($phpAds_config['type_html_php'])
    {
        if (preg_match ("#(\<\?php(.*)\?\>)#i", $htmlcode, $parser_regs))
        {
            // Extract PHP script
            $parser_php     = $parser_regs[2];
            $parser_result     = '';
            
            // Replace output function
            $parser_php = preg_replace ("#echo([^;]*);#i", '$parser_result .=\\1;', $parser_php);
            $parser_php = preg_replace ("#print([^;]*);#i", '$parser_result .=\\1;', $parser_php);
            $parser_php = preg_replace ("#printf([^;]*);#i", '$parser_result .= sprintf\\1;', $parser_php);
            
            // Split the PHP script into lines
            $parser_lines = explode (";", $parser_php);
            for ($parser_i = 0; $parser_i < sizeof($parser_lines); $parser_i++)
            {
                if (trim ($parser_lines[$parser_i]) != '')
                    eval (trim ($parser_lines[$parser_i]).';');
            }
            
            // Replace the script with the result
            $htmlcode = str_replace ($parser_regs[1], $parser_result, $htmlcode);
        }
    }
    
    return ($htmlcode);
}

/*********************************************************/
/* Build the HTML needed to display a banner             */
/*********************************************************/

function phpAds_buildBannerCode ($bannerid, $fullpreview = false)
{
    include_once '../libraries/lib-view-main.inc.php';
    include_once '../libraries/db.php';
    $aBanner = MAX_getBannerByBannerId($bannerid);
    $bannerCode = MAX_buildBannerHtml($aBanner, 0, '', '', '', true, false, false);
    
    return $bannerCode;
}

/*********************************************************/
/* Build Click-Thru ratio                                */
/*********************************************************/

function phpAds_buildCTR ($views, $clicks)
{
    return phpAds_formatPercentage(phpAds_buildRatio($clicks, $views));
}

function phpAds_buildRatio($numerator, $denominator)
{
    // Strip any non-digit characters from numbers
    $numerator = preg_replace('/[^0-9]/', '', $numerator);
    $denominator = preg_replace('/[^0-9]/', '', $denominator);
    return ($denominator == 0 ? 0 : $numerator/$denominator);
}

function phpAds_formatPercentage($number, $decimals=-1)
{
    global $phpAds_config, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator;
    if ($decimals < 0)
        $decimals = $phpAds_config['percentage_decimals'];
        
    return number_format($number*100, $decimals, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator).'%';
}

function phpAds_buildRatioPercentage($numerator, $denominator)
{
    return phpAds_formatPercentage(phpAds_buildRatio($numerator, $denominator));
}

function phpAds_formatNumber ($number)
{
    global $phpAds_ThousandsSeperator;
    
    if (!strcmp($number, '-'))
        return '-';
    
    return (number_format($number, 0, '', $phpAds_ThousandsSeperator));
}



/*********************************************************/
/* Delete statistics                                     */
/*********************************************************/

function phpAds_deleteStatsByBannerID($bannerid)
{
    global $phpAds_config;
    
    $today = getdate();
    
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adclicks'].     " WHERE bannerid=".$bannerid."        AND t_stamp > date_sub(now(), interval 1 hour)") or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adstats'].      " WHERE bannerid=".$bannerid."        AND day = '".$today['year'].'-'.$today['month'].'-'.$today['mday']."' AND hour >= ".$today[hours]) or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adviews'].      " WHERE bannerid=".$bannerid."        AND t_stamp > date_sub(now(), interval 1 hour)") or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_conversionlog']." WHERE action_bannerid=".$bannerid." AND t_stamp > date_sub(now(), interval 1 hour)") or phpAds_sqlDie();
}


function phpAds_deleteStatsByTrackerID($trackerid)
{
    global $phpAds_config;
    
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_adconversions']." WHERE trackerid=".$trackerid." AND t_stamp > date_sub(now(), interval 1 hour)") or phpAds_sqlDie();
    phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_conversionlog']." WHERE trackerid=".$trackerid." AND t_stamp > date_sub(now(), interval 1 hour)") or phpAds_sqlDie();
}


/*********************************************************/
/* Get overview statistics                                 */
/*********************************************************/

function phpAds_totalStats($column, $bannerid, $timeconstraint="")
{
    global $phpAds_config;
    
    $where = "";
    
    if (!empty($bannerid)) 
        $where = "WHERE bannerid = '$bannerid'";
    
    if (!empty($timeconstraint))
    {
        if (!empty($bannerid))
            $where .= " AND ";
        else
            $where = "WHERE ";
        
        if ($timeconstraint == "month")
        {
            $begin = date('Ymd', mktime(0, 0, 0, date('m'), 1, date('Y')));
            $end   = date('Ymd', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
            $where .= "day >= $begin AND day < $end";
        }
        elseif ($timeconstraint == "week")
        {
            $begin = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
            $end   = date('Ymd', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));
            $where .= "day >= $begin AND day < $end";
        }
        else
        {
            $begin = date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
            
            $where .= "day = $begin";
        }
    }
    
    $res = phpAds_dbQuery("SELECT SUM($column) as qnt FROM ".$phpAds_config['tbl_adstats']." $where") or phpAds_sqlDie();
    
    if (phpAds_dbNumRows ($res))
    { 
        $row = phpAds_dbFetchArray($res);
        return ($row['qnt']);
    }
    else
        return (0);
}

function phpAds_totalClicks($bannerid="", $timeconstraint="")
{
    global $phpAds_config;
    
    return phpAds_totalStats("clicks", $bannerid, $timeconstraint);
}

function phpAds_totalConversions($bannerid="", $timeconstraint="")
{
    global $phpAds_config;
    
    return phpAds_totalStats("conversions", $bannerid, $timeconstraint);
}

function phpAds_totalViews($bannerid="", $timeconstraint="")
{
    global $phpAds_config;
    
    return phpAds_totalStats("views", $bannerid, $timeconstraint);
}

function phpAds_htmlQuotes ($string)
{
    $string = stripslashes ($string);
    $string = str_replace ('"', '&quot;', $string);
    $string = str_replace ("'", '&#039;', $string);
    
    return $string;
}


/*********************************************************/
/* Calculates timestamp taking DST into account          */
/*********************************************************/

function phpAds_makeTimestamp($start, $offset = 0)
{
    if (!$offset)
        return $start;
    
    return $start + $offset + (date('I', $start) - date('I', $start + $offset)) * 60;
}

function phpAds_getClientDetails($clientid)
{
    global $phpAds_config;
    global $clientCache;
    
    if (isset($clientCache[$clientid]) && is_array($clientCache[$clientid]))
    {
        $row = $clientCache[$clientid];
    }
    else
    {
        $res = phpAds_dbQuery(
            "SELECT *".
            " FROM ".$phpAds_config['tbl_clients'].
            " WHERE clientid=".$clientid
        ) or phpAds_sqlDie();
        
        $row = phpAds_dbFetchArray($res);
        
        $clientCache[$clientid] = $row;
    }
    
    return ($row);
}

function phpAds_getCampaignDetails($campaignid)
{
    global $phpAds_config;
    global $campaignCache;
    
    if (isset($campaignCache[$campaignid]) && is_array($campaignCache[$campaignid]))
    {
        $row = $campaignCache[$campaignid];
    }
    else
    {
        $res = phpAds_dbQuery(
            "SELECT *".
            " FROM ".$phpAds_config['tbl_campaigns'].
            " WHERE campaignid=".$campaignid
        ) or phpAds_sqlDie();
        
        $row = phpAds_dbFetchArray($res);
        
        $campaignCache[$campaignid] = $row;
    }
    
    return ($row);
}

function phpAds_getTrackerDetails($trackerid)
{
    global $phpAds_config;
    global $trackerCache;
    
    if (isset($trackerCache[$trackerid]) && is_array($trackerCache[$trackerid]))
    {
        $row = $trackerCache[$trackerid];
    }
    else
    {
        $res = phpAds_dbQuery(
            "SELECT *".
            " FROM ".$phpAds_config['tbl_trackers'].
            " WHERE trackerid=".$trackerid
        ) or phpAds_sqlDie();
        
        $row = phpAds_dbFetchArray($res);
        
        $trackerCache[$trackerid] = $row;
    }
    
    return ($row);
}


?>
