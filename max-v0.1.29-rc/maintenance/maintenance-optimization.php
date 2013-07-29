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
$Id: maintenance-optimization.php 3145 2005-05-20 13:15:01Z andrew $
*/

	// get active campaigns which are set to be optimized
	$result_campaigns = phpAds_dbQuery("
        SELECT
        	campaignname AS name,
        	campaignid AS id,
        	active,
        	optimise
        FROM 
        	".$conf['table']['campaigns']."
        WHERE 
        	active = 't' 
            AND optimise = 't'
        LIMIT
        	15");

	while ($campaigns = phpAds_dbFetchArray($result_campaigns)) {
		// get banners in this campaign
		$result_banners	= phpAds_dbQuery("
        	SELECT
        		b.bannerid	 		AS bannerid,
        		b.campaignid		AS campaignid,
        		b.weight 			AS weight,
        		sum(s.views)		AS views,
        		sum(s.clicks) 		AS clicks,
        		sum(s.conversions) 	AS conversions
        	FROM 
        		".$conf['table']['banners']." AS b,
        		".$conf['table']['adstats']." AS s
        	WHERE 
        		b.bannerid = s.bannerid AND
        		b.campaignid = ".$campaigns['id']."
        	GROUP BY 
        		b.bannerid
							");
		if (mysql_num_rows($result_banners) >1) {
			$banners_opt = array();
			// create array with banners for this campaign
			while ($banners = phpAds_dbFetchArray($result_banners)) {
				$banners_opt[] = $banners;
            }
			// sort array of banners by CTR descending
			phpAds_sortArray($banners_opt, 'conversions', false);

			// reset all banners in this campaign to weight=1
			$update	= phpAds_dbQuery("
            	UPDATE
            		" . $conf['table']['banners'] . "
            	SET
            		weight = 1
            	WHERE
            		campaignid = " . $campaigns['id']);
			// change the banner with most conversions to weight=10				
			$update	= phpAds_dbQuery("
            	UPDATE
            		" . $conf['table']['banners'] . "
            	SET
            		weight = 10
            	WHERE
            		bannerid = " . $banners_opt[0]['bannerid']);
		}
	}

function phpAds_sortArray(&$array, $column=0, $ascending=TRUE)
{
	
	for ($i=0; $i < count($array); $i++)
		if (isset($array[$i]['children']) && is_array($array[$i]['children']))
			phpAds_sortArray($array[$i]['children'], $column, $ascending);
	
	phpAds_qsort($array, $column, $ascending);
}

function phpAds_qsort(&$array, $column=0, $ascending=true, $first=0, $last=0)
{
	if ($last == 0)
		$last = count($array) - 1;
	
	if ($last > $first)
	{
		$alpha = $first;
		$omega = $last;
		$mid = floor(($alpha+$omega)/2);
		$guess = $array[$mid][$column];
		
		while ($alpha <= $omega)
		{
			if ($ascending)
			{
				while ( ($array[$alpha][$column] < $guess) && ($alpha < $last) )
					$alpha++;
				while ( ($array[$omega][$column] > $guess) && ($omega > $first) )
					$omega--;
			}
			else
			{
				while ( ($array[$alpha][$column] > $guess) && ($alpha < $last) )
					$alpha++;
				while ( ($array[$omega][$column] < $guess) && ($omega > $first) )
					$omega--;
			}
			
			if ($alpha <= $omega)
			{
				$temp = $array[$alpha];
				$array[$alpha] = $array[$omega];
				$array[$omega] = $temp;
				$alpha++;
				$omega--;
			}
		}
		
		if ($first < $omega)
			phpAds_qsort($array, $column, $ascending, $first, $omega);
		if ($alpha < $last)
			phpAds_qsort($array, $column, $ascending, $alpha, $last);
	}
}
?>