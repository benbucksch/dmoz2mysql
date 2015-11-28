<?php
/****h* Introduction/class_clean.php
 * NAME
 *		class_clean.php	
 * USAGE
 *		This file holds the CleanXML class, which is used
 *		to clean up dirty ODP XML.		 
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
 
/*****c* class_clean.php/CleanXML
* FUNCTION
*		This class main purpose is to clean dirty XML.
* PROPERTIES
*		_dirty_data:
*			@string - holds the "dirty" data
*		_clean_data:
*			@string - holds the clean data
*		write_fp:
*			@file_pointer - Used to open a fp to the write file
* METHODS
*		cleanFile, _correction, _writeTheCleanData
* EXTENDS
*		Dot
****/
class CleanXML extends Dot
{
	var $_dirty_data;
	var $_clean_data;
	var $_write_fp;
	
	/*****f* CleanXML/cleanFile
	* FUNCTION
	*		This is the functions that cleans the file. First it creates
	*		2 file pointers. One FP to the read file and one FP
	*		to the write file. Then it's just reads the read file until
	*		EOF. While reading it inserst the dirty data in the _correction
	*		function, in here the data is cleaned. When this process is finished
	*		we have a new "clean" file called clean_$__filename. 
	*		Then the script deletes the dirty file and renames the clean_$__filename
	*		to just $__filename.
	* INPUT
	*		$__filename (@string): 
	*			The filename of the file we are about to clean	
	* ACCESS
	*		Public
	* USES
	*		_correction, _writeTheCleanData
	****/	
	function cleanFile($__filename)
	{
		Basic::printToConsole("Cleaning $__filename\nThis could take some time!");
		Basic::printToConsole("\nStatus: ");

		//Create a dot object - used to print the dot
		$this->count = 0;
		$this->frequency = 15000; //Set the frequency
		
		//Open 2 file pointers - one for read and one for write
		$__fp = fopen($__filename, 'r');
		$this->_write_fp = fopen('clean_' . $__filename , 'w');
		
		//Continue until you have reached end of file
		while(!feof($__fp))
		{
			$this->_dirty_data = fgets($__fp, 8192); //Get some dirty data
			
			$this->_correction(); //Clean the dirty data
			
			$this->_dirty_data = ''; //Reset dirty data
		}
		
		fclose($__fp); //Close the pointer
		fclose($this->_write_fp); //Close the pointer
		
		unlink($__filename); //Delete the old file
		
		//Rename the clean file to the old file
		rename('clean_' . $__filename, $__filename);
		
		Basic::printToConsole("\nFinished!\n");
	}
	
	/*****f* CleanXML/_correction
	* FUNCTION
	*		Internal function that cleans the XML data.
	*		It cleans the data for tags, and unsupported chars.
	*		RegExp patterns:
	*			'=<[biu]>(.*)</[biu]>=i', '=&(?!amp;)=i', '=<strong>(.*)</strong>=i',
	*			'=[\x00-\x08\x0b-\x0c\x0e-\x1f]=
	*		Corrections
	*			"$1", "&amp;", "$1", ''	
	* ACCESS
	*		Private
	* USED BY
	*		cleanFile
	****/
	function _correction()
	{
		//The things we want to clean
		$pattern = array('=<[biu]>(.*)</[biu]>=i', '=&(?!amp;)=i', '=<strong>(.*)</strong>=i', '=[\x00-\x08\x0b-\x0c\x0e-\x1f]=');
		
		//The corrections (the stuff we insert)
		$contractions = array("$1", '&amp;', "$1", ''); 
		
		//Do the the magic with RegExp's
		$this->_clean_data = preg_replace($pattern, $contractions, $this->_dirty_data);
		
		$this->_writeTheCleanData(); //Call the method which writse the clean data.
	}
	
	/*****f* CleanXML/_writeTheCleanData
	* FUNCTION
	*		This function writes our clean data to our write file pointer.
	*		At last it resets the _clean_data.	
	* ACCESS
	*		Private
	* USED BY
	*		_correction
	****/
	function _writeTheCleanData()
	{
		$this->PrintDot(); //Print dot
		
		fwrite($this->_write_fp, $this->_clean_data); //Write to our write FP
		
		$this->_clean_data = ''; //Reset the value
	}
}

?>
