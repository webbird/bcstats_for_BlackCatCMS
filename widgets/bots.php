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
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Bots and Crawlers'),
    'preferred_column'       => 3
);

if(!function_exists('render_widget_BCStats_bots'))
{
    function render_widget_BCStats_bots()
    {
        global $parser;
        require_once dirname(__FILE__).'/../inc/Statistics.php';

        $db       = CAT_Helper_DB::getInstance();
        $browsers = $db->query(
            'SELECT * FROM `:prefix:mod_bcstats_browsers` WHERE `year`=YEAR(NOW()) AND `type`=? ORDER BY `count` DESC, `name` ASC, `version` DESC',
            array('Bot/Crawler')
        )->fetchAll();

        $chart    = NULL;
        $settings = BCStats_Statistics::getSettings();

        require_once CAT_PATH.'/modules/lib_chartjs/inc/Chart.php';
        $result = lib_chartjs_Chart::prepareData(
            array(
                'data'     => $browsers,
                'group_by' => 'name',
                'converts' => array(
                    'lastseen'  => 'CAT_Helper_DateTime::getDateTime'
                ),
                'internals' => array(
                    'summarize' => array( 'key' => 'count', 'return_as' => 'sum' ),
                    'title'     => array( 'key' => 'name', 'additionals' => array('maker') )
                )
            )
        );

        if($settings['show_charts'] == 'Y')
        {
            $type  = $settings['charttype'];
            $func  = 'get'.ucfirst($type).'chart';
            $chart = lib_chartjs_Chart::$func(
                array(
                    'data'        => $result,
                    'id'          => 'botChart',
                    'color_scale' => $settings['chroma_scale'],
                    'color_by'    => 'count',
                    'group_by'    => 'name',
                )
            );
        }

        $parser->setPath(dirname(__FILE__).'/../templates/default');
        return $parser->get('browsers.tpl',array('browsers'=>$result,'chart'=>$chart));
    }
}

if( CAT_Helper_Addons::versionCompare(CAT_VERSION,'1.2','<') )
{
    $widget_name = CAT_Helper_I18n::getInstance()->translate('Bots and Crawlers');
    require_once dirname(__FILE__).'/../inc/Statistics.php';
    BCStats_Statistics::addFooterFiles();
    echo render_widget_BCStats_bots();
}