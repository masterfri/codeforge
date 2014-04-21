CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salt` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date_created` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`(4)),
  KEY `username` (`username`(4))
) AUTO_INCREMENT=1 CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user` VALUES 
	(1,'admin@example.com','admin','235c3072d3dd58d88ed495cb746b7fe4','lqrDqJ7TCVenHcr','admin',1,NULL);
