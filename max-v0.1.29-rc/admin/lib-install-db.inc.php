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
$Id: lib-install-db.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Define
define('phpAds_databaseUpgradeSupported', true);
define('phpAds_databaseCreateSupported', true);
define('phpAds_databaseCheckSupported', true);
define('phpAds_tableTypesSupported', true);

/*********************************************************/
/* Check if the database already exists                  */
/*********************************************************/

function phpAds_checkDatabaseExists()
{
    // Get the database structure
    $dbstructure 	 = phpAds_prepareDatabaseStructure();
    $availabletables = array();
    // Get table names
    $res = phpAds_dbQuery("SHOW TABLES");
    while ($row = phpAds_dbFetchRow($res)) {
        $availabletables[] = $row[0];
    }
    $result = false;
    for (reset($dbstructure); $key = key($dbstructure); next($dbstructure)) {
        if (is_array($availabletables) && in_array($key, $availabletables)) {
            // Table exists
            $result = true;
            break;
        }
    }
    return $result;
}

/*********************************************************/
/* Check if the database is valid                        */
/*********************************************************/

function phpAds_checkDatabaseValid()
{
    // Get the database structure
    $dbstructure = phpAds_prepareDatabaseStructure();
    $result      = true;
    // Get table names
    $res = phpAds_dbQuery("SHOW TABLES");
    while ($row = phpAds_dbFetchRow($res)) {
        $availabletables[] = $row[0];
    }
    for (reset($dbstructure); $key = key($dbstructure); next($dbstructure)) {
        if (is_array($availabletables) && in_array($key, $availabletables)) {
            // Table exists, check if it is valid
            if (!phpAds_checkTable($key, $dbstructure[$key])) {
                $result = false;
            }
        } else {
            $result = false;
        }
    }
    return ($result);
}

/*********************************************************/
/* Upgrade the database to the latest structure          */
/*********************************************************/

function phpAds_upgradeDatabase($tabletype = '')
{
    // Get the database structure
    $dbstructure = phpAds_prepareDatabaseStructure();
    // Get table names
    $res = phpAds_dbQuery("SHOW TABLES");
    while ($row = phpAds_dbFetchRow($res)) {
        $availabletables[] = $row[0];
    }
    for (reset($dbstructure); $key = key($dbstructure); next($dbstructure)) {
        if (is_array($availabletables) && in_array($key, $availabletables)) {
            // Table exists, upgrade
            phpAds_upgradeTable($key, $dbstructure[$key]);
        } else {
            // Table doesn't exists, create
            phpAds_createTable($key, $dbstructure[$key], $tabletype);
        }
    }
    return true;
}

/*********************************************************/
/* Upgrade the data to the latest structure              */
/*********************************************************/

function phpAds_upgradeData()
{
    // Detect version of needed plugins
    phpAds_upgradeDetectPluginVersion();
    // Upgrade append type to zones when possible
    phpAds_upgradeAppendZones();
    // Upgrade append type to zones when possible
    phpAds_upgradeDisplayLimitations();
    // Split out Campaign and Advertiser information
    phpAds_upgradeSplitClientTable();
    phpAds_upgradeSplitClientTableBanners();
    phpAds_upgradeSplitClientTableTargetStats();
    phpAds_upgradeSplitClientTableZone();
    // Make sure that a date and hour are in the stats generation columns
    phpAds_upgradeHourlyStats();
    // Create target stats form userlog
    phpAds_upgradeTargetStats();
    // Update the password to MD5 hashes
    phpAds_upgradePasswordMD5();
}

/*********************************************************/
/* Create the database                                   */
/*********************************************************/

function phpAds_createDatabase($tabletype = '')
{
    // Get the database structure
    $dbstructure = phpAds_prepareDatabaseStructure();
    $availabletables = array();
    // Get table names
    $res = phpAds_dbQuery("SHOW TABLES");
    while ($row = phpAds_dbFetchRow($res)) {
        $availabletables[] = $row[0];
    }
    for (reset($dbstructure); $key = key($dbstructure); next($dbstructure)) {
        if (is_array($availabletables) && in_array($key, $availabletables)) {
            // Table exists, drop it
            phpAds_dropTable($key);
        }
        // Table doesn't exists, create
        phpAds_createTable($key, $dbstructure[$key], $tabletype);
        if (preg_match('/application_variable/', $key)) {
            // Insert the Max version into the database
            global $phpAds_version_readable;
            $query = "INSERT INTO $key (name, value) VALUES ('max_version', '$phpAds_version_readable')";
            phpAds_dbQuery($query);
        }
    }


    return true;
}

