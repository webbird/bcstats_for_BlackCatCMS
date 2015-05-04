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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

use phpbrowscap\Browscap;
use GeoIp2\Database\Reader;

if (!class_exists('BCStats_Tracker'))
{
    class BCStats_Tracker
    {
        private static $localpath;
        private static $vendorpath;
        private static $instance;
        private static $geo;     // accessor to GeoIp2 city reader

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
         * accessor to GeoLite2 database; uses the smaller country database by
         * default
         *
         * @access public
         * @return object
         **/
        public static function GeoLite2($detailed=false)
        {
            if(!self::$geo || !is_object(self::$geo))
            {
                $dbfile     = NULL;
                if($detailed)
                {
                    if(file_exists(self::$vendorpath.'/data/GeoIp2-City.mmdb'))
                        $dbfile = CAT_Helper_Directory::sanitizePath(self::$vendorpath.'/data/GeoIp2-City.mmdb');
                    elseif(file_exists(self::$vendorpath.'/data/GeoLite2-City.mmdb'))
                        $dbfile = CAT_Helper_Directory::sanitizePath(self::$vendorpath.'/data/GeoLite2-City.mmdb');
                }
                else
                {
                    if(file_exists(self::$vendorpath.'/data/GeoIp2-Country.mmdb'))
                        $dbfile = CAT_Helper_Directory::sanitizePath(self::$vendorpath.'/data/GeoIp2-Country.mmdb');
                    elseif(file_exists(self::$vendorpath.'/data/GeoLite2-Country.mmdb'))
                        $dbfile = CAT_Helper_Directory::sanitizePath(self::$vendorpath.'/data/GeoLite2-Country.mmdb');
                }
                if(!$dbfile) return false;
                self::$geo = new Reader($dbfile);
            }
            return self::$geo;
        }   // end function GeoLite2()
        

        public static function track($return_stats=false,$display='vertical')
        {
            $ip   = self::getIP();
            $db   = CAT_Helper_DB::getInstance();
$ip = '139.2.51.71';
            // don't track localhost
            if($ip && !( $ip == '127.0.0.1' || substr($ip,0,2) == '0::' ) )
            {
                // only makes sense if we have an IP
                if($ip && !self::is_reload($ip))
                {
                    // get geodata as we don't save the IP
                    $geo  = self::resolveGeoData($ip);
                    $data = array(
                        'ua'      => isset($_SERVER['HTTP_USER_AGENT'])      ? $_SERVER['HTTP_USER_AGENT']      : NULL,
                        'lang'    => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : NULL,
                        'ref'     => isset($_SERVER['HTTP_REFERER'])         ? $_SERVER['HTTP_REFERER']         : NULL,
                        'scheme'  => isset($_SERVER['REQUEST_SCHEME'])       ? $_SERVER['REQUEST_SCHEME']       : NULL,
                        'url'     => isset($_SERVER['SCRIPT_NAME'])          ? $_SERVER['SCRIPT_NAME']          : NULL,
                        'query'   => isset($_SERVER['QUERY_STRING'])         ? $_SERVER['QUERY_STRING']         : NULL,
                        'page_id' => ( defined('PAGE_ID')                    ? PAGE_ID                          : CAT_Helper_Page::getDefaultPage() ),
                    );
                    $db->query(
                        'INSERT INTO `:prefix:mod_bcstats_log` ( `timestamp`, `data` ) VALUES ( ?, ? )',
                        array( time(), serialize(array_merge($data,$geo)) )
                    );
                    // update counter per date; allows to show "visitors today" and "visitors yesterday" on the frontpage
                    $sql = 'INSERT INTO `:prefix:mod_bcstats_visitors` ( `date`, `count` ) '
                         . 'VALUES ( NOW(), "1" ) '
                         . 'ON DUPLICATE KEY UPDATE `count`=`count`+1;';
                    $db->query($sql);

                }
            }

            if($return_stats)
            {
                return self::renderStats($display);
            }
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function renderStats($display='vertical')
        {
            global $parser;

            $db       = CAT_Helper_DB::getInstance();
            $lang     = CAT_Helper_I18n::getInstance();
            $tpl_data = array('today'=>'0','yesterday'=>0,'total'=>0,'online'=>0,'display'=>($display?$display:'vertical'));

            // number of visitors for today and yesterday
            $counter_data = $db->query(
                  'SELECT * FROM `:prefix:mod_bcstats_visitors` '
                . 'WHERE `date`=CURDATE() OR `date`= DATE_SUB(CURDATE(),INTERVAL 1 DAY) '
                . 'ORDER BY `date` ASC'
            )->fetchAll();

            if(count($counter_data))
            {
                $data  = array();
                foreach($counter_data as $i => $item)
                {
                    $data[$item['date']] = $item['count'];
                }
                $today = strftime('%Y-%m-%d',time());
                $date = new DateTime($today);
                $date->modify( '-1 day' );
                $yesterday = $date->format('Y-m-d');

                $tpl_data['today']     = isset($data[$today])     ? $data[$today]     : 0;
                $tpl_data['yesterday'] = isset($data[$yesterday]) ? $data[$yesterday] : 0;
            }

            // total number of visitors
            $counter_data = $db->query(
                'SELECT SUM(`count`) AS `total` FROM `:prefix:mod_bcstats_visitors`'
            )->fetch();

            if(count($counter_data))
            {
                $tpl_data['total'] = $counter_data['total'];
            }

            // visitors online
            $counter_data = $db->query(
                'SELECT COUNT(`id`) AS `online` FROM `:prefix:mod_bcstats_log` WHERE `timestamp` >= UNIX_TIMESTAMP(TIMESTAMPADD(MINUTE,-15,NOW()))'
            )->fetch();

            if(count($counter_data))
            {
                $tpl_data['online'] = $counter_data['online'];
            }

            $parser->setPath(dirname(__FILE__).'/../templates/default');
            $lang->addFile(LANGUAGE.'.php',dirname(__FILE__).'/../languages');
            return $parser->get('stats.tpl',$tpl_data);
        }   // end function renderStats()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function resolveGeoData($ip,$detailed=false)
        {
            $self   = self::getInstance();
            $result = array();
            // we have to suppress GeoIp2 Exceptions here
            try {
                $method = ( $detailed ? 'city' : 'country');
                $record = $self->GeoLite2()->$method($ip);
            }
            catch ( Exception $e ) {}

            if($record)
            {
                $country  = isset($record->country->names[strtolower(LANGUAGE)])
                          ? $record->country->names[strtolower(LANGUAGE)]
                          : $record->country->name
                          ;
                $result = array(
                    'country' => $country,
                    'iso'     => $record->country->isoCode
                );

                if($detailed)
                {
                    $city     = isset($record->city->names[strtolower(LANGUAGE)])
                              ? $record->city->names[strtolower(LANGUAGE)]
                              : $record->city->name
                              ;
                    $province = isset($record->mostSpecificSubdivision->names[strtolower(LANGUAGE)])
                              ? $record->mostSpecificSubdivision->names[strtolower(LANGUAGE)]
                              : $record->mostSpecificSubdivision->name
                              ;
                    $result   = array_merge( $result, array(
                        'subdivision' => $province,
                        'city'        => $city
                    ));

                }

                return $result;
            }
        }   // end function resolveGeoData()
        

        /**
         *
         * @access protected
         * @return
         **/
        protected static function getIP()
        {
            $ip = NULL;
            if (isset($_SERVER['HTTP_CLIENT_IP']))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            elseif (isset($_SERVER['REMOTE_ADDR']))
                $ip = $_SERVER['REMOTE_ADDR'];
            return $ip;
        }   // end function getIP()
        

        /**
         *
         * @access private
         * @return
         **/
        private static function is_reload()
        {
            if (version_compare(phpversion(), '5.4', '<'))
            {
                if(session_id() == '')
                {
                    session_start();
                    return false;
                }
            }
            else
            {
                if (session_status() == PHP_SESSION_NONE)
                {
                    session_start();
                    return false;
                }
            }
            if(isset($_SESSION['_bcstats_']))
            {
                $db       = CAT_Helper_DB::getInstance();
                $data     = $db->query(
                    'SELECT `set_content` FROM `:prefix:mod_bcstats_settings` WHERE `set_name`=?',
                    array('reload_time')
                )->fetchAll();
                $reload_time = $data[0]['set_content'];
                // count visitor again after reload time
                if($reload_time && $_SESSION['_bcstats_'] < ( time() - $reload_time ) )
                {
                    $_SESSION['_bcstats_'] = time();
                    return false;
                }
                return true;
            }
            else
            {
                $_SESSION['_bcstats_'] = time();
                return false;
            }
        }   // end function is_reload()
        

    } // class CAT_Helper_Tracker

} // if class_exists()