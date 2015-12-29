<?php
global $wpdb;

$wpdb->query("
  CREATE TABLE `ctc_tutor_qualifications` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `user_ID` int(11) NOT NULL COMMENT 'The ID number of the tutor',
  `course_ID` int(11) NOT NULL COMMENT 'course id',
  PRIMARY KEY (`qid`),
  KEY `tid` (`user_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
$wpdb->query("
  CREATE TABLE `ctc_applications` (
  `application_ID` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL,
  `course_ID` int(10) NOT NULL,
  `frequency` int(11) NOT NULL COMMENT 'The frequency with which the student will be tutored. 1 = once, 2 = weekly, 3 = bi-weekly, 4 = monthly, 5 = bi-monthly',
  `comments` varchar(1024) NOT NULL,
  `submitdate` date NOT NULL,
  `tutor_ID` int(4) DEFAULT NULL,
  `claimDate` date DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`application_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8
");
$wpdb->query("
  CREATE TABLE `ctc_courses` (
  `course_ID` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  PRIMARY KEY (`course_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8
");

$wpdb->query("
CREATE TABLE IF NOT EXISTS `ctc_tutor_status` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `user_ID` int(11) NOT NULL COMMENT 'The ID number of the tutor',
  `isActive` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If true, the tutor is actively looking for new students to teach',
  PRIMARY KEY (`qid`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
");

?>