/*********************************************************/
/* Check if a table has the correct structure            */
/*********************************************************/

function phpAds_checkTable($name, $structure)
{
    $result  = true;
    $columns = $structure['columns'];
    if (isset($structure['primary'])) $primary = $structure['primary'];
    if (isset($structure['index']))   $index   = $structure['index'];
    if (isset($structure['unique']))  $unique  = $structure['unique'];
    // Get existing columns
    $res = phpAds_dbQuery("DESCRIBE ".$name);
    while ($row = phpAds_dbFetchArray($res)) {
        $availablecolumns[$row['Field']] = $row;
    }
    // Check columns
    for (reset($columns); $key = key($columns); next($columns)) {
        if (!(isset($availablecolumns[$key]) && is_array($availablecolumns[$key]))) {
            $result = false;
        }
    }
    return $result;
}

/*********************************************************/
/* Upgrade a table to the latest structure               */
/*********************************************************/

function phpAds_upgradeTable($name, $structure)
{
    $columns = $structure['columns'];
    if (isset($structure['primary'])) $primary = $structure['primary'];
    if (isset($structure['index']))   $index   = $structure['index'];
    if (isset($structure['unique']))  $unique  = $structure['unique'];
    // Change case of all columns to lower
    $res = phpAds_dbQuery("DESCRIBE ".$name);
    while ($row = phpAds_dbFetchArray($res)) {
        if ($row['Field'] != strtolower($row['Field'])) {
            // Change case
            $check = $row['Type'];
            if ($row['Default'] != '') $check .= " DEFAULT '".$row['Default']."'";
            if ($row['Null'] != 'YES') $check .= " NOT NULL";
            if (ereg('auto_increment', $row['Extra'])) $check .= " AUTO_INCREMENT";
            $query = "ALTER TABLE ".$name." CHANGE COLUMN ".$row['Field']." ".strtolower($row['Field'])." ".$check;
            phpAds_dbQuery($query);
        }
    }
    // Get existing columns
    $res = phpAds_dbQuery("DESCRIBE ".$name);
    while ($row = phpAds_dbFetchArray($res)) {
        $availablecolumns[$row['Field']] = $row;
    }
    // Get existing indexes
    $res = phpAds_dbQuery("SHOW INDEX FROM ".$name);
    while ($row = phpAds_dbFetchArray($res)) {
        if ($row['Key_name'] != 'PRIMARY') {
            if ($row['Non_unique'] == 0) {
                $availableunique[$row['Key_name']][] = $row['Column_name'];
            } else {
                $availableindex[$row['Key_name']][] = $row['Column_name'];
            }
        } else {
            $availableprimary[] = $row['Column_name'];
        }
    }
    // Delete not needed unique indexes
    if (isset($availableunique) && is_array($availableunique)) {
        for (reset($availableunique); $key = key($availableunique); next($availableunique)) {
            if (!isset($unique[$key]) || !is_array($unique[$key]) || sizeof($unique[$key]) == 0) {
                phpAds_dbQuery("ALTER TABLE ".$name." DROP INDEX ".$key);
            }
        }
    }
    // Delete not needed indexes
    if (isset($availableindex) && is_array($availableindex)) {
        for (reset($availableindex); $key = key($availableindex); next($availableindex)) {
            if (!isset($index[$key]) || !is_array($index[$key]) || sizeof($index[$key]) == 0) {
                phpAds_dbQuery("ALTER TABLE ".$name." DROP INDEX ".$key);
            }
        }
    }
    // Delete not needed primary key
    if (isset($availableprimary) && is_array($availableprimary)) {
        if (!isset($primary) || !is_array($primary) || sizeof($primary) == 0) {
            phpAds_dbQuery("ALTER TABLE ".$name." DROP PRIMARY KEY");
        }
    }
    // Delete info about indexes
    if (isset($availableunique))  unset($availableunique);
    if (isset($availableindex))   unset($availableindex);
    if (isset($availableprimary)) unset($availableprimary);
    // Get existing indexes again
    $res = phpAds_dbQuery("SHOW INDEX FROM ".$name);
    while ($row = phpAds_dbFetchArray($res)) {
        if ($row['Key_name'] != 'PRIMARY') {
            if ($row['Non_unique'] == 0) {
                $availableunique[$row['Key_name']][] = $row['Column_name'];
            } else {
                $availableindex[$row['Key_name']][] = $row['Column_name'];
            }
        } else {
            $availableprimary[] = $row['Column_name'];
        }
    }
    // Check columns
    for (reset($columns); $key = key($columns); next($columns)) {
        $createdefinition = $key." ".$columns[$key];
        if (isset($availablecolumns[$key]) && is_array($availablecolumns[$key])) {
            // Column exists, check if it need updating
            $check = $availablecolumns[$key]['Type'];
            if ($availablecolumns[$key]['Default'] != '') $check .= " DEFAULT '".$availablecolumns[$key]['Default']."'";
            if ($availablecolumns[$key]['Null'] != 'YES') $check .= " NOT NULL";
            if (ereg('auto_increment', $availablecolumns[$key]['Extra'])) $check .= " AUTO_INCREMENT";
            if ($check != $columns[$key]) {
                // Check if the column is a boolean
                if (ereg("enum\('t','f'\)", $columns[$key]) && $availablecolumns[$key]['Type'] == "enum('true','false')") {
                    // Boolean found
                    // Change to intermediate type first
                    $intermediate = "enum('true','false','t','f')";
                    if ($availablecolumns[$key]['Default'] != '') $intermediate .= " DEFAULT '".$availablecolumns[$key]['Default']."'";
                    if ($availablecolumns[$key]['Null'] != 'YES') $intermediate .= " NOT NULL";
                    if (ereg('auto_increment', $availablecolumns[$key]['Extra'])) $intermediate .= " AUTO_INCREMENT";
                    phpAds_dbQuery("ALTER TABLE ".$name." MODIFY COLUMN ".$key." ".$intermediate);
                    // Change values
                    phpAds_dbQuery("UPDATE ".$name." SET ".$key." = 't' WHERE ".$key." = 'true'");
                    phpAds_dbQuery("UPDATE ".$name." SET ".$key." = 'f' WHERE ".$key." = 'false'");
                    // Okay, now continue and change the type to the new boolean
                }
                phpAds_dbQuery("ALTER TABLE ".$name." MODIFY COLUMN ".$createdefinition);
            }
        } else {
            // Column doesn't exist, create it
            phpAds_dbQuery("ALTER TABLE ".$name." ADD COLUMN ".$createdefinition);
        }
    }
    $incrementmodified = false;
    // Check Primary
    if (isset($primary) && is_array($primary) && sizeof($primary) > 0) {
        // Okay... there needs to be a primary key
        if (!isset($availableprimary) || !is_array($availableprimary)) {
            // Primary key does not exist yet, so create it from scratch
            phpAds_dbQuery("ALTER TABLE ".$name." ADD PRIMARY KEY (".implode(",", $primary).")");
        } else {
            // Primary key already exists, check if it is the same as we want it to be
            if (implode(',', $availableprimary) != implode(',', $primary)) {
                // An existing primary key needs to be modified
                // Check if this column is 'auto_increment'
                if (sizeof($primary) == 1 && ereg('auto_increment', $availablecolumns[$primary[0]]['Extra'])) {
                    // Get name of column
                    $key = $primary[0];
                    // Remove 'auto_increment' from column
                    $createdefinition = $key." ".str_replace('AUTO_INCREMENT', '', $columns[$key]);
                    phpAds_dbQuery("ALTER TABLE ".$name." MODIFY COLUMN ".$createdefinition);
                    $incrementmodified = $key;
                }
                // Recreated primary keys
                phpAds_dbQuery("ALTER TABLE ".$name." DROP PRIMARY KEY");
                phpAds_dbQuery("ALTER TABLE ".$name." ADD PRIMARY KEY (".implode(",", $primary).")");
            }
        }
    }
    // Check Indexes
    if (isset($index) && is_array($index) && sizeof($index) > 0) {
        for (reset($index); $key = key($index); next($index)) {
            if (!isset($availableindex[$key]) || !is_array($availableindex[$key])) {
                // Index does not exist, so create it
                phpAds_dbQuery("ALTER TABLE ".$name." ADD INDEX ".$key." (".implode(",", $index[$key]).")");
            }
        }
    }
    // Check Unique Indexes
    if (isset($unique) && is_array($unique) && sizeof($unique) > 0) {
        for (reset($unique); $key = key($unique); next($unique)) {
            if (!isset($availableunique[$key]) || !is_array($availableunique[$key])) {
                // Index does not exist, so create it
                phpAds_dbQuery("ALTER TABLE ".$name." ADD UNIQUE ".$key." (".implode(",", $unique[$key]).")");
            }
        }
    }
    // Recreate 'auto_increment'
    if ($incrementmodified != false) {
        $createdefinition = $incrementmodified." ".$columns[$incrementmodified];
        phpAds_dbQuery("ALTER TABLE ".$name." MODIFY COLUMN ".$createdefinition);
    }
}

