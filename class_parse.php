<?php
/****h* Introduction/class_parse.php
 * NAME
 *		class_parse.php
 * FUNCTION
 *		This file contains all the main classes that is used to parse the XML 
 *		(rdf) files.
 *		Classes included are following:
 *			 PraseXMLGlobal:
 *				A parent class that has some classes, that can be used both by
 *				structure and content parsing.
 *			ParseXMLStructure:
 *				A class that is used to parse the structure RDF file.
 * AUTHOR
 *		Amir Salihefendic (amix@amix.dk)
 * COPYRIGHT
 *		JFL Webcom (http://www.webcom.dk)
 * CREATION DATE
 *		23 nov. 2003
 * HISTORY
 *		06 dec. 2003: Just added the change log! :)
 *		07 dec. 2003: Added content parser
 *		07 dec. 2003: Added a new class XMLGlobal!
 *		07 dec. 2003: Fixed some bugs :D
 *		12 feb. 2004: Fixed shit loads of "" code-not-so good errors
 *					  Fixed a HUGE bug (which took long time to find).
 *					  the bug made catid's to 0 - but it's fixed now!
 *		13 feb. 2004: To work properly the class needs to load whole files
 *					  into the memory. To help your computer I have now created
 *					  class that splits the big file into some smaller parts.
 *		18 feb. 2004: Fixed a bug in the split rutine (it didn't split the
 *					  content file :()
 *		13 maj 2004: Rewritten alle comments, so they support ROBODOC - kinky stuff :-D
 *		24 maj 2004: Updated the XML parser.
 ****/
 
/*****c* class_parse.php/XMLGlobal
 * FUNCTION
 *		This class holds global methods that can be used by structre and contents
 *		parsers.
 * USAGE
 *		This class is used as a parent for XMLParseStructure and XMLParseContent		 
 * PROPERTIES
 *		Status:
 *		h (@int):
 *			Hours
 *		m (@int):
 *			Minutes
 *		s (@int)
 *			Sec.
 *		
 *		Everythings else:
 *		xml_file (@string):
 *			The filename of XML file we are parsing
 *		start_time (@int):
 *			The start time
 * METHODS
 *		setXMLFile, setStartTime, _getMicroTime, _echoStatus, _splitTime, _startToParse
 * USED BY
 *		XMLParseStructure, XMLParseContent
 ****/
class XMLGlobal
{
	var $h;
	var $m;
	var $s;
	
	var $xml_file;
	var $start_time;
	
	/*****f* XMLGlobal/setXMLFile
	* FUNCTION
	*		Just a methods that sets the filename
	* INPUT
	*		__filename (@string):
	*			The filename of our XML file
	* ACCESS
	*		Public
	****/
	function setXMLFile($__filename) 
	{
		$this->xml_file = $__filename;
	}
	
	/*****f* XMLGlobal/setStartTime
	* FUNCTION
	*		A method that you need to call to set the start time		
	* ACCESS
	*		Public
	****/
	function setStartTime() {
		$this->start_time =  XMLGlobal::_getMicroTime();
	}
	
	/*****f* XMLGlobal/_getMicroTime
	* FUNCTION
	*		A method that gets the microtime
	* USED BY
	*		setStartTime, echoStatus	
	* ACCESS
	*		Private
	****/
	function _getMicroTime(){ 
		list($usec, $sec) = explode(' ', microtime()); 
		return ((float)$usec + (float)$sec); 
	}
	
	/*****f* XMLGlobal/_echoStatus
	* FUNCTION
	*		A methods that prints out the status
	* INPUT
	*		__start_time (@int):
	*			When was the script started
	*		__count_rows (@int):
	*			How many rows have we inserted sofar
	*		__milestone (@string):
	*			A text that tells a litte about our milestone
	* USED BY
	*		_endTagProcessor
	* ACCESS
	*		Private
	****/
	function _echoStatus($__start_time, $__count_rows, $__milestone){
		$end_time = $this->_getMicroTime();
		$time = $__start_time - $end_time;
		$dot = strrpos($time, '.');
		$script_time = abs(substr($time, 0, $dot + 2));
		
		$this->_splitTime($script_time);
		
		Basic::printToConsole("\n", false);
		Basic::printToConsole($__milestone . "\n", false);
		Basic::printToConsole('Time and date: [' . date('m/d/y - H:i:s') . "]\n", false);
		Basic::printToConsole('Run-time for the script: ' . $this->h . ':' . $this->m . ':' . $this->s, false);
		Basic::printToConsole('Rows inserted: ' . $__count_rows . "\n");
		flush();
	}
	
