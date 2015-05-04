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

$widget_settings = array(
    'allow_global_dashboard' => true,
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Visitors per page'),
    'preferred_column'       => 1
);

if(!function_exists('render_widget_BCStats_visitors_per_page'))
{
    function render_widget_BCStats_visitors_per_page()
    {
        global $parser;
        require_once dirname(__FILE__).'/../inc/Statistics.php';

        $db       = CAT_Helper_DB::getInstance();
        $lang     = CAT_Helper_I18n::getInstance();
        $year     = CAT_Helper_Validate::sanitizeGet('year')  ?: date('Y');

        $visitors = $db->query(
            'SELECT `t1`.`page_id`, `t1`.`count`, `t1`.`lastseen`, `t2`.`menu_title` AS `title`, `t2`.`link` FROM `:prefix:mod_bcstats_pages` AS `t1` '.
            'LEFT OUTER JOIN `:prefix:pages` AS `t2` '.
            'ON `t1`.`page_id`=`t2`.`page_id` '.
            'WHERE `year`=? ORDER BY `count` DESC, `t1`.`page_id` ASC',
            array($year)
        )->fetchAll();

        if(count($visitors))
        {
            foreach($visitors as &$item)
            {
                $item['lastseen'] = CAT_Helper_DateTime::getDateTime($item['lastseen']);
            }
        }

        $parser->setPath(dirname(__FILE__).'/../templates/default');
        return $parser->get('visitors_per_page.tpl',array('visitors'=>$visitors));
    }
}

if( CAT_Helper_Addons::versionCompare(CAT_VERSION,'1.2','<') )
{
    $widget_name = CAT_Helper_I18n::getInstance()->translate('Visitors per page');
    require_once dirname(__FILE__).'/../inc/Statistics.php';
    BCStats_Statistics::addFooterFiles();
    echo render_widget_BCStats_visitors_per_page();
}