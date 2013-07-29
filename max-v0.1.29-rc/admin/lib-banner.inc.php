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
$Id: lib-banner.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/

function phpAds_getBannerCache($banner)
{
    global $phpAds_config;
    
    $buffer = $banner['htmltemplate'];

    
    // Strip slashes from urls
    $banner['url']      = stripslashes($banner['url']);
    $banner['imageurl'] = stripslashes($banner['imageurl']);
    
    
    // The following properties depend on data from the invocation process
    // and can't yet be determined: {zoneid}, {bannerid}
    // These properties will be set during invocation
    
    // Auto change HTML banner
    if ($banner['storagetype'] == 'html')
    {
        if ($banner['autohtml'] == 't' && $phpAds_config['type_html_auto'])
        {
            if ($buffer != '')
            {
                // Remove target parameters
                $buffer = preg_replace("/ target\s*=\s*'[^']*'/i", ' ', $buffer);  // target='_blank'
                $buffer = preg_replace("/ target\s*=\s*'[^\"]*\"/i", ' ', $buffer);  // target="_blank"
                //$buffer = preg_replace(" target=(some word)", '', $buffer); // target=_blank
                
                // Put our click URL and our target parameter in all anchors..
                $buffer = preg_replace("/<a href='/i", "<a target='{target}' href='{clickurl}", $buffer);
                $buffer = preg_replace("/<a href=\"/i", "<a target=\"{target}\" href=\"{clickurl}", $buffer);

                // Put our click URL and our target parameter in all forms.
                $buffer = preg_replace("/<form*action='*'*>/i","<form target='{target}' $1action='{url_prefix}/adclick.php'$3><input type='hidden' name='{clickurlparams}$2'>", $buffer);
                $buffer = preg_replace("/<form*action=\"*\"*>/i","<form target=\"{target}\" $1action=\"{url_prefix}/adclick.php\"$3><input type=\"hidden\" name=\"{clickurlparams}$2\">", $buffer);
            }

                
                switch ($banner['adserver']) {
                    case 'doubleclick' :
                    /*
<IFRAME SRC="http://ad.uk.doubleclick.net/adi/N2121.migration.caratintuk/B1371495;sz=468x60;ord=[timestamp]?" WIDTH=470 HEIGHT=62 MARGINWIDTH=0 MARGINHEIGHT=0 HSPACE=0 VSPACE=0 FRAMEBORDER=0 SCROLLING=no BORDERCOLOR='#000000'>
<SCRIPT language='JavaScript1.1' SRC="http://ad.uk.doubleclick.net/adj/N2121.migration.caratintuk/B1371495;abr=!ie;sz=468x60;ord=[timestamp]?">
</SCRIPT>
<NOSCRIPT>
<A HREF="http://ad.uk.doubleclick.net/jump/N2121.migration.caratintuk/B1371495;abr=!ie4;abr=!ie5;sz=468x60;ord=[timestamp]?">
<IMG SRC="http://ad.uk.doubleclick.net/ad/N2121.migration.caratintuk/B1371495;abr=!ie4;abr=!ie5;sz=468x60;ord=[timestamp]?" BORDER=0 WIDTH=468 HEIGHT=60 ALT="Click Here">Click Here</A>
</NOSCRIPT>
</IFRAME>
                   */
                        $search  = array("/\[timestamp\]/i", "/(http:.*?;)(.*?)/i");
                        $replace = array("{random}",      "$1click0={clickurl};$2");
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                    
                    case 'atlas' :
                        $search  = array("/\[timestamp\]/i","/(http:.*?direct\/01)?click=(.*?)/i");
                        $replace = array("{random}",     "$1click={clickurl}$2");
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                    
                    case 'max' :
                        $search  = array("cb=Insert_Random_Number_Here", "ct0=Insert_Clicktrack_URL_Here");
                        $replace = array("cb={random}", "ct0={clickurl}");
                        $buffer = str_replace ($search, $replace, $buffer);
                        break;
                    
                    case 'tangozebra' :
                        $search = "/tz_redirector_(\d+)\s*=\s*\"\"/";
                        $replace = "tz_redirector_$1=\"{clickurl}\"";
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                        
                    case 'eyeblaster' :
/*
// Before click tracking
<script>
<!--
var gfEbInIframe = false;
var gEbAd = new Object();
gEbAd.nFlightID = 24566;
//-->
</script>
<script src="http://ds.serving-sys.com/BurstingScript/ebServing_21906.js"></script>

// After click tracking
<script>
<!--
var gfEbInIframe = false;
var gEbAd = new Object();
gEbAd.nFlightID = 24566;
//Interactions
gEbAd.interactions = new Object();
gEbAd.interactions["_eyeblaster"] = "ebN=http://awarez.net;";
//-->
</script>
<script src="http://ds.serving-sys.com/BurstingScript/ebServing_21906.js"></script>
*/

                        /* Strategy:  Search for the following:
                           gEbAd.nFlightId = nnnnnn;
                           //-->
                           And replace with the following:
                           gEbAd.nFlightId = nnnnnn;
                           //Interactions
                           gEbAd.interactions = new Object();
                           gEbAd.interactions["_eyeblaster"] = "ebN=(clicktrackurl)";
                       */ 
                        $search = "/gEbAd.nFlightId\s*=\s*(\d+);\s*(\n|\r\n|\r)\s*\/\/-->/";
                        $replace = "gEbAd.nFlightId = $1;\n\/\/Interactions\ngEbAd.interactions = new Object();\ngEbAd.interactions[\"_eyeblaster\"] = \"ebN={clickurl)\"";
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                        
                    case 'bluestreak' :
                        $search = array("n=Insert_Time_Stamp_Here","cltk=Insert_Click_Track_URL_Here");
                        $replace = array("n={random}","cltk={clickurl}");
                        $buffer = str_replace ($search, $replace, $buffer);
                        break;
                        
                    case 'mediaplex' :
                        $del = $phpAds_config['click_tracking_delimiter'];
                        $delnum = strlen($del);
                        $search = array('/mpt=(ADD_RANDOM_NUMBER_HERE|\[CACHEBUSTER\])/', "/mpvc=(.*(\'|\"))/i");
						$replace = array('mpt={random}', "mpvc={clickurl}\\2");
                        
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                        
                        // {url_prefix}/adclick.php?maxparams=2__bannerid={bannerid}__zoneid={zoneid}__cb={random}__maxdest=[url_plain]
                        
                    case 'falk':
                        $del = $phpAds_config['click_tracking_delimiter'];
                        $delnum = strlen($del);
                        $search = array('#&cntadd=1#', '#&cnturl=[^&]*#', '#rdm=(\\\\\'\+rdm\+\\\\\'|\[timestamp\])#');
                        $replace = array('', '', 'rdm={random}&cntadd=1&cnturl={url_prefix}/adclick.php?maxparams=2__bannerid={bannerid}__zoneid={zoneid}__cb={random}__maxdest=[url_plain]');
                        
                        $buffer = preg_replace ($search, $replace, $buffer);
                        break;
                }
                
/*
<script language="JavaScript1.1" src="http://adfarm.mediaplex.com/ad/js/3990-21328-3822-1?mpt={random:10}&mpvc={url_prefix}/adclick.php?bannerid={bannerid}&zoneid={zoneid}&source={source}&dest=">
</script>
<noscript>
  <a href="http://adfarm.mediaplex.com/ad/ck/3990-21328-3822-1?mpt={random:10}&
    <img src="http://adfarm.mediaplex.com/ad/bn/3990-21328-3822-1?mpt={random:10}&
alt="Click Here" border="0">
</a>
</noscript>
*/
                
            // End link processing
                
        }
    }
    
    return ($buffer);
}


