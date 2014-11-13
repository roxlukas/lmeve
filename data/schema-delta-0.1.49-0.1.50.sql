--schema delta for page cache

CREATE TABLE IF NOT EXISTS `lmpagecache` (
  `pageLabel` varchar(32) NOT NULL,
  `pageContents` text NOT NULL,
  `timestamp` TIMESTAMP NOT NULL,
  PRIMARY KEY (`pageLabel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;