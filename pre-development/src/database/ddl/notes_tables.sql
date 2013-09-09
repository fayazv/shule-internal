SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `shuledirect` ;
CREATE SCHEMA IF NOT EXISTS `shuledirect` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `shuledirect` ;

-- -----------------------------------------------------
-- Table `shuledirect`.`note_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`note_types` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`note_types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(20) NOT NULL ,
  `depth` INT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`languages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`languages` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`languages` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `language` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`notes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`notes` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`notes` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content` VARCHAR(2047) NOT NULL ,
  `position` INT NOT NULL ,
  `note_type_id` INT NOT NULL ,
  `parent_notes_id` INT NULL ,
  `language_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `note_types_idx` (`note_type_id` ASC) ,
  INDEX `fk_notes_parent_notes_id` (`parent_notes_id` ASC) ,
  INDEX `fk_notes_language_id` (`language_id` ASC) ,
  CONSTRAINT `fk_notes_note_types_id`
    FOREIGN KEY (`note_type_id` )
    REFERENCES `shuledirect`.`note_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_notes_parent_notes_id`
    FOREIGN KEY (`parent_notes_id` )
    REFERENCES `shuledirect`.`notes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_notes_language_id`
    FOREIGN KEY (`language_id` )
    REFERENCES `shuledirect`.`languages` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`tags` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`tags` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `notes_id` INT NOT NULL ,
  `content` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `notes_id_idx` (`notes_id` ASC) ,
  CONSTRAINT `fk_tags_notes_id`
    FOREIGN KEY (`notes_id` )
    REFERENCES `shuledirect`.`notes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`media_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`media_types` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`media_types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `type` VARCHAR(100) NOT NULL ,
  `is_printable` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`media` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`media` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `notes_id` INT NOT NULL ,
  `content` VARCHAR(511) NOT NULL ,
  `description` VARCHAR(511) NULL ,
  `media_type_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `notes_id_idx` (`notes_id` ASC) ,
  INDEX `media_type_id_idx` (`media_type_id` ASC) ,
  CONSTRAINT `fk_media_notes_id`
    FOREIGN KEY (`notes_id` )
    REFERENCES `shuledirect`.`notes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_media_media_type_id`
    FOREIGN KEY (`media_type_id` )
    REFERENCES `shuledirect`.`media_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`question_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`question_types` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`question_types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `type` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`question_difficulties`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`question_difficulties` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`question_difficulties` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `difficulty` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`question_sources`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`question_sources` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`question_sources` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`questions` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`questions` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `notes_id` INT NOT NULL ,
  `default_question` VARCHAR(1023) NOT NULL ,
  `answer` VARCHAR(255) NOT NULL ,
  `override_question` VARCHAR(1023) NULL ,
  `question_type_id` INT NOT NULL ,
  `question_difficulty_id` INT NOT NULL ,
  `question_source_id` INT NOT NULL ,
  `language_id` INT NOT NULL ,
  `is_choice_order_significant` TINYINT(1) NOT NULL ,
  `default_explanation` VARCHAR(1023) NULL ,
  `override_explanation` VARCHAR(1023) NULL ,
  `is_sms_friendly` TINYINT(1) NOT NULL ,
  INDEX `question_type_id_idx` (`question_type_id` ASC) ,
  INDEX `notes_id_idx` (`notes_id` ASC) ,
  INDEX `question_difficulty_id_idx` (`question_difficulty_id` ASC) ,
  INDEX `question_source_id_idx` (`question_source_id` ASC) ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_questions_language_id` (`language_id` ASC) ,
  CONSTRAINT `fk_questions_question_type_id`
    FOREIGN KEY (`question_type_id` )
    REFERENCES `shuledirect`.`question_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_questions_notes_id`
    FOREIGN KEY (`notes_id` )
    REFERENCES `shuledirect`.`notes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_questions_question_difficulty_id`
    FOREIGN KEY (`question_difficulty_id` )
    REFERENCES `shuledirect`.`question_difficulties` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_questions_question_source_id`
    FOREIGN KEY (`question_source_id` )
    REFERENCES `shuledirect`.`question_sources` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_questions_language_id`
    FOREIGN KEY (`language_id` )
    REFERENCES `shuledirect`.`languages` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`question_media`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`question_media` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`question_media` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `question_id` INT NOT NULL ,
  `content` VARCHAR(1023) NOT NULL ,
  `description` VARCHAR(255) NULL ,
  `media_type_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_question_media_question_id`
    FOREIGN KEY (`question_id` )
    REFERENCES `shuledirect`.`questions` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_question_media_media_type_id`
    FOREIGN KEY (`media_type_id` )
    REFERENCES `shuledirect`.`media_types` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`question_choices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`question_choices` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`question_choices` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `is_sms_friendly` TINYINT(1) NOT NULL ,
  `position` INT NOT NULL ,
  `question_id` INT NOT NULL ,
  `default_choice` VARCHAR(255) NOT NULL ,
  `override_choice` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_question_choices_question_id`
    FOREIGN KEY (`question_id` )
    REFERENCES `shuledirect`.`questions` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`exams`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`exams` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`exams` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `notes_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_exams_notes_id` (`notes_id` ASC) ,
  CONSTRAINT `fk_exams_notes_id`
    FOREIGN KEY (`notes_id` )
    REFERENCES `shuledirect`.`notes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `shuledirect`.`exam_questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shuledirect`.`exam_questions` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `shuledirect`.`exam_questions` (
  `exam_id` INT NOT NULL ,
  `question_id` INT NOT NULL ,
  `position` INT NULL ,
  INDEX `fk_exam_questions_exam_id` (`exam_id` ASC) ,
  INDEX `fk_exam_questions_question_id` (`question_id` ASC) ,
  CONSTRAINT `fk_exam_questions_exam_id`
    FOREIGN KEY (`exam_id` )
    REFERENCES `shuledirect`.`exams` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exam_questions_question_id`
    FOREIGN KEY (`question_id` )
    REFERENCES `shuledirect`.`questions` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