	/*****f* XMLGlobal/_splitTime
	* FUNCTION
	*		A method that splits current run time for the script
	* USED BY
	*		_echoStatus	
	* ACCESS
	*		Private
	****/
	function _splitTime($__script_time)
	{
		$this->h = '0'; 
		$this->m = '0'; 
		$this->s = intval($__script_time % 60);
		
	 	if($__script_time > 60) {
			$this->m = intval($__script_time / 60) % 60;
		}
		
	 	if($__script_time > 3600) { 
			$this->h = intval($__script_time / 3600);
		}
	}
	
	/*****f* XMLGlobal/_startToParse
	* FUNCTION
	*		A method that creates the PHP's parsers and starts parsing.
	* USED BY
	*		startParse (XMLContentParser and XMLStructureParser)
	* ACCESS
	*		Private
	****/
	function _startToParse()
	{
		//Load php's XML parser
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, &$this);
		
		//Parser option: Skip whitespace
		xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, true); 
		
		//Parser option: Case folding off
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false); 
	
		//Set callback functions
		xml_set_element_handler($this->xml_parser, "_startTagProcessor", "_endTagProcessor"); 
		xml_set_character_data_handler($this->xml_parser, "_charDataProcessor");
		
		//Read XML file
		if (!($fp = fopen($this->xml_file, 'r'))) 
		{
		      basic::error('Fatal error', "File I/O error: $this->xml_file"); 
		} 
		
		//Parse XML 
		while ($data = fread($fp, 4096)) 
		{
		      //error... :( 
		      if (!xml_parse($this->xml_parser, $data, feof($fp))) 
		      {
		            $ec = xml_get_error_code($this->xml_parser); 
		            basic::error('Fatal error', "XML parser error (error code " . $ec . "): " . xml_error_string($ec) . 
		"\nThe error was found on line: " . xml_get_current_line_number($this->xml_parser)); 
		      } 
		} 

		//free your mind, and the rest will follow :)
		xml_parser_free($this->xml_parser);
	}
	
}

/*****c* class_parse.php/ParseXMLStructure
 * FUNCTION
 *		A class that is used to parse the XML structure file.
 * USAGE
 *		Call setStartTime - sets the start timem
 *		Call setXMLFile(filename) - set the filename of our XML file
 *		Call startParse - starts parsing the document and inserting the data in our
 *		MySQL database.		 
 * PROPERTIES
 *		The basic properties to get this class going:
 *		count_rows (@int):
 *			How many rows we have done so far
 *		count_rows_temp (@int):
 *			A temporary counter (--Reset after ECHO_STATS_FREQUNCY rows)
 *
 *		XML tags and tehir contents
 *		current_tag (@string):
 *			Hold what tag we currently are in
 *		permitted_tags (@array)
 *			An array that holds the permitted tags
 *		
 *		Properties for the XML structure:
 *		topic (@string)
 *		catid (@int)
 *		title (@string)
 *		description (@string)
 *		last_update (@date)
 *		
 *		Variables for the XML data type
 *		type
 *		resource
 * METHODS
 *		startParse, _startTagProcessor, _endTagProcessor, _charDataProcessor
 ****/
class ParseXMLStructure extends XMLGlobal
{
	var $count_rows; 
	var $count_rows_temp; 	
	
	var $current_tag;
	var $permitted_tags;
	
	var $topic;
	var $catid;
	var $title;
	var $description;
	var $last_update;
		
	var $type;
	var $resource;
	
