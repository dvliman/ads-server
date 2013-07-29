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
$Id: invocation.lang.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Invocation Types
$GLOBALS['strInvocationRemote']				= "Remote Invocation";
$GLOBALS['strInvocationRemoteNoCookies']	= "Remote Invocation - no cookies";
$GLOBALS['strInvocationJS']					= "Remote Invocation for Javascript";
$GLOBALS['strInvocationIframes']			= "Remote Invocation for Frames";
$GLOBALS['strInvocationXmlRpc']				= "Remote Invocation using XML-RPC";
$GLOBALS['strInvocationCombined']			= "Combined Remote Invocation";
$GLOBALS['strInvocationPopUp']				= "Pop-up";
$GLOBALS['strInvocationAdLayer']			= "Interstitial or Floating DHTML";
$GLOBALS['strInvocationLocal']				= "Local mode";


// Other
$GLOBALS['strCopyToClipboard']				= "Copy to clipboard";


// Measures
$GLOBALS['strAbbrPixels']					= "px";
$GLOBALS['strAbbrSeconds']					= "sec";


// Common Invocation Parameters
$GLOBALS['strInvocationWhat']						= "Banner selection";
$GLOBALS['strInvocationClientID']					= "Advertiser";
$GLOBALS['strInvocationCampaignID']					= "Campaign";
$GLOBALS['strInvocationTarget']						= "Target frame";
$GLOBALS['strInvocationSource']						= "Source";
$GLOBALS['strInvocationWithText']					= "Show text below banner";
$GLOBALS['strInvocationDontShowAgain']				= "Don't show the banner again on the same page";
$GLOBALS['strInvocationDontShowAgainCampaign']		= "Don't show a banner from the same campaign again on the same page";
$GLOBALS['strInvocationTemplate'] 					= "Store the banner inside a variable so it can be used in a template";
$GLOBALS['strInvocationBannerID']					= "Banner ID";

// Iframe
$GLOBALS['strIFrameRefreshAfter']		= "Refresh after";
$GLOBALS['strIframeResizeToBanner']		= "Resize iframe to banner dimensions";
$GLOBALS['strIframeMakeTransparent']		= "Make the iframe transparent";
$GLOBALS['strIframeIncludeNetscape4']		= "Include Netscape 4 compatible ilayer";


// PopUp
$GLOBALS['strPopUpStyle']			= "Pop-up type";
$GLOBALS['strPopUpStylePopUp']			= "Pop-up";
$GLOBALS['strPopUpStylePopUnder']		= "Pop-under";
$GLOBALS['strPopUpCreateInstance']		= "Instance when the pop-up is created";
$GLOBALS['strPopUpImmediately']			= "Immediately";
$GLOBALS['strPopUpOnClose']			= "When the page is closed";
$GLOBALS['strPopUpAfterSec']			= "After";
$GLOBALS['strAutoCloseAfter']			= "Automatically close after";
$GLOBALS['strPopUpTop']				= "Initial position (top)";
$GLOBALS['strPopUpLeft']			= "Initial position (left)";
$GLOBALS['strWindowOptions']		= "Window options";
$GLOBALS['strShowToolbars']			= "Toolbars";
$GLOBALS['strShowLocation']			= "Location";
$GLOBALS['strShowMenubar']			= "Menubar";
$GLOBALS['strShowStatus']			= "Status";
$GLOBALS['strWindowResizable']		= "Resizable";
$GLOBALS['strShowScrollbars']		= "Scrollbars";


// XML-RPC
$GLOBALS['strXmlRpcLanguage']			= "Host language";


// AdLayer
$GLOBALS['strAdLayerStyle']			= "Style";

$GLOBALS['strAlignment']			= "Alignment";
$GLOBALS['strHAlignment']			= "Horizontal alignment";
$GLOBALS['strLeft']				= "Left";
$GLOBALS['strCenter']				= "Center";
$GLOBALS['strRight']				= "Right";

$GLOBALS['strVAlignment']			= "Vertical alignment";
$GLOBALS['strTop']				= "Top";
$GLOBALS['strMiddle']				= "Middle";
$GLOBALS['strBottom']				= "Bottom";

$GLOBALS['strAutoCollapseAfter']		= "Automatically collapse after";
$GLOBALS['strCloseText']			= "Close text";
$GLOBALS['strClose']				= "[Close]";
$GLOBALS['strBannerPadding']			= "Banner padding";

$GLOBALS['strHShift']				= "Horizontal shift";
$GLOBALS['strVShift']				= "Vertical shift";

$GLOBALS['strShowCloseButton']			= "Show close button";
$GLOBALS['strBackgroundColor']			= "Background color";
$GLOBALS['strBorderColor']			= "Border color";

$GLOBALS['strDirection']			= "Direction";
$GLOBALS['strLeftToRight']			= "Left to right";
$GLOBALS['strRightToLeft']			= "Right to left";
$GLOBALS['strLooping']				= "Looping";
$GLOBALS['strAlwaysActive']			= "Always active";
$GLOBALS['strSpeed']				= "Speed";
$GLOBALS['strPause']				= "Pause";
$GLOBALS['strLimited']				= "Limited";
$GLOBALS['strLeftMargin']			= "Left margin";
$GLOBALS['strRightMargin']			= "Right margin";
$GLOBALS['strTransparentBackground']		= "Transparent background";

$GLOBALS['strSmoothMovement']		= "Smooth movement";
$GLOBALS['strHideNotMoving']		= "Hide the banner when the cursor is not moving";
$GLOBALS['strHideDelay']			= "Delay before banner is hidden";
$GLOBALS['strHideTransparancy']		= "Transparancy of the hidden banner";


$GLOBALS['strAdLayerStyleName']	= array(
	'geocities'		=> "Geocities",
	'simple'		=> "Simple",
	'cursor'		=> "Cursor",
	'floater'		=> "Floater"
);

// Support for 3rd party server clicktracking
$GLOBALS['str3rdPartyTrack']		= "Support 3rd Party Server Clicktracking";

?>
