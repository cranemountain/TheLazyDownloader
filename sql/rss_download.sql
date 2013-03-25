/*
Database - rss_download
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`rss_download` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `rss_download`;

/*Table structure for table `active_downloads` */

DROP TABLE IF EXISTS `active_downloads`;

CREATE TABLE `active_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(255) DEFAULT NULL,
  `started` tinyint(1) DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `link` varchar(512) NOT NULL,
  `download_type` varchar(24) DEFAULT NULL,
  `complete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`,`link`),
  UNIQUE KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `active_downloads` */

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `in_name` varchar(256) DEFAULT NULL,
  `not_in_name` varchar(256) DEFAULT NULL,
  `episode_check` tinyint(1) DEFAULT '0',
  `download_path` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`type`,`in_name`,`not_in_name`,`episode_check`,`download_path`) values (1,'tv','video','1080p,720p,h264,x264,PDTV,HDTV','480p,bluray,bdrip,dvdr,dvd-r,dvdscr,dutch,swesub,ita,german,spanish,polish,french,danish,subbed,flac,ogg,wma,wmv,itouch,ps3,audiobook',1,'/mnt/scratch/complete/tv'),(2,'movies','video','dvdrip,brrip,bdrip,h264,x264','480p,dvd-r,telecine,telesync,cam,ps3,crop,r2,r3,r4,r5,r6,r7,r8,r9',0,'/mnt/scratch/complete/movies'),(3,'mp3','audio',NULL,'480p,720p,1080p,audiobook,ogg,wma,flac,wmv,xvid,divx,dvdr,dvdscr,keygen,-dvbc-,mkv,x264,h264,-sat-,-cable-,-dab-,-sbd-,ebook',0,'/mnt/scratch/complete/mp3'),(4,'audiobooks','audio','audiobook','480p,720p,1080p,ogg,wma,flac,wmv,xvid,divx,dvdr,dvdscr,keygen,-dvbc-,mkv,x264,h264,-sat-,-cable-,-dab-,-sbd-,ebook,german,french,danish',0,'/mnt/scratch/complete/audiobooks'),(5,'other','other',NULL,'480p,720p,1080p,xvid,dvdr,dvd-r,dvdscr,swesub,german,french,danish,subbed,audiobook',0,'/mnt/scratch/complete/other'),(7,'software','software',NULL,'480p,720p,xvid,1080p,dvdr,dvd-r,dvdscr,swesub,german,french,danish,subbed,audiobook',0,'/mnt/scratch/complete/software'),(8,'documentry','video','1080p,720p,h264,x264,PDTV,HDTV','480p,bluray,bdrip,dvdr,dvd-r,dvdscr,dutch,swesub,german,spanish,polish,french,danish,subbed,flac,ogg,wma,wmv,itouch,ps3,audiobook',0,'/mnt/scratch/complete/documentries');

/*Table structure for table `completed_downloads` */

DROP TABLE IF EXISTS `completed_downloads`;

CREATE TABLE `completed_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `download_type` varchar(24) DEFAULT NULL,
  `leech` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`,`title`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `completed_downloads` */

/*Table structure for table `downloads` */

DROP TABLE IF EXISTS `downloads`;

CREATE TABLE `downloads` (
  `id` int(11) unsigned NOT NULL,
  `site` varchar(255) DEFAULT NULL,
  `torrent_downloaded` tinyint(1) DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `original_title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `link` varchar(512) DEFAULT NULL,
  `link_type` varchar(24) DEFAULT NULL,
  `download_complete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  UNIQUE KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `downloads` */

/*Table structure for table `favorites` */

DROP TABLE IF EXISTS `favorites`;

CREATE TABLE `favorites` (
  `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `not_in_name` varchar(64) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `days_between` int(2) DEFAULT '0',
  `last_match` datetime DEFAULT '1970-01-01 00:00:00',
  `added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `email_when_finished` int(1) DEFAULT '0',
  `remove_on_match` int(1) DEFAULT '0',
  `episode_check` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `favorites` */

/*Table structure for table `rss_feeds` */

DROP TABLE IF EXISTS `rss_feeds`;

CREATE TABLE `rss_feeds` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) DEFAULT '1',
  `delay` int(2) DEFAULT '0',
  `type` varchar(24) DEFAULT NULL,
  `leech` tinyint(1) DEFAULT '0',
  `leech_category` varchar(64) DEFAULT NULL,
  `site` varchar(128) DEFAULT NULL,
  `last_hit` timestamp NULL DEFAULT NULL,
  `address` varchar(512) DEFAULT NULL,
  `uploaded` double(10,2) DEFAULT NULL,
  `downloaded` double(10,2) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `rss_feeds` */

/*Table structure for table `str_replace` */

DROP TABLE IF EXISTS `str_replace`;

CREATE TABLE `str_replace` (
  `org` varchar(128) NOT NULL,
  `repl` varchar(128) DEFAULT NULL,
  `ignore_case` tinyint(1) DEFAULT '1',
  `order` int(5) DEFAULT '0',
  PRIMARY KEY (`org`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `str_replace` */

/*Table structure for table `system_settings` */

DROP TABLE IF EXISTS `system_settings`;

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(64) DEFAULT NULL,
  `data` varchar(512) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

/*Data for the table `system_settings` */

insert  into `system_settings`(`id`,`variable`,`data`) values (1,'days_to_keep','6'),(2,'max_torrent_age','21'),(3,'max_torrent_ratio','500'),(4,'hours_to_keep_links','24'),(17,'sabnzbd_autofetch_path','/var/downloads/nzbfiles'),(6,'min_torrent_ratio','1'),(7,'transmission_host','localhost'),(8,'transmission_user','cleartext'),(9,'transmission_pass','cleartext'),(10,'transmission_port','get from transmission installation'),(18,'sabnzbd_port','get from sabnzbd installation'),(12,'sabnzbd_api_key','get from sabnzbd installation'),(13,'sabnzbd_nzb_key','get from sabnzbd installation'),(14,'system_api_key','your choice'),(15,'incomplete_path','/var/downloads/incomplete'),(16,'complete_path','/var/downloads/complete'),(19,'incomplete_path_leech','/var/downloads/incomplete'),(20,'complete_path_leech','/var/downloads/complete/leech'),(21,'rss_feed','http://localhost/torrents/api.php?action=get_rss&key=SYSTEM_API_KEY'),(22,'network_path','\\\\server\\share');

/*Table structure for table `torrents` */

DROP TABLE IF EXISTS `torrents`;

CREATE TABLE `torrents` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feed_id` int(6) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(512) DEFAULT NULL,
  `type` varchar(24) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `torrents` */

