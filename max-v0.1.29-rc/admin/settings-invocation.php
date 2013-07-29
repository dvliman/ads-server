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
$Id: settings-invocation.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
include ("lib-settings.inc.php");


// Register input variables
phpAds_registerGlobal (
	 'acl'
	,'allow_invocation_frame'
	,'allow_invocation_interstitial'
	,'allow_invocation_js'
	,'allow_invocation_local'
	,'allow_invocation_plain'
	,'allow_invocation_plain_nocookies'
	,'allow_invocation_popup'
	,'allow_invocation_xmlrpc'
	,'con_key'
	,'delivery_caching'
	,'mult_key'
	,'p3p_compact_policy'
	,'p3p_policies'
	,'p3p_policy_location'
	,'use_keywords'
);


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);


$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true')
{
	phpAds_SettingsWriteAdd('allow_invocation_plain', isset($allow_invocation_plain));
	phpAds_SettingsWriteAdd('allow_invocation_plain_nocookies', isset($allow_invocation_plain_nocookies));
	phpAds_SettingsWriteAdd('allow_invocation_js', isset($allow_invocation_js));
	phpAds_SettingsWriteAdd('allow_invocation_frame', isset($allow_invocation_frame));
	phpAds_SettingsWriteAdd('allow_invocation_xmlrpc', isset($allow_invocation_xmlrpc));
	phpAds_SettingsWriteAdd('allow_invocation_local', isset($allow_invocation_local));
	phpAds_SettingsWriteAdd('allow_invocation_interstitial', isset($allow_invocation_interstitial));
	phpAds_SettingsWriteAdd('allow_invocation_popup', isset($allow_invocation_popup));
	
	if (isset($delivery_caching)) phpAds_SettingsWriteAdd('delivery_caching', $delivery_caching);
	phpAds_SettingsWriteAdd('acl', isset($acl));
	phpAds_SettingsWriteAdd('use_keywords', isset($use_keywords));
	phpAds_SettingsWriteAdd('con_key', isset($con_key));
	phpAds_SettingsWriteAdd('mult_key', isset($mult_key));
	
	phpAds_SettingsWriteAdd('p3p_policies', isset($p3p_policies));
	if (isset($p3p_compact_policy)) phpAds_SettingsWriteAdd('p3p_compact_policy', $p3p_compact_policy);
	if (isset($p3p_policy_location)) phpAds_SettingsWriteAdd('p3p_policy_location', $p3p_policy_location);
	
	if (!count($errormessage))
	{
		phpAds_SettingsWriteFlush();
		header("Location: settings-host.php");
		exit;
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PrepareHelp();
phpAds_PageHeader("5.1");
if (phpAds_isUser(phpAds_Admin))
{
	phpAds_ShowSections(array("5.1", "5.3", "5.4", "5.2","5.5"));
}
elseif (phpAds_isUser(phpAds_Agency))
{
	phpAds_ShowSections(array("5.1"));
}
phpAds_SettingsSelection("invocation");



/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

// Determine delivery cache methods
$delivery_cache_methods['none'] = $strNone;
$delivery_cache_methods['db'] = $strCacheDatabase;

if ($fp = @fopen(phpAds_path.'/cache/available', 'wb'))
{
	@fclose($fp);
	@unlink(phpAds_path.'/cache/available');
	
	$delivery_cache_methods['file'] = $strCacheFiles;
}

if (function_exists('shmop_open'))
	$delivery_cache_methods['shm'] = $strCacheShmop." (".$strExperimental.")";

if (function_exists('shm_attach'))
	$delivery_cache_methods['sysvshm'] = $strCacheSysvshm." (".$strExperimental.")"; 


$settings = array (

array (
	'text' 	  => $strDeliverySettings,
	'visible' => phpAds_isUser(phpAds_Admin),
	'items'	  => array (
		array (
			'type' 	  => 'select', 
			'name' 	  => 'delivery_caching',
			'text' 	  => $strCacheType,
			'items'   => $delivery_cache_methods
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'acl',
			'text'	  => $strUseAcl
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'use_keywords',
			'text'	  => $strUseKeywords
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'con_key',
			'text'	  => $strUseConditionalKeys,
			'depends' => 'use_keywords==true'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'mult_key',
			'text'	  => $strUseMultipleKeys,
			'depends' => 'use_keywords==true'
		)
	)
),
array (
	'text' 	  => $strAllowedInvocationTypes,
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_plain',
			'text'	  => $strAllowRemoteInvocation
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_plain_nocookies',
			'text'	  => $strAllowRemoteInvocationNoCookies
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_js',
			'text'	  => $strAllowRemoteJavascript
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_frame',
			'text'	  => $strAllowRemoteFrames
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_xmlrpc',
			'text'	  => $strAllowRemoteXMLRPC
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_local',
			'text'	  => $strAllowLocalmode
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_interstitial',
			'text'	  => $strAllowInterstitial
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'allow_invocation_popup',
			'text'	  => $strAllowPopups
		)
	)
),
array (
	'text' 	  => $strP3PSettings,
	'visible' => phpAds_isUser(phpAds_Admin),
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'p3p_policies',
			'text'	  => $strUseP3P
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'p3p_compact_policy',
			'text' 	  => $strP3PCompactPolicy,
			'size'	  => 35,
			'depends' => 'p3p_policies==true'
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'p3p_policy_location',
			'text' 	  => $strP3PPolicyLocation,
			'size'	  => 35,
			'depends' => 'p3p_policies==true',
			'check'   => 'url'
		)
	)
));



/*********************************************************/
/* Main code                                             */
/*********************************************************/

phpAds_ShowSettings($settings, $errormessage);



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>