/*********************************************************/
/* Create a table                                        */
/*********************************************************/

function phpAds_createTable($name, $structure, $tabletype = '')
{
    $columns = $structure['columns'];
    if (isset($structure['primary'])) $primary = $structure['primary'];
    if (isset($structure['index']))   $index   = $structure['index'];
    if (isset($structure['unique']))  $unique  = $structure['unique'];
    // Create empty array
    $createdefinitions = array();
    // Add columns
    for (reset($columns); $key = key($columns); next($columns)) {
        $createdefinitions[] = $key." ".$columns[$key];
    }
    if (isset($primary) && is_array($primary)) {
        $createdefinitions[] = "PRIMARY KEY (".implode(",", $primary).")";
    }
    if (isset($index) && is_array($index)) {
        for (reset($index); $key = key($index); next($index)) {
            $createdefinitions[] = "KEY $key (".implode(",", $index[$key]).")";
        }
    }
    if (isset($unique) && is_array($unique)) {
        for (reset($unique); $key = key($unique); next($unique)) {
            $createdefinitions[] = "UNIQUE $key (".implode(",", $unique[$key]).")";
        }
    }
    if (is_array($createdefinitions) && sizeof($createdefinitions) > 0) {
        $query  = "CREATE TABLE $name (";
        $query .= implode (", ", $createdefinitions);
        $query .= ")";
        // Tabletype
        if ($tabletype != '') {
            $query .= " TYPE=".$tabletype;
        }
        phpAds_dbQuery($query);
    }
}

