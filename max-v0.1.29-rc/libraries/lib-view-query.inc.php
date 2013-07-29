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
$Id: lib-view-query.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBVIEWQUERY_INCLUDED', true);


/*********************************************************/
/* Build the query needed to fetch banners               */
/*********************************************************/

function phpAds_buildQuery ($part, $lastpart, $precondition)
{
	global $phpAds_config;

	// Setup basic query
	$select = "
        SELECT
	        b.bannerid AS bannerid,
	        b.campaignid AS campaignid,
	        b.priority AS priority,
	        b.contenttype AS contenttype,
	        b.storagetype AS storagetype,
	        b.bannertext AS bannertext,
	        b.filename AS filename,
	        b.imageurl AS imageurl,
	        b.url AS url,
	        b.htmlcache AS htmlcache,
	        b.width AS width,
	        b.height AS height,
	        b.weight AS weight,
	        b.seq AS seq,
	        b.autohtml AS autohtml,
	        b.adserver AS adserver,
	        b.target AS target,
	        b.alt AS alt,
	        b.status AS status,
	        b.block AS block,
	        b.capping AS capping,
	        b.session_capping AS session_capping,
	        b.compiledlimitation AS compiledlimitation,
	        b.alt_filename AS alt_filename,
	        b.alt_contenttype AS alt_contenttype,
	        b.alt_imageurl AS alt_imageurl,
	        b.append AS append,
	        b.weight AS campaignweight
        FROM
            {$phpAds_config['tbl_banners']} AS b,
            {$phpAds_config['tbl_campaigns']} AS m
        WHERE
            b.active='t'
            AND m.active = 't'
            AND b.campaignid=m.campaignid
	";

	// Add preconditions to query
	if ($precondition != '')
		$select .= " $precondition ";


	// Other
	if ($part != '')
	{
		$conditions = '';
		$onlykeywords = true;

		$part_array = explode(',', $part);
		for ($k=0; $k < count($part_array); $k++)
		{
			// Process switches
			if ($phpAds_config['con_key'])
			{
				if (substr($part_array[$k], 0, 1) == '+' || substr($part_array[$k], 0, 1) == '_')
				{
					$operator = 'AND';
					$part_array[$k] = substr($part_array[$k], 1);
				}
				elseif (substr($part_array[$k], 0, 1) == '-')
				{
					$operator = 'NOT';
					$part_array[$k] = substr($part_array[$k], 1);
				}
				else
					$operator = 'OR';
			}
			else
				$operator = 'OR';


			//	Test statements
			if($part_array[$k] != '' && $part_array[$k] != ' ')
			{
				// Banner dimensions
				if(preg_match('#^[0-9]+x[0-9]+$#', $part_array[$k]))
				{
					list($width, $height) = explode('x', $part_array[$k]);

					if ($operator == 'OR')
						$conditions .= "OR (b.width = $width AND b.height = $height) ";
					elseif ($operator == 'AND')
						$conditions .= "AND (b.width = $width AND b.height = $height) ";
					else
						$conditions .= "AND (b.width != $width OR b.height != $height) ";

					$onlykeywords = false;
				}

				// Banner Width
				elseif (substr($part_array[$k],0,6) == 'width:')
				{
					$part_array[$k] = substr($part_array[$k], 6);
					if($part_array[$k] != '' && $part_array[$k] != ' ')

					if ($operator == 'OR')
						$conditions .= "OR b.width = '".trim($part_array[$k])."' ";
					elseif ($operator == 'AND')
						$conditions .= "AND b.width = '".trim($part_array[$k])."' ";
					else
						$conditions .= "AND b.width != '".trim($part_array[$k])."' ";

					$onlykeywords = false;
				}

				// Banner ID
				elseif ((substr($part_array[$k], 0, 9) == 'bannerid:') || (preg_match('#^[0-9]+$#', $part_array[$k])))
				{
					if (substr($part_array[$k], 0, 9) == 'bannerid:')
						$part_array[$k] = substr($part_array[$k], 9);

					if ($part_array[$k] != '' && $part_array[$k] != ' ')
					{
						if ($operator == 'OR')
							$conditions .= "OR b.bannerid='".trim($part_array[$k])."' ";
						elseif ($operator == 'AND')
							$conditions .= "AND b.bannerid='".trim($part_array[$k])."' ";
						else
							$conditions .= "AND b.bannerid!='".trim($part_array[$k])."' ";
					}

					$onlykeywords = false;
				}

				// Campaign ID
				elseif (substr($part_array[$k], 0, 11) == 'campaignid:')
				{
					$part_array[$k] = substr($part_array[$k], 11);
					if ($part_array[$k] != '' && $part_array[$k] != ' ')
					{
						if ($operator == 'OR')
							$conditions .= "OR m.campaignid='".trim($part_array[$k])."' ";
						elseif ($operator == 'AND')
							$conditions .= "AND m.campaignid='".trim($part_array[$k])."' ";
						else
							$conditions .= "AND m.campaignid!='".trim($part_array[$k])."' ";
					}

					$onlykeywords = false;
				}

				// Format
				elseif (substr($part_array[$k], 0, 7) == 'format:')
				{
					$part_array[$k] = substr($part_array[$k], 7);
					if($part_array[$k] != '' && $part_array[$k] != ' ')
					{
						if ($operator == 'OR')
							$conditions .= "OR b.contenttype='".trim($part_array[$k])."' ";
						elseif ($operator == 'AND')
							$conditions .= "AND b.contenttype='".trim($part_array[$k])."' ";
						else
							$conditions .= "AND b.contenttype!='".trim($part_array[$k])."' ";
					}

					$onlykeywords = false;
				}

				// HTML
				elseif($part_array[$k] == 'html')
				{
					if ($operator == 'OR')
						$conditions .= "OR b.contenttype='html' ";
					elseif ($operator == 'AND')
						$conditions .= "AND b.contenttype='html' ";
					else
						$conditions .= "AND b.contenttype!='html' ";

					$onlykeywords = false;
				}

				// Keywords
				else
				{
					if ($phpAds_config['use_keywords'])
					{
						if (!$phpAds_config['mult_key'])
						{
							if ($operator == 'OR')
								$conditions .= "OR b.keyword = '".trim($part_array[$k])."' ";
							elseif ($operator == 'AND')
								$conditions .= "AND b.keyword = '".trim($part_array[$k])."' ";
							else
								$conditions .= "AND b.keyword != '".trim($part_array[$k])."' ";
						}
						else
						{
							if ($operator == 'OR')
								$conditions .= "OR CONCAT(' ',b.keyword,' ') LIKE '% $part_array[$k] %' ";
							elseif ($operator == 'AND')
								$conditions .= "AND CONCAT(' ',b.keyword,' ') LIKE '% $part_array[$k] %' ";
							else
								$conditions .= "AND CONCAT(' ',b.keyword,' ') NOT LIKE '% $part_array[$k] %' ";
						}
					}
				}
			}
		}

		// Strip first AND or OR from $conditions
		$conditions = strstr($conditions, ' ');

		// Add global keyword
		if ($phpAds_config['use_keywords'] && $lastpart == true && $onlykeywords == true)
			$conditions .= "OR CONCAT(' ',b.keyword,' ') LIKE '% global %' ";

		// Add conditions to select
		if ($conditions != '') $select .= ' AND ('.$conditions.') ';
	}

	return ($select);
}


?>