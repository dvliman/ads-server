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
$Id: lib-view-main.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Seed the random number generator
//mt_srand((double)microtime() * 1000000);
mt_srand(floor((isset($n) && strlen($n) > 5 ? hexdec($n[0].$n[2].$n[3].$n[4].$n[5]): 1000000) * (double)microtime()));


/*********************************************************/
/* Create the HTML needed to display the banner          */
/*********************************************************/

function view_raw($what, $target = '', $source = '', $withtext = 0, $context = 0, $richmedia = true, $ct0 ='')
{
    global $phpAds_config, $HTTP_SERVER_VARS;
    global $phpAds_followedChain;

    $userid = phpAds_getUniqueUserID();
    phpAds_setCookie("phpAds_id", $userid, time()+365*24*60*60);

    $outputbuffer = '';

    // Set flag
    $found = false;

    // Reset followed zone chain
    $phpAds_followedChain = array();

    $first = true;
    
    global $g_append, $g_prepend;
    $g_append = "";
    $g_prepend = "";
    
    while (($first || $what != '') && $found == false) {
        $first = false;
        if (substr($what,0,5) == 'zone:') {
            if (!defined('LIBVIEWZONE_INCLUDED')) {
                require (phpAds_path.'/libraries/lib-view-zone.inc.php');
            }

            $row = phpAds_fetchBannerZone($what, $context, $source, $richmedia);
        } else {
            if (!defined('LIBVIEWQUERY_INCLUDED')) {
                require (phpAds_path.'/libraries/lib-view-query.inc.php');
            }
            if (!defined('LIBVIEWDIRECT_INCLUDED')) {
                require (phpAds_path.'/libraries/lib-view-direct.inc.php');
            }
            $row = phpAds_fetchBannerDirect($what, $context, $source, $richmedia);
        }

        if (is_array ($row)) {
          $found = true;
        } else {
          $what  = $row;
        }
    }


    if ($found) {
        $zoneId = empty($row['zoneid']) ? 0 : $row['zoneid'];
        $outputbuffer = MAX_buildBannerHtml($row, $zoneId, $source, $target, $ct0, $withtext, true, true, $richmedia);
        // Prepare impression logging
        if ($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon']) {
            phpAds_logImpression ($userid, $row['bannerid'], $row['zoneid'], $source);
        }

        // Return banner
        return ( array( 'html'       => $outputbuffer,
                        'bannerid'   => $row['bannerid'],
                        'alt'        => $row['alt'],
                        'width'      => $row['width'],
                        'height'     => $row['height'],
                        'url'        => $row['url'],
                        'campaignid' => $row['campaignid'])
                      );

    } else {
        // An error occured, or there are no banners to display at all
        
        // Use the default banner if defined
        if ($phpAds_config['default_banner_target'] != '' && $phpAds_config['default_banner_url'] != '') {

            // Determine target
            if ($target == '') {
                $target = '_blank';  // default
            }

            // Show default banner
            $outputbuffer = $g_prepend . '<a href=\''.$phpAds_config['default_banner_target'].'\' target=\''.$target.'\'><img src=\''.$phpAds_config['default_banner_url'].'\' border=\'0\' alt=\'\'></a>' . $g_append;

            // Return banner
            return ( array( 'html' => $outputbuffer,
                           'bannerid' => '')
                          );
        }
        else {
            $outputbuffer = $g_prepend . $g_append;
            return ( array( 'html' => $outputbuffer, 'bannerid' => '' ) );
        }
    }
}

/*********************************************************/
/* Display a banner                                      */
/*********************************************************/

function view($what, $target = '', $source = '', $withtext = 0, $context = 0, $richmedia = true)
{

    $output = view_raw($what, "$target", "$source", $withtext, $context, $richmedia);
    print($output['html']);
    return ($output['bannerid']);
}



