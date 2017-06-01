<?php

	require_once(TOOLKIT . '/class.datasource.php');
	require_once(EXTENSIONS . '/anti_spam_question/lib/class.anti_spam_question.php');

	class datasourceanti_spam_question extends Datasource {

		/**
		 * About
		 */
		public function about()
		{
			return array(
				'name' => anti_spam_question::EXT_NAME,
				'version' => '2.0.0',
				'release-date' => '2017-02-24',
				'author' => array(
					'name' => anti_spam_question::EXT_NAME,
					'website' => 'https://github.com/twiro/anti_spam_question'
				),
				'description'  => 'Chooses a random question to be used in conjunction with the Anti Spam Question Filter.'
			);
		}
		
		public function allowEditorToParse()
		{
			return false;
		}

		/**
		 * Execute
		 */
		public function execute(array &$param_pool = null)
		{

			$tbl = anti_spam_question::EXT_TBL_NAME;
			$sql = "SELECT * FROM `$tbl`";
			$entries = Symphony::Database()->fetch($sql);
			$random = array_rand($entries);
			$entry = $entries[$random];

			if (!$entry) {
				$result = new XMLElement(anti_spam_question::EXT_DS_ROOT);
				$result->appendChild(new XMLElement('error', __('No question found.')));
			} else {
				$result = new XMLElement(anti_spam_question::EXT_DS_ROOT);
				$result->appendChild( new  XMLElement('entry', $entry['question'], array('id' => $entry['id'])));
			}

			return $result;
		}
	}
