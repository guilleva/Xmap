
DROP TABLE IF EXISTS `#__xmap_sitemap`;

CREATE TABLE `#__xmap_sitemap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `introtext` text DEFAULT NULL,
  `metadesc` text DEFAULT NULL,
  `metakey` text DEFAULT NULL,
  `attribs` text DEFAULT NULL,
  `selections` text DEFAULT NULL,
  `excluded_items` text DEFAULT NULL,
  `is_default` int(1) DEFAULT 0,
  `state` int(2) DEFAULT NULL,
  `access` int DEFAULT NULL,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `count_xml` int(11) DEFAULT NULL,
  `count_html` int(11) DEFAULT NULL,
  `views_xml` int(11) DEFAULT NULL,
  `views_html` int(11) DEFAULT NULL,
  `lastvisit_xml` int(11) DEFAULT NULL,
  `lastvisit_html` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
