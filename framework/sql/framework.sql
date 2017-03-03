CREATE TABLE `common_log` (
  `log_id`    INT(11)     NOT NULL AUTO_INCREMENT,
  `log_level` VARCHAR(15) NOT NULL,
  `log_type`  VARCHAR(50)          DEFAULT NULL,
  `log_msg`   TEXT        NOT NULL,
  `log_data`  TEXT,
  `user_id`   VARCHAR(100),
  `created`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `cl_idx_log_level` (`log_level`),
  KEY `cl_idx_log_type` (`log_type`),
  KEY `cl_idx_user_id` (`user_id`),
  KEY `cl_idx_created` (`created`)
)
  ENGINE =InnoDB
  DEFAULT CHARSET =utf8$$

