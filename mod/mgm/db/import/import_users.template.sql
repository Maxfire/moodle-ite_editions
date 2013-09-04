-- Set initial key value for courses and editions
ALTER TABLE `mgm24`.`mdl_course` AUTO_INCREMENT = 1000;
ALTER TABLE `mgm24`.`mdl_edicion` AUTO_INCREMENT = 1000;

-- mdl_user
DELETE FROM `mgm24`.`mdl_user` where id>2 and id != 77910;
ALTER TABLE `mgm24`.`mdl_user` AUTO_INCREMENT = 110000;
INSERT INTO `mgm24`.`mdl_user` (id, auth, confirmed, policyagreed, deleted, mnethostid, username, password, idnumber, firstname, lastname, email, emailstop, icq, skype, yahoo, aim, msn, phone1, phone2, institution, department, address, city, country, lang, theme, timezone, firstaccess, lastaccess, lastlogin, currentlogin, lastip, secret, picture, url, description, mailformat, maildigest, maildisplay, htmleditor, autosubscribe, trackforums, timemodified, trustbitmask, imagealt)
SELECT id, auth, confirmed, policyagreed, deleted, 1, username, password, idnumber, firstname, lastname, email, emailstop, icq, skype, yahoo, aim, msn, phone1, phone2, institution, department, address, city, country, lang, theme, timezone, firstaccess, lastaccess, lastlogin, currentlogin, lastip, secret, picture, url, description, mailformat, maildigest, maildisplay, htmleditor, autosubscribe, trackforums, timemodified, trustbitmask, imagealt 
from `mgm19`.`mdl_user` where id>2 and id != 77910 limit 5000000;

-- user_info_category
DELETE FROM `mgm24`.`mdl_user_info_category` WHERE id >= 1;
ALTER TABLE `mgm24`.`mdl_user_info_category` AUTO_INCREMENT = 1000;
INSERT INTO `mgm24`.`mdl_user_info_category`
(`id`,
`name`,
`sortorder`)
SELECT
`mdl_user_info_category`.`id`,
`mdl_user_info_category`.`name`,
`mdl_user_info_category`.`sortorder`
FROM `mgm19`.`mdl_user_info_category` limit 5000000;

-- user_info_data
DELETE FROM `mgm24`.`mdl_user_info_data` WHERE userid > 2 and userid != 77910;
ALTER TABLE `mgm24`.`mdl_user_info_data` AUTO_INCREMENT = 2000000;
INSERT INTO `mgm24`.`mdl_user_info_data`
(`id`,
`userid`,
`fieldid`,
`data`
-- `dataformat`
)
SELECT
`mdl_user_info_data`.`id`,
`mdl_user_info_data`.`userid`,
`mdl_user_info_data`.`fieldid`,
`mdl_user_info_data`.`data`
FROM `mgm19`.`mdl_user_info_data` WHERE userid > 2 and userid != 77910 limit 5000000;


-- user_info_field
DELETE FROM `mgm24`.`mdl_user_info_field` WHERE id >= 1 ;
ALTER TABLE `mgm24`.`mdl_user_info_field` AUTO_INCREMENT = 1000;
INSERT INTO `mgm24`.`mdl_user_info_field` 
(`id`,
`shortname`,
`name`,
`datatype`,
`description`,
-- `descriptionformat`,
`categoryid`,
`sortorder`,
`required`,
`locked`,
`visible`,
`forceunique`,
`signup`,
`defaultdata`,
-- `defaultdataformat`,
`param1`,
`param2`,
`param3`,
`param4`,
`param5`)
SELECT
`mdl_user_info_field`.`id`,
`mdl_user_info_field`.`shortname`,
`mdl_user_info_field`.`name`,
`mdl_user_info_field`.`datatype`,
`mdl_user_info_field`.`description`,
`mdl_user_info_field`.`categoryid`,
`mdl_user_info_field`.`sortorder`,
`mdl_user_info_field`.`required`,
`mdl_user_info_field`.`locked`,
`mdl_user_info_field`.`visible`,
`mdl_user_info_field`.`forceunique`,
`mdl_user_info_field`.`signup`,
`mdl_user_info_field`.`defaultdata`,
`mdl_user_info_field`.`param1`,
`mdl_user_info_field`.`param2`,
`mdl_user_info_field`.`param3`,
`mdl_user_info_field`.`param4`,
`mdl_user_info_field`.`param5`
FROM `mgm19`.`mdl_user_info_field` limit 5000000;