/*********************************************************/
/* Drop an existing table                                */
/*********************************************************/

function phpAds_dropTable($name)
{
    return phpAds_dbQuery("DROP TABLE ".$name);
}


/*********************************************************/
/* Get table types                                       */
/*********************************************************/

function phpAds_getTableTypes()
{
    $types['MYISAM'] = 'MyISAM';
    $types['BDB'] = 'Berkeley DB';
    $types['GEMINI'] = 'NuSphere Gemini';
    $types['INNODB'] = 'InnoDB';
    return $types;
}

function phpAds_checkTableType($type)
{
    // Assume MySQL always supports MyISAM table types
    if ($type == 'MYISAM') {
        return true;
    } else {
        $res = phpAds_dbQuery("SHOW VARIABLES");
        while ($row = phpAds_dbFetchRow($res)) {
            if ($type == 'BDB' && $row[0] == 'have_bdb' && $row[1] == 'YES') {
                return true;
            }
            if ($type == 'GEMINI' && $row[0] == 'have_gemini' && $row[1] == 'YES') {
                return true;
            }
            if ($type == 'INNODB' && $row[0] == 'have_innodb' && $row[1] == 'YES') {
                return true;
            }
        }
    }
    return false;
}

/*********************************************************/
/* Get the default table type                            */
/*********************************************************/

function phpAds_getTableTypeDefault()
{
    /*
    $res = phpAds_dbQuery("SHOW VARIABLES");
    while ($row = phpAds_dbFetchRow($res)) {
    if ($row[0] == 'table_type') {
    return $row[1];
    }
    }
    return false;
    */
    return 'MYISAM';
}

/*********************************************************/
/* Read the database structure from a sql file           */
/*********************************************************/

function phpAds_readDatabaseStructure()
{
    global $phpAds_config;
    $sql = join("", file(phpAds_path."/libraries/defaults/all.sql"));
    // Stripping comments
    $sql = ereg_replace("$-- [^\n]*\n", "\n", $sql);
    $sql = ereg_replace("$#[^\n]*\n", "\n", $sql);
    // Stripping (CR)LFs
    //$sql = str_replace("\r?\n\r?", "", $sql);
    $sql = str_replace("\n", " ", $sql);
    $sql = str_replace("\r", " ", $sql);
    // Unifying duplicate blanks
    $sql = ereg_replace("[[:blank:]]+", " ", $sql);
    $sql = explode(";", $sql);
    // Replacing table names to match config.inc.php
    for ($i = 0; $i < sizeof($sql); $i++) {
        if (ereg("CREATE TABLE (max_[^\(]*) \(", $sql[$i], $regs)) {
            $tablename = str_replace("max_", "tbl_", $regs[1]);
            if (isset($phpAds_config[$tablename])) {
                $sql[$i] = str_replace($regs[1], $phpAds_config[$tablename], $sql[$i]);
            }
        }
    }
    // Create an array with an element for each query
    return $sql;
}