	/*****f* ParseXMLStructure/startParse
	* USAGE
	*		Used to start parsing of our file.
	* ACCESS
	*		Public
	****/	
	function startParse() 
	{
		//Starts with clean properties
		$this->current_tag = '';
		$this->topic = '';
		$this->catid = '';
		$this->title = '';
		$this->last_update = '';
		$this->description = '';
		$this->type = '';
		$this->resource = '';
		
		//
		//Here we specify what tags are legal
		//
		$this->permitted_tags = array('narrow', 'narrow1', 'narrow2', 'symbolic', 'symbolic1', 'symbolic2', 'related', 'newsgroup');
		
		$this->_startToParse(); //Start to parse function [located in XMLGlobal]

		//Print out that it is finished!
		Basic::printToConsole("Finished processing structure RDF file!\nIt took " . $this->h . ' hours, ' . $this->m . ' minutes and ' . $this->s . " seconds\nInserted rows into the database: " . $this->count_rows . "\n");
	}
	
	/*****f* ParseXMLStructure/_startTagProcessor
	* USAGE
	*		Function that processes the start tags.
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__tag_name (@string)
	*			The name of the current tagname
	*		__attributes (@array)
	*			Attributes of the tagname
	* ACCESS
	*		Private
	****/
	function _startTagProcessor($__parser, $__tag_name, $__attributes)
	{
		//Sets what tag we currenly are in
		$this->current_tag = $__tag_name;
		
		//Check if the current tag is topic
		if(strtolower($this->current_tag) == 'topic') 
		{
			//If it's true get id
			$this->topic = $__attributes['r:id'];
		}
		
		//Check if the tag is equal to some of our permitted tags
		if(in_array(strtolower($this->current_tag), $this->permitted_tags)) 
		{
			//Set type to be equal with the name
			$this->type = $__tag_name;
			
			//Set the resource to be equal the resource found in the tag
			$this->resource = $__attributes['r:resource'];
		}
	}
	
	/*****f* ParseXMLStructure/_endTagProcessor
	* USAGE
	*		This is our end tag processor. When a tag ends, it's gets In hEEreeE
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__tag_name (@string)
	*			The name of the current tagname
	* ACCESS
	*		Private
	****/
	function _endTagProcessor($parser, $__tag_name)
	{
		//Check if the end tag is topic, if it is it run a SQL query
		if(strtolower($__tag_name) == 'topic') 
		{
			$query = 'INSERT INTO structure';
			$query .= '(catid,name,title,description,lastupdate)';
			$query .= ' VALUES("' . $this->catid . '", "' . addslashes($this->topic) . '", "' . addslashes(trim($this->title)) . '", "' . addslashes(trim($this->description)).'", "' . $this->last_update . '")';
			Database::sqlWithoutAnswer($query);
			$this->count_rows++; //Count rows
			$this->count_rows_temp++; //Temporary count rows - used to make a milestone
			$query = '';

			//Reset the tags
			$this->catid = '';
			$this->topic = '';
			$this->title = '';
			$this->description = '';
			$this->last_update = '';
			$this->current_tag = '';
		}
		
		//Check if the end tag is something else
		if(in_array(strtolower($this->current_tag), $this->permitted_tags))
		{
			$query = 'INSERT INTO datatypes';
			$query .= '(catid,type,resource)';
			$query .= ' VALUES("' . $this->catid . '", "' . addslashes($this->type) . '", "' . addslashes(trim($this->resource)) . '")';
			Database::sqlWithoutAnswer($query);
			$this->count_rows++; //Count rows
			$this->count_rows_temp++; //Temporary count rows - used to make a milestone
			$query = '';
			$this->type = '';
			$this->resource = '';		
			$this->current_tag = '';
		}
		
		//Check if the stats are set
		if(ECHO_STATS) {			
			//Check if ECHO_STATS_FREQUNCY is reached
			if($this->count_rows_temp >= ECHO_STATS_FREQUNCY)
			{
				$this->count_rows_temp = 0;
				$this->_echoStatus($this->start_time, $this->count_rows, 'Yet another '.ECHO_STATS_FREQUNCY.' rows reached! - structure RDF document');
			}
		}
			
	}
	
