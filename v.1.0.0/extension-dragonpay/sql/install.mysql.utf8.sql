DROP TABLE IF EXISTS `#__ticketmaster_dragonpay`;

CREATE TABLE IF NOT EXISTS `#__ticketmaster_dragonpay` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `#__ticketmaster_dragonpay` (`field`) VALUES
('public_key'),
('license_data'),
('status');