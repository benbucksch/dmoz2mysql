<?php 
/****h* Introduction/config.php
 * NAME
 *		config.php	
 * USAGE
 *		In this file you have several options available - - to
 *		custimize the script for your use. *		 
 * TYPE
 *		Just a file that contains some important information
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk)
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		3 dec. 2003
 * HISTORY
 *		12 feb. 2004: Added some features
 *		19 maj  2004: Rewritten alle comments, so they support ROBODOC - kinky stuff :-D 
 *		24 maj 2004: Added console color (CONSOLE_COLOR) option
 ******
 ****/

/****** config.php/Common_global_values
* FUNCTION
*		Here you specify common global value:
*			ECHO_STATS (@bol):
*				If ECHO_STATS is true -  statistics will be displayed 
*				(stats are used when parsing the RDF documents).
*			ECHO_STATS_FREQUNCY (@int):
*				A value that contains what frequncy the stats should be displayed.
*			DOWNLOAD_SPEED (@int): 
*				Download speed. in kilobyte
*			CONSOLE_COLOR (@string):
*				Define what color you want to use. You can set it to:
*				black, red, green and blue
****/
DEFINE('CONSOLE_COLOR', 'green');
DEFINE('ECHO_STATS', true);
DEFINE('ECHO_STATS_FREQUNCY', 50000);
DEFINE('DOWNLOAD_SPEED', 50);

/****** config.php/Database_information
* FUNCTION
*		Here you specify your database information:
*			DB_SERVER (@string):
*				The server address - could be localhost or a URL
*			DB_USER (@string):
*				Database username
*			DB_PASSWORD (@string): 
*				Password for your database
*			DB_DATABASE (@string):
*				The database you have to create - dmoz is a good name or anything else. 
****/
DEFINE('DB_SERVER', 'localhost');
DEFINE('DB_USER', '');
DEFINE('DB_PASSWORD', '');

DEFINE('DB_DATABASE', 'dmoz');

/****** config.php/Script_properties
* FUNCTION
*		In this script you specify what the script shell do:
*			CHECK_FOR_UPDATES (@bool):
*				If it's set to true, then the script will check for updated DMOZ dumps.
*			STRUCTURE_DOWNLOAD_AND_EXTRACT (@bool):
*				If it's set to true, then the script will download and extract
*				the structure DMOZ dump.
*			STRUCTURE_CLEAN (@bool): 
*				If it's set to true, then the script will clean the structure file.
*			STRUCTURE_PARSE_N_INSERT (@bool):
*				If it's set to true, then the script will parse the structure rdf file 
*				and insert data into the MySQL db.
*			CONTENT_DOWNLOAD_AND_EXTRACT (@bool):
*				If it's set to true, then the script will download 
*				and extract the content DMOZ dump.
*			CONTENT_CLEAN (@bool): 
*				If it's set to true, then the script will clean the content file.
*			CONTENT_PARSE_N_INSERT (@bool):
*				If it's set to true, then the script will parse the content rdf file and
*				insert data into the MySQL db.
****/

DEFINE('CHECK_FOR_UPDATES', true); 

DEFINE('STRUCTURE_DOWNLOAD_AND_EXTRACT', true); 
DEFINE('STRUCTURE_CLEAN', true);
DEFINE('STRUCTURE_PARSE_N_INSERT', true); 

DEFINE('CONTENT_DOWNLOAD_AND_EXTRACT', true);
DEFINE('CONTENT_CLEAN', true);
DEFINE('CONTENT_PARSE_N_INSERT', true);

/****** config.php/Filenames
* FUNCTION
*		Here you specify common global value:
*			$rdffile_structure (@string):
*				Structure RDF filename 
*			$rdffile_content (@string):
*				Content RDF filename
* WARNING
*		No need to edit those filenames!
****/

DEFINE('FILE_RDF_STRUCTURE', 'structure.rdf'); //No need to edit this!
DEFINE('FILE_RDF_CONTENT', 'content.rdf'); //No need to edit this!

?>
