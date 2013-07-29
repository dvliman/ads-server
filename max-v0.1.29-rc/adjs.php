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
$Id: adjs.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Figure out our location
define ('phpAds_path', '.');



/*********************************************************/
/* Include required files                                */
/*********************************************************/

require	(phpAds_path."/config.inc.php"); 
require_once (phpAds_path."/libraries/lib-io.inc.php");
require (phpAds_path."/libraries/lib-db.inc.php");

if (($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon']) || $phpAds_config['acl'])
{
	require (phpAds_path."/libraries/lib-remotehost.inc.php");
	
	if ($phpAds_config['log_adviews'] && !$phpAds_config['log_beacon'])
		require (phpAds_path."/libraries/lib-log.inc.php");
	
	if ($phpAds_config['acl'])
		require (phpAds_path."/libraries/lib-limitations.inc.php");
}

require	(phpAds_path."/libraries/lib-view-main.inc.php");
require (phpAds_path."/libraries/lib-cache.inc.php");



/*********************************************************/
/* Java-encodes text                                     */
/*********************************************************/

function enjavanate ($str, $limit = 60)
{
	$str   = str_replace("\r", '', $str);
	
	print "var phpadsbanner = '';\n\n";
	
	while (strlen($str) > 0)
	{
		$line = substr ($str, 0, $limit);
		$str  = substr ($str, $limit);
		
		$line = str_replace('\\', "\\\\", $line);
		$line = str_replace('\'', "\\'", $line);
		$line = str_replace("\r", '', $line);
		$line = str_replace("\n", "\\n", $line);
		$line = str_replace("\t", "\\t", $line);
		$line = str_replace('<', "<'+'", $line);
		
		print "phpadsbanner += '$line';\n";
	}
	
	print "\ndocument.write(phpadsbanner);\n";
}



/*********************************************************/
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
	 'block'
	,'context'
	,'exclude'
	,'source'
	,'target'
	,'withtext'
	,'withText'
	,'what'
	,'referer'
	,'ct0'
	,'zoneid'
	,'campaignid'
	,'bannerid'
);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (!isset($context)) 		$context = '';
if (!isset($source))		$source = '';
if (!isset($target)) 		$target = '';
if (isset($withText) && 
	!isset($withtext))  	$withtext = $withText;
if (!isset($withtext)) 		$withtext = '';
$ct0 = (empty($ct0) || $ct0 == 'undefined') ? '' : $ct0;
if (!isset($what)) {
    if ($zoneid)     { $what = 'zone:'.$zoneid; }
    if ($campaignid) { $what = 'campaignid:'.$campaignid; }
    if ($bannerid)   { $what = 'bannerid:'.$bannerid; }
    
    if (!isset($what)) { $what = ''; }
}

// Derive the source parameter
$source = phpAds_deriveSource($source);

if (isset($exclude) && $exclude != '')
{
	$exclude = explode (',', $exclude);
	$context = array();
	
	for ($i = 0; $i < count($exclude); $i++)
		if ($exclude[$i] != '')
			$context[] = array ("!=" => $exclude[$i]);
}

// Set real referer
if (isset($referer) && $referer)
	$HTTP_REFERER = $HTTP_SERVER_VARS['HTTP_REFERER'] = stripslashes($referer);


// Get the banner
$output = view_raw ($what, $target, $source, $withtext, $context, true, $ct0);
phpAds_flushCookie ();

// Show the banner
header("Content-type: application/x-javascript");
enjavanate($output['html']);

// Block this banner for next invocation
if (isset($block) && $block != '' && $block != '0' && $output['bannerid'])
	print ("\nif (document.phpAds_used) document.phpAds_used += 'bannerid:".$output['bannerid'].",';\n");

// Block this campaign for next invocation
if (isset($blockcampaign) && $blockcampaign != '' && $blockcampaign != '0' && $output['campaignid'])
	print ("\nif (document.phpAds_used) document.phpAds_used += 'campaignid:".$output['campaignid'].",';\n");

?>