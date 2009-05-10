<?php
	
	class extension_answerme extends Extension {
		public function about() {
			return array(
				'name'			=> 'Answer Me',
				'version'		=> '1.01',
				'release-date'	=> '2009-01-08',
				'author'		=> array(
					'name'			=> 'Mark Lewis',
					'website'		=> 'http://casadelewis.com/',
					'email'			=> 'mark@casadelewis.com'
				),
				'description'	=> 'Protect your front-end forms by asking random questions.'
	 		);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_answerme`");
		}
		
		public function install() {
			$this->_Parent->Database->query("CREATE TABLE `tbl_answerme` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`question` varchar(255) collate utf8_unicode_ci NOT NULL,
				`answer` varchar(100) collate utf8_unicode_ci NOT NULL,
				PRIMARY KEY  (`id`)
				) TYPE=MyISAM");
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendFilter'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendFilter'
				),
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilterDocumentation',
					'callback'	=> 'appendDocumentation'
				),				
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilterDocumentation',
					'callback'	=> 'appendDocumentation'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPreSaveFilter',
					'callback'	=> 'processData'
				)
			);
		}
				
		public function appendFilter($context) {
			$context['options'][] = array(
				'answerme',
				@in_array(
					'answerme', $context['selected']
				),
				'Answer Me'
			);
		}

		public function appendDocumentation($context) {
			if (!in_array('answerme', $context['selected'])) return;
			
			$context['documentation'][] = new XMLElement('h3', 'Answer Me Filter');
			$context['documentation'][] = new XMLElement('p', 'To use the Answer Me filter, attach the Answer Me event on the pages that you use this event and add the following to your form:');

			$label = Widget::Label('<xsl:value-of select="events/answer-me/question" />');
			$label->appendChild(Widget::Input('answerme[answer]', NULL, 'text' ));
			$label->appendChild(Widget::Input('answerme[id]', '{events/answer-me/question/@id}', 'hidden' ));

			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($label);
		}

		public function fetchNavigation(){ 
			return array(
				array(
					'location' => 400,
					'name' => 'Answer Me',
					'children' => array(
						
						array(
							'name' => 'Overview',
							'link' => '/questions/'							
						)
					)
				)
			);
		}
		
		public function processData($context) {
			if (!in_array('answerme', $context['event']->eParamFILTERS)) return;
	
			$sql = "SELECT * FROM `tbl_answerme` WHERE `id` = '" . $_POST['answerme']['id'] . "' LIMIT 1";
			$entry = $this->_Parent->Database->fetchRow('0', $sql);
			
			if(strtolower($entry['answer']) == strtolower($_POST['answerme']['answer']))
			{
				$context['messages'][] = array('answerme', true, NULL);
			}
			else
			{
				$context['messages'][] = array('answerme', false, 'The answer to the question was incorrect.');
			}
		}
	}
	
?>