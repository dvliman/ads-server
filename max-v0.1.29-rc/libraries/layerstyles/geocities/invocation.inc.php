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
$Id: invocation.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Define constant used to place code generator
define('phpAds_adLayerLoaded', true);


// Register input variables
phpAds_registerGlobal ('align', 'collapsetime', 'padding', 'closetext');



/*********************************************************/
/* Place ad-generator settings                           */
/*********************************************************/

function phpAds_placeLayerSettings ()
{
	global $align, $collapsetime, $padding, $closetext;
	global $tabindex;
	
	if (!isset($align)) $align = 'right';
	if (!isset($collapsetime)) $collapsetime = '-';
	if (!isset($padding)) $padding = '2';
	if (!isset($closetext)) $closetext = $GLOBALS['strClose'];
	
	
	echo "<tr><td height='30' colspan='3'>&nbsp;</td></tr>";
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td>";
	echo "<td width='200'>".$GLOBALS['strAlignment']."</td><td width='370'>";
	echo "<select name='align' style='width:175px;' tabindex='".($tabindex++)."'>";
		echo "<option value='left'".($align == 'left' ? ' selected' : '').">".$GLOBALS['strLeft']."</option>";
		echo "<option value='center'".($align == 'center' ? ' selected' : '').">".$GLOBALS['strCenter']."</option>";
		echo "<option value='right'".($align == 'right' ? ' selected' : '').">".$GLOBALS['strRight']."</option>";
	echo "</select>";
	echo "</td></tr>";
	echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
	echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td>";
	echo "<td width='200'>".$GLOBALS['strCloseText']."</td><td width='370'>";
		echo "<input class='flat' type='text' name='closetext' size='' value='".$closetext."' style='width:175px;' tabindex='".($tabindex++)."'></td></tr>";
	echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
	echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td>";
	echo "<td width='200'>".$GLOBALS['strAutoCollapseAfter']."</td><td width='370'>";
		echo "<input class='flat' type='text' name='collapsetime' size='' value='".(isset($collapsetime) ? $collapsetime : '-')."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrSeconds']."</td></tr>";
	echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
	echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td>";
	echo "<td width='200'>".$GLOBALS['strBannerPadding']."</td><td width='370'>";
		echo "<input class='flat' type='text' name='padding' size='' value='".(isset($padding) ? $padding : '0')."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
	echo "<tr><td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>";
}



/*********************************************************/
/* Place ad-generator settings                           */
/*********************************************************/

function phpAds_generateLayerCode ($parameters)
{
	global $phpAds_config;
	global $align, $collapsetime, $padding, $closetext;
	
	$parameters[] = 'layerstyle=geocities';
	$parameters[] = 'align='.(isset($align) ? $align : 'right');
	$parameters[] = 'padding='.(isset($padding) ? (int)$padding : '2');
	
	if (isset($closetext)) $parameters[] = 'closetext='.urlencode($closetext);
	
	if (isset($collapsetime) && $collapsetime > 0)
		$parameters[] = 'collapsetime='.$collapsetime;
	
	$buffer = "<script language='JavaScript' type='text/javascript' src='".$phpAds_config['url_prefix']."/adlayer.php";
	if (sizeof($parameters) > 0)
		$buffer .= "?".implode ("&", $parameters);
	$buffer .= "'></script>";
	
	return $buffer;
}



/*********************************************************/
/* Return $show var for generators                       */
/*********************************************************/

function phpAds_getlayerShowVar ()
{
	return array (
		'what' => true,
		'acid' => true,
		'target' => true,
		'campaignid' => true,		
		'source' => true,
		'layerstyle' => true,
		'layercustom' => true
	);
}

?>
