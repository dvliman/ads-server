<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
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
*/

/**
 * File to perform upgrades from phpAdsNew 2.0 or greater, and from previous
 * version of Max.
 */

class Upgrade
{
    var $prefix;
    var $dbConnection;
    var $tables;
    var $tableType;
    var $upgradeFrom;
    var $upgradeTo;
    
    /**
     * The class constructor method.
     *
     * @param string $prefix The table prefix to use for database tables.
     * @param mixed $dbConnection A reference to an existing MySQL connection
     *                            to the admin database.
     */
    function Upgrade($prefix, $dbConnection)
    {
        // Set time limit and ignore user abort, as upgrade can take some itme
        if (!ini_get('safe_mode')) {
            @set_time_limit(1800);
            @ignore_user_abort(true);
        }
        $this->prefix = $prefix;
        $this->dbConnection = $dbConnection;
        $this->tables = phpAds_prepareDatabaseStructure();
        global $phpAds_config;
        $this->tableType = $phpAds_config['table_type'];
    }
    
    /**
     * A method to determine if an older version of Max (or phpAdsNew)
     * is currently installed.
     *
     * @param string $version The version of Max currently being installed.
     * @return boolean True if a previous version of Max is installed, 
     *                 false otherwise.
     */
    function previousVersionExists($version)
    {
        $this->upgradeTo = $version;
        // Does the application variables table exists?
        $query = 'DESCRIBE ' . $this->prefix . 'application_variable';
        $result = mysql_query($query, $this->dbConnection);
        if (!$result) {
            // Could not find the application variables table,
            // so definately need to upgrade
            return true;
        } else {
            // What is the current version of Max that is installed?
            $query = '
                SELECT
                    value AS max_version
                FROM
                    ' . $this->prefix . 'application_variable
                WHERE
                    name = \'max_version\'';
            $result = mysql_query($query, $this->dbConnection);
            if ($result) {
                $row = mysql_fetch_array($result);
                $this->upgradeFrom = $row['max_version'];
                return $this->_compareVersions($this->upgradeTo, $this->upgradeFrom);
            }
        }
        // Versions were the same, or less, or there was an error.
        // Don't upgrade.
        return false;
    }
    
    /**
     * A method to perform the necessary upgrade steps to update Max
     * to the necessary database format.
     *
     * @param array $errors A reference to an array to have errors added to it.
     */
    function upgradeDatabase(&$errors)
    {
        $errors = array();
        // Is the upgrageFrom variable defined?
        if (isset($this->upgradeFrom)) {
            // We are upgrading from Max after v0.1.16-beta,
            // so just do the required upgrade actions
            if ($this->_compareVersions('v0.1.21-rc', $this->upgradeFrom)) {
                // Upgrade to v0.1.21-rc
                $this->_upgradeToOneTwentyOneRC($errors);
            }
        } else {
            // Perfom *all* possible upgrade actions, in order
            $this->_upgradeEarly($errors);            // Upgrade to v0.1.16-beta
            $this->_upgradeToOneTwentyOneRC($errors); // Upgrade to v0.1.21-rc
        }
        if (count($errors) == 0) {
            // Always upgrade the installed version number
            $this->_upgradeInstalledVersion();
        }
    }
    
