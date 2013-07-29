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
$Id: bc.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Compatibility code for users of PHP < 4.3.0

// WARNING! This file must be maintained in TWO places - in the main Max
// library directory, and in the maintenance library directory, until the
// new directory structure is ready for release.

// Define the is_a() function
if (!function_exists('is_a')) {
    function is_a($object, $className)
    {
        return ((strtolower($className) == get_class($object)) || (is_subclass_of($object, $className)));
    }
}
 
// Define the file_get_contents() function
if (!function_exists('file_get_contents')) {
    function file_get_contents($filename)
    {
        $fd = fopen($filename, "rb");
        $content = fread($fd, filesize($filename));
        fclose($fd);
        return $content;
    }
}

// Define the __FUNCTION__ constant
if (!defined('__FUNCTION__')) {
    define('__FUNCTION__', null);
}

?>