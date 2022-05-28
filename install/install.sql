CREATE TABLE IF NOT EXISTS `smart_price_history` (
  `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_sku` varchar(10) COLLATE utf8_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `product_id` (`product_id`),
  KEY `product_sku` (`product_sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;