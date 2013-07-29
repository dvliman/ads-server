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
$Id: js-form.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Figure out our location
if (!defined("phpAds_path"))
{
	if (strlen(__FILE__) > strlen(basename(__FILE__)))
	    define ('phpAds_path', ereg_replace("[/\\\\]admin[/\\\\][^/\\\\]+$", '', __FILE__));
	else
	    define ('phpAds_path', '..');
}

// Load language strings
@include (phpAds_path.'/language/english/default.lang.php');
if ($HTTP_GET_VARS['language'] != 'english' && file_exists(phpAds_path.'/language/'.$HTTP_GET_VARS['language'].'/default.lang.php'))
	@include (phpAds_path.'/language/'.$HTTP_GET_VARS['language'].'/default.lang.php');

// Send content-type header
header("Content-type: application/x-javascript");

?>



/*********************************************************/
/* Check form                                            */
/*********************************************************/

function phpAds_formSetRequirements(obj, descr, req, check)
{
	obj = findObj(obj);
	
	// set properties
	if (obj)
	{
		obj.validateReq = req;
		obj.validateCheck = check;
		obj.validateDescr = descr;
	}
}

function phpAds_formSetUnique(obj, unique)
{
	obj = findObj(obj);
	
	// set properties
	if (obj)
		obj.validateUnique = unique;
}

function phpAds_formUpdate(obj)
{
	if (obj.validateCheck || obj.validateReq)
	{
		err = false;
		val = obj.value;
		
		if ((val == '' || val == '-' || val == 'http://') && obj.validateReq == true)
			err = true;
		
		if (obj.validateCheck && err == false && val != '')
		{
			if (obj.validateCheck == 'url' &&
				val.substr(0,7) != 'http://' && 
				val.substr(0,8) != 'https://')
				err = true;
				
			if (obj.validateCheck == 'email' && 
				(val.indexOf('@') < 1 || val.indexOf('@') == (val.length - 1)))
				err = true;
			
			if (obj.validateCheck == 'number*' &&
				(isNaN(val) && val != '*' || parseInt(val) < 0))
				err = true;
	
			if (obj.validateCheck.substr(0,7) == 'number+')
			{	
				if (obj.validateCheck.length > 7)
					min = obj.validateCheck.substr(7,obj.validateCheck.length - 7);
				else
					min = 0;
				
				if (min == 0 && val == '-') val = 0;
				
				if (isNaN(val) || parseInt(val) < parseInt(min))
					err = true;
			}
			
			if (obj.validateCheck.substr(0,8) == 'compare:')
			{
				compare = obj.validateCheck.substr(8,obj.validateCheck.length - 8);
				compareobj = findObj(compare);
				
				if (val != compareobj.value)
					err = true;
			}
			
			if (obj.validateCheck == 'unique')
			{
				needle = obj.value.toLowerCase();
				haystack = obj.validateUnique.toLowerCase();
				
				if (haystack.indexOf('|'+needle+'|') > -1)
					err = true;
			}
		}
		
		// Change class
		if (err)
			obj.className='error';
		else
			obj.className='flat';
		
		return (err);
	}
}


function phpAds_formCheck(f)
{
	var noerrors = true;
	var first	 = false;
	var fields   = new Array();

	// Check for errors
	for (var i = 0; i < f.elements.length; i++)
	{
		if (f.elements[i].validateCheck ||
			f.elements[i].validateReq)
		{
			err = phpAds_formUpdate (obj = f.elements[i]);
			
			if (err)
			{
				if (first == false) first = i;
				
				fields.push(f.elements[i].validateDescr);
				noerrors = false;
			}
		}
	}
	
	if (noerrors == false)
	{
		alert ('<?php echo addslashes($strFieldContainsErrors) ?>' +
			   '                     \n\n- ' + 
			   fields.join('\n- ') + 
			   '\n\n' +
			   '<?php echo addslashes($strFieldFixBeforeContinue1) ?>' +
			   '\n' +
			   '<?php echo addslashes($strFieldFixBeforeContinue2) ?>' +
			   '\n');
		
		// Select field with first error
		f.elements[first].select();
		f.elements[first].focus();
	}
	
	return (noerrors);
}

function phpAds_CopyClipboard(obj)
{
	obj = findObj(obj);
	
	if (obj) {
		window.clipboardData.setData('Text', obj.value);
	}
}