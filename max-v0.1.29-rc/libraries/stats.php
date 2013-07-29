<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
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
$Id: stats.php 375 2004-06-14 13:24:39Z scott $
*/

    include_once 'pear/Date.php';
    
    function MAX_getDatesByPeriod($period)
    {
        switch ($period) {
            case 'today':      $dayBegin = & new Date();
                               $dayEnd   = & new Date();
                               break;
            case 'yesterday':  $dayBegin = & new Date(Date_Calc::prevDay());
                               $dayEnd   = & new Date(Date_Calc::prevDay());
                               break;
            case 'last7days' : $dayBegin = & new Date();
                               $dayBegin->subtractSpan(new Date_Span('6, 0, 0, 0'));
                               $dayEnd   = & new Date();
                               break;
            case 'thisweek':   $dayBegin = & new Date(Date_Calc::beginOfWeek());
                               $dayEnd   = & new Date(Date_Calc::beginOfNextWeek());
                               $dayEnd->subtractSpan(new Date_Span('1, 0, 0, 0'));
                               break;
            case 'lastweek':   $dayBegin = & new Date(Date_Calc::beginOfPrevWeek());
                               $dayEnd   = & new Date(Date_Calc::beginOfWeek());
                               $dayEnd->subtractSpan(new Date_Span('1, 0, 0, 0'));
                               break;
            case 'thismonth':  $dayBegin = & new Date(Date_Calc::beginOfMonth());
                               $dayEnd   = & new Date(Date_Calc::beginOfNextMonth());
                               $dayEnd->subtractSpan(new Date_Span('1, 0, 0, 0'));
                               break;
            case 'lastmonth':  $dayBegin = & new Date(Date_Calc::beginOfPrevMonth());
                               $dayEnd   = & new Date(Date_Calc::beginOfMonth());
                               $dayEnd->subtractSpan(new Date_Span("1, 0, 0, 0"));
                               break;
            case 'allstats':
            default:
                               $dayBegin = null;
                               $dayEnd = null;
        }
        $aDates = array();
        $aDates['day_begin'] = is_object($dayBegin) ? $dayBegin->format('%Y-%m-%d') : '';
        $aDates['day_end']   = is_object($dayEnd)   ? $dayEnd->format('%Y-%m-%d') : '';
        
        return $aDates;
    }
    
    function MAX_getDatesByPeriodLimitStart($period, $limit, $start)
    {
        $begin = $limit + $start-1;
        $end = $start;
        switch ($period) {
            case 'daily':      $dayBegin = & new Date();
                               $dayBegin->subtractSpan(new Date_Span("$begin, 0, 0, 0"));
                               $dayEnd   = & new Date();
                               $dayBegin->subtractSpan(new Date_Span("$end, 0, 0, 0"));
                               break;
            case 'weekly':     $dayBegin = & new Date(Date_Calc::prevDay());
                               $dayEnd   = & new Date(Date_Calc::prevDay());
                               break;
            case 'monthly' :    $dayBegin = & new Date();
                               $dayBegin->subtractSpan(new Date_Span('6, 0, 0, 0'));
                               $dayEnd   = & new Date();
                               break;
            case 'allstats':
            default:
                               $dayBegin = null;
                               $dayEnd = null;
        }
        $aDates = array();
        $aDates['day_begin'] = is_object($dayBegin) ? $dayBegin->format('%Y-%m-%d') : '';
        $aDates['day_end']   = is_object($dayEnd)   ? $dayEnd->format('%Y-%m-%d') : '';
        
        return $aDates;
    }
    
    function MAX_sortArray(&$aStats, $column, $ascending = true)
    {
        // I need to set these variables as globals so that they can be accessed in the Array compare function.
        $GLOBALS['sortColumn'] = $column;
        $GLOBALS['sortAscending'] = $ascending;
        uasort($aStats, '_sortArrayCompare');
    }
    function _sortArrayCompare($a, $b)
    {
        global $sortColumn, $sortAscending;
        
        switch ($sortColumn) {
            case 'name' : $compare = strcmp(strtolower($a[$sortColumn]), strtolower($b[$sortColumn]));
                          break;
                          
            case 'ctr'  : $ratioA = $a['views'] > 0 ? $a['clicks']/$a['views'] : 0;
                          $ratioB = $b['views'] > 0 ? $b['clicks']/$b['views'] : 0;
                          if ($ratioA == $ratioB) return 0;
                          $compare = $ratioA > $ratioB ? 1 : -1;
                          break;
                          
            case 'cnvr' : $ratioA = $a['clicks'] > 0 ? $a['conversions']/$a['clicks'] : 0;
                          $ratioB = $b['clicks'] > 0 ? $b['conversions']/$b['clicks'] : 0;
                          if ($ratioA == $ratioB) return 0;
                          $compare = $ratioA > $ratioB ? 1 : -1;
                          break;
                          
            default     : $compare = ($a[$sortColumn] > $b[$sortColumn]) ? 1 : -1;
        }
        if (!$sortAscending) {
            $compare = ($compare == 1) ? -1 : 1;
        }
        
        return $compare;
    }
?>