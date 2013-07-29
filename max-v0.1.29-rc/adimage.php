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
$Id: adimage.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require	("config.inc.php");
require_once ("libraries/lib-io.inc.php");
require ("libraries/lib-db.inc.php");


// Register input variables
phpAds_registerGlobal ('filename', 'contenttype');


// Open a connection to the database
phpAds_dbConnect();


if (isset($filename) && $filename != '')
{
	$res = phpAds_dbQuery("
		SELECT
			contents,
			UNIX_TIMESTAMP(t_stamp) AS t_stamp
		FROM
			".$phpAds_config['tbl_images']."
		WHERE
			filename = '".$filename."'
		");
	
	if (phpAds_dbNumRows($res) == 0)
	{
		// Filename not found, show default banner
		if ($phpAds_config['default_banner_url'] != "")
		{
			Header("Location: ".$phpAds_config['default_banner_url']);
		}
	}
	else
	{
		// Filename found, dump contents to browser
		$row = phpAds_dbFetchArray($res);
		
		// Check if the browser sent a If-Modified-Since header and if the image was
		// modified since that date
		if (!isset($HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE']) ||
			$row['t_stamp'] > strtotime($HTTP_SERVER_VARS['HTTP_IF_MODIFIED_SINCE']))
		{
			Header ("Last-Modified: ".gmdate('D, d M Y H:i:s', $row['t_stamp']).' GMT');
			
			if (isset($contenttype) && $contenttype != '')
			{
				switch ($contenttype)
				{
					case 'swf': Header('Content-type: application/x-shockwave-flash; name='.$filename); break;
					case 'dcr': Header('Content-type: application/x-director; name='.$filename); break;
					case 'rpm': Header('Content-type: audio/x-pn-realaudio-plugin; name='.$filename); break;
					case 'mov': Header('Content-type: video/quicktime; name='.$filename); break;
					default:	Header('Content-type: image/'.$contenttype.'; name='.$filename); break;
				}
			}
			
			echo $row['contents'];
		}
		else
		{
			// Send "Not Modified" status header
			if (php_sapi_name() == 'cgi')
			{
				// PHP as CGI, use Status: [status-number]
				Header ('Status: 304 Not Modified');
			}
			else
			{
				// PHP as module, use HTTP/1.x [status-number]
				Header ($HTTP_SERVER_VARS['SERVER_PROTOCOL'].' 304 Not Modified');
			}
		}
	}
}
else
{
	// Filename not specified, show default banner
	
	if ($phpAds_config['default_banner_url'] != "")
	{
		Header("Location: ".$phpAds_config['default_banner_url']);
	}
}

phpAds_dbClose();

?>