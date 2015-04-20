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
    'allow_global_dashboard' => false,
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Visitors per month'),
    'preferred_column'       => 1
);

global $parser;
require_once dirname(__FILE__).'/../inc/Statistics.php';

$db       = CAT_Helper_DB::getInstance();
$lang     = CAT_Helper_I18n::getInstance();
$from     = new DateTime('first day of this month');
$to       = new DateTime('last day of this month');
$visitors = $db->query(
    'SELECT * FROM `:prefix:mod_bcstats_visitors` WHERE `date` BETWEEN ? AND ?',
    array( $from->format('Y-m-d'), $to->format('Y-m-d') )
)->fetchAll();

$month   = CAT_Helper_Validate::sanitizeGet('month') ?: date('m');
$year    = CAT_Helper_Validate::sanitizeGet('year')  ?: date('Y');
$days    = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first   = gmmktime(0,0,0,$month,1,$year);
$weekday = gmstrftime('%w',$first);
$set     = array();
$labels  = range(1,$days);

// get all years we have data for
$oldest = $db->query(
    'SELECT `date` FROM `:prefix:mod_bcstats_visitors` ORDER BY `date` ASC LIMIT 1'
)->fetchAll();
$latest = $db->query(
    'SELECT `date` FROM `:prefix:mod_bcstats_visitors` ORDER BY `date` DESC LIMIT 1'
)->fetchAll();

$oldest_date = new DateTime($oldest[0]['date']);
$latest_date = new DateTime($latest[0]['date']);
$years       = range($oldest_date->format('Y'),$latest_date->format('Y'));
$months      = array();

foreach(range(1,12) as $mon)
{
    $months[$mon] = strftime('%B',gmmktime(0,0,0,$mon,1,$year));
}

foreach(range(1,$days) as $day)
{
    $date  = new DateTime($day.'.'.$month.'.'.$year);
    $items = CAT_Helper_Array::ArrayFilterByKey($visitors, 'date', $date->format('Y-m-d'));
    if(is_array($items) && count($items))
    {
        $set[$day] = $items[0]['count'];
    }
    else
    {
        $set[$day] = 0;
    }
}

$chart    = NULL;
$output   = NULL;
$settings = BCStats_Statistics::getSettings();

if($settings['show_charts'] == 'Y')
{
    require_once CAT_PATH.'/modules/lib_chartjs/inc/Chart.php';
    $chart = '        <span class="monthname">'.CAT_Helper_I18n::getInstance()->translate(strftime('%B',time())).' '.$year.'</span><br />'
           . lib_chartjs_Chart::getLinechart(array('datasets'=>array('Visitors'=>$set)),'visitorsChart',$labels,$settings['chroma_scale']);
    if(CAT_Helper_Validate::sanitizeGet('_cat_ajax'))
    {
        echo json_encode(array('type'=>'chart','content'=>$chart));
        exit();
    }
}
else
{
    // Sunday is 0, so we do some math for Monday as first day of week
    $weekday = ($weekday + 7 - 1) % 7;

    $output = '<tr>';
    if($weekday >= 1 )
        $output .= '<td colspan="'.$weekday.'" class="before">&nbsp;</td>';
    $day_of_week = $weekday;

    foreach($set as $day => $count)
    {
        if($day_of_week == 7)
        {
            $output .= '</tr><tr>';
            $day_of_week = 0;
        }
        $output .= '<td'
                .  ( ($count > 0) ? ' class="has_data"' : '' )
                .  '><strong>'.$day.'</strong><br />'.$count.'</td>'
                ;
        $day_of_week += 1;
    }

    if($day_of_week < 7)
        $output .='<td colspan="'.(7-$day_of_week).'" class="after">&nbsp;</td>';
    $output .= '</tr>';

    if(CAT_Helper_Validate::sanitizeGet('_cat_ajax'))
    {
        echo json_encode(array('type'=>'table','content'=>$output));
        exit();
    }
}



$parser->setPath(dirname(__FILE__).'/../templates/default');
$parser->output('visitors_per_month.tpl',array(
    'chart'     => $chart,
    'years'     => $years,
    'months'    => $months,
    'calsheet'  => $output,
    'monthname' => strftime('%B',time()),
    'year'      => $year
));
