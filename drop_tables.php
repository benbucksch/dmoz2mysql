<?php 
/****h* Introduction/drop_tables.php
 * NAME
 *		drop_tables.php
 * USAGE
 *		Just call this script to delete the created tables.
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk) -
 * 		Every hour, because it's all good.
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		23 nov. 2003
 * HISTORY
 *		12 feb. 2004: Fixed some "" things - changed them to ''
 ****/

/****** drop_tables.php/Include_stuff
* FUNCTION
*		Include classes and the config file.
****/
include("config.php");
require("class_command.php");

Database::connect(); //Connect to the database

/****** drop_tables.php/Drop_tables
* FUNCTION
*		Drop the tables in the database
****/
//Create content_description table
$query = 'DROP TABLE content_description, content_links, datatypes, structure';
Database::sqlWithoutAnswer($query); //Create :)

Basic::printToConsole("\nTables in the database (" . DB_DATABASE . ") are successfully deleted!\n");

Database::close(); //Close connection
?>
