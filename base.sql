delimiter $$

CREATE TABLE `inflect` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `nominative` varchar(250) NOT NULL,
  `genitive` varchar(250) DEFAULT NULL,
  `dative` varchar(250) DEFAULT NULL,
  `accusative` varchar(250) DEFAULT NULL,
  `instrumental` varchar(250) DEFAULT NULL,
  `prepositional` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8$$

delimiter $$

CREATE TABLE `words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(250) NOT NULL,
  `meta_id` int(11) DEFAULT NULL,
  `type_id` int(2) DEFAULT NULL,
  `inflect_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8$$

