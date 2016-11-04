-- -----------------------------------------------------
-- Table `#__osmap_sitemaps`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemaps` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL DEFAULT NULL,
  `params` TEXT NULL DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT '0',
  `published` TINYINT(1) NOT NULL DEFAULT '1',
  `created_on` DATETIME NULL DEFAULT NULL,
  `links_count` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `default_idx` (`is_default` ASC, `id` ASC))
ENGINE=INNODB DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `#__osmap_sitemap_menus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemap_menus` (
  `sitemap_id` INT(11) UNSIGNED NOT NULL,
  `menutype_id` INT(11) NOT NULL,
  `changefreq` ENUM('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `priority` FLOAT NOT NULL DEFAULT '0.5',
  `ordering` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sitemap_id`, `menutype_id`),
  INDEX `fk_sitemaps_idx` (`sitemap_id` ASC),
  INDEX `ordering_idx` (`sitemap_id` ASC, `ordering` ASC),
  CONSTRAINT `fk_sitemaps`
    FOREIGN KEY (`sitemap_id`)
    REFERENCES `#__osmap_sitemaps` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE=INNODB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `#__osmap_items_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_items_settings` (
  `sitemap_id` INT(11) UNSIGNED NOT NULL,
  `uid` VARCHAR(100) NOT NULL DEFAULT '',
  `settings_hash` CHAR(32),
  `published` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `changefreq` ENUM('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `priority` FLOAT NOT NULL DEFAULT '0.5',
  `format` TINYINT(1) UNSIGNED DEFAULT '2' COMMENT 'Format of the setting: 1) Legacy Mode - UID Only; 2) Based on menu ID and UID',
  PRIMARY KEY (`sitemap_id`,`uid`,`settings_hash`))
ENGINE=INNODB DEFAULT CHARSET=utf8;
