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
$Id: 5.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Compatility code for users of PHP 5

// WARNING! This file must be maintained in TWO places - in the main Max
// library directory, and in the maintenance library directory, until the
// new directory structure is ready for release.

// Deal with server variables if register_long_arrays is not enabled
if (!isset($HTTP_SERVER_VARS)) {
    $HTTP_SERVER_VARS =& $_SERVER;
    $HTTP_GET_VARS    =& $_GET;
    $HTTP_POST_VARS   =& $_POST;
    $HTTP_COOKIE_VARS =& $_COOKIE;
    $HTTP_POST_FILES  =& $_FILES;
    $HTTP_ENV_VARS    =& $_ENV;
}

?>