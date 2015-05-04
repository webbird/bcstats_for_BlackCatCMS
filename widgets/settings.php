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
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Settings'),
    'preferred_column'       => 1
);

if(!function_exists('render_widget_BCStats_settings'))
{
    function render_widget_BCStats_settings()
    {
        // see https://github.com/gka/chroma.js/wiki/Predefined-Colors
        $supported_scales = array(
            'Spectral', 'Greens', 'Blues', 'Reds', 'Purples', 'Oranges', 'Accent', 'Pastel1', 'Pastel2'
        );

        $settings    = BCStats_Statistics::getSettings();
        $changes     = 0; // for changes that need a redirect
        $db          = CAT_Helper_DB::getInstance();
        $dashboard_support = true;

        if( CAT_Helper_Addons::versionCompare(CAT_VERSION,'1.2','<') )
            $dashboard_support = false;

        // browscap.ini settings cannot be changed if there is already some data
        $can_change_browscapini = true;
        $data = $db->query(
            'SELECT COUNT(*) AS `count` FROM `:prefix:mod_bcstats_browsers`'
        )->fetchAll();

        if(count($data) && isset($data[0]) && isset($data[0]['count']) && $data[0]['count'] > 0 )
            $can_change_browscapini = false;

        if(CAT_Helper_Validate::sanitizePost('action') && CAT_Helper_Validate::sanitizePost('action') == 'settings')
        {
            $newsettings = array();

            // reload
            $newsettings['reload_time'] = CAT_Helper_Validate::sanitizePost('reload_time');
            if(!$newsettings['reload_time'] || $newsettings['reload_time'] > 86400 || $newsettings['reload_time'] < 60 )
                $newsettings['reload_time']  = 3600;
                // browscap.ini to use
                $newsettings['browscapini'] = CAT_Helper_Validate::sanitizePost('browscapini');
                if(!$newsettings['browscapini'])
                    $newsettings['browscapini']      = 'basic';

            // layout
            if($dashboard_support)
            {
                $supported_layouts = CAT_Helper_Dashboard::supportedLayouts();
                $newsettings['preferred_layout'] = CAT_Helper_Validate::sanitizePost('preferred_layout');
                if(!in_array($newsettings['preferred_layout'],array_keys($supported_layouts)))
                    $newsettings['preferred_layout'] = $settings['preferred_layout'];
            }

            // show_charts
            $newsettings['show_charts']          = CAT_Helper_Validate::sanitizePost('show_charts');
            if(!$newsettings['show_charts'] || $newsettings['show_charts'] != 'Y')
                $newsettings['show_charts']      = 'N';
            else
                $newsettings['show_charts']      = 'Y';
                // chart type
                $supported_types = array('pie','doughnut','polar');
                $newsettings['charttype'] = CAT_Helper_Validate::sanitizePost('charttype');
                if(!in_array($newsettings['charttype'],array_keys($supported_types)))
                    $newsettings['charttype'] = $settings['charttype'];

            // chroma scale
            $newsettings['chroma_scale'] = CAT_Helper_Validate::sanitizePost('chroma_scale');
            if(!in_array($newsettings['chroma_scale'],array_keys($supported_scales)))
                $newsettings['chroma_scale'] = $settings['chroma_scale'];

            // map_view
            $newsettings['map_view']             = CAT_Helper_Validate::sanitizePost('map_view');

                foreach(array('reload_time','preferred_layout','show_charts','map_view','chroma_scale','charttype','browscapini') as $key)
            {
                if(isset($newsettings[$key]))
                {
                    $db->query(
                        'UPDATE `:prefix:mod_bcstats_settings` SET `set_content`=? WHERE `set_name`=?',
                        array($newsettings[$key],$key)
                    );
                    if($key == 'map_view') $changes++;
                }
            }

            // reset the dashboard
            if($dashboard_support && isset($newsettings['preferred_layout']) && $newsettings['preferred_layout'] != $settings['preferred_layout'])
            {
                CAT_Helper_Dashboard::resetDashboard( 'BCStats', $newsettings['preferred_layout'] );
                $changes++;
            }

            // reload settings
            $settings    = BCStats_Statistics::getSettings();
        }

        if($changes > 0) {
            $backend = CAT_Backend::getInstance();
            $backend->print_success($backend->lang()->translate('The settings were saved.'),CAT_ADMIN_URL.'/admintools/tool.php?tool=BCStats');
            exit;
        }

        global $parser;
        $parser->setPath(dirname(__FILE__).'/../templates/default');
            return $parser->get('settings.tpl',array(
                'settings'=>$settings,
                'supported_scales'=>$supported_scales,
                'can_change_browscapini'=>$can_change_browscapini,
                'dashboard_support' => $dashboard_support,
            ));
    }
}

if( CAT_Helper_Addons::versionCompare(CAT_VERSION,'1.2','<') )
{
    echo CAT_Helper_I18n::getInstance()->translate('To change the settings, use the BCStats Admin Tool!');
    return;
}