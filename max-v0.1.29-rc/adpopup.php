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
$Id: adpopup.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Figure out our location
define ('phpAds_path', '.');


// Set invocation type
define ('phpAds_invocationType', 'popup');



/*********************************************************/
/* Include required files                                */
/*********************************************************/

include_once (phpAds_path."/config.inc.php"); 
include_once (phpAds_path."/libraries/lib-io.inc.php");
include_once (phpAds_path."/libraries/lib-db.inc.php");

if (($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon']) || $phpAds_config['acl'])
{
	include_once (phpAds_path."/libraries/lib-remotehost.inc.php");
	
	if ($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon'])
		include_once (phpAds_path."/libraries/lib-log.inc.php");
	
	if ($phpAds_config['acl'])
		include_once (phpAds_path."/libraries/lib-limitations.inc.php");
}

include_once (phpAds_path."/libraries/lib-view-main.inc.php");
include_once (phpAds_path."/libraries/lib-cache.inc.php");



/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
    'what',
    'context',
    'target',
    'source',
    'withtext',
    'withText',
    'left',
    'top',
    'popunder',
    'timeout',
    'delay',
    'toolbars',
    'location',
    'menubar',
    'status',
    'resizable',
    'scrollbars',
    'zoneid',
    'campaignid',
    'bannerid',
    'ct0'
);



/*********************************************************/
/* Set default values for input variables                */
/*********************************************************/

if (isset($withText) && !isset($withtext))  $withtext = $withText;

if (!isset($what)) {
    if ($zoneid)     { $what = 'zone:'.$zoneid; }
    if ($campaignid) { $what = 'campaignid:'.$campaignid; }
    if ($bannerid)   { $what = 'bannerid:'.$bannerid; }
    
    if (!isset($what)) { $what = ''; }
}

if (!isset($target)) 	 $target = '_blank';
if (!isset($source)) 	 $source = '';
if (!isset($withtext)) 	 $withtext = '';
if (!isset($context)) 	 $context = '';

if (!isset($timeout))    $timeout    = 0;

if (!isset($toolbars))   $toolbars   = 0;
if (!isset($location))	 $location   = 0;
if (!isset($menubar))	 $menubar    = 0;
if (!isset($status))	 $status     = 0;
if (!isset($resizable))  $resizable  = 0;
if (!isset($scrollbars)) $scrollbars = 0;


// Remove referer, to be sure it doesn't cause problems with limitations
if (isset($HTTP_SERVER_VARS['HTTP_REFERER'])) unset($HTTP_SERVER_VARS['HTTP_REFERER']);
if (isset($HTTP_REFERER)) unset($HTTP_REFERER);



/*********************************************************/
/* Determine which banner we are going to show           */
/*********************************************************/

$found = false;
	
// Reset followed zone chain
$phpAds_followedChain = array();
	
$first = true;
	
while (($first || $what != '') && $found == false) {
	$first = false;
	if (substr($what,0,5) == 'zone:') {
		if (!defined('LIBVIEWZONE_INCLUDED')) {
			require (phpAds_path.'/libraries/lib-view-zone.inc.php');
		}
		
		$row = phpAds_fetchBannerZone($what, $context, $source, true);
	} else {
		if (!defined('LIBVIEWDIRECT_INCLUDED')) {
			require (phpAds_path.'/libraries/lib-view-direct.inc.php');
		}
		
		$row = phpAds_fetchBannerDirect($what, $context, $source, true);
	}
	
	if (is_array ($row)) {
		$found = true;
	} else {
		$what  = $row;
	}
}

// Do not pop a window if not banner was found..
if (!$found) {
	exit;
}
	
$contenturl  = _getDeliveryUrlPrefix() . "/adcontent.php?bannerid={$row['bannerid']}&zoneid={$row['zoneid']}&target={$target}&withtext={$withtext}&source=".urlencode($source)."&timeout={$timeout}&ct0={$ct0}";
	
/*********************************************************/
/* Build the code needed to pop up a window              */
/*********************************************************/

header("Content-type: application/x-javascript");
echo "
var phpads_errorhandler = null;

if (window.captureEvents && Event.ERROR)
  window.captureEvents (Event.ERROR);

// Error handler to prevent 'Access denied' errors
function phpads_onerror(e) {
  window.onerror = phpads_errorhandler;
  return true;
}

function phpads_{$row['bannerid']}_pop() {
  phpads_errorhandler = window.onerror;
  window.onerror = phpads_onerror;

  // Determine the size of the window
  var X={$row['width']};
  var Y={$row['height']};

  // If Netscape 3 is used add 20 to the size because it doesn't support a margin of 0
  if(!window.resizeTo) {
    X+=20;
    Y+=20;
  }

  // Open the window if needed
  window.phpads_{$row['bannerid']}=window.open('', 'phpads_{$row['bannerid']}','height='+Y+',width='+X+',toolbar=".($toolbars == 1 ? 'yes' : 'no').",location=".($location == 1 ? 'yes' : 'no').",menubar=".($menubar == 1 ? 'yes' : 'no').",status=".($status == 1 ? 'yes' : 'no').",resizable=".($resizable == 1 ? 'yes' : 'no').",scrollbars=".($scrollbars == 1 ? 'yes' : 'no')."');

  if (window.phpads_{$row['bannerid']}.document.title == '' || window.phpads_{$row['bannerid']}.location == 'about:blank' || window.phpads_{$row['bannerid']}.location == '') {
    var browser = navigator.userAgent.toLowerCase();

    // Resize window to correct size, determine outer width and height - IE 5.1x on MAC should't resize!
    if (window.resizeTo && browser.indexOf('msie 5.1') == -1 && browser.indexOf('mac') == -1) {
      if(phpads_{$row['bannerid']}.innerHeight) {
        var diffY = phpads_{$row['bannerid']}.outerHeight-Y;
        var diffX = phpads_{$row['bannerid']}.outerWidth-X;
        var outerX = X+diffX;
        var outerY = Y+diffY;
      } else {
        phpads_{$row['bannerid']}.resizeTo(X, Y);
        var time = new Date().getTime();
        while (!phpads_{$row['bannerid']}.document.body) {
          if (new Date().getTime() - time > 250) {
            phpads_{$row['bannerid']}.close();
            return false;
          }
        }
        var diffY = phpads_{$row['bannerid']}.document.body.clientHeight-Y;
        var diffX = phpads_{$row['bannerid']}.document.body.clientWidth-X;
        var outerX = X-diffX;
        var outerY = Y-diffY;
      }
      phpads_{$row['bannerid']}.resizeTo(outerX, outerY);
    }";

if (isset($left) && isset($top)) {
	echo "
    if (window.moveTo) {";
	
	if ($left == 'center') {
		echo "
      var posX = parseInt((screen.width/2)-(outerX/2));";
	} elseif ($left >= 0) {
		echo "
      var posX = $left;";
	} elseif ($left < 0) {
	    echo "
      var posX = screen.width-outerX+$left;";
	}
	
	if ($top == 'center') {
	    echo "
      var posY = parseInt((screen.height/2)-(outerY/2));";
	} elseif ($top  >= 0) {
        echo "
      var posY = $top;";
	} elseif ($top  < 0) {
		echo "
      var posY = screen.height-outerY+$top;";
	}
	
	echo "
      phpads_{$row['bannerid']}.moveTo(posX, posY);
    }";
}

// Set the actual location after resize otherwise we might get 'access denied' errors
echo "
    phpads_{$row['bannerid']}.location='$contenturl';";

// Move main window to the foreground if we are dealing with a popunder
if (isset($popunder) && $popunder == '1') {
	echo "
    window.focus();";
}

echo "
  }

  window.onerror = phpads_errorhandler;
  return true;
}";

if (isset($delay) && $delay == 'exit') {
	echo "
if (window.captureEvents && Event.UNLOAD)
  window.captureEvents (Event.UNLOAD);
window.onunload = phpads_{$row['bannerid']}_pop;";
} elseif (isset($delay) && $delay > 0) {
	echo "
window.setTimeout(\"phpads_{$row['bannerid']}_pop();\", ".($delay * 1000).");";
} else {
	echo "
phpads_{$row['bannerid']}_pop();";
}
?>
