CREATE TABLE IF NOT EXISTS `itemdb_bots` (
  `BotName` varchar(50) NOT NULL,
  `Passkey` varchar(255) NOT NULL,
  PRIMARY KEY (`BotName`,`Passkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `itemdb_items` (
  `ToonName` varchar(50) NOT NULL,
  `BotName` varchar(100) NOT NULL,
  `GuildName` varchar(100) NOT NULL,
  `ServerName` varchar(20) NOT NULL,
  `ItemName` varchar(100) NOT NULL,
  `LowID` varchar(10) NOT NULL,
  `HighID` varchar(10) NOT NULL,
  `LowLVL` varchar(10) NOT NULL,
  `HighLVL` varchar(10) NOT NULL,
  `LowCRC` varchar(50) NOT NULL,
  `MidCRC` varchar(50) NOT NULL,
  `HighCRC` varchar(50) NOT NULL,
  `Color` varchar(10) NOT NULL,
  `InsertOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `itemdb_recipes` (
  `LowID` varchar(10) NOT NULL,
  `HighID` varchar(10) NOT NULL,
  `Qty` int(11) DEFAULT NULL,
  `Item_LowID` varchar(10) NOT NULL,
  `Item_HighID` varchar(10) NOT NULL,
  `Item_Qty` int(11) DEFAULT NULL,
  `InsertOn` datetime DEFAULT NULL,
  PRIMARY KEY (`LowID`,`HighID`,`Item_LowID`,`Item_HighID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `itemdb_recipe_crafters` (
  `ToonName` varchar(50) NOT NULL,
  `BotName` varchar(100) DEFAULT NULL,
  `GuildName` varchar(100) DEFAULT NULL,
  `LowID` varchar(10) NOT NULL,
  `HighID` varchar(10) NOT NULL,
  `InsertOn` datetime DEFAULT NULL,
  PRIMARY KEY (`ToonName`,`LowID`,`HighID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;