

CREATE TABLE IF NOT EXISTS `llx_dinamicprice_categorie` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_categorie` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `value` double(20,2) NOT NULL,
  `user` int(11) NOT NULL,
  `entity` int(11) NOT NULL,
  `tms` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


