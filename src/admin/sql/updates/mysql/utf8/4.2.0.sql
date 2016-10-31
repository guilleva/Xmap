-- ============================================================================
-- New Field
ALTER TABLE `#__osmap_items_settings` CHANGE `url_hash` `settings_hash` CHAR(32)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';

-- ============================================================================
-- Rename the index to keep consistency
ALTER TABLE `#__osmap_sitemaps` DROP INDEX `default`;
ALTER TABLE `#__osmap_sitemaps` ADD INDEX `default_idx` (`is_default`);

-- ============================================================================
-- Recreate the table to rename the foreign key and constraint.
-- A long name can cause issues on some servers when it get trimmed and force
-- duplicate names
DROP TABLE IF EXISTS `#__osmap_sitemap_menus_new`;

CREATE TABLE IF NOT EXISTS `#__osmap_sitemap_menus_new` (
  `sitemap_id` int(11) unsigned NOT NULL,
  `menutype_id` int(11) NOT NULL,
  `changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `priority` float NOT NULL DEFAULT '0.5',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sitemap_id`,`menutype_id`),
  KEY `idx_ordering` (`sitemap_id`,`ordering`),
  KEY `idx_sitemap_menus` (`sitemap_id`),
  CONSTRAINT `fk_sitemaps_menus` FOREIGN KEY (`sitemap_id`) REFERENCES `#__osmap_sitemaps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT INTO `#__osmap_sitemap_menus_new` SELECT * FROM `#__osmap_sitemap_menus`;

DROP TABLE `#__osmap_sitemap_menus`;

RENAME TABLE `#__osmap_sitemap_menus_new` TO `#__osmap_sitemap_menus`;

-- ============================================================================
-- Add the column "format" to the item settings table
ALTER TABLE `#__osmap_items_settings` ADD `format` TINYINT(1) UNSIGNED DEFAULT NULL COMMENT 'Format of the setting: 1) Legacy Mode - UID Only; 2) Based on menu ID and UID';