/*********************************************************/
/* Parse the an sql file and return all queries          */
/*********************************************************/

function phpAds_prepareDatabaseStructure()
{
    $dbstructure = array();
    // Read the all.sql file
    $queries = phpAds_readDatabaseStructure();
    for ($i = 0; $i < sizeof($queries) - 1; $i++) {
        if (ereg("CREATE TABLE ([^\(]*) \((.*)\)", $queries[$i], $regs)) {
            $tablename   = $regs[1];
            $definitions = $regs[2];
            $definitions = explode(", ", $definitions);
            for ($j = 0; $j < sizeof($definitions); $j++) {
                $definition = trim($definitions[$j]);
                if (ereg("^PRIMARY KEY \((.*)\)$", $definition, $regs)) {
                    $items = explode(",", $regs[1]);
                    for ($k = 0; $k < sizeof($items); $k++) {
                        $dbstructure[$tablename]['primary'][] = $items[$k];
                    }
                } elseif (ereg("^(KEY|INDEX) ([^ ]*) \((.*)\)$", $definition, $regs)) {
                    $items = explode(",", $regs[3]);
                    for ($k = 0; $k < sizeof($items); $k++)
                    $dbstructure[$tablename]['index'][$regs[2]][] = $items[$k];
                } elseif (ereg("^UNIQUE ([^ ]*) \((.*)\)$", $definition, $regs)) {
                    $items = explode(",", $regs[2]);
                    for ($k = 0; $k < sizeof($items); $k++) {
                        $dbstructure[$tablename]['unique'][$regs[1]][] = $items[$k];
                    }
                } elseif (ereg("^([^ ]*) (.*)$", $definition, $regs)) {
                    $dbstructure[$tablename]['columns'][$regs[1]] = $regs[2];
                }
            }
        }
    }
    return $dbstructure;
}

/*********************************************************/
/* Version specific updates                              */
/*********************************************************/