    /**
     * A private method for comparing version numbers.
     *
     * @private
     * @param string $first The first version number.
     * @param string $second The second version number.
     * @return boolean True if the first version number is greater than the second,
     *                 false otherwise.
     */
    function _compareVersions($first, $second)
    {
        if ((!isset($first)) || (!isset($second))) {
            return false;
        }
        // Obtain the parts of the verison numbers
        if (preg_match('/(\d+)\.(\d+)\.(\d+)(?:-([a-z]+))?/', $first, $matches)) {
            $firstMajor = $matches[1];
            $firstMinor = $matches[2];
            $firstPatch = $matches[3];
            $firstType  = $matches[4];
        }
        if (preg_match('/(\d+)\.(\d+)\.(\d+)(?:-([a-z]+))?/', $second, $matches)) {
            $secondMajor = $matches[1];
            $secondMinor = $matches[2];
            $secondPatch = $matches[3];
            $secondType  = $matches[4];
        }
        // Compare the major versions
        if (isset($firstMajor) && isset($secondMajor) && ($firstMajor > $secondMajor)) {
            return true;
        }
        // Compare the minor versions
        if (isset($firstMajor) && isset($secondMajor) && ($firstMajor == $secondMajor)) {
            if (isset($firstMinor) && isset($secondMinor) && ($firstMinor > $secondMinor)) {
                return true;
            }
        }
        // Compare the patch levels
        if (isset($firstMajor) && isset($secondMajor) && ($firstMajor == $secondMajor)) {
            if (isset($firstMinor) && isset($secondMinor) && ($firstMinor == $secondMinor)) {
                if (isset($firstPatch) && isset($secondPatch) && ($firstPatch > $secondPatch)) {
                    return true;
                }
            }
        }
        // Compare the release types
        if (isset($firstMajor) && isset($secondMajor) && ($firstMajor == $secondMajor)) {
            if (isset($firstMinor) && isset($secondMinor) && ($firstMinor == $secondMinor)) {
                if (isset($firstPatch) && isset($secondPatch) && ($firstPatch == $secondPatch)) {
                    if (isset($firstType) && isset($secondType)) {
                        if (($secondType == 'alpha') && ($firstType != 'alpha')) {
                            return true;
                        } else if (($secondType == 'beta') && (($firstType != 'alpha') || ($firstType != 'beta'))) {
                            return true;
                        } else if (($secondType == 'rc') && ($firstType == 'stable')) {
                            return true;
                        }
                    }
                }
            }
        }
        // Version was the same, less
        return false;
    }
    
    /**
     * A private method for updating the version number
     *
     * @private
     * @param array $errors A reference to an array to have errors added to it.
     */
    function _upgradeInstalledVersion(&$errors)
    {
        $query = 'UPDATE ' . $this->prefix . 'application_variable SET value = \'' . $this->upgradeTo . ' \' WHERE name = \'max_version\'';
        $result = mysql_query($query, $this->dbConnection);
        if (!$result) {
            $errors[] = "Error executing upgrade code: $query";
        }
    }
    
    /**
     * A private method to upgrade the database from the v0.1.16-beta
     * format to the v0.1.21-rc format.
     *
     * @private
     */
    function _upgradeToOneTwentyOneRC(&$errors)
    {
        // Fix the config table's gui_link_compact_limit field
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_link_compact_limit gui_link_compact_limit INTEGER DEFAULT \'50\'';
        $this->_runQueries($queries, $errors);        
    }
    
