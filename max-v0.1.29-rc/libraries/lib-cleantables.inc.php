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
$Id: lib-cleantables.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBCLEANTABLES_INCLUDED', true);



/*********************************************************/
/* Clean stats and userlog entries                       */
/*********************************************************/

function phpAds_cleanTables($weeks, $stats)
{
	global $phpAds_config;
	
	$report = '';
	
	
	// Determine tables
	if ($stats)
		$tables = array(
			$phpAds_config['tbl_adstats'] => array('day', 'Ymd'),
			$phpAds_config['tbl_adviews'] => array('t_stamp', 'YmdHis'),
			$phpAds_config['tbl_adclicks'] => array('t_stamp', 'YmdHis'),
			$phpAds_config['tbl_adconversions'] => array('t_stamp', 'YmdHis')
		);
	else
		$tables = array(
			$phpAds_config['tbl_userlog'] => array('timestamp', '')
		);
	
	
	$t_stamp = phpAds_makeTimestamp(mktime (0, 0, 0, date('m'),
		date('d'), date('Y')), (-7 * $weeks + 1) * 60*60*24);
	
	while (list($k, $v) = each($tables))
	{
		if (!$v[1])
			$begin = $t_stamp;
		else
			$begin = date($v[1], $t_stamp);
		
		phpAds_dbQuery("
			DELETE FROM
				".$k."
			WHERE
				".$v[0]." < ".$begin."
		");
	
		$report .= 'Table '.$k.': deleted '.phpAds_dbAffectedRows().' rows'."\n";
	}
	
	return $report;
}

?>