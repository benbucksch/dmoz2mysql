<?php
/****h* Introduction/class_download.php
 * NAME
 *		class_download.php
 * USAGE
 *		Start of to set the download_speed.
 *		Next set the filename to download.
 *		Next delete the old file by calling the method delete()
 *		Next setPath (the patch of the file we wish to download).
 *		Next call method download() to download it
 *		Next call method extract() to extract it
 * FUNCTION
 *		This files holds one class (DownloadFile). This class is used to
 *		download and extract the DMOZ data dumps.
 *		Lucky for us - they are packed with Gunzip - and PHP supports gunzip.. yay ;)
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk)
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		23 nov. 2003
 * HISTORY
 *		23 nov. 2003: Created the class
 *		6  dec. 2003: Just added the change log!
 *		7 dec.  2003: Added support for the class CheckURL
 *		12 feb  2004: Remade the gunzip extracter. Now it rocks!
 *					  Fixed some "" to ''.
 *		19 maj  2004: Rewritten alle comments, so they support ROBODOC - kinky stuff :-D
 * USES
 *		CheckURL, Dot		
 ****/

/*****c* class_download.php/DownloadExtractFile
 * FUNCTION
 *		This class main purpose is to clean dirty XML.
 * PROPERTIES
 *		filename (@string):
 *			Filename of our file, that we want to make magic to.
 *		path (@string):
 *			The path of the file we are about to download
 *		download_speed (@int):
 *			The download speed (in KB)
 * METHODS
 *		setPath, setDownloadSpeed, delete, download, extract.
 * EXTENDS
 *		Dot
 ****/
class DownloadExtractFile extends Dot
{
	var $filename; 
 	var $path;
 	var $download_speed;
 	
	/*****f* DownloadExtractFile/setPath
	* FUNCTION
	*		Set path of the file we are about to download.	
	* INPUT
	*		__path (@string):
	*			An URL..
	* ACCESS
	*		Public
	****/
 	function setPath($__path) 
 	{
 		$this->path = $__path;
 	}
 	
	/*****f* DownloadExtractFile/setDownloadSpeed
	* FUNCTION
	*		Set the download speed
	* INPUT
	*		__speed (@int):
	*			Download speed (KB) 25 i.e. 25 KB/s
	* ACCESS
	*		Public
	****/
 	function setDownloadSpeed($__speed) 
 	{
 		$this->downloadSpeed = $__speed;
 	}
 	
 	/*****f* DownloadExtractFile/setFilename
	* FUNCTION
	*		Set the download speed
	* INPUT
	*		__filename (@string):
	*			What should our file be named :)
	* ACCESS
	*		Public
	****/	
 	function setFilename($__filename) 
 	{
 		$this->filename = $__filename;
 	}
 	
 	/*****f* DownloadExtractFile/delete
	* FUNCTION
	*		Delete a the old file, if it's there.			
	* ACCESS
	*		Public
	****/	
 	function delete() 
 	{
 		if(@!unlink($this->filename.'.u8.gz')) 
 		{
 			Basic::printToConsole("Warning: Could not elete the old gun zipped file. Maybe it isn't created yet.\n");
 		}

		if(@!unlink($this->filename)) 
 		{
 			Basic::printToConsole("Warning: Could not delete the old RDF file. Maybe it isn't created yet.\n");
 		}
 	}
 	
 	/*****f* DownloadExtractFile/download
	* FUNCTION
	*		Download our file
	* USES
	*		CheckURL, Dot	
	* ACCESS
	*		Public
	****/	
 	function download()
 	{
 		$this->count = 0;
 		
 		//
 		//Download file information
 		//
 		Basic::printToConsole('Downloading file information:  ', false);
 		$filecheck = new CheckURL; //Create a new object 
    	$filecheck->downloadHeaders($this->path); //Download headers from a URL 
    	$filecheck->lastModified(); //Run the function that finds when the URL(document) was last modified. 
    	$filecheck->contentLenght(); //Run the function that finds content lenght
    	
    	$filesize = round($filecheck->content_lenght/1024); //Create a variable filesize
    	
    	Basic::printToConsole('DOWNLOADED', false);

 		$this->frequency = 100; //Set the frequency
		Basic::printToConsole("\nThe script is downloading " . $this->path . "\nThe file is downloaded with " . $this->downloadSpeed . " KB/s\nSize of the file is: $filesize KB\nThe DMOZ data dumps were last updated: " . $filecheck->last_modified ."\n\nDownloading please wait");
		 		
 		//Open the url of the structure rdf file
		 if(!($file = fopen($this->path,"r")))
		 {
			 basic::error('Fatal error', 'I/O error! Could not download the file');
		 }
		
		//Open the file we write to
		if(!$openfile = fopen($this->filename . ".u8.gz", 'w'))
		{
			basic::error('Fatal error', 'Cannot open the file ('.$this->filename.'.u8.gz)');
		}
		
		//Write the data
		while ($data = fread($file, round(DOWNLOAD_SPEED * 1024))) 
		{
			if(!fwrite($openfile, $data)) 
			{
				basic::error('Fatal error', 'Cannot write to file ' . $this->filename . '');
			}
			
			//Don't write the dot every time
			$this->PrintDot();
			
		}
		fclose ($file);
		fclose($openfile);
		Basic::printToConsole("\n\n " . strtoupper($this->filename) . ".U8.GZ WAS SUCCESSFUL DOWNLOADED \n Download URL: " . $this->path . "\n");
 	}
 	
 	/*****f* DownloadExtractFile/extract
	* FUNCTION
	*		Extract the downloaded gunzip file.
	* ACCESS
	*		Public
	****/ 	
	function extract()
 	{
 		Basic::printToConsole("\n\nExtracting the gunzipped file...", False);
 		
 		 //Open the downloaded gunzip file
		 if(!($file = gzopen($this->filename.".u8.gz","r")))
		 {
		 	 basic::error('Fatal error', 'I/O error! Could not open the downloaded file!');
		 }
		 
		 		
		//Open the file we write to
		if(!$openfile = fopen($this->filename,'a'))
		{
			basic::error('Fatal error', 'Cannot open the file (' . $this->filename . ')');
		}
		
		//Write the data
		while (!gzeof($file))
		{
			$buff = gzgets($file, 4096) ;
			fputs($openfile, $buff) ;
		}                    
		
		fclose ($openfile);
		gzclose ($file);
		Basic::printToConsole("\n\n " . strtoupper($this->filename) . ".U8.GZ WAS SUCCESSFUL EXTRACTED! \n Filename: " . $this->filename . "\n");
 	}	
} 	

?>
