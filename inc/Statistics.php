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

if (!class_exists('BCStats_Statistics'))
{
    require_once CAT_PATH.'/modules/lib_chartjs/inc/Chart.php';

    class BCStats_Statistics
    {
        private static $localpath;
        private static $vendorpath;
        private static $instance;
        private static $bc;                 // accessor to Browscap
        private static $settings = NULL;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance   = new self();
                self::$localpath  = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../');
                self::$vendorpath = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../vendor');
                set_include_path(get_include_path() . PATH_SEPARATOR . self::$vendorpath);
                spl_autoload_register(function($class) {
                    require self::$vendorpath.'/'.str_replace('\\',DIRECTORY_SEPARATOR,$class).'.php';
                });
            }
            return self::$instance;
        }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * main function; gets the dashboard for the current user
         *
         * @access public
         * @return
         **/
        public static function Dashboard()
        {
            global $parser;
            if( CAT_Helper_Addons::versionCompare(CAT_VERSION,'1.2','>=') )
            {
                $dashboard = CAT_Helper_Dashboard::renderDashboard('BCStats',false);
            }
            else
            {
                $dashboard = self::renderDashboard();
            }
            $parser->output('tool.tpl',array('dashboard'=>$dashboard,'settings'=>self::getSettings()));
        }   // end function showWidgets()

        /**
         * accessor to Browscap
         *
         * @access public
         * @return object
         **/
        public static function browser()
        {
            if(!self::$bc || !is_object(self::$bc))
            {
                $settings = self::getSettings();
                $ini      = ( isset($settings['browscapini']) ? $settings['browscapini'] : 'basic' );
                \Crossjoin\Browscap\Cache\File::setCacheDirectory(CAT_PATH.'/temp/cache');
                // disable automatic updates
                $updater = new \Crossjoin\Browscap\Updater\None();
                \Crossjoin\Browscap\Browscap::setUpdater($updater);
                // set local updater that extends \Crossjoin\Browscap\Updater\AbstractUpdater or
                // \Crossjoin\Browscap\Updater\AbstractUpdaterRemote
                $updater = new \Crossjoin\Browscap\Updater\Local();
                $updater->setOption('LocalFile', CAT_PATH.'/temp/cache/'.$ini.'_php_browscap.ini');
                \Crossjoin\Browscap\Browscap::setUpdater($updater);
                //\Crossjoin\Browscap\Browscap::setDatasetType(\Crossjoin\Browscap\Browscap::DATASET_TYPE_LARGE);
                self::$bc = new \Crossjoin\Browscap\Browscap();
            }

            return self::$bc;
        }   // end function browser()

        /**
         * uses Browscap to resolve the User Agent
         *
         * @access public
         * @return array
         **/
        public static function getBrowserDetails($ua)
        {
            $self   = self::getInstance();
            #$record = $self->browser()->getBrowser($ua,true); // returns array
            $record = $self->browser()->getBrowser($ua)->getData(); // returns object
            if($record) return $record;
            return $ua;
        }   // end function getBrowserDetails()

        /**
         *
         * @access public
         * @return
         **/
        public static function getSettings()
        {
            if(!self::$settings)
            {
                $db       = CAT_Helper_DB::getInstance();
                $data     = $db->query(
                    'SELECT * FROM `:prefix:mod_bcstats_settings`'
                )->fetchAll();
                self::$settings = array();
                foreach($data as $item)
                {
                    self::$settings[$item['set_name']] = $item['set_content'];
                }
            }
            return self::$settings;
        }   // end function getSettings()

        /**
         *
         * @access private
         * @return
         **/
        public static function analyzeLog()
        {
            // get all data from the log
            $db       = CAT_Helper_DB::getInstance();
            $self     = self::getInstance();

            $logdata  = $db->query(
                'SELECT * FROM `:prefix:mod_bcstats_log`'
            )->fetchAll();

            foreach($logdata as $line)
            {
                $data = @unserialize($line['data']);
                if(isset($data['ua']))
                {
                    $ua = self::getBrowserDetails($data['ua']);
                    if(is_object($ua))
                    {
                        // ----- save browser stats -----
                        $sql = 'INSERT INTO `:prefix:mod_bcstats_browsers` ( `year`, `name`, `version`, `maker`, `type`, `count`, `lastseen` ) '
                             . 'VALUES ( ?, ?, ?, ?, ?, "1", ? ) '
                             . 'ON DUPLICATE KEY UPDATE `count`=`count`+1, `lastseen`=?;';

                        $db->query($sql,array(
                            strftime('%Y',$line['timestamp']),
                            $ua->browser,
                            $ua->version,
                            ( property_exists ($ua,'browser_maker') ? $ua->browser_maker : '-' ),
                            ( property_exists ($ua,'browser_type')  ? $ua->browser_type  : '-' ),
                            $line['timestamp'],
                            $line['timestamp']
                        ));

                        // ----- save device stats -----
                        $sql = 'INSERT INTO `:prefix:mod_bcstats_devices` ( `year`, `type`, `platform`, `win64`, `mobile`, `count`, `lastseen` ) '
                             . 'VALUES ( ?, ?, ?, ?, ?, "1", ? ) '
                             . 'ON DUPLICATE KEY UPDATE `count`=`count`+1, `lastseen`=?;';

                        $db->query($sql,array(
                            strftime('%Y',$line['timestamp']),
                            $ua->device_type,
                            $ua->platform,
                            ( (property_exists ($ua,'win64')          && $ua->win64 )          ? 1 : 0 ),
                            ( (property_exists ($ua,'ismobiledevice') && $ua->ismobiledevice ) ? 1 : 0 ),
                            $line['timestamp'],
                            $line['timestamp']
                        ));

                    }
                }

                if(isset($data['country']))
                {
                    // ----- save geo data -----
                    $sql = 'INSERT INTO `:prefix:mod_bcstats_countries` ( `year`, `iso`, `country`, `count`, `lastseen` ) '
                         . 'VALUES ( ?, ?, ?, "1", ? ) '
                         . 'ON DUPLICATE KEY UPDATE `count`=`count`+1, `lastseen`=?;';

                    $db->query($sql,array(
                        strftime('%Y',$line['timestamp']),
                        $data['iso'],
                        $data['country'],
                        $line['timestamp'],
                        $line['timestamp']
                    ));
                }

                // ----- page view -----
                if(isset($data['page_id']))
                {
                    $sql = 'INSERT INTO `:prefix:mod_bcstats_pages` ( `year`, `page_id`, `count`, `lastseen` ) '
                         . 'VALUES ( ?, ?, "1", ? ) '
                         . 'ON DUPLICATE KEY UPDATE `count`=`count`+1, `lastseen`=?;';

                    $db->query($sql,array(
                        strftime('%Y',$line['timestamp']),
                        $data['page_id'],
                        $line['timestamp'],
                        $line['timestamp']
                    ));
                }

                // remove from log
                $db->query(
                    'DELETE FROM `:prefix:mod_bcstats_log` WHERE `id`=?',
                    array($line['id'])
                );
            }
        }   // end function analyzeLog()

