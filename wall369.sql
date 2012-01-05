CREATE TABLE IF NOT EXISTS `wall369_comment` (
`comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`user_id` int(10) unsigned NOT NULL,
`comment_content` text NOT NULL,
`comment_datecreated` datetime NOT NULL,
PRIMARY KEY (`comment_id`),
KEY `post_id` (`post_id`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

CREATE TABLE IF NOT EXISTS `wall369_photo` (
`photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`post_id` int(10) unsigned NOT NULL,
`photo_file` varchar(100) NOT NULL,
PRIMARY KEY (`photo_id`),
KEY `post_id` (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;

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
`user_file` varchar(100) NOT NULL,
`user_datecreated` datetime NOT NULL,
PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;