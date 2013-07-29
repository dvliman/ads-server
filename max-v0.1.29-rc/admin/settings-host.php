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
$Id: settings-host.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
include ("lib-settings.inc.php");


// Register input variables
phpAds_registerGlobal ('reverse_lookup', 'proxy_lookup', 'obfuscate', 'geotracking_location', 'geotracking_type', 
					   'geotracking_cookie');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);


$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true')
{
	phpAds_SettingsWriteAdd('reverse_lookup', isset($reverse_lookup));
	phpAds_SettingsWriteAdd('proxy_lookup', isset($proxy_lookup));
	phpAds_SettingsWriteAdd('obfuscate', isset($obfuscate));

	if (isset($geotracking_type)) 
	{
		if ($geotracking_type == '0') $geotracking_type = '';
		phpAds_SettingsWriteAdd('geotracking_type', $geotracking_type);
	}
	phpAds_SettingsWriteAdd('geotracking_cookie', isset($geotracking_cookie));
	
	
	if (isset($geotracking_location))
	{
		if (file_exists($geotracking_location) || $geotracking_location == '')
			phpAds_SettingsWriteAdd('geotracking_location', $geotracking_location);
		else
			$errormessage[1][] = $strGeotrackingLocationError;
	}
	
	
	
	if (!count($errormessage))
	{
		phpAds_SettingsWriteFlush();
		header("Location: settings-stats.php");
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
phpAds_SettingsSelection("host");



/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

// Prepare geotargeting options
$geo_plugins = array();

$geo_plugin_dir = opendir(phpAds_path.'/libraries/geotargeting/');
while ($geo_plugin = readdir($geo_plugin_dir))
{
	if (preg_match('|geo-.*\.inc\.php|i', $geo_plugin) &&
		file_exists(phpAds_path.'/libraries/geotargeting/'.$geo_plugin))
	{
		@include_once (phpAds_path.'/libraries/geotargeting/'.$geo_plugin);
		
		eval("$"."geo_plugin_info = phpAds_".$phpAds_geoPluginID."_getInfo();");
		$geo_plugins_info[$phpAds_geoPluginID] = $geo_plugin_info;
		$geo_plugins[$phpAds_geoPluginID] = $geo_plugin_info['name'];
	}
}

closedir($geo_plugin_dir);
asort($geo_plugins, SORT_STRING);


$i = 1;
$geo_plugins_sorted = array($strNone);
$geo_plugins_db = 'geotracking_type!=0';

while (list($k, $v) = each ($geo_plugins))
{
	$geo_plugins_sorted[$k] = $v;
	
	if (!$geo_plugins_info[$k]['db'])
		$geo_plugins_db .= ' && geotracking_type!='.$i;
	
	$i++;
}




$settings = array (

array (
	'text' 	  => $strRemoteHost,
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'reverse_lookup',
			'text'	  => $strReverseLookup
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'proxy_lookup',
			'text'	  => $strProxyLookup
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'obfuscate',
			'text'	  => $strObfuscate
		)
	)
),
array (
	'text' 	  => $strGeotargeting,
	'items'	  => array (
		array (
			'type' 	  => 'select', 
			'name' 	  => 'geotracking_type',
			'text' 	  => $strGeotrackingType,
			'items'   => $geo_plugins_sorted
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'geotracking_location',
			'text' 	  => $strGeotrackingLocation,
			'size'	  => 35,
			'depends' => $geo_plugins_db
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'geotracking_cookie',
			'text'	  => $strGeoStoreCookie,
			'depends' => 'geotracking_type>0'
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