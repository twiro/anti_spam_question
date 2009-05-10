<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	Class eventAnswer_Me extends Event
	{		
		public static function about()
		{								
			return array(
						 'name' => 'Answer Me',
						 'author' => array('name' => 'Mark Lewis',
										   'website' => 'http://www.casadelewis.com',
										   'email' => 'mark@casadelewis.com'),
						 'version' => '1.0',
						 'release-date' => '2008-07-28',
						 'trigger-condition' => 'onload'
						 );						 
		}
				
		public function load()
		{	
			return $this->__trigger();
		}

		public static function documentation()
		{
			return new XMLElement('p', 'Chooses a random question to be used in conjunction with the Answer Me filter.');
		}
		
		protected function __trigger()
		{
			$entries = $this->_Parent->Database->fetch('SELECT * FROM `tbl_answerme`');
			
			$random = array_rand($entries);
			$entry = $entries[$random];
			
			$result = new XMLElement('answer-me');
			$result->appendChild( new  XMLElement('question', $entry['question'], array('id' => $entry['id'])));
			
			return $result;
		}
	}

?>