function MAX_buildBannerHtml($aBanner, $zoneId=0, $source='', $target='', $ct0='', $withText=false, $logClick=true, $logView=true, $richMedia=true)
{
    $code = '';

    switch ($aBanner['contenttype']) {
        case 'gif'  :
        case 'jpeg' :
        case 'png'  :
            $code = _buildBannerCodeImage($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, false, $richMedia);
            break;
        case 'swf'  :
            $code = _buildBannerCodeFlash($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView);
            break;
        case 'txt'  :
            $code = _buildBannerCodeText($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView);
            break;
        case 'mov'  :
            $code = _buildBannerCodeQuicktime($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView);
            break;
        default :
            switch ($aBanner['storagetype']) {
                case 'html' :
                    $code = _buildBannerCodeHtml($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView);
                    break;
                case 'url' : // External banner without a recognised content type - assume image...
                    $code = _buildBannerCodeImage($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, false, $richMedia);
                    break;
                case 'txt' :
                    $code = _buildBannerCodeText($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView);
            }
            break;
    }

    // Transform any code
    // Get the target
    if (empty($target))
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
    // Get a timestamp
    list($usec, $sec) = explode(' ', microtime());
    $time = (float)$usec + (float)$sec;
    // Get a random number
    $random = substr(md5(uniqid($time, true)), 0, 10);
    
    // Get the click URL
    $clickUrl = _buildClickUrl($aBanner, $zoneId, $source, urlencode($ct0), $logClick, true);
    $urlPrefix = _getDeliveryUrlPrefix() . '/adclick.php';
    $code = str_replace('{clickurl}', $clickUrl, $code);  // This step needs to be done separately because {clickurl} can contain {random}...
    $search = array('{timestamp}','{random}','{target}','{url_prefix}','{bannerid}','{zoneid}','{source}');
    $replace = array($time, $random, $target, $urlPrefix, $aBanner['bannerid'], $zoneId, $source);
    
    if ( ($_SERVER['SERVER_PORT'] == 443) && ($aBanner['autohtml'] == 't') && ($aBanner['adserver'] != '') ) {
        $search[] = 'http:';
        $replace[] = 'https:';
    }
    $code = str_replace($search, $replace, $code);

    // Make the clickurl available to adview        
    $GLOBALS['adview_clickurl'] = str_replace('{random}', $random, $clickUrl);
    
    return $code;
}

