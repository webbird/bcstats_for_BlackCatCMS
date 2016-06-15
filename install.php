<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         BCStats
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// import database structure
sqlImport(file_get_contents(dirname(__FILE__).'/install/structure.sql'),'%prefix%',CAT_TABLE_PREFIX);

// add files to class_secure
$addons_helper = new CAT_Helper_Addons();
foreach(
	array(
		'ajax/ajax_get_countries.php',
        'widgets/visitors_per_month.php'
	)
	as $file
) {
	if ( false === $addons_helper->sec_register_file( 'BCStats', $file ) )
	{
		 error_log( "Unable to register file -$file-!" );
    }
}

// install droplet
CAT_Helper_Droplet::installDroplet(
    dirname(__FILE__).'/install/droplet_cat_counter.zip',
    CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/unzip/')
);

// copy browscap.ini to cache folder
copy(dirname(__FILE__).'/install/basic_php_browscap.ini', CAT_PATH.'/temp/cache/basic_php_browscap.ini');

/**
* extracts SQL statements from a string and executes them as single
* statements
*
* @access public
* @param  string  $import
*
**/
function sqlImport($import,$replace_prefix=NULL,$replace_with=NULL)
{
    global $database;
    $errors = array();
    $import = preg_replace( "%/\*(.*)\*/%Us", ''          , $import );
    $import = preg_replace( "%^--(.*)\n%mU" , ''          , $import );
    $import = preg_replace( "%^$\n%mU"      , ''          , $import );
    if($replace_prefix)
        $import = preg_replace( "~".$replace_prefix."~", $replace_with, $import );
    $import = preg_replace( "%\r?\n%"       , ''          , $import );
    $import = str_replace ( '\\\\r\\\\n'    , "\n"        , $import );
    $import = str_replace ( '\\\\n'         , "\n"        , $import );
    // split into chunks
    $sql = preg_split(
        '~(insert\s+(?:ignore\s+)into\s+|update\s+|replace\s+into\s+|create\s+table|truncate\s+table|delete\s+from)~i',
        $import,
        -1,
        PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
    );
    if(!count($sql) || !count($sql)%2)
        return false;
    // index 1,3,5... is the matched delim, index 2,4,6... the remaining string
    $stmts = array();
    for($i=0;$i<count($sql)-1;$i++)
        $stmts[] = $sql[$i] . $sql[++$i];
    foreach ($stmts as $imp){
        if ($imp != '' && $imp != ' '){
            $ret = $database->query($imp);
            if($database->isError())
                $errors[] = $database->getError();
        }
    }
    if($errors)
        $database->errors = $errors;
    return ( count($errors) ? false : true );
}   // end function sqlImport()