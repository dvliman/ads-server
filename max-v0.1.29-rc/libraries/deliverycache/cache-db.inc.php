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
$Id: cache-db.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBVIEWCACHE_INCLUDED', true);

// Open a connection to the database for subsequent cache requests
phpAds_dbConnect();

function phpAds_cacheFetch ($name)
{
	global $phpAds_config;
	
	$cacheres = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_cache']." WHERE cacheid='".$name."'");
	
	if ($cacherow = phpAds_dbFetchArray($cacheres))
		return (unserialize($cacherow['content']));
	else
		return false;
}

function phpAds_cacheStore ($name, $cache)
{
	global $phpAds_config;
	
	$result = phpAds_dbQuery("UPDATE ".$phpAds_config['tbl_cache']." SET content='".addslashes(serialize($cache))."' WHERE cacheid='".$name."'");
	
    if (phpAds_dbAffectedRows() == 0) 
    	$result = phpAds_dbQuery("INSERT INTO ".$phpAds_config['tbl_cache']." SET cacheid='".$name."', content='".addslashes(serialize($cache))."'");
}

function phpAds_cacheDelete ($name='')
{
	global $phpAds_config;
	
	if ($name == '')
		$result = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_cache']);
	else
		$result = phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_cache']." WHERE cacheid='".$name."'");
}


function phpAds_cacheInfo ()
{
	global $phpAds_config;
	
	$result = array();
	
	$cacheres = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_cache']);
	
	while ($cacherow = phpAds_dbFetchArray($cacheres))
		$result[$cacherow['cacheid']] = strlen ($cacherow['content']);
	
	return ($result);
}

?>