function phpAds_upgradeDetectPluginVersion()
{
    global $phpAds_config;
    // Include swf library
    include 'lib-swf.inc.php';
    // Check if plugin detection is needed
    if (!isset($phpAds_config['config_version']) || $phpAds_config['config_version'] < 0.0) {
        $banners = array();
        // Fetch all banners
        $res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_banners']);
        while ($row = phpAds_dbFetchArray($res)) {
            $banners[] = $row;
        }
        for ($i = 0; $i < count($banners); $i++) {
            if ($banners[$i]['storagetype'] == 'sql' || $banners[$i]['storagetype'] == 'web') {
                $pluginversion = 0;
                $htmltemplate = $banners[$i]['htmltemplate'];
                if ($banners[$i]['contenttype'] == 'swf') {
                    // Determine version
                    $swf_file = phpAds_ImageRetrieve($banners[$i]['storagetype'], $banners[$i]['filename']);
                    $pluginversion = phpAds_SWFVersion($swf_file);
                    // Update template
                    $htmltemplate = ereg_replace("#version=[^\']*'", "#version={pluginversion:4,0,0,0}'", $htmltemplate);
                } elseif ($banners[$i]['contenttype'] == 'dcr') {
                    // Update template
                    $htmltemplate = ereg_replace("#version=[^\']*'", "#version={pluginversion:8,5,0,321}'", $htmltemplate);
                }
                $htmltemplate = addslashes($htmltemplate);
                // Update the banner
                $res = phpAds_dbQuery("    
                    UPDATE
                        ".$phpAds_config['tbl_banners']."
                    SET
                        pluginversion = '".$pluginversion."',
                        htmltemplate = '".$htmltemplate."'
                    WHERE
                        bannerid = ".$banners[$i]['bannerid']."
                    ");
            }
        }
    }
}

function phpAds_upgradeHTMLCache()
{
    global $phpAds_config;
    $res = phpAds_dbQuery("
        SELECT
            *
        FROM
            ".$phpAds_config['tbl_banners']."
        ");
    while ($current = phpAds_dbFetchArray($res)) {
        // Rebuild filename
        if ($current['storagetype'] == 'sql') {
            $current['imageurl'] = '';
        }
        if ($current['storagetype'] == 'web') {
            $current['imageurl'] = '';
        }
        // Add slashes to status to prevent javascript errors
        // NOTE: not needed in banner-edit because of magic_quotes_gpc
        $current['status'] = addslashes($current['status']);
        // Rebuild cache
        $current['htmltemplate'] = stripslashes($current['htmltemplate']);
        $current['htmlcache']    = addslashes(phpAds_getBannerCache($current));
        phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_banners']."
			SET
				htmlcache = '".$current['htmlcache']."',
				imageurl  = '".$current['imageurl']."'
			WHERE
				bannerid = ".$current['bannerid']."
		");
    }
}

function phpAds_upgradeAppendZones()
{
    global $phpAds_config;
    // Check if md5 adding is needed
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery("
				SELECT
					zoneid,
					append
				FROM
					".$phpAds_config['tbl_zones']."
				WHERE
					appendtype = ".phpAds_ZoneAppendRaw."
			");
        while ($row = phpAds_dbFetchArray($res)) {
            $append = phpAds_ZoneParseAppendCode($row['append']);
            if ($append[0]['zoneid']) {
                phpAds_dbQuery("
						UPDATE
							".$phpAds_config['tbl_zones']."
						SET
							appendtype = ".phpAds_ZoneAppendZone."
						WHERE
							zoneid = '".$row['zoneid']."'
					");
            }
        }
    }
}

function phpAds_upgradeDisplayLimitations()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery("
				SELECT
					*
				FROM
					".$phpAds_config['tbl_acls']."
		");
        while ($row = phpAds_dbFetchArray($res)) {
            $data['logical'] 		= $row['acl_con'];
            $data['type']	 		= $row['acl_type'];
            $data['executionorder'] = $row['acl_order'];
            $data['data']			= addslashes($row['acl_data']);
            $data['comparison']		= $row['acl_ad'] == 'allow' ? '==' : '!=';
            phpAds_dbQuery("
				UPDATE
					".$phpAds_config['tbl_acls']."
				SET
					logical 		= '".$row['acl_con']."',
					type	 		= '".$row['acl_type']."',
					executionorder  = '".$row['acl_order']."',
					data			= '".addslashes($row['acl_data'])."',
					comparison		= '".($row['acl_ad'] == 'allow' ? '==' : '!=')."'
				WHERE
					bannerid = '".$row['bannerid']."' AND
					acl_order = '".$row['acl_order']."'
			");
        }
        // Delete old columns
        phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_acls']." DROP COLUMN acl_con");
        phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_acls']." DROP COLUMN acl_type");
        phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_acls']." DROP COLUMN acl_data");
        phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_acls']." DROP COLUMN acl_ad");
        phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_acls']." DROP COLUMN acl_order");
    }
}

function phpAds_upgradeSplitClientTableBanners()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery(
        "DESCRIBE ".$phpAds_config['tbl_banners']." clientid"
        );
        if (phpAds_dbNumRows($res) > 0) {
            $res = phpAds_dbQuery(
            "SELECT bannerid, clientid".
            " FROM ".$phpAds_config['tbl_banners'].
            " WHERE clientid > 0"
            );
            while ($row = phpAds_dbFetchArray($res)) {
                phpAds_dbQuery(
                "UPDATE ".$phpAds_config['tbl_banners'].
                " SET campaignid=".$row['clientid'].
                " WHERE bannerid=".$row['bannerid']
                );
            }
            // Delete old columns
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_banners']." DROP COLUMN clientid");
        }
    }
}

function phpAds_upgradeSplitClientTableTargetStats()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery(
        "DESCRIBE ".$phpAds_config['tbl_targetstats']." clientid"
        );
        if (phpAds_dbNumRows($res) > 0) {
            $res = phpAds_dbQuery(
            "SELECT day, clientid, target, views, modified".
            " FROM ".$phpAds_config['tbl_targetstats'].
            " WHERE clientid > 0"
            );
            while ($row = phpAds_dbFetchArray($res))
            {
                phpAds_dbQuery(
                "UPDATE ".$phpAds_config['tbl_targetstats'].
                " SET campaignid=".$row['clientid'].
                " WHERE day='".$row['day']."'"."'".
                " AND target='".$row['target']."'".
                " AND views='".$row['views']."'".
                " AND modified='".$row['modified']."'"
                );
            }
            // Delete old columns
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_targetstats']." DROP COLUMN clientid");
        }
    }
}

