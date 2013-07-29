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
$Id: adclick.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Figure out our location
define ('phpAds_path', '.');

/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php");
require_once (phpAds_path."/libraries/lib-io.inc.php");
require (phpAds_path."/libraries/lib-db.inc.php");
include_once (phpAds_path . '/libraries/db.php');

if ($phpAds_config['log_adclicks'])
{
    require (phpAds_path."/libraries/lib-remotehost.inc.php");
    require (phpAds_path."/libraries/lib-log.inc.php");
}



/*********************************************************/
/* Register input variables                              */
/*********************************************************/

if (substr($_SERVER['QUERY_STRING'], 0, 10) == 'maxparams=') {
    $maxparams = substr($_SERVER['QUERY_STRING'], 10);
    // First, find the delimiter used in this example:
    $tmpDelimiter = $maxparams{0};
    if (is_numeric($tmpDelimiter)) {
        $delnum    = $tmpDelimiter;
        $delimiter = substr($maxparams, 1, $tmpDelimiter);
        $maxparams = urldecode(substr($maxparams, $tmpDelimiter+1));
    } else {
        $delnum    = 1;
        $delimiter = $tmpDelimiter;
        $maxparams = urldecode(substr($maxparams, 1));
    }
    // Everything up to the 'maxdest' parameter is a variable.
    //  The maxdest parameter is the last parameter that we need to deal with.
    //  Everything after maxdest is redirected to the next clickurl.
    //  This is done so we can redirect to ourselves.
    $sMaxDest = 'maxdest=';
    $nMaxDest = strlen($sMaxDest);
    $ix = strpos($maxparams, $sMaxDest);
    if ($ix > 0) {
        $maxdest = substr($maxparams, $ix+$nMaxDest);
        $maxparams = substr($maxparams, 0, $ix-$delnum);
    } else {
    $sMaxDest = 'dest=';
    $nMaxDest = strlen($sMaxDest);
        $ix = strpos($maxparams, $sMaxDest);
        if ($ix > 0) {
            $maxdest = substr($maxparams, $ix+$nMaxDest);
            $maxparams = substr($maxparams, 0, $ix-$delnum);
        }
    }
    // Split out the params based on the delimiter:
    $aElements = explode($delimiter, $maxparams);
    // Now, use a regular expression to break out 'a=b' to 'a', 'b'.
    // Remember to account for the fact that there may be more than one '=' sign in the expression,
    //  but the expression should only account for the first one:
    $aValidVariables = array('bannerid','n','log','zoneid','source','maxdest','ismap');
    foreach ($aElements as $element) {
        if (!empty($element)) {
            preg_match('/([^=]*)=(.*)/', $element, $matches);
            if (!empty($matches[1]) && in_array($matches[1], $aValidVariables)) {
                $GLOBALS[$matches[1]] = $matches[2];
            }
        }
    }
} else {
    phpAds_registerGlobal (
                            'bannerid',
                            'bannerID',
                            'dest',
                            'maxdest',
                            'ismap',
                            'n',
                            'log',
                            'source',
                            'zoneid'
                          );
}


/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Determine the user ID
$userid = phpAds_getUniqueUserID();
phpAds_setCookie("phpAds_id", $userid, time()+365*24*60*60);


if (!isset($bannerid) && isset($bannerID)) {
    $bannerid = $bannerID;
}

if (!isset($maxdest) && isset($dest)) {
    $maxdest = $dest;
}


// Fetch BannerID
if (!isset($bannerid) or $bannerid == '') {
    // Bannerid and destination not known, try to get values from the phpAds_banner cookie.
    if (empty($n)) {
        $n = 'default';
    }

    if (!empty($_COOKIE['phpAds_banner'][$n]) && $_COOKIE['phpAds_banner'][$n] != 'DEFAULT') {
        $cookie = unserialize (stripslashes($_COOKIE['phpAds_banner'][$n]));
        
        if (isset($cookie['bannerid'])) {
            $bannerid = addslashes($cookie['bannerid']);
        } else {
            $bannerid = 'DEFAULT';
        }
        
        if (isset($cookie['zoneid'])) {
            $zoneid = addslashes($cookie['zoneid']);
        }
        
        if (isset($cookie['source'])) {
            $source = addslashes($cookie['source']);
        }
        
        if (isset($cookie['maxdest'])) {
            $maxdest =addslashes($cookie['maxdest']);
        }
        
    } else {
        $bannerid = 'DEFAULT';
    }
}

// If zoneid is not set, log it as a regular banner
if (!isset($zoneid) || strlen($zoneid)==0) {
    $zoneid = 0;
}

// Derive the source
if (isset($source)) {
    $source = phpAds_deriveSource($source);
} else {
    $source = '';
}

// Log clicks
if (!phpAds_isClickBlocked($bannerid)) {
    
    if ($phpAds_config['log_adclicks']) {
        phpAds_logClick($userid, $bannerid, $zoneid, $source);
    }
    
    // Send block cookies
    phpAds_updateClickBlockTime($bannerid);
    phpAds_flushCookie ();
}

// Get destination URL
if (!empty($maxdest)) {                    // Otherwise check for destination specified in tag
    $url = stripslashes($maxdest);
} else {
    if ($bannerid != 'DEFAULT') {
        $aBanner = MAX_getCacheBannerByBannerId($bannerid);
        if (!empty($aBanner)) {
            // Get the url from the banner cache
            $url = $aBanner['url'];
        }
    } else {
        // Get the default banner url
        $url = $phpAds_config['default_banner_target'];
    }
}
if (empty($url) || stristr($url, "\n") || stristr($url, "\r")) {
    // Cannot find the destination - redirect to where you came from...
    $url = $_SERVER['HTTP_REFERER'];
}

// Get vars so that we can pass them to the next call
$aVariables = array();
$aValidVariables = array('bannerid','cb','dest','maxdest','ismap','log','maxparams','n','source','zoneid','amp;cb');
if (isset($_GET) && !isset($maxparams)) {
    foreach ($_GET as $name => $value) {
        if (!in_array($name, $aValidVariables)) {
            $aVariables[] = "$name=$value";
        }
    }
}
if (isset($_POST)) {
    foreach ($_POST as $name => $value) {
        if (!in_array($name, $aValidVariables)) {
            $aVariables[] = "$name=$value";
        }
    }
}

if (!empty($aVariables)) {
    $variables = implode('&', $aVariables);
    if (strpos ($url, '?') > 0) {
        $url = "$url&$variables";
    } else {
        $url = "$url?$variables";
    }
}

// ISMAP click location
if (!empty($ismap)) {
    $url .= $ismap;
}
    
// Redirect to the destination url
Header ("Location: $url");
exit;

?>
