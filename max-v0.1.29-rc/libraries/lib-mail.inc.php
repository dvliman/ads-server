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
$Id: lib-mail.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBMAIL_INCLUDED', true);


/*********************************************************/
/* Send an email                                         */
/*********************************************************/

function phpAds_sendMail ($email, $readable, $subject, $contents)
{
	global $phpAds_config, $phpAds_CharSet;
	
	// Build To header
	if (!get_cfg_var('SMTP'))
		$param_to = '"'.$readable.'" <'.$email.'>';
	else
		$param_to = $email;
	
	// Build additional headers
	$param_headers = "Content-Transfer-Encoding: 8bit\r\n";
	
	if (isset($phpAds_CharSet))
		$param_headers .= "Content-Type: text/plain; charset=".$phpAds_CharSet."\r\n"; 
	
	if (get_cfg_var('SMTP'))
		$param_headers .= 'To: "'.$readable.'" <'.$email.">\r\n";
	
	$param_headers .= 'From: "'.$phpAds_config['admin_fullname'].'" <'.$phpAds_config['admin_email'].'>'."\r\n";
	
	if ($phpAds_config['admin_email_headers'] != '')
		$param_headers .= "\r\n".$phpAds_config['admin_email_headers'];
	
	// Use only \n as header separator when qmail is used
	if ($phpAds_config['qmail_patch'])
		$param_headers = str_replace("\r", '', $param_headers);
	
	// Add \r to linebreaks in the contents for MS Exchange compatibility
	$contents = str_replace("\n", "\r\n", $contents);
	
	return (@mail ($param_to, $subject, $contents, $param_headers));
}


?>