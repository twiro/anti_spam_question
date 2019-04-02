<?php

	class anti_spam_question {
	
		/**
		 * Name of the extension table
		 * @var string
		 */
		const EXT_NAME = 'Anti Spam Question';

		/**
		 * Name of the extension table
		 * @var string
		 */
		const EXT_TBL_NAME = 'tbl_anti_spam_question';
		
		/**
		 * Define datasource root node name
		 * @var string
		 */
		const EXT_DS_ROOT = 'anti-spam-question';
		
		/**
		 * The extension's content path
		 * @var string
		 */
		const EXT_CONTENT_PATH = '/extension/anti_spam_question/questions';
		
		/**
		 * Creates the table needed for the extensions entries
		 */
		public static function createTable()
		{
			$tbl = self::EXT_TBL_NAME;
			
			return Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `$tbl` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`question` varchar(255) NOT NULL,
					`answer` varchar(100) NOT NULL,
					PRIMARY KEY (`id`)
				)  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}

		/**
		 * Deletes the table with the extensions entries
		 */
		public static function deleteTable()
		{
			$tbl = self::EXT_TBL_NAME;

			return Symphony::Database()->query("
				DROP TABLE IF EXISTS `$tbl`
			");
		}
		
		/**
		 * event filter
		 */
		public function eventFilter($context)
		{
			if (in_array('anti-spam-question', $context['event']->eParamFILTERS)) {
				
				$tbl = self::EXT_TBL_NAME;
				$sql = "SELECT * FROM `$tbl` WHERE `id` = '" . $_POST['anti-spam-question']['id'] . "' LIMIT 1";
				$entry = Symphony::Database()->fetchRow('0', $sql);

				if(strtolower($entry['answer']) == strtolower($_POST['anti-spam-question']['answer'])) {
					$context['messages'][] = array('anti-spam-question', true, NULL);
				} else {
					$context['messages'][] = array('anti-spam-question', false, __('The answer to the anti spam question was incorrect.'));
				}	
			}
		}
	}
