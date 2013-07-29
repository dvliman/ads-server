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
$Id: adcnvvars.php 3145 2005-05-20 13:15:01Z andrew $
*/

    // Figure out our location
    define ('phpAds_path', '.');

    // Include required files
    include_once phpAds_path . '/config.inc.php';
    include_once phpAds_path . '/libraries/lib-log.inc.php';
    include_once phpAds_path . '/libraries/lib-io.inc.php';
    include_once phpAds_path . '/libraries/lib-db.inc.php';
    include_once phpAds_path . '/libraries/db.php';
    
    global $phpAds_config;
    
    // Register input variables
    $localConversionId = !empty($_GET['local_conversionid']) ? $_GET['local_conversionid'] : '';
    $dbServerIp =        !empty($_GET['dbserver_ip']) ? $_GET['dbserver_ip'] : '';
    $trackerId =         !empty($_GET['trackerid']) ? $_GET['trackerid'] : 0;
    
    $first = true;
    $variables = MAX_getCacheVariablesByTrackerId($trackerId);
    foreach ($variables as $variable) {
        if ($variable['variabletype'] == 'js') {
            if ($first) {
                $first = false;
                phpAds_dbConnect(phpAds_rawDb);
            }
            
            $variableId = $variable['variableid'];
            $variableValue = isset($_GET[$variable['name']]) ? $_GET[$variable['name']] : null;
            
            if ($variableId > 0) {
                phpAds_logVariableValue($variableId, $variableValue, $localConversionId, $dbServerIp);
            }
    	}
    }
?>