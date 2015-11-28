<?php 
/****h* Introduction/create_tables.php
 * NAME
 *		create_tables.php
 * USAGE
 *		Just call this script to create the needed tables in your database		 
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk) -
 * 		Every hour, because it's all good.
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		23 nov. 2003
 * HISTORY
 *		6 dec.  2003: Fixed some things. Added more complex regex's.
 *		12 feb. 2004: Fixed some "" things - changed them to ''
 *		19 maj  2004: Rewritten all comments, so they support ROBODOC - kinky stuff :-D
 ****/

/****** create_tables.php/Include_stuff
 * FUNCTION
 *		Include classes and the config file.
 ****/
include('config.php');
require('class_command.php');

Database::connect(); //Connect to the database
 

/****** create_tables.php/Create_tables_in_db
 * FUNCTION
 *		Creates the tables in our database.
 ****/

//Create content_description table
$query = "CREATE TABLE content_description (
  externalpage text NOT NULL,
  title text NOT NULL,
  description text NOT NULL,
  ages varchar(100) NOT NULL default '',
  mediadate date NOT NULL default '0000-00-00',
  priority tinyint(2) NOT NULL default '0'
) TYPE=MyISAM;\n";
Database::sqlWithoutAnswer($query); //Create :)

//Create content_links table
$query = "CREATE TABLE content_links (
  catid bigint(8) NOT NULL default '0',
  topic text NOT NULL,
  type varchar(20) NOT NULL default '',
  resource text NOT NULL,
  KEY catid (catid)
) TYPE=MyISAM;\n";
Database::sqlWithoutAnswer($query); //Create :)

//Create structure table
$query = "CREATE TABLE structure (
  catid bigint(8) NOT NULL default '0',
  name text NOT NULL,
  title varchar(255) NOT NULL default '',
  description text NOT NULL default '',
  lastupdate datetime NOT NULL default '0000-00-00 00:00:00',
  KEY catid (catid)
) TYPE=MyISAM;\n";
Database::sqlWithoutAnswer($query); //Create :)

//Create datatypes table
$query = "CREATE TABLE datatypes (
  catid bigint(8) NOT NULL default '0',
  type varchar(20) NOT NULL default '',
  resource text NOT NULL,
  KEY catid (catid)
) TYPE=MyISAM;\n";
Database::sqlWithoutAnswer($query); //Create :)

Basic::printToConsole("\nTables in the database (" . DB_DATABASE . ") are successfully created!\n");

Database::close(); //Close connection
?>
