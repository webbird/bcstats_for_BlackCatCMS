CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_browsers` (
	`year` CHAR(4) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	`version` VARCHAR(50) NOT NULL,
	`type` VARCHAR(50) NULL DEFAULT NULL,
	`maker` VARCHAR(50) NOT NULL,
	`count` INT(11) UNSIGNED NOT NULL DEFAULT '1',
	`lastseen` VARCHAR(50) NOT NULL,
	UNIQUE INDEX `year_name_version` (`year`, `name`, `version`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_countries` (
  `year` char(4) NOT NULL,
  `iso` char(2) NOT NULL,
  `country` varchar(50) NOT NULL,
  `count` int(11) unsigned NOT NULL,
  `lastseen` varchar(50) NOT NULL,
  UNIQUE INDEX `year_iso` (`year`, `iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_devices` (
  `year` char(4) NOT NULL,
  `type` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `win64` enum('0','1') NOT NULL,
  `mobile` enum('0','1') NOT NULL,
  `tablet` enum('0','1') NOT NULL,
  `count` int(11) unsigned NOT NULL,
  `lastseen` varchar(50) NOT NULL,
  UNIQUE KEY `type_platform_64bit` (`type`,`platform`,`win64`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` varchar(25) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `set_name` varchar(50) NOT NULL DEFAULT '0',
  `set_content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_visitors` (
  `date` date NOT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_bcstats_pages` (
  `year` char(4) NOT NULL,
  `page_id` int(11) unsigned NOT NULL,
  `count` int(11) unsigned NOT NULL,
  `lastseen` varchar(50) NOT NULL,
  UNIQUE KEY `year_page_id` (`year`,`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (2, 'reload_time', '3600');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (3, 'show_charts', 'Y');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (4, 'preferred_layout', '50-50');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (5, 'map_view', 'europe');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (6, 'chroma_scale', 'Spectral');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (7, 'charttype', 'pie');
INSERT INTO `cat_mod_bcstats_settings` (`id`, `set_name`, `set_content`) VALUES (8, 'browscapini', 'basic');
