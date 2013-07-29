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
$Id: lib-maintenance.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Load translations
@include (phpAds_path.'/language/english/maintenance.lang.php');
if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/maintenance.lang.php'))
	@include (phpAds_path.'/language/'.$phpAds_config['language'].'/maintenance.lang.php');



function phpAds_MaintenanceSelection($section)
{
	global 
		$phpAds_config
		,$phpAds_TextDirection
		,$strBanners
		,$strCache
		,$strChooseSection
		,$strPriority
		,$strSourceEdit
		,$strStats
		,$strStorage
	;

?>
<script language="JavaScript">
<!--
function maintenance_goto_section()
{
	s = document.maintenance_selection.section.selectedIndex;

	s = document.maintenance_selection.section.options[s].value;
	document.location = 'maintenance-' + s + '.php';
}
// -->
</script>
<?php
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
    echo "<tr><form name='maintenance_selection'><td height='35'>";
	echo "<b>".$strChooseSection.":&nbsp;</b>";
    echo "<select name='section' onChange='maintenance_goto_section();'>";
	
	echo "<option value='banners'".($section == 'banners' ? ' selected' : '').">".$strBanners."</option>";
	echo "<option value='priority'".($section == 'priority' ? ' selected' : '').">".$strPriority."</option>";
	
	if ($phpAds_config['type_web_allow'] == true && (($phpAds_config['type_web_mode'] == 0 && 
	    $phpAds_config['type_web_dir'] != '') || ($phpAds_config['type_web_mode'] == 1 && 
	    $phpAds_config['type_web_ftp'] != '')) && $phpAds_config['type_web_url'] != '')
		echo "<option value='storage'".($section == 'storage' ? ' selected' : '').">".$strStorage."</option>";
	
	if ($phpAds_config['delivery_caching'] != 'none')
		echo "<option value='cache'".($section == 'zones' ? ' selected' : '').">".$strCache."</option>";

	//echo "<option value='source-edit'".($section == 'source-edit' ? ' selected' : '').">".$strSourceEdit."</option>";
	echo "</select>&nbsp;<a href='javascript:void(0)' onClick='maintenance_goto_section();'>";
	echo "<img src='images/".$phpAds_TextDirection."/go_blue.gif' border='0'></a>";
    echo "</td></form></tr>";
  	echo "</table>";
	
	phpAds_ShowBreak();
}

?>