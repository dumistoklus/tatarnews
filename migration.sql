CREATE TABLE IF NOT EXISTS `fms_banip` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `fms_articles` ADD  `third_col` ENUM(  '0',  '1' ) NOT NULL;