<?php
/****h* Introduction/class_command.php
 * NAME
 *		class_command.php	
 * USAGE
 *		This file holds some important classes. 
 *		Includes following classes:
 *		Database:
 *			A class that is used to handle database related stuff
 *		CheckURL
 *			A class that checks a give URL
 *		Dot
 *			A class that handles the dot printing in command promt
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk) -
 * 		Every hour, because it's all good.
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		23 nov. 2003
 * HISTORY
 *		6 dec.  2003: Just added the change log!
 *		7 dec.  2003: Inserted the Dot class
 *		12 feb. 2004: Fixed some "" things in variables.. with ''
 *		19 maj  2004: Rewritten all comments, so they support ROBODOC - kinky stuff :-D
 ****/

/*****c* class_command.php/Basic
 * FUNCTION
 *		This holds the some basic methods.. Like an error handler.
 * METHODS
 *		error
 ****/
class Basic {
	
	/*****f* Basic/error
	* FUNCTION
	*		This is our error handler. All error should be passed to this error handler!
	* INPUT
	*		$__error_type (@string):
	*			Holds what kind of error it is. Can be Fatal error or just a Warning
	*		$__error_message (@string):
	*			Message that should be displayed.
	* ACCESS
	*		Public
	****/
	function error($__error_type, $__error_message)
	{
		// Array containing known error-types
		$valid_error_types = array('Fatal error', 'Warning');
			
		// If not - print a 'Fatal Error' (will terminate the script) 
		if (in_array($__error_type, $valid_error_types) == false) { 
			basic::error('Fatal error', 'Invalid error-type: "' . $__error_type . '" in error()');
		}

		// Write error-message
		Basic::printToConsole($__error_type . ": " . $__error_message);
		
		// If the error is fatal the script will be terminated
		if ($__error_type == 'Fatal error') { 
			exit;
		}
	
	}
	
	/*****f* Basic/printToConsole
	* FUNCTION
	*		This is a method to print to our console. Alle informative information, 
	*		should be posted to this method.
	* INPUT
	*		__text (@string):
	*			Holds the text that is going to be displayed.
	*		__line_wrap (@bol):
	*			Default set to true (if it should wrap the text in \n)
	* ACCESS
	*		Public
	****/
	function printToConsole($__text, $__line_wrap = true) {
		//Switch the colors, which are set in the as a global in the config.php
		switch(CONSOLE_COLOR) {
			case 'black':
				$ansi_color = "\033[01;30m";
				break;
			case 'red':
				$ansi_color = "\033[01;31m";
				break;
			case 'green':
				$ansi_color = "\033[01;32m";
				break;
			case 'blue':
				$ansi_color = "\033[01;34m";
				break;
		}
		
		//If it set to true, set some breaks
		if($__line_wrap) {
			$break = "\n";
		}
				
		echo $break . $ansi_color . $__text . $break . "\033[0m";
	}

} 


/*****c* class_command.php/Database
 * FUNCTION
 *		This class holds methods that handles database
 *		related stuff. I.e. connect to the database, close connection
 *		and queries.
 * METHODS
 *		connect, close, sqlWithoutAnswer
 ****/
class Database {
	
	/*****f* Database/connect
	* FUNCTION
	*		A method that connects to the database. This method
	*		depends on config.php and the database globals..
	* ACCESS
	*		Public
	****/
	function connect() 
  	{
		//Try to connect to a database
  		if(!mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD)) {
  			Basic::error('Fatal error', 'Can\'t connect to the MySQL database!');
  		}

		//Select a database
		if(!mysql_select_db(DB_DATABASE)) {
			Basic::error('Fatal error', 'Can\'t select the database:' . DB_DATABASE . 'Please be sure that the database is created!');
		}
		
  	}
	
	/*****f* Database/close
	* FUNCTION
	*		A methods that closes the connection to the database.
	* ACCESS
	*		Public
	****/
	function close() 
	{
		if(!mysql_close()) {
			basic::Error('Fatal error', 'Could not close connection to the database!');
		}
		
	} 
	
	/*****f* Database/sqlWithoutAnser
	* FUNCTION
	*		A methods that does a MySQL query.
	* INPUT
	*		$__query (@string):
	*			A SQL query.
	* ACCESS
	*		Public
	****/
	function sqlWithoutAnswer($__query) 
	{
		$do_query = mysql_query($__query);
		
		if(!$do_query) {
			Basic::error('Fatal error', "Could not do the following query: $query");
		}
		
	}
	
}

/*****c* class_command.php/CheckURL
 * FUNCTION
 *		This class can get information about an URL. This class has methods,
 *		that download the headers of a given URL. Those headers are then parsed for
 *		diffrent information that we need.
 * PROPERTIES
 *		data (@string):
 *			Header data that we get back from a server when we give it a URL
 *		last_modified (@date):
 *			A propertie that holds the date - when was the URL last modified
 *		current_version (@date):
 *			The current version of the OPD dump
 *		content_lenght (@int):
 *			The content lenght of a URL... aka File size
 * METHODS
 *		downloadHeaders, _urlParse, lastModified, contentLenght, lastModifiedCompare
 ****/
class CheckURL 
{ 
    var $data; 
    var $last_modified;
    var $current_version;
    var $content_lenght;
    
