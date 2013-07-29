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
$Id: lib-swf.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Define SWF tags
define ('swf_tag_identify', 		 chr(0x46).chr(0x57).chr(0x53));
define ('swf_tag_compressed', 		 chr(0x43).chr(0x57).chr(0x53));
define ('swf_tag_geturl',   		 chr(0x00).chr(0x83));
define ('swf_tag_null',     		 chr(0x00));
define ('swf_tag_actionpush', 		 chr(0x96));
define ('swf_tag_actiongetvariable', chr(0x1C));
define ('swf_tag_actiongeturl2', 	 chr(0x9A).chr(0x01));


// Define preferences
$swf_variable		= 'clickTAG';		// The name of the ActionScript variable used for urls
$swf_target_var		= 'clickTARGET';	// The name of the ActionScript variable used for targets



/*********************************************************/
/* Get the Flash version of the banner                   */
/*********************************************************/

function phpAds_SWFVersion($buffer)
{
	if (substr($buffer, 0, 3) == swf_tag_identify ||
		substr($buffer, 0, 3) == swf_tag_compressed)
		return ord(substr($buffer, 3, 1));
	else
		return false;
}



/*********************************************************/
/* Is the Flash file compressed?                         */
/*********************************************************/

function phpAds_SWFCompressed($buffer)
{
	if (substr($buffer, 0, 3) == swf_tag_compressed)
		return true;
	else
		return false;
}



/*********************************************************/
/* Compress Flash file                                   */
/*********************************************************/

function phpAds_SWFCompress($buffer)
{
	$version = ord(substr($buffer, 3, 1));
	
	if (function_exists('gzcompress') &&
	    substr($buffer, 0, 3) == swf_tag_identify &&
		$version >= 3)
	{
		// When compressing an old file, update
		// version, otherwise keep existing version
		if ($version < 6) $version = 6;
		
		$output  = 'C';
		$output .= substr ($buffer, 1, 2);
		$output .= chr($version);
		$output .= substr ($buffer, 4, 4);
		$output .= gzcompress (substr ($buffer, 8));
		
		return ($output);
	}
	else
		return ($buffer);
}



/*********************************************************/
/* Decompress Flash file                                 */
/*********************************************************/

function phpAds_SWFDecompress($buffer)
{
	if (function_exists('gzuncompress') &&
		substr($buffer, 0, 3) == swf_tag_compressed &&
		ord(substr($buffer, 3, 1)) >= 6)
	{
		$output  = 'F';
		$output .= substr ($buffer, 1, 7);
		$output .= gzuncompress (substr ($buffer, 8));
		
		return ($output);
	}
	else
		return ($buffer);
}



/*********************************************************/
/* Get the dimensions of the Flash banner                */
/*********************************************************/

function phpAds_SWFBits($buffer, $pos, $count)
{
	$result = 0;
	
	for ($loop = $pos; $loop < $pos + $count; $loop++)
		$result = $result + ((((ord($buffer[(int)($loop / 8)])) >> (7 - ($loop % 8))) & 0x01) << ($count - ($loop - $pos) - 1));
	
	return $result;
}

function phpAds_SWFDimensions($buffer)
{
	// Decompress if file is a Flash MX compressed file
	if (phpAds_SWFCompressed($buffer))
		$buffer = phpAds_SWFDecompress($buffer);
	
	// Get size of rect structure
	$bits   = phpAds_SWFBits ($buffer, 64, 5);
	
	// Get rect
	$width  = (int)(phpAds_SWFBits ($buffer, 69 + $bits, $bits) - phpAds_SWFBits ($buffer, 69, $bits)) / 20;
	$height = (int)(phpAds_SWFBits ($buffer, 69 + (3 * $bits), $bits) - phpAds_SWFBits ($buffer, 69 + (2 * $bits), $bits)) / 20;
	
	
	return (
		array($width, $height)
	);
}



/*********************************************************/
/* Get info about the hardcoded urls                     */
/*********************************************************/

