CREATE TABLE  `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mime` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`)
) AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci;
