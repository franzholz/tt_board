#
# Table structure for table 'tt_board'
#
CREATE TABLE tt_board (
  author varchar(80) DEFAULT '' NOT NULL,
  email varchar(80) DEFAULT '' NOT NULL,
  city varchar(255) DEFAULT '' NOT NULL,
  subject tinytext,
  message text,
  parent int(10) unsigned DEFAULT '0' NOT NULL,
  notify_me tinyint(3) unsigned DEFAULT '0' NOT NULL,
  doublePostCheck int(11) unsigned DEFAULT '0' NOT NULL,
  cr_ip varchar(15) DEFAULT '' NOT NULL,
  reference text,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY parent_select (pid,parent),
  KEY postcheck (doublePostCheck)
);
