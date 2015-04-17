CREATE TABLE IF NOT EXISTS `PREFIX_registro` (
  `id_registro` int(10) unsigned NOT NULL auto_increment,
  `id_customer` int(10) unsigned NOT NULL,
  `id_cart` int(10) unsigned NOT NULL,
  `amount` decimal(13,6) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  `error_code` varchar(64) character set utf8 NOT NULL,
  PRIMARY KEY  (`id_registro`)
)AUTO_INCREMENT=1;