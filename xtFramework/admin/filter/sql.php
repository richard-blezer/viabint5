

INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_FROM', 'von', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_FROM', 'from', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_TO', 'bis', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_TO', 'to', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_INCLUDES', 'enthaltet', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_INCLUDES', 'includes', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_ADVANCED_FILTER', 'Advanced Filter', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_ADVANCED_FILTER', 'Erweiterte Filter', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_FILTER', 'Filter', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_FILTER', 'Filter', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_FIND', 'Suchen', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_FIND', 'Find', 'admin', '');

INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_ON_INDEXPAGE', 'auf Startseite', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_ON_INDEXPAGE', 'on index page', 'admin', '');

INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_DATE', 'Datum', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_DATE', 'Date', 'admin', '');

INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_ACTIVE', 'Aktiv', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_ACTIVE', 'Active', 'admin', '');

INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('de', 'TEXT_CUSTOMER_NAME', 'Kundename', 'admin', '');
INSERT INTO `xt_language_content` (`language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES ('en', 'TEXT_CUSTOMER_NAME', 'Customer name', 'admin', '');

UPDATE `xt_language_content` SET `language_value` = 'Username' WHERE `xt_language_content`.`language_content_id` = 3811 LIMIT 1 ;
UPDATE `xt_new`.`xt_language_content` SET `language_value` = 'digital product' WHERE `xt_language_content`.`language_content_id` =4671 LIMIT 1 ;
UPDATE `xt_new`.`xt_language_content` SET `language_value` = 'Shipping method' WHERE `xt_language_content`.`language_content_id` =5609 LIMIT 1 ;