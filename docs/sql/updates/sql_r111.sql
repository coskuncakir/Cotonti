/* Subforums Enhancment */
ALTER TABLE `sed_forum_sections` ADD COLUMN `fs_mastername` varchar(128) NOT NULL default '';
ALTER TABLE `sed_users` ADD COLUMN `user_theme` VARCHAR(16) NOT NULL DEFAULT '';