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
 *   @category        CAT_Modules
 *   @package         BCStats
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$LANG = array(
    // database rows
    'count' => 'Anzahl',
    'lastseen' => 'Zuletzt gesehen',
    'maker' => 'Hersteller',
    'name' => 'Name',
    'version' => 'Version',
    // Widgets
    'Africa' => 'Afrika',
    'America' => 'Amerika',
    'Asia' => 'Asien',
    'Australia' => 'Australien',
    'Browsers' => 'Browser',
    'Choose' => 'Wählen',
    'Devices' => 'Geräte',
    'Europe' => 'Europa',
    'Loading...' => 'Lade...',
    'Visitors map' => 'Besucherkarte',
    'Visitors per month' => 'Besucher per Monat',
    'World' => 'Welt',
    // Settings
    '2 columns' => '2 Spalten',
    '3 columns' => '3 Spalten',
    'Charts will make the dashboard load slower, but look nicer' => 'Charts machen das Dashboard langsamer, aber auch attraktiver',
    'Chroma scale' => 'Farbschema',
    'Dashboard layout' => 'Dashboard Layout',
    'Default view for visitors map' => 'Standardansicht für Besucherkarte',
    'If a visitor comes back within this time (in seconds), he will not be counted again.' => 'Kommt ein Besucher innerhalb dieser Zeitspanne (in Sekunden) zurück, wird er nicht erneut gezählt.',
    'Please note: The dashboard will be reset when changing this value! All your settings will be lost!' => 'Hinweis: Das Dashboard wird zurückgesetzt, wenn Sie diesen Wert verändern. Alle Ihre Dashboard-Einstellungen gehen hierbei verloren!',
    'Reload time' => 'Reloadsperre',
    'Show charts' => 'Charts anzeigen',
    'The settings were saved.' => 'Einstellungen gespeichert.',
    // month names
    'January' => 'Januar',
    // Frontend
    'Number of visitors within the last 15 minutes' => 'Anzahl Besucher in den letzten 15 Minuten',
    'Total visitors' => 'Besucher gesamt',
    'Visitors online' => 'Besucher online',
    'Visitors today' => 'Besucher heute',
    'Visitors yesterday' => 'Besucher gestern',
);