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
$Id: settings-interface.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Include required files
include ("lib-settings.inc.php");

// Register input variables
phpAds_registerGlobal('name', 'my_header', 'my_footer', 'client_welcome',
                      'client_welcome_msg', 'content_gzip_compression',
                      'admin_instant_update', 'instant_update_priority',
                      'instant_update_cache');

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true') {
	if (isset($name)) {
		phpAds_SettingsWriteAdd('name', $name);
	}
	if (isset($my_header)) {
		if (file_exists($my_header) || $my_header == '') {
			phpAds_SettingsWriteAdd('my_header', $my_header);
		} else {
			$errormessage[0][] = $strMyHeaderError;
		}
	}
	if (isset($my_footer)) {
		if (file_exists($my_footer) || $my_footer == '') {
			phpAds_SettingsWriteAdd('my_footer', $my_footer);
		} else {
			$errormessage[0][] = $strMyFooterError;
		}
	}
	phpAds_SettingsWriteAdd('content_gzip_compression', isset($content_gzip_compression));
	phpAds_SettingsWriteAdd('client_welcome', isset($client_welcome));
	if (isset($client_welcome_msg)) {
		phpAds_SettingsWriteAdd('client_welcome_msg', $client_welcome_msg);
	}
	phpAds_SettingsWriteAdd('admin_instant_update',    isset($admin_instant_update));
	phpAds_SettingsWriteAdd('instant_update_priority', isset($instant_update_priority));
	phpAds_SettingsWriteAdd('instant_update_cache',    isset($instant_update_cache));
	if (!count($errormessage)) {
		phpAds_SettingsWriteFlush();
		header("Location: settings-defaults.php");
		exit;
	}
}

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PrepareHelp();
phpAds_PageHeader("5.1");
if (phpAds_isUser(phpAds_Admin)) {
	phpAds_ShowSections(array("5.1", "5.3", "5.4", "5.2","5.5"));
} elseif (phpAds_isUser(phpAds_Agency)) {
	phpAds_ShowSections(array("5.1"));
}
phpAds_SettingsSelection("interface");

/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

$settings = array (
    array (
    	'text' 	  => $strGeneralSettings,
    	'items'	  => array (
    		array (
    			'type' 	  => 'text', 
    			'name' 	  => 'name',
    			'text' 	  => $strAppName,
    			'size'	  => 35
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type' 	  => 'text', 
    			'name' 	  => 'my_header',
    			'text' 	  => $strMyHeader,
    			'size'	  => 35
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type' 	  => 'text', 
    			'name' 	  => 'my_footer',
    			'text' 	  => $strMyFooter,
    			'size'	  => 35
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type'    => 'checkbox',
    			'name'    => 'content_gzip_compression',
    			'text'	  => $strGzipContentCompression
    		)
    	)
    ),
    array (
    	'text' 	  => $strClientInterface,
    	'items'	  => array (
    		array (
    			'type'    => 'checkbox',
    			'name'    => 'client_welcome',
    			'text'	  => $strClientWelcomeEnabled
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type' 	  => 'textarea', 
    			'name' 	  => 'client_welcome_msg',
    			'text' 	  => $strClientWelcomeText,
    			'depends' => 'client_welcome==true'
    		)
    	)
    ),
    array (
    	'text' 	  => $strInstantUpdateSettings,
    	'items'	  => array (
    		array (
    			'type'    => 'checkbox',
    			'name'    => 'admin_instant_update',
    			'text'	  => $strInstantUpdate
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type'    => 'checkbox',
    			'name'    => 'instant_update_priority',
    			'text'	  => $strInstantUpdatePriority,
    			'depends' => 'admin_instant_update==true'
    		),
    		array (
    			'type'    => 'break'
    		),
    		array (
    			'type'    => 'checkbox',
    			'name'    => 'instant_update_cache',
    			'text'	  => $strInstantUpdateCache,
    			'depends' => 'admin_instant_update==true'
    		)
    	)
    )
);

/*********************************************************/
/* Main code                                             */
/*********************************************************/

phpAds_ShowSettings($settings, $errormessage);

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>