function phpAds_upgradeSplitClientTableZone()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery(
        "SELECT zoneid,what".
        " FROM ".$phpAds_config['tbl_zones']
        );
        while ($row = phpAds_dbFetchArray($res)) {
            $what = $row['what'];
            if (preg_match('#clientid:#i', $what) ) {
                $changed = false;
                $what_array = explode(",", $what);
                for ($k = 0; $k < count($what_array); $k++) {
                    if (substr($what_array[$k],0,9) == "clientid:") {
                        $id = substr($what_array[$k],9);
                        $res2 = phpAds_dbQuery(
                        "SELECT campaignid".
                        " FROM ".$phpAds_config['tbl_campaigns'].
                        " WHERE campaignid=".$id
                        );
                        // This is a campaign and not an advertiser
                        if (phpAds_dbNumRows($res2) > 0) {
                            $what_array[$k] = "campaignid:".$id;
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    $what = implode(",",$what_array);
                    phpAds_dbQuery(
                    "UPDATE ".$phpAds_config['tbl_zones'].
                    " SET what='".$what."'".
                    " WHERE zoneid=".$row['zoneid']
                    );
                }
            }
        }
    }
}

function phpAds_upgradeSplitClientTable()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) || $phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery(
        "DESCRIBE ".$phpAds_config['tbl_clients']
        );
        if (phpAds_dbNumRows($res) > 0) {
            $res = phpAds_dbQuery(
            "SELECT * FROM ".$phpAds_config['tbl_clients'].
            " WHERE parent > 0"
            );
            if (($row['target'] > 0) && ($row['weight'] == 0) ) {
                $row['priority'] = 'h';
            } else {
                $row['priority'] = 'l';
            }
            $row['optimise'] = 'f';
            $row['anonymous'] = 'f';
            while ($row = phpAds_dbFetchArray($res)) {
                phpAds_dbQuery(
                "INSERT INTO ".$phpAds_config['tbl_campaigns'].
                " (campaignid".
                ",campaignname".
                ",views".
                ",clicks".
                ",conversions".
                ",expire".
                ",activate".
                ",active".
                ",weight".
                ",target".
                ",clientid".
                ",priority".
                ",optimise".
                ",anonymous)".
                " VALUES ".
                "('".$row['clientid']."'".
                ",'".$row['clientname']."'".
                ",'".$row['views']."'".
                ",'".$row['clicks']."'".
                ",'".$row['conversions']."'".
                ",'".$row['expire']."'".
                ",'".$row['activate']."'".
                ",'".$row['active']."'".
                ",'".$row['weight']."'".
                ",'".$row['target']."'".
                ",'".$row['parent']."'".
                ",'".$row['priority']."'".
                ",'".$row['optimise']."'".
                ",'".$row['anonymous']."')"
                ) or phpAds_sqlDie();
            }
            // Delete rows moved into campaign table
            phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_clients']." WHERE parent > 0");
            // Delete old columns
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN views");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN clicks");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN expire");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN activate");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN active");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN weight");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN target");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN parent");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN conversions");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN priority");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN optimise");
            phpAds_dbQuery("ALTER TABLE ".$phpAds_config['tbl_clients']." DROP COLUMN anonymous");
        }
    }
}

function phpAds_upgradeHourlyStats()
{
    global $phpAds_config;
    phpAds_dbQuery(
    "UPGRADE ".$phpAds_config['tbl_config'].
    " SET statslasthour=HOUR(NOW())".
    ",statslastday=NOW()"
    );
}

