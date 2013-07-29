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
$Id: lib-db.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// MySQL DB Resource
$phpAds_db_link = '';


// Add database name to table names if compatibility mode is used
if ($phpAds_config['compatibility_mode'])
{
	$phpAds_config['tbl_acls'] 					= $phpAds_config['dbname'].".".$phpAds_config['tbl_acls'];
	$phpAds_config['tbl_adclicks'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_adclicks'];
	$phpAds_config['tbl_adconversions'] 		= $phpAds_config['dbname'].".".$phpAds_config['tbl_adconversions'];
	$phpAds_config['tbl_adstats'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_adstats'];
	$phpAds_config['tbl_adviews'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_adviews'];
	$phpAds_config['tbl_affiliates']			= $phpAds_config['dbname'].".".$phpAds_config['tbl_affiliates'];
	$phpAds_config['tbl_banners'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_banners'];
	$phpAds_config['tbl_cache'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_cache'];
	$phpAds_config['tbl_campaigns'] 			= $phpAds_config['dbname'].".".$phpAds_config['tbl_campaigns'];
	$phpAds_config['tbl_campaigns_trackers'] 	= $phpAds_config['dbname'].".".$phpAds_config['tbl_campaigns_trackers'];
	$phpAds_config['tbl_clients'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_clients'];
	$phpAds_config['tbl_config'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_config'];
	$phpAds_config['tbl_conversionlog'] 		= $phpAds_config['dbname'].".".$phpAds_config['tbl_conversionlog'];
	$phpAds_config['tbl_images'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_images'];
	$phpAds_config['tbl_session'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_session'];
	$phpAds_config['tbl_targetstats'] 			= $phpAds_config['dbname'].".".$phpAds_config['tbl_targetstats'];
	$phpAds_config['tbl_trackers'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_trackers'];
	$phpAds_config['tbl_userlog'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_userlog'];
	$phpAds_config['tbl_zones'] 				= $phpAds_config['dbname'].".".$phpAds_config['tbl_zones'];
	$phpAds_config['tbl_variables'] 			= $phpAds_config['dbname'].".".$phpAds_config['tbl_variables'];
	$phpAds_config['tbl_variablevalues'] 		= $phpAds_config['dbname'].".".$phpAds_config['tbl_variablevalues'];
}

// Disable delayed inserts when not using MyISAM tables
if ($phpAds_config['table_type'] != 'MYISAM')
	$phpAds_config['insert_delayed'] = false;



/*********************************************************/
/* Check if the extension is available                   */
/*********************************************************/

function phpAds_dbAvailable()
{
	return (function_exists('mysql_connect'));
}


/*********************************************************/
/* Open a connection to the database			         */
/*********************************************************/

function phpAds_dbConnect($db = phpAds_adminDb)
{
	global $phpAds_config;
	global $phpAds_db_link, $phpAds_rawDb_link;
	
	if ($db == phpAds_rawDb) {
	    
	    if (isset($phpAds_config['rawDbname'])) {
	       $dbport     = isset($phpAds_config['rawDbport']) ? $phpAds_config['rawDbport'] : 3306;
	       $dbhost     = $dbport != 3306 ? $phpAds_config['rawDbhost'].':'.$dbport : $phpAds_config['rawDbhost'];
	       $dbuser     = $phpAds_config['rawDbuser'];
	       $dbpassword = $phpAds_config['rawDbpassword'];
	       $dbname     = $phpAds_config['rawDbname'];
	    } else {
	        // Use the admin database as the raw database - normal Max
	        // users will do this when connecting to the "raw" database
	        $dbport     = isset($phpAds_config['dbport']) ? $phpAds_config['dbport'] : 3306;
	        $dbhost     = $dbport != 3306 ? $phpAds_config['dbhost'].':'.$dbport : $phpAds_config['dbhost'];
	        $dbuser     = $phpAds_config['dbuser'];
	        $dbpassword = $phpAds_config['dbpassword'];
	        $dbname     = $phpAds_config['dbname'];
	    }
	    
	    if ($phpAds_config['persistent_connections']) {
	        $phpAds_rawDb_link = @mysql_pconnect($dbhost, $dbuser, $dbpassword);
	    } else {
	        $phpAds_rawDb_link = @mysql_connect($dbhost, $dbuser, $dbpassword);
	    }
	    
	    if ($phpAds_config['compatibility_mode']) {
	        return $phpAds_rawDb_link;
	    }
	    
	    if (@mysql_select_db ($dbname, $phpAds_rawDb_link)) {
	        return $phpAds_rawDb_link;
	    }
	    
	} else {
	    
	    $dbport     = isset($phpAds_config['dbport']) ? $phpAds_config['dbport'] : 3306;
	    $dbhost     = $dbport != 3306 ? $phpAds_config['dbhost'].':'.$dbport : $phpAds_config['dbhost'];
	    $dbuser     = $phpAds_config['dbuser'];
	    $dbpassword = $phpAds_config['dbpassword'];
	    $dbname     = $phpAds_config['dbname'];
	    
	    if ($phpAds_config['persistent_connections']) {
	        $phpAds_db_link = @mysql_pconnect($dbhost, $dbuser, $dbpassword);
	    } else {
	        $phpAds_db_link = @mysql_connect($dbhost, $dbuser, $dbpassword);
	    }
	    
	    if ($phpAds_config['compatibility_mode'])
	    {
	        return $phpAds_db_link;
	    }
	    
	    if (@mysql_select_db ($dbname, $phpAds_db_link)) {
	        return $phpAds_db_link;
	    }
	    
	}

}



/*********************************************************/
/* Close the connection to the database			         */
/*********************************************************/

function phpAds_dbClose($db = phpAds_adminDb)
{
	// Never close the database connection, because
	// it may interfere with other scripts which
	// share the same connection.

	global $phpAds_db_link, $phpAds_rawDb_link;

	switch($db) {
		case phpAds_rawDb: 
				mysql_close($phpAds_rawDb_link);
				break;

		case phpAds_adminDb:
		default:
				break;
	}
}



/*********************************************************/
/* Execute a query								         */
/*********************************************************/

function phpAds_dbQuery($query, $db = phpAds_adminDb)
{
    global $phpAds_last_query;
	global $phpAds_db_link, $phpAds_rawDb_link;
	
    $phpAds_last_query = $query;
    return @mysql_query ($query, $db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}



/*********************************************************/
/* Get the number of rows returned                       */
/*********************************************************/

function phpAds_dbNumRows($res)
{
	return @mysql_num_rows($res);
}



/*********************************************************/
/* Get next row as an array with keys                    */
/*********************************************************/

function phpAds_dbFetchArray($res)
{
	return @mysql_fetch_array($res, MYSQL_ASSOC);
}



/*********************************************************/
/* Get next row as an array                              */
/*********************************************************/

function phpAds_dbFetchRow($res)
{
	return @mysql_fetch_row($res);
}



/*********************************************************/
/* Get a specific row and column                         */
/*********************************************************/

function phpAds_dbResult($res, $row, $column)
{
	return @mysql_result($res, $row, $column);
}



/*********************************************************/
/* Free the result from memory                           */
/*********************************************************/

function phpAds_dbFreeResult($res)
{
	return @mysql_free_result($res);
}



/*********************************************************/
/* Return the number of affected rows                    */
/*********************************************************/

function phpAds_dbAffectedRows($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_affected_rows($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}



/*********************************************************/
/* Go to the specified row                               */
/*********************************************************/

function phpAds_dbSeekRow($res, $row)
{
	return @mysql_data_seek($res, $row);
}



/*********************************************************/
/* Get the ID of the last inserted row                   */
/*********************************************************/

function phpAds_dbInsertID($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_insert_id($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}



/*********************************************************/
/* Get the error message if something went wrong         */
/*********************************************************/

function phpAds_dbError ($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_error($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

function phpAds_dbErrorNo ($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_errno($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

/*********************************************************/
/* Obtain/release the maintenance priority lock          */
/*********************************************************/

/**
 * Obtains an advisory database lock for the maintenance priority process.
 *
 * @return boolean True if lock was obtained, false otherwise.
 */
function obtainPriorityLock()
{
    $query = "SELECT GET_LOCK('priority', 1) AS 'lock'";
    $res = phpAds_dbQuery($query);
    $row = phpAds_dbResult($res, 0, 'lock');
    if ($row[0] == 1) {
        // Lock obtained successfully
        return true;
    }
    return false;
}

/**
 * Releases the advisory database lock for the maintenance priority process.
 */
function releasePriorityLock()
{
   $query = "SELECT RELEASE_LOCK('priority')";
   $res = phpAds_dbQuery($query);
}

?>