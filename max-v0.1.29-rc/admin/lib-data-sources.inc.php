<?php

/************************************************************************/
/* phpAdsNew 2                                                          */
/* ===========                                                          */
/*                                                                      */
/* For more information visit: http://www.phpadsnew.com                 */
/************************************************************************/

function phpAds_getSources($name='', $parent='')
{
	global $phpAds_config;

	if (strlen($parent) > 0)
	{
		$n = substr_count($parent,'/') + 2;
		$query =
			"SELECT".
			" SUBSTRING_INDEX(SUBSTRING_INDEX(source,'/',".$n."),'/',-1) AS source_part".
			",COUNT(*) AS sum_views".
			" FROM ".$phpAds_config['tbl_adviews'].
			" WHERE source LIKE '".$parent."/%'".
			" AND t_stamp > DATE_SUB(NOW(), INTERVAL 7 DAY)".
			" GROUP BY source_part".
			" ORDER BY sum_views DESC"
		;
	}
	else
	{
		$query =
			"SELECT".
			" SUBSTRING_INDEX(source, '/', 1) AS source_part".
			",COUNT(*) AS sum_views".
			" FROM ".$phpAds_config['tbl_adviews'].
			" WHERE t_stamp > DATE_SUB(NOW(), INTERVAL 7 DAY)".
			" GROUP BY source_part".
			" ORDER BY sum_views DESC"
		;
	}
		
	$source_arr = array();

	$res_sources = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row_sources = phpAds_dbFetchArray($res_sources))
	{
		$source_arr[] = $row_sources;
		//echo "filing source: ".$row_sources['source']."...<br>\n";
		//phpAds_buildSourceArrayChildren($source_arr, $row_sources['source']);
	}
	
	// Sort the array
	//$ascending = !( ($orderdirection == 'down') || ($orderdirection == '') );
	//phpAds_sortSources($source_arr, $listorder, $ascending);
	
	return $source_arr;
}

function phpAds_buildSourceArrayChildren(&$source_arr, $path)
{
	if (!isset($source_arr['children']) || !is_array($source_arr['children']))
		$source_arr['children'] = array();
	
	// First, get the name of this branch of the source.
	$len = strpos($path, '/');
	$name = (is_integer($len)) ? substr($path, 0, $len) : $path;
	$remainder = (is_integer($len)) ? substr($path, $len+1) : '';
		
	// Next, see if there is already a branch present in the sources array
	$index = false;
	for ($i=0; $i<sizeof($source_arr['children']); $i++)
	{
		if ($name == $source_arr['children'][$i]['name'])
		{
			$index = $i;
			break;
		}
	}
	if (!is_integer($index))
	{
		$tmp_source_arr['name'] = $name;
		$tmp_source_arr['path'] = (strlen($source_arr['path']) > 0) ? $source_arr['path'].'/'.$name : $name;
		if (is_integer($len))
			$tmp_source_arr['children'] = array();
		$source_arr['children'][] = $tmp_source_arr;
		$index = sizeof($source_arr['children']) - 1;
	}
	
	// If there are children, recursively populate the children array
	if (strlen($remainder) > 0)
	{
		phpAds_buildSourceArrayChildren($source_arr['children'][$index], $remainder, $row_source_stats);
	}
}

function phpAds_sortSources(&$sources, $column=0, $ascending=true)
{
	if (isset($sources['children']) && is_array($sources['children']))
	{
		if (sizeof($sources['children'] > 1))
			phpAds_qsort($sources['children'], $column, $ascending);

		for ($i=0; $i<sizeof($sources['children']); $i++)
		{
			phpAds_sortSources($sources['children'][$i], $column, $ascending);
		}
	}
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
