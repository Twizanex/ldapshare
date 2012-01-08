CREATE TABLE IF NOT EXISTS `wall369_comment` (
`comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`user_id` int(10) unsigned NOT NULL,
`comment_content` text NOT NULL,
`comment_datecreated` datetime NOT NULL,
PRIMARY KEY (`comment_id`),
KEY `post_id` (`post_id`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_like` (
`like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`user_id` int(10) unsigned NOT NULL,
`like_datecreated` datetime NOT NULL,
PRIMARY KEY (`like_id`),
KEY `post_id` (`post_id`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_link` (
`link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`link_url` varchar(255) DEFAULT NULL,
`link_title` varchar(255) NOT NULL,
`link_image` varchar(255) DEFAULT NULL,
`link_icon` varchar(255) DEFAULT NULL,
`link_content` text,
`link_datecreated` datetime NOT NULL,
PRIMARY KEY (`link_id`),
KEY `post_id` (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_photo` (
`photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`photo_file` varchar(100) NOT NULL,
`photo_datecreated` datetime NOT NULL,
PRIMARY KEY (`photo_id`),
KEY `post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_post` (
`post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(10) unsigned NOT NULL,
`post_content` text NOT NULL,
`post_httpuseragent` varchar(255) DEFAULT NULL,
`post_remoteaddr` varchar(255) DEFAULT NULL,
`post_datecreated` datetime NOT NULL,
PRIMARY KEY (`post_id`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_user` (
`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`user_email` varchar(255) NOT NULL,
`user_lastname` varchar(255) NOT NULL,
`user_firstname` varchar(255) NOT NULL,
`user_file` varchar(100) DEFAULT NULL,
`user_datecreated` datetime NOT NULL,
PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

INSERT INTO `wall369_user` (`user_id`, `user_email`, `user_lastname`, `user_firstname`, `user_file`, `user_datecreated`) VALUES
(1001, 'email1', 'Lastname1', 'Firstname1', NULL, NOW()),
(1002, 'email2', 'Lastname2', 'Firstname2', NULL, NOW()),
(1003, 'email3', 'Lastname3', 'Firstname3', NULL, NOW()),
(1004, 'email4', 'Lastname4', 'Firstname4', NULL, NOW()),
(1005, 'email5', 'Lastname5', 'Firstname5', NULL, NOW()),
(1006, 'email6', 'Lastname6', 'Firstname6', NULL, NOW()),
(1007, 'email7', 'Lastname7', 'Firstname7', NULL, NOW()),
(1008, 'email8', 'Lastname8', 'Firstname8', NULL, NOW()),
(1009, 'email9', 'Lastname9', 'Firstname9', NULL, NOW()),
(1010, 'email10', 'Lastname10', 'Firstname10', NULL, NOW())
