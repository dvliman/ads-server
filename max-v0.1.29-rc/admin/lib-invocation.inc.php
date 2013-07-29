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
$Id: lib-invocation.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/

include_once '../libraries/db.php';

// Register input variables
phpAds_registerGlobal (
     'block'
    ,'bannerid'
    ,'blockcampaign'
    ,'campaignid'
    ,'clientid'
    ,'codetype'
    ,'delay'
    ,'delay_type'
    ,'generate'
    ,'height'
    ,'hostlanguage'
    ,'ilayer'
    ,'layerstyle'
    ,'left'
    ,'location'
    ,'menubar'
    ,'popunder'
    ,'raw'
    ,'refresh'
    ,'resizable'
    ,'resize'
    ,'scrollbars'
    ,'source'
    ,'status'
    ,'submitbutton'
    ,'target'
    ,'template'
    ,'thirdpartytrack'
    ,'timeout'
    ,'toolbars'
    ,'top'
    ,'transparent'
    ,'uniqueid'
    ,'website'
    ,'what'
    ,'width'
    ,'withtext'
);

// Load translations
if (file_exists("../language/".strtolower($phpAds_config['language'])."/invocation.lang.php"))
    require ("../language/".strtolower($phpAds_config['language'])."/invocation.lang.php");
else
    require ("../language/english/invocation.lang.php");

/*********************************************************/
/* Generate bannercode                                   */
/*********************************************************/