/*Table structure for table `torrents_leech` */

DROP TABLE IF EXISTS `torrents_leech`;

CREATE TABLE `torrents_leech` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `site` varchar(255) DEFAULT NULL,
  `feed_id` int(6) DEFAULT NULL,
  `torrent_downloaded` tinyint(1) DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `link` varchar(512) DEFAULT NULL,
  `type` varchar(24) DEFAULT NULL,
  `category` varchar(64) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `torrents_leech` */

/*Table structure for table `transmission` */

DROP TABLE IF EXISTS `transmission`;

CREATE TABLE `transmission` (
  `id` int(11) unsigned NOT NULL,
  `added` timestamp NULL DEFAULT NULL,
  `category` varchar(64) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `tracker` varchar(255) DEFAULT NULL,
  `ratio` double(8,2) DEFAULT NULL,
  `age` double(6,2) DEFAULT NULL,
  `percent_done` double(5,1) DEFAULT NULL,
  `total_size` double(10,2) DEFAULT NULL,
  `downloaded` double(10,2) DEFAULT NULL,
  `uploaded` double(10,2) DEFAULT NULL,
  `leech` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `transmission` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT '0',
  `last_login_time` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT '0',
  `last_type` varchar(32) DEFAULT NULL,
  `days_to_show` int(2) DEFAULT '7',
  `reload` int(6) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`password`,`first_name`,`last_name`,`email`,`locked`,`last_login_time`,`last_login_ip`,`admin`,`last_type`,`days_to_show`,`reload`) values (1,'any','cleartext','John','Doe',NULL,0,'2013-03-25 06:42:49','94.234.170.47',1,'All',1,60);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
