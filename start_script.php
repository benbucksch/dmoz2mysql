<?php 
/****h* Introduction/start_script.php
 * NAME
 *		start_script.php
 * USAGE
 *		First be sure you have configured this script (see config.php).
 *		Then run this script from promt. Change the directory with cd to
 *		the place where your start_script.php is located. 
 *		And then type this:
 *			UNIX:
 * 				You probably have a symbolic link, if not search google:
 *				php start_script.php
 *			Windows:
 *				Locate where you php.exe is. If it is C:\php\php.exe
 *				then do following:
 *				C:\php\php.exe start_script.php			
 * FUNCTION
 *		This scripts initializes all the classes and runs the script.
 *		Best way to configure this script is by config.php. But you may
 *		also wish to edit it in here. This script is well documented, 
 *		it should not be that hard.
 * TYPE
 *		A script used to create classes.
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk)
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		8 dec. 2003
 * HISTORY
 *		12 feb. 2004: Remade it. You now control this script from config.php!
 *		13 feb. 2004: Added class_split.php
 *		15 maj  2004: Remove class_split.php - New XML parser no need for it :-)
 *		19 maj  2004: Rewritten alle comments, so they support ROBODOC - kinky stuff :-D 
 *		24 maj  2004: Well lot's of improvements.. it's sick ;)
 * USES
 *		All classes
 ****/

/****** start_script.php/set_time_limit
 * FUNCTION
 *		Set maximum execution time to none
 ****/
set_time_limit(0);

/****** start_script.php/Include_stuff
 * FUNCTION
 *		Include classes and the config file.
 ****/
include('config.php');
require('class_command.php');
require('class_clean.php');
require('class_parse.php');
require('class_download.php');

/****** start_script.php/Common_calls
 * FUNCTION
 *		Common calls: connect to the database
 *		Create the objects:
 *			check_url (checks a specific URL)
 *			downloadfile (downloads files)
 *			clean_xml (cleans the XML files)
 *			parse_xml_structure
 *			parse_xml_content
 ****/
Database::connect(); //Connect to the database
$filecheck = new CheckURL; //Create a object that can check a specific URL
$file = new DownloadExtractFile; //Create a new object
$clean_xml = new CleanXML;
$parse_structure = new ParseXMLStructure; //Create a new object
$parse_content = new ParseXMLContent;


/****** start_script.php/Check_for_updates
 * FUNCTION
 *		This section download the headers for the structure file. 
 *		Then it checks when the file was last modified.
 *		at last it compares it with the users last update.
 ****/
if(CHECK_FOR_UPDATES) {
	Basic::printToConsole('CHECKING FOR UPDATES...');
	$filecheck->downloadHeaders('http://rdf.dmoz.org/rdf/structure.rdf.u8.gz'); //Download headers from a URL 
	$filecheck->lastModified(); //Run the function that finds when the URL(document) was last modified.
	$filecheck->lastModifiedCompare(); //Compare the DMOZ file, with your last updated
	Basic::printToConsole("OK. Ready for an update!\n");
}

/****** start_script.php/Structure_file
 * SECTION
 *		Calls that handle the DMOZ structure file	
 ****/

/****** Structure_file/Download
 * FUNCTION
 *		Download the structure file	
 ****/
if(STRUCTURE_DOWNLOAD_AND_EXTRACT) {
	$file->setDownloadSpeed(DOWNLOAD_SPEED); //Set download speed
	$file->setFilename(FILE_RDF_STRUCTURE); //Set the filename
	$file->delete(); //Delete the old file - if it's there
	$file->setPath('http://rdf.dmoz.org/rdf/structure.rdf.u8.gz'); //Set path of what file it is downloading.
	$file->download(); //Start the download
	$file->extract(); //Extract the file
}

/****** Structure_file/Clean_xml
 * FUNCTION
 *		Clean the structure file! (dirty xml - we don't like it :D)	
 ****/
if(STRUCTURE_CLEAN) {
	$clean_xml->cleanFile(FILE_RDF_STRUCTURE);
}

/****** Structure_file/Parse_and_insert
 * FUNCTION
 *		Parse and insert the structure RDF file into a database	
 ****/
if(STRUCTURE_PARSE_N_INSERT) {
	$parse_structure->setStartTime(); //Start time
	$parse_structure->setXMLFile(FILE_RDF_STRUCTURE); //Set what XML file to parse
	$parse_structure->startParse(); //Start parsing the document
}

/****** start_script.php/Content_file
 * SECTION 
 *		Calls that handle the DMOZ content file	
 ****/

/****** Content_file/Download
 * FUNCTION
 *		 Download the content file	
 ****/
if(CONTENT_DOWNLOAD_AND_EXTRACT) {
	$file->setFilename(FILE_RDF_CONTENT); //Set the filename
	$file->delete(); //Delete the old file - if it's there
	$file->setPath('http://rdf.dmoz.org/rdf/content.rdf.u8.gz'); //Set path of what file it is downloading.
	$file->download(); //Start the download
	$file->extract(); //Extract the file
}

/****** Content_file/Clean_xml
 * FUNCTION
 *		 Clean the content file! (dirty xml - we don't like it :D)	
 ****/
if(CONTENT_CLEAN) {
	$clean_xml->cleanFile(FILE_RDF_CONTENT);
}

/****** Content_file/Parse_and_insert
 * FUNCTION
 *		 Parse and insert the content RDF file into a database	
 ****/
if(CONTENT_PARSE_N_INSERT) {
	$parse_content->setStartTime(); //Start time
	$parse_content->setXMLFile(FILE_RDF_CONTENT); //Set what XML file to parse
	$parse_content->startParse(); //Start parsing the document
}

//Write to a lastupdate.data file
$filecheck->writeLastUpdate();

Basic::printToConsole('FINISHED - Farewell and thanks for the fish! :)');

Database::close(); //Close connection
?> 