	/*****f* ParseXMLStructure/_charDataProcessor
	* USAGE
	*		This is our content processor
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__data (@string)
	*			The data dude.. the data :)
	* ACCESS
	*		Private
	****/
	function _charDataProcessor($__parser, $__data) 
	{
		//Checks if there is something between the tags
		if(trim($__data) != '')
		{
			//Finds out what kind of data it is
			switch($this->current_tag) {
				case 'catid':
					$this->catid .= $__data;
					break;
				case 'd:Title':
					$this->title .= $__data;
					break;
				case 'd:Description':
					$this->description .= $__data;
					break;
				case 'lastUpdate':
					$this->last_update .= $__data;
					break;
				default:
				break;
			}
		}
	}
		
}

/*****c* class_parse.php/ParseXMLContent
 * FUNCTION
 *		A class that is used to parse the XML content file.
 * USAGE
 *		Call setStartTime - sets the start timem
 *		Call setXMLFile(filename) - set the filename of our XML file
 *		Call startParse - starts parsing the document and inserting the data in our
 *		MySQL database.		 
 * PROPERTIES
 *		The basic properties to get this class going:
 *		count_rows (@int):
 *			How many rows we have done so far
 *		count_rows_temp (@int):
 *			A temporary counter (--Reset after ECHO_STATS_FREQUNCY rows)
 *
 *		XML tags and tehir contents
 *		current_tag (@string):
 *			Hold what tag we currently are in
 *		permitted_tags (@array)
 *			An array that holds the permitted tags
 *		
 *		Properties for the XML structure:
 *		-------
 *		CONTENT LINKS (content_links):
 *		topic (@string)
 *		type (@string)
 *		resource (@string)
 *		catid (@int)
 *		
 *		CONTENT DESCRIPTION (content_description):
 *		external_page (@string)
 *		title (@string)
 *		description (@string)
 *		ages (@string)
 *		mediadate (@date)
 *		priority (@int)
 * METHODS
 *		startParse, _startTagProcessor, _endTagProcessor, _charDataProcessor
 ****/
class ParseXMLContent extends XMLGlobal 
{
	var $count_rows;
	var $count_rows_temp;
	
	var $current_tag;
	var $permitted_tags;
	
	//(content_links)
	var $topic;
	var $type;
	var $ressorce;	
	var $catid;
	
	//(content_description)
	var $external_page;
	var $title;
	var $description;
	var $ages;
	var $mediadate;
	var $priority;
	
	/*****f* ParseXMLContent/startParse
	* USAGE
	*		Used to start parsing of our file.
	* ACCESS
	*		Public
	****/
	function startParse() 
	{
		//Starts with clean properties
		//(content_links)
		$this->topic = '';
		$this->type = '';
		$this->resource = '';
		
		//(content_description)
		$this->external_page = '';
		$this->title = '';
		$this->description = '';
		$this->ages = '';
		$this->mediadate = '';
		$this->priority = '';
		
		$this->current_tag = '';
		
		//
		//Here we specify what tags are legal
		//
		$this->permitted_tags = array('link', 'link1');
		
		$this->_startToParse(); //Start to parse function [located in XMLGlobal]
		
		//Print out that it is finished!
		Basic::printToConsole("Finished processing content RDF file!\nIt took " . $this->h . ' hours, ' . $this->m . ' minutes and ' . $this->s . " seconds\nInserted rows into the database: " . $this->count_rows . "\n");
	}
	
