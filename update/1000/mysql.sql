ALTER TABLE `images` ADD `image_tags` text;
ALTER TABLE `posts` ADD `post_category` varchar(255) default NULL;
ALTER TABLE `pages` ADD `page_category` varchar(255) default NULL;
ALTER TABLE `users` ADD `user_uri` varchar(255) default NULL;
ALTER TABLE `tags` ADD `tag_parents` text;
ALTER TABLE `guests` ADD `guest_inclusive` tinyint(3) unsigned default NULL;