function phpAds_upgradeTargetStats()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) || $phpAds_config['config_version'] < 0.0) {
        $res = phpAds_dbQuery("
            SELECT
                timestamp,
                details
            FROM
                ".$phpAds_config['tbl_userlog']."
            WHERE
                action = 11
            ORDER BY
                timestamp
			");
        while ($row = phpAds_dbFetchArray($res)) {
            while (ereg('\[id([0-9]+)\]: ([0-9]+)', $row['details'], $match)) {
                $day = date('Y-m-d', $row['timestamp']);
                if (!isset($start)) {
                    $start = $row['timestamp'];
                }
                $autotargets[$day][$match[1]]['target'] = $match[2];
                $row['details'] = str_replace($match[0], '', $row['details']);
            }
        }
        if (!isset($start)) {
            // No autotargeting logs, exit
            return;
        }
        $t_stamp = mktime(0, 0, 0, date('m', $start), date('d', $start), date('Y', $start));
        $t_stamp_now = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        while ($t_stamp < $t_stamp_now) {
            $day    = date('Ymd', $t_stamp);
            $begin  = $day.'000000';
            $end    = $day.'235959';
            $campaigns = array();
            if (isset($autotargets[$day])) {
                while (list($campaignid, ) = each($autotargets[$day])) {
                    $campaigns[] = $campaignid;
                    // We need to figure out how to upgrade to batch statistics...
                    if ($phpAds_config['compact_stats']) {
                        $res_views = phpAds_dbQuery("
                            SELECT
								SUM(views) AS sum_views
							FROM
								".$phpAds_config['tbl_adstats']." AS v,
								".$phpAds_config['tbl_banners']." AS b
							WHERE
								v.day = ".$day." AND
								b.bannerid = v.bannerid AND
								b.clientid = ".$campaignid."
							");
                    } else {
                        $res_views = phpAds_dbQuery("
							SELECT
								COUNT(*) AS sum_views
							FROM
								".$phpAds_config['tbl_adviews']." AS v,
								".$phpAds_config['tbl_banners']." AS b
							WHERE
								v.t_stamp >= ".$begin." AND
								v.t_stamp <= ".$end." AND
								b.bannerid = v.bannerid AND
								b.campaignid = ".$campaignid."
							");
                    }
                    if ($views = phpAds_dbResult($res_views, 0, 0)) {
                        $autotargets[$day][$campaignid]['views'] = $views;
                    }
                }
            }
            if (count($campaigns)) {
                // We need to figure out how to upgrade to batch statistics...
                if ($phpAds_config['compact_stats']) {
                    $res_views = phpAds_dbQuery("
						SELECT
							SUM(views) AS sum_views
						FROM
							".$phpAds_config['tbl_adstats']." AS v,
							".$phpAds_config['tbl_banners']." AS b
						WHERE
							v.day = ".$day." AND
							b.bannerid = v.bannerid AND
							b.campaignid NOT IN (".join(', ', $campaigns).")
						");
                } else {
                    $res_views = phpAds_dbQuery("
						SELECT
							COUNT(*) AS sum_views
						FROM
							".$phpAds_config['tbl_adviews']." AS v,
							".$phpAds_config['tbl_banners']." AS b
						WHERE
							v.t_stamp >= ".$begin." AND
							v.t_stamp <= ".$end." AND
							b.bannerid = v.bannerid AND
							b.campaignid NOT IN (".join(', ', $campaigns).")
						");
                }
            } else {
                // We need to figure out how to upgrade to batch statistics
                if ($phpAds_config['compact_stats']) {
                    $res_views = phpAds_dbQuery("
						SELECT
							SUM(views) AS sum_views
						FROM
							".$phpAds_config['tbl_adstats']." AS v
						WHERE
							v.day = ".$day."
						");
                } else {
                    $res_views = phpAds_dbQuery("
						SELECT
							COUNT(*) AS sum_views
						FROM
							".$phpAds_config['tbl_adviews']." AS v
						WHERE
							v.t_stamp >= ".$begin." AND
							v.t_stamp <= ".$end."
						");
                }
            }
            $views = phpAds_dbResult($res_views, 0, 0);
            $autotargets[$day][0]['views'] = $views ? $views : 0;
            $t_stamp = phpAds_makeTimestamp($t_stamp, 60*60*24);
        }
        for (reset($autotargets); $day = key($autotargets); next($autotargets)) {
            reset($autotargets[$day]);
            while (list($campaignid, $value) = each($autotargets[$day])) {
                phpAds_dbQuery("
					INSERT INTO
						".$phpAds_config['tbl_targetstats']." (
							day,
							campaignid,
							target,
							views
						) VALUES (
							'".$day."',
							".$campaignid.",
							".(isset($value['target']) ? (int)$value['target'] : 0).",
							".(isset($value['views']) ? (int)$value['views'] : 0)."
						)
					");
            }
        }
    }
}

function phpAds_upgradePasswordMD5()
{
    global $phpAds_config;
    if (!isset($phpAds_config['config_version']) ||	$phpAds_config['config_version'] < 0.0) {
        // Update the advertisers
        $res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_clients']."
			SET
				clientpassword = MD5(clientpassword)
			WHERE
				clientpassword != ''
		");
        // Update the publisher
        $res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_affiliates']."
			SET
				password = MD5(password)
			WHERE
				password != ''
		");
        // Update the administrator
        $res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_config']."
			SET
				admin_pw = MD5(admin_pw)
		");
    }
}

?>