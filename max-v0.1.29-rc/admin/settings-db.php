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
$Id: settings-db.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
include ("lib-settings.inc.php");


// Register input variables
phpAds_registerGlobal ('dbhost', 'dbport', 'dbuser', 'dbpassword', 'dbname', 
					   'persistent_connections', 'insert_delayed', 
					   'compatibility_mode', 'auto_clean_tables_vacuum');


// Security check
phpAds_checkAccess(phpAds_Admin);


$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true')
{

	if (isset($dbpassword) && ereg('^\*+$', $dbpassword))
		$dbpassword = $phpAds_config['dbpassword'];
	
	if (isset($dbhost) && isset($dbuser) && isset($dbpassword) && isset($dbname))
	{
		phpAds_dbClose();
		
		unset($phpAds_db_link);
		
		$phpAds_config['dbhost'] = $dbhost;
		$phpAds_config['dbport'] = $dbport;
		$phpAds_config['dbuser'] = $dbuser;
		$phpAds_config['dbpassword'] = $dbpassword;
		$phpAds_config['dbname'] = $dbname;
		$phpAds_config['persistent_connections'] = isset($persistent_connections) ? true : false;
		
		if (!phpAds_dbConnect(true))
			$errormessage[0][] = $strCantConnectToDb;
		else
		{
			phpAds_SettingsWriteAdd('dbname', $dbhost);
			phpAds_SettingsWriteAdd('dbport', $dbport);
			phpAds_SettingsWriteAdd('dbuser', $dbuser);
			phpAds_SettingsWriteAdd('dbpassword', $dbpassword);
			phpAds_SettingsWriteAdd('dbname', $dbname);
			
			phpAds_SettingsWriteAdd('persistent_connections', isset($persistent_connections));
		}
	}
	
	phpAds_SettingsWriteAdd('insert_delayed', isset($insert_delayed));
	phpAds_SettingsWriteAdd('compatibility_mode', isset($compatibility_mode));
	
	
	if (!count($errormessage))
	{
		phpAds_SettingsWriteFlush();
		header("Location: settings-invocation.php");
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
phpAds_SettingsSelection("db");



/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

$settings = array (

array (
	'text' 	  => $strDatabaseServer,
	'visible' => phpAds_isUser(phpAds_Admin),
	'items'	  => array (
		array (
			'type' 	  => 'text', 
			'name' 	  => 'dbhost',
			'text' 	  => $strDbHost,
			'req'	  => true,
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'dbport',
			'text' 	  => $strDbPort,
			'req'	  => true,
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'dbuser',
			'text' 	  => $strDbUser,
			'req'	  => true,
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'password', 
			'name' 	  => 'dbpassword',
			'text' 	  => $strDbPassword,
			'req'	  => false,
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'dbname',
			'text' 	  => $strDbName,
			'req'	  => true,
		)
	)
),
array (
	'text' 	  => $strDatabaseOptimalisations,
	'visible' => phpAds_isUser(phpAds_Admin),
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'persistent_connections',
			'text'	  => $strPersistentConnections
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'insert_delayed',
			'text'	  => $strInsertDelayed,
			'visible' => $phpAds_productname == 'Max Media Manager' && $phpAds_config['table_type'] == 'MYISAM'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'compatibility_mode',
			'text'	  => $strCompatibilityMode,
			'visible' => $phpAds_productname == 'Max Media Manager'
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