    /**
     * A private method to upgrade the database for all versions
     * prior to v0.1.16-beta. Note that in some cases, a check is
     * performed to test is the chages have already been made, as
     * the changes cannot be applied twice. In other cases, no check
     * is made, as the changes can simply be ignored by MySQL if
     * they are applied for a second time.
     *
     * @private
     * @param array $errors A reference to an array to have errors added to it.
     */
    function _upgradeEarly(&$errors)
    {
        // acls table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'acls CHANGE logical logical set(\'and\',\'or\') NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'acls CHANGE type type varchar(16) NOT NULL default \'\'';
        $this->_runQueries($queries, $errors);
        
        // adclicks table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks DROP INDEX bannerid_date';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks ADD userid varchar(32) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks CHANGE t_stamp t_stamp timestamp(14) NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks CHANGE host host varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks CHANGE source source varchar(50) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks CHANGE country country char(2) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adclicks ADD INDEX bannerid (bannerid)';
        $this->_runQueries($queries, $errors);
        
        // adconversions table
        $key = $this->prefix . 'adconversions';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // adstats table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats DROP PRIMARY KEY';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats DROP KEY bannerid_day';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats ADD conversions int(11) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats CHANGE bannerid bannerid mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats CHANGE zoneid zoneid mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats ADD KEY day (day)';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adstats ADD KEY bannerid (bannerid)';
        $this->_runQueries($queries, $errors);
        
        // adviews table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews DROP INDEX bannerid_date';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews ADD userid varchar(32) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews CHANGE t_stamp t_stamp timestamp(14) NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews CHANGE host host varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews CHANGE source source varchar(50) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews CHANGE country country char(2) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'adviews ADD INDEX bannerid (bannerid)';
        $this->_runQueries($queries, $errors);

        // affiliates table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates ADD agencyid mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates ADD mnemonic varchar(5) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE name name varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE contact contact varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE email email varchar(64) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE website website varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE username username varchar(64) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE password password varchar(64) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE permissions permissions mediumint(9) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'affiliates CHANGE language language varchar(64) default NULL';
        $this->_runQueries($queries, $errors);
        
        // agency table
        $key = $this->prefix . 'agency';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // application_variable table
        $key = $this->prefix . 'application_variable';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        $query = '
            INSERT INTO
                ' . $this->prefix . 'application_variable
                (name, value)
            VALUES
                (\'max_version\', \'' . $this->upgradeTo . '\')';
        $result = mysql_query($query, $this->dbConnection);
        if (!$result) {
            $errors[] = "Error executing upgrade code: $query";
        }
        
        // banners table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE clientid campaignid mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE filename filename varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE imageurl imageurl varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE htmltemplate htmltemplate text NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE htmlcache htmlcache text NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE target target varchar(16) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE url url text NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE alt alt varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE status status varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE keyword keyword varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE bannertext bannertext text NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE description description varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD adserver varchar(50) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE compiledlimitation compiledlimitation text NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners CHANGE append append text NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD alt_filename varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD alt_imageurl varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD alt_contenttype enum(\'gif\',\'jpeg\',\'png\') NOT NULL default \'gif\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD KEY campaignid (campaignid)';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD capping int(11) NOT NULL default \'0\' AFTER block';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'banners ADD session_capping int(11) NOT NULL default \'0\' AFTER capping';
        $this->_runQueries($queries, $errors);
        
        // cache table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'cache CHANGE content content blob NOT NULL';
        $this->_runQueries($queries, $errors);
        
        // campaigns table
        $key = $this->prefix . 'campaigns';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        $query = '
            INSERT INTO
                ' . $this->prefix . 'campaigns
            SELECT
                clientid AS campaignid,
                clientname AS campaignname,
                parent AS clientid,
                views AS views,
                clicks AS clicks,
                \'-1\' AS conversions,
                expire AS expire,
                activate AS activate,
                active AS active,
                \'h\' AS priority,
                weight AS weight,
                target AS target,
                \'f\' AS optimise,
                \'f\' AS anonymous
            FROM
                ' . $this->prefix . 'clients
            WHERE
                parent > 0
        ';
        $result = mysql_query($query, $this->dbConnection);
        if (!$result) {
            // There was an error copying the data from the old phpAdsNew
            // clients table into the new Max campaigns table. If this was
            // because the parent column no longer exists in the client
            // table, this is okay, as we are obviously upgrading from
            // an early version of Max, and this has already been done.
            // If not, this is an error.
            if (mysql_errno($this->dbConnection) != 1054) {
                $errors[] = "Error executing upgrade code: $query";
            }
        } else {
            // The copy of data worked, so now update the data in the
            // campaigns table
            $query = 'UPDATE ' . $this->prefix . 'campaigns SET priority=\'l\' WHERE weight > 0 AND views = -1 AND clicks = -1 and conversions = -1';
            $result = mysql_query($query, $this->dbConnection);
            if (!$result) {
                $errors[] = "Error executing upgrade code: $query";
            }
        }
        
        // campaigns_trackers table
        $key = $this->prefix . 'campaigns_trackers';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // clients table
        $queries = array();
        $queries[] = 'DELETE FROM ' . $this->prefix . 'clients WHERE parent > 0';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients ADD agencyid mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE clientname clientname varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE contact contact varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE email email varchar(64) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE clientusername clientusername varchar(64) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE clientpassword clientpassword varchar(64) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE permissions permissions mediumint(9) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients CHANGE language language varchar(64) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients DROP active';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients DROP weight';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients DROP target';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'clients DROP parent';
        $this->_runQueries($queries, $errors);
        
        // config table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config DROP PRIMARY KEY';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE configid agencyid mediumint(9) DEFAULT \'0\' NOT NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE my_header my_header varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE my_footer my_footer varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE language language varchar(32) default \'english\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE name name varchar(32) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE override_gd_imageformat override_gd_imageformat varchar(4) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE begin_of_week begin_of_week tinyint(2) default \'1\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE percentage_decimals percentage_decimals tinyint(2) default \'2\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_sql_allow type_sql_allow enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_url_allow type_url_allow enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_web_allow type_web_allow enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_html_allow type_html_allow enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_txt_allow type_txt_allow enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_web_mode type_web_mode tinyint(2) default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_web_dir type_web_dir varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_web_ftp type_web_ftp varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE type_web_url type_web_url varchar(255) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin admin varchar(64) default \'phpadsuser\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin_pw admin_pw varchar(64) default \'phpadspass\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin_fullname admin_fullname varchar(255) default \'Your Name\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin_email admin_email varchar(64) default \'your@email.com\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD warn_admin enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD warn_agency enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD warn_client enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD warn_limit mediumint(9) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin_email_headers admin_email_headers varchar(64) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE admin_novice admin_novice enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE default_banner_weight default_banner_weight tinyint(4) default \'1\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE default_campaign_weight default_campaign_weight tinyint(4) default \'1\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE client_welcome client_welcome enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE client_welcome_msg client_welcome_msg text';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE content_gzip_compression content_gzip_compression enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE userlog_email userlog_email enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE userlog_priority userlog_priority enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE userlog_autoclean userlog_autoclean enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_campaign_info gui_show_campaign_info enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_campaign_preview gui_show_campaign_preview enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_banner_info gui_show_banner_info enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_banner_preview gui_show_banner_preview enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_banner_html gui_show_banner_html enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_matching gui_show_matching enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_show_parents gui_show_parents enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_hide_inactive gui_hide_inactive enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE gui_link_compact_limit gui_link_compact_limit tinyint(2) default \'50\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE qmail_patch qmail_patch enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE updates_frequency updates_frequency tinyint(2) default \'7\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE updates_timestamp updates_timestamp int(11) default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE updates_last_seen updates_last_seen decimal(7,3) default \'0.000\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_plain allow_invocation_plain enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD allow_invocation_plain_nocookies enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_js allow_invocation_js enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_frame allow_invocation_frame enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_xmlrpc allow_invocation_xmlrpc enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_local allow_invocation_local enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_interstitial allow_invocation_interstitial enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE allow_invocation_popup allow_invocation_popup enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE auto_clean_tables auto_clean_tables enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE auto_clean_tables_interval auto_clean_tables_interval tinyint(2) default \'5\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE auto_clean_userlog auto_clean_userlog enum(\'t\',\'f\') default \'f\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE auto_clean_userlog_interval auto_clean_userlog_interval tinyint(2) default \'5\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE auto_clean_tables_vacuum auto_clean_tables_vacuum enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE autotarget_factor autotarget_factor float default \'-1\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config CHANGE maintenance_timestamp maintenance_timestamp int(11) default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD compact_stats enum(\'t\',\'f\') default \'t\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD statslastday date NOT NULL default \'0000-00-00\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD statslasthour tinyint(4) NOT NULL default \'0\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'config ADD PRIMARY KEY (agencyid)';
        $this->_runQueries($queries, $errors);
        
        // conversionlog table
        $key = $this->prefix . 'conversionlog';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // images table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'images CHANGE filename filename varchar(128) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'images CHANGE t_stamp t_stamp timestamp(14) NOT NULL';
        $this->_runQueries($queries, $errors);
        
        // log_maintenance table
        $key = $this->prefix . 'log_maintenance';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);

        // session table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'session CHANGE sessionid sessionid varchar(32) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'session CHANGE lastused lastused timestamp(14) NOT NULL';
        $this->_runQueries($queries, $errors);

        // targetstats table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'targetstats DROP PRIMARY KEY';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'targetstats CHANGE clientid campaignid mediumint(9) NOT NULL default \'0\'';
        $this->_runQueries($queries, $errors);
        
        // trackers table
        $key = $this->prefix . 'trackers';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // userlog table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'userlog CHANGE details details text';
        $this->_runQueries($queries, $errors);
        
        // variables table
        $key = $this->prefix . 'variables';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // variablevalues table
        $key = $this->prefix . 'variablevalues';
        phpAds_createTable($key, $this->tables[$key], $this->tableType);
        
        // zones table
        $queries = array();
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'zones CHANGE affiliateid affiliateid mediumint(9) default NULL';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'zones CHANGE zonename zonename varchar(245) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'zones CHANGE description description varchar(255) NOT NULL default \'\'';
        $queries[] = 'ALTER TABLE ' . $this->prefix . 'zones ADD COLUMN forceappend enum (\'t\', \'f\') DEFAULT \'f\'';
        $this->_runQueries($queries, $errors);
        // Fix the 'what' column which links campaigns (used to be clients)
        $query = 'SELECT * FROM ' . $this->prefix . 'zones';
        $result = mysql_query($query, $this->dbConnection);
        if (!$result) {
            $errors[] = "Error executing upgrade code: $query";
        } else {
            while ($row = mysql_fetch_array($result)) {
                $newWhat = preg_replace('/clientid:/', 'campaignid:', $row['what']);
                if ($newWhat!= $row['what']) {
                    $query = 'UPDATE ' . $this->prefix . 'zones SET what = \'' . $newWhat . '\' WHERE ';
                    foreach ($row as $key => $value) {
                        if (preg_match('/\d+/', $key)) {
                            continue;
                        } else if ($key == 'what') {
                            continue;
                        } else if ($value != '') {
                            $query .= "$key = '$value' AND ";
                        }
                    }
                    $query = preg_replace('/ AND $/', '', $query);
                    if (!preg_match('/WHERE $/', $query)) {
                        $innerResult = mysql_query($query, $this->dbConnection);
                        if (!$innerResult) {
                            $errors[] = "Error executing upgrade code: $query";
                        }
                    }
                }
            }
        }
    }
    