// *****************************************************************************
// *****           BACKWARD COMPATIBILITY FUNCTIONS          *******************
// ***** this is for BC < v1.2 only and can be removed later *******************
// *****************************************************************************

        /**
         * adds JS and CSS to the footer for global dashboard
         *
         * @access public
         * @return void
         **/
        public static function addFooterFiles()
        {
            if(!defined('__BCStats_Headers_Added'))
            {
                CAT_Helper_Page::addCSS(CAT_URL.'/modules/BCStats/css/backend.css');
                global $mod_footers;
                include dirname(__FILE__).'/../footers.inc.php';
                foreach($mod_footers['backend']['js'] as $file)
                {
                    CAT_Helper_Page::addJS($file,'backend','footer');
                }
                CAT_Helper_Page::addJS('/modules/BCStats/js/backend_body.js','backend','footer');
                define('__BCStats_Headers_Added',true);
            }
        }

        /**
         * this method is defined in CAT_Helper_Array on BlackCat v1.2
         **/
        public static function ArrayFilterByKey(&$array, $key, $value)
        {
            if(!method_exists('CAT_Helper_Array','ArrayFilterByKey'))
            {
                $result = array();
                foreach ($array as $k => $elem) {
                    if (isset($elem[$key]) && $elem[$key] == $value) {
                        $result[] = $array[$k];
                        unset($array[$k]);
                    }
                }
                return $result;
            } else {
                return CAT_Helper_Array::ArrayFilterByKey($array,$key,$value);
            }
        }   // end function ArrayFilterByKey()

        /**
         * render the dashboard
         *
         * @access private
         * @return string
         **/
        private static function renderDashboard()
        {
            global $parser;

            $dashboard = '<div class="fc_info">'
                       . CAT_Helper_Validate::getInstance()->lang()->translate(
                             'Sorry, but the BCStats backend requires BlackCat CMS v1.2 to work correctly. You can view the widgets here, but not manage the dashboard configuration.'
                         )
                       . '</div>';

            // get widgets
            $widgets   = CAT_Helper_Directory::getInstance()
                       ->maxRecursionDepth(1)
                       ->setSkipFiles(array('index.php','widgets.config.php'))
                       ->getPHPFiles(CAT_PATH.'/modules/BCStats/widgets');

            $rendered  = array();

            foreach( $widgets as $widget )
            {
                $path  = pathinfo(CAT_Helper_Directory::sanitizePath($widget),PATHINFO_DIRNAME);
                $wname = pathinfo(CAT_Helper_Directory::sanitizePath($widget),PATHINFO_FILENAME);
                $info  = $content = NULL;

                if ( file_exists($path.'/info.php') )
                {
                    $info = CAT_Helper_Addons::checkInfo($path);
                }

                // ignore the error messages thrown for global dashboard
                ob_start();
                    $widget_settings = array();
                    include $widget;
                ob_clean();

                $widget_func  = 'render_widget_BCStats_'.$wname;
                $rendered[]   = $parser->get('widget.tpl',array('content'=>$widget_func(),'widget_title'=>(isset($widget_settings['widget_title'])?$widget_settings['widget_title']:$wname)));
            }

            $half = ceil(count($rendered)/2);

            return $parser->get('dashboard.tpl',array('widgets'=>array_splice($rendered,0,$half)))
                .  $parser->get('dashboard.tpl',array('widgets'=>$rendered));

        }   // end function renderDashboard()

    }
}