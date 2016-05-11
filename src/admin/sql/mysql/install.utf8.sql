-- -----------------------------------------------------
-- Table `#__osmap_sitemap`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemap` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `metadesc` TEXT NULL,
  `metakey` TEXT NULL,
  `params` TEXT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `published` TINYINT(1) NOT NULL DEFAULT 1,
  `created` TIMESTAMP NULL,
  `links_count` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `default` (`is_default` ASC, `id` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `#__osmap_sitemap_menus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemap_menus` (
  `sitemap_id` INT(11) NOT NULL,
  `menutype` VARCHAR(100) NOT NULL,
  `priority` VARCHAR(3) NOT NULL DEFAULT '0.5',
  `changefreq` VARCHAR(7) NOT NULL DEFAULT 'weekly',
  INDEX `fk_osmap_sitemap_menus_osmap_sitemap_idx` (`sitemap_id` ASC),
  PRIMARY KEY (`sitemap_id`, `menutype`),
  CONSTRAINT `fk_osmap_sitemap_menus_osmap_sitemap`
    FOREIGN KEY (`sitemap_id`)
    REFERENCES `#__osmap_sitemap` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `#__osmap_sitemap_hidden`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemap_hidden` (
  `sitemap_id` INT(11) NOT NULL,
  `itemid` INT(11) NOT NULL,
  INDEX `fk_osmap_sitemap_excluded_osmap_sitemap1_idx` (`sitemap_id` ASC),
  PRIMARY KEY (`itemid`, `sitemap_id`),
  CONSTRAINT `fk_osmap_sitemap_excluded_osmap_sitemap1`
    FOREIGN KEY (`sitemap_id`)
    REFERENCES `#__osmap_sitemap` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;