function phpAds_SWFInfo($buffer)
{
	global $swf_variable, $swf_target_var;
	
	
	// Decompress if file is a Flash MX compressed file
	if (phpAds_SWFCompressed($buffer))
		$buffer = phpAds_SWFDecompress($buffer);
	
	$parameters = array();
	$pos = 0;
	$linkcount = 1;
	
	while ($result = strpos($buffer, swf_tag_geturl, $pos))
	{
		$result++;
		
		if (strtolower(substr($buffer, $result + 3, 7)) == 'http://' ||
		    strtolower(substr($buffer, $result + 3, 11)) == 'javascript:')
		{
			$parameter_length = ord(substr($buffer, $result + 1, 1));
			$parameter_total  = substr($buffer, $result + 3, $parameter_length);
			$parameter_split  = strpos($parameter_total, swf_tag_null);
			$parameter_url    = substr($parameter_total, 0, $parameter_split);
			$parameter_target = substr($parameter_total, $parameter_split + 1, strlen($parameter_total) - $parameter_split - 2);
			
			$replacement = swf_tag_actionpush.
						     chr(strlen($swf_variable)+2).
						     swf_tag_null.
						   swf_tag_null.
						     $swf_variable.
						   swf_tag_null.
						   
						   swf_tag_actiongetvariable.
						   
						   swf_tag_actionpush.
						     chr(strlen($swf_target_var)+2).
						     swf_tag_null.
						   swf_tag_null.
						     $swf_target_var.
						   swf_tag_null.
						   
						   swf_tag_actiongetvariable.
						   
						   swf_tag_actiongeturl2;
			
			if (strlen($replacement) > $parameter_length + 3)
			{
				break;
			}
			else
			{
				$parameters[$linkcount] = array(
					$result, $parameter_url, $parameter_target
				);
			}
			
			$linkcount++;
		}
		
		$pos = $result;
	}
	
	if (count($parameters))
		return ($parameters);
	else
		return false;
}



/*********************************************************/
/* Convert hard coded urls                               */
/*********************************************************/

function phpAds_SWFConvert($buffer, $compress, $allowed)
{
	global $swf_variable, $swf_target_var;
	
	// Decompress if file is a Flash MX compressed file
	if (phpAds_SWFCompressed($buffer))
		$buffer = phpAds_SWFDecompress($buffer);
	
	
	$parameters = array();
	$pos = 0;
	$linkcount = 1;
	$allowedcount = 1;
	
	while ($result = strpos($buffer, swf_tag_geturl, $pos))
	{
		$result++;
		
		if (strtolower(substr($buffer, $result + 3, 7)) == 'http://' ||
		    strtolower(substr($buffer, $result + 3, 11)) == 'javascript:')
		{
			$parameter_length = ord(substr($buffer, $result + 1, 1));
			$parameter_total  = substr($buffer, $result + 3, $parameter_length);
			$parameter_split  = strpos($parameter_total, swf_tag_null);
			$parameter_url    = substr($parameter_total, 0, $parameter_split);
			$parameter_target = substr($parameter_total, $parameter_split + 1, strlen($parameter_total) - $parameter_split - 2);
			
			$replacement = swf_tag_actionpush.
						     chr(strlen($swf_variable)+2).
						     swf_tag_null.
						   swf_tag_null.
						     $swf_variable.
						   swf_tag_null.
						   
						   swf_tag_actiongetvariable.
						   
						   swf_tag_actionpush.
						     chr(strlen($swf_target_var)+2).
						     swf_tag_null.
						   swf_tag_null.
						     $swf_target_var.
						   swf_tag_null.
						   
						   swf_tag_actiongetvariable.
						   
						   swf_tag_actiongeturl2;
			
			if (strlen($replacement) > $parameter_length + 3)
			{
				break;
			}
			elseif (strlen($replacement) < $parameter_length + 3)
			{
				$padding = $parameter_length + 3 - strlen($replacement);
				
				for ($i=0;$i<$padding;$i++)
				{
					$replacement .= swf_tag_null;
				}
			}
			
			// Is this link allowed to be converted?
			if (in_array($allowedcount, $allowed))
			{
				// Convert
				$replacement = substr($buffer, 0, $result).
							   $replacement.
							   substr($buffer, $result + strlen($replacement), strlen($buffer) - ($result + strlen($replacement)));
			   	
				$buffer = $replacement;
				$parameters[$linkcount] = $allowedcount;
				
				$linkcount++;
			}
			
			$allowedcount++;
		}
		
		$pos = $result;
	}
	
	
	if ($compress == true)
		$buffer = phpAds_SWFCompress($buffer);
	
	return (array($buffer, $parameters));
}

?>