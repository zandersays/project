SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `databaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `databaseName` ;

-- -----------------------------------------------------
-- Table `databaseName`.`locale`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`locale` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`locale` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  `short_name` VARCHAR(8) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `status` ENUM('unverified','active','banned','deactivated') NOT NULL DEFAULT 'unverified' ,
  `username` VARCHAR(32) NOT NULL ,
  `password` VARCHAR(32) NOT NULL ,
  `meta` TEXT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`content`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`content` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`content` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `locale_id` INT NOT NULL ,
  `user_id` INT NULL ,
  `status` ENUM('active') NOT NULL ,
  `key` VARCHAR(64) NOT NULL ,
  `value` TEXT NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_content_locale` (`locale_id` ASC) ,
  INDEX `fk_content_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_content_locale`
    FOREIGN KEY (`locale_id` )
    REFERENCES `databaseName`.`locale` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_content_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`content_comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`content_comment` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`content_comment` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `status` ENUM('active') NOT NULL ,
  `comment` TEXT NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_content_comment_content1` (`content_id` ASC) ,
  INDEX `fk_content_comment_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_content_comment_content1`
    FOREIGN KEY (`content_id` )
    REFERENCES `databaseName`.`content` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_content_comment_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_password_reset`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_password_reset` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_password_reset` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `status` ENUM('available','viewed','consumed') NOT NULL DEFAULT 'available' ,
  `key` VARCHAR(32) NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_password_reset_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_password_reset_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_email_verification`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_email_verification` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_email_verification` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `status` ENUM('available','consumed') NOT NULL ,
  `key` VARCHAR(32) NOT NULL ,
  `old_email` VARCHAR(128) NOT NULL ,
  `new_email` VARCHAR(128) NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_email_change_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_email_change_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_login_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_login_log` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_login_log` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NULL ,
  `status` ENUM('success','failure') NOT NULL ,
  `identifier` VARCHAR(128) NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_login_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_login_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_type` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_type_id` INT NOT NULL ,
  `owner_user_id` INT NULL ,
  `status` ENUM('active','deactivated') NOT NULL ,
  `name` VARCHAR(128) NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_account_type1` (`account_type_id` ASC) ,
  INDEX `fk_account_user1` (`owner_user_id` ASC) ,
  CONSTRAINT `fk_account_account_type1`
    FOREIGN KEY (`account_type_id` )
    REFERENCES `databaseName`.`account_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_user1`
    FOREIGN KEY (`owner_user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_api_key`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_api_key` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_api_key` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_id` INT NOT NULL ,
  `key` VARCHAR(128) NOT NULL ,
  `privileges` TEXT NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_api_key_account1` (`account_id` ASC) ,
  CONSTRAINT `fk_account_api_key_account1`
    FOREIGN KEY (`account_id` )
    REFERENCES `databaseName`.`account` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`api_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`api_log` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`api_log` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_api_key_id` INT NULL ,
  `api` VARCHAR(64) NOT NULL ,
  `command` VARCHAR(128) NOT NULL ,
  `status` VARCHAR(32) NULL ,
  `response` TEXT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_api_history_user_api_key1` (`user_api_key_id` ASC) ,
  CONSTRAINT `fk_api_history_user_api_key1`
    FOREIGN KEY (`user_api_key_id` )
    REFERENCES `databaseName`.`account_api_key` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_account`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_account` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_account` (
  `id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `account_id` INT NOT NULL ,
  `status` ENUM('active','deactivated') NOT NULL ,
  `permissions` TEXT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_account_user1` (`user_id` ASC) ,
  INDEX `fk_user_account_account1` (`account_id` ASC) ,
  CONSTRAINT `fk_user_account_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_account_account1`
    FOREIGN KEY (`account_id` )
    REFERENCES `databaseName`.`account` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_type_feature`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_type_feature` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_type_feature` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_type_id` INT NOT NULL ,
  `name` VARCHAR(64) NOT NULL ,
  `description` TEXT NULL ,
  `management_route` VARCHAR(128) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_type_feature_account_type1` (`account_type_id` ASC) ,
  CONSTRAINT `fk_account_type_feature_account_type1`
    FOREIGN KEY (`account_type_id` )
    REFERENCES `databaseName`.`account_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_type_permission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_type_permission` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_type_permission` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_type_id` INT NOT NULL ,
  `parent_account_type_permission_id` INT NULL ,
  `account_type_feature_id` INT NULL ,
  `status` ENUM('locked','unlocked') NOT NULL ,
  `name` VARCHAR(128) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_type_permission_account_type1` (`account_type_id` ASC) ,
  INDEX `fk_account_type_permission_account_type_permission1` (`parent_account_type_permission_id` ASC) ,
  INDEX `fk_account_type_permission_account_type_feature1` (`account_type_feature_id` ASC) ,
  CONSTRAINT `fk_account_type_permission_account_type1`
    FOREIGN KEY (`account_type_id` )
    REFERENCES `databaseName`.`account_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_type_permission_account_type_permission1`
    FOREIGN KEY (`parent_account_type_permission_id` )
    REFERENCES `databaseName`.`account_type_permission` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_type_permission_account_type_feature1`
    FOREIGN KEY (`account_type_feature_id` )
    REFERENCES `databaseName`.`account_type_feature` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_account_permission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_account_permission` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_account_permission` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_account_id` INT NOT NULL ,
  `account_type_permission_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_account_permission_user_account1` (`user_account_id` ASC) ,
  INDEX `fk_user_account_permission_account_type_permission1` (`account_type_permission_id` ASC) ,
  CONSTRAINT `fk_user_account_permission_user_account1`
    FOREIGN KEY (`user_account_id` )
    REFERENCES `databaseName`.`user_account` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_account_permission_account_type_permission1`
    FOREIGN KEY (`account_type_permission_id` )
    REFERENCES `databaseName`.`account_type_permission` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_type_role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_type_role` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_type_role` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_type_id` INT NOT NULL ,
  `name` VARCHAR(64) NOT NULL ,
  `description` TEXT NULL ,
  `permissions` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_type_role_account_type1` (`account_type_id` ASC) ,
  CONSTRAINT `fk_account_type_role_account_type1`
    FOREIGN KEY (`account_type_id` )
    REFERENCES `databaseName`.`account_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_account_role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_account_role` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_account_role` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_account_id` INT NOT NULL ,
  `account_type_role_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_account_role_user_account1` (`user_account_id` ASC) ,
  INDEX `fk_user_account_role_account_type_role1` (`account_type_role_id` ASC) ,
  CONSTRAINT `fk_user_account_role_user_account1`
    FOREIGN KEY (`user_account_id` )
    REFERENCES `databaseName`.`user_account` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_account_role_account_type_role1`
    FOREIGN KEY (`account_type_role_id` )
    REFERENCES `databaseName`.`account_type_role` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`account_type_role_permission`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`account_type_role_permission` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`account_type_role_permission` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `account_type_role_id` INT NOT NULL ,
  `account_type_permission_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_account_type_role_permission_account_type_role1` (`account_type_role_id` ASC) ,
  INDEX `fk_account_type_role_permission_account_type_permission1` (`account_type_permission_id` ASC) ,
  CONSTRAINT `fk_account_type_role_permission_account_type_role1`
    FOREIGN KEY (`account_type_role_id` )
    REFERENCES `databaseName`.`account_type_role` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_type_role_permission_account_type_permission1`
    FOREIGN KEY (`account_type_permission_id` )
    REFERENCES `databaseName`.`account_type_permission` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`error_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`error_log` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`error_log` (
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`warning_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`warning_log` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`warning_log` (
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`settings_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`settings_log` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`settings_log` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `action` TEXT NOT NULL ,
  `ip_added_by` INT(20) UNSIGNED NOT NULL ,
  `time_added` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_settings_log_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_settings_log_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `databaseName`.`user_email`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `databaseName`.`user_email` ;

CREATE  TABLE IF NOT EXISTS `databaseName`.`user_email` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `status` ENUM('primary','secondary') NOT NULL ,
  `email` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_email_user1` (`user_id` ASC) ,
  CONSTRAINT `fk_user_email_user1`
    FOREIGN KEY (`user_id` )
    REFERENCES `databaseName`.`user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `databaseName`.`account_type`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `databaseName`;
INSERT INTO `databaseName`.`account_type` (`id`, `name`) VALUES ('1', 'Project');

COMMIT;

-- -----------------------------------------------------
-- Data for table `databaseName`.`account`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `databaseName`;
INSERT INTO `databaseName`.`account` (`id`, `account_type_id`, `owner_user_id`, `status`, `name`, `time_added`) VALUES ('1', '1', NULL, 'active', 'Project', '0');

COMMIT;

-- -----------------------------------------------------
-- Data for table `databaseName`.`account_type_role`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `databaseName`;
INSERT INTO `databaseName`.`account_type_role` (`id`, `account_type_id`, `name`, `description`, `permissions`) VALUES ('1', '1', 'Administrator', 'Administers all aspects of Project deployment.', '{}');

COMMIT;