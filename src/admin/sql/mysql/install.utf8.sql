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
  INDEX `default` (`is_default` ASC, `id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `#__osmap_sitemap_menus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__osmap_sitemap_menus` (
  `sitemap_id` INT(11) UNSIGNED NOT NULL,
  `menutype_id` INT(11) NOT NULL,
  `priority` VARCHAR(3) NOT NULL DEFAULT '0.5',
  `changefreq` ENUM('hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `ordering` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sitemap_id`, `menutype_id`),
  INDEX `fk_osmap_sitemap_menus_osmap_sitemaps_idx` (`sitemap_id` ASC),
  INDEX `ordering` (`sitemap_id` ASC, `ordering` ASC),
  CONSTRAINT `fk_osmap_sitemap_menus_osmap_sitemaps`
    FOREIGN KEY (`sitemap_id`)
    REFERENCES `#__osmap_sitemaps` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;