function phpAds_rebuildBannerCache ($bannerid)
{
    global $phpAds_config;
    
    // Retrieve current values
    $res = phpAds_dbQuery ("
        SELECT
            *
        FROM
            ".$phpAds_config['tbl_banners']."
        WHERE
            bannerid = '".$bannerid."'
    ") or phpAds_sqlDie();
    
    $current = phpAds_dbFetchArray($res);
    
    
    // Add slashes to status to prevent javascript errors
    // NOTE: not needed in banner-edit because of magic_quotes_gpc
    $current['status'] = addslashes($current['status']);
    
    
    // Rebuild cache
    $current['htmltemplate'] = stripslashes($current['htmltemplate']);
    $current['htmlcache']    = addslashes(phpAds_getBannerCache($current));
    
    phpAds_dbQuery("
        UPDATE
            ".$phpAds_config['tbl_banners']."
        SET
            htmlcache = '".$current['htmlcache']."'
        WHERE
            bannerid = '".$current['bannerid']."'
    ") or phpAds_sqlDie();
}



function phpAds_compileLimitation ($bannerid = '')
{
    global $phpAds_config;
    
    if ($bannerid == '')
    {
        // Loop through all banners
        $res = phpAds_dbQuery("
            SELECT
                bannerid
            FROM
                ".$phpAds_config['tbl_banners']."
        ");
        
        while ($current = phpAds_dbFetchArray($res))
            phpAds_compileLimitation ($current['bannerid']);
    }
    else
    {
        // Compile limitation
        $res = phpAds_dbQuery("
            SELECT
                *
            FROM
                ".$phpAds_config['tbl_acls']."
            WHERE
                bannerid = '".$bannerid."'
            ORDER BY
                executionorder
        ") or phpAds_sqlDie();
        
        while ($row = phpAds_dbFetchArray ($res))
        {
            $acl[$row['executionorder']]['logical']     = $row['logical'];
            $acl[$row['executionorder']]['type']         = $row['type'];
            $acl[$row['executionorder']]['comparison']     = $row['comparison'];
            $acl[$row['executionorder']]['data']         = addslashes($row['data']);
        }
        
        
        $expression = '';
        $i = 0;
        
        if (isset($acl) && count($acl))
        {
            reset($acl);
            while (list ($key,) = each ($acl))
            {
                if ($i > 0)
                    $expression .= ' '.$acl[$key]['logical'].' ';
                
                switch ($acl[$key]['type'])
                {
                    case 'clientip':
                        $expression .= "phpAds_aclCheckClientIP(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'browser':
                        $expression .= "phpAds_aclCheckUseragent(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'os':
                        $expression .= "phpAds_aclCheckUseragent(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'useragent':
                        $expression .= "phpAds_aclCheckUseragent(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'language':
                        $expression .= "phpAds_aclCheckLanguage(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'country':
                        $expression .= "phpAds_aclCheckCountry(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'continent':
                        $expression .= "phpAds_aclCheckContinent(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'region':
                        $expression .= "phpAds_aclCheckRegion(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'weekday':
                        $expression .= "phpAds_aclCheckWeekday(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'domain':
                        $expression .= "phpAds_aclCheckDomain(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'source':
                        $expression .= "phpAds_aclCheckSource(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\', $"."source)";
                        break;
                    case 'time':
                        $expression .= "phpAds_aclCheckTime(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'date':
                        $expression .= "phpAds_aclCheckDate(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    case 'referer':
                        $expression .= "phpAds_aclCheckReferer(\'".addslashes($acl[$key]['data'])."\', \'".$acl[$key]['comparison']."\')";
                        break;
                    default:
                        return(0);
                }
                
                $i++;
            }
        }
        
        if ($expression == '')
            $expression = 'true';
        
        $res = phpAds_dbQuery("
            UPDATE
                ".$phpAds_config['tbl_banners']."
            SET
                compiledlimitation='".$expression."'
            WHERE
                bannerid='".$bannerid."'
        ") or phpAds_sqlDie();
    }
}

?>
