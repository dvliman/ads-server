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
$Id: adframe.php 3145 2005-05-20 13:15:01Z andrew $
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
/* Register input variables                              */
/*********************************************************/

phpAds_registerGlobal (
	'context'
	,'source'
	,'refresh'
	,'resize'
	,'rewrite'
	,'target'
	,'what'
	,'withtext'
	,'withText'
	,'referer'
	,'ct0'
	,'zoneid'
	,'campaignid'
	,'bannerid'
);



/*********************************************************/
/* Main code                                             */
/*********************************************************/


if (!isset($context)) 	$context = '';
if (!isset($rewrite))	$rewrite = 1;
if (!isset($source))	$source = '';
if (!isset($target)) 	$target = '_blank';
if (!isset($withtext)) 	$withtext = '';
if (!isset($ct0)) 	$ct0 = '';
if (!isset($what)) {
    if ($zoneid)     { $what = 'zone:'.$zoneid; }
    if ($campaignid) { $what = 'campaignid:'.$campaignid; }
    if ($bannerid)   { $what = 'bannerid:'.$bannerid; }
    
    if (!isset($what)) { $what = ''; }
}

$source = phpAds_deriveSource($source);

// Remove referer, to be sure it doesn't cause problems with limitations
if (isset($HTTP_SERVER_VARS['HTTP_REFERER'])) unset($HTTP_SERVER_VARS['HTTP_REFERER']);
if (isset($HTTP_REFERER)) unset($HTTP_REFERER);


// Get the banner
$banner = view_raw ($what, $target, $source, $withtext, $context, true, $ct0);
phpAds_flushCookie ();


// Rewrite targets in HTML code to make sure they are 
// local to the parent and not local to the iframe
if (isset($rewrite) && $rewrite == 1)
{
	$banner['html'] = preg_replace('#target\s*=\s*([\'"])_parent\1#i', "target='_top'", $banner['html']);
	$banner['html'] = preg_replace('#target\s*=\s*([\'"])_self\1#i', "target='_parent'", $banner['html']);
}


// Build HTML
echo "<html>\n";
echo "<head>\n";
echo "<title>".($banner['alt'] ? $banner['alt'] : 'Advertisement')."</title>\n";

// Add refresh meta tag if $refresh is set and numeric
if (isset($refresh) && !preg_match('/[^\d]/', $refresh)) {
	echo "<meta http-equiv='refresh' content='".$refresh."'>\n";
}

if (isset($resize) && $resize == 1)
{
	echo "<script language='JavaScript'>\n";
	echo "<!--\n";
	echo "\tfunction phpads_adjustframe(frame) {\n";
	echo "\t\tif (document.all) {\n";
    echo "\t\t\tparent.document.all[frame.name].width = ".$banner['width'].";\n";
    echo "\t\t\tparent.document.all[frame.name].height = ".$banner['height'].";\n";
  	echo "\t\t}\n";
  	echo "\t\telse if (document.getElementById) {\n";
    echo "\t\t\tparent.document.getElementById(frame.name).width = ".$banner['width'].";\n";
    echo "\t\t\tparent.document.getElementById(frame.name).height = ".$banner['height'].";\n";
  	echo "\t\t}\n";
	echo "\t}\n";
	echo "// -->\n";
	echo "</script>\n";
}

echo "</head>\n";

if (isset($resize) && $resize == 1)
	echo "<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' style='background-color:transparent; width: 100%; text-align: center;' onload=\"phpads_adjustframe(window);\">\n";
else
	echo "<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' style='background-color:transparent; width: 100%; text-align: center;'>\n";

echo $banner['html'];
echo "\n</body>\n";

echo "</html>\n";

?>