	/*****f* CheckURL/downloadHeaders
	* FUNCTION
	*		Method that gets the headers from an URL - which is the input of this method.
	* INPUT
	*		$__url (@string): 
	*			The URL which we want to get headers from
	* ACCESS
	*		Public
	****/
    function downloadHeaders($__url) 
    { 
        //If the open fails return nothing 
        if (!($fp = @fopen($__url, 'r'))) {
            return null;
		}
         
        //Check what version the user uses - ie. stream_get_meta_data > php 4.3.0 
        if(function_exists('version_compare') and version_compare(PHP_VERSION, '4.3.0') > 0) 
        {         
            $download_meta = stream_get_meta_data($fp);
			
            for ($i = 0; isset($download_meta['wrapper_data'][$i]); $i++) 
            { 
                $this->data .= $download_meta['wrapper_data'][$i]."\n"; 
            }
			
            fclose($fp); 
        }
		//Return nothing if php is older than version 4.3.0
        else { 
            return null; 
        } 
    } 
    
	/*****f* CheckURL/_urlParse
	* FUNCTION
	*		Method that parts an URL
	* INPUT
	*		$__url (@string): 
	*			The URL that you want to part
	*		$__request ($string):
	*			The request of the return value. IE: host or path
	* OUTPUT
	*		@string - host or path
	* ACCESS
	*		Private	
	****/
    function _urlParse($__url, $__request) 
    { 
        //Part the url using parse_url 
        $parts = parse_url($__url); 
         
        //Return the requested component 
        switch($__request) 
        { 
            case 'host': 
                return $parts['host']; 
            break; 
             
            case 'path': 
                return $parts['path']; 
            break; 
        }         
         
    } 
     
   	/*****f* CheckURL/lastModified
	* FUNCTION
	*		Method that extracts the last-modified date from our header data (data propertie)	
	* ACCESS
	*		Public
	****/ 
    function lastModified() 
    { 
        preg_match("/last-modified: (.*)/i",$this->data,$match); 
        $this->last_modified = $match[1]; 
    } 
    
    /*****f* CheckURL/contentLenght
	* FUNCTION
	*		Method that extracts the file size from our header data (data propertie)	
	* ACCESS
	*		Public
	****/ 
    function contentLenght() 
    { 
        preg_match("/content-length: (.*)/i",$this->data,$match); 
        $this->content_lenght = $match[1]; 
    } 
    
    /*****f* CheckURL/lastModifiedCompare
	* FUNCTION
	*		A method that checks if the ODP data last-modified date matches the date found
	*		inside lastupdate.data. If it's the same.. the script stops! If the ODP data
	*		dump is fresh - then we proceed with our download.
	*
	*		If we proceed - - then our current last-modified date is stored in a file:
	*		lastupdate.data	
	* ACCESS
	*		Public
	****/  
    function lastModifiedCompare() 
    { 
		$get_last_update = @file('lastupdate.data');
		
		//Check if the date&time in the lastupdate.data are the same... if they are ... then death comes and takes everything away. 	
 		if($get_last_update[0] == $this->last_modified) {
			Basic::error('Fatal error', "\n\nNo need to update. No new RDF dumps at DMOZ. DMOZ files were last updated " . $this->last_modified . ". \nPS: If you are having trouble with this update - delete/edit the file lastupdate.data");
 		}
		else {
 			if($get_last_update[0] == '') {
 				$this->current_version = 'you don\'t have one :)';
 			}
 			else {
				// Set a global
 				$this->current_version = $get_last_update[0];	
 			}
 			
 			return true;
 		}
    }

	/*****f* CheckURL/writeLastUpdate
	* FUNCTION
	*		This just write the last update date to a file			
	* ACCESS
	*		Public
	****/
	function writeLastUpdate() {
		//Write the data to a file
		$fp = fopen('lastupdate.data', 'w');
		fwrite($fp, $this->current_version);
		fclose($fp);
	}

}

/*****c* class_command.php/Dot
 * FUNCTION
 *		This class can be used to control when a dot (.) is going to be printed.
 *		I.e. it's not smart to print a dot out for every row we insert in our db.
 *		Then you will have like 2 mio dots :)
 * USAGE
 *		Start to set the frequency (i.e. print the dot every 50000 time). Then just call
 *		printDot.. And the method will find out it should print the dot or not.
 * PROPERTIES
 *		count (@int):
 *			Variable to control that the dot does not print everytime.
 *			I.e. it's just the counter that we used to check if we have reached
 *			the frequency.
 *		frequency (@int):
 *			On what frequency should the dot be displayed 
 * METHODS
 *		printDot, setFrequency	
 ****/
class Dot
{
	var $count;
	var $frequency;
	
	/*****f* Dot/printDot
	* FUNCTION
	*		A methods that prints out the dot if the counter has the same value
	*		as the frequency. Every time this function is called the counter gets ++.	
	* ACCESS
	*		Public
	****/
	function printDot()
	{
		//Don't write the dot every time
		$this->count++;

		//Used to check out if we just started, and to check if we have reached our frequency.
		//If it's reached, then the count will be set to 0.
		switch($this->count) 
		{
			case 1:
				Basic::printToConsole('.', false);
			break;
			
			case $this->frequency:
				$this->count = 0;
			break;
				
			default:
		}
	}
	
	/*****f* Dot/setFrequency
	* FUNCTION
	*		A methods that set our frequency.
	* INPUT
	*		$__freq (@int):
	*			A number .. i.e. 50000 - print the dot every 50000..	
	* ACCESS
	*		Public
	****/
	function setFrequency($__freq)
	{
		$this->frequency = $__freq;
	}
	
}
?>
