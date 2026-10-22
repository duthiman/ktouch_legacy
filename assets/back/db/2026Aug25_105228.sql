

CREATE TABLE `balance` (
  `id` int NOT NULL,
  `balance` text COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO balance VALUES("1","2110.35");





CREATE TABLE `kp_backdb` (
  `back_id` int NOT NULL AUTO_INCREMENT,
  `backupfilename` varchar(55) NOT NULL,
  `backupfiledate` datetime NOT NULL,
  `username` varchar(11) NOT NULL,
  `ipaddress` varchar(22) NOT NULL,
  PRIMARY KEY (`back_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

INSERT INTO kp_backdb VALUES("1","2026Aug17_131104.sql","2026-08-17 13:11:04","1","192.168.88.102");
INSERT INTO kp_backdb VALUES("2","2026Aug24_103155.sql","2026-08-24 10:31:55","1","192.168.88.115");
INSERT INTO kp_backdb VALUES("3","2026Aug25_105228.sql","2026-08-25 10:52:28","4","192.168.88.115");





CREATE TABLE `kp_banks` (
  `id` int NOT NULL,
  `bank_id` int NOT NULL,
  `name` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_bill_merge` (
  `merge_id` int NOT NULL AUTO_INCREMENT,
  `merge_no` varchar(40) NOT NULL DEFAULT '',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `merge_state` tinyint(1) NOT NULL DEFAULT '1',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cleared_by` int NOT NULL DEFAULT '0',
  `cleared_at` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`merge_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_bill_merge_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `merge_id` int NOT NULL DEFAULT '0',
  `sale_id` int NOT NULL DEFAULT '0',
  `root_sale_id` int NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `item_type` varchar(20) NOT NULL DEFAULT 'single',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_merge_id` (`merge_id`),
  KEY `idx_sale_id` (`sale_id`),
  KEY `idx_root_sale_id` (`root_sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_bill_merge_request` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `merge_id` int NOT NULL DEFAULT '0',
  `requester_user_id` int NOT NULL DEFAULT '0',
  `requester_sale_id` int NOT NULL DEFAULT '0',
  `requester_root_sale_id` int NOT NULL DEFAULT '0',
  `target_user_id` int NOT NULL DEFAULT '0',
  `target_sale_id` int NOT NULL DEFAULT '0',
  `target_root_sale_id` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL,
  `responded_by` int NOT NULL DEFAULT '0',
  `note` text,
  PRIMARY KEY (`request_id`),
  KEY `idx_merge_id` (`merge_id`),
  KEY `idx_requester` (`requester_user_id`),
  KEY `idx_target` (`target_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_business_profiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(100) NOT NULL,
  `profile_slug` varchar(50) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `uq_profile_slug` (`profile_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kp_business_profiles VALUES("1","Full-Service Restaurant","restaurant","Dine-in with kitchen, grill, multi-counter POS, and structured inventory.","1","2026-08-11 11:19:22");
INSERT INTO kp_business_profiles VALUES("2","Quick-Service / Café","cafe","Single counter, simple purchases, no grill, no sports.","1","2026-08-11 11:19:22");
INSERT INTO kp_business_profiles VALUES("3","Sports Bar","sports_bar","Bar with sports viewing, grill, multi-counter, simple purchases.","1","2026-08-11 11:19:22");
INSERT INTO kp_business_profiles VALUES("4","Bar (Beverages Only)","bar","Drinks only. No kitchen, no grill, no store auto-issue, no sports.","1","2026-08-11 11:19:22");





CREATE TABLE `kp_cancelled` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` int NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_cancelled_sale_items` (
  `sale_id` int NOT NULL,
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `total` int NOT NULL,
  `cntr_id` int NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_cash_collection` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pay_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `user_id` int NOT NULL,
  `appr_by` int NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shift` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_cat` (
  `cat_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `dpt` int NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

INSERT INTO kp_cat VALUES("1","BEER","1");
INSERT INTO kp_cat VALUES("2","SPIRITS","1");
INSERT INTO kp_cat VALUES("3","WINES","1");
INSERT INTO kp_cat VALUES("5","SOFT DRINKS","1");
INSERT INTO kp_cat VALUES("6","OTHERS","1");
INSERT INTO kp_cat VALUES("11","KITCHEN","2");
INSERT INTO kp_cat VALUES("12","TOTS","1");
INSERT INTO kp_cat VALUES("15","CAN","1");





CREATE TABLE `kp_cntr1_closing_stock` (
  `item_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `shift` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_cntr1_closing_stock VALUES("1","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("2","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("3","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("4","26.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("5","14.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("6","23.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("7","95.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("8","47.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("9","80.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("10","33.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("11","11.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("12","33.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("13","29.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("14","45.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("15","21.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("16","27.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("17","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("18","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("19","26.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("22","151.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("23","39.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("25","48.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("27","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("28","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("29","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("30","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("31","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("32","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("33","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("34","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("36","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("37","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("39","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("40","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("41","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("48","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("51","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("52","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("53","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("55","71.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("56","33.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("57","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("58","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("59","26.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("60","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("64","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("66","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("67","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("69","54.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("70","43.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("71","134.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("72","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("73","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("74","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("75","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("76","14.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("77","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("78","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("82","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("83","7.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("84","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("85","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("86","75.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("87","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("89","16.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("92","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("93","1431.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("94","1245.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("95","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("96","1937.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("97","932.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("98","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("99","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("100","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("101","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("103","881.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("104","20.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("105","20.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("107","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("108","62.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("109","810.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("111","9.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("112","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("113","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("114","20.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("115","42.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("117","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("119","84.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("120","121.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("121","122.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("122","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("123","74.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("124","95.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("125","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("126","29.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("127","78.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("128","29.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("129","63.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("130","64.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("131","30.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("132","58.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("133","65.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("134","15.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("135","26.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("136","25.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("137","12.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("138","18.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("139","15.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("140","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("141","43.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("142","23.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("143","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("144","140.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("145","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("146","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("147","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("148","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("149","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("150","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("151","9.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("152","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("153","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("154","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("155","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("156","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("157","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("158","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("159","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("160","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("161","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("162","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("163","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("164","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("165","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("166","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("167","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("168","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("169","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("170","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("171","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("172","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("173","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("174","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("175","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("176","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("177","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("178","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("179","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("180","7.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("181","7.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("182","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("183","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("184","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("185","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("186","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("187","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("188","10.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("189","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("190","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("191","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("192","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("193","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("194","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("195","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("196","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("197","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("198","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("199","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("200","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("201","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("202","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("203","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("204","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("205","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("206","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("207","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("208","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("209","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("210","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("211","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("212","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("213","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("214","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("215","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("216","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("217","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("218","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("219","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("220","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("221","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("222","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("223","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("224","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("225","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("226","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("227","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("228","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("229","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("230","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("231","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("232","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("233","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("234","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("235","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("236","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("237","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("238","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("239","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("240","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("242","9.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("243","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("244","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("245","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("246","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("247","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("248","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("249","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("250","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("251","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("252","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("253","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("254","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("255","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("256","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("257","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("258","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("259","1120.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("260","1137.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("261","751.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("262","671.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("263","1003.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("264","1000.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("265","1007.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("266","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("267","1120.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("268","968.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("269","1637.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("270","1045.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("271","2019.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("272","999.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("273","1357.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("274","982.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("275","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("276","932.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("277","1088.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("278","974.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("279","1273.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("281","40.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("282","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("283","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("284","24.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("285","9.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("286","7.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("287","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("288","9.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("289","12.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("290","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("291","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("292","56.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("293","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("294","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("295","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("296","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("297","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("298","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("299","0.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("300","6.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("301","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("302","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("303","5.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("304","2.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("305","4.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("306","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("307","3.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("308","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("309","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("310","1.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("311","16.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("312","4728.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("313","4030.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("314","544.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("315","3355.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("316","1018.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("317","15.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("318","630.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("319","8.00","2026-08-24");
INSERT INTO kp_cntr1_closing_stock VALUES("320","2.00","2026-08-24");





CREATE TABLE `kp_cntr1_stock_take` (
  `take_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `user_id` int NOT NULL,
  `opening` decimal(10,2) DEFAULT NULL,
  `counted` decimal(10,2) DEFAULT NULL,
  `closing` decimal(10,2) DEFAULT NULL,
  `shift` date NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`take_id`)
) ENGINE=InnoDB AUTO_INCREMENT=636 DEFAULT CHARSET=latin1;

INSERT INTO kp_cntr1_stock_take VALUES("1","204","1","0.00","5.00","5.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("2","204","1","0.00","5.00","5.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("3","244","1","0.00","2.00","22.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("4","244","1","0.00","22.00","22.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("5","205","1","0.00","1.00","15.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("6","205","1","0.00","15.00","15.00","2026-08-11","2");
INSERT INTO kp_cntr1_stock_take VALUES("7","108","1","100.00","30.00","30.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("8","126","1","100.00","113.00","113.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("9","4","1","23.00","29.00","29.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("10","132","1","50.00","73.00","73.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("11","134","1","0.00","24.00","24.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("12","6","1","86.00","45.00","45.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("13","5","1","2.00","10.00","10.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("14","10","1","66.00","41.00","41.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("15","121","1","0.00","115.00","115.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("16","122","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("17","56","1","123.00","33.00","33.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("18","105","1","98.00","16.00","16.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("19","55","1","31.00","44.00","44.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("20","128","1","24.00","44.00","44.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("21","129","1","0.00","49.00","49.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("22","114","1","100.00","19.00","19.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("23","133","1","0.00","49.00","49.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("24","130","1","0.00","53.00","53.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("25","7","1","42.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("26","11","1","97.00","17.00","17.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("27","119","1","50.00","78.00","78.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("28","136","1","25.00","48.00","48.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("29","70","1","99.00","67.00","67.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("30","115","1","100.00","54.00","54.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("31","69","1","63.00","34.00","34.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("32","292","1","24.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("33","8","1","25.00","53.00","53.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("34","19","1","10.00","50.00","50.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("35","127","1","25.00","63.00","63.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("36","131","1","0.00","34.00","34.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("37","124","1","25.00","89.00","89.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("38","13","1","42.00","34.00","34.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("39","71","1","154.00","62.00","62.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("40","12","1","36.00","34.00","34.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("41","123","1","75.00","76.00","76.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("42","14","1","0.00","19.00","19.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("43","86","1","34.00","84.00","84.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("44","15","1","10.00","24.00","24.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("45","125","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("46","135","1","0.00","30.00","30.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("47","16","1","10.00","29.00","29.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("48","9","1","96.00","71.00","71.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("49","120","1","300.00","55.00","55.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("50","138","1","0.00","29.00","29.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("51","140","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("52","142","1","0.00","37.00","37.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("53","141","1","0.00","39.00","39.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("54","139","1","0.00","26.00","26.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("55","137","1","0.00","12.00","12.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("56","289","1","0.00","12.00","12.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("57","287","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("58","291","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("59","104","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("60","59","1","0.00","22.00","22.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("61","290","1","0.00","6.00","6.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("62","285","1","0.00","10.00","10.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("63","288","1","0.00","11.00","11.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("64","284","1","0.00","25.00","25.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("65","281","1","0.00","40.00","40.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("66","286","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("67","282","1","0.00","22.00","22.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("68","283","1","0.00","10.00","10.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("69","280","1","0.00","18.00","","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("70","76","1","8.00","16.00","16.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("71","145","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("72","25","1","44.00","61.00","61.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("73","23","1","111.00","57.00","57.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("74","299","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("75","146","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("76","39","1","99.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("77","22","1","134.00","152.00","152.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("78","143","1","0.00","11.00","11.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("79","144","1","132.00","114.00","114.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("81","204","1","5.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("82","203","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("83","205","1","15.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("84","244","1","22.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("85","243","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("86","175","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("87","229","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("88","230","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("89","222","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("90","224","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("91","223","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("92","221","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("93","236","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("94","235","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("95","234","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("96","254","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("97","178","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("98","200","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("99","199","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("100","190","1","0.00","7.00","7.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("101","181","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("102","180","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("103","179","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("104","209","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("105","239","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("106","97","1","122.00","976.00","976.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("107","2","1","20.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("108","3","1","100.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("109","92","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("110","83","1","99.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("111","300","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("112","84","1","100.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("113","225","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("114","301","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("115","226","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("116","227","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("117","228","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("118","258","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("119","159","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("120","160","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("121","255","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("122","237","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("123","238","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("124","193","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("125","30","1","101.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("126","29","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("127","207","1","1.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("128","217","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("129","246","1","0.00","7.00","7.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("130","37","1","10.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("131","247","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("132","109","1","0.00","926.00","926.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("133","17","1","10.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("134","176","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("135","212","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("136","211","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("137","206","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("138","248","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("139","101","1","10.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("140","18","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("141","249","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("142","95","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("143","197","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("144","196","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("145","214","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("146","189","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("147","245","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("148","216","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("149","163","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("150","161","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("151","57","1","100.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("152","167","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("153","162","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("154","194","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("155","184","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("156","41","1","10.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("157","58","1","10.00","1501.00","1501.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("158","82","1","100.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("159","112","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("160","27","1","101.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("161","201","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("162","28","1","200.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("163","202","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("164","273","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("165","182","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("167","36","1","200.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("168","186","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("169","185","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("170","183","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("171","213","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("172","208","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("173","33","1","20.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("174","34","1","200.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("175","218","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("176","215","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("177","1","1","1.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("178","93","1","20.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("179","94","1","0.00","578.00","578.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("180","169","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("181","170","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("182","192","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("183","191","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("184","294","1","2.00","12.00","12.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("185","171","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("186","77","1","102.00","11.00","11.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("187","85","1","100.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("188","78","1","102.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("189","87","1","200.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("190","293","1","3.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("191","195","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("192","256","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("193","257","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("194","64","1","200.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("195","103","1","300.00","881.00","881.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("196","31","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("197","32","1","20.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("198","168","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("199","164","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("200","177","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("202","233","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("203","187","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("204","188","1","0.00","10.00","10.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("205","296","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("206","107","1","100.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("207","165","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("208","157","1","0.00","7.00","7.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("209","99","1","200.00","6.00","6.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("210","100","1","100.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("211","166","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("212","172","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("213","173","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("214","174","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("215","240","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("217","242","1","4.00","8.00","8.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("218","241","1","2.00","0.00","","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("219","232","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("220","231","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("221","252","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("222","253","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("223","250","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("224","251","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("225","266","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("226","210","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("227","220","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("228","219","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("229","113","1","100.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("230","98","1","50.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("231","40","1","51.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("232","158","1","5.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("233","111","1","22.00","7.00","7.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("234","89","1","112.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("235","272","1","0.00","999.00","999.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("236","198","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("237","277","1","0.00","1438.00","1438.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("238","267","1","0.00","1120.00","1120.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("239","264","1","0.00","1000.00","1000.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("240","278","1","0.00","702.00","702.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("241","265","1","0.00","685.00","685.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("242","275","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("243","261","1","0.00","751.00","751.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("244","268","1","0.00","1056.00","1056.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("245","269","1","0.00","1637.00","1637.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("246","263","1","0.00","1501.00","1501.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("247","274","1","0.00","1145.00","1145.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("248","279","1","0.00","1516.00","1516.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("249","270","1","0.00","1131.00","1131.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("250","276","1","0.00","932.00","932.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("251","271","1","0.00","1138.00","1138.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("252","260","1","0.00","1187.00","1187.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("253","259","1","0.00","1120.00","1120.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("254","262","1","0.00","758.00","758.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("255","48","1","101.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("256","153","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("257","66","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("258","295","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("259","150","1","0.00","7.00","7.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("260","151","1","3.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("261","297","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("262","298","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("263","149","1","0.00","8.00","8.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("264","148","1","0.00","0.00","0.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("265","152","1","1.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("266","51","1","100.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("267","147","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("268","96","1","9.00","2417.00","2417.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("269","67","1","101.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("270","52","1","10.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("271","53","1","12.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("273","72","1","100.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("274","75","1","100.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("275","117","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("276","156","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("277","154","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("278","60","1","201.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("279","155","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("280","73","1","100.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("281","74","1","100.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("282","303","1","0.00","5.00","5.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("283","302","1","0.00","4.00","4.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("284","304","1","0.00","6.00","6.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("285","305","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("286","306","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("287","309","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("288","308","1","0.00","1.00","1.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("289","307","1","0.00","3.00","3.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("290","310","1","0.00","2.00","2.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("292","312","1","0.00","4968.00","4968.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("293","313","1","0.00","5266.00","5266.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("294","314","1","0.00","1062.00","1062.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("295","315","1","0.00","3355.00","3355.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("296","316","1","0.00","1018.00","1018.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("298","317","1","0.00","18.00","18.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("306","311","1","0.00","16.00","16.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("307","318","1","0.00","630.00","630.00","2026-08-17","2");
INSERT INTO kp_cntr1_stock_take VALUES("308","126","4","113.00","43.00","29.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("309","132","4","73.00","58.00","58.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("312","134","4","24.00","15.00","15.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("313","311","4","16.00","16.00","16.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("314","121","4","115.00","126.00","122.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("315","122","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("317","55","4","44.00","71.00","71.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("318","128","4","44.00","29.00","29.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("319","129","4","49.00","63.00","63.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("320","114","4","19.00","20.00","20.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("321","133","4","49.00","65.00","65.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("322","130","4","53.00","64.00","64.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("323","7","4","3.00","97.00","95.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("324","119","4","78.00","84.00","84.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("325","136","4","48.00","25.00","25.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("326","69","4","34.00","56.00","54.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("327","292","4","0.00","56.00","56.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("328","141","4","39.00","43.00","43.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("329","8","4","53.00","49.00","47.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("330","127","4","63.00","78.00","78.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("331","131","4","34.00","30.00","30.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("332","124","4","89.00","95.00","95.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("333","71","4","62.00","135.00","134.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("336","86","4","84.00","75.00","75.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("337","125","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("338","135","4","30.00","26.00","26.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("339","9","4","71.00","80.00","80.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("340","120","4","55.00","123.00","121.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("343","4","4","29.00","26.00","26.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("344","138","4","29.00","18.00","18.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("345","140","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("346","6","4","45.00","23.00","23.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("347","5","4","10.00","14.00","14.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("348","10","4","41.00","33.00","33.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("349","56","4","33.00","33.00","33.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("350","142","4","37.00","25.00","23.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("351","105","4","16.00","20.00","20.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("352","11","4","17.00","11.00","11.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("353","115","4","54.00","42.00","42.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("354","70","4","67.00","43.00","43.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("355","19","4","50.00","27.00","26.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("356","139","4","26.00","15.00","15.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("357","13","4","34.00","29.00","29.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("358","12","4","34.00","33.00","33.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("359","14","4","19.00","45.00","45.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("360","15","4","24.00","25.00","21.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("361","137","4","12.00","12.00","12.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("362","16","4","29.00","27.00","27.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("363","285","4","10.00","9.00","9.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("364","288","4","11.00","9.00","9.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("365","289","4","12.00","12.00","12.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("366","287","4","5.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("367","284","4","25.00","24.00","24.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("368","281","4","40.00","40.00","40.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("369","286","4","3.00","7.00","7.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("370","282","4","22.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("371","291","4","0.00","1.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("372","283","4","10.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("373","290","4","6.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("374","317","4","18.00","15.00","15.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("375","145","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("376","25","4","61.00","48.00","48.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("378","299","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("379","23","4","57.00","42.00","39.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("380","108","4","30.00","62.00","62.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("381","146","4","1.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("382","76","4","16.00","14.00","14.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("383","104","4","2.00","20.00","20.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("384","59","4","22.00","26.00","26.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("385","22","4","152.00","153.00","151.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("386","143","4","11.00","7.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("387","144","4","114.00","142.00","140.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("388","244","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("389","243","4","5.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("390","175","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("391","306","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("392","229","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("393","230","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("394","309","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("395","308","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("396","222","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("397","224","4","1.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("398","223","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("399","221","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("400","236","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("401","235","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("402","234","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("403","254","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("404","178","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("405","200","4","5.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("406","199","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("407","2","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("408","190","4","7.00","7.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("409","3","4","3.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("410","181","4","4.00","7.00","7.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("411","180","4","5.00","7.00","7.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("412","179","4","3.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("413","209","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("414","239","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("415","97","4","976.00","932.00","932.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("416","92","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("417","83","4","4.00","7.00","7.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("418","300","4","4.00","6.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("419","301","4","4.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("420","301","4","4.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("421","84","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("422","225","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("423","226","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("424","227","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("425","227","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("426","228","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("427","258","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("428","159","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("429","159","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("430","160","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("431","255","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("432","237","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("433","238","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("434","193","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("435","30","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("436","29","4","3.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("437","207","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("439","217","4","2.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("440","246","4","7.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("441","37","4","3.00","6.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("442","247","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("443","109","4","926.00","810.00","810.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("444","17","4","4.00","3.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("445","176","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("446","176","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("447","203","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("448","204","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("449","205","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("450","212","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("451","211","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("452","206","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("453","206","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("454","248","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("456","18","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("457","249","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("458","101","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("459","95","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("460","197","4","2.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("461","196","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("462","214","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("463","189","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("464","245","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("465","216","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("466","163","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("467","161","4","2.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("468","57","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("469","167","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("470","162","4","3.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("472","304","4","6.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("473","303","4","5.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("474","302","4","4.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("475","194","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("476","184","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("477","41","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("478","82","4","1.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("479","112","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("480","27","4","3.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("481","201","4","2.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("482","28","4","2.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("483","202","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("490","182","4","4.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("491","36","4","1.00","3.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("492","186","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("493","185","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("494","183","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("509","213","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("510","208","4","5.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("511","33","4","2.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("512","34","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("513","218","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("514","215","4","1.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("515","1","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("516","93","4","0.00","1431.00","1431.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("517","94","4","578.00","1245.00","1245.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("518","310","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("519","169","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("520","170","4","1.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("521","191","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("522","192","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("523","294","4","12.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("524","171","4","5.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("525","77","4","11.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("527","78","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("528","87","4","0.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("529","293","4","0.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("530","85","4","0.00","6.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("531","195","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("532","256","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("533","257","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("534","64","4","1.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("535","103","4","881.00","881.00","881.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("536","31","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("537","32","4","0.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("538","168","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("539","307","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("540","164","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("541","177","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("542","233","4","0.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("543","187","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("544","188","4","10.00","10.00","10.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("545","305","4","3.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("546","296","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("547","107","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("548","165","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("549","157","4","7.00","6.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("550","99","4","6.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("551","100","4","3.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("552","166","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("553","172","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("555","173","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("556","174","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("557","240","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("558","319","4","0.00","8.00","8.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("559","242","4","8.00","9.00","9.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("560","39","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("561","232","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("562","231","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("563","252","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("564","253","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("565","250","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("566","251","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("567","210","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("568","220","4","2.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("569","219","4","4.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("570","113","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("571","98","4","3.00","1.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("572","40","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("573","158","4","3.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("574","111","4","7.00","9.00","9.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("575","89","4","4.00","16.00","16.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("576","198","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("577","277","4","1438.00","1088.00","1088.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("578","267","4","1120.00","1120.00","1120.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("579","264","4","1000.00","1000.00","1000.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("580","278","4","702.00","974.00","974.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("581","318","4","630.00","630.00","630.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("582","265","4","685.00","1007.00","1007.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("583","275","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("584","261","4","751.00","751.00","751.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("585","268","4","1056.00","968.00","968.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("586","269","4","1637.00","1637.00","1637.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("588","58","4","1501.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("589","263","4","1501.00","1003.00","1003.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("590","273","4","0.00","1357.00","1357.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("591","274","4","1145.00","982.00","982.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("592","279","4","1516.00","1273.00","1273.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("593","270","4","1131.00","1045.00","1045.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("594","276","4","932.00","932.00","932.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("595","271","4","1138.00","2019.00","2019.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("596","260","4","1187.00","1137.00","1137.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("597","266","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("598","259","4","1120.00","1120.00","1120.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("599","262","4","758.00","671.00","671.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("600","272","4","999.00","999.00","999.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("601","72","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("602","314","4","1062.00","544.00","544.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("603","48","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("604","315","4","3355.00","3355.00","3355.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("605","153","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("606","295","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("607","66","4","2.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("609","150","4","7.00","6.00","6.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("610","151","4","5.00","9.00","9.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("611","297","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("612","298","4","0.00","0.00","0.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("613","320","4","0.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("614","149","4","8.00","5.00","5.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("615","148","4","0.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("616","152","4","3.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("617","313","4","5266.00","4030.00","4030.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("618","51","4","4.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("619","312","4","4968.00","4728.00","4728.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("620","147","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("621","96","4","2417.00","1937.00","1937.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("622","316","4","1018.00","1018.00","1018.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("623","67","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("624","52","4","4.00","4.00","4.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("625","53","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("626","75","4","4.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("627","117","4","2.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("628","156","4","3.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("629","154","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("630","60","4","2.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("631","155","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("632","155","4","1.00","1.00","1.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("633","73","4","1.00","3.00","3.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("634","74","4","4.00","2.00","2.00","2026-08-24","2");
INSERT INTO kp_cntr1_stock_take VALUES("635","123","4","76.00","76.00","74.00","2026-08-24","2");





CREATE TABLE `kp_cntr2_closing_stock` (
  `item_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `shift` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_cntr2_stock_take` (
  `take_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `user_id` int NOT NULL,
  `opening` decimal(10,2) DEFAULT NULL,
  `counted` decimal(10,2) DEFAULT NULL,
  `closing` decimal(10,2) DEFAULT NULL,
  `shift` date NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`take_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_counter_close_mode_totals` (
  `close_mode_id` int NOT NULL AUTO_INCREMENT,
  `close_sum_id` int NOT NULL DEFAULT '0',
  `pay_id` int NOT NULL DEFAULT '0',
  `same_day_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `previous_day_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`close_mode_id`),
  KEY `idx_close_sum_id` (`close_sum_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_counter_close_mode_totals VALUES("1","1","1","300.00","0.00","300.00","2026-08-17 13:11:03");
INSERT INTO kp_counter_close_mode_totals VALUES("2","1","2","0.00","0.00","0.00","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_mode_totals VALUES("3","1","3","0.00","0.00","0.00","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_mode_totals VALUES("4","1","4","0.00","0.00","0.00","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_mode_totals VALUES("5","2","1","0.00","0.00","0.00","2026-08-24 10:31:40");
INSERT INTO kp_counter_close_mode_totals VALUES("6","2","2","0.00","0.00","0.00","2026-08-24 10:31:40");
INSERT INTO kp_counter_close_mode_totals VALUES("7","2","3","0.00","0.00","0.00","2026-08-24 10:31:40");
INSERT INTO kp_counter_close_mode_totals VALUES("8","2","4","0.00","0.00","0.00","2026-08-24 10:31:40");
INSERT INTO kp_counter_close_mode_totals VALUES("9","3","1","0.00","0.00","0.00","2026-08-25 10:52:12");
INSERT INTO kp_counter_close_mode_totals VALUES("10","3","2","0.00","0.00","0.00","2026-08-25 10:52:12");
INSERT INTO kp_counter_close_mode_totals VALUES("11","3","3","0.00","0.00","0.00","2026-08-25 10:52:12");
INSERT INTO kp_counter_close_mode_totals VALUES("12","3","4","0.00","0.00","0.00","2026-08-25 10:52:13");





CREATE TABLE `kp_counter_close_stock_take_items` (
  `close_take_id` int NOT NULL AUTO_INCREMENT,
  `close_sum_id` int NOT NULL DEFAULT '0',
  `shift_date` date NOT NULL,
  `cntr_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `opening` decimal(12,2) NOT NULL DEFAULT '0.00',
  `counted` decimal(12,2) NOT NULL DEFAULT '0.00',
  `closing` decimal(12,2) NOT NULL DEFAULT '0.00',
  `variance_qty` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_ok` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`close_take_id`),
  KEY `idx_close_sum_id` (`close_sum_id`),
  KEY `idx_counter_shift` (`cntr_id`,`shift_date`)
) ENGINE=InnoDB AUTO_INCREMENT=587 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_counter_close_stock_take_items VALUES("1","1","2026-08-11","1","204","0.00","5.00","5.00","5.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("2","1","2026-08-11","1","204","0.00","5.00","5.00","5.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("3","1","2026-08-11","1","244","0.00","2.00","22.00","2.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("4","1","2026-08-11","1","244","0.00","22.00","22.00","22.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("5","1","2026-08-11","1","205","0.00","1.00","15.00","1.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("6","1","2026-08-11","1","205","0.00","15.00","15.00","15.00","0","2026-08-17 13:11:04");
INSERT INTO kp_counter_close_stock_take_items VALUES("7","2","2026-08-17","1","108","100.00","30.00","30.00","-70.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("8","2","2026-08-17","1","126","100.00","113.00","113.00","13.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("9","2","2026-08-17","1","4","23.00","29.00","29.00","6.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("10","2","2026-08-17","1","132","50.00","73.00","73.00","23.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("11","2","2026-08-17","1","134","0.00","24.00","24.00","24.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("12","2","2026-08-17","1","6","86.00","45.00","45.00","-41.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("13","2","2026-08-17","1","5","2.00","10.00","10.00","8.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("14","2","2026-08-17","1","10","66.00","41.00","41.00","-25.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("15","2","2026-08-17","1","121","0.00","115.00","115.00","115.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("16","2","2026-08-17","1","122","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("17","2","2026-08-17","1","56","123.00","33.00","33.00","-90.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("18","2","2026-08-17","1","105","98.00","16.00","16.00","-82.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("19","2","2026-08-17","1","55","31.00","44.00","44.00","13.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("20","2","2026-08-17","1","128","24.00","44.00","44.00","20.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("21","2","2026-08-17","1","129","0.00","49.00","49.00","49.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("22","2","2026-08-17","1","114","100.00","19.00","19.00","-81.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("23","2","2026-08-17","1","133","0.00","49.00","49.00","49.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("24","2","2026-08-17","1","130","0.00","53.00","53.00","53.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("25","2","2026-08-17","1","7","42.00","3.00","3.00","-39.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("26","2","2026-08-17","1","11","97.00","17.00","17.00","-80.00","0","2026-08-24 10:31:41");
INSERT INTO kp_counter_close_stock_take_items VALUES("27","2","2026-08-17","1","119","50.00","78.00","78.00","28.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("28","2","2026-08-17","1","136","25.00","48.00","48.00","23.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("29","2","2026-08-17","1","70","99.00","67.00","67.00","-32.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("30","2","2026-08-17","1","115","100.00","54.00","54.00","-46.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("31","2","2026-08-17","1","69","63.00","34.00","34.00","-29.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("32","2","2026-08-17","1","292","24.00","0.00","0.00","-24.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("33","2","2026-08-17","1","8","25.00","53.00","53.00","28.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("34","2","2026-08-17","1","19","10.00","50.00","50.00","40.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("35","2","2026-08-17","1","127","25.00","63.00","63.00","38.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("36","2","2026-08-17","1","131","0.00","34.00","34.00","34.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("37","2","2026-08-17","1","124","25.00","89.00","89.00","64.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("38","2","2026-08-17","1","13","42.00","34.00","34.00","-8.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("39","2","2026-08-17","1","71","154.00","62.00","62.00","-92.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("40","2","2026-08-17","1","12","36.00","34.00","34.00","-2.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("41","2","2026-08-17","1","123","75.00","76.00","76.00","1.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("42","2","2026-08-17","1","14","0.00","19.00","19.00","19.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("43","2","2026-08-17","1","86","34.00","84.00","84.00","50.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("44","2","2026-08-17","1","15","10.00","24.00","24.00","14.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("45","2","2026-08-17","1","125","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("46","2","2026-08-17","1","135","0.00","30.00","30.00","30.00","0","2026-08-24 10:31:42");
INSERT INTO kp_counter_close_stock_take_items VALUES("47","2","2026-08-17","1","16","10.00","29.00","29.00","19.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("48","2","2026-08-17","1","9","96.00","71.00","71.00","-25.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("49","2","2026-08-17","1","120","300.00","55.00","55.00","-245.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("50","2","2026-08-17","1","138","0.00","29.00","29.00","29.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("51","2","2026-08-17","1","140","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("52","2","2026-08-17","1","142","0.00","37.00","37.00","37.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("53","2","2026-08-17","1","141","0.00","39.00","39.00","39.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("54","2","2026-08-17","1","139","0.00","26.00","26.00","26.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("55","2","2026-08-17","1","137","0.00","12.00","12.00","12.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("56","2","2026-08-17","1","289","0.00","12.00","12.00","12.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("57","2","2026-08-17","1","287","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("58","2","2026-08-17","1","291","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("59","2","2026-08-17","1","104","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("60","2","2026-08-17","1","59","0.00","22.00","22.00","22.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("61","2","2026-08-17","1","290","0.00","6.00","6.00","6.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("62","2","2026-08-17","1","285","0.00","10.00","10.00","10.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("63","2","2026-08-17","1","288","0.00","11.00","11.00","11.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("64","2","2026-08-17","1","284","0.00","25.00","25.00","25.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("65","2","2026-08-17","1","281","0.00","40.00","40.00","40.00","0","2026-08-24 10:31:43");
INSERT INTO kp_counter_close_stock_take_items VALUES("66","2","2026-08-17","1","286","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("67","2","2026-08-17","1","282","0.00","22.00","22.00","22.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("68","2","2026-08-17","1","283","0.00","10.00","10.00","10.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("69","2","2026-08-17","1","280","0.00","18.00","0.00","18.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("70","2","2026-08-17","1","76","8.00","16.00","16.00","8.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("71","2","2026-08-17","1","145","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("72","2","2026-08-17","1","25","44.00","61.00","61.00","17.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("73","2","2026-08-17","1","23","111.00","57.00","57.00","-54.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("74","2","2026-08-17","1","299","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("75","2","2026-08-17","1","146","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("76","2","2026-08-17","1","39","99.00","3.00","3.00","-96.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("77","2","2026-08-17","1","22","134.00","152.00","152.00","18.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("78","2","2026-08-17","1","143","0.00","11.00","11.00","11.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("79","2","2026-08-17","1","144","132.00","114.00","114.00","-18.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("80","2","2026-08-17","1","204","5.00","2.00","2.00","-3.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("81","2","2026-08-17","1","203","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("82","2","2026-08-17","1","205","15.00","2.00","2.00","-13.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("83","2","2026-08-17","1","244","22.00","2.00","2.00","-20.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("84","2","2026-08-17","1","243","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("85","2","2026-08-17","1","175","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("86","2","2026-08-17","1","229","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("87","2","2026-08-17","1","230","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:44");
INSERT INTO kp_counter_close_stock_take_items VALUES("88","2","2026-08-17","1","222","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("89","2","2026-08-17","1","224","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("90","2","2026-08-17","1","223","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("91","2","2026-08-17","1","221","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("92","2","2026-08-17","1","236","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("93","2","2026-08-17","1","235","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("94","2","2026-08-17","1","234","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("95","2","2026-08-17","1","254","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("96","2","2026-08-17","1","178","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("97","2","2026-08-17","1","200","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("98","2","2026-08-17","1","199","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("99","2","2026-08-17","1","190","0.00","7.00","7.00","7.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("100","2","2026-08-17","1","181","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("101","2","2026-08-17","1","180","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("102","2","2026-08-17","1","179","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("103","2","2026-08-17","1","209","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("104","2","2026-08-17","1","239","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("105","2","2026-08-17","1","97","122.00","976.00","976.00","854.00","0","2026-08-24 10:31:45");
INSERT INTO kp_counter_close_stock_take_items VALUES("106","2","2026-08-17","1","2","20.00","3.00","3.00","-17.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("107","2","2026-08-17","1","3","100.00","3.00","3.00","-97.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("108","2","2026-08-17","1","92","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("109","2","2026-08-17","1","83","99.00","4.00","4.00","-95.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("110","2","2026-08-17","1","300","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("111","2","2026-08-17","1","84","100.00","1.00","1.00","-99.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("112","2","2026-08-17","1","225","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("113","2","2026-08-17","1","301","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("114","2","2026-08-17","1","226","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("115","2","2026-08-17","1","227","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("116","2","2026-08-17","1","228","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("117","2","2026-08-17","1","258","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("118","2","2026-08-17","1","159","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("119","2","2026-08-17","1","160","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("120","2","2026-08-17","1","255","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("121","2","2026-08-17","1","237","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("122","2","2026-08-17","1","238","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("123","2","2026-08-17","1","193","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("124","2","2026-08-17","1","30","101.00","3.00","3.00","-98.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("125","2","2026-08-17","1","29","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("126","2","2026-08-17","1","207","1.00","2.00","2.00","1.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("127","2","2026-08-17","1","217","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("128","2","2026-08-17","1","246","0.00","7.00","7.00","7.00","0","2026-08-24 10:31:46");
INSERT INTO kp_counter_close_stock_take_items VALUES("129","2","2026-08-17","1","37","10.00","3.00","3.00","-7.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("130","2","2026-08-17","1","247","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("131","2","2026-08-17","1","109","0.00","926.00","926.00","926.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("132","2","2026-08-17","1","17","10.00","4.00","4.00","-6.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("133","2","2026-08-17","1","176","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("134","2","2026-08-17","1","212","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("135","2","2026-08-17","1","211","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("136","2","2026-08-17","1","206","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("137","2","2026-08-17","1","248","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("138","2","2026-08-17","1","101","10.00","3.00","3.00","-7.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("139","2","2026-08-17","1","18","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("140","2","2026-08-17","1","249","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("141","2","2026-08-17","1","95","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("142","2","2026-08-17","1","197","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("143","2","2026-08-17","1","196","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("144","2","2026-08-17","1","214","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("145","2","2026-08-17","1","189","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("146","2","2026-08-17","1","245","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("147","2","2026-08-17","1","216","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:47");
INSERT INTO kp_counter_close_stock_take_items VALUES("148","2","2026-08-17","1","163","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("149","2","2026-08-17","1","161","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("150","2","2026-08-17","1","57","100.00","3.00","3.00","-97.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("151","2","2026-08-17","1","167","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("152","2","2026-08-17","1","162","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("153","2","2026-08-17","1","194","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("154","2","2026-08-17","1","184","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("155","2","2026-08-17","1","41","10.00","3.00","3.00","-7.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("156","2","2026-08-17","1","58","10.00","1501.00","1501.00","1491.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("157","2","2026-08-17","1","82","100.00","1.00","1.00","-99.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("158","2","2026-08-17","1","112","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("159","2","2026-08-17","1","27","101.00","3.00","3.00","-98.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("160","2","2026-08-17","1","201","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("161","2","2026-08-17","1","28","200.00","2.00","2.00","-198.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("162","2","2026-08-17","1","202","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("163","2","2026-08-17","1","273","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("164","2","2026-08-17","1","182","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("165","2","2026-08-17","1","36","200.00","1.00","1.00","-199.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("166","2","2026-08-17","1","186","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("167","2","2026-08-17","1","185","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("168","2","2026-08-17","1","183","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("169","2","2026-08-17","1","213","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:48");
INSERT INTO kp_counter_close_stock_take_items VALUES("170","2","2026-08-17","1","208","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("171","2","2026-08-17","1","33","20.00","2.00","2.00","-18.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("172","2","2026-08-17","1","34","200.00","2.00","2.00","-198.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("173","2","2026-08-17","1","218","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("174","2","2026-08-17","1","215","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("175","2","2026-08-17","1","1","1.00","2.00","2.00","1.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("176","2","2026-08-17","1","93","20.00","0.00","0.00","-20.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("177","2","2026-08-17","1","94","0.00","578.00","578.00","578.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("178","2","2026-08-17","1","169","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("179","2","2026-08-17","1","170","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("180","2","2026-08-17","1","192","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("181","2","2026-08-17","1","191","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("182","2","2026-08-17","1","294","2.00","12.00","12.00","10.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("183","2","2026-08-17","1","171","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("184","2","2026-08-17","1","77","102.00","11.00","11.00","-91.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("185","2","2026-08-17","1","85","100.00","0.00","0.00","-100.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("186","2","2026-08-17","1","78","102.00","0.00","0.00","-102.00","0","2026-08-24 10:31:49");
INSERT INTO kp_counter_close_stock_take_items VALUES("187","2","2026-08-17","1","87","200.00","0.00","0.00","-200.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("188","2","2026-08-17","1","293","3.00","0.00","0.00","-3.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("189","2","2026-08-17","1","195","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("190","2","2026-08-17","1","256","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("191","2","2026-08-17","1","257","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("192","2","2026-08-17","1","64","200.00","1.00","1.00","-199.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("193","2","2026-08-17","1","103","300.00","881.00","881.00","581.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("194","2","2026-08-17","1","31","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("195","2","2026-08-17","1","32","20.00","0.00","0.00","-20.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("196","2","2026-08-17","1","168","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("197","2","2026-08-17","1","164","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("198","2","2026-08-17","1","177","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("199","2","2026-08-17","1","233","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("200","2","2026-08-17","1","187","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("201","2","2026-08-17","1","188","0.00","10.00","10.00","10.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("202","2","2026-08-17","1","296","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("203","2","2026-08-17","1","107","100.00","1.00","1.00","-99.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("204","2","2026-08-17","1","165","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:50");
INSERT INTO kp_counter_close_stock_take_items VALUES("205","2","2026-08-17","1","157","0.00","7.00","7.00","7.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("206","2","2026-08-17","1","99","200.00","6.00","6.00","-194.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("207","2","2026-08-17","1","100","100.00","3.00","3.00","-97.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("208","2","2026-08-17","1","166","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("209","2","2026-08-17","1","172","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("210","2","2026-08-17","1","173","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("211","2","2026-08-17","1","174","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("212","2","2026-08-17","1","240","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("213","2","2026-08-17","1","242","4.00","8.00","8.00","4.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("214","2","2026-08-17","1","241","2.00","0.00","0.00","-2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("215","2","2026-08-17","1","232","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("216","2","2026-08-17","1","231","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("217","2","2026-08-17","1","252","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("218","2","2026-08-17","1","253","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("219","2","2026-08-17","1","250","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("220","2","2026-08-17","1","251","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("221","2","2026-08-17","1","266","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("222","2","2026-08-17","1","210","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("223","2","2026-08-17","1","220","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("224","2","2026-08-17","1","219","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("225","2","2026-08-17","1","113","100.00","0.00","0.00","-100.00","0","2026-08-24 10:31:51");
INSERT INTO kp_counter_close_stock_take_items VALUES("226","2","2026-08-17","1","98","50.00","3.00","3.00","-47.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("227","2","2026-08-17","1","40","51.00","3.00","3.00","-48.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("228","2","2026-08-17","1","158","5.00","3.00","3.00","-2.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("229","2","2026-08-17","1","111","22.00","7.00","7.00","-15.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("230","2","2026-08-17","1","89","112.00","4.00","4.00","-108.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("231","2","2026-08-17","1","272","0.00","999.00","999.00","999.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("232","2","2026-08-17","1","198","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("233","2","2026-08-17","1","277","0.00","1438.00","1438.00","1438.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("234","2","2026-08-17","1","267","0.00","1120.00","1120.00","1120.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("235","2","2026-08-17","1","264","0.00","1000.00","1000.00","1000.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("236","2","2026-08-17","1","278","0.00","702.00","702.00","702.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("237","2","2026-08-17","1","265","0.00","685.00","685.00","685.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("238","2","2026-08-17","1","275","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("239","2","2026-08-17","1","261","0.00","751.00","751.00","751.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("240","2","2026-08-17","1","268","0.00","1056.00","1056.00","1056.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("241","2","2026-08-17","1","269","0.00","1637.00","1637.00","1637.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("242","2","2026-08-17","1","263","0.00","1501.00","1501.00","1501.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("243","2","2026-08-17","1","274","0.00","1145.00","1145.00","1145.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("244","2","2026-08-17","1","279","0.00","1516.00","1516.00","1516.00","0","2026-08-24 10:31:52");
INSERT INTO kp_counter_close_stock_take_items VALUES("245","2","2026-08-17","1","270","0.00","1131.00","1131.00","1131.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("246","2","2026-08-17","1","276","0.00","932.00","932.00","932.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("247","2","2026-08-17","1","271","0.00","1138.00","1138.00","1138.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("248","2","2026-08-17","1","260","0.00","1187.00","1187.00","1187.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("249","2","2026-08-17","1","259","0.00","1120.00","1120.00","1120.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("250","2","2026-08-17","1","262","0.00","758.00","758.00","758.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("251","2","2026-08-17","1","48","101.00","3.00","3.00","-98.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("252","2","2026-08-17","1","153","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("253","2","2026-08-17","1","66","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("254","2","2026-08-17","1","295","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("255","2","2026-08-17","1","150","0.00","7.00","7.00","7.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("256","2","2026-08-17","1","151","3.00","5.00","5.00","2.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("257","2","2026-08-17","1","297","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("258","2","2026-08-17","1","298","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("259","2","2026-08-17","1","149","0.00","8.00","8.00","8.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("260","2","2026-08-17","1","148","0.00","0.00","0.00","0.00","1","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("261","2","2026-08-17","1","152","1.00","3.00","3.00","2.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("262","2","2026-08-17","1","51","100.00","4.00","4.00","-96.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("263","2","2026-08-17","1","147","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("264","2","2026-08-17","1","96","9.00","2417.00","2417.00","2408.00","0","2026-08-24 10:31:53");
INSERT INTO kp_counter_close_stock_take_items VALUES("265","2","2026-08-17","1","67","101.00","4.00","4.00","-97.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("266","2","2026-08-17","1","52","10.00","4.00","4.00","-6.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("267","2","2026-08-17","1","53","12.00","3.00","3.00","-9.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("268","2","2026-08-17","1","72","100.00","2.00","2.00","-98.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("269","2","2026-08-17","1","75","100.00","4.00","4.00","-96.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("270","2","2026-08-17","1","117","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("271","2","2026-08-17","1","156","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("272","2","2026-08-17","1","154","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("273","2","2026-08-17","1","60","201.00","2.00","2.00","-199.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("274","2","2026-08-17","1","155","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("275","2","2026-08-17","1","73","100.00","1.00","1.00","-99.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("276","2","2026-08-17","1","74","100.00","4.00","4.00","-96.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("277","2","2026-08-17","1","303","0.00","5.00","5.00","5.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("278","2","2026-08-17","1","302","0.00","4.00","4.00","4.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("279","2","2026-08-17","1","304","0.00","6.00","6.00","6.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("280","2","2026-08-17","1","305","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("281","2","2026-08-17","1","306","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("282","2","2026-08-17","1","309","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("283","2","2026-08-17","1","308","0.00","1.00","1.00","1.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("284","2","2026-08-17","1","307","0.00","3.00","3.00","3.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("285","2","2026-08-17","1","310","0.00","2.00","2.00","2.00","0","2026-08-24 10:31:54");
INSERT INTO kp_counter_close_stock_take_items VALUES("286","2","2026-08-17","1","312","0.00","4968.00","4968.00","4968.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("287","2","2026-08-17","1","313","0.00","5266.00","5266.00","5266.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("288","2","2026-08-17","1","314","0.00","1062.00","1062.00","1062.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("289","2","2026-08-17","1","315","0.00","3355.00","3355.00","3355.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("290","2","2026-08-17","1","316","0.00","1018.00","1018.00","1018.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("291","2","2026-08-17","1","317","0.00","18.00","18.00","18.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("292","2","2026-08-17","1","311","0.00","16.00","16.00","16.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("293","2","2026-08-17","1","318","0.00","630.00","630.00","630.00","0","2026-08-24 10:31:55");
INSERT INTO kp_counter_close_stock_take_items VALUES("294","3","2026-08-24","1","126","113.00","43.00","29.00","-70.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("295","3","2026-08-24","1","132","73.00","58.00","58.00","-15.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("296","3","2026-08-24","1","134","24.00","15.00","15.00","-9.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("297","3","2026-08-24","1","311","16.00","16.00","16.00","0.00","1","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("298","3","2026-08-24","1","121","115.00","126.00","122.00","11.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("299","3","2026-08-24","1","122","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("300","3","2026-08-24","1","55","44.00","71.00","71.00","27.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("301","3","2026-08-24","1","128","44.00","29.00","29.00","-15.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("302","3","2026-08-24","1","129","49.00","63.00","63.00","14.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("303","3","2026-08-24","1","114","19.00","20.00","20.00","1.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("304","3","2026-08-24","1","133","49.00","65.00","65.00","16.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("305","3","2026-08-24","1","130","53.00","64.00","64.00","11.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("306","3","2026-08-24","1","7","3.00","97.00","95.00","94.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("307","3","2026-08-24","1","119","78.00","84.00","84.00","6.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("308","3","2026-08-24","1","136","48.00","25.00","25.00","-23.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("309","3","2026-08-24","1","69","34.00","56.00","54.00","22.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("310","3","2026-08-24","1","292","0.00","56.00","56.00","56.00","0","2026-08-25 10:52:13");
INSERT INTO kp_counter_close_stock_take_items VALUES("311","3","2026-08-24","1","141","39.00","43.00","43.00","4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("312","3","2026-08-24","1","8","53.00","49.00","47.00","-4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("313","3","2026-08-24","1","127","63.00","78.00","78.00","15.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("314","3","2026-08-24","1","131","34.00","30.00","30.00","-4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("315","3","2026-08-24","1","124","89.00","95.00","95.00","6.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("316","3","2026-08-24","1","71","62.00","135.00","134.00","73.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("317","3","2026-08-24","1","86","84.00","75.00","75.00","-9.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("318","3","2026-08-24","1","125","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("319","3","2026-08-24","1","135","30.00","26.00","26.00","-4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("320","3","2026-08-24","1","9","71.00","80.00","80.00","9.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("321","3","2026-08-24","1","120","55.00","123.00","121.00","68.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("322","3","2026-08-24","1","4","29.00","26.00","26.00","-3.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("323","3","2026-08-24","1","138","29.00","18.00","18.00","-11.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("324","3","2026-08-24","1","140","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("325","3","2026-08-24","1","6","45.00","23.00","23.00","-22.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("326","3","2026-08-24","1","5","10.00","14.00","14.00","4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("327","3","2026-08-24","1","10","41.00","33.00","33.00","-8.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("328","3","2026-08-24","1","56","33.00","33.00","33.00","0.00","1","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("329","3","2026-08-24","1","142","37.00","25.00","23.00","-12.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("330","3","2026-08-24","1","105","16.00","20.00","20.00","4.00","0","2026-08-25 10:52:14");
INSERT INTO kp_counter_close_stock_take_items VALUES("331","3","2026-08-24","1","11","17.00","11.00","11.00","-6.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("332","3","2026-08-24","1","115","54.00","42.00","42.00","-12.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("333","3","2026-08-24","1","70","67.00","43.00","43.00","-24.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("334","3","2026-08-24","1","19","50.00","27.00","26.00","-23.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("335","3","2026-08-24","1","139","26.00","15.00","15.00","-11.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("336","3","2026-08-24","1","13","34.00","29.00","29.00","-5.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("337","3","2026-08-24","1","12","34.00","33.00","33.00","-1.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("338","3","2026-08-24","1","14","19.00","45.00","45.00","26.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("339","3","2026-08-24","1","15","24.00","25.00","21.00","1.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("340","3","2026-08-24","1","137","12.00","12.00","12.00","0.00","1","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("341","3","2026-08-24","1","16","29.00","27.00","27.00","-2.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("342","3","2026-08-24","1","285","10.00","9.00","9.00","-1.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("343","3","2026-08-24","1","288","11.00","9.00","9.00","-2.00","0","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("344","3","2026-08-24","1","289","12.00","12.00","12.00","0.00","1","2026-08-25 10:52:15");
INSERT INTO kp_counter_close_stock_take_items VALUES("345","3","2026-08-24","1","287","5.00","3.00","3.00","-2.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("346","3","2026-08-24","1","284","25.00","24.00","24.00","-1.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("347","3","2026-08-24","1","281","40.00","40.00","40.00","0.00","1","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("348","3","2026-08-24","1","286","3.00","7.00","7.00","4.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("349","3","2026-08-24","1","282","22.00","5.00","5.00","-17.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("350","3","2026-08-24","1","291","0.00","1.00","0.00","1.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("351","3","2026-08-24","1","283","10.00","0.00","0.00","-10.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("352","3","2026-08-24","1","290","6.00","5.00","5.00","-1.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("353","3","2026-08-24","1","317","18.00","15.00","15.00","-3.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("354","3","2026-08-24","1","145","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("355","3","2026-08-24","1","25","61.00","48.00","48.00","-13.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("356","3","2026-08-24","1","299","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("357","3","2026-08-24","1","23","57.00","42.00","39.00","-15.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("358","3","2026-08-24","1","108","30.00","62.00","62.00","32.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("359","3","2026-08-24","1","146","1.00","2.00","2.00","1.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("360","3","2026-08-24","1","76","16.00","14.00","14.00","-2.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("361","3","2026-08-24","1","104","2.00","20.00","20.00","18.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("362","3","2026-08-24","1","59","22.00","26.00","26.00","4.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("363","3","2026-08-24","1","22","152.00","153.00","151.00","1.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("364","3","2026-08-24","1","143","11.00","7.00","6.00","-4.00","0","2026-08-25 10:52:16");
INSERT INTO kp_counter_close_stock_take_items VALUES("365","3","2026-08-24","1","144","114.00","142.00","140.00","28.00","0","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("366","3","2026-08-24","1","244","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("367","3","2026-08-24","1","243","5.00","5.00","5.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("368","3","2026-08-24","1","175","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("369","3","2026-08-24","1","306","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("370","3","2026-08-24","1","229","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("371","3","2026-08-24","1","230","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("372","3","2026-08-24","1","309","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("373","3","2026-08-24","1","308","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("374","3","2026-08-24","1","222","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("375","3","2026-08-24","1","224","1.00","3.00","3.00","2.00","0","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("376","3","2026-08-24","1","223","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("377","3","2026-08-24","1","221","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("378","3","2026-08-24","1","236","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("379","3","2026-08-24","1","235","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("380","3","2026-08-24","1","234","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("381","3","2026-08-24","1","254","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("382","3","2026-08-24","1","178","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("383","3","2026-08-24","1","200","5.00","5.00","5.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("384","3","2026-08-24","1","199","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("385","3","2026-08-24","1","2","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:17");
INSERT INTO kp_counter_close_stock_take_items VALUES("386","3","2026-08-24","1","190","7.00","7.00","5.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("387","3","2026-08-24","1","3","3.00","0.00","0.00","-3.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("388","3","2026-08-24","1","181","4.00","7.00","7.00","3.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("389","3","2026-08-24","1","180","5.00","7.00","7.00","2.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("390","3","2026-08-24","1","179","3.00","2.00","2.00","-1.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("391","3","2026-08-24","1","209","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("392","3","2026-08-24","1","239","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("393","3","2026-08-24","1","97","976.00","932.00","932.00","-44.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("394","3","2026-08-24","1","92","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("395","3","2026-08-24","1","83","4.00","7.00","7.00","3.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("396","3","2026-08-24","1","300","4.00","6.00","6.00","2.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("397","3","2026-08-24","1","301","4.00","5.00","5.00","1.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("398","3","2026-08-24","1","301","4.00","5.00","5.00","1.00","0","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("399","3","2026-08-24","1","84","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("400","3","2026-08-24","1","225","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("401","3","2026-08-24","1","226","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("402","3","2026-08-24","1","227","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("403","3","2026-08-24","1","227","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("404","3","2026-08-24","1","228","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("405","3","2026-08-24","1","258","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("406","3","2026-08-24","1","159","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("407","3","2026-08-24","1","159","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:18");
INSERT INTO kp_counter_close_stock_take_items VALUES("408","3","2026-08-24","1","160","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("409","3","2026-08-24","1","255","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("410","3","2026-08-24","1","237","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("411","3","2026-08-24","1","238","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("412","3","2026-08-24","1","193","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("413","3","2026-08-24","1","30","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("414","3","2026-08-24","1","29","3.00","2.00","2.00","-1.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("415","3","2026-08-24","1","207","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("416","3","2026-08-24","1","217","2.00","0.00","0.00","-2.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("417","3","2026-08-24","1","246","7.00","5.00","5.00","-2.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("418","3","2026-08-24","1","37","3.00","6.00","6.00","3.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("419","3","2026-08-24","1","247","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("420","3","2026-08-24","1","109","926.00","810.00","810.00","-116.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("421","3","2026-08-24","1","17","4.00","3.00","2.00","-1.00","0","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("422","3","2026-08-24","1","176","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("423","3","2026-08-24","1","176","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("424","3","2026-08-24","1","203","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("425","3","2026-08-24","1","204","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:19");
INSERT INTO kp_counter_close_stock_take_items VALUES("426","3","2026-08-24","1","205","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("427","3","2026-08-24","1","212","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("428","3","2026-08-24","1","211","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("429","3","2026-08-24","1","206","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("430","3","2026-08-24","1","206","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("431","3","2026-08-24","1","248","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("432","3","2026-08-24","1","18","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("433","3","2026-08-24","1","249","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("434","3","2026-08-24","1","101","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("435","3","2026-08-24","1","95","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("436","3","2026-08-24","1","197","2.00","4.00","4.00","2.00","0","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("437","3","2026-08-24","1","196","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("438","3","2026-08-24","1","214","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("439","3","2026-08-24","1","189","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("440","3","2026-08-24","1","245","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("441","3","2026-08-24","1","216","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:20");
INSERT INTO kp_counter_close_stock_take_items VALUES("442","3","2026-08-24","1","163","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("443","3","2026-08-24","1","161","2.00","3.00","3.00","1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("444","3","2026-08-24","1","57","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("445","3","2026-08-24","1","167","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("446","3","2026-08-24","1","162","3.00","2.00","2.00","-1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("447","3","2026-08-24","1","304","6.00","2.00","2.00","-4.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("448","3","2026-08-24","1","303","5.00","5.00","5.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("449","3","2026-08-24","1","302","4.00","3.00","3.00","-1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("450","3","2026-08-24","1","194","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("451","3","2026-08-24","1","184","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("452","3","2026-08-24","1","41","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("453","3","2026-08-24","1","82","1.00","2.00","2.00","1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("454","3","2026-08-24","1","112","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("455","3","2026-08-24","1","27","3.00","2.00","2.00","-1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("456","3","2026-08-24","1","201","2.00","3.00","3.00","1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("457","3","2026-08-24","1","28","2.00","3.00","3.00","1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("458","3","2026-08-24","1","202","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("459","3","2026-08-24","1","182","4.00","5.00","5.00","1.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("460","3","2026-08-24","1","36","1.00","3.00","2.00","2.00","0","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("461","3","2026-08-24","1","186","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:21");
INSERT INTO kp_counter_close_stock_take_items VALUES("462","3","2026-08-24","1","185","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("463","3","2026-08-24","1","183","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("464","3","2026-08-24","1","213","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("465","3","2026-08-24","1","208","5.00","5.00","5.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("466","3","2026-08-24","1","33","2.00","0.00","0.00","-2.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("467","3","2026-08-24","1","34","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("468","3","2026-08-24","1","218","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("469","3","2026-08-24","1","215","1.00","2.00","2.00","1.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("470","3","2026-08-24","1","1","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("471","3","2026-08-24","1","93","0.00","1431.00","1431.00","1431.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("472","3","2026-08-24","1","94","578.00","1245.00","1245.00","667.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("473","3","2026-08-24","1","310","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("474","3","2026-08-24","1","169","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("475","3","2026-08-24","1","170","1.00","0.00","0.00","-1.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("476","3","2026-08-24","1","191","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("477","3","2026-08-24","1","192","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("478","3","2026-08-24","1","294","12.00","0.00","0.00","-12.00","0","2026-08-25 10:52:22");
INSERT INTO kp_counter_close_stock_take_items VALUES("479","3","2026-08-24","1","171","5.00","5.00","5.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("480","3","2026-08-24","1","77","11.00","3.00","3.00","-8.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("481","3","2026-08-24","1","78","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("482","3","2026-08-24","1","87","0.00","3.00","3.00","3.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("483","3","2026-08-24","1","293","0.00","5.00","5.00","5.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("484","3","2026-08-24","1","85","0.00","6.00","6.00","6.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("485","3","2026-08-24","1","195","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("486","3","2026-08-24","1","256","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("487","3","2026-08-24","1","257","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("488","3","2026-08-24","1","64","1.00","2.00","2.00","1.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("489","3","2026-08-24","1","103","881.00","881.00","881.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("490","3","2026-08-24","1","31","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("491","3","2026-08-24","1","32","0.00","2.00","2.00","2.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("492","3","2026-08-24","1","168","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("493","3","2026-08-24","1","307","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("494","3","2026-08-24","1","164","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("495","3","2026-08-24","1","177","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("496","3","2026-08-24","1","233","0.00","1.00","1.00","1.00","0","2026-08-25 10:52:23");
INSERT INTO kp_counter_close_stock_take_items VALUES("497","3","2026-08-24","1","187","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("498","3","2026-08-24","1","188","10.00","10.00","10.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("499","3","2026-08-24","1","305","3.00","4.00","4.00","1.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("500","3","2026-08-24","1","296","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("501","3","2026-08-24","1","107","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("502","3","2026-08-24","1","165","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("503","3","2026-08-24","1","157","7.00","6.00","6.00","-1.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("504","3","2026-08-24","1","99","6.00","5.00","5.00","-1.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("505","3","2026-08-24","1","100","3.00","2.00","2.00","-1.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("506","3","2026-08-24","1","166","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("507","3","2026-08-24","1","172","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("508","3","2026-08-24","1","173","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("509","3","2026-08-24","1","174","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("510","3","2026-08-24","1","240","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("511","3","2026-08-24","1","319","0.00","8.00","8.00","8.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("512","3","2026-08-24","1","242","8.00","9.00","9.00","1.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("513","3","2026-08-24","1","39","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("514","3","2026-08-24","1","232","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("515","3","2026-08-24","1","231","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("516","3","2026-08-24","1","252","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("517","3","2026-08-24","1","253","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("518","3","2026-08-24","1","250","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("519","3","2026-08-24","1","251","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("520","3","2026-08-24","1","210","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("521","3","2026-08-24","1","220","2.00","4.00","4.00","2.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("522","3","2026-08-24","1","219","4.00","2.00","2.00","-2.00","0","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("523","3","2026-08-24","1","113","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:24");
INSERT INTO kp_counter_close_stock_take_items VALUES("524","3","2026-08-24","1","98","3.00","1.00","0.00","-2.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("525","3","2026-08-24","1","40","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("526","3","2026-08-24","1","158","3.00","5.00","5.00","2.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("527","3","2026-08-24","1","111","7.00","9.00","9.00","2.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("528","3","2026-08-24","1","89","4.00","16.00","16.00","12.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("529","3","2026-08-24","1","198","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("530","3","2026-08-24","1","277","1438.00","1088.00","1088.00","-350.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("531","3","2026-08-24","1","267","1120.00","1120.00","1120.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("532","3","2026-08-24","1","264","1000.00","1000.00","1000.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("533","3","2026-08-24","1","278","702.00","974.00","974.00","272.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("534","3","2026-08-24","1","318","630.00","630.00","630.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("535","3","2026-08-24","1","265","685.00","1007.00","1007.00","322.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("536","3","2026-08-24","1","275","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("537","3","2026-08-24","1","261","751.00","751.00","751.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("538","3","2026-08-24","1","268","1056.00","968.00","968.00","-88.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("539","3","2026-08-24","1","269","1637.00","1637.00","1637.00","0.00","1","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("540","3","2026-08-24","1","58","1501.00","0.00","0.00","-1501.00","0","2026-08-25 10:52:25");
INSERT INTO kp_counter_close_stock_take_items VALUES("541","3","2026-08-24","1","263","1501.00","1003.00","1003.00","-498.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("542","3","2026-08-24","1","273","0.00","1357.00","1357.00","1357.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("543","3","2026-08-24","1","274","1145.00","982.00","982.00","-163.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("544","3","2026-08-24","1","279","1516.00","1273.00","1273.00","-243.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("545","3","2026-08-24","1","270","1131.00","1045.00","1045.00","-86.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("546","3","2026-08-24","1","276","932.00","932.00","932.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("547","3","2026-08-24","1","271","1138.00","2019.00","2019.00","881.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("548","3","2026-08-24","1","260","1187.00","1137.00","1137.00","-50.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("549","3","2026-08-24","1","266","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("550","3","2026-08-24","1","259","1120.00","1120.00","1120.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("551","3","2026-08-24","1","262","758.00","671.00","671.00","-87.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("552","3","2026-08-24","1","272","999.00","999.00","999.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("553","3","2026-08-24","1","72","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("554","3","2026-08-24","1","314","1062.00","544.00","544.00","-518.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("555","3","2026-08-24","1","48","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("556","3","2026-08-24","1","315","3355.00","3355.00","3355.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("557","3","2026-08-24","1","153","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("558","3","2026-08-24","1","295","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("559","3","2026-08-24","1","66","2.00","0.00","0.00","-2.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("560","3","2026-08-24","1","150","7.00","6.00","6.00","-1.00","0","2026-08-25 10:52:26");
INSERT INTO kp_counter_close_stock_take_items VALUES("561","3","2026-08-24","1","151","5.00","9.00","9.00","4.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("562","3","2026-08-24","1","297","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("563","3","2026-08-24","1","298","0.00","0.00","0.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("564","3","2026-08-24","1","320","0.00","2.00","2.00","2.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("565","3","2026-08-24","1","149","8.00","5.00","5.00","-3.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("566","3","2026-08-24","1","148","0.00","4.00","4.00","4.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("567","3","2026-08-24","1","152","3.00","4.00","4.00","1.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("568","3","2026-08-24","1","313","5266.00","4030.00","4030.00","-1236.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("569","3","2026-08-24","1","51","4.00","3.00","3.00","-1.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("570","3","2026-08-24","1","312","4968.00","4728.00","4728.00","-240.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("571","3","2026-08-24","1","147","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("572","3","2026-08-24","1","96","2417.00","1937.00","1937.00","-480.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("573","3","2026-08-24","1","316","1018.00","1018.00","1018.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("574","3","2026-08-24","1","67","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("575","3","2026-08-24","1","52","4.00","4.00","4.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("576","3","2026-08-24","1","53","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("577","3","2026-08-24","1","75","4.00","3.00","3.00","-1.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("578","3","2026-08-24","1","117","2.00","2.00","2.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("579","3","2026-08-24","1","156","3.00","3.00","3.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("580","3","2026-08-24","1","154","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("581","3","2026-08-24","1","60","2.00","1.00","1.00","-1.00","0","2026-08-25 10:52:27");
INSERT INTO kp_counter_close_stock_take_items VALUES("582","3","2026-08-24","1","155","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:28");
INSERT INTO kp_counter_close_stock_take_items VALUES("583","3","2026-08-24","1","155","1.00","1.00","1.00","0.00","1","2026-08-25 10:52:28");
INSERT INTO kp_counter_close_stock_take_items VALUES("584","3","2026-08-24","1","73","1.00","3.00","3.00","2.00","0","2026-08-25 10:52:28");
INSERT INTO kp_counter_close_stock_take_items VALUES("585","3","2026-08-24","1","74","4.00","2.00","2.00","-2.00","0","2026-08-25 10:52:28");
INSERT INTO kp_counter_close_stock_take_items VALUES("586","3","2026-08-24","1","123","76.00","76.00","74.00","0.00","1","2026-08-25 10:52:28");





CREATE TABLE `kp_counter_close_summary` (
  `close_sum_id` int NOT NULL AUTO_INCREMENT,
  `shift_date` date NOT NULL,
  `cntr_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `open_ord` int NOT NULL DEFAULT '0',
  `close_ord` int NOT NULL DEFAULT '0',
  `open_orders` int NOT NULL DEFAULT '0',
  `pending_returns` int NOT NULL DEFAULT '0',
  `pending_transfers` int NOT NULL DEFAULT '0',
  `total_bills` int NOT NULL DEFAULT '0',
  `total_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_item_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bar_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `kitchen_sales` decimal(10,2) NOT NULL DEFAULT '0.00',
  `expense_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `day_credit_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `credit_paid_today_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `credit_paid_previous_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `same_day_payments_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `previous_day_payments_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payments_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `today_requests_total` int NOT NULL DEFAULT '0',
  `today_returns_accepted_total` int NOT NULL DEFAULT '0',
  `today_returns_rejected_total` int NOT NULL DEFAULT '0',
  `today_voided_items_total` int NOT NULL DEFAULT '0',
  `today_items_sold_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `today_transfer_to_total` int NOT NULL DEFAULT '0',
  `today_transfer_from_total` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`close_sum_id`),
  KEY `idx_counter_shift` (`cntr_id`,`shift_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_counter_close_summary VALUES("1","2026-08-11","1","1","25006","25006","0","0","0","1","0.00","0.00","0.00","0.00","0.00","0.00","0.00","0.00","300.00","0.00","300.00","4","2","0","0","0.00","0","0","2026-08-17 13:11:03");
INSERT INTO kp_counter_close_summary VALUES("2","2026-08-17","1","1","25006","25007","0","0","0","0","0.00","26700.00","26700.00","0.00","0.00","0.00","0.00","0.00","0.00","0.00","0.00","3","0","0","0","102.00","0","0","2026-08-24 10:31:40");
INSERT INTO kp_counter_close_summary VALUES("3","2026-08-24","1","4","25007","20","17","0","0","17","23740.00","25240.00","25240.00","0.00","0.00","0.00","0.00","0.00","0.00","0.00","0.00","2","0","0","0","50.00","0","0","2026-08-25 10:52:12");





CREATE TABLE `kp_counter_handover` (
  `handover_id` int NOT NULL AUTO_INCREMENT,
  `shift_date` date NOT NULL,
  `cntr_id` int NOT NULL DEFAULT '0',
  `from_user` int NOT NULL DEFAULT '0',
  `to_user` int NOT NULL DEFAULT '0',
  `total_system` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_declared` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_variance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'MATCH',
  `note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`handover_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_counter_handover_modes` (
  `handover_mode_id` int NOT NULL AUTO_INCREMENT,
  `handover_id` int NOT NULL DEFAULT '0',
  `pay_id` int NOT NULL DEFAULT '0',
  `system_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `declared_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `variance_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'MATCH',
  PRIMARY KEY (`handover_mode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_counter_hist` (
  `cntr_id` int NOT NULL,
  `shift` date NOT NULL,
  `opening` datetime DEFAULT NULL,
  `closing` datetime DEFAULT NULL,
  `open_by` int DEFAULT NULL,
  `close_by` int DEFAULT NULL,
  `open_ord` int DEFAULT NULL,
  `close_ord` int DEFAULT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_hist VALUES("1","2026-08-11","2026-08-11 18:18:18","2026-08-17 13:11:02","1","1","25006","25006","2");
INSERT INTO kp_counter_hist VALUES("1","2026-08-17","2026-08-17 13:11:59","2026-08-24 10:31:39","1","1","25006","25007","2");
INSERT INTO kp_counter_hist VALUES("1","2026-08-24","2026-08-24 10:32:22","2026-08-25 10:52:11","1","4","25007","20","2");
INSERT INTO kp_counter_hist VALUES("1","2026-08-24","2026-08-24 11:34:36","2026-08-25 10:52:11","4","4","0","20","2");





CREATE TABLE `kp_counter_history` (
  `cntr_id` int NOT NULL,
  `transaction` varchar(55) NOT NULL,
  `reason` varchar(55) NOT NULL,
  `user_id` int NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_history VALUES("1","CHANGED CASHIER FROM 4 TO KTOUCH_ADMIN"," test","1","2026-07-28","2026-08-11 18:15:32");
INSERT INTO kp_counter_history VALUES("1","CHANGED COUNTER STATUS  TO CONTINUING","","1","2026-07-28","2026-08-11 18:17:07");
INSERT INTO kp_counter_history VALUES("1","CHANGED COUNTER STATUS  TO FINISHED","","1","2026-07-28","2026-08-11 18:17:26");
INSERT INTO kp_counter_history VALUES("1","CHANGED COUNTER STATUS  TO FINISHED","","1","2026-08-11","2026-08-11 18:19:47");
INSERT INTO kp_counter_history VALUES("1","CHANGED CASHIER FROM 1 TO HARUN"," JHJ","1","2026-08-24","2026-08-24 11:31:51");





CREATE TABLE `kp_counter_req` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_id` int NOT NULL,
  `req_user` int NOT NULL,
  `req_shift` varchar(55) NOT NULL DEFAULT '',
  `issue_user` int DEFAULT NULL,
  `issue_status` int NOT NULL DEFAULT '2',
  `issue_shift` date DEFAULT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `rec_user` int NOT NULL DEFAULT '2',
  `rec_shift` date DEFAULT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `req_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `counter` varchar(55) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `issue_created_at` timestamp NULL DEFAULT NULL,
  `rec_created_at` timestamp NULL DEFAULT NULL,
  `print_cntr_id` int NOT NULL DEFAULT '0',
  `issued_by` int NOT NULL DEFAULT '0',
  `received_by` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `req_id` (`req_id`),
  KEY `idx_kp_counter_req_req_id` (`req_id`),
  KEY `idx_kp_counter_req_req_user` (`req_user`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_req VALUES("1","405","1","2026-08-11","1","1","2026-08-11","1","2","","2026-08-11 19:58:39","2","1","1","2026-08-11 19:44:55","","2026-08-11 19:58:39","1","0","0");
INSERT INTO kp_counter_req VALUES("2","406","1","2026-08-11","1","1","2026-08-12","1","2","","2026-08-12 21:04:58","2","1","1","2026-08-12 20:15:24","2026-08-12 20:52:13","2026-08-12 21:04:58","1","0","0");
INSERT INTO kp_counter_req VALUES("3","407","1","2026-08-11","1","1","2026-08-12","1","2","","2026-08-14 22:24:41","2","1","1","2026-08-12 21:31:31","","2026-08-14 22:24:41","1","0","0");
INSERT INTO kp_counter_req VALUES("4","408","1","2026-08-11","","2","","2","2","","2026-08-17 13:07:17","2","2","1","2026-08-17 13:07:16","","","1","0","0");
INSERT INTO kp_counter_req VALUES("5","409","1","2026-08-17","","2","","2","2","","2026-08-17 17:23:19","2","2","1","2026-08-17 17:23:19","","","1","0","0");
INSERT INTO kp_counter_req VALUES("6","410","1","2026-08-17","","2","","2","2","","2026-08-19 13:24:54","2","2","1","2026-08-19 13:24:54","","","1","0","0");
INSERT INTO kp_counter_req VALUES("7","411","1","2026-08-17","","2","","2","2","","2026-08-19 17:47:21","2","2","1","2026-08-19 17:47:21","","","1","0","0");
INSERT INTO kp_counter_req VALUES("8","404","4","2026-08-24","","2","","2","2","","2026-08-24 18:50:14","2","2","1","2026-08-24 18:50:14","","","1","0","0");
INSERT INTO kp_counter_req VALUES("9","412","4","2026-08-24","","2","","2","2","","2026-08-24 19:03:54","2","2","1","2026-08-24 19:03:54","","","1","0","0");





CREATE TABLE `kp_counter_req_del` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_id` int NOT NULL,
  `user_id` int NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(155) COLLATE latin1_general_ci NOT NULL,
  `counter` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_counter_req_del VALUES("1","384","10","2026-06-28","2026-06-28 10:20:08"," Uotdated","1");





CREATE TABLE `kp_counter_req_del_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `req_id` int NOT NULL,
  `user_id` int NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `counter` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_counter_req_id` (
  `req_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_req_id VALUES("412");





CREATE TABLE `kp_counter_req_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_id` int NOT NULL,
  `item_id` int NOT NULL,
  `req_qty` decimal(12,2) NOT NULL DEFAULT '0.00',
  `req_shift` varchar(55) NOT NULL DEFAULT '',
  `req_user` int DEFAULT NULL,
  `req_status` int NOT NULL DEFAULT '2',
  `issue_qty` decimal(12,2) NOT NULL DEFAULT '0.00',
  `issue_shift` date DEFAULT NULL,
  `issue_status` int NOT NULL DEFAULT '2',
  `issue_user` int DEFAULT NULL,
  `rec_qty` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rec_shift` date DEFAULT NULL,
  `rec_user` int DEFAULT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `counter` varchar(55) NOT NULL DEFAULT '',
  `source_counter` varchar(55) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kp_counter_req_items_req_id` (`req_id`),
  KEY `idx_kp_counter_req_items_item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_req_items VALUES("1","405","71","50.00","2026-08-11","1","2","0.00","2026-08-11","1","1","50.00","","","1","1","1","","2026-08-11 18:21:23");
INSERT INTO kp_counter_req_items VALUES("2","405","119","25.00","2026-08-11","1","2","0.00","2026-08-11","1","1","25.00","","","1","1","1","","2026-08-11 18:21:54");
INSERT INTO kp_counter_req_items VALUES("3","405","86","25.00","2026-08-11","1","2","0.00","2026-08-11","1","1","25.00","","","1","1","1","","2026-08-11 18:23:18");
INSERT INTO kp_counter_req_items VALUES("4","405","120","100.00","2026-08-11","1","2","0.00","2026-08-11","1","1","100.00","","","1","1","1","","2026-08-11 18:23:43");
INSERT INTO kp_counter_req_items VALUES("5","405","123","75.00","2026-08-11","1","2","0.00","2026-08-11","1","1","75.00","","","1","1","1","","2026-08-11 18:24:07");
INSERT INTO kp_counter_req_items VALUES("6","405","128","24.00","2026-08-11","1","2","0.00","2026-08-11","1","1","24.00","","","1","1","1","","2026-08-11 18:24:43");
INSERT INTO kp_counter_req_items VALUES("7","405","12","24.00","2026-08-11","1","2","0.00","2026-08-11","1","1","24.00","","","1","1","1","","2026-08-11 18:27:14");
INSERT INTO kp_counter_req_items VALUES("8","405","144","72.00","2026-08-11","1","2","0.00","2026-08-11","1","1","72.00","","","1","1","1","","2026-08-11 18:28:03");
INSERT INTO kp_counter_req_items VALUES("9","405","23","12.00","2026-08-11","1","2","0.00","2026-08-11","1","1","12.00","","","1","1","1","","2026-08-11 18:30:28");
INSERT INTO kp_counter_req_items VALUES("10","405","25","12.00","2026-08-11","1","2","0.00","2026-08-11","1","1","12.00","","","1","1","1","","2026-08-11 18:33:08");
INSERT INTO kp_counter_req_items VALUES("11","405","22","24.00","2026-08-11","1","2","0.00","2026-08-11","1","1","24.00","","","1","1","1","","2026-08-11 18:34:30");
INSERT INTO kp_counter_req_items VALUES("12","405","136","25.00","2026-08-11","1","2","0.00","2026-08-11","1","1","25.00","","","1","1","1","","2026-08-11 18:36:12");
INSERT INTO kp_counter_req_items VALUES("13","405","151","3.00","2026-08-11","1","2","0.00","2026-08-11","1","1","3.00","","","1","1","1","","2026-08-11 18:37:20");
INSERT INTO kp_counter_req_items VALUES("14","405","48","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 18:37:43");
INSERT INTO kp_counter_req_items VALUES("15","405","53","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 18:38:33");
INSERT INTO kp_counter_req_items VALUES("16","405","67","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 18:38:54");
INSERT INTO kp_counter_req_items VALUES("17","405","60","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 18:39:23");
INSERT INTO kp_counter_req_items VALUES("18","405","89","12.00","2026-08-11","1","2","0.00","2026-08-11","1","1","12.00","","","1","1","1","","2026-08-11 18:41:31");
INSERT INTO kp_counter_req_items VALUES("19","405","111","12.00","2026-08-11","1","2","0.00","2026-08-11","1","1","12.00","","","1","1","1","","2026-08-11 18:41:47");
INSERT INTO kp_counter_req_items VALUES("20","405","158","5.00","2026-08-11","1","2","0.00","2026-08-11","1","1","5.00","","","1","1","1","","2026-08-11 18:42:02");
INSERT INTO kp_counter_req_items VALUES("21","405","77","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 18:43:35");
INSERT INTO kp_counter_req_items VALUES("22","405","78","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 18:43:55");
INSERT INTO kp_counter_req_items VALUES("23","405","127","25.00","2026-08-11","1","2","0.00","2026-08-11","1","1","25.00","","","1","1","1","","2026-08-11 19:34:46");
INSERT INTO kp_counter_req_items VALUES("24","405","132","25.00","2026-08-11","1","2","0.00","2026-08-11","1","1","25.00","","","1","1","1","","2026-08-11 19:37:49");
INSERT INTO kp_counter_req_items VALUES("25","405","293","3.00","2026-08-11","1","2","0.00","2026-08-11","1","1","3.00","","","1","1","1","","2026-08-11 19:38:46");
INSERT INTO kp_counter_req_items VALUES("26","405","294","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 19:39:17");
INSERT INTO kp_counter_req_items VALUES("27","405","27","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 19:40:36");
INSERT INTO kp_counter_req_items VALUES("28","405","207","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 19:41:51");
INSERT INTO kp_counter_req_items VALUES("29","405","30","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 19:42:04");
INSERT INTO kp_counter_req_items VALUES("30","405","40","1.00","2026-08-11","1","2","0.00","2026-08-11","1","1","1.00","","","1","1","1","","2026-08-11 19:42:21");
INSERT INTO kp_counter_req_items VALUES("31","405","242","4.00","2026-08-11","1","2","0.00","2026-08-11","1","1","4.00","","","1","1","1","","2026-08-11 19:42:49");
INSERT INTO kp_counter_req_items VALUES("32","405","241","2.00","2026-08-11","1","2","0.00","2026-08-11","1","1","2.00","","","1","1","1","","2026-08-11 19:43:01");
INSERT INTO kp_counter_req_items VALUES("36","406","120","200.00","2026-08-11","1","2","200.00","2026-08-12","1","1","200.00","2026-08-11","1","1","1","1","","2026-08-12 20:02:28");
INSERT INTO kp_counter_req_items VALUES("37","406","126","100.00","2026-08-11","1","2","100.00","2026-08-12","1","1","100.00","2026-08-11","1","1","1","1","","2026-08-12 20:03:04");
INSERT INTO kp_counter_req_items VALUES("38","406","71","100.00","2026-08-11","1","2","100.00","2026-08-12","1","1","100.00","2026-08-11","1","1","1","1","","2026-08-12 20:03:26");
INSERT INTO kp_counter_req_items VALUES("39","406","124","25.00","2026-08-11","1","2","25.00","2026-08-12","1","1","25.00","2026-08-11","1","1","1","1","","2026-08-12 20:09:32");
INSERT INTO kp_counter_req_items VALUES("40","406","132","25.00","2026-08-11","1","2","25.00","2026-08-12","1","1","25.00","2026-08-11","1","1","1","1","","2026-08-12 20:10:01");
INSERT INTO kp_counter_req_items VALUES("41","406","7","25.00","2026-08-11","1","2","25.00","2026-08-12","1","1","25.00","2026-08-11","1","1","1","1","","2026-08-12 20:10:25");
INSERT INTO kp_counter_req_items VALUES("42","406","8","25.00","2026-08-11","1","2","25.00","2026-08-12","1","1","25.00","2026-08-11","1","1","1","1","","2026-08-12 20:10:39");
INSERT INTO kp_counter_req_items VALUES("43","406","152","1.00","2026-08-11","1","2","1.00","2026-08-12","1","1","1.00","2026-08-11","1","1","1","1","","2026-08-12 20:10:58");
INSERT INTO kp_counter_req_items VALUES("44","406","119","25.00","2026-08-11","1","2","25.00","2026-08-12","1","1","25.00","2026-08-11","1","1","1","1","","2026-08-12 20:11:33");
INSERT INTO kp_counter_req_items VALUES("45","406","292","24.00","2026-08-11","1","2","24.00","2026-08-12","1","1","24.00","2026-08-11","1","1","1","1","","2026-08-12 20:11:49");
INSERT INTO kp_counter_req_items VALUES("46","406","56","24.00","2026-08-11","1","2","24.00","2026-08-12","1","1","24.00","2026-08-11","1","1","1","1","","2026-08-12 20:12:08");
INSERT INTO kp_counter_req_items VALUES("47","406","144","60.00","2026-08-11","1","2","60.00","2026-08-12","1","1","60.00","2026-08-11","1","1","1","1","","2026-08-12 20:12:29");
INSERT INTO kp_counter_req_items VALUES("48","406","25","12.00","2026-08-11","1","2","12.00","2026-08-12","1","1","12.00","2026-08-11","1","1","1","1","","2026-08-12 20:12:41");
INSERT INTO kp_counter_req_items VALUES("49","407","295","2.00","2026-08-11","1","2","0.00","2026-08-12","1","1","0.00","2026-08-11","1","1","1","1","","2026-08-12 21:30:35");
INSERT INTO kp_counter_req_items VALUES("55","408","126","100.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:23:28");
INSERT INTO kp_counter_req_items VALUES("56","408","120","150.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:24:44");
INSERT INTO kp_counter_req_items VALUES("57","408","121","100.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:26:04");
INSERT INTO kp_counter_req_items VALUES("58","408","123","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:26:19");
INSERT INTO kp_counter_req_items VALUES("59","408","71","100.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:27:24");
INSERT INTO kp_counter_req_items VALUES("60","408","7","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:27:47");
INSERT INTO kp_counter_req_items VALUES("61","408","124","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:28:11");
INSERT INTO kp_counter_req_items VALUES("62","408","127","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:28:21");
INSERT INTO kp_counter_req_items VALUES("63","408","19","24.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:29:37");
INSERT INTO kp_counter_req_items VALUES("64","408","132","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:31:06");
INSERT INTO kp_counter_req_items VALUES("65","408","8","25.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:31:30");
INSERT INTO kp_counter_req_items VALUES("66","408","6","24.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:31:47");
INSERT INTO kp_counter_req_items VALUES("67","408","55","24.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:32:25");
INSERT INTO kp_counter_req_items VALUES("68","408","144","96.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:34:10");
INSERT INTO kp_counter_req_items VALUES("69","408","23","36.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:34:32");
INSERT INTO kp_counter_req_items VALUES("70","408","180","3.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:34:58");
INSERT INTO kp_counter_req_items VALUES("71","408","77","5.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:35:37");
INSERT INTO kp_counter_req_items VALUES("72","408","85","4.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:36:14");
INSERT INTO kp_counter_req_items VALUES("73","408","98","3.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:37:19");
INSERT INTO kp_counter_req_items VALUES("74","408","74","2.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:42:00");
INSERT INTO kp_counter_req_items VALUES("75","408","2","3.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-16 14:43:14");
INSERT INTO kp_counter_req_items VALUES("77","408","193","1.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 12:23:50");
INSERT INTO kp_counter_req_items VALUES("78","408","300","4.00","2026-08-11","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 12:38:07");
INSERT INTO kp_counter_req_items VALUES("79","409","71","150.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:39:06");
INSERT INTO kp_counter_req_items VALUES("80","409","119","25.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:39:33");
INSERT INTO kp_counter_req_items VALUES("81","409","7","125.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:40:36");
INSERT INTO kp_counter_req_items VALUES("82","409","126","100.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:41:01");
INSERT INTO kp_counter_req_items VALUES("83","409","120","150.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:41:35");
INSERT INTO kp_counter_req_items VALUES("84","409","123","50.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:41:51");
INSERT INTO kp_counter_req_items VALUES("85","409","121","75.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:42:12");
INSERT INTO kp_counter_req_items VALUES("86","409","127","25.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:42:30");
INSERT INTO kp_counter_req_items VALUES("87","409","5","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:44:48");
INSERT INTO kp_counter_req_items VALUES("88","409","14","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:45:19");
INSERT INTO kp_counter_req_items VALUES("89","409","15","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:45:35");
INSERT INTO kp_counter_req_items VALUES("90","409","141","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:46:26");
INSERT INTO kp_counter_req_items VALUES("91","409","158","5.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:49:35");
INSERT INTO kp_counter_req_items VALUES("92","409","89","12.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:50:28");
INSERT INTO kp_counter_req_items VALUES("93","409","111","12.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:50:37");
INSERT INTO kp_counter_req_items VALUES("94","409","37","4.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:54:33");
INSERT INTO kp_counter_req_items VALUES("95","409","36","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:55:27");
INSERT INTO kp_counter_req_items VALUES("96","409","2","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 16:58:08");
INSERT INTO kp_counter_req_items VALUES("97","409","215","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:00:27");
INSERT INTO kp_counter_req_items VALUES("98","409","95","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:01:25");
INSERT INTO kp_counter_req_items VALUES("99","409","196","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:05:41");
INSERT INTO kp_counter_req_items VALUES("100","409","242","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:09:17");
INSERT INTO kp_counter_req_items VALUES("101","409","98","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:10:49");
INSERT INTO kp_counter_req_items VALUES("102","409","32","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:13:40");
INSERT INTO kp_counter_req_items VALUES("103","409","82","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:15:39");
INSERT INTO kp_counter_req_items VALUES("104","409","25","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:18:50");
INSERT INTO kp_counter_req_items VALUES("105","409","22","96.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-17 17:21:42");
INSERT INTO kp_counter_req_items VALUES("106","410","126","100.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:08:44");
INSERT INTO kp_counter_req_items VALUES("107","410","121","75.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:09:06");
INSERT INTO kp_counter_req_items VALUES("108","410","123","25.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:09:28");
INSERT INTO kp_counter_req_items VALUES("109","410","71","50.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:09:57");
INSERT INTO kp_counter_req_items VALUES("110","410","124","50.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:10:14");
INSERT INTO kp_counter_req_items VALUES("111","410","120","150.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:10:30");
INSERT INTO kp_counter_req_items VALUES("112","410","7","100.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:10:53");
INSERT INTO kp_counter_req_items VALUES("113","410","132","25.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:11:09");
INSERT INTO kp_counter_req_items VALUES("114","410","8","25.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:11:44");
INSERT INTO kp_counter_req_items VALUES("115","410","141","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:12:00");
INSERT INTO kp_counter_req_items VALUES("116","410","144","72.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 13:12:56");
INSERT INTO kp_counter_req_items VALUES("117","411","105","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:18:48");
INSERT INTO kp_counter_req_items VALUES("118","411","292","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:19:05");
INSERT INTO kp_counter_req_items VALUES("119","411","23","12.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:19:26");
INSERT INTO kp_counter_req_items VALUES("120","411","25","36.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:19:48");
INSERT INTO kp_counter_req_items VALUES("121","411","22","48.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:20:15");
INSERT INTO kp_counter_req_items VALUES("122","411","104","24.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:26:05");
INSERT INTO kp_counter_req_items VALUES("123","411","151","5.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:27:07");
INSERT INTO kp_counter_req_items VALUES("124","411","152","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:27:44");
INSERT INTO kp_counter_req_items VALUES("125","411","72","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:27:56");
INSERT INTO kp_counter_req_items VALUES("126","411","53","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:28:30");
INSERT INTO kp_counter_req_items VALUES("127","411","75","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:30:03");
INSERT INTO kp_counter_req_items VALUES("128","411","67","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:30:36");
INSERT INTO kp_counter_req_items VALUES("129","411","73","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:30:57");
INSERT INTO kp_counter_req_items VALUES("130","411","156","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:31:24");
INSERT INTO kp_counter_req_items VALUES("131","411","111","12.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:31:54");
INSERT INTO kp_counter_req_items VALUES("132","411","161","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:34:07");
INSERT INTO kp_counter_req_items VALUES("133","411","78","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:34:38");
INSERT INTO kp_counter_req_items VALUES("134","411","77","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:34:51");
INSERT INTO kp_counter_req_items VALUES("135","411","293","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:35:03");
INSERT INTO kp_counter_req_items VALUES("136","411","85","4.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:35:25");
INSERT INTO kp_counter_req_items VALUES("137","411","181","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:36:02");
INSERT INTO kp_counter_req_items VALUES("138","411","36","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:36:42");
INSERT INTO kp_counter_req_items VALUES("139","411","3","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:37:42");
INSERT INTO kp_counter_req_items VALUES("140","411","190","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:38:00");
INSERT INTO kp_counter_req_items VALUES("141","411","2","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:38:17");
INSERT INTO kp_counter_req_items VALUES("142","411","197","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:38:37");
INSERT INTO kp_counter_req_items VALUES("143","411","28","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:38:55");
INSERT INTO kp_counter_req_items VALUES("144","411","40","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:39:47");
INSERT INTO kp_counter_req_items VALUES("145","411","98","4.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:40:07");
INSERT INTO kp_counter_req_items VALUES("146","411","207","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:41:21");
INSERT INTO kp_counter_req_items VALUES("147","411","37","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:42:46");
INSERT INTO kp_counter_req_items VALUES("148","411","246","2.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:42:57");
INSERT INTO kp_counter_req_items VALUES("149","411","242","3.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:45:54");
INSERT INTO kp_counter_req_items VALUES("150","411","233","1.00","2026-08-17","1","2","0.00","","2","","0.00","","","2","2","1","","2026-08-19 17:46:37");
INSERT INTO kp_counter_req_items VALUES("151","404","71","75.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:48:16");
INSERT INTO kp_counter_req_items VALUES("152","404","119","25.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:48:55");
INSERT INTO kp_counter_req_items VALUES("153","404","120","100.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:49:15");
INSERT INTO kp_counter_req_items VALUES("154","404","121","50.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:49:35");
INSERT INTO kp_counter_req_items VALUES("155","404","123","50.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:50:11");
INSERT INTO kp_counter_req_items VALUES("156","404","126","175.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:50:32");
INSERT INTO kp_counter_req_items VALUES("157","404","128","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:50:45");
INSERT INTO kp_counter_req_items VALUES("158","404","70","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:51:02");
INSERT INTO kp_counter_req_items VALUES("159","404","7","25.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:51:45");
INSERT INTO kp_counter_req_items VALUES("160","404","132","25.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:52:05");
INSERT INTO kp_counter_req_items VALUES("161","404","134","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:52:31");
INSERT INTO kp_counter_req_items VALUES("162","404","141","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:54:15");
INSERT INTO kp_counter_req_items VALUES("163","404","6","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:54:29");
INSERT INTO kp_counter_req_items VALUES("164","404","139","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:54:40");
INSERT INTO kp_counter_req_items VALUES("165","404","5","24.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:54:50");
INSERT INTO kp_counter_req_items VALUES("166","404","25","48.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:56:30");
INSERT INTO kp_counter_req_items VALUES("167","404","136","25.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:57:06");
INSERT INTO kp_counter_req_items VALUES("168","404","86","25.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:57:33");
INSERT INTO kp_counter_req_items VALUES("169","404","22","144.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:59:01");
INSERT INTO kp_counter_req_items VALUES("170","404","150","4.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 17:59:47");
INSERT INTO kp_counter_req_items VALUES("171","404","51","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:02:17");
INSERT INTO kp_counter_req_items VALUES("172","404","72","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:02:33");
INSERT INTO kp_counter_req_items VALUES("173","404","75","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:07:02");
INSERT INTO kp_counter_req_items VALUES("174","404","60","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:10:44");
INSERT INTO kp_counter_req_items VALUES("175","404","156","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:11:26");
INSERT INTO kp_counter_req_items VALUES("176","404","74","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:11:42");
INSERT INTO kp_counter_req_items VALUES("177","404","100","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:13:27");
INSERT INTO kp_counter_req_items VALUES("178","404","111","12.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:14:27");
INSERT INTO kp_counter_req_items VALUES("179","404","158","5.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:18:24");
INSERT INTO kp_counter_req_items VALUES("180","404","162","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:19:21");
INSERT INTO kp_counter_req_items VALUES("181","404","78","6.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:21:09");
INSERT INTO kp_counter_req_items VALUES("182","404","77","5.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:21:33");
INSERT INTO kp_counter_req_items VALUES("183","404","87","3.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:21:56");
INSERT INTO kp_counter_req_items VALUES("184","404","294","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:22:13");
INSERT INTO kp_counter_req_items VALUES("185","404","179","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:22:49");
INSERT INTO kp_counter_req_items VALUES("186","404","302","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:23:32");
INSERT INTO kp_counter_req_items VALUES("187","404","304","3.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:24:08");
INSERT INTO kp_counter_req_items VALUES("188","404","3","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:25:20");
INSERT INTO kp_counter_req_items VALUES("189","404","2","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:25:56");
INSERT INTO kp_counter_req_items VALUES("190","404","190","3.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:26:14");
INSERT INTO kp_counter_req_items VALUES("191","404","29","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:26:34");
INSERT INTO kp_counter_req_items VALUES("192","404","95","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:26:49");
INSERT INTO kp_counter_req_items VALUES("193","404","196","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:27:13");
INSERT INTO kp_counter_req_items VALUES("194","404","27","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:28:23");
INSERT INTO kp_counter_req_items VALUES("195","404","207","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:30:03");
INSERT INTO kp_counter_req_items VALUES("196","404","217","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:30:17");
INSERT INTO kp_counter_req_items VALUES("197","404","40","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:30:57");
INSERT INTO kp_counter_req_items VALUES("198","404","98","5.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:31:34");
INSERT INTO kp_counter_req_items VALUES("199","404","219","3.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:33:47");
INSERT INTO kp_counter_req_items VALUES("200","404","83","3.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:34:54");
INSERT INTO kp_counter_req_items VALUES("201","404","246","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:35:58");
INSERT INTO kp_counter_req_items VALUES("202","404","248","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:36:34");
INSERT INTO kp_counter_req_items VALUES("203","404","33","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 18:41:21");
INSERT INTO kp_counter_req_items VALUES("204","412","17","2.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 19:02:12");
INSERT INTO kp_counter_req_items VALUES("205","412","18","1.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 19:02:47");
INSERT INTO kp_counter_req_items VALUES("206","412","291","10.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 19:03:10");
INSERT INTO kp_counter_req_items VALUES("207","412","143","12.00","2026-08-24","4","2","0.00","","2","","0.00","","","2","2","1","","2026-08-24 19:03:33");





CREATE TABLE `kp_counter_req_status` (
  `req_id` int NOT NULL,
  `req_user_id` int NOT NULL,
  `req_status` int NOT NULL DEFAULT '2',
  `req_user` int NOT NULL DEFAULT '0',
  `counter` varchar(55) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_kp_counter_req_status_req_id` (`req_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_counter_req_status VALUES("5","25","2","0","","2026-06-07 13:11:47");
INSERT INTO kp_counter_req_status VALUES("7","2","2","0","","2026-06-07 13:11:47");
INSERT INTO kp_counter_req_status VALUES("256","17","2","0","","2026-06-07 13:11:47");





CREATE TABLE `kp_counter_trans` (
  `trans_id` int NOT NULL,
  `cntr_from` int NOT NULL,
  `cntr_to` int NOT NULL,
  `user_to` int NOT NULL,
  `user_from` int NOT NULL,
  `trans_datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accept_datime` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `shift` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_counter_trans_id` (
  `trans_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_counter_trans_id VALUES("0");





CREATE TABLE `kp_counter_trans_items` (
  `trans_id` int NOT NULL,
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `counter` int NOT NULL,
  `dpt` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_counter_trans_status` (
  `trans_id` int NOT NULL,
  `cntr_to` int NOT NULL,
  `cntr_from` int NOT NULL,
  `user_id` int NOT NULL,
  `shift` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_counters` (
  `cntr_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL,
  `dpt` int NOT NULL,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `del_status` int NOT NULL DEFAULT '2',
  `take_status` int DEFAULT '2',
  `counter_status` int NOT NULL DEFAULT '2',
  `kit_default` int NOT NULL DEFAULT '2',
  `close_status` int NOT NULL DEFAULT '2',
  `printer_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`cntr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

INSERT INTO kp_counters VALUES("1","MAIN COUNTER","1","4","2020-03-24 12:22:30","2","2","2","2","2","MAIN BAR");
INSERT INTO kp_counters VALUES("2","COUNTER B","1","16","2020-03-24 12:23:27","2","2","2","2","2","COUNTER B");





CREATE TABLE `kp_cust_hist` (
  `cust_id` int NOT NULL,
  `trans` varchar(22) COLLATE latin1_general_ci NOT NULL,
  `trans_column` varchar(22) COLLATE latin1_general_ci NOT NULL,
  `trans_id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cf_bal` decimal(10,2) NOT NULL,
  `trans_date` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_customer_credit_payment_items` (
  `credit_item_id` int NOT NULL AUTO_INCREMENT,
  `credit_pay_id` int NOT NULL DEFAULT '0',
  `cust_id` int NOT NULL DEFAULT '0',
  `sale_id` int NOT NULL DEFAULT '0',
  `ord_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`credit_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_customer_credit_payment_modes` (
  `credit_mode_id` int NOT NULL AUTO_INCREMENT,
  `credit_pay_id` int NOT NULL DEFAULT '0',
  `pay_mode_id` int NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reference_no` varchar(120) NOT NULL DEFAULT '',
  PRIMARY KEY (`credit_mode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_customer_credit_payments` (
  `credit_pay_id` int NOT NULL AUTO_INCREMENT,
  `cust_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_mode_id` int NOT NULL DEFAULT '0',
  `reference_no` varchar(120) NOT NULL DEFAULT '',
  `note` text,
  `shift_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`credit_pay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_customers` (
  `cust_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `alias` text NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `email` text NOT NULL,
  `location` text NOT NULL,
  `bal` decimal(10,2) NOT NULL,
  `del_status` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`cust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

INSERT INTO kp_customers VALUES("1","FATUMA","","+254710432536","","","UKUNDA","300.00","2");
INSERT INTO kp_customers VALUES("2","GOOD LUCK","","+254721173699","","","UKUNDA","4600.00","2");
INSERT INTO kp_customers VALUES("3","SARA","","+254113149956","","","UKUNDA","3850.00","2");
INSERT INTO kp_customers VALUES("4","BEKA","","+254742401574","","","BIGA","1800.00","2");





CREATE TABLE `kp_day_shift` (
  `shift` date NOT NULL,
  `opening` datetime DEFAULT NULL,
  `closing` datetime NOT NULL,
  `open_by` int NOT NULL,
  `close_by` int NOT NULL,
  `open_ord` int NOT NULL,
  `close_ord` int NOT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_day_shift VALUES("2026-08-11","2026-08-11 18:18:04","2026-08-17 13:11:03","1","1","25006","25006","1");
INSERT INTO kp_day_shift VALUES("2026-08-17","2026-08-17 13:11:31","2026-08-24 10:31:39","1","1","25006","25007","1");
INSERT INTO kp_day_shift VALUES("2026-08-24","2026-08-24 10:32:11","2026-08-25 10:52:12","1","4","25007","20","1");
INSERT INTO kp_day_shift VALUES("2026-08-24","2026-08-24 11:33:57","2026-08-25 10:52:12","4","4","0","20","1");





CREATE TABLE `kp_del_items` (
  `sale_id` int NOT NULL,
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `counter` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_del_orders` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `appr_by` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `shift` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_displinary` (
  `discipline_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0',
  `reason` text,
  `day` date DEFAULT NULL,
  PRIMARY KEY (`discipline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_grn` (
  `grn_id` int NOT NULL,
  `po_id` int NOT NULL,
  `user_id` int NOT NULL,
  `sup_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid` decimal(10,2) NOT NULL,
  `bal` decimal(10,2) NOT NULL,
  `status` int DEFAULT '2',
  `pay_day` date NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`grn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_grn VALUES("1","2","1","4","229026.00","0.00","229026.00","1","2026-08-15","2026-08-15","2026-08-15 23:39:56");
INSERT INTO kp_grn VALUES("2","3","1","3","7964.00","0.00","7964.00","1","2026-08-15","2026-08-15","2026-08-15 23:49:05");
INSERT INTO kp_grn VALUES("3","4","1","3","48243.00","0.00","48243.00","1","2026-08-15","2026-08-15","2026-08-15 23:57:46");
INSERT INTO kp_grn VALUES("4","5","1","3","769734.64","0.00","769734.64","1","2026-08-15","2026-08-15","2026-08-16 01:32:30");
INSERT INTO kp_grn VALUES("5","6","1","5","36000.00","0.00","36000.00","1","2026-08-16","2026-08-16","2026-08-16 16:36:27");
INSERT INTO kp_grn VALUES("6","9","1","6","11334.96","0.00","11334.96","1","2026-08-17","2026-08-17","2026-08-17 15:03:04");
INSERT INTO kp_grn VALUES("7","11","1","6","682082.60","0.00","682082.60","1","2026-08-17","2026-08-17","2026-08-17 16:38:26");
INSERT INTO kp_grn VALUES("8","13","1","4","205344.10","0.00","205344.10","1","2026-08-17","2026-08-17","2026-08-17 17:07:50");
INSERT INTO kp_grn VALUES("9","15","1","7","116808.00","0.00","116808.00","1","2026-08-17","2026-08-17","2026-08-17 17:27:49");
INSERT INTO kp_grn VALUES("10","16","1","7","32880.00","0.00","32880.00","1","2026-08-17","2026-08-17","2026-08-17 17:33:10");
INSERT INTO kp_grn VALUES("11","17","1","5","36000.00","0.00","36000.00","1","2026-08-17","2026-08-17","2026-08-17 17:36:59");
INSERT INTO kp_grn VALUES("12","18","1","3","723792.16","0.00","723792.16","1","2026-08-19","2026-08-19","2026-08-19 14:04:41");
INSERT INTO kp_grn VALUES("13","19","1","4","86751.62","0.00","86751.62","1","2026-08-19","2026-08-19","2026-08-19 14:34:57");
INSERT INTO kp_grn VALUES("14","20","1","6","14199.96","0.00","14199.96","1","2026-08-19","2026-08-19","2026-08-19 14:48:17");
INSERT INTO kp_grn VALUES("15","21","1","4","72962.00","0.00","72962.00","1","2026-08-22","2026-08-22","2026-08-22 22:08:33");
INSERT INTO kp_grn VALUES("16","22","1","6","21270.60","0.00","21270.60","1","2026-08-22","2026-08-22","2026-08-22 22:11:39");
INSERT INTO kp_grn VALUES("17","23","1","7","65304.00","0.00","65304.00","1","2026-08-22","2026-08-22","2026-08-22 22:20:10");
INSERT INTO kp_grn VALUES("18","25","1","3","583144.04","0.00","583144.04","1","2026-08-23","2026-08-23","2026-08-24 00:28:25");
INSERT INTO kp_grn VALUES("19","26","1","5","36000.00","0.00","36000.00","1","2026-08-23","2026-08-23","2026-08-24 00:30:31");
INSERT INTO kp_grn VALUES("20","27","1","7","42662.00","0.00","42662.00","1","2026-08-23","2026-08-23","2026-08-24 00:33:56");
INSERT INTO kp_grn VALUES("21","28","1","3","103627.76","0.00","103627.76","1","2026-08-23","2026-08-23","2026-08-24 00:38:21");





CREATE TABLE `kp_grn_hist` (
  `grn_id` int NOT NULL,
  `user_id` int NOT NULL,
  `bal` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `day` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_grn_id` (
  `grn_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_grn_id VALUES("21");





CREATE TABLE `kp_grn_items` (
  `grn_id` int NOT NULL,
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `total` int NOT NULL,
  `user_id` int NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `s2s` int NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_grn_items VALUES("1","296","2","10100","20200","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","244","2","2825","5650","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","243","2","2123","4246","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","295","2","954","1908","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","29","3","2485","7455","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","207","2","3834","7668","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","217","2","3325","6650","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","163","1","8458","8458","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","57","1","5232","5232","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","213","2","2377","4754","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","108","36","42","1496","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","31","2","6913","13825","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","73","6","1064","6384","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","60","6","1064","6384","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","210","4","2605","10420","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","89","36","1279","46044","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","111","36","659","23724","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","149","12","761","9132","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","148","12","761","9132","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","151","12","929","11148","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","297","12","929","11148","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","154","1","1848","1848","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("1","117","3","2040","6120","1","2026-08-15","2026-08-15 23:39:56","1");
INSERT INTO kp_grn_items VALUES("2","8","50","159","7964","1","2026-08-15","2026-08-15 23:49:05","1");
INSERT INTO kp_grn_items VALUES("3","126","225","169","38025","1","2026-08-15","2026-08-15 23:57:46","1");
INSERT INTO kp_grn_items VALUES("3","121","50","204","10218","1","2026-08-15","2026-08-15 23:57:46","1");
INSERT INTO kp_grn_items VALUES("4","7","250","223","55750","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","136","50","158","7900","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","9","75","213","15975","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","132","50","159","7950","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","127","50","159","7950","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","126","425","169","66092","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","119","100","169","15551","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","71","525","169","81643","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","123","300","187","56100","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","86","50","187","9350","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","179","12","896","10752","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","293","20","256","5120","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","99","24","576","13824","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","173","2","6320","12640","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","241","11","1240","13640","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","40","12","1400","16800","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","120","750","196","134940","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","121","550","204","103428","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","124","150","222","30654","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","16","48","217","10402","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","15","48","242","11610","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","14","96","243","23288","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","10","24","217","5201","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","19","48","176","8446","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","141","96","176","16892","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","142","24","240","5767","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","6","24","193","4623","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","70","72","176","12669","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("4","12","24","199","4778","1","2026-08-15","2026-08-16 01:32:30","1");
INSERT INTO kp_grn_items VALUES("5","144","720","50","36000","1","2026-08-16","2026-08-16 16:36:27","1");
INSERT INTO kp_grn_items VALUES("6","22","264","32","8580","1","2026-08-17","2026-08-17 15:03:04","1");
INSERT INTO kp_grn_items VALUES("6","299","12","96","1155","1","2026-08-17","2026-08-17 15:03:04","1");
INSERT INTO kp_grn_items VALUES("6","76","12","133","1600","1","2026-08-17","2026-08-17 15:03:04","1");
INSERT INTO kp_grn_items VALUES("7","85","24","672","16128","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","7","125","223","27895","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","142","48","240","11534","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","115","24","175","4200","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","8","50","159","7964","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","141","120","176","21115","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","9","25","213","5331","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","132","50","159","7964","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","70","48","176","8446","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","127","50","159","7964","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","139","24","176","4223","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","126","400","169","67600","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","121","250","204","51090","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","119","100","169","16908","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","124","125","222","27760","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","71","275","169","46475","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","123","175","187","32669","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","86","50","187","9334","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","120","750","196","146640","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","300","12","960","11520","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","2","12","3911","46933","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","188","12","853","10240","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","305","12","1799","21589","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","171","12","352","4224","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","87","12","672","8064","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","157","24","416","9984","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","173","6","6320","37920","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("7","242","18","576","10368","1","2026-08-17","2026-08-17 16:38:26","1");
INSERT INTO kp_grn_items VALUES("8","239","3","2376","7128","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","225","2","3450","6900","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","296","3","10100","30300","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","82","3","3597","10791","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","32","2","4991","9982","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","59","24","195","4680","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","89","12","1279","15348","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","111","12","659","7912","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","216","2","1328","2656","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","27","6","3228","19369","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","95","6","2507","15042","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","33","4","1980","7920","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","215","2","1650","3300","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","72","6","930","5580","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","96","1","4609","4609","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","74","6","1579","9474","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","201","6","1243","7458","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","114","20","350","7000","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("8","162","3","9965","29895","1","2026-08-17","2026-08-17 17:07:50","1");
INSERT INTO kp_grn_items VALUES("9","292","48","253","12144","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","69","72","245","17640","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","56","48","285","13680","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","55","48","230","11040","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","128","24","206","4944","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","133","48","206","9888","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","130","48","245","11760","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("9","25","144","248","35712","1","2026-08-17","2026-08-17 17:27:49","1");
INSERT INTO kp_grn_items VALUES("10","128","48","206","9888","1","2026-08-17","2026-08-17 17:33:10","1");
INSERT INTO kp_grn_items VALUES("10","69","24","245","5880","1","2026-08-17","2026-08-17 17:33:10","1");
INSERT INTO kp_grn_items VALUES("10","292","24","253","6072","1","2026-08-17","2026-08-17 17:33:10","1");
INSERT INTO kp_grn_items VALUES("10","55","48","230","11040","1","2026-08-17","2026-08-17 17:33:10","1");
INSERT INTO kp_grn_items VALUES("11","144","720","50","36000","1","2026-08-17","2026-08-17 17:36:59","1");
INSERT INTO kp_grn_items VALUES("12","5","24","193","4623","1","2026-08-19","2026-08-19 14:04:41","1");
INSERT INTO kp_grn_items VALUES("12","6","24","193","4623","1","2026-08-19","2026-08-19 14:04:41","1");
INSERT INTO kp_grn_items VALUES("12","85","12","672","8064","1","2026-08-19","2026-08-19 14:04:41","1");
INSERT INTO kp_grn_items VALUES("12","7","300","223","66948","1","2026-08-19","2026-08-19 14:04:41","1");
INSERT INTO kp_grn_items VALUES("12","142","48","240","11534","1","2026-08-19","2026-08-19 14:04:41","1");
INSERT INTO kp_grn_items VALUES("12","136","25","158","3958","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","8","50","159","7964","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","141","144","176","25338","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","98","24","760","18240","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","132","50","159","7964","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","19","24","176","4223","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","70","96","176","16892","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","126","575","169","97175","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","121","350","204","71526","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","10","24","217","5201","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","119","75","169","12681","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","124","175","222","38864","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","71","425","169","71825","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","123","275","187","51337","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","14","72","243","17466","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","15","48","242","11610","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","86","50","187","9334","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","120","750","196","146640","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","16","24","217","5201","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("12","18","2","2280","4560","1","2026-08-19","2026-08-19 14:04:42","1");
INSERT INTO kp_grn_items VALUES("13","320","3","739","2216","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","196","6","1825","10951","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","32","3","4991","14972","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","29","3","2484","7452","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","36","6","2780","16677","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","215","2","1417","2834","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("13","111","48","659","31649","1","2026-08-19","2026-08-19 14:34:57","1");
INSERT INTO kp_grn_items VALUES("14","22","240","32","7800","1","2026-08-19","2026-08-19 14:48:17","1");
INSERT INTO kp_grn_items VALUES("14","104","24","200","4800","1","2026-08-19","2026-08-19 14:48:17","1");
INSERT INTO kp_grn_items VALUES("14","76","12","133","1600","1","2026-08-19","2026-08-19 14:48:17","1");
INSERT INTO kp_grn_items VALUES("15","89","24","640","15348","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","111","24","659","15816","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","34","2","1750","3500","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","303","12","448","5376","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","156","6","2041","12246","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","233","3","4930","14790","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("15","161","2","2943","5886","1","2026-08-22","2026-08-22 22:08:33","1");
INSERT INTO kp_grn_items VALUES("16","23","180","79","14251","1","2026-08-22","2026-08-22 22:11:39","1");
INSERT INTO kp_grn_items VALUES("16","22","216","32","7020","1","2026-08-22","2026-08-22 22:11:39","1");
INSERT INTO kp_grn_items VALUES("17","56","48","285","13680","1","2026-08-22","2026-08-22 22:20:10","1");
INSERT INTO kp_grn_items VALUES("17","55","48","230","11040","1","2026-08-22","2026-08-22 22:20:10","1");
INSERT INTO kp_grn_items VALUES("17","129","24","206","4944","1","2026-08-22","2026-08-22 22:20:10","1");
INSERT INTO kp_grn_items VALUES("17","130","24","245","5880","1","2026-08-22","2026-08-22 22:20:10","1");
INSERT INTO kp_grn_items VALUES("17","25","120","248","29760","1","2026-08-22","2026-08-22 22:20:10","1");
INSERT INTO kp_grn_items VALUES("18","85","12","672","8064","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","7","225","223","50211","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","136","25","158","3958","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","8","50","159","7964","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","141","48","176","8446","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","98","24","760","18240","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","132","75","159","11946","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","70","48","176","8446","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","127","50","159","7964","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","126","400","169","67600","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","121","300","204","61308","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","119","25","169","4227","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","124","150","222","33312","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","71","325","169","54925","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","123","125","187","23335","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","14","48","243","11644","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","86","75","187","14001","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","120","750","196","146640","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","16","24","217","5201","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","37","48","576","27648","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("18","87","12","672","8064","1","2026-08-23","2026-08-24 00:28:25","1");
INSERT INTO kp_grn_items VALUES("19","144","720","50","36000","1","2026-08-23","2026-08-24 00:30:31","1");
INSERT INTO kp_grn_items VALUES("20","105","24","230","5520","1","2026-08-23","2026-08-24 00:33:56","1");
INSERT INTO kp_grn_items VALUES("20","69","72","245","17640","1","2026-08-23","2026-08-24 00:33:56","1");
INSERT INTO kp_grn_items VALUES("20","292","38","253","9614","1","2026-08-23","2026-08-24 00:33:56","1");
INSERT INTO kp_grn_items VALUES("20","128","48","206","9888","1","2026-08-23","2026-08-24 00:33:56","1");
INSERT INTO kp_grn_items VALUES("21","119","100","169","16908","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","136","25","158","3958","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","9","50","213","10662","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","127","50","159","7964","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","123","175","187","32669","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","14","72","243","17466","1","2026-08-23","2026-08-24 00:38:21","1");
INSERT INTO kp_grn_items VALUES("21","86","75","187","14001","1","2026-08-23","2026-08-24 00:38:21","1");





CREATE TABLE `kp_grn_status` (
  `grn_id` int NOT NULL,
  `user_id` int NOT NULL,
  `po_id` int NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_hhr_set` (
  `hhr_id` int NOT NULL AUTO_INCREMENT,
  `day` varchar(22) NOT NULL,
  `start_time` varchar(22) NOT NULL,
  `end_time` varchar(22) NOT NULL,
  `set_by` int NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hhr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_item_attribute_mapping` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `attr_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mapping` (`item_id`,`attr_id`),
  KEY `attr_id` (`attr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_item_attribute_mapping VALUES("1","22","1");
INSERT INTO kp_item_attribute_mapping VALUES("4","22","2");
INSERT INTO kp_item_attribute_mapping VALUES("13","22","3");
INSERT INTO kp_item_attribute_mapping VALUES("12","22","4");
INSERT INTO kp_item_attribute_mapping VALUES("3","22","5");
INSERT INTO kp_item_attribute_mapping VALUES("6","22","6");
INSERT INTO kp_item_attribute_mapping VALUES("9","22","7");
INSERT INTO kp_item_attribute_mapping VALUES("5","22","8");
INSERT INTO kp_item_attribute_mapping VALUES("7","25","9");
INSERT INTO kp_item_attribute_mapping VALUES("2","25","10");
INSERT INTO kp_item_attribute_mapping VALUES("8","25","11");
INSERT INTO kp_item_attribute_mapping VALUES("10","25","12");
INSERT INTO kp_item_attribute_mapping VALUES("11","25","13");
INSERT INTO kp_item_attribute_mapping VALUES("14","25","14");





CREATE TABLE `kp_item_hist` (
  `item_id` int NOT NULL,
  `transaction` varchar(55) COLLATE latin1_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_item_hist VALUES("88","ITEM DISCONTINUED","1","2026-07-28","2026-07-28 23:16:09");
INSERT INTO kp_item_hist VALUES("116","ITEM CREATED","1","2026-07-28","2026-07-28 23:41:13");
INSERT INTO kp_item_hist VALUES("4","ITEM CATEGORY CHANGED","1","2026-07-29","2026-07-29 21:37:57");
INSERT INTO kp_item_hist VALUES("13","ITEM CATEGORY CHANGED","1","2026-07-29","2026-07-29 21:38:15");
INSERT INTO kp_item_hist VALUES("4","ITEM CATEGORY CHANGED","1","2026-07-29","2026-07-29 21:38:15");
INSERT INTO kp_item_hist VALUES("117","ITEM CREATED","1","2026-08-05","2026-08-05 21:14:18");
INSERT INTO kp_item_hist VALUES("118","ITEM CREATED","1","2026-08-07","2026-08-07 20:42:22");
INSERT INTO kp_item_hist VALUES("118","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 16:58:26");
INSERT INTO kp_item_hist VALUES("49","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:12:16");
INSERT INTO kp_item_hist VALUES("116","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:13:59");
INSERT INTO kp_item_hist VALUES("79","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:48");
INSERT INTO kp_item_hist VALUES("80","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("81","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("90","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("50","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("21","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("24","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:49");
INSERT INTO kp_item_hist VALUES("68","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:50");
INSERT INTO kp_item_hist VALUES("102","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:50");
INSERT INTO kp_item_hist VALUES("38","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:50");
INSERT INTO kp_item_hist VALUES("44","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:50");
INSERT INTO kp_item_hist VALUES("46","ITEM DISCONTINUED","1","2026-08-08","2026-08-08 17:45:50");
INSERT INTO kp_item_hist VALUES("119","ITEM CREATED","1","2026-08-08","2026-08-08 17:50:56");
INSERT INTO kp_item_hist VALUES("120","ITEM CREATED","1","2026-08-08","2026-08-08 17:54:48");
INSERT INTO kp_item_hist VALUES("121","ITEM CREATED","1","2026-08-08","2026-08-08 17:57:18");
INSERT INTO kp_item_hist VALUES("122","ITEM CREATED","1","2026-08-08","2026-08-08 18:02:14");
INSERT INTO kp_item_hist VALUES("123","ITEM CREATED","1","2026-08-08","2026-08-08 18:04:44");
INSERT INTO kp_item_hist VALUES("124","ITEM CREATED","1","2026-08-08","2026-08-08 18:08:01");
INSERT INTO kp_item_hist VALUES("125","ITEM CREATED","1","2026-08-08","2026-08-08 18:17:04");
INSERT INTO kp_item_hist VALUES("126","ITEM CREATED","1","2026-08-08","2026-08-08 18:55:36");
INSERT INTO kp_item_hist VALUES("127","ITEM CREATED","1","2026-08-08","2026-08-08 18:58:36");
INSERT INTO kp_item_hist VALUES("128","ITEM CREATED","1","2026-08-08","2026-08-08 19:10:24");
INSERT INTO kp_item_hist VALUES("129","ITEM CREATED","1","2026-08-08","2026-08-08 19:13:17");
INSERT INTO kp_item_hist VALUES("130","ITEM CREATED","1","2026-08-08","2026-08-08 19:16:50");
INSERT INTO kp_item_hist VALUES("131","ITEM CREATED","1","2026-08-08","2026-08-08 19:19:11");
INSERT INTO kp_item_hist VALUES("132","ITEM CREATED","1","2026-08-08","2026-08-08 19:23:39");
INSERT INTO kp_item_hist VALUES("133","ITEM CREATED","1","2026-08-08","2026-08-08 19:25:19");
INSERT INTO kp_item_hist VALUES("134","ITEM CREATED","1","2026-08-08","2026-08-08 19:28:13");
INSERT INTO kp_item_hist VALUES("135","ITEM CREATED","1","2026-08-08","2026-08-08 19:29:59");
INSERT INTO kp_item_hist VALUES("136","ITEM CREATED","1","2026-08-08","2026-08-08 19:31:47");
INSERT INTO kp_item_hist VALUES("137","ITEM CREATED","1","2026-08-08","2026-08-08 19:37:17");
INSERT INTO kp_item_hist VALUES("138","ITEM CREATED","1","2026-08-08","2026-08-08 19:40:17");
INSERT INTO kp_item_hist VALUES("139","ITEM CREATED","1","2026-08-08","2026-08-08 19:42:24");
INSERT INTO kp_item_hist VALUES("140","ITEM CREATED","1","2026-08-08","2026-08-08 19:44:39");
INSERT INTO kp_item_hist VALUES("141","ITEM CREATED","1","2026-08-08","2026-08-08 19:45:50");
INSERT INTO kp_item_hist VALUES("142","ITEM CREATED","1","2026-08-08","2026-08-08 19:46:42");
INSERT INTO kp_item_hist VALUES("143","ITEM CREATED","1","2026-08-08","2026-08-08 19:48:37");
INSERT INTO kp_item_hist VALUES("144","ITEM CREATED","1","2026-08-08","2026-08-08 19:49:57");
INSERT INTO kp_item_hist VALUES("145","ITEM CREATED","1","2026-08-08","2026-08-08 19:54:54");
INSERT INTO kp_item_hist VALUES("146","ITEM CREATED","1","2026-08-08","2026-08-08 19:58:24");
INSERT INTO kp_item_hist VALUES("147","ITEM CREATED","1","2026-08-08","2026-08-08 20:08:14");
INSERT INTO kp_item_hist VALUES("148","ITEM CREATED","1","2026-08-08","2026-08-08 20:11:35");
INSERT INTO kp_item_hist VALUES("149","ITEM CREATED","1","2026-08-08","2026-08-08 20:12:25");
INSERT INTO kp_item_hist VALUES("150","ITEM CREATED","1","2026-08-08","2026-08-08 20:15:05");
INSERT INTO kp_item_hist VALUES("151","ITEM CREATED","1","2026-08-08","2026-08-08 20:16:06");
INSERT INTO kp_item_hist VALUES("152","ITEM CREATED","1","2026-08-08","2026-08-08 20:18:01");
INSERT INTO kp_item_hist VALUES("153","ITEM CREATED","1","2026-08-08","2026-08-08 20:20:28");
INSERT INTO kp_item_hist VALUES("154","ITEM CREATED","1","2026-08-08","2026-08-08 20:21:41");
INSERT INTO kp_item_hist VALUES("155","ITEM CREATED","1","2026-08-08","2026-08-08 20:22:37");
INSERT INTO kp_item_hist VALUES("156","ITEM CREATED","1","2026-08-08","2026-08-08 20:23:43");
INSERT INTO kp_item_hist VALUES("157","ITEM CREATED","1","2026-08-08","2026-08-08 20:29:00");
INSERT INTO kp_item_hist VALUES("158","ITEM CREATED","1","2026-08-08","2026-08-08 20:32:11");
INSERT INTO kp_item_hist VALUES("159","ITEM CREATED","1","2026-08-08","2026-08-08 20:37:07");
INSERT INTO kp_item_hist VALUES("160","ITEM CREATED","1","2026-08-08","2026-08-08 20:38:11");
INSERT INTO kp_item_hist VALUES("161","ITEM CREATED","1","2026-08-08","2026-08-08 20:39:38");
INSERT INTO kp_item_hist VALUES("162","ITEM CREATED","1","2026-08-08","2026-08-08 20:40:45");
INSERT INTO kp_item_hist VALUES("163","ITEM CREATED","1","2026-08-08","2026-08-08 20:45:21");
INSERT INTO kp_item_hist VALUES("164","ITEM CREATED","1","2026-08-08","2026-08-08 20:50:24");
INSERT INTO kp_item_hist VALUES("165","ITEM CREATED","1","2026-08-08","2026-08-08 21:33:28");
INSERT INTO kp_item_hist VALUES("166","ITEM CREATED","1","2026-08-08","2026-08-08 21:36:43");
INSERT INTO kp_item_hist VALUES("167","ITEM CREATED","1","2026-08-08","2026-08-08 21:56:25");
INSERT INTO kp_item_hist VALUES("168","ITEM CREATED","1","2026-08-08","2026-08-08 21:58:31");
INSERT INTO kp_item_hist VALUES("169","ITEM CREATED","1","2026-08-08","2026-08-08 22:01:13");
INSERT INTO kp_item_hist VALUES("170","ITEM CREATED","1","2026-08-08","2026-08-08 22:02:53");
INSERT INTO kp_item_hist VALUES("171","ITEM CREATED","1","2026-08-08","2026-08-08 22:04:14");
INSERT INTO kp_item_hist VALUES("172","ITEM CREATED","1","2026-08-08","2026-08-08 22:07:10");
INSERT INTO kp_item_hist VALUES("173","ITEM CREATED","1","2026-08-08","2026-08-08 22:08:03");
INSERT INTO kp_item_hist VALUES("174","ITEM CREATED","1","2026-08-08","2026-08-08 22:09:02");
INSERT INTO kp_item_hist VALUES("175","ITEM CREATED","1","2026-08-08","2026-08-08 22:09:51");
INSERT INTO kp_item_hist VALUES("176","ITEM CREATED","1","2026-08-08","2026-08-08 22:11:28");
INSERT INTO kp_item_hist VALUES("177","ITEM CREATED","1","2026-08-08","2026-08-08 22:12:23");
INSERT INTO kp_item_hist VALUES("178","ITEM CREATED","1","2026-08-08","2026-08-08 22:13:14");
INSERT INTO kp_item_hist VALUES("179","ITEM CREATED","1","2026-08-08","2026-08-08 22:14:27");
INSERT INTO kp_item_hist VALUES("180","ITEM CREATED","1","2026-08-08","2026-08-08 22:15:10");
INSERT INTO kp_item_hist VALUES("181","ITEM CREATED","1","2026-08-08","2026-08-08 22:16:03");
INSERT INTO kp_item_hist VALUES("182","ITEM CREATED","1","2026-08-08","2026-08-08 22:17:04");
INSERT INTO kp_item_hist VALUES("183","ITEM CREATED","1","2026-08-08","2026-08-08 22:17:57");
INSERT INTO kp_item_hist VALUES("184","ITEM CREATED","1","2026-08-08","2026-08-08 22:18:45");
INSERT INTO kp_item_hist VALUES("185","ITEM CREATED","1","2026-08-08","2026-08-08 22:29:43");
INSERT INTO kp_item_hist VALUES("186","ITEM CREATED","1","2026-08-08","2026-08-08 22:32:01");
INSERT INTO kp_item_hist VALUES("187","ITEM CREATED","1","2026-08-08","2026-08-08 22:32:54");
INSERT INTO kp_item_hist VALUES("188","ITEM CREATED","1","2026-08-08","2026-08-08 22:35:57");
INSERT INTO kp_item_hist VALUES("189","ITEM CREATED","1","2026-08-08","2026-08-08 22:40:14");
INSERT INTO kp_item_hist VALUES("190","ITEM CREATED","1","2026-08-08","2026-08-08 22:41:26");
INSERT INTO kp_item_hist VALUES("191","ITEM CREATED","1","2026-08-08","2026-08-08 22:42:32");
INSERT INTO kp_item_hist VALUES("192","ITEM CREATED","1","2026-08-08","2026-08-08 22:43:30");
INSERT INTO kp_item_hist VALUES("193","ITEM CREATED","1","2026-08-08","2026-08-08 22:44:29");
INSERT INTO kp_item_hist VALUES("194","ITEM CREATED","1","2026-08-08","2026-08-08 22:45:29");
INSERT INTO kp_item_hist VALUES("195","ITEM CREATED","1","2026-08-08","2026-08-08 22:47:08");
INSERT INTO kp_item_hist VALUES("196","ITEM CREATED","1","2026-08-08","2026-08-08 22:48:10");
INSERT INTO kp_item_hist VALUES("197","ITEM CREATED","1","2026-08-08","2026-08-08 22:49:01");
INSERT INTO kp_item_hist VALUES("198","ITEM CREATED","1","2026-08-08","2026-08-08 22:49:54");
INSERT INTO kp_item_hist VALUES("199","ITEM CREATED","1","2026-08-09","2026-08-09 22:52:18");
INSERT INTO kp_item_hist VALUES("200","ITEM CREATED","1","2026-08-09","2026-08-09 22:53:36");
INSERT INTO kp_item_hist VALUES("201","ITEM CREATED","1","2026-08-09","2026-08-09 22:55:24");
INSERT INTO kp_item_hist VALUES("202","ITEM CREATED","1","2026-08-09","2026-08-09 22:59:10");
INSERT INTO kp_item_hist VALUES("203","ITEM CREATED","1","2026-08-09","2026-08-09 23:04:15");
INSERT INTO kp_item_hist VALUES("204","ITEM CREATED","1","2026-08-09","2026-08-09 23:05:55");
INSERT INTO kp_item_hist VALUES("205","ITEM CREATED","1","2026-08-09","2026-08-09 23:07:57");
INSERT INTO kp_item_hist VALUES("206","ITEM CREATED","1","2026-08-09","2026-08-09 23:09:00");
INSERT INTO kp_item_hist VALUES("207","ITEM CREATED","1","2026-08-09","2026-08-09 23:10:09");
INSERT INTO kp_item_hist VALUES("208","ITEM CREATED","1","2026-08-09","2026-08-09 23:11:19");
INSERT INTO kp_item_hist VALUES("209","ITEM CREATED","1","2026-08-09","2026-08-09 23:12:17");
INSERT INTO kp_item_hist VALUES("210","ITEM CREATED","1","2026-08-09","2026-08-09 23:13:01");
INSERT INTO kp_item_hist VALUES("211","ITEM CREATED","1","2026-08-09","2026-08-09 23:14:49");
INSERT INTO kp_item_hist VALUES("212","ITEM CREATED","1","2026-08-09","2026-08-09 23:15:34");
INSERT INTO kp_item_hist VALUES("213","ITEM CREATED","1","2026-08-09","2026-08-09 23:16:19");
INSERT INTO kp_item_hist VALUES("214","ITEM CREATED","1","2026-08-09","2026-08-09 23:17:04");
INSERT INTO kp_item_hist VALUES("215","ITEM CREATED","1","2026-08-09","2026-08-09 23:17:58");
INSERT INTO kp_item_hist VALUES("216","ITEM CREATED","1","2026-08-09","2026-08-09 23:21:44");
INSERT INTO kp_item_hist VALUES("217","ITEM CREATED","1","2026-08-09","2026-08-09 23:22:42");
INSERT INTO kp_item_hist VALUES("218","ITEM CREATED","1","2026-08-09","2026-08-09 23:24:45");
INSERT INTO kp_item_hist VALUES("219","ITEM CREATED","1","2026-08-09","2026-08-09 23:28:36");
INSERT INTO kp_item_hist VALUES("220","ITEM CREATED","1","2026-08-09","2026-08-09 23:29:24");
INSERT INTO kp_item_hist VALUES("221","ITEM CREATED","1","2026-08-09","2026-08-09 23:30:23");
INSERT INTO kp_item_hist VALUES("222","ITEM CREATED","1","2026-08-09","2026-08-09 23:31:03");
INSERT INTO kp_item_hist VALUES("223","ITEM CREATED","1","2026-08-09","2026-08-09 23:31:52");
INSERT INTO kp_item_hist VALUES("224","ITEM CREATED","1","2026-08-09","2026-08-09 23:32:50");
INSERT INTO kp_item_hist VALUES("225","ITEM CREATED","1","2026-08-09","2026-08-09 23:35:05");
INSERT INTO kp_item_hist VALUES("226","ITEM CREATED","1","2026-08-09","2026-08-09 23:36:16");
INSERT INTO kp_item_hist VALUES("227","ITEM CREATED","1","2026-08-09","2026-08-09 23:36:55");
INSERT INTO kp_item_hist VALUES("228","ITEM CREATED","1","2026-08-09","2026-08-09 23:38:06");
INSERT INTO kp_item_hist VALUES("229","ITEM CREATED","1","2026-08-09","2026-08-09 23:39:25");
INSERT INTO kp_item_hist VALUES("230","ITEM CREATED","1","2026-08-09","2026-08-09 23:41:44");
INSERT INTO kp_item_hist VALUES("231","ITEM CREATED","1","2026-08-09","2026-08-09 23:42:40");
INSERT INTO kp_item_hist VALUES("232","ITEM CREATED","1","2026-08-09","2026-08-09 23:43:18");
INSERT INTO kp_item_hist VALUES("233","ITEM CREATED","1","2026-08-09","2026-08-09 23:46:26");
INSERT INTO kp_item_hist VALUES("234","ITEM CREATED","1","2026-08-09","2026-08-09 23:47:43");
INSERT INTO kp_item_hist VALUES("235","ITEM CREATED","1","2026-08-09","2026-08-09 23:48:44");
INSERT INTO kp_item_hist VALUES("236","ITEM CREATED","1","2026-08-09","2026-08-09 23:49:18");
INSERT INTO kp_item_hist VALUES("237","ITEM CREATED","1","2026-08-09","2026-08-09 23:50:00");
INSERT INTO kp_item_hist VALUES("238","ITEM CREATED","1","2026-08-09","2026-08-09 23:51:33");
INSERT INTO kp_item_hist VALUES("239","ITEM CREATED","1","2026-08-09","2026-08-09 23:54:12");
INSERT INTO kp_item_hist VALUES("240","ITEM CREATED","1","2026-08-09","2026-08-09 23:58:03");
INSERT INTO kp_item_hist VALUES("241","ITEM CREATED","1","2026-08-10","2026-08-10 00:05:58");
INSERT INTO kp_item_hist VALUES("242","ITEM CREATED","1","2026-08-10","2026-08-10 00:06:52");
INSERT INTO kp_item_hist VALUES("243","ITEM CREATED","1","2026-08-10","2026-08-10 00:07:46");
INSERT INTO kp_item_hist VALUES("244","ITEM CREATED","1","2026-08-10","2026-08-10 00:08:36");
INSERT INTO kp_item_hist VALUES("245","ITEM CREATED","1","2026-08-10","2026-08-10 00:09:16");
INSERT INTO kp_item_hist VALUES("246","ITEM CREATED","1","2026-08-10","2026-08-10 00:16:16");
INSERT INTO kp_item_hist VALUES("247","ITEM CREATED","1","2026-08-10","2026-08-10 00:51:23");
INSERT INTO kp_item_hist VALUES("248","ITEM CREATED","1","2026-08-10","2026-08-10 00:53:09");
INSERT INTO kp_item_hist VALUES("249","ITEM CREATED","1","2026-08-10","2026-08-10 00:54:16");
INSERT INTO kp_item_hist VALUES("250","ITEM CREATED","1","2026-08-10","2026-08-10 00:55:22");
INSERT INTO kp_item_hist VALUES("251","ITEM CREATED","1","2026-08-10","2026-08-10 00:56:25");
INSERT INTO kp_item_hist VALUES("252","ITEM CREATED","1","2026-08-10","2026-08-10 00:58:00");
INSERT INTO kp_item_hist VALUES("253","ITEM CREATED","1","2026-08-10","2026-08-10 00:58:57");
INSERT INTO kp_item_hist VALUES("254","ITEM CREATED","1","2026-08-10","2026-08-10 00:59:51");
INSERT INTO kp_item_hist VALUES("255","ITEM CREATED","1","2026-08-10","2026-08-10 01:00:50");
INSERT INTO kp_item_hist VALUES("256","ITEM CREATED","1","2026-08-10","2026-08-10 01:01:47");
INSERT INTO kp_item_hist VALUES("257","ITEM CREATED","1","2026-08-10","2026-08-10 01:02:59");
INSERT INTO kp_item_hist VALUES("258","ITEM CREATED","1","2026-08-10","2026-08-10 01:17:07");
INSERT INTO kp_item_hist VALUES("259","ITEM CREATED","1","2026-08-10","2026-08-10 01:18:46");
INSERT INTO kp_item_hist VALUES("260","ITEM CREATED","1","2026-08-10","2026-08-10 01:20:22");
INSERT INTO kp_item_hist VALUES("261","ITEM CREATED","1","2026-08-10","2026-08-10 01:20:57");
INSERT INTO kp_item_hist VALUES("262","ITEM CREATED","1","2026-08-10","2026-08-10 01:21:39");
INSERT INTO kp_item_hist VALUES("263","ITEM CREATED","1","2026-08-10","2026-08-10 01:25:19");
INSERT INTO kp_item_hist VALUES("264","ITEM CREATED","1","2026-08-10","2026-08-10 01:26:46");
INSERT INTO kp_item_hist VALUES("265","ITEM CREATED","1","2026-08-10","2026-08-10 01:27:59");
INSERT INTO kp_item_hist VALUES("266","ITEM CREATED","1","2026-08-10","2026-08-10 01:28:52");
INSERT INTO kp_item_hist VALUES("267","ITEM CREATED","1","2026-08-10","2026-08-10 01:29:40");
INSERT INTO kp_item_hist VALUES("268","ITEM CREATED","1","2026-08-10","2026-08-10 01:30:56");
INSERT INTO kp_item_hist VALUES("269","ITEM CREATED","1","2026-08-10","2026-08-10 01:36:11");
INSERT INTO kp_item_hist VALUES("270","ITEM CREATED","1","2026-08-10","2026-08-10 01:38:31");
INSERT INTO kp_item_hist VALUES("271","ITEM CREATED","1","2026-08-10","2026-08-10 01:40:06");
INSERT INTO kp_item_hist VALUES("272","ITEM CREATED","1","2026-08-10","2026-08-10 01:41:22");
INSERT INTO kp_item_hist VALUES("273","ITEM CREATED","1","2026-08-10","2026-08-10 01:42:17");
INSERT INTO kp_item_hist VALUES("274","ITEM CREATED","1","2026-08-10","2026-08-10 01:43:08");
INSERT INTO kp_item_hist VALUES("275","ITEM CREATED","1","2026-08-10","2026-08-10 01:44:10");
INSERT INTO kp_item_hist VALUES("276","ITEM CREATED","1","2026-08-10","2026-08-10 01:45:09");
INSERT INTO kp_item_hist VALUES("277","ITEM CREATED","1","2026-08-10","2026-08-10 01:45:53");
INSERT INTO kp_item_hist VALUES("278","ITEM CREATED","1","2026-08-10","2026-08-10 01:46:32");
INSERT INTO kp_item_hist VALUES("279","ITEM CREATED","1","2026-08-10","2026-08-10 01:47:21");
INSERT INTO kp_item_hist VALUES("280","ITEM CREATED","1","2026-08-10","2026-08-10 01:50:40");
INSERT INTO kp_item_hist VALUES("281","ITEM CREATED","1","2026-08-10","2026-08-10 01:51:32");
INSERT INTO kp_item_hist VALUES("282","ITEM CREATED","1","2026-08-10","2026-08-10 01:52:08");
INSERT INTO kp_item_hist VALUES("283","ITEM CREATED","1","2026-08-10","2026-08-10 01:52:47");
INSERT INTO kp_item_hist VALUES("284","ITEM CREATED","1","2026-08-10","2026-08-10 01:53:41");
INSERT INTO kp_item_hist VALUES("285","ITEM CREATED","1","2026-08-10","2026-08-10 01:54:56");
INSERT INTO kp_item_hist VALUES("286","ITEM CREATED","1","2026-08-10","2026-08-10 01:55:30");
INSERT INTO kp_item_hist VALUES("287","ITEM CREATED","1","2026-08-10","2026-08-10 01:56:21");
INSERT INTO kp_item_hist VALUES("288","ITEM CREATED","1","2026-08-10","2026-08-10 01:57:15");
INSERT INTO kp_item_hist VALUES("289","ITEM CREATED","1","2026-08-10","2026-08-10 01:59:53");
INSERT INTO kp_item_hist VALUES("290","ITEM CREATED","1","2026-08-10","2026-08-10 02:01:17");
INSERT INTO kp_item_hist VALUES("291","ITEM CREATED","1","2026-08-10","2026-08-10 02:01:52");
INSERT INTO kp_item_hist VALUES("292","ITEM CREATED","1","2026-08-11","2026-08-11 18:28:30");
INSERT INTO kp_item_hist VALUES("23","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:30:08");
INSERT INTO kp_item_hist VALUES("23","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:30:25");
INSERT INTO kp_item_hist VALUES("25","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:31:56");
INSERT INTO kp_item_hist VALUES("25","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:32:34");
INSERT INTO kp_item_hist VALUES("22","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:34:04");
INSERT INTO kp_item_hist VALUES("22","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:34:23");
INSERT INTO kp_item_hist VALUES("77","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:46:47");
INSERT INTO kp_item_hist VALUES("78","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:46:47");
INSERT INTO kp_item_hist VALUES("85","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:46:47");
INSERT INTO kp_item_hist VALUES("87","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:46:47");
INSERT INTO kp_item_hist VALUES("171","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:46:47");
INSERT INTO kp_item_hist VALUES("77","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:50:18");
INSERT INTO kp_item_hist VALUES("78","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:50:18");
INSERT INTO kp_item_hist VALUES("85","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:50:18");
INSERT INTO kp_item_hist VALUES("87","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:50:18");
INSERT INTO kp_item_hist VALUES("171","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:50:18");
INSERT INTO kp_item_hist VALUES("293","ITEM CREATED","1","2026-08-11","2026-08-11 18:53:10");
INSERT INTO kp_item_hist VALUES("293","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:54:29");
INSERT INTO kp_item_hist VALUES("293","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:54:39");
INSERT INTO kp_item_hist VALUES("1","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("2","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("3","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("27","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("28","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("29","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("30","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("33","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:18");
INSERT INTO kp_item_hist VALUES("34","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:19");
INSERT INTO kp_item_hist VALUES("36","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:19");
INSERT INTO kp_item_hist VALUES("40","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:19");
INSERT INTO kp_item_hist VALUES("41","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:19");
INSERT INTO kp_item_hist VALUES("98","ITEM CATEGORY CHANGED","1","2026-08-11","2026-08-11 18:58:19");
INSERT INTO kp_item_hist VALUES("294","ITEM CREATED","1","2026-08-11","2026-08-11 19:01:09");
INSERT INTO kp_item_hist VALUES("295","ITEM CREATED","1","2026-08-12","2026-08-12 20:48:52");
INSERT INTO kp_item_hist VALUES("296","ITEM CREATED","1","2026-08-15","2026-08-15 22:58:23");
INSERT INTO kp_item_hist VALUES("297","ITEM CREATED","1","2026-08-15","2026-08-15 23:32:13");
INSERT INTO kp_item_hist VALUES("298","ITEM CREATED","1","2026-08-15","2026-08-15 23:33:11");
INSERT INTO kp_item_hist VALUES("299","ITEM CREATED","1","2026-08-16","2026-08-16 16:42:40");
INSERT INTO kp_item_hist VALUES("300","ITEM CREATED","1","2026-08-17","2026-08-17 12:36:28");
INSERT INTO kp_item_hist VALUES("300","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:06:07");
INSERT INTO kp_item_hist VALUES("84","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:06:19");
INSERT INTO kp_item_hist VALUES("83","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:06:34");
INSERT INTO kp_item_hist VALUES("108","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:07:11");
INSERT INTO kp_item_hist VALUES("108","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:07:23");
INSERT INTO kp_item_hist VALUES("176","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:10:46");
INSERT INTO kp_item_hist VALUES("203","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:11:37");
INSERT INTO kp_item_hist VALUES("204","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:12:32");
INSERT INTO kp_item_hist VALUES("205","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:12:38");
INSERT INTO kp_item_hist VALUES("206","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:12:46");
INSERT INTO kp_item_hist VALUES("211","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:12:55");
INSERT INTO kp_item_hist VALUES("212","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:13:07");
INSERT INTO kp_item_hist VALUES("31","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:14:05");
INSERT INTO kp_item_hist VALUES("32","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:14:18");
INSERT INTO kp_item_hist VALUES("169","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:14:44");
INSERT INTO kp_item_hist VALUES("170","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:14:58");
INSERT INTO kp_item_hist VALUES("175","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:15:06");
INSERT INTO kp_item_hist VALUES("191","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:15:17");
INSERT INTO kp_item_hist VALUES("301","ITEM CREATED","1","2026-08-17","2026-08-17 14:21:28");
INSERT INTO kp_item_hist VALUES("5","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:38:24");
INSERT INTO kp_item_hist VALUES("6","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:38:31");
INSERT INTO kp_item_hist VALUES("4","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:38:42");
INSERT INTO kp_item_hist VALUES("11","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:38:49");
INSERT INTO kp_item_hist VALUES("10","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:38:58");
INSERT INTO kp_item_hist VALUES("12","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:11");
INSERT INTO kp_item_hist VALUES("13","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:18");
INSERT INTO kp_item_hist VALUES("14","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:40");
INSERT INTO kp_item_hist VALUES("15","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:46");
INSERT INTO kp_item_hist VALUES("16","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:53");
INSERT INTO kp_item_hist VALUES("19","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:39:59");
INSERT INTO kp_item_hist VALUES("56","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:40:06");
INSERT INTO kp_item_hist VALUES("70","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:40:17");
INSERT INTO kp_item_hist VALUES("115","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:40:25");
INSERT INTO kp_item_hist VALUES("4","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:40:31");
INSERT INTO kp_item_hist VALUES("199","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:43:11");
INSERT INTO kp_item_hist VALUES("200","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:43:19");
INSERT INTO kp_item_hist VALUES("55","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:48:44");
INSERT INTO kp_item_hist VALUES("57","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 14:52:21");
INSERT INTO kp_item_hist VALUES("39","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 15:18:35");
INSERT INTO kp_item_hist VALUES("39","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 15:18:51");
INSERT INTO kp_item_hist VALUES("302","ITEM CREATED","1","2026-08-17","2026-08-17 15:40:46");
INSERT INTO kp_item_hist VALUES("303","ITEM CREATED","1","2026-08-17","2026-08-17 15:42:18");
INSERT INTO kp_item_hist VALUES("304","ITEM CREATED","1","2026-08-17","2026-08-17 15:44:39");
INSERT INTO kp_item_hist VALUES("305","ITEM CREATED","1","2026-08-17","2026-08-17 15:53:50");
INSERT INTO kp_item_hist VALUES("306","ITEM CREATED","1","2026-08-17","2026-08-17 15:55:20");
INSERT INTO kp_item_hist VALUES("307","ITEM CREATED","1","2026-08-17","2026-08-17 15:57:26");
INSERT INTO kp_item_hist VALUES("308","ITEM CREATED","1","2026-08-17","2026-08-17 15:58:53");
INSERT INTO kp_item_hist VALUES("309","ITEM CREATED","1","2026-08-17","2026-08-17 15:59:59");
INSERT INTO kp_item_hist VALUES("310","ITEM CREATED","1","2026-08-17","2026-08-17 16:02:27");
INSERT INTO kp_item_hist VALUES("311","ITEM CREATED","1","2026-08-17","2026-08-17 16:11:59");
INSERT INTO kp_item_hist VALUES("312","ITEM CREATED","1","2026-08-17","2026-08-17 16:14:49");
INSERT INTO kp_item_hist VALUES("313","ITEM CREATED","1","2026-08-17","2026-08-17 16:15:50");
INSERT INTO kp_item_hist VALUES("314","ITEM CREATED","1","2026-08-17","2026-08-17 16:16:41");
INSERT INTO kp_item_hist VALUES("315","ITEM CREATED","1","2026-08-17","2026-08-17 16:18:20");
INSERT INTO kp_item_hist VALUES("316","ITEM CREATED","1","2026-08-17","2026-08-17 16:19:51");
INSERT INTO kp_item_hist VALUES("317","ITEM CREATED","1","2026-08-17","2026-08-17 16:22:47");
INSERT INTO kp_item_hist VALUES("318","ITEM CREATED","1","2026-08-17","2026-08-17 16:25:25");
INSERT INTO kp_item_hist VALUES("318","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:25:54");
INSERT INTO kp_item_hist VALUES("241","ITEM DISCONTINUED","1","2026-08-17","2026-08-17 16:40:56");
INSERT INTO kp_item_hist VALUES("89","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:48:28");
INSERT INTO kp_item_hist VALUES("319","ITEM CREATED","1","2026-08-17","2026-08-17 16:48:30");
INSERT INTO kp_item_hist VALUES("111","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:48:34");
INSERT INTO kp_item_hist VALUES("158","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:48:43");
INSERT INTO kp_item_hist VALUES("37","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:50:11");
INSERT INTO kp_item_hist VALUES("83","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:50:30");
INSERT INTO kp_item_hist VALUES("95","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:50:41");
INSERT INTO kp_item_hist VALUES("99","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:50:49");
INSERT INTO kp_item_hist VALUES("100","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:50:56");
INSERT INTO kp_item_hist VALUES("99","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:03");
INSERT INTO kp_item_hist VALUES("101","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:12");
INSERT INTO kp_item_hist VALUES("107","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:24");
INSERT INTO kp_item_hist VALUES("109","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:30");
INSERT INTO kp_item_hist VALUES("113","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:38");
INSERT INTO kp_item_hist VALUES("157","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:51:45");
INSERT INTO kp_item_hist VALUES("159","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:00");
INSERT INTO kp_item_hist VALUES("160","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:07");
INSERT INTO kp_item_hist VALUES("161","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:15");
INSERT INTO kp_item_hist VALUES("162","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:23");
INSERT INTO kp_item_hist VALUES("163","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:45");
INSERT INTO kp_item_hist VALUES("164","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:52:51");
INSERT INTO kp_item_hist VALUES("165","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:03");
INSERT INTO kp_item_hist VALUES("166","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:17");
INSERT INTO kp_item_hist VALUES("167","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:25");
INSERT INTO kp_item_hist VALUES("168","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:33");
INSERT INTO kp_item_hist VALUES("172","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:44");
INSERT INTO kp_item_hist VALUES("173","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:50");
INSERT INTO kp_item_hist VALUES("174","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:53:56");
INSERT INTO kp_item_hist VALUES("177","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:05");
INSERT INTO kp_item_hist VALUES("178","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:13");
INSERT INTO kp_item_hist VALUES("179","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:18");
INSERT INTO kp_item_hist VALUES("180","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:24");
INSERT INTO kp_item_hist VALUES("181","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:33");
INSERT INTO kp_item_hist VALUES("182","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:40");
INSERT INTO kp_item_hist VALUES("183","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:54:45");
INSERT INTO kp_item_hist VALUES("184","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:55:29");
INSERT INTO kp_item_hist VALUES("185","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:56:19");
INSERT INTO kp_item_hist VALUES("215","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:56:39");
INSERT INTO kp_item_hist VALUES("218","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:56:45");
INSERT INTO kp_item_hist VALUES("207","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:57:46");
INSERT INTO kp_item_hist VALUES("208","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:57:52");
INSERT INTO kp_item_hist VALUES("209","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:01");
INSERT INTO kp_item_hist VALUES("210","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:16");
INSERT INTO kp_item_hist VALUES("201","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:26");
INSERT INTO kp_item_hist VALUES("202","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:32");
INSERT INTO kp_item_hist VALUES("213","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:46");
INSERT INTO kp_item_hist VALUES("214","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 16:58:52");
INSERT INTO kp_item_hist VALUES("196","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:06:18");
INSERT INTO kp_item_hist VALUES("197","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:06:28");
INSERT INTO kp_item_hist VALUES("231","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:08:04");
INSERT INTO kp_item_hist VALUES("232","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:08:12");
INSERT INTO kp_item_hist VALUES("198","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:08:22");
INSERT INTO kp_item_hist VALUES("273","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:08:43");
INSERT INTO kp_item_hist VALUES("273","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:08:49");
INSERT INTO kp_item_hist VALUES("186","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:09:25");
INSERT INTO kp_item_hist VALUES("187","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:09:32");
INSERT INTO kp_item_hist VALUES("188","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:09:38");
INSERT INTO kp_item_hist VALUES("189","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:09:44");
INSERT INTO kp_item_hist VALUES("190","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:09:51");
INSERT INTO kp_item_hist VALUES("192","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:10:01");
INSERT INTO kp_item_hist VALUES("193","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:10:12");
INSERT INTO kp_item_hist VALUES("194","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:10:25");
INSERT INTO kp_item_hist VALUES("195","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:10:37");
INSERT INTO kp_item_hist VALUES("195","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:10:50");
INSERT INTO kp_item_hist VALUES("216","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:11:12");
INSERT INTO kp_item_hist VALUES("217","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:11:28");
INSERT INTO kp_item_hist VALUES("219","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:11:34");
INSERT INTO kp_item_hist VALUES("219","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:11:42");
INSERT INTO kp_item_hist VALUES("220","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:11:54");
INSERT INTO kp_item_hist VALUES("221","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:01");
INSERT INTO kp_item_hist VALUES("222","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:07");
INSERT INTO kp_item_hist VALUES("223","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:13");
INSERT INTO kp_item_hist VALUES("224","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:19");
INSERT INTO kp_item_hist VALUES("225","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:30");
INSERT INTO kp_item_hist VALUES("226","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:39");
INSERT INTO kp_item_hist VALUES("227","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:45");
INSERT INTO kp_item_hist VALUES("228","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:12:52");
INSERT INTO kp_item_hist VALUES("229","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:00");
INSERT INTO kp_item_hist VALUES("230","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:06");
INSERT INTO kp_item_hist VALUES("233","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:16");
INSERT INTO kp_item_hist VALUES("234","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:25");
INSERT INTO kp_item_hist VALUES("235","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:35");
INSERT INTO kp_item_hist VALUES("236","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:41");
INSERT INTO kp_item_hist VALUES("237","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:52");
INSERT INTO kp_item_hist VALUES("238","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:13:57");
INSERT INTO kp_item_hist VALUES("239","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:14:09");
INSERT INTO kp_item_hist VALUES("58","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:14:41");
INSERT INTO kp_item_hist VALUES("112","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:14:49");
INSERT INTO kp_item_hist VALUES("82","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:15:01");
INSERT INTO kp_item_hist VALUES("266","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:15:36");
INSERT INTO kp_item_hist VALUES("272","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:15:42");
INSERT INTO kp_item_hist VALUES("250","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:07");
INSERT INTO kp_item_hist VALUES("249","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:18");
INSERT INTO kp_item_hist VALUES("248","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:26");
INSERT INTO kp_item_hist VALUES("247","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:33");
INSERT INTO kp_item_hist VALUES("246","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:44");
INSERT INTO kp_item_hist VALUES("266","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:16:59");
INSERT INTO kp_item_hist VALUES("315","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:18:22");
INSERT INTO kp_item_hist VALUES("59","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:19:49");
INSERT INTO kp_item_hist VALUES("299","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:12");
INSERT INTO kp_item_hist VALUES("76","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:19");
INSERT INTO kp_item_hist VALUES("299","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:29");
INSERT INTO kp_item_hist VALUES("104","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:37");
INSERT INTO kp_item_hist VALUES("280","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:44");
INSERT INTO kp_item_hist VALUES("281","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:51");
INSERT INTO kp_item_hist VALUES("282","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:20:59");
INSERT INTO kp_item_hist VALUES("283","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:21:06");
INSERT INTO kp_item_hist VALUES("284","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:21:18");
INSERT INTO kp_item_hist VALUES("285","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:21:24");
INSERT INTO kp_item_hist VALUES("286","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:21:34");
INSERT INTO kp_item_hist VALUES("287","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:21:57");
INSERT INTO kp_item_hist VALUES("288","ITEM CATEGORY CHANGED","1","2026-08-17","2026-08-17 17:23:42");
INSERT INTO kp_item_hist VALUES("280","ITEM DISCONTINUED","1","2026-08-17","2026-08-17 17:24:01");
INSERT INTO kp_item_hist VALUES("320","ITEM CREATED","1","2026-08-19","2026-08-19 14:26:45");
INSERT INTO kp_item_hist VALUES("17","ITEM CATEGORY CHANGED","1","2026-08-24","2026-08-24 19:01:12");
INSERT INTO kp_item_hist VALUES("18","ITEM CATEGORY CHANGED","1","2026-08-24","2026-08-24 19:01:51");





CREATE TABLE `kp_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `bname` varchar(55) NOT NULL,
  `name` varchar(33) NOT NULL,
  `cat_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `dpt` int NOT NULL,
  `buying` int NOT NULL,
  `b_price` int DEFAULT NULL,
  `price` int NOT NULL,
  `hhr` int NOT NULL,
  `take` int NOT NULL DEFAULT '2',
  `del_status` int NOT NULL DEFAULT '2',
  `recipe` int NOT NULL DEFAULT '2',
  `menu` int NOT NULL,
  `deci` int NOT NULL DEFAULT '2',
  `stock` int NOT NULL DEFAULT '2',
  `ware_qty` int NOT NULL,
  `ware_reorder` int NOT NULL,
  `store_qty` decimal(10,2) NOT NULL,
  `store_take` int NOT NULL DEFAULT '2',
  `store_reorder` int NOT NULL,
  `inst` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reorder` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_reorder` int NOT NULL,
  `cntr1_take` int NOT NULL DEFAULT '2',
  `cntr2_reorder` int NOT NULL,
  `cntr2_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr2_take` int NOT NULL DEFAULT '2',
  `tot_status` int NOT NULL DEFAULT '2',
  `tot_item_id` int NOT NULL,
  `tot_num` int NOT NULL,
  `port_status` int NOT NULL,
  `portions` int NOT NULL,
  `change_price` int NOT NULL,
  `tax_id` int NOT NULL,
  `cntr3_qty` decimal(10,2) NOT NULL,
  `cntr3_reorder` decimal(10,2) NOT NULL,
  `cntr3_take` int NOT NULL DEFAULT '2',
  `has_attributes` int NOT NULL DEFAULT '2',
  `stock_take_type` tinyint(1) NOT NULL DEFAULT '2',
  `empty_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `full_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bottle_mls` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tot_size` decimal(10,2) NOT NULL DEFAULT '30.00',
  PRIMARY KEY (`item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=latin1;

INSERT INTO kp_items VALUES("1","JOHN WALKER BLONDE 750ML","JOHN WALKER BLONDE 750ML","2","13","1","0","1991","3000","3500","2","2","2","1","0","1","0","0","10.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","1","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("2","JOHN WALKER BLACK 750ML","BLACK LABEL 1LTR","2","13","1","0","3911","5700","5500","2","2","2","1","0","1","0","0","39.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("3","JOHN WALKER BLACK 750ML","BLACK LABEL 750ML","2","13","1","0","3236","4700","4850","2","2","2","1","0","1","0","0","35.00","2","10","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("4","BALOZI LAGER CAN","BALOZI LAGER CAN","15","1","1","0","199","300","300","2","2","2","1","2","1","0","0","84.00","2","24","0","0.00","0.00","26.00","0","1","0","39.00","2","2","0","0","0","0","0","1","0.00","0.00","2","1","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("5","BALOZI LAGER","GORDONS PURPLE CAN","15","2","1","0","193","300","300","2","2","2","1","2","1","0","0","24.00","2","24","0","0.00","0.00","14.00","0","1","0","64.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("6","GORDONS PINK TONIC","GORDONS PINK CAN","15","2","1","0","193","300","300","2","2","2","1","2","1","0","0","48.00","2","24","0","0.00","0.00","23.00","0","1","0","74.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("7","MANYATTA PINEAPPLE & MINT","MANYATTA PINEAPPLE & MINT","1","2","1","0","223","300","350","2","2","2","1","2","1","0","0","1126.00","2","24","0","0.00","0.00","95.00","0","1","0","66.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("8","S.ICE PINEAPPLE PUNCH","SIMRNOF ICE PINEAPPLE PUNCH","1","2","1","0","159","280","280","2","2","2","1","2","1","0","0","225.00","2","24","0","0.00","0.00","47.00","0","1","0","78.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("9","WHITE CAP CRISP","WHITE CAP CRISP","1","2","1","0","213","300","300","2","2","2","1","2","1","0","0","150.00","2","24","0","0.00","0.00","80.00","0","1","0","83.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("10","GUINNESS CAN","GUINNESS CAN","15","2","1","0","217","350","300","2","2","2","1","2","1","0","0","96.00","2","24","0","0.00","0.00","33.00","0","1","0","76.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("11","PLISNER CAN","PILSNER CAN","15","2","1","0","199","300","300","2","2","2","1","0","1","0","0","23.00","2","12","0","0.00","0.00","11.00","0","1","0","99.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("12","TUSKER LAGER","TUSKER LAGER CAN","15","2","1","0","199","300","300","2","2","2","1","0","1","0","0","48.00","2","24","0","0.00","0.00","33.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("13","TUSKER CIDER","TUSKER CIDER CAN","15","1","1","0","233","350","350","2","2","2","1","2","1","0","0","66.00","2","24","0","0.00","0.00","29.00","0","1","0","95.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("14","TUSKER LITE","TUSKER LITE CAN","15","2","1","0","243","350","350","2","2","2","1","0","1","0","0","318.00","2","24","0","0.00","0.00","45.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("15","TUSKER MALT","TUSKER MALT CAN","15","2","1","0","242","350","350","2","2","2","1","0","1","0","0","120.00","2","24","0","0.00","0.00","21.00","0","1","0","99.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("16","WHITE CAP","WHITE CAP CAN","15","2","1","0","217","350","350","2","2","2","1","0","1","0","0","126.00","2","24","0","0.00","0.00","27.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("17","GILBEYS 750ML","GILBEYS  750ML","2","11","1","0","1240","2400","2100","2","2","2","1","0","1","0","0","50.00","2","3","0","120.00","0.00","2.00","0","1","0","100.00","2","1","17","25","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("18","GORDONS GIN 1LTR","GORDONS GIN 1LTR","2","11","1","0","2280","3500","3500","2","2","2","1","0","1","0","0","2.00","2","1","0","0.00","0.00","1.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("19","SMIRNOFF ICE BLACK CAN","BLACK ICE CAN","15","2","1","0","176","300","300","2","2","2","1","0","1","0","0","72.00","2","24","0","0.00","0.00","26.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("22","SODA BOTTLE","SODA BOTTLE","5","6","1","0","32","100","100","2","2","2","1","0","1","0","0","1008.00","2","10","0","0.00","0.00","151.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","1","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("23","KERINGET 1LITER","KERINGET 1LITER","5","9","1","0","79","200","200","2","2","2","1","0","1","0","0","384.00","2","10","0","0.00","0.00","39.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("25","DELMONTE","DELMONTE","5","33","1","0","248","400","350","2","2","2","1","1","1","0","0","276.00","2","10","0","0.00","0.00","48.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","1","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("27","JAMESON 1LTR","JAMESON 1LTR","2","13","1","0","3228","4700","4500","2","2","2","1","0","1","0","0","14.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("28","JAMESON 750ML","JAMESON 750ML","2","13","1","0","2350","3700","4000","2","2","2","1","0","1","0","0","12.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("29","FAMOUS GROUSE 1LTR","FAMOUS GROUSE 1LTR","2","13","1","0","2484","4000","3500","2","2","2","1","2","1","0","0","9.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("30","FAMOUS 750ML","FAMOUS 750ML","2","13","1","0","1750","3000","3000","2","2","2","1","0","1","0","0","8.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("31","MARTEL VS 1 LTR","MARTEL VS 1 LTR","2","61","1","0","6913","13000","7000","2","2","2","1","0","1","0","0","3.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("32","MARTEL VS 750ML","MARTEL VS 750ML","2","61","1","0","4991","8000","7500","2","2","2","1","0","1","0","0","11.00","2","0","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("33","JOHN BARR BLACK 1 LTR","JOHN BARR BLACK 1 LTR","2","13","1","0","1980","3500","3200","2","2","2","1","0","1","0","0","11.00","2","10","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("34","JOHN BARR BLACK 750ML ","JOHN BARR BLACK 750ML ","2","13","1","0","1750","3000","2500","2","2","2","1","2","1","0","0","9.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("36","JD 700 ML","JD 700 ML","2","13","1","0","2780","4700","5000","2","2","2","1","0","1","0","0","16.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("37","GILBEYS 350ML","GILBEYS 350ML","2","11","1","0","576","1200","850","2","2","2","1","0","1","0","0","98.00","2","0","0","0.00","0.00","6.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("39","SMINORFF RED 750ML","SMIRNOFF RED 750ML","2","10","1","0","1240","2200","2000","2","2","2","1","0","1","0","0","17.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("40","VAT 69 750ML","VAT 69 750ML","2","13","1","0","1400","2400","2500","2","2","2","1","0","1","0","0","22.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("41","JACK DANIELS 1LTR","JACK DANIELS 1LTR","2","13","1","0","500","5700","5500","2","2","2","1","0","1","0","0","10.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("48","4TH STREET WHITE 750ML","4TH STREET WHITE 750ML","3","4","1","0","886","1500","1500","2","2","2","1","0","1","0","0","16.00","2","100","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("51","CELLAR CASK WHITE 750ML","CELLAR CASK WHITE 750ML","3","4","1","0","850","1500","1500","2","2","2","1","0","1","0","0","12.00","2","100","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("52","DROSTYHOF WHITE 750ML","DROSTYHOF WHITE 750ML","3","4","1","0","902","1500","1500","2","2","2","1","0","1","0","0","18.00","2","100","0","0.00","0.00","4.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("53","FOUR COUSINS RED 750ML","FOUR COUSINS RED 750ML","3","3","1","0","859","1500","2000","2","2","2","1","0","1","0","0","16.00","2","100","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("55","HEINEKEN","HEINEKEN BOTTLE","1","2","1","0","230","300","300","2","2","2","1","2","1","0","0","228.00","2","100","0","0.00","0.00","71.00","0","1","0","78.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("56","HEINEKEIN CAN","HEINEKEIN CAN","15","1","1","0","285","350","350","2","2","2","1","2","1","0","0","108.00","2","100","0","0.00","0.00","33.00","0","1","0","98.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("57","HENNESY VS 700ML","HENNESY VS 700ML","2","61","1","0","5232","8000","7500","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("58","JAGERMEISTER 1LTR","JAGER SHOTS","12","10","1","0","3489","250","4500","2","2","2","1","0","1","0","0","0.00","2","100","0","96.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("59","REDBULL 250ML","REDBULL 250ML","5","0","1","0","195","250","300","2","2","2","1","0","1","0","0","60.00","2","100","0","0.00","0.00","26.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("60","ROBERTSON RED 750ML","ROBERTSON RED 750ML","3","4","1","0","1064","1800","2000","2","2","2","1","0","1","0","0","10.00","2","100","0","0.00","0.00","1.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("64","MALIBU 1LTR","MALIBU 700MLS","2","11","1","0","2559","3200","3500","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("66","BLACK BIRD SWEET REDROSE 750ML","BLACK BIRD SWEET REDROSE 750ML","3","3","1","0","925","1500","1500","2","2","2","1","0","1","0","0","0.00","2","10","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("67","DROSTYHOF RED 750ML","DROSTYHOF RED 750ML","3","3","1","0","961","1500","1500","2","2","2","1","0","1","0","0","19.00","2","100","0","0.00","0.00","4.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("69","SAVANNA DRY 330ML","SAVANNA DRY 330ML","1","2","1","0","245","350","350","2","2","2","1","1","1","0","0","216.00","2","100","0","0.00","0.00","54.00","0","1","0","79.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("70","S.ICE GUARANA CAN","GUARANA CAN","15","2","1","0","176","300","300","2","2","2","1","0","1","0","0","360.00","2","100","0","0.00","0.00","43.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("71","TUSKER LAGER 500ML","TUSKER LAGER 500ML","1","2","1","0","169","260","260","2","2","2","1","0","1","0","0","1650.00","2","100","0","0.00","0.00","134.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("72","FOURTH STREE RED 750ML","4TH STREET RED 750ML","3","3","1","0","930","1500","1800","2","2","2","1","0","1","0","0","24.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("73","ROBERTSON WHYTE 750ML","ROBERTSON WHYTE 750ML","3","4","1","0","1064","1800","2000","2","2","2","1","0","1","0","0","7.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("74","ROSSO NOBILE 75CL","ROSSO NOBILE 75CL","3","3","1","0","1579","2700","2500","2","2","2","1","0","1","0","0","17.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("75","FOUR COUSINS 750ML","FOUR COUSINS WHITE 750ML","3","4","1","0","800","1500","1800","2","2","2","1","0","1","0","0","0.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("76","MINUTE MAID 1LTR","MINUTE MAID 1LTR","5","16","1","0","133","350","200","2","2","2","1","0","1","0","0","24.00","2","100","0","0.00","0.00","14.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("77","KC GINGER 250ML","KC GINGER 250ML","2","60","1","0","256","500","400","2","2","2","1","0","1","0","0","159.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("78","KC PINEAPPLE","KC PINEAPPLE 250ML","2","60","1","0","256","500","400","2","2","2","1","0","1","0","0","98.00","2","10","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("82","JAGER TOT","JAGERMEISTER 1LTR","2","25","1","0","3597","5700","250","2","2","2","1","0","1","0","0","6.00","2","66","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("83","CAPTAIN MORGAN GOLD 250ML","CAPTAIN MORGAN GOLD 250ML","2","29","1","0","336","600","500","2","2","2","1","0","1","0","0","48.00","2","10","0","0.00","0.00","7.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("84","CAPTAIN MORGAN MUCKPIT 750ML","CAPTAIN MORGAN MUCKPIT 750ML","2","29","1","0","1080","1700","2000","2","2","2","1","0","1","0","0","0.00","2","10","0","0.00","0.00","1.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("85","KC LEMON GINGER 750ML","KC LEMON GINGER 750ML","2","60","1","0","672","1500","1200","2","2","2","1","0","1","0","0","121.00","2","10","0","0.00","0.00","6.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("86","TUSKER MALT 330ML","TUSKER MALT BOTTLE","1","2","1","0","187","260","260","2","2","2","1","0","1","0","0","525.00","2","100","0","0.00","0.00","75.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("87","KC PINEAPPLE 750ML","KC PINEAPPLE 750ML","2","60","1","0","672","1500","1200","2","2","2","1","0","1","0","0","24.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("89","VICEROY 750ML","VICEROY 750ML","2","12","1","0","640","2400","2200","2","2","2","1","0","1","0","0","84.00","2","10","0","0.00","0.00","16.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("92","CAMINO GOLD SHOTS","CAMINO GOLD SHOTS","2","12","1","0","1950","200","150","2","2","2","1","0","1","0","0","0.00","2","33","0","-3.00","0.00","0.00","0","1","0","100.00","2","1","97","25","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("93","JOSE CUERVO SHOTS","JOSE CUERVO GOLD SHOTS","2","12","1","0","82","200","200","2","2","2","1","0","1","0","0","0.00","2","33","0","32.00","0.00","1431.00","0","1","0","100.00","2","1","93","33","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("94","JOSE CUERVO SILVER SHOTS","JOSE CUERVO SILVER SHOTS","2","12","1","0","2350","200","200","2","2","2","1","0","1","0","0","0.00","2","33","0","0.00","0.00","1245.00","0","1","0","100.00","2","1","94","33","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("95","GRANTS 1LTR","GRANTS 1LTR","2","13","1","0","2507","4200","3800","2","2","2","1","0","1","0","0","20.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("96","DROSTDY HOF 5LITRE","DROSTDY HOF RED GLASS","3","3","1","0","4609","200","200","2","2","2","1","0","1","0","0","3.00","2","1","0","0.00","0.00","1937.00","0","1","0","100.00","2","2","0","0","1","25","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("97","CAMINO CLEAR SHOTS","CAMINO CLEAR SHOTS","2","12","1","0","2000","200","150","2","2","2","1","0","1","0","0","0.00","2","33","0","123.00","0.00","932.00","0","1","0","100.00","2","1","97","25","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("98","VAT 69 375ML","VAT 69 375ML","2","13","1","0","760","1200","1500","2","2","2","1","0","1","0","0","84.00","2","10","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("99","RICHOT 350ML","RICHOT 350ML","2","12","1","0","576","1200","900","2","2","2","1","0","1","0","0","49.00","2","10","0","0.00","0.00","5.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("100","RICHOT 750ML","RICHOT 750ML","2","12","1","0","1240","2400","2000","2","2","2","1","0","1","0","0","18.00","2","10","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("101","GORDONS DRY 350ML","GORDONS DRY 350ML","2","11","1","0","1008","1500","1400","2","2","2","1","0","1","0","0","0.00","2","10","0","0.00","0.00","3.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("103","MALIBU 700ML","MALIBU  SHOTS","2","11","1","0","1708","200","3000","2","2","2","1","0","1","0","0","0.00","2","10","0","22.00","0.00","881.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("104","MONSTER  ","MONSTER  ","5","0","1","0","200","280","300","2","2","2","1","0","1","0","0","48.00","2","96","0","0.00","0.00","20.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("105","HEINEKEN 0","HEINEKEN ZERO","1","1","1","0","230","260","260","2","2","2","1","0","1","0","0","48.00","2","48","0","0.00","0.00","20.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("107","REMY MARTIN 1LTR","REMY MARTIN VSOP 1LTR","2","61","1","0","9000","12000","12800","2","2","2","1","0","1","0","0","1.00","2","5","0","0.00","0.00","1.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("108","LEMONADE 300ML","LEMONADE 300ML","5","6","1","0","42","100","100","2","2","2","1","0","1","0","0","72.00","2","60","0","0.00","0.00","62.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("109","GILBEYS SHOTS 750ML","GILBEYS SHOTS ","2","11","1","0","1240","150","150","2","2","2","1","2","1","0","0","0.00","2","25","0","0.00","0.00","810.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("111","VICEROY 375ML","VICEROY 375ML","2","12","1","0","659","1200","1200","2","2","2","1","0","1","0","0","132.00","2","1000","0","0.00","0.00","9.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("112","JAGERMEISTER 700","JAGERMEISTER 700ML","2","25","1","0","2250","4600","3600","2","2","2","1","0","1","0","0","6.00","2","5","0","0.00","0.00","2.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("113","VAT69 1LTR","VAT 69 1LTR","2","13","1","0","1700","3500","3000","2","2","2","1","0","1","0","0","0.00","2","5","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("114","JINRO SOJU","JINRO SOJU","1","1","1","0","350","500","500","2","2","2","1","0","1","0","0","50.00","2","48","0","0.00","0.00","20.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("115","S.ICE RASPBERRY CAN","RASPBERRY CAN","15","2","1","0","175","300","300","2","2","2","1","0","1","0","0","48.00","2","24","0","0.00","0.00","42.00","0","1","0","99.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("117","MUCHO MAS 750ML","MUCHO MAS 750ML","3","3","1","0","2600","4000","4000","2","2","2","1","0","1","0","0","4.00","2","10","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("119","PILSNER LAGER","PILSNER LAGER","1","1","1","0","169","260","260","2","2","2","1","0","1","0","0","600.00","2","100","0","0.00","0.00","84.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("120","WHITECAP LAGER","WHITECAP LAGER","1","1","1","0","196","280","280","2","2","2","1","0","1","0","0","3550.00","2","100","0","0.00","0.00","121.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("121","GUINNESS KUBWA","GUINNESS KUBWA","1","1","1","0","204","280","280","2","2","2","1","0","1","0","0","1525.00","2","100","0","0.00","0.00","122.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("122","GUINNESS SMOOTH","GUINNESS SMOOTH","1","1","1","0","169","280","280","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("123","TUSKER LITE","TUSKER LITE","1","1","1","0","187","270","270","2","2","2","1","0","1","0","0","1075.00","2","100","0","0.00","0.00","74.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("124","TUSKER CIDER","TUSKER CIDER","1","1","1","0","222","300","300","2","2","2","1","0","1","0","0","650.00","2","100","0","0.00","0.00","95.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("125","TUSKER NDIMU","TUSKER NDIMU","1","1","1","0","151","220","220","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("126","BALOZI","BALOZI","1","1","1","0","169","260","260","2","2","2","1","0","1","0","0","2025.00","2","100","0","0.00","0.00","29.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("127","SNAPP","SNAPP","1","1","1","0","159","260","260","2","2","2","1","0","1","0","0","225.00","2","100","0","0.00","0.00","78.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("128","HUNTERS DRY","HUNTERS DRY","1","1","1","0","206","300","300","2","2","2","1","0","1","0","0","192.00","2","100","0","0.00","0.00","29.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("129","HUNTERS GOLD","HUNTERS GOLD","1","1","1","0","206","300","300","2","2","2","1","0","1","0","0","48.00","2","100","0","0.00","0.00","63.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("130","KO CIDER","KO CIDER","1","1","1","0","245","300","300","2","2","2","1","0","1","0","0","72.00","2","100","0","0.00","0.00","64.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("131","SUMMIT LAGER","SUMMIT LAGER","1","1","1","0","170","250","250","2","2","2","1","0","1","0","0","75.00","2","100","0","0.00","0.00","30.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("132","BLACK ICE","BLACK ICE","1","1","1","0","159","270","270","2","2","2","1","0","1","0","0","275.00","2","100","0","0.00","0.00","58.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("133","KINGFISHER","KINGFISHER","1","1","1","0","206","280","280","2","2","2","1","0","1","0","0","48.00","2","100","0","0.00","0.00","65.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("134","DESPERADO","DESPERADO","1","1","1","0","298","350","350","2","2","2","1","0","1","0","0","24.00","2","100","0","0.00","0.00","15.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("135","UB","UB","1","1","1","0","130","200","200","2","2","2","1","0","1","0","0","20.00","2","100","0","0.00","0.00","26.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("136","RASPBERRY BOTTLES","RASPBERRY BOTTLES","1","1","1","0","158","270","270","2","2","2","1","0","1","0","0","150.00","2","100","0","0.00","0.00","25.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("137","UB CAN","UB CAN","15","0","1","0","120","300","300","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","12.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("138","FAXES","FAXES","15","0","1","0","290","350","350","2","2","2","1","0","1","0","0","24.00","2","100","0","0.00","0.00","18.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("139","SNAPP CAN","SNAPP CAN","15","0","1","0","176","300","300","2","2","2","1","0","1","0","0","48.00","2","100","0","0.00","0.00","15.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("140","GK SMOOTH CAN","GK SMOOTH CAN","15","0","1","0","173","300","300","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("141","PUNCH CAN","PUNCH CAN","15","0","1","0","176","300","300","2","2","2","1","0","1","0","0","576.00","2","100","0","0.00","0.00","43.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("142","MANYATTA CAN","MANYATTA CAN","15","0","1","0","240","350","350","2","2","2","1","0","1","0","0","156.00","2","100","0","0.00","0.00","23.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("143","TONIC SODA","TONIC SODA","5","6","1","0","67","150","100","2","2","2","1","0","1","0","0","24.00","2","100","0","0.00","0.00","6.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("144","WATER 1L","WATER 1L","5","9","1","0","50","100","100","2","2","2","1","0","1","0","0","2304.00","2","100","0","0.00","0.00","140.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("145","COKE ZERO","COKE ZERO","5","6","1","0","66","150","150","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("146","LIME 1.5LTRS","LIME 1.5LTRS","5","6","1","0","290","350","350","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("147","CHAMDOR 750ML","CHAMDOR 750ML","3","4","1","0","725","2000","2000","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("148","CASABUENA","CASABUENA WHITE","3","4","1","0","761","1300","1300","2","2","2","1","0","1","0","0","36.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("149","CASABUENA RED","CASABUENA RED","3","3","1","0","761","1300","1300","2","2","2","1","0","1","0","0","12.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("150","CAPRICE 1L SWEET RED","CAPRICE 1L SWEET RED","3","3","1","0","903","1300","1300","2","2","2","1","0","1","0","0","25.00","2","100","0","0.00","0.00","6.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("151","CAPRICE 1L SWEET WHITE","CAPRICE 1L SWEET WHITE","3","4","1","0","929","1300","1300","2","2","2","1","0","1","0","0","29.00","2","100","0","0.00","0.00","9.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("152","CELLAR CASK RED 750ML","CELLAR CASK RED 750ML","3","3","1","0","1015","1500","1500","2","2","2","1","0","1","0","0","15.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("153","ASCONI","ASCONI","3","4","1","0","1683","2500","2500","2","2","2","2","0","1","0","0","12.00","2","0","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("154","ROBERTSON RED 1.5L","ROBERTSON RED 1.5L","3","3","1","0","1848","3600","3600","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("155","ROBERTSON WHITE 1.5L","ROBERTSON WHITE 1.5L","3","4","1","0","1350","2700","2700","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("156","NEDERBUG","NEDERBUG","3","3","1","0","2041","2600","2600","2","2","2","1","0","1","0","0","20.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("157","RICHOT 250ML","RICHOT 250ML","2","12","1","0","416","700","700","2","2","2","1","0","1","0","0","56.00","2","100","0","0.00","0.00","6.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("158","VICEROY 250 ML","VICEROY 250 ML","2","12","1","0","380","800","800","2","2","2","1","0","1","0","0","74.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("159","COURVOISIER VS 750ML","COURVOISIER VS 750ML","2","61","1","0","4250","6500","6500","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("160","COURVOISIER VSOP 1L","COURVOISIER VSOP 1L","2","61","1","0","6500","10000","10000","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("161","HENNESY 350ML","HENNESY 350ML","2","61","1","0","2943","4000","4000","2","2","2","1","0","1","0","0","8.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("162","HENNESY VSOP 700ML","HENNESY VSOP 700ML","2","61","1","0","9965","15000","15000","2","2","2","1","0","1","0","0","8.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("163","HENNESSY VS 1L","HENNESSY VS 1L","2","61","1","0","8458","11000","11000","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("164","MARTEL XO","MARTEL XO","2","61","1","0","15000","30000","30000","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("165","REMY MARTIN VSOP 750ML","REMY MARTIN VSOP 750ML","2","61","1","0","8308","9000","13000","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("166","SHERIDAN 1L","SHERIDAN 1L","2","25","1","0","4000","6600","6600","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("167","HENNESY VSOP 1L","HENNESY VSOP 1L","2","61","1","0","10000","17500","17500","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("168","MARTEL VSOP 1L","MARTEL VSOP 1L","2","61","1","0","10800","18000","18000","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("169","JOSE QUERVO GOLD 750ML","JOSE QUERVO GOLD 750ML","2","37","1","0","2800","3800","3500","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("170","JOSE QUEVO SILVER 750ML","JOSE QUEVO SILVER 750ML","2","37","1","0","2900","3800","4000","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("171","KC 350ML ","KC 350ML ","2","60","1","0","352","700","1000","2","2","2","1","0","1","0","0","22.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("172","SINGLETON 12 YRS","SINGLETON 12 YRS","2","13","1","0","4700","7500","7500","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("173","SINGLETON 15YRS","SINGLETON 15YRS","2","13","1","0","6320","8500","8500","2","2","2","1","0","1","0","0","11.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("174","SINGLETON 18YRS","SINGLETON 18YRS","2","13","1","0","8400","15000","15000","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("175","ALL SEASONS 750ML","ALL SEASONS 750ML","2","13","1","0","970","2200","2200","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("176","GLEN SILVER 750ML","GLEN SILVER 750ML","2","13","1","0","1170","2000","2000","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("177","MARTELL BLUESWIFT","MARTELL BLUESWIFT","2","61","1","0","9000","15000","15000","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("178","BEST WHISKY 750ML","BEST WHISKY 750ML","2","13","1","0","910","1700","1700","2","2","2","1","0","1","0","0","10.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("179","BOND 7 750ML","BOND 7 750ML","2","13","1","0","896","2400","2400","2","2","2","1","0","1","0","0","20.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("180","BOND 7 350ML","BOND 7 350ML","2","13","1","0","576","1200","1200","2","2","2","1","0","1","0","0","54.00","2","100","0","0.00","0.00","7.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("181","BOND 7 250ML","BOND 7 250ML","2","13","1","0","415","600","600","2","2","2","1","0","1","0","0","38.00","2","100","0","0.00","0.00","7.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("182","JD 350ML","JD 350ML","2","13","1","0","2000","3200","3200","2","2","2","1","0","1","0","0","6.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("183","JD SINGLE BARREL","JD SINGLE BARREL","2","13","1","0","5600","8200","8200","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("184","JACK  GENTLEMAN","JACK  GENTLEMAN","2","13","1","0","4500","6000","6000","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("185","JD HONEY","JD HONEY","2","13","1","0","3700","5500","5500","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("186","JD FIRE 1L","JD FIRE 1L","2","13","1","0","3500","6500","6500","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("187","RED LABEL 1L","RED LABEL 1L","2","13","1","0","1991","4000","4000","2","2","2","1","0","1","0","0","26.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("188","RED LABEL 350ML ","RED LABEL 350ML ","2","13","1","0","853","2000","2000","2","2","2","1","0","1","0","0","30.00","2","100","0","0.00","0.00","10.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("189","GREEN LABEL 750ML","GREEN LABEL 750ML","2","13","1","0","6800","9500","9500","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("190","BLACK LABEL 350ML","BLACK LABEL 350ML","2","13","1","0","1564","2700","2700","2","2","2","1","0","1","0","0","25.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("191","JW 750ML GOLD RESERVE","JW GOLD RESERVE  750ML","2","4","1","0","7600","12500","12500","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("192","JW 750ML 18YRS","JW  18YRS  750ML","2","13","1","0","9700","14000","14000","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("193","JW DOUBLE BLACK 1L","DOUBLE BLACK 1L","2","13","1","0","5280","8200","8200","2","2","2","1","0","1","0","0","17.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("194","J& B 1L","J & B 1L","2","13","1","0","1600","3200","3200","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("195","KETELONE","KETEL ONE","2","10","1","0","1600","3000","3000","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("196","GRANTS 750ML","GRANTS 750ML","2","13","1","0","1825","3200","3200","2","2","2","1","0","1","0","0","21.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("197","GRANTS 350ML","GRANTS 350ML","2","13","1","0","800","1600","1600","2","2","2","1","0","1","0","0","8.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("198","WILLIAMLAWSON 750ML","WILLIAM LAWSON 750ML","2","13","1","0","1450","2200","2200","2","2","2","1","0","1","0","0","9.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("199","BLACK AND WHITE 750ML","BLACK AND WHITE 750ML","2","13","1","0","11130","2200","1","2","2","2","1","0","1","0","0","7.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("200","BLACK AND WHITE 375ML","BLACK AND WHITE 375ML","2","13","1","0","575","1100","1","2","2","2","1","0","1","0","0","63.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("201","JAMESON 350ML","JAMESON 350ML","2","13","1","0","1243","2500","1","2","2","2","1","0","1","0","0","13.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("202","JAMESON BLACK BARREL","JAMESON BLACK BARREL","2","13","1","0","4200","6500","1","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("203","GLENFIDDICH 12YRS","GLENFIDDICH 12YRS 750ML","2","13","1","0","5200","10500","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("204","15YRS GLENFIDDICH 750ML","GLENFIDDICH 15YRS 750ML","2","13","1","0","7200","10500","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("205","18YRS GLENFIDDICH","GLENFIDDICH 18YRS 750ML","2","13","1","0","11350","17000","1","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("206","GLENLIVET FOUNDERS 750ML","GLENLIVET FOUNDERS 750ML","2","13","1","0","8500","12500","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("207","FERNET 1L","FERNET 1L","2","25","1","0","3834","5500","1","2","2","2","1","0","1","0","0","8.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("208","JIMBEAM 750ML","JIMBEAM 750ML","2","13","1","0","2100","3000","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("209","BULLETBOURBON","BULLET BOURBON","2","13","1","0","2150","6000","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("210","TULLAMORE 700ML","TULLAMORE 700ML","2","13","1","0","2605","3700","1","2","2","2","1","0","1","0","0","6.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("211","GLENLIVET 750ML 12YRS","GLENLIVET 750ML 12YRS","2","13","1","0","4800","9000","1","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("212","GLENLIVET 15YRS","GLENLIVET 15YRS 750ML","2","13","1","0","1","1","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("213","JIMBEAM 1L","JIMBEAM 1L","2","13","1","0","2377","4000","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("214","GRAYSON 750ML","GRAYSON 750ML","2","13","1","0","630","1900","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("215","JOHN BARR RED 750ML","JOHN BARR RED 750ML","2","13","1","0","1417","2500","1","2","2","2","1","0","1","0","0","6.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("216","HAMPTONS","HAMPTONS","2","13","1","0","1328","2500","1","2","2","2","1","0","1","0","0","12.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("217","FERNET 750ML","FERNET 750ML","2","25","1","0","3325","4600","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("218","JOHN BARR RED 1L","JOHN BARR RED 1L","2","13","1","0","1600","3200","1","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("219","V &A 250ML","V &A 250ML","2","25","1","0","296","500","1","2","2","2","1","0","1","0","0","29.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("220","V & A 750ML","V & A 750ML","2","25","1","0","780","1500","1","2","2","2","1","0","1","0","0","13.00","2","100","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("221","BAILEYS STRAWBERRY","BAILEYS STRAWBERRY","2","25","1","0","1","1","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("222","BAILEYS 1L","BAILEYS 1L","2","25","1","0","2650","4000","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("223","BAILEYS 750ML ","BAILEYS 750ML ","2","25","1","0","1500","3200","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("224","BAILEYS 370ML","BAILEYS 370ML","2","25","1","0","1220","2000","1","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("225","CHIVAS 12 YRS","CHIVAS 12 YRS","2","13","1","0","3450","5600","1","2","2","2","1","0","1","0","0","5.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("226","CHIVAS 15YRS","CHIVAS 15YRS","2","13","1","0","3200","5000","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("227","CHIVAS 18YRS","CHIVAS 18YRS","2","13","1","0","1","1","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("228","CHIVAS 1L","CHIVAS 1L","2","13","1","0","1","1","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("229","AMARULA 350ML","AMARULA 350ML","2","25","1","0","800","2200","1","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("230","AMARULA 750ML","AMARULA 750ML","2","25","1","0","1100","4000","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("231","SOUTHERN COMFORT 750ML","SOUTHERN COMFORT 750ML","2","13","1","0","1850","4000","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("232","SOUTHERN COMFORT 1L","SOUTHERN COMFORT 1L","2","13","1","0","2950","4500","1","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("233","MONKEY SHOULDER 1L","MONKEY SHOULDER 1L","2","13","1","0","4930","8000","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("234","BALLENTINES 750ML","BALLENTINES 750ML","2","13","1","0","1850","3500","1","2","2","2","1","0","1","0","0","4.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("235","BALLENTINES 350ML","BALLENTINES 350ML","2","13","1","0","960","1500","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("236","BALLENTINES 1L","BALLENTINES 1L","2","13","1","0","2450","4500","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("237","DON MONTEGO 500ML","DON MONTEGO 500ML","2","12","1","0","1050","1700","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("238","DON MONTEGO 750ML","DON MONTEGO 750ML","2","12","1","0","2200","3800","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("239","CAMINO 750ML","CAMINO 750ML","2","12","1","0","2376","3500","1","2","2","2","1","0","1","0","0","7.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("240","SMIRNOFF VODKA 1L","SMIRNOFF RED 1L","2","10","1","0","1600","3000","1","2","2","2","1","0","1","0","0","12.00","2","10","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("242","SMIRNOFF VODKA 350ML","SMIRNOFF RED 350ML","2","10","1","0","576","1000","1","2","2","2","1","0","1","0","0","77.00","2","100","0","0.00","0.00","9.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("243","ABSOLUTE VODKA 750ML","ABSOLUTE VODKA 750ML","2","10","1","0","2123","3200","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("244","ABSOLUTE VODKA 1L","ABSOLUTE VODKA 1L","2","10","1","0","2825","4200","1","2","2","2","1","0","1","0","0","2.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("245","GREY GOOSE 1L","GREY GOOSE 1L","2","10","1","0","4600","6600","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("246","GILBEYS 250ML","GILBEYS 250ML","2","11","1","0","350","800","1","2","2","2","1","0","1","0","0","73.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("247","GILBEYS PINK 750ML","GILBEYS PINK 750ML","2","11","1","0","1240","2400","1","2","2","2","1","0","1","0","0","60.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("248","GORDONS CLEAR 750ML","GORDONS CLEAR 750ML","2","11","1","0","850","3000","1","2","2","2","1","0","1","0","0","9.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("249","GORDONS PINK 750ML","GORDONS PINK 750ML","2","11","1","0","2200","3500","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("250","TANQUERY GIN","TANQUERY GIN","2","11","1","0","1917","4000","1","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("251","TANQUERY SERVILLA","TANQUERY SERVILLA","2","11","1","0","2370","4000","1","2","2","2","1","0","1","0","0","3.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("252","TANQUERY 10 1L","TANQUERY 10 1L","2","11","1","0","2450","4200","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("253","TANQUERY 10 750ML","TANQUERY 10 750ML","2","11","1","0","4080","7500","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("254","BEEFEATER DRY GIN","BEEFEATER DRY GIN","2","11","1","0","2550","4000","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("255","CRUXLAND GIN 750ML","CRUXLAND GIN 750ML","2","11","1","0","2050","3500","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("256","KO  TAMBUZI 750ML","KO  TAMBUZI 750ML","2","11","1","0","1550","2700","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("257","KO CLASSIC 750ML","KO CLASSIC 750ML","2","11","1","0","2700","4500","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("258","CIROC 750ML","CIROC 750ML","2","10","1","0","4250","5000","1","2","2","2","1","0","1","0","0","1.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("259","VAT 69 SHOTS","VAT 69 SHOTS","12","26","1","0","100","150","150","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1120.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("260","SPUTHERN COMFORT 1L SHOT","SOUTHERN COMFORT SHOT","12","26","1","0","100","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1137.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("261","GREY GOOSE SHOT","GREY GOOSE SHOT","12","26","1","0","100","150","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","751.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("262","VICEROY SHOT","VICEROY SHOT","12","26","1","0","50","150","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","671.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("263","JAGGERMASTER SHOT","JAGGERMASTER SHOT","12","26","1","0","1","1","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1003.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("264","FAMOUS SHOT","FAMOUS SHOT","12","26","1","0","100","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1000.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("265","GORDONS SHOT","GORDONS SHOT","12","26","1","0","100","200","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1007.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("266","TEQUILA SHOTS","TEQUILA SHOTS","12","37","1","0","100","200","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("267","CHAMPARI SHOT","CHAMPARI SHOT","12","26","1","0","100","150","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1120.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("268","HENESSY VS SHOT","HENESSY VS SHOT","12","26","1","0","200","400","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","968.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("269","HENNESY VSOP SHOT","HENNESY VSOP SHOT","12","26","1","0","300","600","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1637.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("270","MARTEL VS SHOT","MARTEL VS SHOT","12","26","1","0","200","400","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1045.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("271","SHERIDAN SHOT","SHERIDAN SHOT","12","26","1","0","150","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","2019.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("272","VODKA SHOT","VODKA SHOT","12","0","1","0","100","150","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","999.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("273","JAMESON SHOT","JAMESON SHOT","12","13","1","0","100","200","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1357.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("274","JD SHOT","JD SHOT","12","26","1","0","150","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","982.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("275","GRANTS SHOT","GRANTS SHOT","12","26","1","0","150","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("276","REDLABEL SHOT","REDLABEL SHOT","12","26","1","0","150","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","932.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("277","BLACKLABEL SHOT","BLACK LABEL SHOT","12","26","1","0","150","250","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1088.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("278","FERNET SHOT","FERNET SHOT","12","26","1","0","100","200","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","974.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("279","LIME SHOT","LIME SHOT","12","26","1","0","20","50","50","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","1273.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("281","HEDEX","HEDEX","6","23","1","0","10","20","20","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","40.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("282","MARAMOJA","MARAMOJA","6","23","1","0","10","20","20","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("283","PANADOL","PANADOL","6","23","1","0","10","20","20","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("284","ENO SACHETS","ENO SACHETS","6","23","1","0","15","30","30","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","24.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("285","ACTOL TABLETS","ACTOL TABLETS","6","23","1","0","10","30","30","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","9.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("286","HONEY","HONEY","6","40","1","0","40","100","100","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","7.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("287","EMBASSY LIGHTS","EMBASSY LIGHTS","6","38","1","0","540","600","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("288","DUNHILL DOUBLE","DUNHILL DOUBLE","6","38","1","0","550","650","650","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","9.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("289","DUNHILL SINGLE","DUNHILL SINGLE","6","38","1","0","550","650","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","12.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("290","SPORTMAN ","SPORTMAN ","6","38","1","0","350","450","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("291","MATCHBOX","MATCHBOX","6","38","1","0","5","10","1","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("292","SAVANNA LEMON","SAVANNA LEMON","1","1","1","0","253","350","350","2","2","2","1","0","1","0","0","186.00","2","100","0","0.00","0.00","56.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("293","KC SMOOTH 250ML","KC SMOOTH 250ML","2","60","1","0","256","500","500","2","2","2","1","0","1","0","0","117.00","2","100","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("294","KC  SMOOTH 750ML","KC  SMOOTH 750ML","2","60","1","0","672","1500","1500","2","2","2","1","0","1","0","0","98.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("295","BLACKBIRD ROSE 750ML","BLACK BIRD ROSE 750ML","3","4","1","0","954","1500","1500","2","2","2","1","0","1","0","0","100.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("296","REMY MARTIN 1738 750ML","REMY MARTIN 1738 750ML","2","61","1","0","10100","15000","15000","2","2","2","1","2","1","0","0","105.00","2","3","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("297","CAPRICE DRY RED","CAPRICE DRY RED","3","3","1","0","929","1300","1300","2","2","2","1","2","1","0","0","112.00","2","6","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("298","CAPRICE DRY WHITE","CAPRICE DRY WHITE","3","4","1","0","929","1300","1300","2","2","2","1","2","1","0","0","100.00","2","6","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("299","KERINGET SPARKLING 1L","KERINGET SPARKLING 1L","5","9","1","0","96","200","200","2","2","2","1","2","1","0","0","112.00","2","1","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("300","CAPTAIN MORGAN GOLD 750ML","CAPTAIN MORGAN GOLD 750ML","2","29","1","0","960","1700","1700","2","2","2","1","2","1","0","0","112.00","2","10","0","0.00","0.00","6.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("301","CAPTAIN MORGAN MUCKPIT 250ML","CAPTAIN MORGAN MUCKPIT 250ML","2","29","1","0","330","600","600","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("302","HUNTERS CHOICE 750ML","HUNTERS CHOICE 750ML","2","13","1","0","910","1700","1700","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("303","HUNTERS CHOICE 350ML","HUNTERS CHOICE 350ML","2","13","1","0","448","900","900","2","2","2","1","2","1","0","0","112.00","2","10","0","0.00","0.00","5.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("304","HUNTERS CHOICE 250ML","HUNTERS CHOICE 250ML","2","13","1","0","300","600","600","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("305","RED LABEL 750ML","RED LABEL 750ML","2","13","1","0","1799","3000","3000","2","2","2","1","2","1","0","0","112.00","2","10","0","0.00","0.00","4.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("306","AMARULA 1L","AMARULA 1L","2","25","1","0","2000","5000","5000","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("307","MARTEL VSOP 700ML","MARTEL VSOP 700ML","2","61","1","0","7000","13000","13000","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","3.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("308","ASTROL  BLANCO 750ML","ASTROL  BLANCO 750ML","2","37","1","0","1900","7000","7000","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("309","ASTRO RIPOSADO 750ML","ASTRO RIPOSADO 750ML","2","37","1","0","3400","9000","9000","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("310","JOSE QUERVO GOLD 1L","JOSE QUERVO GOLD 1L","2","37","1","0","2500","4200","4200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","1.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("311","GUINESS SMALL","GUINESS SMALL","1","2","1","0","120","280","280","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","16.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("312","CELLAR CASK WHITE GLASS","CELLAR CASK WHITE GLASS","3","4","1","0","100","200","200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","4728.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("313","CELLAR CASK RED GLASS","CELLAR CASK RED GLASS","3","3","1","0","100","200","200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","4030.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("314","4TH STREET RED GLASS","4TH STREET RED GLASS","3","3","1","0","100","200","200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","544.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("315","4TH STREET WHITE GLASS","4TH STREET WHITE GLASS","3","4","1","0","100","200","200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","3355.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("316","DROSTDY HOF WHITE GLASS","DROSTDY HOF WHITE GLASS","3","4","1","0","100","200","200","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","1018.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("317","CONDOM","TRUST","6","24","1","0","70","150","150","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","15.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("318","GLENLIVET 15YRS TOT","GLENLIVET 15YRS TOT","12","13","1","0","300","600","600","2","2","2","1","2","1","0","0","100.00","2","10","0","0.00","0.00","630.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("319","SMIRNOFF RED 250ML","SMIRNOFF RED 250ML","2","10","1","0","416","700","1","2","2","2","1","2","1","0","0","100.00","2","100","0","0.00","0.00","8.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items VALUES("320","CAPRICE SWEETRED 750ML BOTTLE","CAPRICE SWEETRED 750ML BOTTLE","3","3","1","0","739","1200","1200","2","2","2","2","2","1","0","0","103.00","2","100","0","0.00","0.00","2.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");





CREATE TABLE `kp_items_arch` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `bname` varchar(55) NOT NULL,
  `name` varchar(33) NOT NULL,
  `cat_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `dpt` int NOT NULL,
  `buying` int NOT NULL,
  `b_price` int DEFAULT NULL,
  `price` int NOT NULL,
  `hhr` int NOT NULL,
  `take` int NOT NULL DEFAULT '2',
  `del_status` int NOT NULL DEFAULT '2',
  `recipe` int NOT NULL DEFAULT '2',
  `menu` int NOT NULL,
  `deci` int NOT NULL DEFAULT '2',
  `stock` int NOT NULL DEFAULT '2',
  `ware_qty` int NOT NULL,
  `ware_reorder` int NOT NULL,
  `store_qty` decimal(10,2) NOT NULL,
  `store_take` int NOT NULL DEFAULT '2',
  `store_reorder` int NOT NULL,
  `inst` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reorder` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_reorder` int NOT NULL,
  `cntr1_take` int NOT NULL DEFAULT '2',
  `cntr2_reorder` int NOT NULL,
  `cntr2_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr2_take` int NOT NULL DEFAULT '2',
  `tot_status` int NOT NULL DEFAULT '2',
  `tot_item_id` int NOT NULL,
  `tot_num` int NOT NULL,
  `port_status` int NOT NULL,
  `portions` int NOT NULL,
  `change_price` int NOT NULL,
  `tax_id` int NOT NULL,
  `cntr3_qty` decimal(10,2) NOT NULL,
  `cntr3_reorder` decimal(10,2) NOT NULL,
  `cntr3_take` int NOT NULL DEFAULT '2',
  `has_attributes` int NOT NULL DEFAULT '2',
  `stock_take_type` tinyint(1) NOT NULL DEFAULT '2',
  `empty_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `full_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bottle_mls` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tot_size` decimal(10,2) NOT NULL DEFAULT '30.00',
  PRIMARY KEY (`item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=latin1;

INSERT INTO kp_items_arch VALUES("21","PET SODA","PET SODA","4","9","1","0","61","150","150","2","2","2","1","0","1","0","0","172.00","2","10","0","0.00","0.00","100.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("24","PREDATOR","PREDATOR","4","9","1","0","54","100","100","2","2","2","1","0","1","0","0","112.00","2","10","0","0.00","0.00","100.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("38","SMIRNOFF RED 1LTR","SMIRNOFF RED 1LTR","5","5","1","0","1600","2200","2200","2","2","2","1","0","1","0","0","1.00","2","0","0","0.00","0.00","0.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("44","MANGO LASSI MINT","MANGO LASSI MINT","7","17","1","0","100","200","200","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","4.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("46","CRATE SODA","CRATE SODA","8","0","1","0","460","460","460","2","2","2","2","0","2","0","0","1.00","2","0","0","0.00","0.00","0.00","0","2","0","100.00","2","2","0","0","0","0","0","2","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("49","58 CLASSIC G&T 330ML CAN","58 CLASSIC G&T 330ML CAN","1","2","1","0","216","350","350","2","2","2","1","0","1","0","0","6.00","2","100","0","0.00","0.00","100.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","1","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("50","CELLAR CASK JHB 750ML","CELLAR CASK JHB 750ML","3","3","1","0","985","1850","1850","2","2","2","1","0","1","0","0","34.00","2","100","0","0.00","0.00","100.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("68","SAFARI WATER 1LTR","SAFARI WATER 1LTR","4","13","1","0","42","100","100","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","99.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("79","CHROME GIN 750ML","CHROME GIN 750ML","2","5","1","0","560","1000","1000","2","2","2","1","0","1","0","0","1.00","2","10","0","0.00","0.00","9.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("80","CHROME GIN 250ML","CHROME GIN 250ML","2","5","1","0","208","350","350","2","2","2","1","0","1","0","0","0.00","2","10","0","0.00","0.00","9.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("81","CHROME VODKA 750ML","CHROME VODKA 750ML","2","5","1","0","560","1000","1000","2","2","2","1","0","1","0","0","1.00","2","10","0","0.00","0.00","98.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("88","CRATE BEER","CRATE BEER","1","2","0","0","760","760","760","2","2","2","2","0","2","0","0","0.00","2","0","0","0.00","0.00","0.00","0","2","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("90","DOUBLE BLACK TOT","DOUBLE BLACK SHOT","2","5","1","0","3960","300","300","2","2","2","1","0","1","0","0","1.00","2","33","0","64.00","0.00","100.00","0","1","0","100.00","2","1","90","33","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("102","SMOKEHOUSE WATER 1LTR","SMOKEHOUSE WATER 1LTR","4","13","1","0","33","150","150","2","2","2","1","0","1","0","0","60.00","2","100","0","0.00","0.00","100.00","0","1","0","100.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("116","SAVANNA LEMON","SAVANNA LEMON","1","2","1","0","256","350","350","2","2","2","2","0","1","0","0","0.00","2","0","0","0.00","0.00","0.00","0","2","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("118","117","117","3","3","1","0","2600","4000","4000","2","2","2","1","0","1","0","0","3.00","2","5","0","0.00","0.00","0.00","0","2","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("241","SMIRNOFF VODKA 750ML","SMIRNOFF VODKA 750ML","2","10","1","0","1240","1","1","2","2","2","1","0","1","0","0","26.00","2","100","0","0.00","0.00","0.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");
INSERT INTO kp_items_arch VALUES("280","TRUST","TRUST","6","24","1","0","50","100","100","2","2","2","1","0","1","0","0","0.00","2","100","0","0.00","0.00","18.00","0","1","0","0.00","2","2","0","0","0","0","0","1","0.00","0.00","2","2","2","0.00","0.00","0.00","30.00");





CREATE TABLE `kp_items_attribute_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `kp_items_attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(55) COLLATE utf8mb4_general_ci NOT NULL,
  `affects_price` int NOT NULL DEFAULT '2',
  `adds_new_item` int NOT NULL DEFAULT '2',
  `price_change` int NOT NULL,
  `linked_items` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_items_attributes VALUES("1","FANTA ORANGE","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("2","COKE","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("3","STONEY","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("4","SPRITE","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("5","BLACK CURRENT","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("6","KREST","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("7","NOVIDA","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("8","FANTA PASSION","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("9","MANGO","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("10","APPLE","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("11","MIXED BERRIES","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("12","PASSION","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("13","PINEAPPLE","0","2","0","","0");
INSERT INTO kp_items_attributes VALUES("14","TROPICAL","0","2","0","","0");





CREATE TABLE `kp_items_old` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `bname` varchar(55) NOT NULL,
  `name` varchar(33) NOT NULL,
  `cat_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `dpt` int NOT NULL,
  `b_price` int DEFAULT NULL,
  `price` int NOT NULL,
  `hhr` int NOT NULL,
  `take` int NOT NULL DEFAULT '2',
  `del_status` int NOT NULL DEFAULT '2',
  `recipe` int NOT NULL DEFAULT '2',
  `menu` int NOT NULL,
  `deci` int NOT NULL DEFAULT '2',
  `stock` int NOT NULL DEFAULT '2',
  `ware_qty` int NOT NULL,
  `ware_reorder` int NOT NULL,
  `store_qty` decimal(10,2) NOT NULL,
  `store_take` int NOT NULL DEFAULT '2',
  `store_reorder` int NOT NULL,
  `inst` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reorder` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_reorder` int NOT NULL,
  `cntr1_take` int NOT NULL DEFAULT '2',
  `cntr2_reorder` int NOT NULL,
  `cntr2_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr2_take` int NOT NULL DEFAULT '2',
  `tot_status` int NOT NULL DEFAULT '2',
  `tot_item_id` int NOT NULL,
  `tot_num` int NOT NULL,
  `port_status` int NOT NULL,
  `portions` int NOT NULL,
  `change_price` int NOT NULL,
  `tax_id` int NOT NULL,
  `cntr3_qty` decimal(10,2) NOT NULL,
  `cntr3_reorder` decimal(10,2) NOT NULL,
  `cntr3_take` int NOT NULL DEFAULT '2',
  `has_attributes` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_itemsold` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `bname` varchar(55) NOT NULL,
  `name` varchar(33) NOT NULL,
  `cat_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `dpt` int NOT NULL,
  `b_price` int DEFAULT NULL,
  `price` int NOT NULL,
  `hhr` int NOT NULL,
  `take` int NOT NULL DEFAULT '2',
  `del_status` int NOT NULL DEFAULT '2',
  `recipe` int NOT NULL DEFAULT '2',
  `menu` int NOT NULL,
  `deci` int NOT NULL DEFAULT '2',
  `stock` int NOT NULL DEFAULT '2',
  `store_qty` decimal(10,2) NOT NULL,
  `store_take` int NOT NULL DEFAULT '2',
  `store_reorder` int NOT NULL,
  `inst` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `reorder` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr1_qty` int NOT NULL,
  `cntr1_reorder` int NOT NULL,
  `cntr1_take` int NOT NULL DEFAULT '2',
  `cntr2_reorder` int NOT NULL,
  `cntr2_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cntr2_take` int NOT NULL DEFAULT '2',
  `tot_status` int NOT NULL DEFAULT '2',
  `tot_item_id` int NOT NULL,
  `tot_num` int NOT NULL,
  `port_status` int NOT NULL,
  `portions` int NOT NULL,
  `change_price` int NOT NULL,
  `tax_id` int NOT NULL,
  `has_attributes` int NOT NULL DEFAULT '2',
  `stock_take_type` tinyint(1) NOT NULL DEFAULT '2',
  `empty_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `full_bottle_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bottle_mls` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tot_size` decimal(10,2) NOT NULL DEFAULT '30.00',
  PRIMARY KEY (`item_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_kpesa_settings` (
  `set_id` int NOT NULL DEFAULT '1',
  `api_key` varchar(255) NOT NULL DEFAULT '',
  `fetch_url` varchar(255) NOT NULL DEFAULT 'https://sawela.kpesa.co.ke/api/v1/deposits/payments',
  `has_stk_push` tinyint(1) NOT NULL DEFAULT '0',
  `stk_url` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kp_kpesa_settings VALUES("1","","https://sawela.kpesa.co.ke/api/v1/deposits/payments","0","","1","2026-08-11 11:19:46");





CREATE TABLE `kp_menu` (
  `menu_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `link` varchar(55) NOT NULL,
  `level` int NOT NULL,
  `sub` int NOT NULL,
  `parent` int NOT NULL,
  `icon` varchar(33) NOT NULL,
  `created_by` int NOT NULL,
  `panel` int NOT NULL,
  `modal` int NOT NULL,
  `modal_function` varchar(55) NOT NULL,
  `menu_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;

INSERT INTO kp_menu VALUES("2","DASHBOARD","../dash/admin_dash.php","1","2","0","fa fa-th-large","0","1","0","","0");
INSERT INTO kp_menu VALUES("3","MENU MANAGER","#","1","1","0","fas fa-utensils","0","1","0","","0");
INSERT INTO kp_menu VALUES("4","Menu Items","../menu/","2","2","3","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("5","Items Recipes","../recipe/items_recipe.php","2","2","3","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("6","Menu Categories","../cat/cat.php","2","2","3","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("7","Menu Subcategories","../subcat/subcat.php","2","2","3","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("8","STAFF MANAGER","#","1","1","0","fa fa-users","0","1","0","","0");
INSERT INTO kp_menu VALUES("9","Active Staff List","../ad","2","2","8","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("10","Archived Staff List","../staff/arch_staff.php","2","2","8","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("11","USER MANAGER","#","1","1","0","fa fa-user","0","1","0","","0");
INSERT INTO kp_menu VALUES("12","Active User List","../users/user.php","2","1","11","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("13","Suspended User List","../users/sus_users.php","2","2","11","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("14","Archived User List","../users/arch_users.php","2","2","11","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("15","User Roles","#","2","1","11","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("16","User Role List","../roles/roles.php","3","2","15","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("17","STORE MANAGER","#","1","1","0","fa fa-truck","0","1","0","","0");
INSERT INTO kp_menu VALUES("18","Inventory Manager","#","2","1","17","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("19","Store Stock","../inventory/stock.php","3","2","18","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("20","Issue Counter","../inventory/counter_req.php","3","2","18","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("21","Purchase Manager","../kug/","2","1","17","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("22","Raise P.O","../kugura/init_page.php","3","2","21","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("23","P.O List","../kugura/po_list.php","3","2","21","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("24","REPORTS","#","1","1","0","fa fa-chart-pie","0","1","0","","0");
INSERT INTO kp_menu VALUES("25","Sales","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("26","Shift Sales"," 	../reports/shift_bar.php","4","2","25","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("28","Items","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("29","Department Sale items","../reports/select_shift.php","4","2","28","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("30","Item Sale Summary","../reports/item_select.php","4","2","28","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("31","Stock","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("32","Stock Sheet","#","3","1","31","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("33","Stock Take","../inventory/init_take.php","2","2","17","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("34","SHIFT MANAGER","#","1","1","0","fas fa-concierge-bell","0","1","0","","0");
INSERT INTO kp_menu VALUES("35","Counter Status","../counter/counter_status.php","2","2","34","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("36","Counter Stock Take","../reports/counter_take.php","3","2","31","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("37","Orders","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("38","Voided Orders","../reports/shift_voided.php","3","2","37","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("39","Cancelled Sales","../reports/shift_cancelled.php","3","2","37","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("40","Uncleared Orders","../reports/shift_unclear.php","3","2","37","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("41","Counter","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("42","Counter Requisitions","../reports/counter_req.php","3","2","41","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("43","Departments Sales","../reports/dpt_filter.php","4","2","25","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("44","Purchasing","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("45","Store","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("46","Shift","#","2","1","24","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("47","Shift Report","../reports/shift_report.php","3","2","46","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("48","Purchase By Vendor","../reports/po_sup_select.php","3","1","44","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("49","Pending","../reports/po_list.php","3","2","44","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("50","Store stock take","../reports/store_take.php","3","2","45","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("51","FINANCE","#","1","1","0","fa fa-donate","0","0","2","","0");
INSERT INTO kp_menu VALUES("53","Customers","../customers/customer.php","2","2","51","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("54","Customer Bills","../finance/cust_bill_select.php","2","2","51","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("55","Assign Bills","../finance/assign_bills.php","2","2","51","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("56","Counter Item  Summary","../reports/counter_select.php","3","2","28","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("57","Counter Sales","../reports/counter_s_select.php","3","2","25","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("58","Item per counter","../reports/item_p_select.php","3","2","28","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("59","Item Summary ","../reports/item_sum_select.php","3","2","28","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("60","Purchased Items","../reports/purc_item_select.php","3","2","44","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("61","Stock Levels","../reports/stock_levels.php","3","2","31","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("62","Bar Stock Sheet","../inventory/stock_sheet.php?id=1","4","2","32","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("63","Kitchen Stock Sheet","../inventory/stock_sheet.php?id=2","4","2","32","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("64","Counter Stock Level","../inventory/counter_stock.php","3","2","18","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("65","Bill Payment","#","2","1","51","-","0","0","2","","0");
INSERT INTO kp_menu VALUES("66","Customer Bills","../payment/cust_bal.php","3","2","65","-","0","0","0","","0");
INSERT INTO kp_menu VALUES("67","WAREHOUSE MANAGER","#","1","1","0","fa-solid fa-warehouse ","0","1","2","","0");
INSERT INTO kp_menu VALUES("68","Initialize Request","../ware/req/init_page.php","2","2","67","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("69","Receive from Warehouse","../warerec/","2","2","17","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("70","Counter Items Summary","../reports/req_item_sum.php","3","2","41","-","0","1","2","","0");
INSERT INTO kp_menu VALUES("71","Menu Items Attributes","../menu_attr","2","1","3","-","0","1","0","","0");
INSERT INTO kp_menu VALUES("72","Manual Bill Print","sales/manual_print.php","2","2","24","fa fa-print","0","0","0","","0");





CREATE TABLE `kp_open_color` (
  `id` int NOT NULL AUTO_INCREMENT,
  `color` varchar(20) NOT NULL,
  `label` varchar(60) NOT NULL DEFAULT '',
  `color_key` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_color_key` (`color_key`)
) ENGINE=InnoDB AUTO_INCREMENT=32568 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_open_color VALUES("1","#61b846","Green","green");
INSERT INTO kp_open_color VALUES("2","#f36c26","Orange","orange");
INSERT INTO kp_open_color VALUES("3","#c5432f","Red","red");
INSERT INTO kp_open_color VALUES("4","#d9a441","Gold","gold");
INSERT INTO kp_open_color VALUES("5","#2e7ecb","Blue","blue");
INSERT INTO kp_open_color VALUES("6","#1e8a83","Teal","teal");
INSERT INTO kp_open_color VALUES("7","#814d8c","Plum","plum");
INSERT INTO kp_open_color VALUES("8","#4d4d4d","Charcoal","charcoal");
INSERT INTO kp_open_color VALUES("996","#d9a441","Yellow","yellow");
INSERT INTO kp_open_color VALUES("999","#814d8c","Purple","purple");
INSERT INTO kp_open_color VALUES("1000","#4d4d4d","Black","black");
INSERT INTO kp_open_color VALUES("1001","#d94f87","Pink","pink");
INSERT INTO kp_open_color VALUES("1002","#8b5a2b","Brown","brown");
INSERT INTO kp_open_color VALUES("1003","#234a8f","Navy","navy");
INSERT INTO kp_open_color VALUES("1004","#8bcf3f","Lime","lime");
INSERT INTO kp_open_color VALUES("1005","#8a1c3a","Maroon","maroon");
INSERT INTO kp_open_color VALUES("1006","#1fa7c9","Cyan","cyan");
INSERT INTO kp_open_color VALUES("1007","#8f98a3","Silver","silver");





CREATE TABLE `kp_open_identifier_preset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0',
  `preset_name` varchar(80) NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_preset` (`user_id`,`preset_name`),
  KEY `idx_user_last_used` (`user_id`,`last_used_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_open_identifier_preset VALUES("1","9","Papa tuktuk","2026-06-06 14:27:48","2026-06-06 14:27:48");





CREATE TABLE `kp_pay_id` (
  `pay_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_pay_id VALUES("116718");





CREATE TABLE `kp_pay_mode` (
  `pay_id` int NOT NULL AUTO_INCREMENT,
  `bank_id` int NOT NULL,
  `name` varchar(22) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `btn_color` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `has_ref` int NOT NULL,
  `has_change` int NOT NULL DEFAULT '2',
  `created_by` int NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `del_status` int NOT NULL DEFAULT '2',
  `gate` int NOT NULL DEFAULT '2',
  `tabl` varchar(55) COLLATE latin1_general_ci NOT NULL,
  `zero` int NOT NULL DEFAULT '2',
  `invoice` int NOT NULL DEFAULT '2',
  `has_colle` int NOT NULL DEFAULT '2',
  `accbal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_pay_mode VALUES("1","1","CASH","#630000","2","1","2","2021-03-22 03:38:06","2","2","","2","2","1","5516097.00");
INSERT INTO kp_pay_mode VALUES("2","2","MPESA","#295218","1","2","2","2021-03-22 03:38:21","2","1","split_message","2","2","2","29942012.50");
INSERT INTO kp_pay_mode VALUES("3","1","COMP","BLUE","2","2","1","2021-06-19 04:23:46","2","2","","2","2","2","588042.00");
INSERT INTO kp_pay_mode VALUES("4","1","PDQ","BROWN","1","2","19","2023-06-28 09:03:40","2","2","","2","2","2","3009816.00");





CREATE TABLE `kp_pay_mode_hist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pay_id` int NOT NULL,
  `transaction` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `trans_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `user_id` int NOT NULL,
  `accbal` decimal(10,2) NOT NULL,
  `shift` date NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_pay_mode_hist VALUES("1","116718","","0","300.00","1","5515797.00","2026-08-11","2026-08-17","2026-08-17 13:08:45");





CREATE TABLE `kp_po` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL,
  `req_id` int NOT NULL,
  `sup_id` int NOT NULL,
  `ord_user` int NOT NULL,
  `shift` date NOT NULL,
  `user_id` int NOT NULL,
  `po_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `disc` decimal(10,2) NOT NULL,
  `tax_by` int NOT NULL,
  `tax_type` int NOT NULL,
  `tax_perc` int NOT NULL,
  `vatable` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `rec_staff` int NOT NULL,
  `rec_date` date NOT NULL,
  `stock_status` int NOT NULL DEFAULT '2',
  `grn_status` int NOT NULL DEFAULT '2',
  `convert_currency` int NOT NULL,
  `currency` varchar(11) NOT NULL,
  `convert_rate` decimal(10,2) NOT NULL,
  `converted_total` decimal(10,2) NOT NULL,
  `cf_bal` decimal(10,2) NOT NULL,
  `refno` varchar(55) NOT NULL,
  `s2s` int NOT NULL DEFAULT '2',
  `ord_date` date DEFAULT NULL,
  `appr_status` tinyint(1) NOT NULL DEFAULT '2',
  `appr_user` int NOT NULL DEFAULT '0',
  `appr_datime` datetime DEFAULT NULL,
  `rec_datime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

INSERT INTO kp_po VALUES("1","2","0","4","1","0000-00-00","0","2026-08-15","229026.00","0.00","0","0","16","229026.00","0.00","1","0","0000-00-00","1","2","0","","0.00","0.00","0.00","INV8545","1","","2","0","","");
INSERT INTO kp_po VALUES("2","3","0","3","1","0000-00-00","0","2026-08-15","7964.00","0.00","0","0","16","7964.00","0.00","1","0","0000-00-00","1","2","0","","0.00","0.00","0.00","29851","1","","2","0","","");
INSERT INTO kp_po VALUES("3","4","0","3","1","0000-00-00","0","2026-08-15","48243.00","0.00","0","0","16","48243.00","0.00","1","0","0000-00-00","1","2","0","","0.00","0.00","0.00","29850","1","","2","0","","");
INSERT INTO kp_po VALUES("4","5","0","3","1","2026-08-11","0","2026-08-15","769734.64","0.00","0","1","16","769734.64","106170.30","1","1","2026-08-16","1","2","0","","0.00","0.00","0.00","29849","1","","2","0","","");
INSERT INTO kp_po VALUES("5","6","0","5","1","2026-08-11","0","2026-08-16","36000.00","0.00","0","1","16","36000.00","4965.52","1","1","2026-08-16","1","2","0","","0.00","0.00","0.00","6740","1","","2","0","","");
INSERT INTO kp_po VALUES("6","9","0","6","1","2026-08-17","0","2026-08-17","11334.96","0.00","0","1","16","11334.96","1563.44","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","5838","1","","2","0","","");
INSERT INTO kp_po VALUES("7","11","0","6","1","2026-08-17","0","2026-08-17","682082.60","0.00","0","1","16","682082.60","94080.36","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","CS0B097065420","1","","2","0","","");
INSERT INTO kp_po VALUES("8","13","0","4","1","2026-08-17","0","2026-08-17","205344.10","0.00","0","1","16","205344.10","28323.32","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","8578","1","","2","0","","");
INSERT INTO kp_po VALUES("9","15","0","7","1","2026-08-17","0","2026-08-17","116808.00","0.00","0","1","16","116808.00","16111.45","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","2090","1","","2","0","","");
INSERT INTO kp_po VALUES("10","16","0","7","1","2026-08-17","0","2026-08-17","32880.00","0.00","0","1","16","32880.00","4535.17","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","2092","1","","2","0","","");
INSERT INTO kp_po VALUES("11","17","0","5","1","2026-08-17","0","2026-08-17","36000.00","0.00","0","1","16","36000.00","4965.52","1","1","2026-08-17","1","2","0","","0.00","0.00","0.00","6748","1","","2","0","","");
INSERT INTO kp_po VALUES("12","18","0","3","1","2026-08-17","0","2026-08-19","723792.16","0.00","0","1","16","723792.16","99833.40","1","1","2026-08-19","1","2","0","","0.00","0.00","0.00","CS0B097065496","1","","2","0","","");
INSERT INTO kp_po VALUES("13","19","0","4","1","2026-08-17","0","2026-08-19","86751.62","0.00","0","1","16","86751.62","11965.74","1","1","2026-08-19","1","2","0","","0.00","0.00","0.00","8609","1","","2","0","","");
INSERT INTO kp_po VALUES("14","20","0","6","1","2026-08-17","0","2026-08-19","14199.96","0.00","0","1","16","14199.96","1958.62","1","1","2026-08-19","1","2","0","","0.00","0.00","0.00","5878","1","","2","0","","");
INSERT INTO kp_po VALUES("15","21","0","4","1","2026-08-17","0","2026-08-22","72962.00","0.00","0","1","16","72962.00","10063.72","1","1","2026-08-22","1","2","0","","0.00","0.00","0.00","8634","1","","2","0","","");
INSERT INTO kp_po VALUES("16","22","0","6","1","2026-08-17","0","2026-08-22","21270.60","0.00","0","1","16","21270.60","2933.88","1","1","2026-08-22","1","2","0","","0.00","0.00","0.00","010","1","","2","0","","");
INSERT INTO kp_po VALUES("17","23","0","7","1","2026-08-17","0","2026-08-22","65304.00","0.00","0","1","16","65304.00","9007.45","1","1","2026-08-22","1","2","0","","0.00","0.00","0.00","2095","1","","2","0","","");
INSERT INTO kp_po VALUES("18","25","0","3","1","2026-08-17","0","2026-08-23","583144.04","0.00","0","1","16","583144.04","80433.66","1","1","2026-08-24","1","2","0","","0.00","0.00","0.00","30051","1","","2","0","","");
INSERT INTO kp_po VALUES("19","26","0","5","1","2026-08-17","0","2026-08-23","36000.00","0.00","0","1","16","36000.00","4965.52","1","1","2026-08-24","1","2","0","","0.00","0.00","0.00","6765","1","","2","0","","");
INSERT INTO kp_po VALUES("20","27","0","7","1","2026-08-17","0","2026-08-23","42662.00","0.00","0","1","16","42662.00","5884.41","1","1","2026-08-24","1","2","0","","0.00","0.00","0.00","2096","1","","2","0","","");
INSERT INTO kp_po VALUES("21","28","0","3","1","2026-08-17","0","2026-08-23","103627.76","0.00","0","1","16","103627.76","14293.48","1","1","2026-08-24","1","2","0","","0.00","0.00","0.00","30074","1","","2","0","","");





CREATE TABLE `kp_po_hist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `qty` decimal(12,3) NOT NULL DEFAULT '0.000',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `trans` varchar(100) NOT NULL DEFAULT '',
  `trans_user` int NOT NULL DEFAULT '0',
  `trans_day` date DEFAULT NULL,
  `trans_time` time DEFAULT NULL,
  `trans_datime` datetime DEFAULT NULL,
  `trans_comment` text,
  PRIMARY KEY (`id`),
  KEY `idx_po_hist_po` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_po_id` (
  `po_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_po_id VALUES("28");





CREATE TABLE `kp_po_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_id` int DEFAULT NULL,
  `po_id` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `disc` decimal(10,2) NOT NULL,
  `total` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '2',
  `rec_qty` decimal(10,2) DEFAULT NULL,
  `rec_price` decimal(10,2) DEFAULT NULL,
  `rec_total` decimal(10,2) DEFAULT NULL,
  `rec_staff` int DEFAULT NULL,
  `rec_user` int NOT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `day` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vatable` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) NOT NULL,
  `tax_id` int NOT NULL,
  `tax_type` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=latin1;

INSERT INTO kp_po_items VALUES("2","","2","296","2.00","10100.00","0.00","20200","1","","","","","0","2","1","2026-08-15","2026-08-15 22:59:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("3","","2","244","2.00","2825.00","0.00","5650","1","","","","","0","2","1","2026-08-15","2026-08-15 23:00:54","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("5","","2","243","2.00","2123.00","0.00","4246","1","","","","","0","2","1","2026-08-15","2026-08-15 23:05:47","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("6","","2","295","2.00","954.00","0.00","1908","1","","","","","0","2","1","2026-08-15","2026-08-15 23:08:30","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("7","","2","29","3.00","2485.00","0.00","7455","1","","","","","0","2","1","2026-08-15","2026-08-15 23:09:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("8","","2","207","2.00","3834.00","0.00","7668","1","","","","","0","2","1","2026-08-15","2026-08-15 23:10:38","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("9","","2","217","2.00","3325.00","0.00","6650","1","","","","","0","2","1","2026-08-15","2026-08-15 23:11:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("10","","2","163","1.00","8458.00","0.00","8458","1","","","","","0","2","1","2026-08-15","2026-08-15 23:11:50","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("11","","2","57","1.00","5232.00","0.00","5232","1","","","","","0","2","1","2026-08-15","2026-08-15 23:15:37","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("12","","2","213","2.00","2377.00","0.00","4754","1","","","","","0","2","1","2026-08-15","2026-08-15 23:16:46","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("13","","2","108","36.00","42.00","0.00","1496","1","","","","","0","2","1","2026-08-15","2026-08-15 23:18:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("14","","2","31","2.00","6913.00","0.00","13825","1","","","","","0","2","1","2026-08-15","2026-08-15 23:18:55","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("15","","2","73","6.00","1064.00","0.00","6384","1","","","","","0","2","1","2026-08-15","2026-08-15 23:19:37","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("16","","2","60","6.00","1064.00","0.00","6384","1","","","","","0","2","1","2026-08-15","2026-08-15 23:20:05","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("17","","2","210","4.00","2605.00","0.00","10420","1","","","","","0","2","1","2026-08-15","2026-08-15 23:20:36","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("18","","2","89","36.00","1279.00","0.00","46044","1","","","","","0","2","1","2026-08-15","2026-08-15 23:24:35","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("19","","2","111","36.00","659.00","0.00","23724","1","","","","","0","2","1","2026-08-15","2026-08-15 23:25:39","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("20","","2","149","12.00","761.00","0.00","9132","1","","","","","0","2","1","2026-08-15","2026-08-15 23:26:18","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("21","","2","148","12.00","761.00","0.00","9132","1","","","","","0","2","1","2026-08-15","2026-08-15 23:28:20","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("22","","2","151","12.00","929.00","0.00","11148","1","","","","","0","2","1","2026-08-15","2026-08-15 23:29:24","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("23","","2","297","12.00","929.00","0.00","11148","1","","","","","0","2","1","2026-08-15","2026-08-15 23:33:32","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("24","","2","154","1.00","1848.00","0.00","1848","1","","","","","0","2","1","2026-08-15","2026-08-15 23:34:10","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("25","","2","117","3.00","2040.00","0.00","6120","1","","","","","0","2","1","2026-08-15","2026-08-15 23:38:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("26","","3","8","50.00","159.00","0.00","7964","1","","","","","0","2","1","2026-08-15","2026-08-15 23:45:20","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("28","","4","126","225.00","169.00","0.00","38025","1","","","","","0","2","1","2026-08-15","2026-08-15 23:55:42","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("29","","4","121","50.00","204.00","0.00","10218","1","","","","","0","2","1","2026-08-15","2026-08-15 23:56:40","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("31","","5","7","250.00","223.00","0.00","55790","1","","","","","0","2","1","2026-08-15","2026-08-15 23:59:57","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("35","","5","136","50.00","158.00","0.00","7916","1","","","","","0","2","1","2026-08-16","2026-08-16 00:07:47","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("36","","5","9","75.00","213.00","0.00","15993","1","","","","","0","2","1","2026-08-16","2026-08-16 00:08:52","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("37","","5","132","50.00","159.00","0.00","7964","1","","","","","0","2","1","2026-08-16","2026-08-16 00:09:46","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("40","","5","127","50.00","159.00","0.00","7964","1","","","","","0","2","1","2026-08-16","2026-08-16 00:13:02","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("41","","5","126","425.00","169.00","13.49","66092","1","","","","","0","2","1","2026-08-16","2026-08-16 00:13:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("44","","5","119","100.00","169.00","13.49","15551","1","","","","","0","2","1","2026-08-16","2026-08-16 00:26:33","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("47","","5","71","525.00","169.00","13.49","81643","1","","","","","0","2","1","2026-08-16","2026-08-16 00:37:24","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("48","","5","123","300.00","187.00","0.00","56004","1","","","","","0","2","1","2026-08-16","2026-08-16 00:38:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("51","","5","86","50.00","187.00","0.00","9334","1","","","","","0","2","1","2026-08-16","2026-08-16 00:47:46","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("54","","5","179","12.00","896.00","0.00","10752","1","","","","","0","2","1","2026-08-16","2026-08-16 00:51:30","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("55","","5","293","20.00","256.00","0.00","5120","1","","","","","0","2","1","2026-08-16","2026-08-16 00:52:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("56","","5","99","24.00","576.00","0.00","13824","1","","","","","0","2","1","2026-08-16","2026-08-16 00:53:02","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("57","","5","173","2.00","6320.00","0.00","12640","1","","","","","0","2","1","2026-08-16","2026-08-16 00:55:57","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("58","","5","241","11.00","1240.00","0.00","13640","1","","","","","0","2","1","2026-08-16","2026-08-16 00:57:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("59","","5","40","12.00","1400.00","0.00","16800","1","","","","","0","2","1","2026-08-16","2026-08-16 00:58:19","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("60","","5","120","750.00","195.52","15.60","134940","1","","","","","0","2","1","2026-08-16","2026-08-16 01:10:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("61","","5","121","550.00","204.36","16.31","103428","1","","","","","0","2","1","2026-08-16","2026-08-16 01:13:38","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("62","","5","124","150.00","222.08","17.72","30654","1","","","","","0","2","1","2026-08-16","2026-08-16 01:17:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("63","","5","16","48.00","216.71","0.00","10402","1","","","","","0","2","1","2026-08-16","2026-08-16 01:20:21","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("64","","5","15","48.00","241.88","0.00","11610","1","","","","","0","2","1","2026-08-16","2026-08-16 01:22:12","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("65","","5","14","96.00","242.58","0.00","23288","1","","","","","0","2","1","2026-08-16","2026-08-16 01:24:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("66","","5","10","24.00","216.71","0.00","5201","1","","","","","0","2","1","2026-08-16","2026-08-16 01:25:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("67","","5","19","48.00","175.96","0.00","8446","1","","","","","0","2","1","2026-08-16","2026-08-16 01:26:18","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("68","","5","141","96.00","175.96","0.00","16892","1","","","","","0","2","1","2026-08-16","2026-08-16 01:27:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("69","","5","142","24.00","240.30","0.00","5767","1","","","","","0","2","1","2026-08-16","2026-08-16 01:28:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("70","","5","6","24.00","192.63","0.00","4623","1","","","","","0","2","1","2026-08-16","2026-08-16 01:29:07","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("71","","5","70","72.00","175.96","0.00","12669","1","","","","","0","2","1","2026-08-16","2026-08-16 01:30:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("72","","5","12","24.00","199.08","0.00","4778","1","","","","","0","2","1","2026-08-16","2026-08-16 01:31:41","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("73","","6","144","720.00","50.00","0.00","36000","1","","","","","0","2","1","2026-08-16","2026-08-16 16:35:11","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("74","","7","22","264.00","32.50","0.00","8580","2","","","","","0","2","2","2026-08-16","2026-08-16 16:40:02","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("75","","7","76","12.00","133.33","0.00","1600","2","","","","","0","2","2","2026-08-16","2026-08-16 16:40:54","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("76","","9","22","264.00","32.50","0.00","8580","1","","","","","0","2","1","2026-08-17","2026-08-17 15:00:44","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("77","","9","299","12.00","96.25","0.00","1155","1","","","","","0","2","1","2026-08-17","2026-08-17 15:01:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("78","","9","76","12.00","133.33","0.00","1600","1","","","","","0","2","1","2026-08-17","2026-08-17 15:02:21","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("79","","11","85","24.00","672.00","0.00","16128","1","","","","","0","2","1","2026-08-17","2026-08-17 15:59:31","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("80","","11","7","125.00","223.16","0.00","27895","1","","","","","0","2","1","2026-08-17","2026-08-17 16:01:06","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("81","","11","142","48.00","240.30","0.00","11534","1","","","","","0","2","1","2026-08-17","2026-08-17 16:02:15","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("82","","11","115","24.00","175.00","0.00","4200","1","","","","","0","2","1","2026-08-17","2026-08-17 16:03:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("83","","11","8","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-17","2026-08-17 16:04:51","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("84","","11","141","120.00","175.96","0.00","21115","1","","","","","0","2","1","2026-08-17","2026-08-17 16:05:52","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("85","","11","9","25.00","213.24","0.00","5331","1","","","","","0","2","1","2026-08-17","2026-08-17 16:06:40","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("86","","11","132","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-17","2026-08-17 16:07:39","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("87","","11","70","48.00","175.96","0.00","8446","1","","","","","0","2","1","2026-08-17","2026-08-17 16:08:17","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("88","","11","127","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-17","2026-08-17 16:09:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("89","","11","139","24.00","175.96","0.00","4223","1","","","","","0","2","1","2026-08-17","2026-08-17 16:10:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("90","","11","126","400.00","169.00","0.00","67600","1","","","","","0","2","1","2026-08-17","2026-08-17 16:14:16","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("91","","11","121","250.00","204.36","0.00","51090","1","","","","","0","2","1","2026-08-17","2026-08-17 16:15:10","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("92","","11","119","100.00","169.08","0.00","16908","1","","","","","0","2","1","2026-08-17","2026-08-17 16:15:45","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("93","","11","124","125.00","222.08","0.00","27760","1","","","","","0","2","1","2026-08-17","2026-08-17 16:17:36","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("94","","11","71","275.00","169.00","0.00","46475","1","","","","","0","2","1","2026-08-17","2026-08-17 16:18:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("95","","11","123","175.00","186.68","0.00","32669","1","","","","","0","2","1","2026-08-17","2026-08-17 16:19:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("96","","11","86","50.00","186.68","0.00","9334","1","","","","","0","2","1","2026-08-17","2026-08-17 16:21:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("97","","11","120","750.00","195.52","0.00","146640","1","","","","","0","2","1","2026-08-17","2026-08-17 16:24:01","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("98","","11","300","12.00","960.00","0.00","11520","1","","","","","0","2","1","2026-08-17","2026-08-17 16:24:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("99","","11","2","12.00","3911.08","0.00","46933","1","","","","","0","2","1","2026-08-17","2026-08-17 16:28:21","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("100","","11","188","12.00","853.33","0.00","10240","1","","","","","0","2","1","2026-08-17","2026-08-17 16:29:42","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("101","","11","305","12.00","1799.08","0.00","21589","1","","","","","0","2","1","2026-08-17","2026-08-17 16:30:19","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("102","","11","171","12.00","352.00","0.00","4224","1","","","","","0","2","1","2026-08-17","2026-08-17 16:31:25","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("103","","11","87","12.00","672.00","0.00","8064","1","","","","","0","2","1","2026-08-17","2026-08-17 16:32:46","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("104","","11","157","24.00","416.00","0.00","9984","1","","","","","0","2","1","2026-08-17","2026-08-17 16:33:32","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("105","","11","173","6.00","6320.00","0.00","37920","1","","","","","0","2","1","2026-08-17","2026-08-17 16:35:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("106","","11","242","18.00","576.00","0.00","10368","1","","","","","0","2","1","2026-08-17","2026-08-17 16:36:34","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("107","","12","239","3.00","2376.00","0.00","7128","2","","","","","0","2","2","2026-08-17","2026-08-17 16:40:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("108","","13","239","3.00","2376.00","0.00","7128","1","","","","","0","2","1","2026-08-17","2026-08-17 16:51:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("109","","13","225","2.00","3450.00","0.00","6900","1","","","","","0","2","1","2026-08-17","2026-08-17 16:52:07","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("110","","13","296","3.00","10100.00","0.00","30300","1","","","","","0","2","1","2026-08-17","2026-08-17 16:52:40","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("111","","13","82","3.00","3597.00","0.00","10791","1","","","","","0","2","1","2026-08-17","2026-08-17 16:53:16","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("112","","13","32","2.00","4991.00","0.00","9982","1","","","","","0","2","1","2026-08-17","2026-08-17 16:55:48","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("113","","13","59","24.00","195.00","0.00","4680","1","","","","","0","2","1","2026-08-17","2026-08-17 16:56:16","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("114","","13","89","12.00","1279.00","0.00","15348","1","","","","","0","2","1","2026-08-17","2026-08-17 16:57:47","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("115","","13","111","12.00","659.36","0.00","7912","1","","","","","0","2","1","2026-08-17","2026-08-17 16:58:29","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("116","","13","216","2.00","1328.00","0.00","2656","1","","","","","0","2","1","2026-08-17","2026-08-17 16:59:03","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("117","","13","27","6.00","3228.13","0.00","19369","1","","","","","0","2","1","2026-08-17","2026-08-17 16:59:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("118","","13","95","6.00","2507.00","0.00","15042","1","","","","","0","2","1","2026-08-17","2026-08-17 17:00:43","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("119","","13","33","4.00","1980.00","0.00","7920","1","","","","","0","2","1","2026-08-17","2026-08-17 17:01:16","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("120","","13","215","2.00","1650.00","0.00","3300","1","","","","","0","2","1","2026-08-17","2026-08-17 17:01:55","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("121","","13","72","6.00","930.00","0.00","5580","1","","","","","0","2","1","2026-08-17","2026-08-17 17:03:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("123","","13","96","1.00","4609.00","0.00","4609","1","","","","","0","2","1","2026-08-17","2026-08-17 17:04:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("124","","13","74","6.00","1579.00","0.00","9474","1","","","","","0","2","1","2026-08-17","2026-08-17 17:05:01","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("125","","13","201","6.00","1243.00","0.00","7458","1","","","","","0","2","1","2026-08-17","2026-08-17 17:05:35","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("126","","13","114","20.00","350.00","0.00","7000","1","","","","","0","2","1","2026-08-17","2026-08-17 17:06:01","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("127","","13","162","3.00","9965.00","0.00","29895","1","","","","","0","2","1","2026-08-17","2026-08-17 17:07:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("128","","15","292","48.00","253.00","0.00","12144","1","","","","","0","2","1","2026-08-17","2026-08-17 17:19:57","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("129","","15","69","72.00","245.00","0.00","17640","1","","","","","0","2","1","2026-08-17","2026-08-17 17:20:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("130","","15","56","48.00","285.00","0.00","13680","1","","","","","0","2","1","2026-08-17","2026-08-17 17:21:55","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("131","","15","55","48.00","230.00","0.00","11040","1","","","","","0","2","1","2026-08-17","2026-08-17 17:22:42","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("132","","15","128","24.00","206.00","0.00","4944","1","","","","","0","2","1","2026-08-17","2026-08-17 17:23:17","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("133","","15","133","48.00","206.00","0.00","9888","1","","","","","0","2","1","2026-08-17","2026-08-17 17:24:09","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("134","","15","130","48.00","245.00","0.00","11760","1","","","","","0","2","1","2026-08-17","2026-08-17 17:25:09","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("135","","15","25","144.00","248.00","0.00","35712","1","","","","","0","2","1","2026-08-17","2026-08-17 17:26:58","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("136","","16","128","48.00","206.00","0.00","9888","1","","","","","0","2","1","2026-08-17","2026-08-17 17:28:58","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("137","","16","69","24.00","245.00","0.00","5880","1","","","","","0","2","1","2026-08-17","2026-08-17 17:29:39","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("138","","16","292","24.00","253.00","0.00","6072","1","","","","","0","2","1","2026-08-17","2026-08-17 17:31:46","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("139","","16","55","48.00","230.00","0.00","11040","1","","","","","0","2","1","2026-08-17","2026-08-17 17:32:43","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("140","","17","144","720.00","50.00","0.00","36000","1","","","","","0","2","1","2026-08-17","2026-08-17 17:34:50","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("141","","18","5","24.00","192.63","0.00","4623","1","","","","","0","2","1","2026-08-19","2026-08-19 13:29:57","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("142","","18","6","24.00","192.63","0.00","4623","1","","","","","0","2","1","2026-08-19","2026-08-19 13:32:34","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("143","","18","85","12.00","672.00","0.00","8064","1","","","","","0","2","1","2026-08-19","2026-08-19 13:33:17","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("144","","18","7","300.00","223.16","0.00","66948","1","","","","","0","2","1","2026-08-19","2026-08-19 13:35:53","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("145","","18","142","48.00","240.30","0.00","11534","1","","","","","0","2","1","2026-08-19","2026-08-19 13:37:34","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("146","","18","136","25.00","158.32","0.00","3958","1","","","","","0","2","1","2026-08-19","2026-08-19 13:38:20","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("147","","18","8","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-19","2026-08-19 13:39:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("148","","18","141","144.00","175.96","0.00","25338","1","","","","","0","2","1","2026-08-19","2026-08-19 13:39:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("149","","18","98","24.00","760.00","0.00","18240","1","","","","","0","2","1","2026-08-19","2026-08-19 13:41:41","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("150","","18","132","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-19","2026-08-19 13:44:25","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("151","","18","19","24.00","175.96","0.00","4223","1","","","","","0","2","1","2026-08-19","2026-08-19 13:46:14","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("152","","18","70","96.00","175.96","0.00","16892","1","","","","","0","2","1","2026-08-19","2026-08-19 13:46:54","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("153","","18","126","575.00","169.00","0.00","97175","1","","","","","0","2","1","2026-08-19","2026-08-19 13:47:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("154","","18","121","350.00","204.36","0.00","71526","1","","","","","0","2","1","2026-08-19","2026-08-19 13:50:24","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("155","","18","10","24.00","216.71","0.00","5201","1","","","","","0","2","1","2026-08-19","2026-08-19 13:51:30","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("156","","18","119","75.00","169.08","0.00","12681","1","","","","","0","2","1","2026-08-19","2026-08-19 13:53:36","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("157","","18","124","175.00","222.08","0.00","38864","1","","","","","0","2","1","2026-08-19","2026-08-19 13:54:40","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("158","","18","71","425.00","169.00","0.00","71825","1","","","","","0","2","1","2026-08-19","2026-08-19 13:56:15","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("159","","18","123","275.00","186.68","0.00","51337","1","","","","","0","2","1","2026-08-19","2026-08-19 13:56:59","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("160","","18","14","72.00","242.58","0.00","17466","1","","","","","0","2","1","2026-08-19","2026-08-19 13:57:39","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("161","","18","15","48.00","241.88","0.00","11610","1","","","","","0","2","1","2026-08-19","2026-08-19 13:58:32","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("162","","18","86","50.00","186.68","0.00","9334","1","","","","","0","2","1","2026-08-19","2026-08-19 13:59:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("163","","18","120","750.00","195.52","0.00","146640","1","","","","","0","2","1","2026-08-19","2026-08-19 14:01:48","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("164","","18","16","24.00","216.71","0.00","5201","1","","","","","0","2","1","2026-08-19","2026-08-19 14:02:33","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("165","","18","18","2.00","2280.00","0.00","4560","1","","","","","0","2","1","2026-08-19","2026-08-19 14:03:16","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("166","","19","320","3.00","738.64","0.00","2216","1","","","","","0","2","1","2026-08-19","2026-08-19 14:27:58","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("167","","19","196","6.00","1825.20","0.00","10951","1","","","","","0","2","1","2026-08-19","2026-08-19 14:28:41","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("168","","19","32","3.00","4990.74","0.00","14972","1","","","","","0","2","1","2026-08-19","2026-08-19 14:29:29","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("169","","19","29","3.00","2484.00","0.00","7452","1","","","","","0","2","1","2026-08-19","2026-08-19 14:30:37","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("170","","19","36","6.00","2779.50","0.00","16677","1","","","","","0","2","1","2026-08-19","2026-08-19 14:31:45","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("171","","19","215","2.00","1417.00","0.00","2834","1","","","","","0","2","1","2026-08-19","2026-08-19 14:32:15","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("172","","19","111","48.00","659.36","0.00","31649","1","","","","","0","2","1","2026-08-19","2026-08-19 14:34:05","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("173","","20","22","240.00","32.50","0.00","7800","1","","","","","0","2","1","2026-08-19","2026-08-19 14:43:19","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("174","","20","104","24.00","200.00","0.00","4800","1","","","","","0","2","1","2026-08-19","2026-08-19 14:45:43","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("176","","20","76","12.00","133.33","0.00","1600","1","","","","","0","2","1","2026-08-19","2026-08-19 14:47:59","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("177","","21","89","24.00","639.50","0.00","15348","1","","","","","0","2","1","2026-08-22","2026-08-22 22:03:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("178","","21","111","24.00","659.00","0.00","15816","1","","","","","0","2","1","2026-08-22","2026-08-22 22:04:12","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("179","","21","34","2.00","1750.00","0.00","3500","1","","","","","0","2","1","2026-08-22","2026-08-22 22:05:03","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("180","","21","303","12.00","448.00","0.00","5376","1","","","","","0","2","1","2026-08-22","2026-08-22 22:06:05","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("181","","21","156","6.00","2041.00","0.00","12246","1","","","","","0","2","1","2026-08-22","2026-08-22 22:06:39","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("182","","21","233","3.00","4930.00","0.00","14790","1","","","","","0","2","1","2026-08-22","2026-08-22 22:07:15","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("183","","21","161","2.00","2943.00","0.00","5886","1","","","","","0","2","1","2026-08-22","2026-08-22 22:08:07","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("184","","22","23","180.00","79.17","0.00","14251","1","","","","","0","2","1","2026-08-22","2026-08-22 22:10:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("185","","22","22","216.00","32.50","0.00","7020","1","","","","","0","2","1","2026-08-22","2026-08-22 22:11:18","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("186","","23","56","48.00","285.00","0.00","13680","1","","","","","0","2","1","2026-08-22","2026-08-22 22:13:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("187","","23","55","48.00","230.00","0.00","11040","1","","","","","0","2","1","2026-08-22","2026-08-22 22:18:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("188","","23","129","24.00","206.00","0.00","4944","1","","","","","0","2","1","2026-08-22","2026-08-22 22:18:55","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("189","","23","130","24.00","245.00","0.00","5880","1","","","","","0","2","1","2026-08-22","2026-08-22 22:19:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("190","","23","25","120.00","248.00","0.00","29760","1","","","","","0","2","1","2026-08-22","2026-08-22 22:19:44","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("191","","24","85","12.00","672.00","0.00","8064","2","","","","","0","2","2","2026-08-22","2026-08-22 22:23:19","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("192","","24","7","225.00","223.16","0.00","50211","2","","","","","0","2","2","2026-08-22","2026-08-22 22:24:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("193","","24","136","25.00","158.32","0.00","3958","2","","","","","0","2","2","2026-08-22","2026-08-22 22:25:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("194","","24","8","50.00","159.28","0.00","7964","2","","","","","0","2","2","2026-08-22","2026-08-22 22:25:44","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("195","","24","141","48.00","175.96","0.00","8446","2","","","","","0","2","2","2026-08-22","2026-08-22 22:38:44","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("196","","24","98","24.00","760.00","0.00","18240","2","","","","","0","2","2","2026-08-22","2026-08-22 22:39:45","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("197","","24","132","75.00","159.28","0.00","11946","2","","","","","0","2","2","2026-08-22","2026-08-22 22:42:21","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("198","","24","70","48.00","175.96","0.00","8446","2","","","","","0","2","2","2026-08-22","2026-08-22 22:43:08","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("199","","24","127","50.00","159.28","0.00","7964","2","","","","","0","2","2","2026-08-22","2026-08-22 22:43:57","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("200","","24","126","400.00","169.00","0.00","67600","2","","","","","0","2","2","2026-08-22","2026-08-22 22:44:44","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("201","","24","121","300.00","204.36","0.00","61308","2","","","","","0","2","2","2026-08-22","2026-08-22 22:45:12","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("202","","24","119","25.00","169.08","0.00","4227","2","","","","","0","2","2","2026-08-22","2026-08-22 22:45:32","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("203","","24","124","150.00","222.08","0.00","33312","2","","","","","0","2","2","2026-08-22","2026-08-22 22:46:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("204","","24","71","325.00","169.00","0.00","54925","2","","","","","0","2","2","2026-08-22","2026-08-22 22:47:18","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("205","","24","123","125.00","186.68","0.00","23335","2","","","","","0","2","2","2026-08-22","2026-08-22 22:48:06","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("206","","24","14","48.00","242.58","0.00","11644","2","","","","","0","2","2","2026-08-22","2026-08-22 22:48:59","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("207","","24","86","75.00","186.68","0.00","14001","2","","","","","0","2","2","2026-08-22","2026-08-22 22:50:35","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("208","","25","85","12.00","672.00","0.00","8064","1","","","","","0","2","1","2026-08-24","2026-08-24 00:17:19","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("209","","25","7","225.00","223.16","0.00","50211","1","","","","","0","2","1","2026-08-24","2026-08-24 00:17:59","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("210","","25","136","25.00","158.32","0.00","3958","1","","","","","0","2","1","2026-08-24","2026-08-24 00:18:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("211","","25","8","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-24","2026-08-24 00:18:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("212","","25","141","48.00","175.96","0.00","8446","1","","","","","0","2","1","2026-08-24","2026-08-24 00:19:26","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("213","","25","98","24.00","760.00","0.00","18240","1","","","","","0","2","1","2026-08-24","2026-08-24 00:19:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("214","","25","132","75.00","159.28","0.00","11946","1","","","","","0","2","1","2026-08-24","2026-08-24 00:20:31","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("215","","25","70","48.00","175.96","0.00","8446","1","","","","","0","2","1","2026-08-24","2026-08-24 00:21:03","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("216","","25","127","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-24","2026-08-24 00:21:28","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("217","","25","126","400.00","169.00","0.00","67600","1","","","","","0","2","1","2026-08-24","2026-08-24 00:21:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("218","","25","121","300.00","204.36","0.00","61308","1","","","","","0","2","1","2026-08-24","2026-08-24 00:22:22","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("219","","25","119","25.00","169.08","0.00","4227","1","","","","","0","2","1","2026-08-24","2026-08-24 00:22:50","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("220","","25","124","150.00","222.08","0.00","33312","1","","","","","0","2","1","2026-08-24","2026-08-24 00:23:12","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("221","","25","71","325.00","169.00","0.00","54925","1","","","","","0","2","1","2026-08-24","2026-08-24 00:23:47","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("222","","25","123","125.00","186.68","0.00","23335","1","","","","","0","2","1","2026-08-24","2026-08-24 00:24:17","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("223","","25","14","48.00","242.58","0.00","11644","1","","","","","0","2","1","2026-08-24","2026-08-24 00:24:51","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("224","","25","86","75.00","186.68","0.00","14001","1","","","","","0","2","1","2026-08-24","2026-08-24 00:25:20","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("225","","25","120","750.00","195.52","0.00","146640","1","","","","","0","2","1","2026-08-24","2026-08-24 00:25:56","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("226","","25","16","24.00","216.71","0.00","5201","1","","","","","0","2","1","2026-08-24","2026-08-24 00:26:33","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("227","","25","37","48.00","576.00","0.00","27648","1","","","","","0","2","1","2026-08-24","2026-08-24 00:27:07","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("228","","25","87","12.00","672.00","0.00","8064","1","","","","","0","2","1","2026-08-24","2026-08-24 00:27:48","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("229","","26","144","720.00","50.00","0.00","36000","1","","","","","0","2","1","2026-08-24","2026-08-24 00:30:11","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("230","","27","105","24.00","230.00","0.00","5520","1","","","","","0","2","1","2026-08-24","2026-08-24 00:31:23","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("231","","27","69","72.00","245.00","0.00","17640","1","","","","","0","2","1","2026-08-24","2026-08-24 00:31:49","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("232","","27","292","38.00","253.00","0.00","9614","1","","","","","0","2","1","2026-08-24","2026-08-24 00:33:04","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("233","","27","128","48.00","206.00","0.00","9888","1","","","","","0","2","1","2026-08-24","2026-08-24 00:33:35","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("234","","28","119","100.00","169.08","0.00","16908","1","","","","","0","2","1","2026-08-24","2026-08-24 00:34:48","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("235","","28","136","25.00","158.32","0.00","3958","1","","","","","0","2","1","2026-08-24","2026-08-24 00:35:13","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("236","","28","9","50.00","213.24","0.00","10662","1","","","","","0","2","1","2026-08-24","2026-08-24 00:35:51","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("237","","28","127","50.00","159.28","0.00","7964","1","","","","","0","2","1","2026-08-24","2026-08-24 00:36:32","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("238","","28","123","175.00","186.68","0.00","32669","1","","","","","0","2","1","2026-08-24","2026-08-24 00:37:00","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("239","","28","14","72.00","242.58","0.00","17466","1","","","","","0","2","1","2026-08-24","2026-08-24 00:37:31","0.00","0.00","0","0");
INSERT INTO kp_po_items VALUES("240","","28","86","75.00","186.68","0.00","14001","1","","","","","0","2","1","2026-08-24","2026-08-24 00:37:58","0.00","0.00","0","0");





CREATE TABLE `kp_po_status` (
  `po_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `po_status` int NOT NULL,
  `sup_id` int NOT NULL,
  `day` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent` int NOT NULL DEFAULT '0',
  `po_user_id` int NOT NULL DEFAULT '0',
  `potype` tinyint NOT NULL DEFAULT '2',
  `poby` int NOT NULL DEFAULT '5',
  `po_day` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_po_status VALUES("1","0","2","0","3","0000-00-00","2026-08-15 00:28:18","0","1","2","5","2026-08-14");
INSERT INTO kp_po_status VALUES("7","0","2","0","6","0000-00-00","2026-08-16 16:38:36","0","1","2","5","2026-08-16");
INSERT INTO kp_po_status VALUES("8","0","2","0","5","0000-00-00","2026-08-17 14:58:47","0","1","2","5","2026-08-17");
INSERT INTO kp_po_status VALUES("10","0","2","0","6","0000-00-00","2026-08-17 15:04:03","0","1","2","5","2026-08-17");
INSERT INTO kp_po_status VALUES("12","0","2","0","4","0000-00-00","2026-08-17 16:39:01","0","1","2","5","2026-08-17");
INSERT INTO kp_po_status VALUES("14","0","2","0","4","0000-00-00","2026-08-17 17:08:44","0","1","2","5","2026-08-17");
INSERT INTO kp_po_status VALUES("24","0","2","0","3","0000-00-00","2026-08-22 22:22:17","0","1","2","5","2026-08-22");





CREATE TABLE `kp_print_printers` (
  `prn_id` int unsigned NOT NULL AUTO_INCREMENT,
  `windows_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `role` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'counter_bill_bot',
  `cntr_id` int NOT NULL DEFAULT '0',
  `sort_order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prn_id`),
  KEY `idx_role` (`role`),
  KEY `idx_cntr` (`cntr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_print_printers VALUES("3","BAR","","counter_bill_bot","1","0","2026-06-05 16:57:44");
INSERT INTO kp_print_printers VALUES("4","CASHIER","","cashier_order","1","0","2026-06-05 16:59:02");
INSERT INTO kp_print_printers VALUES("5","KITCHEN","","kitchen_main","0","0","2026-06-05 17:22:48");





CREATE TABLE `kp_print_queue` (
  `queue_id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL DEFAULT '0',
  `ref_id` int NOT NULL DEFAULT '0',
  `print_type` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bill',
  `label` varchar(120) NOT NULL DEFAULT '',
  `cntr_id` int NOT NULL DEFAULT '0',
  `route_path` varchar(255) NOT NULL DEFAULT '',
  `route_query` text,
  `requested_by` int NOT NULL DEFAULT '0',
  `reprint_of` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `print_attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `last_error` varchar(255) NOT NULL DEFAULT '',
  `printed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `idx_cntr_status` (`cntr_id`,`status`),
  KEY `idx_type_ref` (`print_type`,`ref_id`),
  KEY `idx_reprint` (`reprint_of`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_print_queue VALUES("1","0","1","variance","VARIANCE","1","render_variance.php","cntr_id=1&shift=2026-08-24","4","0","1","2026-08-24 16:18:29","0","","2026-08-24 16:18:55");
INSERT INTO kp_print_queue VALUES("2","1","1","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=1","4","0","0","2026-08-24 16:20:15","0","","");
INSERT INTO kp_print_queue VALUES("3","1","1","bot","BOT","1","render_bot.php","sale_id=1","4","0","1","2026-08-24 16:20:15","0","","2026-08-24 16:20:17");
INSERT INTO kp_print_queue VALUES("4","1","1","bill","BILL","1","render_bill.php","sale_id=1","4","0","1","2026-08-24 16:20:15","0","","2026-08-24 16:20:20");
INSERT INTO kp_print_queue VALUES("5","3","3","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=3","3","0","0","2026-08-24 17:09:26","0","","");
INSERT INTO kp_print_queue VALUES("6","3","3","bot","BOT","1","render_bot.php","sale_id=3","3","0","1","2026-08-24 17:09:26","0","","2026-08-24 17:09:30");
INSERT INTO kp_print_queue VALUES("7","3","3","bill","BILL","1","render_bill.php","sale_id=3","3","0","1","2026-08-24 17:09:26","0","","2026-08-24 17:09:31");
INSERT INTO kp_print_queue VALUES("8","0","404","gwitia","REQUISITION","1","render_req.php","id=404&user_id=4","4","0","1","2026-08-24 18:50:15","0","","2026-08-24 18:50:31");
INSERT INTO kp_print_queue VALUES("9","0","412","gwitia","REQUISITION","1","render_req.php","id=412&user_id=4","4","0","1","2026-08-24 19:03:55","0","","2026-08-24 19:04:02");
INSERT INTO kp_print_queue VALUES("10","4","4","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=4","6","0","0","2026-08-24 19:19:35","0","","");
INSERT INTO kp_print_queue VALUES("11","4","4","bot","BOT","1","render_bot.php","sale_id=4","6","0","1","2026-08-24 19:19:35","0","","2026-08-24 19:19:44");
INSERT INTO kp_print_queue VALUES("12","4","4","bill","BILL","1","render_bill.php","sale_id=4","6","0","1","2026-08-24 19:19:35","0","","2026-08-24 19:19:47");
INSERT INTO kp_print_queue VALUES("13","7","7","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=7","9","0","0","2026-08-24 19:51:36","0","","");
INSERT INTO kp_print_queue VALUES("14","7","7","bot","BOT","1","render_bot.php","sale_id=7","9","0","1","2026-08-24 19:51:37","0","","2026-08-24 19:51:50");
INSERT INTO kp_print_queue VALUES("15","7","7","bill","BILL","1","render_bill.php","sale_id=7","9","0","1","2026-08-24 19:51:37","0","","2026-08-24 19:51:54");
INSERT INTO kp_print_queue VALUES("16","6","6","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=6","8","0","0","2026-08-24 20:00:45","0","","");
INSERT INTO kp_print_queue VALUES("17","6","6","bot","BOT","1","render_bot.php","sale_id=6","8","0","1","2026-08-24 20:00:45","0","","2026-08-24 20:01:00");
INSERT INTO kp_print_queue VALUES("18","6","6","bill","BILL","1","render_bill.php","sale_id=6","8","0","1","2026-08-24 20:00:46","0","","2026-08-24 20:01:05");
INSERT INTO kp_print_queue VALUES("19","8","8","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=8","3","0","0","2026-08-24 21:17:31","0","","");
INSERT INTO kp_print_queue VALUES("20","8","8","bot","BOT","1","render_bot.php","sale_id=8","3","0","1","2026-08-24 21:17:31","0","","2026-08-24 21:39:04");
INSERT INTO kp_print_queue VALUES("21","8","8","bill","BILL","1","render_bill.php","sale_id=8","3","0","1","2026-08-24 21:17:32","0","","2026-08-24 21:39:07");
INSERT INTO kp_print_queue VALUES("22","9","9","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=9","6","0","0","2026-08-24 21:21:11","0","","");
INSERT INTO kp_print_queue VALUES("23","9","9","bot","BOT","1","render_bot.php","sale_id=9","6","0","1","2026-08-24 21:21:12","0","","2026-08-24 21:39:09");
INSERT INTO kp_print_queue VALUES("24","9","9","bill","BILL","1","render_bill.php","sale_id=9","6","0","1","2026-08-24 21:21:12","0","","2026-08-24 21:39:11");
INSERT INTO kp_print_queue VALUES("25","10","10","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=10","6","0","0","2026-08-24 21:21:50","0","","");
INSERT INTO kp_print_queue VALUES("26","10","10","bot","BOT","1","render_bot.php","sale_id=10","6","0","1","2026-08-24 21:21:50","0","","2026-08-24 21:39:12");
INSERT INTO kp_print_queue VALUES("27","10","10","bill","BILL","1","render_bill.php","sale_id=10","6","0","1","2026-08-24 21:21:50","0","","2026-08-24 21:39:14");
INSERT INTO kp_print_queue VALUES("28","11","11","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=11","11","0","0","2026-08-24 21:29:08","0","","");
INSERT INTO kp_print_queue VALUES("29","11","11","bot","BOT","1","render_bot.php","sale_id=11","11","0","1","2026-08-24 21:29:08","0","","2026-08-24 21:39:15");
INSERT INTO kp_print_queue VALUES("30","11","11","bill","BILL","1","render_bill.php","sale_id=11","11","0","1","2026-08-24 21:29:09","0","","2026-08-24 21:39:17");
INSERT INTO kp_print_queue VALUES("31","12","12","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=12","9","0","0","2026-08-24 21:53:14","0","","");
INSERT INTO kp_print_queue VALUES("32","12","12","bot","BOT","1","render_bot.php","sale_id=12","9","0","1","2026-08-24 21:53:15","0","","2026-08-24 21:53:24");
INSERT INTO kp_print_queue VALUES("33","12","12","bill","BILL","1","render_bill.php","sale_id=12","9","0","1","2026-08-24 21:53:15","0","","2026-08-24 21:53:27");
INSERT INTO kp_print_queue VALUES("34","13","13","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=13","8","0","0","2026-08-24 21:55:29","0","","");
INSERT INTO kp_print_queue VALUES("35","13","13","bot","BOT","1","render_bot.php","sale_id=13","8","0","1","2026-08-24 21:55:29","0","","2026-08-24 21:55:45");
INSERT INTO kp_print_queue VALUES("36","13","13","bill","BILL","1","render_bill.php","sale_id=13","8","0","1","2026-08-24 21:55:30","0","","2026-08-24 21:55:47");
INSERT INTO kp_print_queue VALUES("37","14","14","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=14","8","0","0","2026-08-24 21:56:51","0","","");
INSERT INTO kp_print_queue VALUES("38","14","14","bot","BOT","1","render_bot.php","sale_id=14","8","0","1","2026-08-24 21:56:51","0","","2026-08-24 21:57:07");
INSERT INTO kp_print_queue VALUES("39","14","14","bill","BILL","1","render_bill.php","sale_id=14","8","0","1","2026-08-24 21:56:52","0","","2026-08-24 21:57:09");
INSERT INTO kp_print_queue VALUES("40","15","15","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=15","3","0","0","2026-08-24 21:58:15","0","","");
INSERT INTO kp_print_queue VALUES("41","15","15","bot","BOT","1","render_bot.php","sale_id=15","3","0","1","2026-08-24 21:58:15","0","","2026-08-24 21:58:32");
INSERT INTO kp_print_queue VALUES("42","15","15","bill","BILL","1","render_bill.php","sale_id=15","3","0","1","2026-08-24 21:58:16","0","","2026-08-24 21:58:34");
INSERT INTO kp_print_queue VALUES("43","16","16","cashier_order","CASHIER ORDER","1","render_cashier_order.php","sale_id=16","8","0","0","2026-08-24 21:59:55","0","","");
INSERT INTO kp_print_queue VALUES("44","16","16","bot","BOT","1","render_bot.php","sale_id=16","8","0","1","2026-08-24 21:59:55","0","","2026-08-24 22:00:22");
INSERT INTO kp_print_queue VALUES("45","16","16","bill","BILL","1","render_bill.php","sale_id=16","8","0","1","2026-08-24 21:59:55","0","","2026-08-24 22:00:26");
INSERT INTO kp_print_queue VALUES("46","18","18","bot","BOT","1","render_bot.php","sale_id=18","3","0","1","2026-08-24 23:12:08","0","","2026-08-24 23:12:33");
INSERT INTO kp_print_queue VALUES("47","18","18","bill","BILL","1","render_bill.php","sale_id=18","3","0","1","2026-08-24 23:12:08","0","","2026-08-24 23:12:37");
INSERT INTO kp_print_queue VALUES("48","19","19","bot","BOT","1","render_bot.php","sale_id=19","3","0","1","2026-08-24 23:13:18","0","","2026-08-24 23:13:38");
INSERT INTO kp_print_queue VALUES("49","19","19","bill","BILL","1","render_bill.php","sale_id=19","3","0","1","2026-08-24 23:13:18","0","","2026-08-24 23:13:41");
INSERT INTO kp_print_queue VALUES("50","17","17","bot","BOT","1","render_bot.php","sale_id=17","6","0","1","2026-08-24 23:17:56","0","","2026-08-24 23:18:16");
INSERT INTO kp_print_queue VALUES("51","17","17","bill","BILL","1","render_bill.php","sale_id=17","6","0","1","2026-08-24 23:17:56","0","","2026-08-24 23:18:20");
INSERT INTO kp_print_queue VALUES("52","0","3","counter_sum","COUNTER SUMMARY","1","render_counter_sum.php","summary_id=3&cntr_id=1&shift=2026-08-24","4","0","0","2026-08-25 10:52:28","0","","");





CREATE TABLE `kp_print_reprints` (
  `reprint_id` int NOT NULL AUTO_INCREMENT,
  `queue_id` int NOT NULL DEFAULT '0',
  `source_queue_id` int NOT NULL DEFAULT '0',
  `print_type` varchar(55) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ref_id` int NOT NULL DEFAULT '0',
  `cntr_id` int NOT NULL DEFAULT '0',
  `requested_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reprint_id`),
  KEY `idx_queue` (`queue_id`),
  KEY `idx_source_queue` (`source_queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `kp_print_ticket_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `batch_id` int NOT NULL DEFAULT '0',
  `sale_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `attr_id` int NOT NULL DEFAULT '0',
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `dpt` int NOT NULL DEFAULT '0',
  `counter` int NOT NULL DEFAULT '0',
  `stock_counter` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batch_sale` (`batch_id`,`sale_id`),
  KEY `idx_batch_dpt` (`batch_id`,`dpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `kp_profile_features` (
  `feature_id` int NOT NULL AUTO_INCREMENT,
  `profile_id` int NOT NULL,
  `feature_key` varchar(60) NOT NULL,
  `feature_value` varchar(255) NOT NULL DEFAULT '1',
  PRIMARY KEY (`feature_id`),
  UNIQUE KEY `uq_profile_feat` (`profile_id`,`feature_key`),
  CONSTRAINT `kp_profile_features_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `kp_business_profiles` (`profile_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kp_profile_features VALUES("1","1","multi_counter","1");
INSERT INTO kp_profile_features VALUES("2","1","counter_count","4");
INSERT INTO kp_profile_features VALUES("3","1","has_store","1");
INSERT INTO kp_profile_features VALUES("4","1","auto_issue_to_store","0");
INSERT INTO kp_profile_features VALUES("5","1","has_kitchen","1");
INSERT INTO kp_profile_features VALUES("6","1","has_grill","1");
INSERT INTO kp_profile_features VALUES("7","1","purchase_flow","structured");
INSERT INTO kp_profile_features VALUES("8","1","structure_purchase_required","1");
INSERT INTO kp_profile_features VALUES("9","1","has_sports","0");
INSERT INTO kp_profile_features VALUES("10","2","multi_counter","0");
INSERT INTO kp_profile_features VALUES("11","2","counter_count","1");
INSERT INTO kp_profile_features VALUES("12","2","has_store","1");
INSERT INTO kp_profile_features VALUES("13","2","auto_issue_to_store","1");
INSERT INTO kp_profile_features VALUES("14","2","has_kitchen","1");
INSERT INTO kp_profile_features VALUES("15","2","has_grill","0");
INSERT INTO kp_profile_features VALUES("16","2","purchase_flow","simple");
INSERT INTO kp_profile_features VALUES("17","2","structure_purchase_required","0");
INSERT INTO kp_profile_features VALUES("18","2","has_sports","0");
INSERT INTO kp_profile_features VALUES("19","3","multi_counter","1");
INSERT INTO kp_profile_features VALUES("20","3","counter_count","3");
INSERT INTO kp_profile_features VALUES("21","3","has_store","1");
INSERT INTO kp_profile_features VALUES("22","3","auto_issue_to_store","1");
INSERT INTO kp_profile_features VALUES("23","3","has_kitchen","0");
INSERT INTO kp_profile_features VALUES("24","3","has_grill","1");
INSERT INTO kp_profile_features VALUES("25","3","purchase_flow","simple");
INSERT INTO kp_profile_features VALUES("26","3","structure_purchase_required","0");
INSERT INTO kp_profile_features VALUES("27","3","has_sports","1");
INSERT INTO kp_profile_features VALUES("28","4","multi_counter","0");
INSERT INTO kp_profile_features VALUES("29","4","counter_count","1");
INSERT INTO kp_profile_features VALUES("30","4","has_store","0");
INSERT INTO kp_profile_features VALUES("31","4","auto_issue_to_store","0");
INSERT INTO kp_profile_features VALUES("32","4","has_kitchen","0");
INSERT INTO kp_profile_features VALUES("33","4","has_grill","0");
INSERT INTO kp_profile_features VALUES("34","4","purchase_flow","simple");
INSERT INTO kp_profile_features VALUES("35","4","structure_purchase_required","0");
INSERT INTO kp_profile_features VALUES("36","4","has_sports","0");





CREATE TABLE `kp_recipe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `raw_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_reject_po` (
  `po_id` int NOT NULL DEFAULT '0',
  `sup_id` int NOT NULL DEFAULT '0',
  `ord_user` int NOT NULL DEFAULT '0',
  `po_date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potype` int NOT NULL DEFAULT '0',
  `poby` int NOT NULL DEFAULT '0',
  `appr_status` int NOT NULL DEFAULT '0',
  `po_status` int NOT NULL DEFAULT '0',
  `appr_user` int NOT NULL DEFAULT '0',
  `appr_comment` text,
  `rec_status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_reject_po_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `po_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `qty` decimal(12,3) NOT NULL DEFAULT '0.000',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_reject_po_items_po` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_rejected_po` (
  `po_id` int NOT NULL DEFAULT '0',
  `sup_id` int NOT NULL DEFAULT '0',
  `ord_user` int NOT NULL DEFAULT '0',
  `po_date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `potype` int NOT NULL DEFAULT '0',
  `poby` int NOT NULL DEFAULT '0',
  `appr_status` int NOT NULL DEFAULT '0',
  `po_status` int NOT NULL DEFAULT '0',
  `appr_user` int NOT NULL DEFAULT '0',
  `appr_comment` text,
  `rec_status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_relationship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_relationship_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=632 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kp_relationship VALUES("8","AUNT");
INSERT INTO kp_relationship VALUES("5","BROTHER");
INSERT INTO kp_relationship VALUES("191","CHILD");
INSERT INTO kp_relationship VALUES("3","FATHER");
INSERT INTO kp_relationship VALUES("9","GRAND FATHER");
INSERT INTO kp_relationship VALUES("10","GRAND MOTHER");
INSERT INTO kp_relationship VALUES("1","HUSBAND");
INSERT INTO kp_relationship VALUES("4","MOTHER");
INSERT INTO kp_relationship VALUES("6","SISTER");
INSERT INTO kp_relationship VALUES("7","UNCLE");
INSERT INTO kp_relationship VALUES("2","WIFE");





CREATE TABLE `kp_req` (
  `req_id` int NOT NULL,
  `req_user` int NOT NULL,
  `req_date` date NOT NULL,
  `app_status` int NOT NULL DEFAULT '2',
  `app_user` int NOT NULL,
  `app_date` date NOT NULL,
  `app_datime` datetime NOT NULL,
  `req_status` int NOT NULL DEFAULT '2',
  `issue_status` int NOT NULL DEFAULT '2',
  `issue_user` int NOT NULL,
  `issue_date` date NOT NULL,
  `issue_datime` datetime NOT NULL,
  `rec_date` date NOT NULL,
  `rec_qty` int NOT NULL,
  `rec_user` int NOT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `req_type` int NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `shop_id` int NOT NULL,
  UNIQUE KEY `req_id` (`req_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_req_id` (
  `req_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_req_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_id` int NOT NULL,
  `item_id` int NOT NULL,
  `req_qty` int NOT NULL,
  `req_user` int NOT NULL,
  `req_date` date NOT NULL,
  `req_status` int NOT NULL DEFAULT '2',
  `app_qty` int DEFAULT NULL,
  `app_user` int DEFAULT NULL,
  `app_datime` datetime DEFAULT NULL,
  `app_status` int NOT NULL DEFAULT '2',
  `issue_status` int NOT NULL DEFAULT '2',
  `issue_user` int NOT NULL,
  `issue_qty` int NOT NULL,
  `issue_shift` date NOT NULL,
  `issue_datime` datetime NOT NULL,
  `rec_qty` int DEFAULT NULL,
  `rec_user` int DEFAULT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `price` int NOT NULL,
  `total` int NOT NULL,
  `rec_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_req_status` (
  `req_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `type` int NOT NULL,
  `day` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_ret_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `item_id` int NOT NULL,
  `oqty` decimal(10,2) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `user_id` int NOT NULL,
  `ret_status` int NOT NULL DEFAULT '2',
  `confirm_by` int NOT NULL,
  `confirm_status` int NOT NULL DEFAULT '2',
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stock_counter` int NOT NULL,
  `sale_counter` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `raise_datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cashier_status` tinyint(1) NOT NULL DEFAULT '2',
  `counter_status` tinyint(1) NOT NULL DEFAULT '2',
  `cashier_approve_datime` datetime DEFAULT NULL,
  `counter_approve_datime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

INSERT INTO kp_ret_items VALUES("1","25006","11","1.00","1.00","1","1","1","1","2026-08-11","2026-08-17 12:15:21","1","1","300.00","2026-08-17 12:15:21","1","1","2026-08-17 12:15:24","2026-08-17 13:10:07");
INSERT INTO kp_ret_items VALUES("2","23345","70","1.00","1.00","1","1","1","1","2026-08-11","2026-08-17 12:15:49","1","1","300.00","2026-08-17 12:15:49","1","1","2026-08-17 12:15:51","2026-08-17 13:10:39");





CREATE TABLE `kp_roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(22) NOT NULL,
  `role_description` varchar(85) NOT NULL,
  `role_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `role_last_update` datetime NOT NULL,
  `role_access_panel` int NOT NULL,
  `access_dash` varchar(55) NOT NULL,
  `front_perm` varchar(255) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

INSERT INTO kp_roles VALUES("1","ADMINISTRATOR","THIS MANAGES THE BACK END OF THE SYSTEM","2024-07-17 04:06:11","0000-00-00 00:00:00","3","admin_dash.php","1, 2, 3, 4, 5, 6, 7,8");
INSERT INTO kp_roles VALUES("2","SUPERVISOR","THE SUPERVISOR  HAS PRIVILEGES OVER ALL USERS","2024-07-17 04:06:11","0000-00-00 00:00:00","2","admin_dash.php","2, 3");
INSERT INTO kp_roles VALUES("3","CASHIER","","2024-07-17 04:06:11","0000-00-00 00:00:00","2","CASHIER","*");
INSERT INTO kp_roles VALUES("5","STORE MANAGER","HELPS IN MANAGING THE STORE","2024-07-17 04:06:11","0000-00-00 00:00:00","1","admin_dash.php","");
INSERT INTO kp_roles VALUES("6","WAITER","","2024-07-17 04:06:11","0000-00-00 00:00:00","2","","");





CREATE TABLE `kp_sale_id` (
  `sale_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_sale_id VALUES("20");





CREATE TABLE `kp_sale_items` (
  `sale_id` int NOT NULL,
  `item_id` int NOT NULL,
  `attr_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `dpt` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int NOT NULL DEFAULT '2',
  `counter` int NOT NULL,
  `pay_status` int NOT NULL DEFAULT '2',
  `hour` int NOT NULL,
  `ret_status` int NOT NULL DEFAULT '2',
  `stock_counter` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `accompaniments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `preparation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `token_points` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` int NOT NULL DEFAULT '0',
  `session_flag` int NOT NULL DEFAULT '2',
  `accompaniment_json` longtext,
  PRIMARY KEY (`id`),
  CONSTRAINT `kp_sale_items_chk_1` CHECK (json_valid(`accompaniments`)),
  CONSTRAINT `kp_sale_items_chk_2` CHECK (json_valid(`preparation`))
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;

INSERT INTO kp_sale_items VALUES("1","291","","4","1.00","1","10.00","10.00","2026-08-24","2026-08-24 16:20:15","2","1","2","1","2","1","2","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("3","126","","3","1.00","1","260.00","260.00","2026-08-24","2026-08-24 17:09:26","2","1","2","1","2","1","4","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("4","144","","6","2.00","1","100.00","200.00","2026-08-24","2026-08-24 19:19:36","2","1","2","1","2","1","10","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("4","143","","6","1.00","1","150.00","150.00","2026-08-24","2026-08-24 19:19:36","2","1","2","1","2","1","11","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("4","17","","6","1.00","1","2400.00","2400.00","2026-08-24","2026-08-24 19:19:36","2","1","2","1","2","1","12","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("5","22","2","7","1.00","1","100.00","100.00","2026-08-24","2026-08-24 19:28:18","2","1","2","1","2","1","13","","","0.00","0","2","[{\"id\":2,\"name\":\"COKE\",\"price_change\":0}]");
INSERT INTO kp_sale_items VALUES("7","98","","9","1.00","1","1200.00","1200.00","2026-08-24","2026-08-24 19:51:37","2","1","2","1","2","1","16","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("7","23","","9","1.00","1","200.00","200.00","2026-08-24","2026-08-24 19:51:37","2","1","2","1","2","1","17","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("6","7","","8","2.00","1","300.00","600.00","2026-08-24","2026-08-24 20:00:47","2","1","2","1","2","1","20","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("8","71","","3","1.00","1","260.00","260.00","2026-08-24","2026-08-24 21:17:32","2","1","2","1","2","1","22","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("9","126","","6","2.00","1","260.00","520.00","2026-08-24","2026-08-24 21:21:12","2","1","2","1","2","1","25","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("9","142","","6","2.00","1","350.00","700.00","2026-08-24","2026-08-24 21:21:12","2","1","2","1","2","1","26","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("10","69","","6","2.00","1","350.00","700.00","2026-08-24","2026-08-24 21:21:50","2","1","2","1","2","1","29","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("11","126","","11","4.00","1","260.00","1040.00","2026-08-24","2026-08-24 21:29:09","2","1","2","1","2","1","31","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("12","123","","9","2.00","1","270.00","540.00","2026-08-24","2026-08-24 21:53:15","2","1","2","1","2","1","33","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("13","19","","8","1.00","1","300.00","300.00","2026-08-24","2026-08-24 21:55:30","2","1","2","1","2","1","35","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("14","120","","8","2.00","1","280.00","560.00","2026-08-24","2026-08-24 21:56:53","2","1","2","1","2","1","37","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("15","121","","3","4.00","1","280.00","1120.00","2026-08-24","2026-08-24 21:58:16","2","1","2","1","2","1","39","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("16","8","","8","2.00","1","280.00","560.00","2026-08-24","2026-08-24 21:59:56","2","1","2","1","2","1","41","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("18","126","","3","7.00","1","260.00","1820.00","2026-08-24","2026-08-24 23:12:09","2","1","2","1","2","1","43","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("19","190","","3","2.00","1","2700.00","5400.00","2026-08-24","2026-08-24 23:13:19","2","1","2","1","2","1","45","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("17","36","","6","1.00","1","4700.00","4700.00","2026-08-24","2026-08-24 23:17:57","2","1","2","1","2","1","49","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("17","23","","6","2.00","1","200.00","400.00","2026-08-24","2026-08-24 23:17:57","2","1","2","1","2","1","50","","","0.00","0","2","");
INSERT INTO kp_sale_items VALUES("17","22","2","6","1.00","1","100.00","100.00","2026-08-24","2026-08-24 23:17:57","2","1","2","1","2","1","51","","","0.00","0","2","[{\"id\":2,\"name\":\"COKE\",\"price_change\":0}]");
INSERT INTO kp_sale_items VALUES("20","15","","11","4.00","1","350.00","1400.00","2026-08-24","2026-08-24 23:18:49","2","1","2","1","2","1","52","","","0.00","0","2","");





CREATE TABLE `kp_sale_payment` (
  `pay_id` int NOT NULL,
  `sale_id` varchar(155) COLLATE latin1_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `ord_user` int NOT NULL,
  `cust_id` int NOT NULL,
  `tendered` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `paid_total` decimal(10,2) NOT NULL,
  `ord_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modesc` decimal(10,2) NOT NULL,
  `mode1` decimal(10,2) NOT NULL,
  `type` int NOT NULL,
  `counter` int NOT NULL,
  `mode2` decimal(10,2) NOT NULL,
  `moderef2` varchar(22) COLLATE latin1_general_ci NOT NULL,
  `mode3` decimal(10,2) NOT NULL,
  `mode4` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moderef3` varchar(120) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `moderef1` varchar(120) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `moderef4` varchar(120) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_sale_print` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `attr_id` int DEFAULT NULL,
  `accompaniment_json` longtext,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `shift` varchar(60) NOT NULL DEFAULT '',
  `status` int NOT NULL DEFAULT '2',
  `dpt` int NOT NULL,
  `b_print` int NOT NULL DEFAULT '2',
  `k_print` int NOT NULL DEFAULT '2',
  `stock_counter` int NOT NULL,
  `counter` int NOT NULL,
  `hour` int NOT NULL,
  `accompaniments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `preparation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_flag` tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  KEY `idx_sale_id` (`sale_id`),
  KEY `idx_sale_dpt` (`sale_id`,`dpt`),
  KEY `idx_sale_flag` (`sale_id`,`session_flag`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_sale_print VALUES("20","5","7","22","2","[{\"id\":2,\"name\":\"COKE\",\"price_change\":0}]","1.00","100.00","100.00","2026-08-24","2","1","2","2","1","1","1","","","2026-08-24 19:28:18","2");
INSERT INTO kp_sale_print VALUES("42","20","11","15","","","4.00","350.00","1400.00","2026-08-24","2","1","2","2","1","1","1","","","2026-08-24 23:18:49","2");





CREATE TABLE `kp_sale_print_old` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `attr_id` int DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `dpt` int NOT NULL,
  `b_print` int NOT NULL DEFAULT '2',
  `k_print` int NOT NULL DEFAULT '2',
  `stock_counter` int NOT NULL,
  `counter` int NOT NULL,
  `hour` int NOT NULL,
  `accompaniments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `preparation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_flag` tinyint(1) NOT NULL DEFAULT '2',
  `accompaniment_json` longtext,
  CONSTRAINT `kp_sale_print_old_chk_1` CHECK (json_valid(`accompaniments`)),
  CONSTRAINT `kp_sale_print_old_chk_2` CHECK (json_valid(`preparation`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_sale_reprint` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `appr_by` int NOT NULL,
  `shift` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_sale_status` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `counter` int NOT NULL,
  `parent` int NOT NULL DEFAULT '0',
  `has_parent` int NOT NULL DEFAULT '2',
  `mode` varchar(55) NOT NULL DEFAULT 'fresh',
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_status` int NOT NULL DEFAULT '2',
  `parent_sale_id` int NOT NULL,
  PRIMARY KEY (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_sale_status VALUES("2","4","2","0","0","2","fresh","2026-08-24 16:22:05","2","0");
INSERT INTO kp_sale_status VALUES("5","7","2","0","0","2","fresh","2026-08-24 19:27:50","2","0");
INSERT INTO kp_sale_status VALUES("20","11","2","0","0","2","fresh","2026-08-24 23:17:35","2","0");





CREATE TABLE `kp_sales` (
  `sale_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tbl` int DEFAULT NULL,
  `shift` date NOT NULL,
  `pay_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pay_status` int NOT NULL DEFAULT '2',
  `pay_mode` varchar(11) DEFAULT NULL,
  `split` int NOT NULL DEFAULT '2',
  `merge` int NOT NULL DEFAULT '2',
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `counter` int NOT NULL,
  `print_cntr_id` int NOT NULL DEFAULT '0',
  `cust_id` int DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token_points` decimal(10,2) NOT NULL DEFAULT '0.00',
  `parent` int NOT NULL DEFAULT '0',
  `is_child_bill` tinyint(1) NOT NULL DEFAULT '0',
  `bill_role` varchar(20) NOT NULL DEFAULT 'closed',
  `bill_status` tinyint(1) NOT NULL DEFAULT '2',
  `root_parent_sale_id` int NOT NULL DEFAULT '0',
  `previous_sale_id` int NOT NULL DEFAULT '0',
  `previous_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `parent_sale` int NOT NULL DEFAULT '0',
  `sale_bal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cf_bal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`),
  KEY `order_no` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_sales VALUES("1","4","","2026-08-24","0000-00-00","10.00","2","","2","2","2026-08-24 16:20:15","1","1","","","","2026-08-24 16:20:15","0.00","0","0","closed","2","0","0","0.00","10.00","10.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("3","3","","2026-08-24","0000-00-00","260.00","2","","2","2","2026-08-24 17:09:26","1","1","","","","2026-08-24 17:09:26","0.00","0","0","closed","2","0","0","0.00","260.00","260.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("4","6","","2026-08-24","0000-00-00","2750.00","2","","2","2","2026-08-24 19:19:35","1","1","","","","2026-08-24 19:19:35","0.00","0","0","closed","2","0","0","0.00","2750.00","2750.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("6","8","","2026-08-24","0000-00-00","600.00","2","","2","2","2026-08-24 20:00:46","1","1","","","","2026-08-24 20:00:46","0.00","0","0","closed","2","0","0","0.00","600.00","600.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("7","9","","2026-08-24","0000-00-00","1400.00","2","","2","2","2026-08-24 19:51:37","1","1","","","","2026-08-24 19:51:37","0.00","0","0","closed","2","0","0","0.00","1400.00","1400.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("8","3","","2026-08-24","0000-00-00","260.00","2","","2","2","2026-08-24 21:17:32","1","1","","","","2026-08-24 21:17:32","0.00","0","0","closed","2","0","0","0.00","260.00","260.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("9","6","","2026-08-24","0000-00-00","1220.00","2","","2","2","2026-08-24 21:21:12","1","1","","","","2026-08-24 21:21:12","0.00","0","0","closed","2","0","0","0.00","1220.00","1220.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("10","6","","2026-08-24","0000-00-00","700.00","2","","2","2","2026-08-24 21:21:50","1","1","","","","2026-08-24 21:21:50","0.00","0","0","closed","2","0","0","0.00","700.00","700.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("11","11","","2026-08-24","0000-00-00","1040.00","2","","2","2","2026-08-24 21:29:09","1","1","","","","2026-08-24 21:29:09","0.00","0","0","closed","2","0","0","0.00","1040.00","1040.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("12","9","","2026-08-24","0000-00-00","540.00","2","","2","2","2026-08-24 21:53:15","1","1","","","","2026-08-24 21:53:15","0.00","0","0","closed","2","0","0","0.00","540.00","540.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("13","8","","2026-08-24","0000-00-00","300.00","2","","2","2","2026-08-24 21:55:30","1","1","","","","2026-08-24 21:55:30","0.00","0","0","closed","2","0","0","0.00","300.00","300.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("14","8","","2026-08-24","0000-00-00","560.00","2","","2","2","2026-08-24 21:56:52","1","1","","","","2026-08-24 21:56:52","0.00","0","0","closed","2","0","0","0.00","560.00","560.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("15","3","","2026-08-24","0000-00-00","1120.00","2","","2","2","2026-08-24 21:58:16","1","1","","","","2026-08-24 21:58:16","0.00","0","0","closed","2","0","0","0.00","1120.00","1120.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("16","8","","2026-08-24","0000-00-00","560.00","2","","2","2","2026-08-24 21:59:56","1","1","","","","2026-08-24 21:59:56","0.00","0","0","closed","2","0","0","0.00","560.00","560.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("17","6","","2026-08-24","0000-00-00","5200.00","2","","2","2","2026-08-24 23:17:56","1","1","","","","2026-08-24 23:17:56","0.00","0","0","closed","2","0","0","0.00","5200.00","5200.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("18","3","","2026-08-24","0000-00-00","1820.00","2","","2","2","2026-08-24 23:12:09","1","1","","","","2026-08-24 23:12:09","0.00","0","0","closed","2","0","0","0.00","1820.00","1820.00","0","0.00","0.00","0");
INSERT INTO kp_sales VALUES("19","3","","2026-08-24","0000-00-00","5400.00","2","","2","2","2026-08-24 23:13:19","1","1","","","","2026-08-24 23:13:19","0.00","0","0","closed","2","0","0","0.00","5400.00","5400.00","0","0.00","0.00","0");





CREATE TABLE `kp_sales_closed_opened` (
  `closed_open_id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL DEFAULT '0',
  `mode` varchar(55) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'each',
  `user_id` int NOT NULL DEFAULT '0',
  `color` int NOT NULL DEFAULT '0',
  `identifier_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'color',
  `identifier_value` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `identifier_label` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `identifier_hex` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `opened_by` int NOT NULL DEFAULT '0',
  `bill_mode` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'each',
  `counter_id` int NOT NULL DEFAULT '0',
  `print_cntr_id` int NOT NULL DEFAULT '0',
  `shift` varchar(60) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`closed_open_id`),
  KEY `idx_sale_id` (`sale_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `kp_sales_open` (
  `open_id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL DEFAULT '0',
  `child_sale_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `color` int NOT NULL DEFAULT '0',
  `identifier_type` varchar(20) NOT NULL DEFAULT 'color',
  `identifier_value` varchar(120) NOT NULL DEFAULT '',
  `identifier_label` varchar(120) NOT NULL DEFAULT '',
  `identifier_hex` varchar(20) NOT NULL DEFAULT '',
  `last_bill` int NOT NULL DEFAULT '0',
  `bill_count` int NOT NULL DEFAULT '0',
  `counter_id` int NOT NULL DEFAULT '0',
  `shift` varchar(60) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bill_mode` varchar(20) NOT NULL DEFAULT 'each',
  `parent_sale_id` int NOT NULL DEFAULT '0',
  `sale_role` varchar(20) NOT NULL DEFAULT 'parent',
  `is_child_bill` tinyint(1) NOT NULL DEFAULT '0',
  `print_cntr_id` int NOT NULL DEFAULT '0',
  `open_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `continuation_type` varchar(20) NOT NULL DEFAULT 'none',
  `parent_closed_open_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`open_id`),
  UNIQUE KEY `uniq_kp_sales_open_sale_id` (`sale_id`),
  KEY `idx_sale_child` (`sale_id`,`child_sale_id`),
  KEY `idx_user_shift` (`user_id`,`shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_sales_paid` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pay_id` int NOT NULL,
  `sale_id` varchar(155) COLLATE latin1_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `ord_user` int NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `paid_total` decimal(10,2) NOT NULL,
  `ord_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `kp_settings_gen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(55) NOT NULL,
  `company_tag` varchar(55) NOT NULL,
  `address` varchar(55) NOT NULL,
  `phone1` varchar(22) NOT NULL,
  `phone2` varchar(22) NOT NULL,
  `email` varchar(55) NOT NULL,
  `kra_pin` varchar(22) NOT NULL,
  `vat` varchar(22) NOT NULL,
  `tax_type` int NOT NULL,
  `mpesa_no` varchar(55) NOT NULL,
  `mpesa_account` varchar(11) NOT NULL,
  `image_path` text NOT NULL,
  `thermal_logo_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

INSERT INTO kp_settings_gen VALUES("1","MUIGAI INN","","     ",""," ","","","","1","8141420","","","");





CREATE TABLE `kp_settings_print` (
  `print_doc` int NOT NULL,
  `show_logo` int NOT NULL,
  `pin` int NOT NULL,
  `tax` int NOT NULL,
  `mpesa` int NOT NULL,
  `thankyou_note` varchar(255) NOT NULL,
  `token_enabled` tinyint(1) NOT NULL DEFAULT '2',
  `token_mode` tinyint(1) NOT NULL DEFAULT '1',
  `token_piece_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `token_percent_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `token_brackets` text,
  `print_mode` tinyint(1) NOT NULL DEFAULT '1',
  `counter_close_rule` tinyint(1) NOT NULL DEFAULT '1',
  `print_template` tinyint(1) NOT NULL DEFAULT '1',
  `receipt_divider_symbol` varchar(8) NOT NULL DEFAULT '*',
  `receipt_divider_count` int NOT NULL DEFAULT '50',
  `qz_paper_width_mm` decimal(6,2) NOT NULL DEFAULT '80.00',
  `qz_margin_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
  `qz_scale_pct` decimal(6,2) NOT NULL DEFAULT '80.00',
  `show_qr` tinyint(1) NOT NULL DEFAULT '2',
  `thermal_logo_height` int NOT NULL DEFAULT '60',
  `kpesa_active` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `print_doc` (`print_doc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_settings_print VALUES("1","2","2","2","1"," ","1","3","0.00","1.00","0,100,5/\n/100.01,250,12/\n/250.01,,20","4","1","1","*","50","80.00","0.00","90.00","2","60","1");





CREATE TABLE `kp_settings_purchase` (
  `settings_id` int NOT NULL AUTO_INCREMENT,
  `front_purchase_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `front_auto_stock_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_settings_purchase VALUES("1","1","0","2026-07-19 14:01:21");





CREATE TABLE `kp_settings_return` (
  `settings_id` int NOT NULL AUTO_INCREMENT,
  `approval_mode` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_settings_return VALUES("1","1");





CREATE TABLE `kp_shift` (
  `shift` date NOT NULL,
  `stock_status` int NOT NULL DEFAULT '2',
  `counter_status` int NOT NULL DEFAULT '2',
  `kit_status` int NOT NULL DEFAULT '2',
  UNIQUE KEY `shift` (`shift`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_shop_expenses` (
  `exp_id` int NOT NULL AUTO_INCREMENT,
  `shift_date` date NOT NULL,
  `title` varchar(120) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `note` text,
  `user_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_sms_settings` (
  `set_id` int NOT NULL DEFAULT '1',
  `sms_provider` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'kreach',
  `sms_api_key` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sms_kreach_base_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sms_kreach_send_endpoint` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/api/v1/sms/send',
  `sms_kreach_bulk_endpoint` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/api/v1/sms/send/bulk',
  `sms_kreach_account_endpoint` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/api/v1/account',
  `sms_kreach_balance_endpoint` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/api/v1/account/balance',
  `sms_sender_id` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `last_balance` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `last_balance_checked_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO kp_sms_settings VALUES("1","kreach","","","/api/v1/sms/send","/api/v1/sms/send/bulk","/api/v1/account","/api/v1/account/balance","","","","2026-06-15 18:09:00");





CREATE TABLE `kp_split_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '',
  `phone` varchar(40) NOT NULL DEFAULT '',
  `code` varchar(80) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `account` varchar(120) NOT NULL DEFAULT '',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date` date DEFAULT NULL,
  `time` varchar(30) NOT NULL DEFAULT '',
  `datime` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '2',
  `claim_status` tinyint(1) NOT NULL DEFAULT '2',
  `assign_status` tinyint(1) NOT NULL DEFAULT '2',
  `approve_status` tinyint(1) NOT NULL DEFAULT '2',
  `clear_status` tinyint(1) NOT NULL DEFAULT '2',
  `user_id` int NOT NULL DEFAULT '0',
  `claim_staff` int NOT NULL DEFAULT '0',
  `assign_by` int NOT NULL DEFAULT '0',
  `app_user` int NOT NULL DEFAULT '0',
  `shift` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`),
  KEY `idx_claim_status` (`claim_status`,`assign_status`),
  KEY `idx_user_shift` (`user_id`,`shift`),
  KEY `idx_claim_staff` (`claim_staff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `staff_name` varchar(33) DEFAULT NULL,
  `staff_nid` varchar(22) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `staff_email` varchar(44) DEFAULT NULL,
  `staff_address` varchar(55) DEFAULT NULL,
  `staff_cv` text,
  `staff_photo` varchar(99) DEFAULT NULL,
  `staff_phone` varchar(22) DEFAULT NULL,
  `staff_next_of_kin` varchar(22) DEFAULT NULL,
  `staff_kin_name` varchar(22) NOT NULL,
  `staff_kin_phone` varchar(22) DEFAULT NULL,
  `staff_kin_relationship` varchar(22) DEFAULT NULL,
  `staff_kin_address` varchar(55) DEFAULT NULL,
  `staff_kin_residence` text NOT NULL,
  `staff_hire_date` datetime DEFAULT NULL,
  `staff_updated_date` datetime DEFAULT NULL,
  `staff_residence` varchar(33) DEFAULT NULL,
  `staff_del_status` int NOT NULL DEFAULT '2',
  `user_status` int NOT NULL DEFAULT '2',
  `staff_nhif` varchar(22) NOT NULL,
  `staff_nssf` varchar(22) NOT NULL,
  `staff_kra_pin` varchar(22) NOT NULL,
  `surname` varchar(150) DEFAULT NULL,
  `alias` varchar(150) DEFAULT NULL,
  `staff_nck_no` varchar(80) DEFAULT NULL,
  `staff_id_imgback` varchar(255) DEFAULT NULL,
  `whatsphone` varchar(60) DEFAULT NULL,
  `staff_kin_id` varchar(80) DEFAULT NULL,
  `staff_kin_relation` varchar(100) DEFAULT NULL,
  `staff_user_status` tinyint NOT NULL DEFAULT '0',
  `staff_cv_status` tinyint NOT NULL DEFAULT '0',
  `staff_nid_img` varchar(255) DEFAULT NULL,
  `staff_nid_img_status` tinyint NOT NULL DEFAULT '0',
  `staff_kin_id_image` varchar(255) DEFAULT NULL,
  `staff_kin_id_image_status` tinyint NOT NULL DEFAULT '0',
  `staff_conduct_img` varchar(255) DEFAULT NULL,
  `staff_conduct_img_status` tinyint NOT NULL DEFAULT '0',
  `emp_type` varchar(80) DEFAULT NULL,
  `salary_cycle` varchar(50) DEFAULT NULL,
  `salary_type` varchar(50) DEFAULT NULL,
  `basic_salary` decimal(14,2) DEFAULT NULL,
  `work_status` varchar(50) DEFAULT NULL,
  UNIQUE KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

INSERT INTO kp_staff VALUES("1","ESTHER","22517188","","","","","0720902827","","","","","","","0000-00-00 00:00:00","","JUJA","2","2","","","","KAMAU","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("2","BELLAH","29692043","","","","","0708779705","","","","","","","0000-00-00 00:00:00","","JUJA","2","2","","","","RONOH","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("3","HARUN","13705831","","","","","0724879447","","","","","","","0000-00-00 00:00:00","","ZIMMERMAN","2","2","","","","WANYOIKE","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("4","ELIZABETH","41045246","","","","","0725617286","","","","","","","0000-00-00 00:00:00","","KENYATTA ROAD","2","2","","","","KARIUKI","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("5","ALICIAH","34501994","","","","","0727940075","","","","","","","0000-00-00 00:00:00","","KIMBO","2","2","","","","MWANGI","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("6","MAUREEN","28020441","","","","","0725446078","","","","","","","0000-00-00 00:00:00","","GITHURAI","2","2","","","","MUNGAI","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("7","BRENDA","37462278","","","","","0714461701","","","","","","","0000-00-00 00:00:00","","KENYATTA ROAD","2","2","","","","RUTH","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("8","PETER","34136760","","","","","0793877999","","","","","","","0000-00-00 00:00:00","","JUJA","2","2","","","","DUNCAN","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("9","JECINTA","32812708","","","","","0705387538","","","","","","","0000-00-00 00:00:00","","ZIMMERMAN","2","2","","","","KANGETHE","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("10","NANCY","28254195","","","","","0769008769","","","","","","","0000-00-00 00:00:00","","JUJA","2","2","","","","MWANGI","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("11","NANCY","25095704","","","","","0727218616","","","","","","","0000-00-00 00:00:00","","KENYATTA ROAD","2","2","","","","KARANJA","","","","","","","0","0","","0","","0","","0","","","","","Active");
INSERT INTO kp_staff VALUES("12","LUCY","333245511","","","","","0114723151","","","","","","","0000-00-00 00:00:00","","GITHURAI 44","2","2","","","","WANDIA","","","","","","","0","0","","0","","0","","0","","","","","Active");





CREATE TABLE `kp_staff_documents` (
  `document_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int unsigned NOT NULL,
  `doc_code` varchar(80) DEFAULT NULL,
  `doc_title` varchar(150) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_ext` varchar(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `is_current` tinyint NOT NULL DEFAULT '1',
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  KEY `idx_staff_documents_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_staff_employment` (
  `employment_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int unsigned NOT NULL,
  `shop_id` int DEFAULT NULL,
  `department_name` varchar(150) DEFAULT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `employment_type` varchar(80) DEFAULT NULL,
  `contract_type` varchar(80) DEFAULT NULL,
  `work_status` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `probation_end_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `exit_date` date DEFAULT NULL,
  `supervisor_name` varchar(150) DEFAULT NULL,
  `notes` text,
  `is_current` tinyint NOT NULL DEFAULT '1',
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`employment_id`),
  KEY `idx_staff_employment_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_staff_employment_history` (
  `history_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int unsigned NOT NULL,
  `employment_id` bigint DEFAULT NULL,
  `financial_id` bigint DEFAULT NULL,
  `change_type` varchar(80) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `change_data` json DEFAULT NULL,
  `changed_by` int DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `idx_staff_history_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_staff_nok` (
  `nok_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int unsigned NOT NULL,
  `nok_name` varchar(150) DEFAULT NULL,
  `nok_phone` varchar(60) DEFAULT NULL,
  `nok_id_number` varchar(80) DEFAULT NULL,
  `nok_relationship` varchar(100) DEFAULT NULL,
  `nok_address` varchar(255) DEFAULT NULL,
  `nok_residence` varchar(150) DEFAULT NULL,
  `is_current` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`nok_id`),
  KEY `idx_staff_nok_staff` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kp_staff_nok VALUES("1","1","NANCY","0720964100","","MOTHER","","JUJA","1","","");
INSERT INTO kp_staff_nok VALUES("2","2","BENJAMIN","0701474752","","FATHER","","","1","","");
INSERT INTO kp_staff_nok VALUES("3","3","TABBY","0722781211","","WIFE","","ZIMMERMAN","1","","");
INSERT INTO kp_staff_nok VALUES("4","4","LUCY","0117433089","","SISTER","","GATUNDU","1","","");
INSERT INTO kp_staff_nok VALUES("5","5","TERESA","0724710846","","SISTER","","THIKA","1","","");
INSERT INTO kp_staff_nok VALUES("6","6","MARGERET","0769583783","","MOTHER","","GITHURAI","1","","");
INSERT INTO kp_staff_nok VALUES("7","7","JESSY","070677715","","HUSBAND","","KENYATAA ROAD","1","","");
INSERT INTO kp_staff_nok VALUES("8","8","BEATRICE","0791555726","","MOTHER","","KISII","1","","");
INSERT INTO kp_staff_nok VALUES("9","9","HANNAH","0745945263","","MOTHER","","GATUNDU","1","","");
INSERT INTO kp_staff_nok VALUES("10","10","JARED","0728248167","","BROTHER","","JUJA","1","","");
INSERT INTO kp_staff_nok VALUES("11","11","NANCY","07111111","","BROTHER","","","1","","");
INSERT INTO kp_staff_nok VALUES("12","12","FRANCIS GITHUA","0726131081","","UNCLE","","NYERI","1","","");





CREATE TABLE `kp_staff_profile_checks` (
  `staff_id` int unsigned NOT NULL,
  `details_present` tinyint NOT NULL DEFAULT '0',
  `nok_present` tinyint NOT NULL DEFAULT '0',
  `docs_present` tinyint NOT NULL DEFAULT '0',
  `image_present` tinyint NOT NULL DEFAULT '0',
  `details_verified` tinyint NOT NULL DEFAULT '0',
  `docs_verified` tinyint NOT NULL DEFAULT '0',
  `available_count` int NOT NULL DEFAULT '0',
  `required_total` int NOT NULL DEFAULT '0',
  `completion_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `updated_at` datetime DEFAULT NULL,
  `employment_present` tinyint NOT NULL DEFAULT '0',
  `financials_present` tinyint NOT NULL DEFAULT '0',
  `employment_verified` tinyint NOT NULL DEFAULT '0',
  `financials_verified` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_staff_token_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0',
  `shift_date` date DEFAULT NULL,
  `sale_id` int NOT NULL DEFAULT '0',
  `item_id` int NOT NULL DEFAULT '0',
  `attr_id` int DEFAULT NULL,
  `token_key` varchar(120) NOT NULL DEFAULT '',
  `token_mode` tinyint(1) NOT NULL DEFAULT '1',
  `token_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `snapshot_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `snapshot_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `source_action` varchar(60) NOT NULL DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  KEY `idx_user_shift` (`user_id`,`shift_date`),
  KEY `idx_sale` (`sale_id`),
  KEY `idx_token_key` (`token_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;






CREATE TABLE `kp_staff_token_wallet` (
  `user_id` int NOT NULL,
  `total_tokens` decimal(10,2) NOT NULL DEFAULT '0.00',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO kp_staff_token_wallet VALUES("1","3.50","2026-08-17 13:10:40");
INSERT INTO kp_staff_token_wallet VALUES("2","70.00","2026-06-21 14:11:48");
INSERT INTO kp_staff_token_wallet VALUES("3","2056.50","2026-06-22 19:10:08");
INSERT INTO kp_staff_token_wallet VALUES("4","142.00","2026-06-11 16:53:06");
INSERT INTO kp_staff_token_wallet VALUES("5","2150.50","2026-06-16 13:49:04");
INSERT INTO kp_staff_token_wallet VALUES("8","3152.00","2026-06-20 14:49:35");
INSERT INTO kp_staff_token_wallet VALUES("9","681.00","2026-06-20 20:41:36");
INSERT INTO kp_staff_token_wallet VALUES("18","93.00","2026-06-14 17:55:54");





CREATE TABLE `kp_stock_take_events` (
  `event_id` int unsigned NOT NULL AUTO_INCREMENT,
  `take_id` int unsigned NOT NULL,
  `user_id` int NOT NULL DEFAULT '0',
  `event_type` varchar(40) NOT NULL,
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `take_id` (`take_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_stock_take_lines` (
  `line_id` int unsigned NOT NULL AUTO_INCREMENT,
  `take_id` int unsigned NOT NULL,
  `item_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `expected_qty` decimal(14,3) NOT NULL DEFAULT '0.000',
  `counted_qty` decimal(14,3) DEFAULT NULL,
  `final_qty` decimal(14,3) DEFAULT NULL,
  `variance_qty` decimal(14,3) DEFAULT NULL,
  `assigned_user_id` int NOT NULL DEFAULT '0',
  `line_status` varchar(20) NOT NULL DEFAULT 'OPEN',
  `retake_required` tinyint NOT NULL DEFAULT '0',
  `count_notes` text,
  `retake_notes` text,
  `counted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`line_id`),
  UNIQUE KEY `take_item` (`take_id`,`item_id`),
  KEY `take_id` (`take_id`),
  KEY `assigned_user_id` (`assigned_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_stock_take_retakes` (
  `retake_id` int unsigned NOT NULL AUTO_INCREMENT,
  `take_id` int unsigned NOT NULL,
  `line_id` int unsigned NOT NULL,
  `item_id` int NOT NULL,
  `original_counted_qty` decimal(14,3) DEFAULT NULL,
  `retake_qty` decimal(14,3) DEFAULT NULL,
  `reason` varchar(255) NOT NULL DEFAULT '',
  `request_notes` text,
  `approval_notes` text,
  `assigned_user_id` int NOT NULL DEFAULT '0',
  `requested_by` int NOT NULL DEFAULT '0',
  `completed_by` int NOT NULL DEFAULT '0',
  `approved_by` int NOT NULL DEFAULT '0',
  `status` varchar(25) NOT NULL DEFAULT 'REQUESTED',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`retake_id`),
  KEY `take_id` (`take_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_stock_take_reviews` (
  `review_id` int unsigned NOT NULL AUTO_INCREMENT,
  `take_id` int unsigned NOT NULL,
  `line_id` int unsigned NOT NULL,
  `item_id` int NOT NULL,
  `explanation` text NOT NULL,
  `reviewed_by` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `take_line` (`take_id`,`line_id`),
  KEY `take_id` (`take_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_stock_takes` (
  `take_id` int unsigned NOT NULL AUTO_INCREMENT,
  `take_code` varchar(40) NOT NULL,
  `scope_type` varchar(20) NOT NULL DEFAULT 'all',
  `scope_id` int NOT NULL DEFAULT '0',
  `status` varchar(30) NOT NULL DEFAULT 'DRAFT',
  `notes` text,
  `approval_notes` text,
  `review_notes` text,
  `created_by` int NOT NULL DEFAULT '0',
  `approved_by` int NOT NULL DEFAULT '0',
  `assigned_by` int NOT NULL DEFAULT '0',
  `reviewed_by` int NOT NULL DEFAULT '0',
  `posted_by` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`take_id`),
  UNIQUE KEY `take_code` (`take_code`),
  KEY `status` (`status`),
  KEY `scope_type` (`scope_type`,`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_stock_track` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `attr_id` int NOT NULL,
  `sale_id` int DEFAULT NULL,
  `po_id` int DEFAULT NULL,
  `req_id` int DEFAULT NULL,
  `grn_id` int NOT NULL,
  `kit_id` int NOT NULL,
  `tot_id` int NOT NULL,
  `trans` varchar(155) NOT NULL,
  `trans_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT NULL,
  `status` varchar(6) DEFAULT NULL,
  `sale_counter` int NOT NULL,
  `stock_counter` int DEFAULT NULL,
  `shift` date DEFAULT NULL,
  `day` date NOT NULL,
  `hour` int DEFAULT NULL,
  `dpt` int DEFAULT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `counter` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=303 DEFAULT CHARSET=latin1;

INSERT INTO kp_stock_track VALUES("1","55","0","23345","","","0","0","0","","0","1","1.00","added","1","1","2026-07-28","0000-00-00","1","1","2026-07-29 20:47:36","0");
INSERT INTO kp_stock_track VALUES("5","105","0","25005","","","0","0","0","","0","1","1.00","added","1","1","2026-07-28","0000-00-00","1","1","2026-08-09 23:31:27","0");
INSERT INTO kp_stock_track VALUES("6","11","0","25005","","","0","0","0","","0","1","1.00","added","1","1","2026-07-28","0000-00-00","1","1","2026-08-09 23:31:31","0");
INSERT INTO kp_stock_track VALUES("7","71","0","","","405","0","0","0","","0","1","50.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:33","0");
INSERT INTO kp_stock_track VALUES("8","119","0","","","405","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:33","0");
INSERT INTO kp_stock_track VALUES("9","86","0","","","405","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:34","0");
INSERT INTO kp_stock_track VALUES("10","120","0","","","405","0","0","0","","0","1","100.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:34","0");
INSERT INTO kp_stock_track VALUES("11","123","0","","","405","0","0","0","","0","1","75.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:34","0");
INSERT INTO kp_stock_track VALUES("12","128","0","","","405","0","0","0","","0","1","24.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:34","0");
INSERT INTO kp_stock_track VALUES("13","12","0","","","405","0","0","0","","0","1","24.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("14","144","0","","","405","0","0","0","","0","1","72.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("15","23","0","","","405","0","0","0","","0","1","12.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("16","25","0","","","405","0","0","0","","0","1","12.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("17","22","0","","","405","0","0","0","","0","1","24.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("18","136","0","","","405","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:35","0");
INSERT INTO kp_stock_track VALUES("19","151","0","","","405","0","0","0","","0","1","3.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("20","48","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("21","53","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("22","67","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("23","60","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("24","89","0","","","405","0","0","0","","0","1","12.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("25","111","0","","","405","0","0","0","","0","1","12.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:36","0");
INSERT INTO kp_stock_track VALUES("26","158","0","","","405","0","0","0","","0","1","5.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("27","77","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("28","78","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("29","127","0","","","405","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("30","132","0","","","405","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("31","293","0","","","405","0","0","0","","0","1","3.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:37","0");
INSERT INTO kp_stock_track VALUES("32","294","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:38","0");
INSERT INTO kp_stock_track VALUES("33","27","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:38","0");
INSERT INTO kp_stock_track VALUES("34","207","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:38","0");
INSERT INTO kp_stock_track VALUES("35","30","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:38","0");
INSERT INTO kp_stock_track VALUES("36","40","0","","","405","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:38","0");
INSERT INTO kp_stock_track VALUES("37","242","0","","","405","0","0","0","","0","1","4.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:39","0");
INSERT INTO kp_stock_track VALUES("38","241","0","","","405","0","0","0","","0","1","2.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-11 19:58:39","0");
INSERT INTO kp_stock_track VALUES("39","97","0","","","","0","0","13","","0","1","1.00","OUT","0","1","2026-08-11","0000-00-00","","1","2026-08-12 19:33:41","0");
INSERT INTO kp_stock_track VALUES("40","97","0","","","","0","0","13","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 19:33:41","0");
INSERT INTO kp_stock_track VALUES("41","126","0","","","406","0","0","0","","0","1","100.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:00:37","0");
INSERT INTO kp_stock_track VALUES("42","120","0","","","406","0","0","0","","0","1","200.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:00:44","0");
INSERT INTO kp_stock_track VALUES("43","71","0","","","406","0","0","0","","0","1","100.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:00:58","0");
INSERT INTO kp_stock_track VALUES("44","124","0","","","406","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:01:04","0");
INSERT INTO kp_stock_track VALUES("45","132","0","","","406","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:01:17","0");
INSERT INTO kp_stock_track VALUES("46","7","0","","","406","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:02:02","0");
INSERT INTO kp_stock_track VALUES("47","8","0","","","406","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:02:13","0");
INSERT INTO kp_stock_track VALUES("48","152","0","","","406","0","0","0","","0","1","1.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:03:58","0");
INSERT INTO kp_stock_track VALUES("49","119","0","","","406","0","0","0","","0","1","25.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:04:21","0");
INSERT INTO kp_stock_track VALUES("50","292","0","","","406","0","0","0","","0","1","24.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:04:29","0");
INSERT INTO kp_stock_track VALUES("51","56","0","","","406","0","0","0","","0","1","24.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:04:37","0");
INSERT INTO kp_stock_track VALUES("52","144","0","","","406","0","0","0","","0","1","60.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:04:42","0");
INSERT INTO kp_stock_track VALUES("53","25","0","","","406","0","0","0","","0","1","12.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-12 21:04:57","0");
INSERT INTO kp_stock_track VALUES("54","295","0","","","407","0","0","0","","0","1","0.00","IN","0","1","2026-08-11","0000-00-00","","1","2026-08-14 22:24:40","0");
INSERT INTO kp_stock_track VALUES("55","7","0","","9","","18","0","0","GRN","0","1","1.00","IN","0","","","2026-08-15","","","2026-08-15 00:22:40","0");
INSERT INTO kp_stock_track VALUES("56","296","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("57","244","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("58","243","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("59","295","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("60","29","0","","2","","1","0","0","GRN","0","1","3.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("61","207","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("62","217","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("63","163","0","","2","","1","0","0","GRN","0","1","1.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("64","57","0","","2","","1","0","0","GRN","0","1","1.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("65","213","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("66","108","0","","2","","1","0","0","GRN","0","1","36.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("67","31","0","","2","","1","0","0","GRN","0","1","2.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("68","73","0","","2","","1","0","0","GRN","0","1","6.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("69","60","0","","2","","1","0","0","GRN","0","1","6.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("70","210","0","","2","","1","0","0","GRN","0","1","4.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("71","89","0","","2","","1","0","0","GRN","0","1","36.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("72","111","0","","2","","1","0","0","GRN","0","1","36.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("73","149","0","","2","","1","0","0","GRN","0","1","12.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("74","148","0","","2","","1","0","0","GRN","0","1","12.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("75","151","0","","2","","1","0","0","GRN","0","1","12.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("76","297","0","","2","","1","0","0","GRN","0","1","12.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("77","154","0","","2","","1","0","0","GRN","0","1","1.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("78","117","0","","2","","1","0","0","GRN","0","1","3.00","IN","0","","","2026-08-15","","","2026-08-15 23:39:56","0");
INSERT INTO kp_stock_track VALUES("79","8","0","","3","","2","0","0","GRN","0","1","50.00","IN","0","","","2026-08-15","","","2026-08-15 23:49:05","0");
INSERT INTO kp_stock_track VALUES("80","126","0","","4","","3","0","0","GRN","0","1","225.00","IN","0","","","2026-08-15","","","2026-08-15 23:57:46","0");
INSERT INTO kp_stock_track VALUES("81","121","0","","4","","3","0","0","GRN","0","1","50.00","IN","0","","","2026-08-15","","","2026-08-15 23:57:46","0");
INSERT INTO kp_stock_track VALUES("82","7","0","","5","","4","0","0","GRN","0","1","250.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("83","136","0","","5","","4","0","0","GRN","0","1","50.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("84","9","0","","5","","4","0","0","GRN","0","1","75.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("85","132","0","","5","","4","0","0","GRN","0","1","50.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("86","127","0","","5","","4","0","0","GRN","0","1","50.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("87","126","0","","5","","4","0","0","GRN","0","1","425.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("88","119","0","","5","","4","0","0","GRN","0","1","100.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("89","71","0","","5","","4","0","0","GRN","0","1","525.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("90","123","0","","5","","4","0","0","GRN","0","1","300.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("91","86","0","","5","","4","0","0","GRN","0","1","50.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("92","179","0","","5","","4","0","0","GRN","0","1","12.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("93","293","0","","5","","4","0","0","GRN","0","1","20.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("94","99","0","","5","","4","0","0","GRN","0","1","24.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("95","173","0","","5","","4","0","0","GRN","0","1","2.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("96","241","0","","5","","4","0","0","GRN","0","1","11.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("97","40","0","","5","","4","0","0","GRN","0","1","12.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("98","120","0","","5","","4","0","0","GRN","0","1","750.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("99","121","0","","5","","4","0","0","GRN","0","1","550.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("100","124","0","","5","","4","0","0","GRN","0","1","150.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("101","16","0","","5","","4","0","0","GRN","0","1","48.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("102","15","0","","5","","4","0","0","GRN","0","1","48.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("103","14","0","","5","","4","0","0","GRN","0","1","96.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("104","10","0","","5","","4","0","0","GRN","0","1","24.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("105","19","0","","5","","4","0","0","GRN","0","1","48.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("106","141","0","","5","","4","0","0","GRN","0","1","96.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("107","142","0","","5","","4","0","0","GRN","0","1","24.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("108","6","0","","5","","4","0","0","GRN","0","1","24.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("109","70","0","","5","","4","0","0","GRN","0","1","72.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("110","12","0","","5","","4","0","0","GRN","0","1","24.00","IN","0","","","2026-08-16","","","2026-08-16 01:32:30","0");
INSERT INTO kp_stock_track VALUES("111","144","0","","6","","5","0","0","GRN","0","1","720.00","IN","0","","","2026-08-16","","","2026-08-16 16:36:28","0");
INSERT INTO kp_stock_track VALUES("115","22","0","","9","","6","0","0","GRN","0","1","264.00","IN","0","","","2026-08-17","","","2026-08-17 15:03:04","0");
INSERT INTO kp_stock_track VALUES("116","299","0","","9","","6","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 15:03:04","0");
INSERT INTO kp_stock_track VALUES("117","76","0","","9","","6","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 15:03:04","0");
INSERT INTO kp_stock_track VALUES("118","85","0","","11","","7","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("119","7","0","","11","","7","0","0","GRN","0","1","125.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("120","142","0","","11","","7","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("121","115","0","","11","","7","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("122","8","0","","11","","7","0","0","GRN","0","1","50.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("123","141","0","","11","","7","0","0","GRN","0","1","120.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("124","9","0","","11","","7","0","0","GRN","0","1","25.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("125","132","0","","11","","7","0","0","GRN","0","1","50.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("126","70","0","","11","","7","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("127","127","0","","11","","7","0","0","GRN","0","1","50.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("128","139","0","","11","","7","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("129","126","0","","11","","7","0","0","GRN","0","1","400.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("130","121","0","","11","","7","0","0","GRN","0","1","250.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("131","119","0","","11","","7","0","0","GRN","0","1","100.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("132","124","0","","11","","7","0","0","GRN","0","1","125.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("133","71","0","","11","","7","0","0","GRN","0","1","275.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("134","123","0","","11","","7","0","0","GRN","0","1","175.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("135","86","0","","11","","7","0","0","GRN","0","1","50.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("136","120","0","","11","","7","0","0","GRN","0","1","750.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("137","300","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("138","2","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("139","188","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("140","305","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("141","171","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("142","87","0","","11","","7","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("143","157","0","","11","","7","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("144","173","0","","11","","7","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("145","242","0","","11","","7","0","0","GRN","0","1","18.00","IN","0","","","2026-08-17","","","2026-08-17 16:38:26","0");
INSERT INTO kp_stock_track VALUES("146","239","0","","13","","8","0","0","GRN","0","1","3.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("147","225","0","","13","","8","0","0","GRN","0","1","2.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("148","296","0","","13","","8","0","0","GRN","0","1","3.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("149","82","0","","13","","8","0","0","GRN","0","1","3.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("150","32","0","","13","","8","0","0","GRN","0","1","2.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("151","59","0","","13","","8","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("152","89","0","","13","","8","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("153","111","0","","13","","8","0","0","GRN","0","1","12.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("154","216","0","","13","","8","0","0","GRN","0","1","2.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("155","27","0","","13","","8","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("156","95","0","","13","","8","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("157","33","0","","13","","8","0","0","GRN","0","1","4.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("158","215","0","","13","","8","0","0","GRN","0","1","2.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("159","72","0","","13","","8","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("160","96","0","","13","","8","0","0","GRN","0","1","1.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("161","74","0","","13","","8","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("162","201","0","","13","","8","0","0","GRN","0","1","6.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("163","114","0","","13","","8","0","0","GRN","0","1","20.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("164","162","0","","13","","8","0","0","GRN","0","1","3.00","IN","0","","","2026-08-17","","","2026-08-17 17:07:50","0");
INSERT INTO kp_stock_track VALUES("165","292","0","","15","","9","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("166","69","0","","15","","9","0","0","GRN","0","1","72.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("167","56","0","","15","","9","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("168","55","0","","15","","9","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("169","128","0","","15","","9","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("170","133","0","","15","","9","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("171","130","0","","15","","9","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("172","25","0","","15","","9","0","0","GRN","0","1","144.00","IN","0","","","2026-08-17","","","2026-08-17 17:27:49","0");
INSERT INTO kp_stock_track VALUES("173","128","0","","16","","10","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:33:10","0");
INSERT INTO kp_stock_track VALUES("174","69","0","","16","","10","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 17:33:10","0");
INSERT INTO kp_stock_track VALUES("175","292","0","","16","","10","0","0","GRN","0","1","24.00","IN","0","","","2026-08-17","","","2026-08-17 17:33:10","0");
INSERT INTO kp_stock_track VALUES("176","55","0","","16","","10","0","0","GRN","0","1","48.00","IN","0","","","2026-08-17","","","2026-08-17 17:33:10","0");
INSERT INTO kp_stock_track VALUES("177","144","0","","17","","11","0","0","GRN","0","1","720.00","IN","0","","","2026-08-17","","","2026-08-17 17:36:59","0");
INSERT INTO kp_stock_track VALUES("181","5","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:41","0");
INSERT INTO kp_stock_track VALUES("182","6","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:41","0");
INSERT INTO kp_stock_track VALUES("183","85","0","","18","","12","0","0","GRN","0","1","12.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:41","0");
INSERT INTO kp_stock_track VALUES("184","7","0","","18","","12","0","0","GRN","0","1","300.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:41","0");
INSERT INTO kp_stock_track VALUES("185","142","0","","18","","12","0","0","GRN","0","1","48.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:41","0");
INSERT INTO kp_stock_track VALUES("186","136","0","","18","","12","0","0","GRN","0","1","25.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("187","8","0","","18","","12","0","0","GRN","0","1","50.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("188","141","0","","18","","12","0","0","GRN","0","1","144.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("189","98","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("190","132","0","","18","","12","0","0","GRN","0","1","50.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("191","19","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("192","70","0","","18","","12","0","0","GRN","0","1","96.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("193","126","0","","18","","12","0","0","GRN","0","1","575.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("194","121","0","","18","","12","0","0","GRN","0","1","350.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("195","10","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("196","119","0","","18","","12","0","0","GRN","0","1","75.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("197","124","0","","18","","12","0","0","GRN","0","1","175.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("198","71","0","","18","","12","0","0","GRN","0","1","425.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("199","123","0","","18","","12","0","0","GRN","0","1","275.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("200","14","0","","18","","12","0","0","GRN","0","1","72.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("201","15","0","","18","","12","0","0","GRN","0","1","48.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("202","86","0","","18","","12","0","0","GRN","0","1","50.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("203","120","0","","18","","12","0","0","GRN","0","1","750.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("204","16","0","","18","","12","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("205","18","0","","18","","12","0","0","GRN","0","1","2.00","IN","0","","","2026-08-19","","","2026-08-19 14:04:42","0");
INSERT INTO kp_stock_track VALUES("206","320","0","","19","","13","0","0","GRN","0","1","3.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("207","196","0","","19","","13","0","0","GRN","0","1","6.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("208","32","0","","19","","13","0","0","GRN","0","1","3.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("209","29","0","","19","","13","0","0","GRN","0","1","3.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("210","36","0","","19","","13","0","0","GRN","0","1","6.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("211","215","0","","19","","13","0","0","GRN","0","1","2.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("212","111","0","","19","","13","0","0","GRN","0","1","48.00","IN","0","","","2026-08-19","","","2026-08-19 14:34:57","0");
INSERT INTO kp_stock_track VALUES("213","22","0","","20","","14","0","0","GRN","0","1","240.00","IN","0","","","2026-08-19","","","2026-08-19 14:48:17","0");
INSERT INTO kp_stock_track VALUES("214","104","0","","20","","14","0","0","GRN","0","1","24.00","IN","0","","","2026-08-19","","","2026-08-19 14:48:17","0");
INSERT INTO kp_stock_track VALUES("215","76","0","","20","","14","0","0","GRN","0","1","12.00","IN","0","","","2026-08-19","","","2026-08-19 14:48:17","0");
INSERT INTO kp_stock_track VALUES("216","89","0","","21","","15","0","0","GRN","0","1","24.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("217","111","0","","21","","15","0","0","GRN","0","1","24.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("218","34","0","","21","","15","0","0","GRN","0","1","2.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("219","303","0","","21","","15","0","0","GRN","0","1","12.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("220","156","0","","21","","15","0","0","GRN","0","1","6.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("221","233","0","","21","","15","0","0","GRN","0","1","3.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("222","161","0","","21","","15","0","0","GRN","0","1","2.00","IN","0","","","2026-08-22","","","2026-08-22 22:08:33","0");
INSERT INTO kp_stock_track VALUES("223","23","0","","22","","16","0","0","GRN","0","1","180.00","IN","0","","","2026-08-22","","","2026-08-22 22:11:39","0");
INSERT INTO kp_stock_track VALUES("224","22","0","","22","","16","0","0","GRN","0","1","216.00","IN","0","","","2026-08-22","","","2026-08-22 22:11:39","0");
INSERT INTO kp_stock_track VALUES("225","56","0","","23","","17","0","0","GRN","0","1","48.00","IN","0","","","2026-08-22","","","2026-08-22 22:20:10","0");
INSERT INTO kp_stock_track VALUES("226","55","0","","23","","17","0","0","GRN","0","1","48.00","IN","0","","","2026-08-22","","","2026-08-22 22:20:10","0");
INSERT INTO kp_stock_track VALUES("227","129","0","","23","","17","0","0","GRN","0","1","24.00","IN","0","","","2026-08-22","","","2026-08-22 22:20:10","0");
INSERT INTO kp_stock_track VALUES("228","130","0","","23","","17","0","0","GRN","0","1","24.00","IN","0","","","2026-08-22","","","2026-08-22 22:20:10","0");
INSERT INTO kp_stock_track VALUES("229","25","0","","23","","17","0","0","GRN","0","1","120.00","IN","0","","","2026-08-22","","","2026-08-22 22:20:10","0");
INSERT INTO kp_stock_track VALUES("230","85","0","","25","","18","0","0","GRN","0","1","12.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("231","7","0","","25","","18","0","0","GRN","0","1","225.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("232","136","0","","25","","18","0","0","GRN","0","1","25.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("233","8","0","","25","","18","0","0","GRN","0","1","50.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("234","141","0","","25","","18","0","0","GRN","0","1","48.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("235","98","0","","25","","18","0","0","GRN","0","1","24.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("236","132","0","","25","","18","0","0","GRN","0","1","75.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("237","70","0","","25","","18","0","0","GRN","0","1","48.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("238","127","0","","25","","18","0","0","GRN","0","1","50.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("239","126","0","","25","","18","0","0","GRN","0","1","400.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("240","121","0","","25","","18","0","0","GRN","0","1","300.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("241","119","0","","25","","18","0","0","GRN","0","1","25.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("242","124","0","","25","","18","0","0","GRN","0","1","150.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("243","71","0","","25","","18","0","0","GRN","0","1","325.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("244","123","0","","25","","18","0","0","GRN","0","1","125.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("245","14","0","","25","","18","0","0","GRN","0","1","48.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("246","86","0","","25","","18","0","0","GRN","0","1","75.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("247","120","0","","25","","18","0","0","GRN","0","1","750.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("248","16","0","","25","","18","0","0","GRN","0","1","24.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("249","37","0","","25","","18","0","0","GRN","0","1","48.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("250","87","0","","25","","18","0","0","GRN","0","1","12.00","IN","0","","","2026-08-24","","","2026-08-24 00:28:25","0");
INSERT INTO kp_stock_track VALUES("251","144","0","","26","","19","0","0","GRN","0","1","720.00","IN","0","","","2026-08-24","","","2026-08-24 00:30:31","0");
INSERT INTO kp_stock_track VALUES("252","105","0","","27","","20","0","0","GRN","0","1","24.00","IN","0","","","2026-08-24","","","2026-08-24 00:33:56","0");
INSERT INTO kp_stock_track VALUES("253","69","0","","27","","20","0","0","GRN","0","1","72.00","IN","0","","","2026-08-24","","","2026-08-24 00:33:56","0");
INSERT INTO kp_stock_track VALUES("254","292","0","","27","","20","0","0","GRN","0","1","38.00","IN","0","","","2026-08-24","","","2026-08-24 00:33:56","0");
INSERT INTO kp_stock_track VALUES("255","128","0","","27","","20","0","0","GRN","0","1","48.00","IN","0","","","2026-08-24","","","2026-08-24 00:33:56","0");
INSERT INTO kp_stock_track VALUES("256","119","0","","28","","21","0","0","GRN","0","1","100.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("257","136","0","","28","","21","0","0","GRN","0","1","25.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("258","9","0","","28","","21","0","0","GRN","0","1","50.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("259","127","0","","28","","21","0","0","GRN","0","1","50.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("260","123","0","","28","","21","0","0","GRN","0","1","175.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("261","14","0","","28","","21","0","0","GRN","0","1","72.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("262","86","0","","28","","21","0","0","GRN","0","1","75.00","IN","0","","","2026-08-24","","","2026-08-24 00:38:21","0");
INSERT INTO kp_stock_track VALUES("263","291","0","1","","","0","0","0","","0","4","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 16:19:59","0");
INSERT INTO kp_stock_track VALUES("264","126","0","3","","","0","0","0","","0","3","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 17:08:48","0");
INSERT INTO kp_stock_track VALUES("272","144","0","4","","","0","0","0","","0","6","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:18:40","0");
INSERT INTO kp_stock_track VALUES("273","143","0","4","","","0","0","0","","0","6","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:18:54","0");
INSERT INTO kp_stock_track VALUES("274","17","0","4","","","0","0","0","","0","6","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:19:22","0");
INSERT INTO kp_stock_track VALUES("275","22","0","5","","","0","0","0","","0","7","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:28:18","0");
INSERT INTO kp_stock_track VALUES("276","98","0","7","","","0","0","0","","0","9","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:50:12","0");
INSERT INTO kp_stock_track VALUES("281","23","0","7","","","0","0","0","","0","9","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:51:17","0");
INSERT INTO kp_stock_track VALUES("282","7","0","6","","","0","0","0","","0","8","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 19:59:53","0");
INSERT INTO kp_stock_track VALUES("283","71","0","8","","","0","0","0","","0","3","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:17:19","0");
INSERT INTO kp_stock_track VALUES("284","126","0","9","","","0","0","0","","0","6","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:20:08","0");
INSERT INTO kp_stock_track VALUES("285","142","0","9","","","0","0","0","","0","6","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:20:53","0");
INSERT INTO kp_stock_track VALUES("286","69","0","10","","","0","0","0","","0","6","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:21:42","0");
INSERT INTO kp_stock_track VALUES("287","126","0","11","","","0","0","0","","0","11","4.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:28:34","0");
INSERT INTO kp_stock_track VALUES("292","123","0","12","","","0","0","0","","0","9","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:52:57","0");
INSERT INTO kp_stock_track VALUES("293","19","0","13","","","0","0","0","","0","8","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:55:07","0");
INSERT INTO kp_stock_track VALUES("294","120","0","14","","","0","0","0","","0","8","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:56:32","0");
INSERT INTO kp_stock_track VALUES("295","121","0","15","","","0","0","0","","0","3","4.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:58:00","0");
INSERT INTO kp_stock_track VALUES("296","8","0","16","","","0","0","0","","0","8","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 21:58:57","0");
INSERT INTO kp_stock_track VALUES("297","126","0","18","","","0","0","0","","0","3","7.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:11:37","0");
INSERT INTO kp_stock_track VALUES("298","190","0","19","","","0","0","0","","0","3","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:13:08","0");
INSERT INTO kp_stock_track VALUES("299","36","0","17","","","0","0","0","","0","6","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:16:21","0");
INSERT INTO kp_stock_track VALUES("300","23","0","17","","","0","0","0","","0","6","2.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:16:37","0");
INSERT INTO kp_stock_track VALUES("301","22","0","17","","","0","0","0","","0","6","1.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:17:07","0");
INSERT INTO kp_stock_track VALUES("302","15","0","20","","","0","0","0","","0","11","4.00","added","1","1","2026-08-24","0000-00-00","1","1","2026-08-24 23:18:49","0");





CREATE TABLE `kp_store_closing_stock` (
  `item_id` int NOT NULL,
  `qty` int NOT NULL,
  `shift` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_store_closing_stock VALUES("1","10","");
INSERT INTO kp_store_closing_stock VALUES("2","27","");
INSERT INTO kp_store_closing_stock VALUES("3","35","");
INSERT INTO kp_store_closing_stock VALUES("4","84","");
INSERT INTO kp_store_closing_stock VALUES("5","0","");
INSERT INTO kp_store_closing_stock VALUES("6","0","");
INSERT INTO kp_store_closing_stock VALUES("7","0","");
INSERT INTO kp_store_closing_stock VALUES("8","0","");
INSERT INTO kp_store_closing_stock VALUES("9","0","");
INSERT INTO kp_store_closing_stock VALUES("10","48","");
INSERT INTO kp_store_closing_stock VALUES("11","23","");
INSERT INTO kp_store_closing_stock VALUES("12","48","");
INSERT INTO kp_store_closing_stock VALUES("13","66","");
INSERT INTO kp_store_closing_stock VALUES("14","30","");
INSERT INTO kp_store_closing_stock VALUES("15","24","");
INSERT INTO kp_store_closing_stock VALUES("16","30","");
INSERT INTO kp_store_closing_stock VALUES("17","50","");
INSERT INTO kp_store_closing_stock VALUES("18","0","");
INSERT INTO kp_store_closing_stock VALUES("19","0","");
INSERT INTO kp_store_closing_stock VALUES("22","312","");
INSERT INTO kp_store_closing_stock VALUES("23","216","");
INSERT INTO kp_store_closing_stock VALUES("25","36","");
INSERT INTO kp_store_closing_stock VALUES("27","9","");
INSERT INTO kp_store_closing_stock VALUES("28","12","");
INSERT INTO kp_store_closing_stock VALUES("29","3","");
INSERT INTO kp_store_closing_stock VALUES("30","9","");
INSERT INTO kp_store_closing_stock VALUES("31","1","");
INSERT INTO kp_store_closing_stock VALUES("32","6","");
INSERT INTO kp_store_closing_stock VALUES("33","7","");
INSERT INTO kp_store_closing_stock VALUES("34","7","");
INSERT INTO kp_store_closing_stock VALUES("36","10","");
INSERT INTO kp_store_closing_stock VALUES("37","50","");
INSERT INTO kp_store_closing_stock VALUES("39","17","");
INSERT INTO kp_store_closing_stock VALUES("40","11","");
INSERT INTO kp_store_closing_stock VALUES("41","10","");
INSERT INTO kp_store_closing_stock VALUES("48","18","");
INSERT INTO kp_store_closing_stock VALUES("51","12","");
INSERT INTO kp_store_closing_stock VALUES("52","18","");
INSERT INTO kp_store_closing_stock VALUES("53","18","");
INSERT INTO kp_store_closing_stock VALUES("55","84","");
INSERT INTO kp_store_closing_stock VALUES("56","36","");
INSERT INTO kp_store_closing_stock VALUES("57","4","");
INSERT INTO kp_store_closing_stock VALUES("58","0","");
INSERT INTO kp_store_closing_stock VALUES("59","36","");
INSERT INTO kp_store_closing_stock VALUES("60","5","");
INSERT INTO kp_store_closing_stock VALUES("64","4","");
INSERT INTO kp_store_closing_stock VALUES("66","0","");
INSERT INTO kp_store_closing_stock VALUES("67","20","");
INSERT INTO kp_store_closing_stock VALUES("69","48","");
INSERT INTO kp_store_closing_stock VALUES("70","96","");
INSERT INTO kp_store_closing_stock VALUES("71","250","");
INSERT INTO kp_store_closing_stock VALUES("72","18","");
INSERT INTO kp_store_closing_stock VALUES("73","1","");
INSERT INTO kp_store_closing_stock VALUES("74","11","");
INSERT INTO kp_store_closing_stock VALUES("75","0","");
INSERT INTO kp_store_closing_stock VALUES("76","0","");
INSERT INTO kp_store_closing_stock VALUES("77","161","");
INSERT INTO kp_store_closing_stock VALUES("78","0","");
INSERT INTO kp_store_closing_stock VALUES("82","3","");
INSERT INTO kp_store_closing_stock VALUES("83","48","");
INSERT INTO kp_store_closing_stock VALUES("84","0","");
INSERT INTO kp_store_closing_stock VALUES("85","73","");
INSERT INTO kp_store_closing_stock VALUES("86","250","");
INSERT INTO kp_store_closing_stock VALUES("87","0","");
INSERT INTO kp_store_closing_stock VALUES("89","24","");
INSERT INTO kp_store_closing_stock VALUES("92","0","");
INSERT INTO kp_store_closing_stock VALUES("93","0","");
INSERT INTO kp_store_closing_stock VALUES("94","0","");
INSERT INTO kp_store_closing_stock VALUES("95","14","");
INSERT INTO kp_store_closing_stock VALUES("96","2","");
INSERT INTO kp_store_closing_stock VALUES("97","0","");
INSERT INTO kp_store_closing_stock VALUES("98","36","");
INSERT INTO kp_store_closing_stock VALUES("99","25","");
INSERT INTO kp_store_closing_stock VALUES("100","18","");
INSERT INTO kp_store_closing_stock VALUES("101","0","");
INSERT INTO kp_store_closing_stock VALUES("103","0","");
INSERT INTO kp_store_closing_stock VALUES("104","24","");
INSERT INTO kp_store_closing_stock VALUES("105","24","");
INSERT INTO kp_store_closing_stock VALUES("107","1","");
INSERT INTO kp_store_closing_stock VALUES("108","36","");
INSERT INTO kp_store_closing_stock VALUES("109","0","");
INSERT INTO kp_store_closing_stock VALUES("111","24","");
INSERT INTO kp_store_closing_stock VALUES("112","6","");
INSERT INTO kp_store_closing_stock VALUES("113","0","");
INSERT INTO kp_store_closing_stock VALUES("114","30","");
INSERT INTO kp_store_closing_stock VALUES("115","24","");
INSERT INTO kp_store_closing_stock VALUES("117","1","");
INSERT INTO kp_store_closing_stock VALUES("119","250","");
INSERT INTO kp_store_closing_stock VALUES("120","50","");
INSERT INTO kp_store_closing_stock VALUES("121","25","");
INSERT INTO kp_store_closing_stock VALUES("122","0","");
INSERT INTO kp_store_closing_stock VALUES("123","100","");
INSERT INTO kp_store_closing_stock VALUES("124","75","");
INSERT INTO kp_store_closing_stock VALUES("125","0","");
INSERT INTO kp_store_closing_stock VALUES("126","100","");
INSERT INTO kp_store_closing_stock VALUES("127","50","");
INSERT INTO kp_store_closing_stock VALUES("128","96","");
INSERT INTO kp_store_closing_stock VALUES("129","24","");
INSERT INTO kp_store_closing_stock VALUES("130","0","");
INSERT INTO kp_store_closing_stock VALUES("131","75","");
INSERT INTO kp_store_closing_stock VALUES("132","0","");
INSERT INTO kp_store_closing_stock VALUES("133","0","");
INSERT INTO kp_store_closing_stock VALUES("134","24","");
INSERT INTO kp_store_closing_stock VALUES("135","20","");
INSERT INTO kp_store_closing_stock VALUES("136","50","");
INSERT INTO kp_store_closing_stock VALUES("137","0","");
INSERT INTO kp_store_closing_stock VALUES("138","24","");
INSERT INTO kp_store_closing_stock VALUES("139","24","");
INSERT INTO kp_store_closing_stock VALUES("140","0","");
INSERT INTO kp_store_closing_stock VALUES("141","168","");
INSERT INTO kp_store_closing_stock VALUES("142","36","");
INSERT INTO kp_store_closing_stock VALUES("143","24","");
INSERT INTO kp_store_closing_stock VALUES("144","276","");
INSERT INTO kp_store_closing_stock VALUES("145","0","");
INSERT INTO kp_store_closing_stock VALUES("146","3","");
INSERT INTO kp_store_closing_stock VALUES("147","1","");
INSERT INTO kp_store_closing_stock VALUES("148","24","");
INSERT INTO kp_store_closing_stock VALUES("149","0","");
INSERT INTO kp_store_closing_stock VALUES("150","25","");
INSERT INTO kp_store_closing_stock VALUES("151","20","");
INSERT INTO kp_store_closing_stock VALUES("152","16","");
INSERT INTO kp_store_closing_stock VALUES("153","12","");
INSERT INTO kp_store_closing_stock VALUES("154","0","");
INSERT INTO kp_store_closing_stock VALUES("155","1","");
INSERT INTO kp_store_closing_stock VALUES("156","14","");
INSERT INTO kp_store_closing_stock VALUES("157","32","");
INSERT INTO kp_store_closing_stock VALUES("158","79","");
INSERT INTO kp_store_closing_stock VALUES("159","1","");
INSERT INTO kp_store_closing_stock VALUES("160","1","");
INSERT INTO kp_store_closing_stock VALUES("161","6","");
INSERT INTO kp_store_closing_stock VALUES("162","5","");
INSERT INTO kp_store_closing_stock VALUES("163","1","");
INSERT INTO kp_store_closing_stock VALUES("164","2","");
INSERT INTO kp_store_closing_stock VALUES("165","2","");
INSERT INTO kp_store_closing_stock VALUES("166","2","");
INSERT INTO kp_store_closing_stock VALUES("167","4","");
INSERT INTO kp_store_closing_stock VALUES("168","2","");
INSERT INTO kp_store_closing_stock VALUES("169","0","");
INSERT INTO kp_store_closing_stock VALUES("170","3","");
INSERT INTO kp_store_closing_stock VALUES("171","10","");
INSERT INTO kp_store_closing_stock VALUES("172","3","");
INSERT INTO kp_store_closing_stock VALUES("173","3","");
INSERT INTO kp_store_closing_stock VALUES("174","0","");
INSERT INTO kp_store_closing_stock VALUES("175","0","");
INSERT INTO kp_store_closing_stock VALUES("176","4","");
INSERT INTO kp_store_closing_stock VALUES("177","1","");
INSERT INTO kp_store_closing_stock VALUES("178","10","");
INSERT INTO kp_store_closing_stock VALUES("179","8","");
INSERT INTO kp_store_closing_stock VALUES("180","54","");
INSERT INTO kp_store_closing_stock VALUES("181","38","");
INSERT INTO kp_store_closing_stock VALUES("182","6","");
INSERT INTO kp_store_closing_stock VALUES("183","0","");
INSERT INTO kp_store_closing_stock VALUES("184","0","");
INSERT INTO kp_store_closing_stock VALUES("185","2","");
INSERT INTO kp_store_closing_stock VALUES("186","5","");
INSERT INTO kp_store_closing_stock VALUES("187","26","");
INSERT INTO kp_store_closing_stock VALUES("188","18","");
INSERT INTO kp_store_closing_stock VALUES("189","3","");
INSERT INTO kp_store_closing_stock VALUES("190","25","");
INSERT INTO kp_store_closing_stock VALUES("191","1","");
INSERT INTO kp_store_closing_stock VALUES("192","1","");
INSERT INTO kp_store_closing_stock VALUES("193","17","");
INSERT INTO kp_store_closing_stock VALUES("194","1","");
INSERT INTO kp_store_closing_stock VALUES("195","0","");
INSERT INTO kp_store_closing_stock VALUES("196","15","");
INSERT INTO kp_store_closing_stock VALUES("197","8","");
INSERT INTO kp_store_closing_stock VALUES("198","9","");
INSERT INTO kp_store_closing_stock VALUES("199","7","");
INSERT INTO kp_store_closing_stock VALUES("200","63","");
INSERT INTO kp_store_closing_stock VALUES("201","7","");
INSERT INTO kp_store_closing_stock VALUES("202","5","");
INSERT INTO kp_store_closing_stock VALUES("203","2","");
INSERT INTO kp_store_closing_stock VALUES("204","2","");
INSERT INTO kp_store_closing_stock VALUES("205","5","");
INSERT INTO kp_store_closing_stock VALUES("206","2","");
INSERT INTO kp_store_closing_stock VALUES("207","7","");
INSERT INTO kp_store_closing_stock VALUES("208","0","");
INSERT INTO kp_store_closing_stock VALUES("209","0","");
INSERT INTO kp_store_closing_stock VALUES("210","2","");
INSERT INTO kp_store_closing_stock VALUES("211","5","");
INSERT INTO kp_store_closing_stock VALUES("212","2","");
INSERT INTO kp_store_closing_stock VALUES("213","0","");
INSERT INTO kp_store_closing_stock VALUES("214","1","");
INSERT INTO kp_store_closing_stock VALUES("215","2","");
INSERT INTO kp_store_closing_stock VALUES("216","10","");
INSERT INTO kp_store_closing_stock VALUES("217","2","");
INSERT INTO kp_store_closing_stock VALUES("218","5","");
INSERT INTO kp_store_closing_stock VALUES("219","29","");
INSERT INTO kp_store_closing_stock VALUES("220","13","");
INSERT INTO kp_store_closing_stock VALUES("221","0","");
INSERT INTO kp_store_closing_stock VALUES("222","0","");
INSERT INTO kp_store_closing_stock VALUES("223","4","");
INSERT INTO kp_store_closing_stock VALUES("224","3","");
INSERT INTO kp_store_closing_stock VALUES("225","3","");
INSERT INTO kp_store_closing_stock VALUES("226","0","");
INSERT INTO kp_store_closing_stock VALUES("227","0","");
INSERT INTO kp_store_closing_stock VALUES("228","0","");
INSERT INTO kp_store_closing_stock VALUES("229","3","");
INSERT INTO kp_store_closing_stock VALUES("230","4","");
INSERT INTO kp_store_closing_stock VALUES("231","4","");
INSERT INTO kp_store_closing_stock VALUES("232","3","");
INSERT INTO kp_store_closing_stock VALUES("233","1","");
INSERT INTO kp_store_closing_stock VALUES("234","4","");
INSERT INTO kp_store_closing_stock VALUES("235","0","");
INSERT INTO kp_store_closing_stock VALUES("236","2","");
INSERT INTO kp_store_closing_stock VALUES("237","1","");
INSERT INTO kp_store_closing_stock VALUES("238","1","");
INSERT INTO kp_store_closing_stock VALUES("239","4","");
INSERT INTO kp_store_closing_stock VALUES("240","12","");
INSERT INTO kp_store_closing_stock VALUES("241","17","");
INSERT INTO kp_store_closing_stock VALUES("242","63","");
INSERT INTO kp_store_closing_stock VALUES("243","0","");
INSERT INTO kp_store_closing_stock VALUES("244","0","");
INSERT INTO kp_store_closing_stock VALUES("245","1","");
INSERT INTO kp_store_closing_stock VALUES("246","73","");
INSERT INTO kp_store_closing_stock VALUES("247","60","");
INSERT INTO kp_store_closing_stock VALUES("248","9","");
INSERT INTO kp_store_closing_stock VALUES("249","0","");
INSERT INTO kp_store_closing_stock VALUES("250","3","");
INSERT INTO kp_store_closing_stock VALUES("251","3","");
INSERT INTO kp_store_closing_stock VALUES("252","1","");
INSERT INTO kp_store_closing_stock VALUES("253","0","");
INSERT INTO kp_store_closing_stock VALUES("254","1","");
INSERT INTO kp_store_closing_stock VALUES("255","0","");
INSERT INTO kp_store_closing_stock VALUES("256","0","");
INSERT INTO kp_store_closing_stock VALUES("257","1","");
INSERT INTO kp_store_closing_stock VALUES("258","1","");
INSERT INTO kp_store_closing_stock VALUES("259","0","");
INSERT INTO kp_store_closing_stock VALUES("260","0","");
INSERT INTO kp_store_closing_stock VALUES("261","0","");
INSERT INTO kp_store_closing_stock VALUES("262","0","");
INSERT INTO kp_store_closing_stock VALUES("263","0","");
INSERT INTO kp_store_closing_stock VALUES("264","0","");
INSERT INTO kp_store_closing_stock VALUES("265","0","");
INSERT INTO kp_store_closing_stock VALUES("266","0","");
INSERT INTO kp_store_closing_stock VALUES("267","0","");
INSERT INTO kp_store_closing_stock VALUES("268","0","");
INSERT INTO kp_store_closing_stock VALUES("269","0","");
INSERT INTO kp_store_closing_stock VALUES("270","0","");
INSERT INTO kp_store_closing_stock VALUES("271","0","");
INSERT INTO kp_store_closing_stock VALUES("272","0","");
INSERT INTO kp_store_closing_stock VALUES("273","0","");
INSERT INTO kp_store_closing_stock VALUES("274","0","");
INSERT INTO kp_store_closing_stock VALUES("275","0","");
INSERT INTO kp_store_closing_stock VALUES("276","0","");
INSERT INTO kp_store_closing_stock VALUES("277","0","");
INSERT INTO kp_store_closing_stock VALUES("278","0","");
INSERT INTO kp_store_closing_stock VALUES("279","0","");
INSERT INTO kp_store_closing_stock VALUES("280","0","");
INSERT INTO kp_store_closing_stock VALUES("281","0","");
INSERT INTO kp_store_closing_stock VALUES("282","0","");
INSERT INTO kp_store_closing_stock VALUES("283","0","");
INSERT INTO kp_store_closing_stock VALUES("284","0","");
INSERT INTO kp_store_closing_stock VALUES("285","0","");
INSERT INTO kp_store_closing_stock VALUES("286","0","");
INSERT INTO kp_store_closing_stock VALUES("287","0","");
INSERT INTO kp_store_closing_stock VALUES("288","0","");
INSERT INTO kp_store_closing_stock VALUES("289","0","");
INSERT INTO kp_store_closing_stock VALUES("290","0","");
INSERT INTO kp_store_closing_stock VALUES("291","0","");





CREATE TABLE `kp_store_hist` (
  `item_id` int NOT NULL,
  `user_id` int NOT NULL,
  `init` int NOT NULL,
  `new` int NOT NULL,
  `reason` varchar(200) NOT NULL,
  `trans` varchar(55) NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_store_hist VALUES("747","1","0","14"," New arrival","STORE STOCK ADJUSTMENTS","2025-09-05 20:30:30");
INSERT INTO kp_store_hist VALUES("1013","1","0","5"," New product","STORE STOCK ADJUSTMENTS","2025-09-07 17:28:06");
INSERT INTO kp_store_hist VALUES("747","1","0","11"," New arrivals","STORE STOCK ADJUSTMENTS","2025-09-07 17:28:39");
INSERT INTO kp_store_hist VALUES("747","1","0","19"," New intake","STORE STOCK ADJUSTMENTS","2025-09-10 16:34:23");
INSERT INTO kp_store_hist VALUES("747","1","0","19"," New stock intake","STORE STOCK ADJUSTMENTS","2025-09-10 17:32:18");
INSERT INTO kp_store_hist VALUES("747","1","0","20"," New arrival","STORE STOCK ADJUSTMENTS","2025-09-11 10:07:49");
INSERT INTO kp_store_hist VALUES("747","0","1","16"," New arrivals","STORE STOCK ADJUSTMENTS","2025-09-11 15:30:30");
INSERT INTO kp_store_hist VALUES("1004","1","0","3"," New arrival","STORE STOCK ADJUSTMENTS","2025-09-13 12:05:51");
INSERT INTO kp_store_hist VALUES("747","0","1","11"," New arrivals","STORE STOCK ADJUSTMENTS","2025-09-13 13:58:16");
INSERT INTO kp_store_hist VALUES("747","1","1","16"," New","STORE STOCK ADJUSTMENTS","2025-09-14 11:51:20");
INSERT INTO kp_store_hist VALUES("2","1","192","201"," Adjust","STORE STOCK ADJUSTMENTS","2025-09-16 12:33:56");
INSERT INTO kp_store_hist VALUES("747","1","0","19"," Adjust","STORE STOCK ADJUSTMENTS","2025-09-16 12:34:11");
INSERT INTO kp_store_hist VALUES("2","1","0","25"," New arrivals","STORE STOCK ADJUSTMENTS","2025-09-20 12:09:04");
INSERT INTO kp_store_hist VALUES("747","1","0","17"," New arrivals","STORE STOCK ADJUSTMENTS","2025-09-20 12:10:30");
INSERT INTO kp_store_hist VALUES("747","1","0","14"," Arrival","STORE STOCK ADJUSTMENTS","2025-09-20 12:11:14");
INSERT INTO kp_store_hist VALUES("747","1","14","16"," Miscount","STORE STOCK ADJUSTMENTS","2025-09-21 11:33:46");
INSERT INTO kp_store_hist VALUES("747","0","0","21"," Freshly made","STORE STOCK ADJUSTMENTS","2025-09-22 12:57:46");
INSERT INTO kp_store_hist VALUES("2","1","0","150"," New","STORE STOCK ADJUSTMENTS","2025-09-23 11:00:18");
INSERT INTO kp_store_hist VALUES("1046","1","1","4"," New","STORE STOCK ADJUSTMENTS","2025-09-23 11:02:36");
INSERT INTO kp_store_hist VALUES("747","1","0","6"," New","STORE STOCK ADJUSTMENTS","2025-09-25 14:37:58");
INSERT INTO kp_store_hist VALUES("491","1","3","18"," oUT OF STOCK","STORE STOCK ADJUSTMENTS","2025-09-26 10:04:55");
INSERT INTO kp_store_hist VALUES("747","1","0","19","Added","STORE STOCK ADJUSTMENTS","2025-09-27 10:58:30");
INSERT INTO kp_store_hist VALUES("1021","1","3","4"," New Stock","STORE STOCK ADJUSTMENTS","2025-09-27 10:58:51");
INSERT INTO kp_store_hist VALUES("855","1","0","16"," New stock","STORE STOCK ADJUSTMENTS","2025-09-27 10:59:14");
INSERT INTO kp_store_hist VALUES("2","1","0","50"," new stock","STORE STOCK ADJUSTMENTS","2025-09-29 11:08:38");
INSERT INTO kp_store_hist VALUES("491","1","18","24"," new stock","STORE STOCK ADJUSTMENTS","2025-09-29 11:09:03");
INSERT INTO kp_store_hist VALUES("747","1","0","16"," new stock","STORE STOCK ADJUSTMENTS","2025-09-29 11:10:13");
INSERT INTO kp_store_hist VALUES("462","1","1","3"," New stock","STORE STOCK ADJUSTMENTS","2025-09-30 08:36:49");
INSERT INTO kp_store_hist VALUES("2","1","0","50"," Wrongly counted","STORE STOCK ADJUSTMENTS","2025-10-01 09:45:12");
INSERT INTO kp_store_hist VALUES("747","1","0","21"," New stock","STORE STOCK ADJUSTMENTS","2025-10-02 12:36:46");
INSERT INTO kp_store_hist VALUES("747","1","0","1000"," New stock","STORE STOCK ADJUSTMENTS","2025-10-05 12:10:39");
INSERT INTO kp_store_hist VALUES("2","1","0","100"," News stock","STORE STOCK ADJUSTMENTS","2025-10-06 11:15:52");
INSERT INTO kp_store_hist VALUES("1004","1","0","56","New stock","STORE STOCK ADJUSTMENTS","2025-10-06 11:17:29");
INSERT INTO kp_store_hist VALUES("1021","1","0","18"," New orders","STORE STOCK ADJUSTMENTS","2025-10-06 13:50:16");
INSERT INTO kp_store_hist VALUES("855","1","0","200"," New order","STORE STOCK ADJUSTMENTS","2025-10-06 13:51:57");
INSERT INTO kp_store_hist VALUES("2","1","0","2000"," New Stock","STORE STOCK ADJUSTMENTS","2025-10-11 12:37:08");
INSERT INTO kp_store_hist VALUES("491","1","0","300"," New Stock","STORE STOCK ADJUSTMENTS","2025-10-11 12:37:56");
INSERT INTO kp_store_hist VALUES("1003","0","0","100"," New Stock","STORE STOCK ADJUSTMENTS","2025-10-18 11:59:34");
INSERT INTO kp_store_hist VALUES("1063","0","0","200"," New stock","STORE STOCK ADJUSTMENTS","2025-10-27 14:00:16");
INSERT INTO kp_store_hist VALUES("869","10","2","200"," New stock","STORE STOCK ADJUSTMENTS","2025-11-03 14:54:57");
INSERT INTO kp_store_hist VALUES("1002","10","0","67"," New stock","STORE STOCK ADJUSTMENTS","2025-11-03 14:55:57");
INSERT INTO kp_store_hist VALUES("878","10","16","1236","New Stock","STORE STOCK ADJUSTMENTS","2025-11-06 10:11:00");
INSERT INTO kp_store_hist VALUES("1022","10","0","2456","New stock","STORE STOCK ADJUSTMENTS","2025-11-09 13:07:11");
INSERT INTO kp_store_hist VALUES("2","10","170","3456","New stock","STORE STOCK ADJUSTMENTS","2025-11-26 12:34:50");
INSERT INTO kp_store_hist VALUES("1018","10","0","1909"," New stock","STORE STOCK ADJUSTMENTS","2025-12-08 13:01:20");
INSERT INTO kp_store_hist VALUES("16","10","100","11000"," Restocked","STORE STOCK ADJUSTMENTS","2025-12-15 12:04:41");
INSERT INTO kp_store_hist VALUES("1021","10","3","3343"," New arrival","STORE STOCK ADJUSTMENTS","2025-12-18 11:42:53");
INSERT INTO kp_store_hist VALUES("488","10","3","3890"," Extra added","STORE STOCK ADJUSTMENTS","2025-12-19 10:33:25");
INSERT INTO kp_store_hist VALUES("1046","10","0","126"," New Stock","STORE STOCK ADJUSTMENTS","2025-12-19 12:16:20");
INSERT INTO kp_store_hist VALUES("1013","10","0","4535"," New Stock","STORE STOCK ADJUSTMENTS","2025-12-19 12:16:35");
INSERT INTO kp_store_hist VALUES("1028","10","1","1897"," New Stock","STORE STOCK ADJUSTMENTS","2025-12-19 12:16:50");
INSERT INTO kp_store_hist VALUES("747","10","36","3986"," New arrival","STORE STOCK ADJUSTMENTS","2025-12-20 14:16:12");
INSERT INTO kp_store_hist VALUES("491","10","8","7868"," New  purchase","STORE STOCK ADJUSTMENTS","2025-12-22 12:58:24");
INSERT INTO kp_store_hist VALUES("869","10","38","38"," 6562","STORE STOCK ADJUSTMENTS","2025-12-22 12:58:38");
INSERT INTO kp_store_hist VALUES("869","10","38","3768"," Newstock","STORE STOCK ADJUSTMENTS","2025-12-22 12:58:52");
INSERT INTO kp_store_hist VALUES("855","10","0","9000"," New stock in","STORE STOCK ADJUSTMENTS","2026-01-23 12:28:13");
INSERT INTO kp_store_hist VALUES("2","10","42","4200"," New purchase","STORE STOCK ADJUSTMENTS","2026-01-28 11:40:50");
INSERT INTO kp_store_hist VALUES("506","10","31","3198"," New stock","STORE STOCK ADJUSTMENTS","2026-02-21 12:57:57");
INSERT INTO kp_store_hist VALUES("12","10","23","2300"," New Stock","STORE STOCK ADJUSTMENTS","2026-03-07 13:04:34");
INSERT INTO kp_store_hist VALUES("504","10","34","3489"," New stock","STORE STOCK ADJUSTMENTS","2026-03-24 13:53:22");
INSERT INTO kp_store_hist VALUES("2","10","17","5417"," New order","STORE STOCK ADJUSTMENTS","2026-06-25 12:28:11");
INSERT INTO kp_store_hist VALUES("1099","10","0","200"," New stock","STORE STOCK ADJUSTMENTS","2026-07-06 20:31:49");
INSERT INTO kp_store_hist VALUES("120","1","50","100","TRANSFER COUNTER B","STORE STOCK ADJUSTMENTS","2026-08-11 19:47:45");
INSERT INTO kp_store_hist VALUES("78","1","0","100","NEW ADDITION","STORE STOCK ADJUSTMENTS","2026-08-11 19:55:04");
INSERT INTO kp_store_hist VALUES("132","1","0","100","NEW ADDITION","STORE STOCK ADJUSTMENTS","2026-08-11 19:56:00");
INSERT INTO kp_store_hist VALUES("120","1","0","750","Addition of stock","STORE STOCK ADJUSTMENTS","2026-08-12 20:50:26");
INSERT INTO kp_store_hist VALUES("7","1","0","250","Addition of stock","STORE STOCK ADJUSTMENTS","2026-08-12 20:51:13");
INSERT INTO kp_store_hist VALUES("8","1","0","50","Addition of stock","STORE STOCK ADJUSTMENTS","2026-08-12 20:52:02");





CREATE TABLE `kp_store_stock_take` (
  `take_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `user_id` int NOT NULL,
  `opening` decimal(10,2) DEFAULT NULL,
  `counted` decimal(10,2) DEFAULT NULL,
  `closing` decimal(10,2) DEFAULT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`take_id`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=latin1;

INSERT INTO kp_store_stock_take VALUES("17","204","1","","2.00","2.00","2026-08-05","2026-08-10 23:00:00");
INSERT INTO kp_store_stock_take VALUES("18","205","1","","5.00","5.00","2026-08-05","2026-08-10 23:00:11");
INSERT INTO kp_store_stock_take VALUES("19","48","1","","18.00","18.00","2026-08-05","2026-08-10 23:00:22");
INSERT INTO kp_store_stock_take VALUES("20","244","1","","0.00","0.00","2026-08-05","2026-08-10 23:00:32");
INSERT INTO kp_store_stock_take VALUES("21","243","1","","0.00","100.00","2026-08-05","2026-08-10 23:00:55");
INSERT INTO kp_store_stock_take VALUES("22","285","1","","0.00","100.00","2026-08-05","2026-08-10 23:01:05");
INSERT INTO kp_store_stock_take VALUES("23","175","1","","0.00","100.00","2026-08-05","2026-08-10 23:01:16");
INSERT INTO kp_store_stock_take VALUES("24","229","1","","3.00","3.00","2026-08-05","2026-08-10 23:01:33");
INSERT INTO kp_store_stock_take VALUES("25","230","1","","4.00","4.00","2026-08-05","2026-08-10 23:01:42");
INSERT INTO kp_store_stock_take VALUES("26","153","1","","12.00","12.00","2026-08-05","2026-08-10 23:01:51");
INSERT INTO kp_store_stock_take VALUES("27","222","1","","0.00","100.00","2026-08-05","2026-08-10 23:03:40");
INSERT INTO kp_store_stock_take VALUES("28","224","1","","3.00","3.00","2026-08-05","2026-08-10 23:03:49");
INSERT INTO kp_store_stock_take VALUES("29","223","1","","4.00","4.00","2026-08-05","2026-08-10 23:04:03");
INSERT INTO kp_store_stock_take VALUES("30","221","1","","0.00","100.00","2026-08-05","2026-08-10 23:04:08");
INSERT INTO kp_store_stock_take VALUES("31","236","1","","2.00","2.00","2026-08-05","2026-08-10 23:04:19");
INSERT INTO kp_store_stock_take VALUES("32","235","1","","0.00","100.00","2026-08-05","2026-08-10 23:04:25");
INSERT INTO kp_store_stock_take VALUES("33","234","1","","4.00","4.00","2026-08-05","2026-08-10 23:04:28");
INSERT INTO kp_store_stock_take VALUES("34","126","1","","100.00","100.00","2026-08-05","2026-08-10 23:04:35");
INSERT INTO kp_store_stock_take VALUES("35","4","1","","84.00","84.00","2026-08-05","2026-08-10 23:05:53");
INSERT INTO kp_store_stock_take VALUES("36","254","1","","1.00","100.00","2026-08-05","2026-08-10 23:06:04");
INSERT INTO kp_store_stock_take VALUES("37","178","1","","10.00","10.00","2026-08-05","2026-08-10 23:06:21");
INSERT INTO kp_store_stock_take VALUES("38","200","1","","63.00","63.00","2026-08-05","2026-08-10 23:08:46");
INSERT INTO kp_store_stock_take VALUES("39","199","1","","7.00","100.00","2026-08-05","2026-08-10 23:08:52");
INSERT INTO kp_store_stock_take VALUES("40","66","1","","0.00","2.00","2026-08-05","2026-08-10 23:09:01");
INSERT INTO kp_store_stock_take VALUES("41","132","1","","0.00","100.00","2026-08-05","2026-08-10 23:09:08");
INSERT INTO kp_store_stock_take VALUES("42","190","1","","25.00","100.00","2026-08-05","2026-08-10 23:09:22");
INSERT INTO kp_store_stock_take VALUES("43","277","1","","0.00","100.00","2026-08-05","2026-08-10 23:09:28");
INSERT INTO kp_store_stock_take VALUES("44","181","1","","38.00","100.00","2026-08-05","2026-08-10 23:09:38");
INSERT INTO kp_store_stock_take VALUES("45","180","1","","54.00","100.00","2026-08-05","2026-08-10 23:09:53");
INSERT INTO kp_store_stock_take VALUES("46","179","1","","8.00","100.00","2026-08-05","2026-08-10 23:09:58");
INSERT INTO kp_store_stock_take VALUES("47","209","1","","0.00","100.00","2026-08-05","2026-08-10 23:10:21");
INSERT INTO kp_store_stock_take VALUES("48","239","1","","4.00","100.00","2026-08-05","2026-08-10 23:10:35");
INSERT INTO kp_store_stock_take VALUES("49","97","1","","0.00","0.00","2026-08-05","2026-08-10 23:10:39");
INSERT INTO kp_store_stock_take VALUES("50","92","1","","0.00","0.00","2026-08-05","2026-08-10 23:10:43");
INSERT INTO kp_store_stock_take VALUES("51","150","1","","25.00","100.00","2026-08-05","2026-08-10 23:10:58");
INSERT INTO kp_store_stock_take VALUES("52","151","1","","20.00","100.00","2026-08-05","2026-08-10 23:11:06");
INSERT INTO kp_store_stock_take VALUES("53","83","1","","48.00","99.00","2026-08-05","2026-08-10 23:11:24");
INSERT INTO kp_store_stock_take VALUES("54","84","1","","0.00","0.00","2026-08-05","2026-08-10 23:11:32");
INSERT INTO kp_store_stock_take VALUES("55","148","1","","24.00","100.00","2026-08-05","2026-08-10 23:11:45");
INSERT INTO kp_store_stock_take VALUES("56","149","1","","0.00","100.00","2026-08-05","2026-08-10 23:11:58");
INSERT INTO kp_store_stock_take VALUES("57","152","1","","16.00","100.00","2026-08-05","2026-08-10 23:12:38");
INSERT INTO kp_store_stock_take VALUES("58","51","1","","12.00","3.00","2026-08-05","2026-08-10 23:13:00");
INSERT INTO kp_store_stock_take VALUES("59","147","1","","1.00","100.00","2026-08-05","2026-08-10 23:14:23");
INSERT INTO kp_store_stock_take VALUES("60","267","1","","0.00","100.00","2026-08-05","2026-08-10 23:14:30");
INSERT INTO kp_store_stock_take VALUES("61","225","1","","3.00","100.00","2026-08-05","2026-08-10 23:14:44");
INSERT INTO kp_store_stock_take VALUES("62","226","1","","0.00","100.00","2026-08-05","2026-08-10 23:14:49");
INSERT INTO kp_store_stock_take VALUES("63","227","1","","0.00","100.00","2026-08-05","2026-08-10 23:14:53");
INSERT INTO kp_store_stock_take VALUES("64","228","1","","0.00","100.00","2026-08-05","2026-08-10 23:14:57");
INSERT INTO kp_store_stock_take VALUES("65","258","1","","1.00","100.00","2026-08-05","2026-08-10 23:15:04");
INSERT INTO kp_store_stock_take VALUES("66","145","1","","0.00","100.00","2026-08-05","2026-08-10 23:15:23");
INSERT INTO kp_store_stock_take VALUES("69","160","1","","1.00","100.00","2026-08-05","2026-08-10 23:19:27");
INSERT INTO kp_store_stock_take VALUES("70","255","1","","0.00","100.00","2026-08-05","2026-08-10 23:19:37");
INSERT INTO kp_store_stock_take VALUES("71","25","1","","36.00","94.00","2026-08-05","2026-08-10 23:19:52");
INSERT INTO kp_store_stock_take VALUES("72","134","1","","24.00","100.00","2026-08-05","2026-08-10 23:20:04");
INSERT INTO kp_store_stock_take VALUES("74","238","1","","1.00","100.00","2026-08-05","2026-08-10 23:20:29");
INSERT INTO kp_store_stock_take VALUES("75","96","1","","2.00","0.00","2026-08-05","2026-08-10 23:20:44");
INSERT INTO kp_store_stock_take VALUES("76","67","1","","20.00","0.00","2026-08-05","2026-08-10 23:20:55");
INSERT INTO kp_store_stock_take VALUES("77","52","1","","18.00","2.00","2026-08-05","2026-08-10 23:21:03");
INSERT INTO kp_store_stock_take VALUES("78","288","1","","0.00","100.00","2026-08-05","2026-08-10 23:21:11");
INSERT INTO kp_store_stock_take VALUES("79","289","1","","0.00","100.00","2026-08-05","2026-08-10 23:21:27");
INSERT INTO kp_store_stock_take VALUES("80","287","1","","0.00","100.00","2026-08-05","2026-08-10 23:21:30");
INSERT INTO kp_store_stock_take VALUES("81","284","1","","0.00","100.00","2026-08-05","2026-08-10 23:21:34");
INSERT INTO kp_store_stock_take VALUES("82","30","1","","9.00","1.00","2026-08-05","2026-08-10 23:21:47");
INSERT INTO kp_store_stock_take VALUES("83","29","1","","3.00","0.00","2026-08-05","2026-08-10 23:21:57");
INSERT INTO kp_store_stock_take VALUES("84","264","1","","0.00","100.00","2026-08-05","2026-08-10 23:22:00");
INSERT INTO kp_store_stock_take VALUES("85","138","1","","24.00","100.00","2026-08-05","2026-08-10 23:22:10");
INSERT INTO kp_store_stock_take VALUES("86","207","1","","7.00","100.00","2026-08-05","2026-08-10 23:22:19");
INSERT INTO kp_store_stock_take VALUES("87","217","1","","2.00","100.00","2026-08-05","2026-08-10 23:22:25");
INSERT INTO kp_store_stock_take VALUES("89","75","1","","0.00","0.00","2026-08-05","2026-08-10 23:24:23");
INSERT INTO kp_store_stock_take VALUES("90","53","1","","18.00","1.00","2026-08-05","2026-08-10 23:24:31");
INSERT INTO kp_store_stock_take VALUES("91","72","1","","18.00","0.00","2026-08-05","2026-08-10 23:24:43");
INSERT INTO kp_store_stock_take VALUES("92","246","1","","73.00","100.00","2026-08-05","2026-08-10 23:24:57");
INSERT INTO kp_store_stock_take VALUES("93","37","1","","50.00","98.00","2026-08-05","2026-08-10 23:25:08");
INSERT INTO kp_store_stock_take VALUES("95","109","1","","0.00","0.00","2026-08-05","2026-08-10 23:25:21");
INSERT INTO kp_store_stock_take VALUES("96","17","1","","50.00","70.00","2026-08-05","2026-08-10 23:25:42");
INSERT INTO kp_store_stock_take VALUES("97","140","1","","0.00","100.00","2026-08-05","2026-08-10 23:26:03");
INSERT INTO kp_store_stock_take VALUES("98","176","1","","4.00","100.00","2026-08-05","2026-08-10 23:26:16");
INSERT INTO kp_store_stock_take VALUES("99","203","1","","2.00","100.00","2026-08-05","2026-08-10 23:27:02");
INSERT INTO kp_store_stock_take VALUES("100","212","1","","2.00","100.00","2026-08-05","2026-08-10 23:27:08");
INSERT INTO kp_store_stock_take VALUES("101","211","1","","5.00","100.00","2026-08-05","2026-08-10 23:27:20");
INSERT INTO kp_store_stock_take VALUES("102","206","1","","2.00","100.00","2026-08-05","2026-08-10 23:27:27");
INSERT INTO kp_store_stock_take VALUES("103","248","1","","9.00","100.00","2026-08-05","2026-08-10 23:27:39");
INSERT INTO kp_store_stock_take VALUES("104","101","1","","0.00","0.00","2026-08-05","2026-08-10 23:27:58");
INSERT INTO kp_store_stock_take VALUES("105","5","1","","0.00","96.00","2026-08-05","2026-08-10 23:28:12");
INSERT INTO kp_store_stock_take VALUES("106","18","1","","0.00","97.00","2026-08-05","2026-08-10 23:28:41");
INSERT INTO kp_store_stock_take VALUES("107","249","1","","0.00","100.00","2026-08-05","2026-08-10 23:28:48");
INSERT INTO kp_store_stock_take VALUES("108","6","1","","0.00","100.00","2026-08-05","2026-08-10 23:29:07");
INSERT INTO kp_store_stock_take VALUES("109","265","1","","0.00","100.00","2026-08-05","2026-08-10 23:29:11");
INSERT INTO kp_store_stock_take VALUES("110","95","1","","14.00","0.00","2026-08-05","2026-08-10 23:29:25");
INSERT INTO kp_store_stock_take VALUES("111","197","1","","8.00","100.00","2026-08-05","2026-08-10 23:29:30");
INSERT INTO kp_store_stock_take VALUES("112","196","1","","15.00","100.00","2026-08-05","2026-08-10 23:29:34");
INSERT INTO kp_store_stock_take VALUES("113","275","1","","0.00","100.00","2026-08-05","2026-08-10 23:29:38");
INSERT INTO kp_store_stock_take VALUES("114","214","1","","1.00","100.00","2026-08-05","2026-08-10 23:29:44");
INSERT INTO kp_store_stock_take VALUES("115","189","1","","3.00","100.00","2026-08-05","2026-08-10 23:31:02");
INSERT INTO kp_store_stock_take VALUES("116","245","1","","1.00","100.00","2026-08-05","2026-08-10 23:31:08");
INSERT INTO kp_store_stock_take VALUES("117","261","1","","0.00","100.00","2026-08-05","2026-08-10 23:31:11");
INSERT INTO kp_store_stock_take VALUES("118","10","1","","48.00","76.00","2026-08-05","2026-08-10 23:31:27");
INSERT INTO kp_store_stock_take VALUES("119","121","1","","25.00","100.00","2026-08-05","2026-08-10 23:31:34");
INSERT INTO kp_store_stock_take VALUES("120","122","1","","0.00","100.00","2026-08-05","2026-08-10 23:31:40");
INSERT INTO kp_store_stock_take VALUES("121","216","1","","10.00","100.00","2026-08-05","2026-08-10 23:31:50");
INSERT INTO kp_store_stock_take VALUES("122","281","1","","0.00","100.00","2026-08-05","2026-08-10 23:31:53");
INSERT INTO kp_store_stock_take VALUES("123","56","1","","36.00","15.00","2026-08-05","2026-08-10 23:32:05");
INSERT INTO kp_store_stock_take VALUES("124","105","1","","24.00","2.00","2026-08-05","2026-08-10 23:32:12");
INSERT INTO kp_store_stock_take VALUES("125","55","1","","84.00","16.00","2026-08-05","2026-08-10 23:32:21");
INSERT INTO kp_store_stock_take VALUES("126","268","1","","0.00","100.00","2026-08-05","2026-08-10 23:32:24");
INSERT INTO kp_store_stock_take VALUES("127","163","1","","1.00","100.00","2026-08-05","2026-08-10 23:32:38");
INSERT INTO kp_store_stock_take VALUES("128","161","1","","6.00","100.00","2026-08-05","2026-08-10 23:32:48");
INSERT INTO kp_store_stock_take VALUES("129","57","1","","4.00","1.00","2026-08-05","2026-08-10 23:32:54");
INSERT INTO kp_store_stock_take VALUES("130","167","1","","4.00","100.00","2026-08-05","2026-08-10 23:33:06");
INSERT INTO kp_store_stock_take VALUES("131","162","1","","5.00","100.00","2026-08-05","2026-08-10 23:33:16");
INSERT INTO kp_store_stock_take VALUES("132","269","1","","0.00","100.00","2026-08-05","2026-08-10 23:33:18");
INSERT INTO kp_store_stock_take VALUES("133","286","1","","0.00","100.00","2026-08-05","2026-08-10 23:33:23");
INSERT INTO kp_store_stock_take VALUES("134","128","1","","96.00","100.00","2026-08-05","2026-08-10 23:33:39");
INSERT INTO kp_store_stock_take VALUES("135","129","1","","24.00","100.00","2026-08-05","2026-08-10 23:33:45");
INSERT INTO kp_store_stock_take VALUES("136","194","1","","1.00","100.00","2026-08-05","2026-08-10 23:34:02");
INSERT INTO kp_store_stock_take VALUES("137","184","1","","0.00","100.00","2026-08-05","2026-08-10 23:34:07");
INSERT INTO kp_store_stock_take VALUES("138","41","1","","10.00","0.00","2026-08-05","2026-08-10 23:34:22");
INSERT INTO kp_store_stock_take VALUES("139","58","1","","0.00","0.00","2026-08-05","2026-08-10 23:34:28");
INSERT INTO kp_store_stock_take VALUES("140","82","1","","3.00","0.00","2026-08-05","2026-08-10 23:39:21");
INSERT INTO kp_store_stock_take VALUES("141","112","1","","6.00","2.00","2026-08-05","2026-08-10 23:39:26");
INSERT INTO kp_store_stock_take VALUES("142","263","1","","0.00","100.00","2026-08-05","2026-08-10 23:39:29");
INSERT INTO kp_store_stock_take VALUES("143","27","1","","9.00","2.00","2026-08-05","2026-08-10 23:39:43");
INSERT INTO kp_store_stock_take VALUES("144","201","1","","7.00","100.00","2026-08-05","2026-08-10 23:39:49");
INSERT INTO kp_store_stock_take VALUES("145","28","1","","12.00","2.00","2026-08-05","2026-08-10 23:40:06");
INSERT INTO kp_store_stock_take VALUES("146","202","1","","5.00","100.00","2026-08-05","2026-08-10 23:40:17");
INSERT INTO kp_store_stock_take VALUES("147","273","1","","0.00","100.00","2026-08-05","2026-08-10 23:40:19");
INSERT INTO kp_store_stock_take VALUES("148","182","1","","6.00","100.00","2026-08-05","2026-08-10 23:40:34");
INSERT INTO kp_store_stock_take VALUES("149","36","1","","10.00","1.00","2026-08-05","2026-08-10 23:40:41");
INSERT INTO kp_store_stock_take VALUES("151","185","1","","2.00","100.00","2026-08-05","2026-08-10 23:40:56");
INSERT INTO kp_store_stock_take VALUES("152","274","1","","0.00","100.00","2026-08-05","2026-08-10 23:40:59");
INSERT INTO kp_store_stock_take VALUES("153","183","1","","0.00","100.00","2026-08-05","2026-08-10 23:41:36");
INSERT INTO kp_store_stock_take VALUES("154","213","1","","0.00","100.00","2026-08-05","2026-08-10 23:42:04");
INSERT INTO kp_store_stock_take VALUES("155","208","1","","0.00","100.00","2026-08-05","2026-08-10 23:42:17");
INSERT INTO kp_store_stock_take VALUES("156","114","1","","30.00","3.00","2026-08-05","2026-08-10 23:42:26");
INSERT INTO kp_store_stock_take VALUES("157","33","1","","7.00","0.00","2026-08-05","2026-08-10 23:42:52");
INSERT INTO kp_store_stock_take VALUES("158","34","1","","7.00","1.00","2026-08-05","2026-08-10 23:43:07");
INSERT INTO kp_store_stock_take VALUES("159","218","1","","5.00","100.00","2026-08-05","2026-08-10 23:43:18");
INSERT INTO kp_store_stock_take VALUES("160","215","1","","2.00","100.00","2026-08-05","2026-08-10 23:43:26");
INSERT INTO kp_store_stock_take VALUES("162","3","1","","35.00","98.00","2026-08-05","2026-08-10 23:44:17");
INSERT INTO kp_store_stock_take VALUES("163","1","1","","10.00","101.00","2026-08-05","2026-08-10 23:44:47");
INSERT INTO kp_store_stock_take VALUES("164","93","1","","0.00","0.00","2026-08-05","2026-08-10 23:44:49");
INSERT INTO kp_store_stock_take VALUES("165","94","1","","0.00","0.00","2026-08-05","2026-08-10 23:44:57");
INSERT INTO kp_store_stock_take VALUES("166","169","1","","0.00","100.00","2026-08-05","2026-08-10 23:45:17");
INSERT INTO kp_store_stock_take VALUES("167","170","1","","3.00","100.00","2026-08-05","2026-08-10 23:45:24");
INSERT INTO kp_store_stock_take VALUES("168","192","1","","1.00","100.00","2026-08-05","2026-08-10 23:45:35");
INSERT INTO kp_store_stock_take VALUES("169","191","1","","1.00","100.00","2026-08-05","2026-08-10 23:45:43");
INSERT INTO kp_store_stock_take VALUES("170","193","1","","17.00","100.00","2026-08-05","2026-08-10 23:45:52");
INSERT INTO kp_store_stock_take VALUES("171","171","1","","10.00","100.00","2026-08-05","2026-08-10 23:45:59");
INSERT INTO kp_store_stock_take VALUES("172","77","1","","161.00","0.00","2026-08-05","2026-08-10 23:46:08");
INSERT INTO kp_store_stock_take VALUES("173","85","1","","73.00","1.00","2026-08-05","2026-08-10 23:46:17");
INSERT INTO kp_store_stock_take VALUES("174","78","1","","0.00","3.00","2026-08-05","2026-08-10 23:46:25");
INSERT INTO kp_store_stock_take VALUES("175","87","1","","0.00","0.00","2026-08-05","2026-08-10 23:46:33");
INSERT INTO kp_store_stock_take VALUES("176","23","1","","216.00","41.00","2026-08-05","2026-08-10 23:46:46");
INSERT INTO kp_store_stock_take VALUES("177","195","1","","0.00","100.00","2026-08-05","2026-08-10 23:47:02");
INSERT INTO kp_store_stock_take VALUES("178","133","1","","0.00","100.00","2026-08-05","2026-08-10 23:47:07");
INSERT INTO kp_store_stock_take VALUES("179","256","1","","0.00","100.00","2026-08-05","2026-08-10 23:47:16");
INSERT INTO kp_store_stock_take VALUES("180","130","1","","0.00","100.00","2026-08-05","2026-08-10 23:47:21");
INSERT INTO kp_store_stock_take VALUES("181","257","1","","1.00","100.00","2026-08-05","2026-08-10 23:47:24");
INSERT INTO kp_store_stock_take VALUES("182","108","1","","36.00","12.00","2026-08-05","2026-08-10 23:47:32");
INSERT INTO kp_store_stock_take VALUES("183","146","1","","3.00","100.00","2026-08-05","2026-08-10 23:47:56");
INSERT INTO kp_store_stock_take VALUES("184","279","1","","0.00","100.00","2026-08-05","2026-08-10 23:48:10");
INSERT INTO kp_store_stock_take VALUES("185","64","1","","4.00","2.00","2026-08-05","2026-08-10 23:48:23");
INSERT INTO kp_store_stock_take VALUES("186","103","1","","0.00","0.00","2026-08-05","2026-08-10 23:48:26");
INSERT INTO kp_store_stock_take VALUES("187","142","1","","36.00","100.00","2026-08-05","2026-08-10 23:48:37");
INSERT INTO kp_store_stock_take VALUES("188","7","1","","0.00","80.00","2026-08-05","2026-08-10 23:48:44");
INSERT INTO kp_store_stock_take VALUES("189","282","1","","0.00","100.00","2026-08-05","2026-08-10 23:48:47");
INSERT INTO kp_store_stock_take VALUES("190","31","1","","1.00","1.00","2026-08-05","2026-08-10 23:48:58");
INSERT INTO kp_store_stock_take VALUES("191","32","1","","6.00","0.00","2026-08-05","2026-08-10 23:49:06");
INSERT INTO kp_store_stock_take VALUES("192","270","1","","0.00","100.00","2026-08-05","2026-08-10 23:49:09");
INSERT INTO kp_store_stock_take VALUES("193","168","1","","2.00","100.00","2026-08-05","2026-08-10 23:49:38");
INSERT INTO kp_store_stock_take VALUES("194","164","1","","2.00","100.00","2026-08-05","2026-08-10 23:49:44");
INSERT INTO kp_store_stock_take VALUES("196","291","1","","0.00","100.00","2026-08-05","2026-08-10 23:50:07");
INSERT INTO kp_store_stock_take VALUES("197","76","1","","0.00","0.00","2026-08-05","2026-08-10 23:50:14");
INSERT INTO kp_store_stock_take VALUES("198","233","1","","1.00","100.00","2026-08-05","2026-08-10 23:50:20");
INSERT INTO kp_store_stock_take VALUES("199","104","1","","24.00","6.00","2026-08-05","2026-08-10 23:50:35");
INSERT INTO kp_store_stock_take VALUES("200","117","1","","1.00","3.00","2026-08-05","2026-08-10 23:51:00");
INSERT INTO kp_store_stock_take VALUES("201","156","1","","14.00","100.00","2026-08-05","2026-08-10 23:51:17");
INSERT INTO kp_store_stock_take VALUES("202","283","1","","0.00","100.00","2026-08-05","2026-08-10 23:51:47");
INSERT INTO kp_store_stock_take VALUES("203","11","1","","23.00","88.00","2026-08-05","2026-08-10 23:54:14");
INSERT INTO kp_store_stock_take VALUES("204","119","1","","250.00","100.00","2026-08-05","2026-08-10 23:54:38");
INSERT INTO kp_store_stock_take VALUES("205","141","1","","168.00","100.00","2026-08-05","2026-08-10 23:54:45");
INSERT INTO kp_store_stock_take VALUES("206","136","1","","50.00","100.00","2026-08-05","2026-08-10 23:54:58");
INSERT INTO kp_store_stock_take VALUES("207","187","1","","26.00","100.00","2026-08-05","2026-08-10 23:55:20");
INSERT INTO kp_store_stock_take VALUES("208","188","1","","18.00","100.00","2026-08-05","2026-08-10 23:55:28");
INSERT INTO kp_store_stock_take VALUES("209","59","1","","36.00","24.00","2026-08-05","2026-08-10 23:55:43");
INSERT INTO kp_store_stock_take VALUES("210","276","1","","0.00","100.00","2026-08-05","2026-08-10 23:55:47");
INSERT INTO kp_store_stock_take VALUES("212","165","1","","2.00","100.00","2026-08-05","2026-08-10 23:56:53");
INSERT INTO kp_store_stock_take VALUES("213","157","1","","32.00","100.00","2026-08-05","2026-08-10 23:57:02");
INSERT INTO kp_store_stock_take VALUES("214","99","1","","25.00","1.00","2026-08-05","2026-08-10 23:57:08");
INSERT INTO kp_store_stock_take VALUES("215","100","1","","18.00","0.00","2026-08-05","2026-08-10 23:57:12");
INSERT INTO kp_store_stock_take VALUES("216","154","1","","0.00","100.00","2026-08-05","2026-08-10 23:57:34");
INSERT INTO kp_store_stock_take VALUES("217","60","1","","5.00","1.00","2026-08-05","2026-08-10 23:57:46");
INSERT INTO kp_store_stock_take VALUES("218","155","1","","1.00","100.00","2026-08-05","2026-08-10 23:57:53");
INSERT INTO kp_store_stock_take VALUES("219","73","1","","1.00","7.00","2026-08-05","2026-08-10 23:58:00");
INSERT INTO kp_store_stock_take VALUES("220","74","1","","11.00","0.00","2026-08-05","2026-08-10 23:58:07");
INSERT INTO kp_store_stock_take VALUES("221","70","1","","96.00","0.00","2026-08-05","2026-08-10 23:58:19");
INSERT INTO kp_store_stock_take VALUES("222","115","1","","24.00","6.00","2026-08-05","2026-08-10 23:58:44");
INSERT INTO kp_store_stock_take VALUES("223","69","1","","48.00","60.00","2026-08-05","2026-08-11 00:00:53");
INSERT INTO kp_store_stock_take VALUES("224","166","1","","2.00","100.00","2026-08-05","2026-08-11 00:01:03");
INSERT INTO kp_store_stock_take VALUES("225","271","1","","0.00","100.00","2026-08-05","2026-08-11 00:01:09");
INSERT INTO kp_store_stock_take VALUES("226","8","1","","0.00","82.00","2026-08-05","2026-08-11 00:02:01");
INSERT INTO kp_store_stock_take VALUES("227","172","1","","3.00","100.00","2026-08-05","2026-08-11 00:02:11");
INSERT INTO kp_store_stock_take VALUES("228","173","1","","3.00","100.00","2026-08-05","2026-08-11 00:02:14");
INSERT INTO kp_store_stock_take VALUES("229","174","1","","0.00","100.00","2026-08-05","2026-08-11 00:02:20");
INSERT INTO kp_store_stock_take VALUES("230","39","1","","17.00","1.00","2026-08-05","2026-08-11 00:02:34");
INSERT INTO kp_store_stock_take VALUES("231","19","1","","0.00","100.00","2026-08-05","2026-08-11 00:02:43");
INSERT INTO kp_store_stock_take VALUES("232","240","1","","12.00","100.00","2026-08-05","2026-08-11 00:02:54");
INSERT INTO kp_store_stock_take VALUES("233","242","1","","63.00","100.00","2026-08-05","2026-08-11 00:03:02");
INSERT INTO kp_store_stock_take VALUES("234","241","1","","17.00","100.00","2026-08-05","2026-08-11 00:03:13");
INSERT INTO kp_store_stock_take VALUES("235","127","1","","50.00","100.00","2026-08-05","2026-08-11 00:03:28");
INSERT INTO kp_store_stock_take VALUES("236","139","1","","24.00","100.00","2026-08-05","2026-08-11 00:03:43");
INSERT INTO kp_store_stock_take VALUES("237","22","1","","312.00","4.00","2026-08-05","2026-08-11 00:03:50");
INSERT INTO kp_store_stock_take VALUES("238","232","1","","3.00","100.00","2026-08-05","2026-08-11 00:04:02");
INSERT INTO kp_store_stock_take VALUES("239","231","1","","4.00","100.00","2026-08-05","2026-08-11 00:04:04");
INSERT INTO kp_store_stock_take VALUES("240","290","1","","0.00","100.00","2026-08-05","2026-08-11 00:04:07");
INSERT INTO kp_store_stock_take VALUES("241","260","1","","0.00","100.00","2026-08-05","2026-08-11 00:04:11");
INSERT INTO kp_store_stock_take VALUES("242","131","1","","75.00","100.00","2026-08-05","2026-08-11 00:04:23");
INSERT INTO kp_store_stock_take VALUES("243","252","1","","1.00","100.00","2026-08-05","2026-08-11 00:05:00");
INSERT INTO kp_store_stock_take VALUES("244","253","1","","0.00","100.00","2026-08-05","2026-08-11 00:05:10");
INSERT INTO kp_store_stock_take VALUES("245","250","1","","3.00","100.00","2026-08-05","2026-08-11 00:05:18");
INSERT INTO kp_store_stock_take VALUES("246","251","1","","3.00","100.00","2026-08-05","2026-08-11 00:05:28");
INSERT INTO kp_store_stock_take VALUES("247","266","1","","0.00","100.00","2026-08-05","2026-08-11 00:05:34");
INSERT INTO kp_store_stock_take VALUES("248","143","1","","24.00","100.00","2026-08-05","2026-08-11 00:05:48");
INSERT INTO kp_store_stock_take VALUES("249","280","1","","0.00","100.00","2026-08-05","2026-08-11 00:05:51");
INSERT INTO kp_store_stock_take VALUES("250","210","1","","2.00","100.00","2026-08-05","2026-08-11 00:06:04");
INSERT INTO kp_store_stock_take VALUES("251","124","1","","75.00","100.00","2026-08-05","2026-08-11 00:06:15");
INSERT INTO kp_store_stock_take VALUES("252","13","1","","66.00","94.00","2026-08-05","2026-08-11 00:06:33");
INSERT INTO kp_store_stock_take VALUES("253","71","1","","250.00","25.00","2026-08-05","2026-08-11 00:06:47");
INSERT INTO kp_store_stock_take VALUES("254","12","1","","48.00","89.00","2026-08-05","2026-08-11 00:06:56");
INSERT INTO kp_store_stock_take VALUES("255","123","1","","100.00","100.00","2026-08-05","2026-08-11 00:07:08");
INSERT INTO kp_store_stock_take VALUES("256","14","1","","30.00","64.00","2026-08-05","2026-08-11 00:07:17");
INSERT INTO kp_store_stock_take VALUES("257","86","1","","250.00","0.00","2026-08-05","2026-08-11 00:07:28");
INSERT INTO kp_store_stock_take VALUES("258","15","1","","24.00","86.00","2026-08-05","2026-08-11 00:07:39");
INSERT INTO kp_store_stock_take VALUES("259","125","1","","0.00","100.00","2026-08-05","2026-08-11 00:07:45");
INSERT INTO kp_store_stock_take VALUES("260","135","1","","20.00","100.00","2026-08-05","2026-08-11 00:07:51");
INSERT INTO kp_store_stock_take VALUES("261","137","1","","0.00","100.00","2026-08-05","2026-08-11 00:07:57");
INSERT INTO kp_store_stock_take VALUES("262","220","1","","13.00","100.00","2026-08-05","2026-08-11 00:08:09");
INSERT INTO kp_store_stock_take VALUES("263","219","1","","29.00","100.00","2026-08-05","2026-08-11 00:08:16");
INSERT INTO kp_store_stock_take VALUES("264","113","1","","0.00","2.00","2026-08-05","2026-08-11 00:08:26");
INSERT INTO kp_store_stock_take VALUES("265","98","1","","36.00","0.00","2026-08-05","2026-08-11 00:08:33");
INSERT INTO kp_store_stock_take VALUES("266","40","1","","11.00","1.00","2026-08-05","2026-08-11 00:08:37");
INSERT INTO kp_store_stock_take VALUES("267","259","1","","0.00","100.00","2026-08-05","2026-08-11 00:08:39");
INSERT INTO kp_store_stock_take VALUES("268","158","1","","79.00","100.00","2026-08-05","2026-08-11 00:09:04");
INSERT INTO kp_store_stock_take VALUES("269","111","1","","24.00","4.00","2026-08-05","2026-08-11 00:09:31");
INSERT INTO kp_store_stock_take VALUES("270","89","1","","24.00","1.00","2026-08-05","2026-08-11 00:09:43");
INSERT INTO kp_store_stock_take VALUES("271","262","1","","0.00","100.00","2026-08-05","2026-08-11 00:09:46");
INSERT INTO kp_store_stock_take VALUES("272","272","1","","0.00","100.00","2026-08-05","2026-08-11 00:09:48");
INSERT INTO kp_store_stock_take VALUES("273","144","1","","276.00","100.00","2026-08-05","2026-08-11 00:10:01");
INSERT INTO kp_store_stock_take VALUES("274","16","1","","30.00","70.00","2026-08-05","2026-08-11 00:10:09");
INSERT INTO kp_store_stock_take VALUES("275","9","1","","0.00","100.00","2026-08-05","2026-08-11 00:10:16");
INSERT INTO kp_store_stock_take VALUES("276","120","1","","50.00","100.00","2026-08-05","2026-08-11 00:10:25");
INSERT INTO kp_store_stock_take VALUES("277","198","1","","9.00","100.00","2026-08-05","2026-08-11 00:10:34");





CREATE TABLE `kp_store_stock_track` (
  `item_id` int NOT NULL,
  `po_id` int DEFAULT NULL,
  `grn_id` int NOT NULL,
  `req_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `status` varchar(6) NOT NULL,
  `shift` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_store_stock_track VALUES("71","","0","405","1","50","OUT","2026-08-11","2026-08-11 19:48:04");
INSERT INTO kp_store_stock_track VALUES("119","","0","405","1","25","OUT","2026-08-11","2026-08-11 19:48:08");
INSERT INTO kp_store_stock_track VALUES("86","","0","405","1","25","OUT","2026-08-11","2026-08-11 19:48:12");
INSERT INTO kp_store_stock_track VALUES("120","","0","405","1","100","OUT","2026-08-11","2026-08-11 19:48:15");
INSERT INTO kp_store_stock_track VALUES("123","","0","405","1","75","OUT","2026-08-11","2026-08-11 19:48:18");
INSERT INTO kp_store_stock_track VALUES("128","","0","405","1","24","OUT","2026-08-11","2026-08-11 19:48:23");
INSERT INTO kp_store_stock_track VALUES("12","","0","405","1","24","OUT","2026-08-11","2026-08-11 19:48:27");
INSERT INTO kp_store_stock_track VALUES("144","","0","405","1","72","OUT","2026-08-11","2026-08-11 19:48:31");
INSERT INTO kp_store_stock_track VALUES("23","","0","405","1","12","OUT","2026-08-11","2026-08-11 19:48:35");
INSERT INTO kp_store_stock_track VALUES("25","","0","405","1","12","OUT","2026-08-11","2026-08-11 19:48:47");
INSERT INTO kp_store_stock_track VALUES("22","","0","405","1","24","OUT","2026-08-11","2026-08-11 19:48:51");
INSERT INTO kp_store_stock_track VALUES("136","","0","405","1","25","OUT","2026-08-11","2026-08-11 19:48:55");
INSERT INTO kp_store_stock_track VALUES("151","","0","405","1","3","OUT","2026-08-11","2026-08-11 19:49:08");
INSERT INTO kp_store_stock_track VALUES("48","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:49:28");
INSERT INTO kp_store_stock_track VALUES("53","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:49:34");
INSERT INTO kp_store_stock_track VALUES("67","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:49:41");
INSERT INTO kp_store_stock_track VALUES("60","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:49:45");
INSERT INTO kp_store_stock_track VALUES("89","","0","405","1","12","OUT","2026-08-11","2026-08-11 19:49:54");
INSERT INTO kp_store_stock_track VALUES("111","","0","405","1","12","OUT","2026-08-11","2026-08-11 19:49:58");
INSERT INTO kp_store_stock_track VALUES("158","","0","405","1","5","OUT","2026-08-11","2026-08-11 19:50:03");
INSERT INTO kp_store_stock_track VALUES("77","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:50:06");
INSERT INTO kp_store_stock_track VALUES("127","","0","405","1","25","OUT","2026-08-11","2026-08-11 19:51:34");
INSERT INTO kp_store_stock_track VALUES("294","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:51:54");
INSERT INTO kp_store_stock_track VALUES("293","","0","405","1","3","OUT","2026-08-11","2026-08-11 19:51:58");
INSERT INTO kp_store_stock_track VALUES("27","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:52:02");
INSERT INTO kp_store_stock_track VALUES("30","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:52:08");
INSERT INTO kp_store_stock_track VALUES("207","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:52:19");
INSERT INTO kp_store_stock_track VALUES("40","","0","405","1","1","OUT","2026-08-11","2026-08-11 19:52:24");
INSERT INTO kp_store_stock_track VALUES("242","","0","405","1","4","OUT","2026-08-11","2026-08-11 19:52:30");
INSERT INTO kp_store_stock_track VALUES("241","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:52:35");
INSERT INTO kp_store_stock_track VALUES("78","","0","405","1","2","OUT","2026-08-11","2026-08-11 19:55:10");
INSERT INTO kp_store_stock_track VALUES("132","","0","405","1","25","OUT","2026-08-11","2026-08-11 19:56:04");
INSERT INTO kp_store_stock_track VALUES("120","","0","406","1","200","OUT","2026-08-12","2026-08-12 20:52:11");
INSERT INTO kp_store_stock_track VALUES("126","","0","406","1","100","OUT","2026-08-12","2026-08-12 20:52:11");
INSERT INTO kp_store_stock_track VALUES("71","","0","406","1","100","OUT","2026-08-12","2026-08-12 20:52:11");
INSERT INTO kp_store_stock_track VALUES("124","","0","406","1","25","OUT","2026-08-12","2026-08-12 20:52:11");
INSERT INTO kp_store_stock_track VALUES("132","","0","406","1","25","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("7","","0","406","1","25","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("8","","0","406","1","25","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("152","","0","406","1","1","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("119","","0","406","1","25","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("292","","0","406","1","24","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("56","","0","406","1","24","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("144","","0","406","1","60","OUT","2026-08-12","2026-08-12 20:52:12");
INSERT INTO kp_store_stock_track VALUES("25","","0","406","1","12","OUT","2026-08-12","2026-08-12 20:52:13");
INSERT INTO kp_store_stock_track VALUES("295","","0","407","1","2","OUT","2026-08-12","2026-08-12 21:31:56");





CREATE TABLE `kp_store_take_status` (
  `shift` date NOT NULL,
  `take_status` int NOT NULL DEFAULT '2',
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_store_take_status VALUES("2026-08-11","3","1");





CREATE TABLE `kp_sub_cat` (
  `sub_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `cat_id` int NOT NULL,
  `dpt` int NOT NULL,
  PRIMARY KEY (`sub_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;

INSERT INTO kp_sub_cat VALUES("1","LOCAL","1","1");
INSERT INTO kp_sub_cat VALUES("2","INTERNATIONAL","1","1");
INSERT INTO kp_sub_cat VALUES("3","RED","3","1");
INSERT INTO kp_sub_cat VALUES("4","WHITE","3","1");
INSERT INTO kp_sub_cat VALUES("5","ENERGY DRINKS","5","1");
INSERT INTO kp_sub_cat VALUES("6","SODAS","5","1");
INSERT INTO kp_sub_cat VALUES("7","PAIN KILLERS","6","1");
INSERT INTO kp_sub_cat VALUES("8","RAHA","6","1");
INSERT INTO kp_sub_cat VALUES("9","WATER","5","1");
INSERT INTO kp_sub_cat VALUES("10","VODKA","2","1");
INSERT INTO kp_sub_cat VALUES("11","GIN","2","1");
INSERT INTO kp_sub_cat VALUES("12","BRANDY","2","1");
INSERT INTO kp_sub_cat VALUES("13","WHISKEY","2","1");
INSERT INTO kp_sub_cat VALUES("15","CREAM","2","1");
INSERT INTO kp_sub_cat VALUES("21","CAN","1","1");
INSERT INTO kp_sub_cat VALUES("22","TOTS","2","1");
INSERT INTO kp_sub_cat VALUES("23","MEDICINE","6","1");
INSERT INTO kp_sub_cat VALUES("24","CONDOMS","6","1");
INSERT INTO kp_sub_cat VALUES("25","LIQUEUR","2","1");
INSERT INTO kp_sub_cat VALUES("26","SHOTS","12","1");
INSERT INTO kp_sub_cat VALUES("28","BREAKAGE","6","1");
INSERT INTO kp_sub_cat VALUES("29","RUMS","2","1");
INSERT INTO kp_sub_cat VALUES("30","BEEF","11","2");
INSERT INTO kp_sub_cat VALUES("33","JUICE","5","1");
INSERT INTO kp_sub_cat VALUES("34","POULTRY","11","2");
INSERT INTO kp_sub_cat VALUES("36","AIRTIME","6","1");
INSERT INTO kp_sub_cat VALUES("37","TEQUILLA","2","1");
INSERT INTO kp_sub_cat VALUES("38","CIGARETTES","6","1");
INSERT INTO kp_sub_cat VALUES("39","POOL","6","1");
INSERT INTO kp_sub_cat VALUES("40","HONEY","6","1");
INSERT INTO kp_sub_cat VALUES("41","CONTAINERS","6","1");
INSERT INTO kp_sub_cat VALUES("43","SOUPS","11","2");
INSERT INTO kp_sub_cat VALUES("45","BREAKFAST","11","2");
INSERT INTO kp_sub_cat VALUES("46","SNACKS","11","2");
INSERT INTO kp_sub_cat VALUES("48","HOT DRINKS","11","2");
INSERT INTO kp_sub_cat VALUES("49","SALADS","11","2");
INSERT INTO kp_sub_cat VALUES("51","PRODUCTS ","6","1");
INSERT INTO kp_sub_cat VALUES("52","LAMB","11","2");
INSERT INTO kp_sub_cat VALUES("58","SEA FOOD","11","1");
INSERT INTO kp_sub_cat VALUES("60","CANES","2","1");
INSERT INTO kp_sub_cat VALUES("61","COGNAC","2","1");





CREATE TABLE `kp_suppliers` (
  `sup_id` int NOT NULL AUTO_INCREMENT,
  `sup_name` varchar(33) NOT NULL,
  `sup_alias` varchar(11) NOT NULL,
  `sup_address` varchar(33) NOT NULL,
  `sub_email` int NOT NULL,
  `sup_phone` varchar(33) NOT NULL,
  `sup_location` text NOT NULL,
  `sup_pin` varchar(15) NOT NULL,
  `sup_vat` varchar(15) NOT NULL,
  `sup_bal` decimal(10,2) NOT NULL,
  `created_by` int NOT NULL,
  `del_status` int NOT NULL DEFAULT '2',
  `sup_email` varchar(150) DEFAULT NULL,
  `sup_type` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`sup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

INSERT INTO kp_suppliers VALUES("1","CASH SUPPLIER","","","0","0008","","","","3720.00","0","2","","1");
INSERT INTO kp_suppliers VALUES("2","NAIVAS SUPERMARKET","","","0","009","","","","0.00","0","2","","1");
INSERT INTO kp_suppliers VALUES("3","OBRADLEYS","","","0","","","P051445810V","P051445810V","0.00","0","2","","1");
INSERT INTO kp_suppliers VALUES("4","J WINES","","","0","","","P051615405A","P051615405A","0.00","0","2","","1");
INSERT INTO kp_suppliers VALUES("5","KABSON WATERS","","N/A","0","N/A","KENYATTA ROAD","P051925239P","N/A","0.00","0","2","N/A","1");
INSERT INTO kp_suppliers VALUES("6","ALAMABHUBESI HOLDINGS SODA","","N/A","0","N/A","RUIRU","P052402134Z","N/A","0.00","0","2","N/A","1");
INSERT INTO kp_suppliers VALUES("7","BHIRY ENT","","N/A","0","N/A","JUJA","A004611823I","N/A","0.00","0","2","N/A","1");





CREATE TABLE `kp_tax` (
  `tax_id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_general_ci NOT NULL,
  `percent` int NOT NULL,
  `code` varchar(11) COLLATE latin1_general_ci NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `del_status` tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`tax_id`),
  KEY `idx_del_status` (`del_status`),
  KEY `idx_name` (`name`(3072))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_tax VALUES("1","VAT","16","S","2021-03-12 08:54:38","2","2");
INSERT INTO kp_tax VALUES("2","ZERO RATE","0","Z","2021-03-12 08:54:57","2","2");
INSERT INTO kp_tax VALUES("3","ECXEMPT","0","E","2021-03-12 08:55:13","2","2");





CREATE TABLE `kp_thesion` (
  `thes_id` int NOT NULL AUTO_INCREMENT,
  `panel` int NOT NULL,
  `dash` varchar(155) COLLATE latin1_general_ci NOT NULL,
  `shop` int NOT NULL,
  `user_id` int NOT NULL,
  `sale_id` int NOT NULL,
  `status` int NOT NULL,
  `cust_id` int NOT NULL,
  `po_id` int NOT NULL,
  `ret_id` int NOT NULL,
  `req_id` int NOT NULL,
  `sup_id` int NOT NULL,
  `petty_id` int NOT NULL,
  `bill_id` int NOT NULL,
  `store_id` int NOT NULL,
  `trans_id` int NOT NULL,
  `grn_id` int NOT NULL,
  `price` int NOT NULL,
  `current_page` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `comp_address` varchar(155) COLLATE latin1_general_ci NOT NULL,
  `day` date NOT NULL,
  `shift` date NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`thes_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_thesion VALUES("1","0","","0","1","0","0","0","0","0","5","3","0","0","0","0","0","0","back/kugura/po_pending.php","9152401f8c7915afafc21d27078065d1","2026-08-12","0000-00-00","2026-08-19 18:13:16");
INSERT INTO kp_thesion VALUES("72","0","","0","0","0","0","0","1366","0","0","1","0","0","0","0","0","0","front/kugura/init_po.php","","2025-05-16","0000-00-00","2025-05-16 02:42:34");
INSERT INTO kp_thesion VALUES("119","0","","0","10","0","0","0","3","0","0","0","0","0","0","0","17","0","back/kugura/init_rec_act.php","31efd489eb46c7053936d40e15555134","0000-00-00","0000-00-00","2026-06-24 10:10:06");





CREATE TABLE `kp_thesion_front` (
  `thesion_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `sale_id` int NOT NULL,
  `req_id` int NOT NULL,
  `tot_id` int NOT NULL,
  `cntr_id` int NOT NULL,
  `current_page` text COLLATE latin1_general_ci NOT NULL,
  `comp_address` text COLLATE latin1_general_ci NOT NULL,
  `shift` date NOT NULL,
  `status` int NOT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`thesion_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=273096 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_thesion_front VALUES("254023","10","0","0","0","0","back/kugura/rec_page.php","9152401f8c7915afafc21d27078065d1","0000-00-00","0","2026-06-24 10:10:06","2026-08-24 20:04:46");
INSERT INTO kp_thesion_front VALUES("268724","17","0","0","0","0","","31efd489eb46c7053936d40e15555134","2026-07-25","0","2026-07-25 09:48:27","0000-00-00 00:00:00");
INSERT INTO kp_thesion_front VALUES("272837","5","0","0","0","0","","31efd489eb46c7053936d40e15555134","2026-07-28","0","2026-07-28 20:07:56","0000-00-00 00:00:00");
INSERT INTO kp_thesion_front VALUES("273015","7","5","0","0","1","front/kwedia/bill_api.php","9152401f8c7915afafc21d27078065d1","2026-08-24","1","2026-08-24 19:27:52","2026-08-24 19:27:52");
INSERT INTO kp_thesion_front VALUES("273068","12","0","0","0","0","","9152401f8c7915afafc21d27078065d1","2026-08-24","0","2026-08-24 22:13:40","0000-00-00 00:00:00");
INSERT INTO kp_thesion_front VALUES("273088","3","0","0","0","0","","9152401f8c7915afafc21d27078065d1","2026-08-24","0","2026-08-24 23:13:27","0000-00-00 00:00:00");
INSERT INTO kp_thesion_front VALUES("273091","11","20","0","0","1","front/kwedia/bill_api.php","9152401f8c7915afafc21d27078065d1","2026-08-24","1","2026-08-24 23:17:35","2026-08-24 23:17:35");
INSERT INTO kp_thesion_front VALUES("273094","6","0","0","0","0","","9152401f8c7915afafc21d27078065d1","2026-08-24","0","2026-08-24 23:18:29","0000-00-00 00:00:00");





CREATE TABLE `kp_tot` (
  `tot_id` int NOT NULL,
  `user_id` int NOT NULL,
  `shift` varchar(55) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `counter` int NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_kp_tot_tot_id` (`tot_id`),
  KEY `idx_kp_tot_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_tot VALUES("5","27","2025-08-29","1","2025-08-29 04:05:14","2026-07-23 18:56:53");
INSERT INTO kp_tot VALUES("8","6","2025-09-13","1","2025-09-13 14:09:53","2026-07-23 18:56:53");
INSERT INTO kp_tot VALUES("12","1","2026-05-28","1","2026-05-28 23:49:55","2026-07-23 18:56:53");
INSERT INTO kp_tot VALUES("13","1","2026-08-11","1","2026-08-17 12:18:19","2026-08-17 12:18:19");





CREATE TABLE `kp_tot_id` (
  `tot_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_tot_id VALUES("13");





CREATE TABLE `kp_tot_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tot_id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_id` int NOT NULL,
  `tot_item_id` int NOT NULL,
  `tots` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shift` varchar(55) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `counter` int NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kp_tot_items_tot_id` (`tot_id`),
  KEY `idx_kp_tot_items_item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_tot_items VALUES("11","5","27","111","573","25.00","2025-08-29","1","2025-08-29 04:05:11","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("12","8","6","113","575","25.00","2025-09-13","1","2025-09-13 14:08:55","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("13","8","6","514","572","25.00","2025-09-13","1","2025-09-13 14:09:04","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("14","8","6","98","580","23.00","2025-09-13","1","2025-09-13 14:09:24","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("15","8","6","111","573","25.00","2025-09-13","1","2025-09-13 14:09:31","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("16","8","6","686","686","25.00","2025-09-13","1","2025-09-13 14:09:35","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("17","8","6","235","235","25.00","2025-09-13","1","2025-09-13 14:09:37","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("18","8","6","738","571","25.00","2025-09-13","1","2025-09-13 14:09:42","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("19","8","6","112","613","25.00","2025-09-13","1","2025-09-13 14:09:47","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("20","12","1","111","573","25.00","2026-05-28","1","2026-05-28 23:49:50","2026-07-23 18:56:53");
INSERT INTO kp_tot_items VALUES("21","13","1","97","97","25.00","2026-08-11","1","2026-08-12 19:33:41","2026-08-12 19:33:41");





CREATE TABLE `kp_tot_status` (
  `tot_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tot_status` int NOT NULL DEFAULT '2',
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int NOT NULL AUTO_INCREMENT,
  `counter` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kp_tot_status_tot_id` (`tot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_tot_status VALUES("6","5","2","2025-09-01 13:14:17","6","0","2026-07-23 18:56:53");





CREATE TABLE `kp_user_hist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `trans` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `trans_by` int NOT NULL,
  `shift` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO kp_user_hist VALUES("1","8","USER PIN CHANGE","8","2025-09-01","2025-09-01 04:40:42");
INSERT INTO kp_user_hist VALUES("2","5","USER PIN CHANGE","5","2025-09-01","2025-09-01 05:07:14");
INSERT INTO kp_user_hist VALUES("3","8","ASSIGNED CASHIER","0","2025-09-04","2025-09-05 05:45:43");
INSERT INTO kp_user_hist VALUES("4","2","ASSIGNED CASHIER","1","2025-09-04","2025-09-05 16:34:09");
INSERT INTO kp_user_hist VALUES("5","7","ASSIGNED CASHIER","0","2025-09-06","2025-09-06 13:01:26");
INSERT INTO kp_user_hist VALUES("6","9","USER PIN CHANGE","9","2025-09-08","2025-09-08 07:34:09");
INSERT INTO kp_user_hist VALUES("7","6","USER PIN CHANGE","6","2025-09-10","2025-09-10 11:31:34");
INSERT INTO kp_user_hist VALUES("8","5","USER PIN CHANGE","5","2025-09-12","2025-09-12 17:40:13");
INSERT INTO kp_user_hist VALUES("9","9","USER PIN CHANGE","9","2025-09-16","2025-09-16 15:24:57");
INSERT INTO kp_user_hist VALUES("10","9","USER PIN CHANGE","9","2025-09-18","2025-09-18 06:59:27");
INSERT INTO kp_user_hist VALUES("11","14","USER PIN CHANGE","14","2025-09-27","2025-09-27 13:57:55");
INSERT INTO kp_user_hist VALUES("12","15","USER PIN CHANGE","15","2025-09-27","2025-09-27 14:07:37");
INSERT INTO kp_user_hist VALUES("13","15","USER PIN CHANGE","15","2025-09-27","2025-09-27 14:08:11");
INSERT INTO kp_user_hist VALUES("14","6","ASSIGNED CASHIER","1","0000-00-00","2025-09-28 04:54:50");
INSERT INTO kp_user_hist VALUES("15","7","ASSIGNED CASHIER","1","2025-09-28","2025-09-28 05:32:32");
INSERT INTO kp_user_hist VALUES("16","10","USER PIN CHANGE","10","2025-09-28","2025-09-28 11:08:20");
INSERT INTO kp_user_hist VALUES("17","4","USER PIN CHANGE","4","2025-10-25","2025-10-25 08:53:27");
INSERT INTO kp_user_hist VALUES("18","7","ASSIGNED CASHIER","1","2025-11-01","2025-11-01 06:37:30");
INSERT INTO kp_user_hist VALUES("19","5","USER PIN CHANGE","5","2025-11-15","2025-11-15 17:04:28");
INSERT INTO kp_user_hist VALUES("20","8","USER PIN CHANGE","8","2025-11-16","2025-11-16 09:19:42");
INSERT INTO kp_user_hist VALUES("21","4","USER PIN CHANGE","4","2025-11-26","2025-11-26 09:33:17");
INSERT INTO kp_user_hist VALUES("22","5","USER PIN CHANGE","5","2025-12-19","2025-12-18 20:37:34");
INSERT INTO kp_user_hist VALUES("23","4","USER PIN CHANGE","4","2025-12-29","2025-12-29 13:18:56");
INSERT INTO kp_user_hist VALUES("24","4","USER PIN CHANGE","4","2026-01-29","2026-01-29 09:42:57");
INSERT INTO kp_user_hist VALUES("25","4","USER PIN CHANGE","4","2026-02-18","2026-02-18 09:43:34");
INSERT INTO kp_user_hist VALUES("26","4","USER PIN CHANGE","4","2026-04-08","2026-04-08 09:49:24");
INSERT INTO kp_user_hist VALUES("27","8","USER PIN CHANGE","8","2026-06-05","2026-06-05 18:00:58");
INSERT INTO kp_user_hist VALUES("28","16","USER PIN CHANGE","16","2026-06-05","2026-06-05 18:01:01");
INSERT INTO kp_user_hist VALUES("29","4","USER PIN CHANGE","4","2026-06-07","2026-06-07 18:56:48");
INSERT INTO kp_user_hist VALUES("30","4","USER PIN CHANGE","0","0000-00-00","2026-06-08 12:59:09");
INSERT INTO kp_user_hist VALUES("31","4","USER PIN CHANGE","4","2026-06-10","2026-06-10 12:02:55");
INSERT INTO kp_user_hist VALUES("32","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:13:35");
INSERT INTO kp_user_hist VALUES("33","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:15");
INSERT INTO kp_user_hist VALUES("34","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:16");
INSERT INTO kp_user_hist VALUES("35","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:16");
INSERT INTO kp_user_hist VALUES("36","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:18");
INSERT INTO kp_user_hist VALUES("37","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:19");
INSERT INTO kp_user_hist VALUES("38","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:20");
INSERT INTO kp_user_hist VALUES("39","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:20");
INSERT INTO kp_user_hist VALUES("40","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:21");
INSERT INTO kp_user_hist VALUES("41","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:21");
INSERT INTO kp_user_hist VALUES("42","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:22");
INSERT INTO kp_user_hist VALUES("43","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:23");
INSERT INTO kp_user_hist VALUES("44","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:23");
INSERT INTO kp_user_hist VALUES("45","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:24");
INSERT INTO kp_user_hist VALUES("46","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:24");
INSERT INTO kp_user_hist VALUES("47","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:30");
INSERT INTO kp_user_hist VALUES("48","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:31");
INSERT INTO kp_user_hist VALUES("49","10","USER PIN CHANGE","10","2026-08-24","2026-08-24 20:16:31");





CREATE TABLE `kp_user_shift_hist` (
  `user_id` int NOT NULL,
  `shift` date NOT NULL,
  `opening` time DEFAULT NULL,
  `closing` time DEFAULT NULL,
  `opening_datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closing_datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_user_shift_hist VALUES("3","2026-08-24","11:16:43","","2026-08-24 11:16:43","2026-08-24 11:16:43");
INSERT INTO kp_user_shift_hist VALUES("4","2026-08-24","11:31:08","10:52:12","2026-08-24 11:31:08","2026-08-25 10:52:12");
INSERT INTO kp_user_shift_hist VALUES("5","2026-08-24","19:11:18","","2026-08-24 19:11:18","2026-08-24 19:11:18");
INSERT INTO kp_user_shift_hist VALUES("6","2026-08-24","19:15:23","","2026-08-24 19:15:23","2026-08-24 19:15:23");
INSERT INTO kp_user_shift_hist VALUES("7","2026-08-24","19:26:42","","2026-08-24 19:26:42","2026-08-24 19:26:42");
INSERT INTO kp_user_shift_hist VALUES("8","2026-08-24","19:37:43","","2026-08-24 19:37:43","2026-08-24 19:37:43");
INSERT INTO kp_user_shift_hist VALUES("9","2026-08-24","19:48:57","","2026-08-24 19:48:57","2026-08-24 19:48:57");
INSERT INTO kp_user_shift_hist VALUES("9","2026-08-24","19:48:58","","2026-08-24 19:48:58","2026-08-24 19:48:58");
INSERT INTO kp_user_shift_hist VALUES("10","2026-08-24","20:04:26","","2026-08-24 20:04:26","2026-08-24 20:04:26");
INSERT INTO kp_user_shift_hist VALUES("11","2026-08-24","21:27:32","","2026-08-24 21:27:32","2026-08-24 21:27:32");
INSERT INTO kp_user_shift_hist VALUES("12","2026-08-24","22:13:27","","2026-08-24 22:13:27","2026-08-24 22:13:27");





CREATE TABLE `kp_users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int DEFAULT NULL,
  `username` varchar(44) DEFAULT NULL,
  `password` varchar(99) DEFAULT NULL,
  `pin` varchar(11) DEFAULT NULL,
  `log_status` int NOT NULL DEFAULT '2',
  `work_status` int NOT NULL DEFAULT '2',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role_id` int DEFAULT NULL,
  `permissions` varchar(255) DEFAULT NULL,
  `del_status` int NOT NULL DEFAULT '2',
  `job` int NOT NULL DEFAULT '2',
  `drole` int NOT NULL DEFAULT '2',
  `shift_status` int NOT NULL DEFAULT '2',
  `cleared` int NOT NULL DEFAULT '2',
  `counter` int DEFAULT NULL,
  `counter_status` int NOT NULL DEFAULT '2',
  `user_status` int NOT NULL DEFAULT '2',
  `sys_default` int NOT NULL DEFAULT '2',
  `kpesa_role` int NOT NULL,
  `max_sale` decimal(12,2) NOT NULL DEFAULT '0.00',
  `profile_id` int NOT NULL DEFAULT '1',
  `user_work_status` tinyint NOT NULL DEFAULT '2',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username` (`username`),
  UNIQUE KEY `user_pin` (`pin`),
  UNIQUE KEY `staff_id` (`staff_id`),
  UNIQUE KEY `user_staff_id` (`staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

INSERT INTO kp_users VALUES("1","0","KTOUCH_ADMIN","100335","100335","2","2","2021-08-04 04:42:40","1","*","2","2","1","2","1","0","2","2","1","0","0.00","1","2");
INSERT INTO kp_users VALUES("2","1","ESSY","22517188","2010","2","2","2026-08-24 11:04:30","1","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("3","2","BELLAH","","1341185","2","2","2026-08-24 11:16:14","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("4","3","HARUN","","2020","2","2","2026-08-24 11:30:58","6","","2","2","1","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("5","4","LIZ","","1145","2","2","2026-08-24 11:58:52","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("6","6","REEN","","1412","2","2","2026-08-24 19:13:19","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("7","7","BRENDA","","1652018","2","2","2026-08-24 19:25:05","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("8","8","PETER","","3152016","2","2","2026-08-24 19:35:54","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("9","9","JECINTA","","2082708","2","2","2026-08-24 19:47:46","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("10","10","NANCY","","2540","2","2","2026-08-24 19:58:25","2","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("11","11","NANCY K","","072721","2","2","2026-08-24 21:27:06","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");
INSERT INTO kp_users VALUES("12","12","WANDIA","","1575360","2","2","2026-08-24 22:02:54","6","","2","2","2","2","1","0","2","2","2","0","0.00","1","2");





CREATE TABLE `kp_users_logger` (
  `log_user_id` int NOT NULL,
  `shift` date NOT NULL,
  `log_datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_description` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO kp_users_logger VALUES("1","2026-07-28","2026-07-28 22:52:48","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-28","2026-07-28 22:54:53","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-28","2026-07-28 23:12:14","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-28","2026-07-28 23:18:37","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-28","2026-07-28 23:33:29","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-29","2026-07-29 20:17:05","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-29","2026-07-29 20:22:22","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-29","2026-07-29 20:53:03","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-07-29","2026-07-29 21:31:19","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-05","2026-08-05 20:54:41","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-05","2026-08-05 20:55:53","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-05","2026-08-05 21:18:09","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-05","2026-08-05 22:36:24","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-07","2026-08-07 20:35:56","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-07","2026-08-07 20:35:58","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 16:48:22","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 16:52:42","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 18:12:53","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 18:36:25","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 19:05:18","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 19:15:00","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 20:05:48","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 20:25:58","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 21:26:46","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-08","2026-08-08 21:31:23","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-09","2026-08-09 21:54:37","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-09","2026-08-09 21:54:39","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-09","2026-08-09 21:54:40","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-09","2026-08-09 23:59:08","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 00:02:27","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 00:50:03","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 01:16:14","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 11:18:33","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 21:51:31","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-10","2026-08-10 22:38:10","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 12:06:31","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 12:51:31","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 15:16:40","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 18:15:01","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 18:24:51","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 19:37:08","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 20:17:54","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 20:17:55","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-11","2026-08-11 21:04:07","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 17:41:29","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 19:13:17","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:19:00","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:46:06","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:46:51","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:53:05","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:54:05","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 20:57:25","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 21:17:46","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-12","2026-08-12 21:34:37","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-14","2026-08-14 22:24:11","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-14","2026-08-14 22:24:50","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-14","2026-08-14 23:38:35","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-15","2026-08-15 00:05:54","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-15","2026-08-15 00:26:54","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-15","2026-08-15 21:45:05","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-15","2026-08-15 21:45:08","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-15","2026-08-15 22:50:22","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-16","2026-08-16 14:17:58","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-16","2026-08-16 14:17:59","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-16","2026-08-16 16:30:35","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 12:28:12","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 13:15:32","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 13:34:29","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 14:28:11","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 14:59:33","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 15:55:29","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 17:30:30","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 17:33:38","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 18:10:30","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 18:34:07","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-17","2026-08-17 18:39:58","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-18","2026-08-18 11:50:14","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-18","2026-08-18 18:49:37","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 10:35:55","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 13:10:23","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 18:08:57","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 18:09:43","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 18:14:09","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 21:04:40","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-19","2026-08-19 21:34:30","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-20","2026-08-20 00:19:21","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-21","2026-08-21 20:57:17","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-21","2026-08-21 21:06:08","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-22","2026-08-22 21:59:32","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 00:15:58","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 10:57:14","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 11:10:37","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 11:32:09","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 12:09:38","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 12:09:48","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 12:27:14","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 12:27:19","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 12:28:04","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 12:37:59","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 13:23:51","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 13:30:50","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 13:47:26","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 14:04:13","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 14:06:10","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 14:07:10","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 14:09:02","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 14:12:21","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 14:14:59","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 15:03:27","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 15:04:00","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 15:26:09","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 15:36:56","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 16:18:46","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 16:20:58","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 16:21:52","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 17:08:17","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 17:16:00","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 17:43:56","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 17:45:01","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 17:45:23","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 17:46:08","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 17:46:46","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 17:47:21","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 18:17:00","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 18:17:46","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 18:51:02","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 18:51:07","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 18:55:17","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 19:10:20","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 19:15:28","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 19:24:23","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 19:24:25","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("7","2026-08-24","2026-08-24 19:26:45","LOGGED IN");
INSERT INTO kp_users_logger VALUES("7","2026-08-24","2026-08-24 19:27:12","LOGGED IN");
INSERT INTO kp_users_logger VALUES("8","2026-08-24","2026-08-24 19:37:56","LOGGED IN");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 19:47:35","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("9","2026-08-24","2026-08-24 19:49:01","LOGGED IN");
INSERT INTO kp_users_logger VALUES("8","2026-08-24","2026-08-24 19:58:40","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:04:46","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:25","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:25","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:26","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:27","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:32","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:32","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:54","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:55","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:55","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:58","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:11:59","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:12:00","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:12:09","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:14:46","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:14:49","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:15:15","LOGGED IN");
INSERT INTO kp_users_logger VALUES("10","2026-08-24","2026-08-24 20:18:22","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:02:51","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:15:33","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:15:57","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:16:04","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:16:16","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 21:16:30","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 21:19:37","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 21:21:27","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:24:42","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("11","2026-08-24","2026-08-24 21:27:41","LOGGED IN");
INSERT INTO kp_users_logger VALUES("9","2026-08-24","2026-08-24 21:51:41","LOGGED IN");
INSERT INTO kp_users_logger VALUES("8","2026-08-24","2026-08-24 21:54:33","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 21:55:26","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("8","2026-08-24","2026-08-24 21:56:11","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 21:57:14","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 21:57:21","LOGGED IN");
INSERT INTO kp_users_logger VALUES("8","2026-08-24","2026-08-24 21:58:15","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 22:08:18","ADMIN LOGIN");
INSERT INTO kp_users_logger VALUES("12","2026-08-24","2026-08-24 22:13:40","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 22:19:56","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 22:19:58","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:24:18","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:25:20","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:27:07","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:27:36","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:27:55","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 22:30:56","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 22:47:26","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:48:15","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:48:20","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 22:49:15","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 23:00:47","LOGGED IN");
INSERT INTO kp_users_logger VALUES("1","2026-08-24","2026-08-24 23:04:00","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("4","2026-08-24","2026-08-24 23:04:11","LOGGED IN TO OPEN COUNTERS");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 23:04:40","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 23:11:16","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 23:12:16","LOGGED IN");
INSERT INTO kp_users_logger VALUES("3","2026-08-24","2026-08-24 23:13:27","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 23:15:47","LOGGED IN");
INSERT INTO kp_users_logger VALUES("11","2026-08-24","2026-08-24 23:17:10","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 23:18:07","LOGGED IN");
INSERT INTO kp_users_logger VALUES("6","2026-08-24","2026-08-24 23:18:29","LOGGED IN");
INSERT INTO kp_users_logger VALUES("11","2026-08-24","2026-08-24 23:19:52","LOGGED IN");
INSERT INTO kp_users_logger VALUES("4","2026-08-25","2026-08-25 10:51:38","LOGGED IN TO OPEN COUNTERS");





CREATE TABLE `kp_variant_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `brand` varchar(100) NOT NULL DEFAULT '',
  `size_val` varchar(100) NOT NULL DEFAULT '',
  `color` varchar(100) NOT NULL DEFAULT '',
  `uom` varchar(50) NOT NULL DEFAULT '',
  `notes` text,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=pending,1=approved,2=rejected',
  `user_id` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_var_req_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;






CREATE TABLE `kp_ware_req` (
  `req_id` int NOT NULL,
  `req_user` int NOT NULL,
  `req_shift` date NOT NULL,
  `issue_user` int DEFAULT NULL,
  `issue_status` int NOT NULL DEFAULT '2',
  `issue_shift` date DEFAULT NULL,
  `rec_status` int NOT NULL DEFAULT '2',
  `rec_user` int NOT NULL DEFAULT '2',
  `rec_shift` date DEFAULT NULL,
  `datime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `req_status` int NOT NULL DEFAULT '2',
  `s2s` int NOT NULL DEFAULT '2',
  `counter` varchar(11) DEFAULT NULL,
  UNIQUE KEY `req_id` (`req_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_ware_req_status` (
  `po_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '2',
  `po_status` int NOT NULL,
  `sup_id` int NOT NULL,
  `day` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `kp_ware_stock_track` (
  `item_id` int NOT NULL,
  `po_id` int DEFAULT NULL,
  `grn_id` int NOT NULL,
  `req_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `status` varchar(6) NOT NULL,
  `shift` date NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






CREATE TABLE `message_claim` (
  `id` int NOT NULL AUTO_INCREMENT,
  `msg_id` int NOT NULL,
  `user_id` int NOT NULL,
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_status` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;






CREATE TABLE `mpesa_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` text COLLATE latin1_general_ci NOT NULL,
  `split_status` int NOT NULL,
  `print_status` int NOT NULL DEFAULT '2',
  `datime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;






CREATE TABLE `split_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_general_ci NOT NULL,
  `phone` text COLLATE latin1_general_ci NOT NULL,
  `code` varchar(10) COLLATE latin1_general_ci NOT NULL,
  `amount` text COLLATE latin1_general_ci NOT NULL,
  `account` text COLLATE latin1_general_ci NOT NULL,
  `balance` text COLLATE latin1_general_ci NOT NULL,
  `cost` text COLLATE latin1_general_ci NOT NULL,
  `date` text COLLATE latin1_general_ci NOT NULL,
  `time` text COLLATE latin1_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `print_status` int NOT NULL DEFAULT '2',
  `user_id` int NOT NULL,
  `assign_status` int NOT NULL DEFAULT '2',
  `status` int NOT NULL DEFAULT '2',
  `shift` date NOT NULL,
  `claim_status` int NOT NULL DEFAULT '2',
  `claim_staff` int NOT NULL,
  `app_status` int NOT NULL DEFAULT '2',
  `app_user` int NOT NULL,
  `app_datime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;