function phpAds_GenerateInvocationCode()
{
    global
         $phpAds_config
        ,$affiliateid
        ,$bannerid
        ,$block
        ,$blockcampaign
        ,$campaignid
        ,$clientid
        ,$codetype
        ,$delay
        ,$delay_type
        ,$domains_table
        ,$extra
        ,$height
        ,$hostlanguage
        ,$ilayer
        ,$left
        ,$location
        ,$menubar
        ,$popunder
        ,$raw
        ,$refresh
        ,$resizable
        ,$resize
        ,$scrollbars
        ,$source
        ,$status
        ,$target
        ,$template
        ,$thirdpartytrack
        ,$timeout
        ,$toolbars
        ,$top
        ,$transparent
        ,$uniqueid
        ,$website
        ,$what
        ,$width
        ,$withtext
        ,$zoneid
    ;
    
    // Check if affiliate is on the same server
    if (isset($website) && $website != '')
    {
        $server_phpads   = parse_url($phpAds_config['url_prefix']);
        $server_affilate = parse_url($website);
        $server_same      = (@gethostbyname($server_phpads['host']) == 
                            @gethostbyname($server_affilate['host']));
    }
    else
        $server_same = true;
    
    
    // Always make sure we create non-SSL bannercodes
    $phpAds_config['url_prefix'] = str_replace ('https://', 'http://', $phpAds_config['url_prefix']);
    
    
    // Clear buffer
    $buffer = '';
    
    $parameters = array();
    $uniqueid = 'a'.substr(md5(uniqid('', 1)), 0, 7);
    if (!isset($withtext)) $withtext = 0;
    
    
    // Set parameters
    if (isset($clientid) && strlen($clientid) && $clientid != '0')
        $parameters['clientid'] = "clientid=".$clientid;
    
    if (isset($zoneid) && $zoneid != '')
        $parameters['zoneid'] = "zoneid=".urlencode($zoneid);

    if (isset($campaignid) && strlen($campaignid) && $campaignid != '0')
        $parameters['campaignid'] = "campaignid=".$campaignid;
    
    if (isset($bannerid) && $bannerid != '')
        $parameters['bannerid'] = "bannerid=".urlencode($bannerid);
    
    if (isset($what) && $what != '')
        $parameters['what'] = "what=".str_replace (",+", ",_", $what);
    
    if (isset($source) && $source != '')
        $parameters['source'] = "source=".urlencode($source);


    // Remote invocation
    
    // Javascript invocation script for a publisher - all zones
    if ($codetype=='publisherJs') {
        $res = phpAds_dbQuery("
            SELECT
                mnemonic
            FROM
                {$phpAds_config['tbl_affiliates']}
            WHERE
                affiliateid=$affiliateid
        ") or phpAds_sqlDie();
        
        $mnemonic = '';
        if ($row = phpAds_dbFetchArray($res)) {
            $mnemonic = $row['mnemonic'];
        }
        
        $res = phpAds_dbQuery("
            SELECT
                affiliateid,
                zoneid,
                zonename,
                width,
                height,
                delivery
            FROM
                {$phpAds_config['tbl_zones']}
            WHERE
                affiliateid=$affiliateid
            ") or phpAds_sqlDie();


        while ($row = phpAds_dbFetchArray($res)) {
        
            $urlPrefix   = preg_replace('#^http(s)?:#','',$phpAds_config['url_prefix']);
            $aZoneId[]   = $row['zoneid'];
            $aZoneName[] = $row['zonename'];
            $aZoneType[] = $row['delivery'];
            $aN[]        = $mnemonic . substr(md5(uniqid('', 1)), 0, 7);
            $aWidth[]    = $row['width'];
            $aHeight[]   = $row['height'];
        }

        if(!isset($aZoneId) || !is_array($aZoneId))
            return 'No Zones Available!';

        $script = "
<html>
<head>
<!-- NOTE:  You can remove the (extensive) comments when moving this code to a live server to reduce the size of your pages -->

<!--
Max Media Manager Channel Script
- Include this script directly ABOVE the Max Media Manager Header Script (defined below), in the <head> tag.

The script below should define a variable, az_channel. This variable should contain the name of the 'virtual directory' of the site.
For example, if you are on the football summary page of the sports section of a news site, the following should be included:
  var az_channel = '$mnemonic/sports/football';
Conversely, if you are on the home page of the site, the variable should be:
  var az_channel = '$mnemonic';
-->
<script language='JavaScript' type='text/javascript'>
<!--
var az_channel = '$mnemonic';
//-->
</script>


<!--
Max Media Manager Header Script
- Include this script below the Max Media Manager Channel Scipt (but still in the <head> tag) of every page on your site.

NOTE:  This script does not change for any page on your site, so it may be more efficient to include it as a .js file
  rather than putting the entire text on every page.  For example, if you cut and paste the code below and store it in a file called
  'mmm.js', the code below should call the script:
     
     <script language='JavaScript' type='text/javascript' src='mmm.js'></script>
-->

<script language='JavaScript' type='text/javascript'>
<!--
var az_p=location.protocol=='https:'?'https:':'http:';
var az_r=Math.floor(Math.random()*99999999);
if (!document.phpAds_used) document.phpAds_used = ',';
function az_adjs(z,n)
{
  if (z>-1) {
    var az=\"<\"+\"script language='JavaScript' type='text/javascript' src='\"+az_p+\"$urlPrefix/adjs.php?n=\"+n+\"&zoneid=\"+z;
    az+=\"&source=\"+az_channel+\"&exclude=\"+document.phpAds_used+\"&r=\"+az_r;
    if (window.location) az+=\"&loc=\"+escape(window.location);
    if (document.referrer) az+=\"&referer=\"+escape(document.referrer);
    az+=\"'><\"+\"/script>\";
    document.write(az);
  }
}
function az_adpop(z,n)
{
  if (z>-1) {
    var az=\"<\"+\"script language='JavaScript' type='text/javascript' src='\"+az_p+\"$urlPrefix/adpopup.php?n=\"+n+\"&zoneid=\"+z;
    az+=\"&source=\"+az_channel+\"&exclude=\"+document.phpAds_used+\"&r=\"+az_r;
    if (window.location) az+=\"&loc=\"+escape(window.location);
    if (document.referrer) az+=\"&referer=\"+escape(document.referrer);
    az+=\"'><\"+\"/script>\";
    document.write(az);
  }
}
//-->
</script>
</head>

<body>
<!--
Max Media Manager Ad Tag Scripts
- The following is the script for each ad tag.  There are a couple of items to watch out for:
 1.  Each tag has a different zone number (var 1), and a different key value (var 2).  If the key value is the same for any two zone tags,
     the clickthrough URL may not work correctly.
 2.  Each tag has a <noscript> section.  If this tag is on an SSL page, change the '{$phpAds_config['url_prefix']}' to '{$phpAds_config['ssl_url_prefix']}'.
     Note that the <noscript> section cannot dynamically choose between SSL and non-SSL.
 3.  The <noscript> section will only show image banners.  There is no width or height in these banners, so if you want these tags to allocate
     space for the ad before it shows, you need to add this information to the <img> tag.
 4.  If you do not want to deal with the intricities of the <noscript> section, delete the tag (from <noscript>... to </noscript>).
     On average, the <noscript> tag is called from less than 1% of internet users.
-->


";
        
            foreach($aZoneName as $key=>$zoneName) {
                $name = str_replace('\'','',$zoneName) . " - " . str_replace('\'','',$aWidth[$key]) . "x". str_replace('\'','',$aHeight[$key]);
                if ($aZoneType[$key] != phpAds_ZonePopup) {
                    $script .= "
                   
<br><br>$name<br>
<script language='JavaScript' type='text/javascript'>
<!--
az_adjs({$aZoneId[$key]},'{$aN[$key]}');
//-->
</script>";
                    if ($aZoneType[$key] != phpAds_ZoneText) {
                        $script .= "<noscript><a target='_blank' href='{$phpAds_config['url_prefix']}/adclick.php?n={$aN[$key]}'><img border='0' alt='' src='{$phpAds_config['url_prefix']}/adview.php?zoneid={$aZoneId[$key]}&n={$aN[$key]}'></a></noscript>";
                    }

                }
                else {
                    // This is a popup zone, so generate popup.php invocation not javascript
                    $script .= "
                   
<br><br>$name<br>
<script language='JavaScript' type='text/javascript'>
<!--
az_adpop({$aZoneId[$key]},'{$aN[$key]}');
//-->
</script>
";
                    
                }
            }
        
            $script .= "

</body>
</html>";
            
        return $script;      
    }
    
    
    // Remote invocation - regular 
    if ($codetype=='adview')
    {
        if (isset($uniqueid) && $uniqueid != '') $parameters[] = "n=".$uniqueid;    
        
        if (isset($thirdpartytrack) && $thirdpartytrack == '1') {
           $parameters['cb'] = "cb=Insert_Random_Number_Here";
           $parameters['ct0'] = "ct0=Insert_Clicktrack_URL_Here";
        }
        
        $buffer .= "<a href='".$phpAds_config['url_prefix']."/adclick.php";
        $buffer .= "?n=".$uniqueid;
        $buffer .= "'";
        if (isset($target) && $target != '')
            $buffer .= " target='".$target."'";
        else
            $buffer .= " target='_blank'";
        $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
        if (sizeof($parameters) > 0)
            $buffer .= "?".implode ("&", $parameters);
        $buffer .= "' border='0' alt=''></a>\n";
    }
    
    // Remote invocation - no cookies
    else if ($codetype=='adviewnocookies')
    {
        if (isset($uniqueid) && $uniqueid != '')
            $parameters[] = "n=".$uniqueid;    
        
        $buffer .= "<a href='".$phpAds_config['url_prefix']."/adclick.php";
        //$buffer .= "?n=".$uniqueid;    
        $buffer .= "?bannerid=" . $bannerid;
        if ((isset($zoneid)) && ($zoneid != '')) $buffer .= "&zoneid=".$zoneid;
        
        $buffer .= "'";
        if (isset($target) && $target != '')
            $buffer .= " target='".$target."'";
        else
            $buffer .= " target='_blank'";
        $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
        
        //$parameters['what'] = "what=bannerid:" . $bannerid;
        
        if (sizeof($parameters) > 0)
            $buffer .= "?" . implode ("&", $parameters);
        $buffer .= "' border='0' alt=''></a>\n";
    }

    // Set parameters
    if (isset($target) && $target != '')
        $parameters['target'] = "target=".urlencode($target);
    
    
    // Remote invocation with JavaScript
    if ($codetype=='adjs')
    {
        if (isset($withtext) && $withtext != '0')
            $parameters['withtext'] = "withtext=1";
        
        if (isset($block) && $block == '1')
            $parameters['block'] = "block=1";
        
        if (isset($blockcampaign) && $blockcampaign == '1')
            $parameters['blockcampaign'] = "blockcampaign=1";
        
        if (isset($campaignid) && ($campaignid != '')) {
            $parameters['campaignid'] = "campaignid=".$campaignid;
            //unset($parameters['campaignid']);
        } 
               
        $buffer .= "<script language='JavaScript' type='text/javascript'>\n";
        $buffer .= "<!--\n";
        
        // support for 3rd party server clicktracking
        if (isset($thirdpartytrack) && $thirdpartytrack == '1')
            $buffer .= "// Insert click tracking URL here\n    document.phpAds_ct0 ='Insert_Clicktrack_URL_Here'\n\n";

        // cache buster
        $buffer .= "   var awrz_rnd = Math.floor(Math.random()*99999999999);\n";
        $buffer .= "   var awrz_protocol = location.protocol.indexOf('https')>-1?'https:':'http:';\n";
        $buffer .= "   if (!document.phpAds_used) document.phpAds_used = ',';\n";
        $buffer .= "   document.write (\"<\" + \"script language='JavaScript' type='text/javascript' src='\");\n";
        $buffer .= "   document.write (awrz_protocol+\"".str_replace('http:','',$phpAds_config['url_prefix'])."/adjs.php?n=".$uniqueid."\");\n";
        if (sizeof($parameters) > 0)
            $buffer .= "   document.write (\"&".implode ("&", $parameters)."\");\n";
        $buffer .= "   document.write (\"&exclude=\" + document.phpAds_used);\n";
        $buffer .= "   document.write (\"&loc=\" + escape(window.location));\n";
        $buffer .= "   if (document.referrer)\n";
        $buffer .= "      document.write (\"&referer=\" + escape(document.referrer));\n";
        $buffer .= "   document.write ('&r=' + awrz_rnd);\n";
        $buffer .= "   document.write (\"&ct0=\" + escape(document.phpAds_ct0));\n";
        $buffer .= "   document.write (\"'><\" + \"/script>\");\n";
        $buffer .= "//-->\n";
        $buffer .= "</script>";
        
        if (isset($parameters['withtext']))
            unset ($parameters['withtext']);
        
        if (isset($parameters['block']))
            unset ($parameters['block']);
        
        if (isset($parameters['blockcampaign']))
            unset ($parameters['blockcampaign']);
        
        if (isset($parameters['target']))
            unset ($parameters['target']);
        
        if (isset($uniqueid) && $uniqueid != '')
            $parameters['n'] = "n=".$uniqueid;

        if ($extra['delivery'] != phpAds_ZoneText) {
            $buffer .= "<noscript><a href='".$phpAds_config['url_prefix']."/adclick.php";
            $buffer .= "?n=".$uniqueid;
            $buffer .= "'";
            if (isset($target) && $target != '')
                $buffer .= " target='".$target."'";
            else
                $buffer .= " target='_blank'";
            $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
            if (sizeof($parameters) > 0)
                $buffer .= "?".implode ("&", $parameters);
            $buffer .= "' border='0' alt=''></a></noscript>\n";
        }
    }
    
    // Remote invocation for iframes
    if ($codetype=='adframe')
    {
        if (isset($refresh) && $refresh != '')
            $parameters['refresh'] = "refresh=".$refresh;
        
        if (isset($resize) && $resize == '1')
            $parameters['resize'] = "resize=1";
        
        if (isset($thirdpartytrack) && $thirdpartytrack == '1') {
           $parameters['cb'] = "cb=Insert_Random_Number_Here";
           $parameters['ct0'] = "ct0=Insert_Clicktrack_URL_Here";
        }
        $buffer .= "<iframe id='".$uniqueid."' name='".$uniqueid."' src='".$phpAds_config['url_prefix']."/adframe.php";
        $buffer .= "?n=".$uniqueid;
        if (sizeof($parameters) > 0)
            $buffer .= "&".implode ("&", $parameters);
        $buffer .= "' framespacing='0' frameborder='no' scrolling='no'";
        if (isset($width) && $width != '' && $width != '-1')
            $buffer .= " width='".$width."'";
        if (isset($height) && $height != '' && $height != '-1')
            $buffer .= " height='".$height."'";
        if (isset($transparent) && $transparent == '1')
            $buffer .= " allowtransparency='true'";
        $buffer .= ">";
        
        
        if (isset($refresh) && $refresh != '')
            unset ($parameters['refresh']);
        
        if (isset($resize) && $resize == '1')
            unset ($parameters['resize']);
        
        if (isset($uniqueid) && $uniqueid != '')
            $parameters['n'] = "n=".$uniqueid;    
        
        if (isset($parameters['target']))
            unset ($parameters['target']);
        
        
        if (isset($ilayer) && $ilayer == 1 &&
            isset($width) && $width != '' && $width != '-1' &&
            isset($height) && $height != '' && $height != '-1')
        {
            $buffer .= "<script language='JavaScript' type='text/javascript'>\n";
            $buffer .= "<!--\n";
            $buffer .= "   document.write (\"<nolayer>\");\n";
            
            $buffer .= "   document.write (\"<a href='".$phpAds_config['url_prefix']."/adclick.php";
            $buffer .= "?n=".$uniqueid;
            $buffer .= "'";
            if (isset($target) && $target != '')
                $buffer .= " target='".$target."'";
            else
                $buffer .= " target='_blank'";
            $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
            if (sizeof($parameters) > 0)
                $buffer .= "?".implode ("&", $parameters);
            $buffer .= "' border='0' alt=''></a>\");\n";
            
            $buffer .= "   document.write (\"</nolayer>\");\n";
            $buffer .= "   document.write (\"<ilayer id='layer".$uniqueid."' visibility='hidden' width='".$width."' height='".$height."'></ilayer>\");\n";
            $buffer .= "//-->\n";
            $buffer .= "</script>";
            
            $buffer .= "<noscript><a href='".$phpAds_config['url_prefix']."/adclick.php";
            $buffer .= "?n=".$uniqueid;
            $buffer .= "'";
            if (isset($target) && $target != '')
                $buffer .= " target='$target'";
            $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
            if (sizeof($parameters) > 0)
                $buffer .= "?".implode ("&", $parameters);
            $buffer .= "' border='0' alt=''></a></noscript>";
        }
        else
        {
            $buffer .= "<a href='".$phpAds_config['url_prefix']."/adclick.php";
            $buffer .= "?n=".$uniqueid;
        
            // support for 3rd party server clicktracking
            if (isset($thirdpartytrack) && $thirdpartytrack == '1')
                $buffer .= "&ct0=Insert_Clicktrack_URL_Here";
            
            $buffer .= "'";
            if (isset($target) && $target != '')
                $buffer .= " target='".$target."'";
            else
                $buffer .= " target='_blank'";
            $buffer .= "><img src='".$phpAds_config['url_prefix']."/adview.php";
            if (sizeof($parameters) > 0) {
                $buffer .= "?";
                foreach ($parameters as $thisKey => $thisValue) { if ($thisKey != "ct0") $buffer .= "&".$thisValue; }
            }
            $buffer .= "' border='0' alt=''></a>";
        }
        
        $buffer .= "</iframe>\n";
        
        if (isset($parameters['n']))
            unset ($parameters['n']);
        
        if (isset($target) && $target != '')
            $parameters['target'] = "target=".urlencode($target);
        
        if (isset($ilayer) && $ilayer == 1 &&
            isset($width) && $width != '' && $width != '-1' &&
            isset($height) && $height != '' && $height != '-1')
        {
            // Do no rewrite target frames
            $parameters['rewrite'] = 'rewrite=0';
            
            $buffer .= "\n\n";
            $buffer .= "<!-- Place this part of the code just above the </body> tag -->\n";
            
            $buffer .= "<layer src='".$phpAds_config['url_prefix']."/adframe.php";
            $buffer .= "?n=".$uniqueid;
            if (sizeof($parameters) > 0)
                $buffer .= "&".implode ("&", $parameters);
            
            $buffer .= "' width='".$width."' height='".$height."' visibility='hidden' onLoad=\"moveToAbsolute(layer".$uniqueid.".pageX,layer".$uniqueid.".pageY);clip.width=".$width.";clip.height=".$height.";visibility='show';\"></layer>";
        }
    }
    
    // Popup
    if ($codetype=='popup')
    {
        if (isset($popunder) && $popunder == '1')
            $parameters['popunder'] = "popunder=1";
        
        if (isset($left) && $left != '' && $left != '-')
            $parameters['left'] = "left=".$left;
        
        if (isset($top) && $top != '' && $top != '-')
            $parameters['top'] = "top=".$top;
        
        if (isset($timeout) && $timeout != '' && $timeout != '-')
            $parameters['timeout'] = "timeout=".$timeout;
        
        if (isset($toolbars) && $toolbars == '1')
            $parameters['toolbars'] = "toolbars=1";
        
        if (isset($location) && $location == '1')
            $parameters['location'] = "location=1";
        
        if (isset($menubar) && $menubar == '1')
            $parameters['menubar'] = "menubar=1";
        
        if (isset($status) && $status == '1')
            $parameters['status'] = "status=1";
        
        if (isset($resizable) && $resizable == '1')
            $parameters['resizable'] = "resizable=1";
        
        if (isset($scrollbars) && $scrollbars == '1')
            $parameters['scrollbars'] = "scrollbars=1";

        if (isset($thirdpartytrack) && $thirdpartytrack == '1') {
           $parameters['cb'] = "cb=Insert_Random_Number_Here";
           $parameters['ct0'] = "ct0=Insert_Clicktrack_URL_Here";
        }

        if (isset($delay_type))
        {
            if ($delay_type == 'seconds' && isset($delay) && $delay != '' && $delay != '-')
                $parameters['delay'] = "delay=".$delay;
            elseif ($delay_type == 'exit')
                $parameters['delay'] = "delay=exit";
        }
        
        $buffer .= "<script language='JavaScript' type='text/javascript' src='".$phpAds_config['url_prefix']."/adpopup.php";
        $buffer .= "?n=".$uniqueid;
        if (sizeof($parameters) > 0)
            $buffer .= "&".implode ("&", $parameters);
        $buffer .= "'></script>\n";
    }
    
    // Remote invocation for layers
    if ($codetype=='adlayer')
        $buffer = phpAds_generateLayerCode($parameters)."\n";
    
    // Remote invocation using XML-RPC
    if ($codetype=='xmlrpc')
    {
        if (!isset($clientid) || $clientid == '') $clientid = 0;
        if (!isset($campaignid) || $campaignid == '') $campaignid = 0;
        
        $params = parse_url($phpAds_config['url_prefix']);
        
        switch($hostlanguage)
        {
            case 'php':
                if (!isset($what) or ($what == "")) {
                    // Need to generate the what variable here
                    if (isset($zoneid) and ($zoneid != "")) { $what = "zone:" . $zoneid; }
                }
                $buffer = "<"."?php\n";
                $buffer .= "    // Remember to copy files in misc/samples/xmlrpc/php to the same directory as your script\n\n";
                $buffer .= "    require('lib-xmlrpc-class.inc.php');\n";
                $buffer .= "    \$xmlrpcbanner = new phpAds_XmlRpc('$params[host]', '$params[path]'".
                    (isset($params['port']) ? ", '$params[port]'" : "").");\n";
                $buffer .= "    \$xmlrpcbanner->view('$what', $clientid, $campaignid, '$target', '$source', '$withtext');\n";
                $buffer .= "?".">\n";
                break;
        }
    }
    
    if ($codetype=='local')
    {
        $path = phpAds_path;
        $path = str_replace ('\\', '/', $path);
        $root = getenv('DOCUMENT_ROOT');
        $pos  = strpos ($path, $root);
        
        if (!isset($clientid)   || $clientid == '')   $clientid = 0;
        if (!isset($zoneid)     || $zoneid == '')     $zoneid = 0;
        if (!isset($campaignid) || $campaignid == '') $campaignid = 0;
        if (!isset($bannerid)   || $bannerid == '')   $bannerid = 0;
        
        
        if (is_int($pos) && $pos == 0)
            $path = "getenv('DOCUMENT_ROOT').'".substr ($path, $pos + strlen ($root))."/phpadsnew.inc.php'";
        else
            $path = "'".$path."/phpadsnew.inc.php'";
        
        $buffer .= "<"."?php\n";
        $buffer .= "    if (@include($path)) {\n";
        $buffer .= "        if (!isset($"."phpAds_context)) $"."phpAds_context = array();\n";
        
        if (isset($raw) && $raw == '1')
        {
            $buffer .= "        $"."phpAds_raw = view_local ('$what', $zoneid, $campaignid, $bannerid, '$target', '$source', '$withtext', $"."phpAds_context);\n";
            
            if (isset($block) && $block == '1')
                $buffer .= "        $"."phpAds_context[] = array('!=' => 'bannerid:'.$"."phpAds_raw['bannerid']);\n";
            
            if (isset($blockcampaign) && $blockcampaign == '1')
                $buffer .= "        $"."phpAds_context[] = array('!=' => 'campaignid:'.$"."phpAds_raw['campaignid']);\n";
            
            $buffer .= "    }\n    \n";
            $buffer .= "    // Assign the $"."phpAds_raw['html'] variable to your template\n";
            $buffer .= "    // echo $"."phpAds_raw['html'];\n";
        }
        else
        {
            $buffer .= "        $"."phpAds_raw = view_local ('$what', $zoneid, $campaignid, $bannerid, '$target', '$source', '$withtext', $"."phpAds_context);\n";
            
            if (isset($block) && $block == '1')
                $buffer .= "        $"."phpAds_context[] = array('!=' => 'bannerid:'.$"."phpAds_raw['bannerid']);\n";
            
            if (isset($blockcampaign) && $blockcampaign == '1')
                $buffer .= "        $"."phpAds_context[] = array('!=' => 'campaignid:'.$"."phpAds_raw['campaignid']);\n";
            
            $buffer .= "        echo $"."phpAds_raw['html'];\n";
            $buffer .= "    }\n";
        }
        
        $buffer .= "?".">\n";
    }
    
    return $buffer;
}

function phpAds_generateTrackerCode($trackerId)
{
    global $phpAds_config, $trackerid;
    $variablesComment = '';
    $variablesQuerystring = '';
    $variables = MAX_getVariablesByTrackerId($trackerId);
    foreach ($variables as $variable) {
        if ($variable['variabletype'] == 'qs') {
            $variablesComment .= "\n  - Each time the page with this code on it is loaded, replace the\n    text INSERT_{$variable['name']}_VALUE below with the value for the\n    \"{$variable['description']}\" variable value, named \"{$variable['name']}\".";
            $variablesQuerystring .= "&{$variable['name']}=INSERT_{$variable['name']}_VALUE";
        }
    }
    $buffer = "
<!--
Max Media Manager Tracker Image Beacon Code:$variablesComment
  - Replace INSERT_RANDOM with a random number or timestamp that is
    generated each time the page with this code on it is loaded, to
    ensure that the logging of the tracker beacon is not cached.
  - If this beacon is going on an SSL page, replace the
    '{$phpAds_config['url_prefix']}' with '{$phpAds_config['ssl_url_prefix']}'
Put this tag in the <head> portion of the web page you want to track.
Any variables that will be tracked along with the beacon were configured
at the time this beacon code was generated, so if you want to change the
variables tracked, this code will need to be re-generated in Max Media
Manager.
-->
<img src='{$phpAds_config['url_prefix']}/adconversion.php?trackerid={$trackerId}{$variablesQuerystring}&cb=INSERT_RANDOM' width='1' height='1' border='0'>";
    return $buffer;
}

function phpAds_generateJavascriptTrackerCode($trackerId)
{
    global $trackerid, $phpAds_config;
    $urlPrefixNoProtocol = str_replace('http:','',$phpAds_config['url_prefix']);
    $buffer  = "
<!-- Max Media Manager Tracker Javascript Beacon Code:
Put this tag in the <head> portion of the web page you want to track,
below any definitions of Javascript variables you want to track. Any
Javascript variables that will be tracked need to be configured in
Max Media Manager, and are defined at run-time, and so are not seen
mentioned in the code below.
-->
<script language='JavaScript' type='text/javascript'>
<!--
var az_r = Math.floor(Math.random()*999999);
var az_p = location.protocol.indexOf('https:')>-1?'https:':'http:';
document.write (\"<\" + \"script language='JavaScript' type='text/javascript' src='\");
document.write (az_p+\"$urlPrefixNoProtocol/adconversionjs.php?trackerid={$trackerId}&r=\"+az_r);
document.write (\"'><\" + \"/script>\");
//-->
</script>";
    return $buffer;
}


/*********************************************************/
/* Place invocation form                                 */
/*********************************************************/

function phpAds_placeInvocationForm($extra = '', $zone_invocation = false)
{
    global
         $HTTP_SERVER_VARS
        ,$block
        ,$blockcampaign
        ,$campaignid
        ,$clientid
        ,$codetype
        ,$delay
        ,$delay_type
        ,$generate
        ,$height
        ,$hostlanguage
        ,$ilayer
        ,$layerstyle
        ,$left
        ,$location
        ,$menubar
        ,$phpAds_config
        ,$phpAds_TextDirection
        ,$popunder
        ,$raw
        ,$refresh
        ,$resizable
        ,$resize
        ,$scrollbars
        ,$source
        ,$status
        ,$submitbutton
        ,$tabindex
        ,$target
        ,$template
        ,$timeout
        ,$toolbars
        ,$top
        ,$transparent
        ,$uniqueid
        ,$what
        ,$width
        ,$withtext
        ,$bannerid
        ,$thirdpartytrack
    ;
    
    
    
    // Check if affiliate is on the same server
    if ($extra != '' && isset ($extra['website']) && $extra['website'])
    {
        $server_phpads   = parse_url($phpAds_config['url_prefix']);
        $server_affilate = parse_url($extra['website']);
        $server_same      = (@gethostbyname($server_phpads['host']) == 
                            @gethostbyname($server_affilate['host']));
    }
    else
        $server_same = true;
    
    
    
    // Hide when integrated in zone-advanced.php
    if (!is_array($extra) || !isset($extra['zoneadvanced']) || !$extra['zoneadvanced'])
        echo "<form name='generate' action='".$HTTP_SERVER_VARS['PHP_SELF']."' method='POST' onSubmit='return phpAds_formCheck(this);'>\n";
    
    // Invocation type selection
    if (!is_array($extra) || (isset($extra['delivery']) && $extra['delivery'] != phpAds_ZoneInterstitial && $extra['delivery'] != phpAds_ZonePopup))
    {
        $allowed['adlayer']  = $phpAds_config['allow_invocation_interstitial'];
        $allowed['popup']      = $phpAds_config['allow_invocation_popup'];
        $allowed['xmlrpc']      = $phpAds_config['allow_invocation_xmlrpc'];
        $allowed['adframe']  = $phpAds_config['allow_invocation_frame'];
        $allowed['adjs']      = $phpAds_config['allow_invocation_js'];
        $allowed['adview']      = $phpAds_config['allow_invocation_plain'];
        $allowed['adviewnocookies']  = $phpAds_config['allow_invocation_plain_nocookies'];
        $allowed['local']      = $phpAds_config['allow_invocation_local'];
        
        if (is_array($extra)) $allowed['popup'] = false;
        if (is_array($extra)) $allowed['adlayer'] = false;
        //if (is_array($extra) && $server_same == false)  $allowed['local'] = false;
        
        if (is_array($extra) && $server_same == false && 
           ($extra['width'] == '-1' || $extra['height'] == '-1')) $allowed['adframe'] = false;
        
        if (is_array($extra) && $extra['delivery'] == phpAds_ZoneText)
        {
            // Only allow Javascript and Localmode
            // when using text ads
            $allowed['adlayer'] = false;
            $allowed['popup'] = false;
            $allowed['adframe'] = false;
            $allowed['adview'] = false;
            $allowed['adviewnocookies'] = false;
        }
        
        if ($zone_invocation) {
            // only for direct selection
            $allowed['adviewnocookies'] = false;
        }
        
        if (!isset($codetype) || $allowed[$codetype] == false)
        {
            while (list($k,$v) = each($allowed))
                if ($v) $codetype = $k;
        }
        
        if (!isset($codetype))
            $codetype = '';
        
        
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        echo "<tr><td height='25' colspan='3'><b>".$GLOBALS['strChooseInvocationType']."</b></td></tr>";
        echo "<tr><td height='35'>";
        echo "<select name='codetype' onChange=\"this.form.submit()\" accesskey=".$GLOBALS['keyList']." tabindex='".($tabindex++)."'>";
        
        if ($allowed['adview'])  echo "<option value='adview'".($codetype == 'adview' ? ' selected' : '').">".$GLOBALS['strInvocationRemote']."</option>";
        if ($allowed['adviewnocookies'])  echo "<option value='adviewnocookies'".($codetype == 'adviewnocookies' ? ' selected' : '').">".$GLOBALS['strInvocationRemoteNoCookies']."</option>";
        if ($allowed['adjs'])    echo "<option value='adjs'".($codetype == 'adjs' ? ' selected' : '').">".$GLOBALS['strInvocationJS']."</option>";
        if ($allowed['adframe']) echo "<option value='adframe'".($codetype == 'adframe' ? ' selected' : '').">".$GLOBALS['strInvocationIframes']."</option>";
        if ($allowed['xmlrpc'])  echo "<option value='xmlrpc'".($codetype == 'xmlrpc' ? ' selected' : '').">".$GLOBALS['strInvocationXmlRpc']."</option>";
        if ($allowed['popup'])      echo "<option value='popup'".($codetype == 'popup' ? ' selected' : '').">".$GLOBALS['strInvocationPopUp']."</option>";
        if ($allowed['adlayer']) echo "<option value='adlayer'".($codetype == 'adlayer' ? ' selected' : '').">".$GLOBALS['strInvocationAdLayer']."</option>";
        if ($allowed['local'])      echo "<option value='local'".($codetype == 'local' ? ' selected' : '').">".$GLOBALS['strInvocationLocal']."</option>";
        
        echo "</select>";
        echo "&nbsp;<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif' border='0'>";
        echo "</td></tr></table>";
        
        phpAds_ShowBreak();
        echo "<br>";
    }
    else
    {
        if ($extra['delivery'] == phpAds_ZoneInterstitial)
            $codetype = 'adlayer';
        
        if ($extra['delivery'] == phpAds_ZonePopup)
            $codetype = 'popup';
        
        if (!isset($codetype)) 
            $codetype = '';
    }
    
    
        
    if ($codetype == 'adlayer')
    {
        if (!isset($layerstyle)) $layerstyle = 'geocities';
        include ('../libraries/layerstyles/'.$layerstyle.'/invocation.inc.php');
    }
    
    //

    if ($codetype != '')
    {
        // Code
        if (isset($submitbutton) || isset($generate) && $generate)
        {
            echo "<table border='0' width='550' cellpadding='0' cellspacing='0'>";
            echo "<tr><td height='25'><img src='images/icon-generatecode.gif' align='absmiddle'>&nbsp;<b>".$GLOBALS['strBannercode']."</b></td>";
            
            // Show clipboard button only on IE
            if (strpos ($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE') > 0 &&
                strpos ($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'Opera') < 1)
            {
                echo "<td height='25' align='right'><img src='images/icon-clipboard.gif' align='absmiddle'>&nbsp;";
                echo "<a href='javascript:phpAds_CopyClipboard(\"bannercode\");'>".$GLOBALS['strCopyToClipboard']."</a></td></tr>";
            }
            else
                echo "<td>&nbsp;</td>";
            
            echo "<tr height='1'><td colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            echo "<tr><td colspan='2'><textarea name='bannercode' class='code-gray' rows='6' cols='55' style='width:550;' readonly>".htmlspecialchars(phpAds_GenerateInvocationCode())."</textarea></td></tr>";
            echo "</table><br>";
            phpAds_ShowBreak();
            echo "<br>";
            
            $generated = true;
        }
        else
            $generated = false;
        
        
        // Hide when integrated in zone-advanced.php
        if (!(is_array($extra) && isset($extra['zoneadvanced']) && $extra['zoneadvanced']))
        {
        
            // Header

            //Parameters Section
            echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
            echo "<tr><td height='25' colspan='3'><img src='images/icon-overview.gif' align='absmiddle'>&nbsp;<b>".$GLOBALS['strParameters']."</b></td></tr>";
            echo "<tr height='1'><td width='30'><img src='images/break.gif' height='1' width='30'></td>";
            echo "<td width='200'><img src='images/break.gif' height='1' width='200'></td>";
            echo "<td width='100%'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            echo "<tr".($zone_invocation || $codetype == 'adviewnocookies' ? '' : " bgcolor='#F6F6F6'")."><td height='10' colspan='3'>&nbsp;</td></tr>";
            //echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";            
        }
        
        
        
        if ($codetype == 'adview')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true);

        if ($codetype == 'adviewnocookies') {
            if (!$zone_invocation) {
                $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'bannerid' => true);
            }
            else {
                $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'bannerzone' => true);
            }
        }
        
        if ($codetype == 'adjs')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'block' => true, 'target' => true, 'source' => true, 'withtext' => true, 'blockcampaign' => true);
        
        if ($codetype == 'adframe')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'refresh' => true, 'size' => true, 'resize' => true, 'transparent' => true, 'ilayer' => true);
        
        if ($codetype == 'ad')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'withtext' => true, 'size' => true, 'resize' => true, 'transparent' => true);
        
        if ($codetype == 'popup')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'absolute' => true, 'popunder' => true, 'timeout' => true, 'delay' => true, 'windowoptions' => true);
        
        if ($codetype == 'adlayer')
            $show = phpAds_getLayerShowVar();
        
        if ($codetype == 'xmlrpc')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'withtext' => true, 'template' => true, 'hostlanguage' => true);
        
        if ($codetype == 'local')
            $show = array ('what' => true, 'clientid' => true, 'campaignid' => true, 'target' => true, 'source' => true, 'withtext' => true, 'block' => true, 'blockcampaign' => true, 'raw' => true);
        
        
        
        // What
        if (!$zone_invocation && isset($show['what']) && $show['what'] == true  && $codetype != 'adviewnocookies')
        {
            echo "<tr bgcolor='#F6F6F6'><td width='30'>&nbsp;</td>";
            echo "<td width='200' valign='top'>".$GLOBALS['strInvocationWhat']."</td><td width='370'>";
            echo "<textarea class='flat' name='what' rows='3' cols='50' style='width:350px;' tabindex='".($tabindex++)."'>".(isset($what) ? stripslashes($what) : '')."</textarea></td></tr>";
            echo "<tr bgcolor='#F6F6F6'><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
            echo "<td bgcolor='#F6F6F6' colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        }
        
        /* Remove advetiser from direct invocation - not needed
        // ClientID
        if (!$zone_invocation && isset($show['clientid']) && $show['clientid'] == true)
        {
            // Display available advertisers...
            echo "<tr bgcolor='#F6F6F6'><td width='30'>&nbsp;</td>\n";
            echo "<td width='200'>".$GLOBALS['strInvocationClientID']."</td><td width='370'>\n";
            echo "<select name='clientid' style='width:350px;' tabindex='".($tabindex++)."'>\n";
                echo "<option value='0'>-</option>\n";
            
            $res = phpAds_dbQuery(
                "SELECT clientid, clientname".
                " FROM ".$phpAds_config['tbl_clients']
            ) or phpAds_sqlDie();
                
            while ($row = phpAds_dbFetchArray($res))
            {
                echo "<option value='".$row['clientid']."'".($clientid == $row['clientid'] ? ' selected' : '').">";
                echo phpAds_buildName ($row['clientid'], $row['clientname']);
                echo "</option>\n";
            }
            
            echo "</select>\n";
            echo "</td></tr>";
//            echo "<tr bgcolor='#F6F6F6'><td height='10' colspan='3'>&nbsp;</td></tr>";
//            echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
//            echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
            echo "<tr bgcolor='#F6F6F6'><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
            echo "<td bgcolor='#F6F6F6' colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        }
        */
        
        
        // CampaignID
        if (!$zone_invocation && isset($show['campaignid']) && $show['campaignid'] == true && $codetype != 'adviewnocookies')
        {
            // Display available campaigns...
            echo "<tr bgcolor='#F6F6F6'><td width='30'>&nbsp;</td>\n";
            echo "<td width='200'>".$GLOBALS['strInvocationCampaignID']."</td><td width='370'>\n";
            echo "<select name='campaignid' style='width:350px;' tabindex='".($tabindex++)."'>\n";
                echo "<option value='0'>-</option>\n";

            if (phpAds_isUser(phpAds_Admin))
            {
                $query = "SELECT campaignid,campaignname".
                    " FROM ".$phpAds_config['tbl_campaigns'];
            }
            elseif (phpAds_isUser(phpAds_Agency))
            {
                $query = "SELECT m.campaignid AS campaignid".
                    ",m.campaignname AS campaignname".
                    " FROM ".$phpAds_config['tbl_campaigns']." AS m".
                    ",".$phpAds_config['tbl_clients']." AS c".
                    " WHERE m.clientid=c.clientid".
                    " AND c.agencyid=".phpAds_getAgencyID();
            }
            $res = phpAds_dbQuery($query)
                or phpAds_sqlDie();

            while ($row = phpAds_dbFetchArray($res))
            {
                echo "<option value='".$row['campaignid']."'".($campaignid == $row['campaignid'] ? ' selected' : '').">";
                echo phpAds_buildName ($row['campaignid'], $row['campaignname']);
                echo "</option>\n";
            }
            
            echo "</select>\n";
            echo "</td></tr>";
            echo "<tr bgcolor='#F6F6F6'><td height='10' colspan='3'>&nbsp;</td></tr>";
            echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
        }

        // BannerID
        if (isset($show['bannerid']) && $show['bannerid'] == true)
        {
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationBannerID']."</td><td width='370'>";
            if ($codetype == 'adviewnocookies') {
                echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='bannerid' value='".(isset($bannerid) ? $bannerid : '')."' style='width: 175px;' tabindex='".($tabindex++)."'></td></tr>";            
            } else {
                echo "<input class='flat' type='text' name='bannerid' size='' value='".(isset($bannerid) ? $bannerid : '')."' style='width:175px;' tabindex='".($tabindex++)."'></td></tr>";
            }
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        }

        /*
        // Banners from a zone
        if (isset($show['bannerzone']) && $show['bannerzone']) {
            // Display available banners...
            if (!$zoneid) $zoneid=0;

            echo "<tr bgcolor='#F6F6F6'><td width='30'>&nbsp;</td>\n";
            echo "<td width='200'>".$GLOBALS['strInvocationCampaignID']."</td><td width='370'>\n";
            echo "<select name='campaignid' style='width:350px;' tabindex='".($tabindex++)."'>\n";
                echo "<option value='0'>-</option>\n";

            if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
            {
                $query = "SELECT b.description, b.bannerid" .
                    " FROM ".$phpAds_config['tbl_banners'] . "AS b".
                    " , ".$phpAds_config['tbl_zones'] . "AS z" .
                    //" WHERE z.zoneid=".$zoneid . " = b." . 
                    " AND active='t'";
                    
            }
            $res = phpAds_dbQuery($query)
                or phpAds_sqlDie();

            while ($row = phpAds_dbFetchArray($res))
            {
                echo "<option value='".$row['bannerid']."'".($bannerid == $row['bannerid'] ? ' selected' : '').">";
                echo phpAds_buildName ($row['bannerid'], $row['description']);
                echo "</option>\n";
            }
            
            echo "</select>\n";
            echo "</td></tr>";
            echo "<tr bgcolor='#F6F6F6'><td height='10' colspan='3'>&nbsp;</td></tr>";
            echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
        }
        */
        
        // Target
        if (isset($show['target']) && $show['target'] == true)
        {
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationTarget']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='target' size='' value='".(isset($target) ? $target : '')."' style='width:175px;' tabindex='".($tabindex++)."'></td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        // Source
        if (isset($show['source']) && $show['source'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationSource']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='source' size='' value='".(isset($source) ? $source : '')."' style='width:175px;' tabindex='".($tabindex++)."'></td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // WithText
        if (isset($show['withtext']) && $show['withtext'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationWithText']."</td>";
            echo "<td width='370'><input type='radio' name='withtext' value='1'".(isset($withtext) && $withtext != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='withtext' value='0'".(!isset($withtext) || $withtext == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }

        // Support for 3rd Party Clicktracking
        echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$GLOBALS['str3rdPartyTrack']."</td>";
        echo "<td width='370'><input type='radio' name='thirdpartytrack' value='1'".(!isset($thirdpartytrack) || $thirdpartytrack != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
        echo "<input type='radio' name='thirdpartytrack' value='0'".(isset($thirdpartytrack) && $thirdpartytrack == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
        echo "</tr>";
        echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        
        // refresh
        if (isset($show['refresh']) && $show['refresh'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strIFrameRefreshAfter']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='refresh' size='' value='".(isset($refresh) ? $refresh : '')."' style='width:175px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrSeconds']."</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // size
        if (!$zone_invocation && isset($show['size']) && $show['size'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strFrameSize']."</td><td width='370'>";
                echo $GLOBALS['strWidth'].": <input class='flat' type='text' name='width' size='3' value='".(isset($width) ? $width : '')."' tabindex='".($tabindex++)."'>&nbsp;&nbsp;&nbsp;";
                echo $GLOBALS['strHeight'].": <input class='flat' type='text' name='height' size='3' value='".(isset($height) ? $height : '')."' tabindex='".($tabindex++)."'>";
            echo "</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // Resize
        if (isset($show['resize']) && $show['resize'] == true)
        {
            // Only show this if affiliate is on the same server
            if ($server_same)
            {
                echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
                echo "<tr><td width='30'>&nbsp;</td>";
                echo "<td width='200'>".$GLOBALS['strIframeResizeToBanner']."</td>";
                echo "<td width='370'><input type='radio' name='resize' value='1'".(isset($resize) && $resize == 1 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
                echo "<input type='radio' name='resize' value='0'".(!isset($resize) || $resize == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
                echo "</tr>";
                echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
            }
            else
                echo "<input type='hidden' name='resize' value='0'>";
            
        }
        
        
        // Transparent
        if (isset($show['transparent']) && $show['transparent'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strIframeMakeTransparent']."</td>";
            echo "<td width='370'><input type='radio' name='transparent' value='1'".(isset($transparent) && $transparent == 1 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='transparent' value='0'".(!isset($transparent) || $transparent == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // Netscape 4 ilayer
        if (isset($show['ilayer']) && $show['ilayer'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strIframeIncludeNetscape4']."</td>";
            echo "<td width='370'><input type='radio' name='ilayer' value='1'".(isset($ilayer) && $ilayer == 1 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='ilayer' value='0'".(!isset($ilayer) || $ilayer == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // Block
        if (isset($show['block']) && $show['block'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationDontShowAgain']."</td>";
            echo "<td width='370'><input type='radio' name='block' value='1'".(isset($block) && $block != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='block' value='0'".(!isset($block) || $block == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // Blockcampaign
        if (isset($show['blockcampaign']) && $show['blockcampaign'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationDontShowAgainCampaign']."</td>";
            echo "<td width='370'><input type='radio' name='blockcampaign' value='1'".(isset($blockcampaign) && $blockcampaign != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='blockcampaign' value='0'".(!isset($blockcampaign) || $blockcampaign == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // Raw
        if (isset($show['raw']) && $show['raw'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strInvocationTemplate']."</td>";
            echo "<td width='370'><input type='radio' name='raw' value='1'".(isset($raw) && $raw != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "<input type='radio' name='raw' value='0'".(!isset($raw) || $raw == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // AdLayer style
        if (isset($show['layerstyle']) && $show['layerstyle'] == true)
        {
            $layerstyles = array();
            
            $stylesdir = opendir('../libraries/layerstyles');
            while ($stylefile = readdir($stylesdir))
            {
                if (is_dir('../libraries/layerstyles/'.$stylefile) &&
                    file_exists('../libraries/layerstyles/'.$stylefile.'/invocation.inc.php'))
                {
                    if (ereg('^[^.]', $stylefile))
                        $layerstyles[$stylefile] = isset($GLOBALS['strAdLayerStyleName'][$stylefile]) ?
                            $GLOBALS['strAdLayerStyleName'][$stylefile] :
                            str_replace("- ", "-", 
                                ucwords(str_replace("-", "- ", $stylefile)));
                }
            }
            closedir($stylesdir);
            
            asort($layerstyles, SORT_STRING);
            
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strAdLayerStyle']."</td><td width='370'>";
            echo "<select name='layerstyle' onChange='this.form.submit()' style='width:175px;' tabindex='".($tabindex++)."'>";
            
            while (list($k, $v) = each($layerstyles))
                echo "<option value='$k'".($layerstyle == $k ? ' selected' : '').">$v</option>";
            
            echo "</select>";
            echo "</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // popunder
        if (isset($show['popunder']) && $show['popunder'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strPopUpStyle']."</td>";
            echo "<td width='370'><input type='radio' name='popunder' value='0'".
                 (!isset($popunder) || $popunder != '1' ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".
                 "<img src='images/icon-popup-over.gif' align='absmiddle'>&nbsp;".$GLOBALS['strPopUpStylePopUp']."<br>";
            echo "<input type='radio' name='popunder' value='1'".
                 (isset($popunder) && $popunder == '1' ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".
                 "<img src='images/icon-popup-under.gif' align='absmiddle'>&nbsp;".$GLOBALS['strPopUpStylePopUnder']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // delay
        if (isset($show['delay']) && $show['delay'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strPopUpCreateInstance']."</td>";
            echo "<td width='370'><input type='radio' name='delay_type' value='none'".
                 (!isset($delay_type) || ($delay_type != 'exit' && $delay_type != 'seconds') ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strPopUpImmediately']."<br>";
            echo "<input type='radio' name='delay_type' value='exit'".
                 (isset($delay_type) && $delay_type == 'exit' ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strPopUpOnClose']."<br>";
            echo "<input type='radio' name='delay_type' value='seconds'".
                 (isset($delay_type) && $delay_type == 'seconds' ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strPopUpAfterSec']."&nbsp;".
                 "<input class='flat' type='text' name='delay' size='' value='".(isset($delay) ? $delay : '-')."' style='width:50px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrSeconds']."</td>";
            echo "</tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // absolute
        if (isset($show['absolute']) && $show['absolute'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strPopUpTop']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='top' size='' value='".(isset($top) ? $top : '-')."' style='width:50px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strPopUpLeft']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='left' size='' value='".(isset($left) ? $left : '-')."' style='width:50px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // timeout
        if (isset($show['timeout']) && $show['timeout'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strAutoCloseAfter']."</td><td width='370'>";
                echo "<input class='flat' type='text' name='timeout' size='' value='".(isset($timeout) ? $timeout : '-')."' style='width:50px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrSeconds']."</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        // Window options
        if (isset($show['windowoptions']) && $show['windowoptions'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td><td width='200' valign='top'>".$GLOBALS['strWindowOptions']."</td><td width='370'>";
            
            echo "<table cellpadding='0' cellspacing='0' border='0'>";
            
            echo "<tr><td>".$GLOBALS['strShowToolbars']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='toolbars' value='1'".(isset($toolbars) && $toolbars != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='toolbars' value='0'".(!isset($toolbars) || $toolbars == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr><tr><td colspan='5'><img src='images/break-l.gif' height='1' width='200' vspace='2'></td></tr>";
            
            echo "<tr><td>".$GLOBALS['strShowLocation']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='location' value='1'".(isset($location) && $location != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='location' value='0'".(!isset($location) || $location == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr><tr><td colspan='5'><img src='images/break-l.gif' height='1' width='200' vspace='2'></td></tr>";
            
            echo "<tr><td>".$GLOBALS['strShowMenubar']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='menubar' value='1'".(isset($menubar) && $menubar != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='menubar' value='0'".(!isset($menubar) || $menubar == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr><tr><td colspan='5'><img src='images/break-l.gif' height='1' width='200' vspace='2'></td></tr>";
            
            echo "<tr><td>".$GLOBALS['strShowStatus']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='status' value='1'".(isset($status) && $status != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='status' value='0'".(!isset($status) || $status == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr><tr><td colspan='5'><img src='images/break-l.gif' height='1' width='200' vspace='2'></td></tr>";
            
            echo "<tr><td>".$GLOBALS['strWindowResizable']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='resizable' value='1'".(isset($resizable) && $resizable != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='resizable' value='0'".(!isset($resizable) || $resizable == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr><tr><td colspan='5'><img src='images/break-l.gif' height='1' width='200' vspace='2'></td></tr>";
            
            echo "<tr><td>".$GLOBALS['strShowScrollbars']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='scrollbars' value='1'".(isset($scrollbars) && $scrollbars != 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strYes']."<br>";
            echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
            echo "<input type='radio' name='scrollbars' value='0'".(!isset($scrollbars) || $scrollbars == 0 ? ' checked' : '')." tabindex='".($tabindex++)."'>&nbsp;".$GLOBALS['strNo']."";
            echo "</td></tr>";

            
            echo "</table>";
            echo "</td></tr><tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        
        // AdLayer custom code
        if (isset($show['layercustom']) && $show['layercustom'] == true)
            phpAds_placeLayerSettings();
        
        
        // Host Language
        if (isset($show['hostlanguage']) && $show['hostlanguage'] == true)
        {
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$GLOBALS['strXmlRpcLanguage']."</td><td width='370'>";
            echo "<select name='hostlanguage' tabindex='".($tabindex++)."'>";
            echo "<option value='php'".($hostlanguage == 'php' ? ' selected' : '').">PHP</option>";
    //        echo "<option value='php-xmlrpc'".($hostlanguage == 'php-xmlrpc' ? ' selected' : '').">PHP with built in XML-RPC extension</option>";
    //        echo "<option value='asp'".($hostlanguage == 'asp' ? ' selected' : '').">ASP</option>";
    //        echo "<option value='jsp'".($hostlanguage == 'jsp' ? ' selected' : '').">JSP</option>";
            echo "</select>";
            echo "</td></tr>";
            echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
        }
        
        // Hide when integrated in zone-advanced.php
        if (!(is_array($extra) && isset($extra['zoneadvanced']) && $extra['zoneadvanced']))
        {
            // Footer
            echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
            echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            
            echo "</table>";
            echo "<br><br>";
            echo "<input type='hidden' value='".($generated ? 1 : 0)."' name='generate'>";
            
            if ($generated)
                echo "<input type='submit' value='".$GLOBALS['strRefresh']."' name='submitbutton' tabindex='".($tabindex++)."'>";
            else
                echo "<input type='submit' value='".$GLOBALS['strGenerate']."' name='submitbutton' tabindex='".($tabindex++)."'>";
        }
        
        
    }
    
    // Put extra hidden fields
    if (is_array($extra))
        while (list($k, $v) = each($extra))
            echo "<input type='hidden' value='$v' name='$k'>";
    
    // Hide when integrated in zone-advanced.php
    if (!is_array($extra) || !isset($extra['zoneadvanced']) || !$extra['zoneadvanced'])
        echo "</form><br><br>";
        
        // javascript form requirements
    if ($codetype == 'adviewnocookies') {
        echo "<script language='JavaScript'>\n";
        echo "<!--\n";
        echo "\tphpAds_formSetRequirements('bannerid', '".addslashes($GLOBALS['strInvocationBannerID'])."', true)\n";
        echo "-->\n";
        echo "</script>\n";
    }
}

?>