    /**
     * A private method for running a series of queries. Checks for various
     * error conditions, and may ignore errors, depending on the query.
     *
     * @private
     * @param array $queries A reference to an array of SQL queries to run.
     * @param array $errors A reference to an array that should contain any errors 
     *                      resulting from running the queries, excluding certain
     *                      cases.
     */
    function _runQueries(&$queries, &$errors)
    {
        foreach ($queries as $query) {
            $result = mysql_query($query, $this->dbConnection);
            if (!$result) {
                $error = mysql_errno($this->dbConnection);
                if (preg_match('/^ALTER\s+TABLE\s+\w+\s+ADD\s+(?!INDEX)(?!KEY)(?!PRIMARY\s+KEY)/', $query)) {
                    // May be error 1060, caused by trying to re-add a column
                    if ($error == 1060) {
                        continue;
                    }
                } else if (preg_match('/^ALTER\s+TABLE\s+\w+\s+ADD\s+(INDEX|KEY)/', $query)) {
                    // May be error 1061, caused by trying to re-add an index or key
                    if ($error == 1061) {
                        continue;
                    }
                } else if (preg_match('/^ALTER\s+TABLE\s+\w+\s+ADD\s+PRIMARY\s+KEY/', $query)) {
                    // May be error 1068, caused by trying to re-add a primary key
                    if ($error == 1068) {
                        continue;
                    }
                } else if (preg_match('/^ALTER\s+TABLE\s+\w+\s+CHANGE/', $query)) {
                    // May be error 1054, caused by trying to change a non-existant column
                    if ($error == 1054) {
                        continue;
                    }
                } else if (preg_match('/^ALTER\s+TABLE\s+\w+\s+DROP(?!PRIMARY\s+KEY)/', $query)) {
                    // May be error 1091, caused by trying to drop a non-existant index, key, or column
                    if ($error == 1091) {
                        continue;
                    }
                } else if (preg_match('/^DELETE\s+FROM/', $query)) {
                    // May be error 1054, caused by tring to delete where a column has already been dropped
                    if ($error == 1054) {
                        continue;
                    }
                }
                $errors[] = "Error executing upgrade code: $query";
            }
        }
    }
    
}

?>