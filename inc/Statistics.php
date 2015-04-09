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
    class BCStats_Statistics
    {
        private static $localpath;
        private static $vendorpath;
        private static $instance;
        private static $bc;       // accessor to Browscap

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance   = new self();
                self::$localpath  = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../');
                self::$vendorpath = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/../vendor');
                set_include_path(get_include_path() . PATH_SEPARATOR . self::$vendorpath);
                spl_autoload_register(function($class) {
                    require self::$vendorpath.'/'.$class.'.php';
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
            $dashboard = CAT_Helper_Dashboard::renderDashboard('BCStats',false);
            $parser->output('tool.tpl',array('dashboard'=>$dashboard));
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
                \Crossjoin\Browscap\Cache\File::setCacheDirectory(CAT_PATH.'/temp/cache');
                // disable automatic updates
                $updater = new \Crossjoin\Browscap\Updater\None();
                \Crossjoin\Browscap\Browscap::setUpdater($updater);
                // set local updater that extends \Crossjoin\Browscap\Updater\AbstractUpdater or
                // \Crossjoin\Browscap\Updater\AbstractUpdaterRemote
                $updater = new \Crossjoin\Browscap\Updater\Local();
                $updater->setOption('LocalFile', CAT_PATH.'/temp/cache/browscap.ini');
                \Crossjoin\Browscap\Browscap::setUpdater($updater);
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
                        $sql = 'INSERT INTO `:prefix:mod_bcstats_browsers` ( `year`, `name`, `version`, `maker`, `count`, `lastseen` ) '
                             . 'VALUES ( ?, ?, ?, ?, "1", ? ) '
                             . 'ON DUPLICATE KEY UPDATE `count`=`count`+1;';

                        $db->query($sql,array(
                            strftime('%Y',$line['timestamp']),
                            $ua->browser,
                            $ua->version,
                            $ua->browser_maker,
                            $line['timestamp']
                        ));

                        // ----- save device stats -----
                        $sql = 'INSERT INTO `:prefix:mod_bcstats_devices` ( `year`, `type`, `platform`, `win64`, `mobile`, `count`, `lastseen` ) '
                             . 'VALUES ( ?, ?, ?, ?, ?, "1", ? ) '
                             . 'ON DUPLICATE KEY UPDATE `count`=`count`+1;';

                        $db->query($sql,array(
                            strftime('%Y',$line['timestamp']),
                            $ua->device_type,
                            $ua->platform,
                            ( $ua->win64          ? 1 : 0 ),
                            ( $ua->ismobiledevice ? 1 : 0 ),
                            $line['timestamp']
                        ));

                    }
                }

                if(isset($data['country']))
                {
                    // ----- save geo data -----
                    $sql = 'INSERT INTO `:prefix:mod_bcstats_countries` ( `year`, `iso`, `country`, `count`, `lastseen` ) '
                         . 'VALUES ( ?, ?, ?, "1", ? ) '
                         . 'ON DUPLICATE KEY UPDATE `count`=`count`+1;';

                    $db->query($sql,array(
                        strftime('%Y',$line['timestamp']),
                        $data['iso'],
                        $data['country'],
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

    }
}