-- user_preferences
DELETE FROM `mgm24`.`mdl_user_preferences` WHERE userid > 2 and userid != 77910;
ALTER TABLE `mgm24`.`mdl_user_preferences` AUTO_INCREMENT = 110000;
INSERT INTO `mgm24`.`mdl_user_preferences`
(`id`,
`userid`,
`name`,
`value`)
SELECT
`mdl_user_preferences`.`id`,
`mdl_user_preferences`.`userid`,
`mdl_user_preferences`.`name`,
`mdl_user_preferences`.`value`
FROM `mgm19`.`mdl_user_preferences` WHERE userid > 2 and userid != 77910 limit 5000000;


-- mdl_edicion_user
DELETE FROM `mgm24`.`mdl_edicion_user` WHERE userid > 2 and userid != 77910;
ALTER TABLE `mgm24`.`mdl_edicion_user` AUTO_INCREMENT = 110000;
INSERT INTO `mgm24`.`mdl_edicion_user`
(`id`,
`userid`,
`dni`,
`cc`,
`especialidades`,
`alt_address`,
`address`,
`tipoid`,
`codtipoparticipante`,
`codniveleducativo`,
`codcuerpodocente`,
`codpostal`,
`sexo`,
`codpais`,
`codprovincia`)
SELECT
`mdl_edicion_user`.`id`,
`mdl_edicion_user`.`userid`,
`mdl_edicion_user`.`dni`,
`mdl_edicion_user`.`cc`,
`mdl_edicion_user`.`especialidades`,
`mdl_edicion_user`.`alt_address`,
`mdl_edicion_user`.`address`,
`mdl_edicion_user`.`tipoid`,
`mdl_edicion_user`.`codtipoparticipante`,
`mdl_edicion_user`.`codniveleducativo`,
`mdl_edicion_user`.`codcuerpodocente`,
`mdl_edicion_user`.`codpostal`,
`mdl_edicion_user`.`sexo`,
`mdl_edicion_user`.`codpais`,
`mdl_edicion_user`.`codprovincia`
FROM `mgm19`.`mdl_edicion_user` WHERE userid > 2 and userid != 77910 limit 5000000;


-- mdl_edicion_cert_history
DELETE FROM `mgm24`.`mdl_edicion_cert_history` WHERE id >= 1;
ALTER TABLE `mgm24`.`mdl_edicion_cert_history` AUTO_INCREMENT = 150000;
INSERT INTO `mgm24`.`mdl_edicion_cert_history`
(`id`,
`courseid`,
`userid`,
`edicionid`,
`roleid`,
`numregistro`,
`confirm`,
`tipodocumento`,
`numdocumento`)
SELECT
`mdl_edicion_cert_history`.`id`,
`mdl_edicion_cert_history`.`courseid`,
`mdl_edicion_cert_history`.`userid`,
`mdl_edicion_cert_history`.`edicionid`,
`mdl_edicion_cert_history`.`roleid`,
`mdl_edicion_cert_history`.`numregistro`,
`mdl_edicion_cert_history`.`confirm`,
`mdl_edicion_cert_history`.`tipodocumento`,
`mdl_edicion_cert_history`.`numdocumento`
FROM `mgm19`.`mdl_edicion_cert_history` limit 5000000;

-- mdl_edicion_centro
DELETE FROM `mgm24`.`mdl_edicion_centro` WHERE id >= 1;
ALTER TABLE `mgm24`.`mdl_edicion_centro` AUTO_INCREMENT = 50000;
INSERT INTO `mgm24`.`mdl_edicion_centro`
(`id`,
`pais`,
`localidad`,
`dgenerica`,
`despecifica`,
`codigo`,
`naturaleza`,
`tipo`,
`direccion`,
`cp`,
`provincia`,
`telefono`)
SELECT
`mdl_edicion_centro`.`id`,
`mdl_edicion_centro`.`pais`,
`mdl_edicion_centro`.`localidad`,
`mdl_edicion_centro`.`dgenerica`,
`mdl_edicion_centro`.`despecifica`,
`mdl_edicion_centro`.`codigo`,
`mdl_edicion_centro`.`naturaleza`,
`mdl_edicion_centro`.`tipo`,
`mdl_edicion_centro`.`direccion`,
`mdl_edicion_centro`.`cp`,
`mdl_edicion_centro`.`provincia`,
`mdl_edicion_centro`.`telefono`
FROM `mgm19`.`mdl_edicion_centro` limit 5000000;
