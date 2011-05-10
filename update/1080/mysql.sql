ALTER TABLE `sizes` CHANGE `size_title` `size_title` varchar(255) default NULL;
ALTER TABLE `sizes` CHANGE `size_label` `size_label` varchar(255) default NULL;
ALTER TABLE `sizes` CHANGE `size_height` `size_height` smallint(5) UNSIGNED default NULL;
ALTER TABLE `sizes` CHANGE `size_width` `size_width` smallint(5) UNSIGNED default NULL;
ALTER TABLE `sizes` CHANGE `size_append` `size_append` varchar(16) default NULL;
ALTER TABLE `sizes` CHANGE `size_prepend` `size_prepend` varchar(16) default NULL;
ALTER TABLE `sizes` CHANGE `size_watermark` `size_watermark` tinyint(3) UNSIGNED default NULL;