	/*****f* ParseXMLContent/_startTagProcessor
	* USAGE
	*		Function that processes the start tags.
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__tag_name (@string)
	*			The name of the current tagname
	*		__attributes (@array)
	*			Attributes of the tagname
	* ACCESS
	*		Private
	****/
	function _startTagProcessor($__parser, $__tag_name, $__attributes)
	{
		
		//Sets what tag we currenly are in
		$this->current_tag = $__tag_name;
		
		//Check if the current tag is topic
		if(strtolower($this->current_tag) == 'topic') 
		{
			//Reset catid
			$this->catid = '';

			//If it's true get id
			$this->topic = $__attributes['r:id'];
		}
		
		//Check if the current tag is external page
		if(strtolower($this->current_tag) == 'externalpage') 
		{
			//If it's true get id
			$this->external_page = $__attributes['about'];
		}
		
		//Check if the tag is equal to some of our permitted tags
		if(in_array(strtolower($this->current_tag), $this->permitted_tags)) 
		{
			//Set type to be equal with the name
			$this->type = $__tag_name;

			//Set the resource to be equal the resource found in the tag
			$this->resource = $__attributes['r:resource'];
		}
	}
	
	/*****f* ParseXMLContent/_endTagProcessor
	* USAGE
	*		This is our end tag processor. When a tag ends, it's gets In hEEreeE
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__tag_name (@string)
	*			The name of the current tagname
	* ACCESS
	*		Private
	****/
	function _endTagProcessor($__parser, $__tag_name)
	{
		//Check if the end tag is external_page
		if(strtolower($__tag_name) == 'externalpage')
		{
			$query = 'INSERT INTO content_description';
			$query .= '(externalpage, title, description, ages, mediadate, priority)';
			$query .= ' VALUES("' . addslashes($this->external_page) . '", "' . addslashes(trim($this->title)) . '", "' . addslashes(trim($this->description)) . '", "' . addslashes(trim($this->ages)) . '", "' . addslashes($this->mediadate) . '", "' . addslashes($this->priority) . '")';
			Database::sqlWithoutAnswer($query);
			$this->count_rows++; //Count rows
			$this->count_rows_temp++; //Temporary count rows - used to make a milestone
			$query = '';
			$this->external_page = '';
			$this->title = '';
			$this->description = '';
			$this->ages = '';
			$this->mediadate = '';
			$this->priority = '';	
			$this->current_tag = '';
		}
		
		//Check if the end tag is in the range of permitted tags
		if(in_array(strtolower($__tag_name), $this->permitted_tags))
		{
			$query = 'INSERT INTO content_links';
			$query .= '(catid, topic, type, resource)';
			$query .= ' VALUES("' . addslashes($this->catid) . '", "' . addslashes($this->topic) . '", "' . addslashes($this->type) . '", "' . addslashes($this->resource) . '")';
			Database::sqlWithoutAnswer($query);
			$this->count_rows++; //Count rows
			$this->count_rows_temp++; //Temporary count rows - used to make a milestone
			$query = '';
			$this->type = '';
			$this->resource = '';		
			$this->current_tag = '';
		}
		
		//Check if the stats are set
		if(ECHO_STATS) {			
			//Check if ECHO_STATS_FREQUNCY is reached
			if($this->count_rows_temp == ECHO_STATS_FREQUNCY)
			{
				$this->count_rows_temp = 0;
				$this->_echoStatus($this->start_time, $this->count_rows, 'Yet another '.ECHO_STATS_FREQUNCY.' rows reached! - content RDF document');
			}
		}
	}
	
	/*****f* ParseXMLContent/_charDataProcessor
	* USAGE
	*		This is our content processor
	* INPUT
	*		__parser (@obj)
	*			What parser is it dude? heh
	*		__data (@string)
	*			The data dude.. the data :)
	* ACCESS
	*		Private
	****/
	function _charDataProcessor($__parser, $__data) 
	{
		//Checks if there is something between the tags
		if(trim($__data) != '')
		{
		//Finds out what kind of data it is
		switch($this->current_tag) {
			case 'catid':
				$this->catid .= $__data;
				break;
			case 'd:Title':
				$this->title .= $__data;
				break;
			case 'd:Description':
				$this->description .= $__data;
				break;
			case 'ages':
				$this->ages .= $__data;
				break;
			case 'mediadate':
				$this->mediadate .= $__data;
				break;
			case 'priority':
				$this->priority .= $__data;
				break;
			default:
			break;
		}
		}
	}
	
		
}

?>