function _buildBannerCodeImage($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $richMedia=true, $useAppend=true)
{
    global $phpAds_config;

    if (!$richMedia)
        return _buildFileUrl($aBanner, $useAlt, '{random}');

    $prepend = (!empty($aBanner['prepend']) && $useAppend) ? $aBanner['prepend'] : '';
    $append = (!empty($aBanner['append']) && $useAppend) ? $aBanner['append'] : '';

    // Create the anchor tag..
    $clickUrl = _buildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
    if (!empty($clickUrl)) {  // There is a link
        $status = !empty($aBanner['status']) ? " onMouseOver=\"self.status='{$aBanner['status']}'; return true;\" onMouseOut=\"self.status=''; return true;\"" : '';
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $clickTag = "<a href='$clickUrl' target='$target'$status>";
        $clickTagEnd = '</a>';
    } else {
        $clickTag = '';
        $clickTagEnd = '';
    }

    // Create the image tag..
    $imageUrl = _buildFileUrl($aBanner, $useAlt);
    if (!empty($imageUrl)) {
        $imgStatus = empty($clickTag) ? $status : '';
        $width = !empty($aBanner['width']) ? $aBanner['width'] : 0;
        $height = !empty($aBanner['height']) ? $aBanner['height'] : 0;
        $alt = !empty($aBanner['alt']) ? $aBanner['alt'] : '';
        $imageTag = "$clickTag<img src='$imageUrl' width='$width' height='$height' alt='$alt' title='$alt' border='0'$imgStatus>$clickTagEnd";
    } else {
        $imageTag = '';
    }
    // Get the text below the banner
    $bannerText = $withText && !empty($aBanner['bannertext']) ? "<br>$clickTag{$aBanner['bannertext']}$clickTagEnd" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return $prepend . $imageTag . $bannerText . $beaconTag . $append;

}
function _buildBannerCodeFlash($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true)
{
    global $phpAds_config;

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';
    $width = !empty($aBanner['width']) ? $aBanner['width'] : 0;
    $height = !empty($aBanner['height']) ? $aBanner['height'] : 0;
    $pluginVersion = !empty($aBanner['pluginversion']) ? $aBanner['pluginversion'] : '4';
    $imageUrlPrefix = ($_SERVER['SERVER_PORT'] == 443) ? $phpAds_config['type_web_ssl_url'] : $phpAds_config['type_web_url'];
    $fileName = !empty($aBanner['filename']) ? $aBanner['filename'] : '';
    $altImageBannercode = !empty($aBanner['alt_filename']) ? _buildBannerCodeImage($aBanner, $zoneId, $source, $ct0, false, $logClick, false, true, false) : '';

    // Create the anchor tag..
    $clickUrl = _buildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
    if (!empty($clickUrl)) {  // There is a link
        $status = !empty($aBanner['status']) ? " onMouseOver=\"self.status='{$aBanner['status']}'; return true;\" onMouseOut=\"self.status=''; return true;\"" : '';
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $swfParams .= 'clickTARGET='.$target.'&clickTAG=' . $clickUrl;
        $clickTag = "<a href='$clickUrl' target='$target'$status>";
        $clickTagEnd = '</a>';
    } else {
        $swfParams = '';
        $clickTag = '';
        $clickTagEnd = '';
    }
    $fileUrl = _buildFileUrl($aBanner, false, $swfParams);
    $protocol = ($_SERVER['SERVER_PORT'] == 443) ? "https" : "http";

	$code = "
<SCRIPT LANGUAGE='JavaScript' type='text/javascript'>
<!--
var plugin = (navigator.mimeTypes && navigator.mimeTypes['application/x-shockwave-flash']) ? navigator.mimeTypes['application/x-shockwave-flash'].enabledPlugin : 0;
if ( plugin ) {
	plugin = parseInt(plugin.description.substring(plugin.description.indexOf('.')-1)) >= $pluginVersion;
}
else if (navigator.userAgent && navigator.userAgent.indexOf('MSIE')>=0 && (navigator.userAgent.indexOf('Windows 95')>=0 || navigator.userAgent.indexOf('Windows 98')>=0 || navigator.userAgent.indexOf('Windows NT')>=0)) {
	document.write('<SCRIPT LANGUAGE=\"VBScript\"> \\n');
	document.write('on error resume next \\n');
	document.write('plugin = ( IsObject(CreateObject(\"ShockwaveFlash.ShockwaveFlash.$pluginVersion\")))\\n');
	document.write('</SCRIPT\> \\n');
}
if ( plugin ) {
	document.write(\"<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='$protocol://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$pluginVersion,0,0,0' width='$width' height='$height'>\");
	document.write(\"<param name='movie' value='$fileUrl'>\");
	document.write(\"<param name='quality' value='high'>\");

    document.write(\"<embed src='$fileUrl' quality='high' width='$width' height='$height' type='application/x-shockwave-flash' pluginspage='$protocol://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></embed>\");
    document.write(\"</object>\");
} else if (!(navigator.appName && navigator.appName.indexOf(\"Netscape\")>=0 && navigator.appVersion.indexOf(\"2.\")>=0)){
	document.write(\"" .str_replace('"', '\"', $altImageBannercode). "\");
}
//-->
</SCRIPT>
<noembed>$altImageBannercode</noembed>
<noscript>$altImageBannercode</noscript>
<!--End of code-->";

    $bannerText = $withText && !empty($aBanner['bannertext']) ? "<br>{$clickTag}{$aBanner['bannertext']}{$clickTagEnd}" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return "{$prepend}{$code}{$bannerText}{$beaconTag}{$append}";
}
function _buildBannerCodeQuicktime($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true)
{
    global $phpAds_config;

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';
    $width = !empty($aBanner['width']) ? $aBanner['width'] : 0;
    $height = !empty($aBanner['height']) ? $aBanner['height'] : 0;
    $pluginVersion = !empty($aBanner['pluginversion']) ? $aBanner['pluginversion'] : '4';
    $imageUrlPrefix = ($HTTP_SERVER_VARS['SERVER_PORT'] == 443) ? $phpAds_config['type_web_ssl_url'] : $phpAds_config['type_web_url'];
    $fileName = !empty($aBanner['filename']) ? $aBanner['filename'] : '';
    $altImageBannercode = _buildBannerCodeImage($aBanner, $zoneId, $source, $ct0, false, $logClick, false, true);

    // Create the anchor tag..
    $clickTag = _buildClickUrl($aBanner, $source, $ct0, $logClick);
    if (!empty($clickTag)) {  // There is a link
        $status = !empty($aBanner['status']) ? " onMouseOver=\"self.status='{$aBanner['status']}'; return true;\" onMouseOut=\"self.status=''; return true;\"" : '';
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $swfParams = 'clickTAG=' . $clickTag;
        $anchor = "<a href='$clickTag' target='$target'$status>";
        $anchorEnd = '</a>';
    } else {
        $swfParams = '';
        $anchor = '';
        $anchorEnd = '';
    }
    $clickTag = _buildClickUrl($aBanner, $source, $ct0, $logClick);
    $fileUrl = _buildFileUrl($aBanner, false, $swfParams);

    $code = "
<object classid='clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B' codebase='http://www.apple.com/qtactivex/qtplugin.cab' width='$width' height='$height'>
<param name='src' value='$fileUrl'>
<param name='controller' value='false'>
<param name='autoplay' value='true'>
<embed src='$fileUrl' controller='false' autoplay='true' width='$width' height='$height' pluginspace='http://www.apple.com/quicktime/download/'></embed>
<noembed>$altImageBannercode</noembed>
</object>";


    $bannerText = $withText && !empty($aBanner['bannertext']) ? "<br>{$anchor}{$aBanner['bannertext']}{$anchorEnd}" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return $prepend . $code . $bannerText . $beaconTag . $append;

}
function _buildBannerCodeReal($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true)
{
    global $phpAds_config;

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';
    $width = !empty($aBanner['width']) ? $aBanner['width'] : 0;
    $height = !empty($aBanner['height']) ? $aBanner['height'] : 0;
    $pluginVersion = !empty($aBanner['pluginversion']) ? $aBanner['pluginversion'] : '4';
    $imageUrlPrefix = ($HTTP_SERVER_VARS['SERVER_PORT'] == 443) ? $phpAds_config['type_web_ssl_url'] : $phpAds_config['type_web_url'];
    $fileName = !empty($aBanner['filename']) ? $aBanner['filename'] : '';
    $altImageBannercode = _buildBannerCodeImage($aBanner, $zoneId, $source, $ct0, false, $logClick, false, true);

    // Create the anchor tag..
    $clickTag = _buildClickUrl($aBanner, $source, $ct0, $logClick);
    if (!empty($clickTag)) {  // There is a link
        $status = !empty($aBanner['status']) ? " onMouseOver=\"self.status='{$aBanner['status']}'; return true;\" onMouseOut=\"self.status=''; return true;\"" : '';
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $swfParams = 'clickTAG=' . $clickTag;
        $anchor = "<a href='$clickTag' target='$target'$status>";
        $anchorEnd = '</a>';
    } else {
        $swfParams = '';
        $anchor = '';
        $anchorEnd = '';
    }
    $clickTag = _buildClickUrl($aBanner, $source, $ct0, $logClick);
    $fileUrl = _buildFileUrl($aBanner, false, $swfParams);

    $code = "
<object classid='clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA' width='$width' height='$height'>
<param name='src' value='$fileUrl'>
<param name='controls' value='ImageWindow'>
<param name='autostart' value='true'>
<embed src='$fileUrl' controls='ImageWindow' autostart='true' width='$width' height='$height' type='audio/x-pn-realaudio-plugin'></embed>
<noembed>$altImageBannercode</noembed>
</object>";


    $bannerText = $withText && !empty($aBanner['bannertext']) ? "<br>{$anchor}{$aBanner['bannertext']}{$anchorEnd}" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return $prepend . $code . $bannerText . $beaconTag . $append;

}

function _buildBannerCodeHtml($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false)
{
    global $phpAds_config;

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';

    $code = !empty($aBanner['htmlcache']) ? $aBanner['htmlcache'] : '';

    // Parse PHP code
    if ($phpAds_config['type_html_php'])
    {
        if (preg_match ("#(\<\?php(.*)\?\>)#iU", $code, $parser_regs))
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
            $code = str_replace ($parser_regs[1], $parser_result, $code);
        }
    }  
      
    // Get the text below the banner
    $bannerText = !empty($aBanner['bannertext']) ? "$clickTag{$aBanner['bannertext']}$clickTagEnd" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return $prepend . $code . $bannerText . $beaconTag . $append;
}

function _buildBannerCodeText($aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false)
{
    global $phpAds_config;

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';

    // Create the anchor tag..
    $clickUrl = _buildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
    if (!empty($clickUrl)) {  // There is a link
        $status = !empty($aBanner['status']) ? " onMouseOver=\"self.status='{$aBanner['status']}'; return true;\" onMouseOut=\"self.status=''; return true;\"" : '';
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $clickTag = "<a href='$clickUrl' target='$target'$status>";
        $clickTagEnd = '</a>';
    } else {
        $clickTag = '';
        $clickTagEnd = '';
    }

    // Get the text below the banner
    $bannerText = !empty($aBanner['bannertext']) ? "$clickTag{$aBanner['bannertext']}$clickTagEnd" : '';
    // Get the image beacon...
    $beaconTag = ($logView && $phpAds_config['log_adviews'] && $phpAds_config['log_beacon']) ? _buildImageBeacon($aBanner, $zoneId, $source) : '';

    return $prepend . $bannerText . $beaconTag . $append;

}
function _buildFileUrl($aBanner, $useAlt=false, $params='')
{
    global $phpAds_config;

    $fileUrl = '';
    if ($aBanner['storagetype'] == 'url') {
        $fileUrl = $aBanner['imageurl'];
        if (!empty($params) && $aBanner['contenttype'] == 'swf') { $fileUrl .= "?$params"; }
    } else {
        $fileName = $useAlt ? $aBanner['alt_filename'] : $aBanner['filename'];
        $params = !empty($params) ? $params : '';
        if (!empty($fileName)) {
            if ($aBanner['storagetype'] == 'web') {
                $fileUrl = _getImageUrlPrefix() . "/$fileName";
                if (!empty($params)) $fileUrl .= "?$params";
            } elseif ($aBanner['storagetype'] == 'url') {
                $fileUrl = $aBanner['imageurl'];
            } elseif ($aBanner['storagetype'] == 'sql') {
                $fileUrl = _getDeliveryUrlPrefix() . "/adimage.php?filename=$fileName&contenttype={$aBanner['contenttype']}";
                if (!empty($params)) $fileUrl .= "&$params";
            }
        }
    }

    return $fileUrl;
}
function _buildClickUrl($aBanner, $zoneId=0, $source='', $ct0='', $logClick=true, $overrideDest=false)
{
    global $phpAds_config;

    $clickUrl = '';

    if (!empty($aBanner['url']) || $overrideDest) {  // There is a link
        $del = $phpAds_config['click_tracking_delimiter'];
        $delnum = strlen($del);
        $random = "{$del}cb={random}";
        $bannerId = !empty($aBanner['bannerid']) ? "{$del}bannerid={$aBanner['bannerid']}" : '';
        $source = !empty($source) ? "{$del}source=" . urlencode($source) : '';
        $log = $logClick ? '' : "{$del}log=no";
        // Determine the destination
        $dest = !empty($aBanner['url']) ? $aBanner['url'] : '';
        $ct0 = (empty($ct0) || $ct0 == 'Insert_Clicktrack_URL_Here') ? '' : $ct0;
        $aBanner['contenttype'] == "swf" ? $maxdest = '' : $maxdest = "{$del}maxdest={$ct0}{$dest}";
        $clickUrl = _getDeliveryUrlPrefix() . "/adclick.php?maxparams={$delnum}{$bannerId}{$del}zoneid={$zoneId}{$source}{$log}{$random}{$maxdest}";
    }

    return $clickUrl;

}
function _buildImageBeacon($aBanner, $zoneId=0, $source='')
{
    global $phpAds_config;

    // Add beacon image for logging
    if (isset($_SERVER['HTTP_USER_AGENT'])
       && preg_match("#Mozilla/(1|2|3|4)#", $_SERVER['HTTP_USER_AGENT'])
       && !preg_match("#compatible#", $_SERVER['HTTP_USER_AGENT'])) {
        $div = "<layer id='beacon_{$aBanner['bannerid']}' width='0' height='0' border='0' visibility='hide'>";
        $style = '';
        $divEnd = '</layer>';
    } else {
        $div = "<div id='beacon_{$aBanner['bannerid']}' style='position: absolute; left: 0px; top: 0px; visibility: hidden;'>";
        $style = " style='width: 0px; height: 0px;'";
        $divEnd = '</div>';
    }

    $urlPrefix = ($_SERVER['SERVER_PORT'] == 443) ? $phpAds_config['ssl_url_prefix'] : $phpAds_config['url_prefix'];
    $beacon = "{$div}<img src='{$urlPrefix}/adlog.php?bannerid={$aBanner['bannerid']}&amp;campaignid={$aBanner['campaignid']}&amp;zoneid={$zoneId}&amp;source={$source}&amp;block={$aBanner['block']}&amp;capping={$aBanner['capping']}&amp;session_capping={$aBanner['session_capping']}&amp;cb={random}' width='0' height='0' alt=''{$style}>{$divEnd}";

    return $beacon;
}

function MAX_buildJavascriptVariablesScript($trackerid, $conversionInfo)
{
    include_once 'db.php';
    
    global $phpAds_config;
    
    $buffer = '';
	$urlPrefix = _getDeliveryUrlPrefix();
    $aTrackerVars = MAX_getCacheVariablesByTrackerId($trackerid);
    $variableQuerystring = '';
    
    if (!empty($aTrackerVars)) {
        foreach($aTrackerVars as $key=>$variable) {
            if ($variable['variabletype'] == 'js') {
                $variableQuerystring .= "&amp;{$variable['name']}=\"+{$variable['name']}+\"";
                $buffer .= "
if (!{$variable['name']}) var {$variable['name']} = 'undefined';";
            }
        }
        if (!empty($variableQuerystring)) {
            $buffer .= "
document.write (\"<\" + \"script language='JavaScript' type='text/javascript' src='\");
document.write (\"$urlPrefix/adcnvvars.php?trackerid=$trackerid&amp;local_conversionid={$conversionInfo['local_conversionid']}&amp;dbserver_ip={$conversionInfo['dbserver_ip']}{$variableQuerystring}'\");
document.write (\"><\" + \"/script>\");";
        }
    }
    if (empty($buffer)) {
        $buffer = "document.write(\"\");";
    }
    
    return $buffer;
}

function _getDeliveryUrlPrefix()
{
    global $phpAds_config;
    return ($_SERVER['SERVER_PORT'] == 443) ? $phpAds_config['ssl_url_prefix']: $phpAds_config['url_prefix'];
}
function _getImageUrlPrefix()
{
    global $phpAds_config;
    return ($_SERVER['SERVER_PORT'] == 443) ? $phpAds_config['type_web_ssl_url']: $phpAds_config['type_web_url'];
}

?>
