<?php

	require_once(EXTENSIONS . '/anti_spam_question/lib/class.anti_spam_question.php');
	
	class extension_anti_spam_question extends Extension {

		/**
		 * Append custom navigation items to backend navigation
		 */
		public function fetchNavigation()
		{
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> anti_spam_question::EXT_NAME,
					'link'		=> '/questions/'
				)
			);
		}
	
		/**
		 * Subscribe to delegates
		 */
		public function getSubscribedDelegates()
		{
			return array(
				array(
					'page' => '/blueprints/events/',
					'delegate' => 'AppendEventFilterDocumentation',
					'callback' => 'appendEventFilterDocumentation'
				),
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPreSaveFilter',
					'callback'	=> 'eventPreSaveFilter'
				)
			);
		}

		/**
		 * install the extension
		 */
		public function install()
		{
			return anti_spam_question::createTable();
		}

		/**
		 * uninstall the extension
		 */
		public function uninstall()
		{
			return anti_spam_question::deleteTable();
		}

		/**
		 * Append filter to symphony's event pages
		 */
		public function appendEventFilter($context)
		{
			$context['options'][] = array(
				anti_spam_question::EXT_DS_ROOT,
				@in_array(
					anti_spam_question::EXT_DS_ROOT, $context['selected']
				),
				anti_spam_question::EXT_NAME
			);
		}

		/**
		 * Append filter documentation to symphony's event pages
		 */
		public function appendEventFilterDocumentation($context)
		{
			if (in_array('anti-spam-question', $context['selected'])) {

				$context['documentation'][] = new XMLElement('h3', __('Anti Spam Question Filter'));
				$context['documentation'][] = new XMLElement('p', __('To use the Anti Spam Question Filter, attach the Anti Spam Question Datasource on the pages that you use this event and add the following to your form:'));

				$code = Widget::Label('<xsl:value-of select="//anti-spam-question/entry" />');
				$code->appendChild(Widget::Input('anti-spam-question[answer]', NULL, 'text' ));
				$code->appendChild(Widget::Input('anti-spam-question[id]', '{//anti-spam-question/entry/@id}', 'hidden' ));

				$context['documentation'][] = contentAjaxEventDocumentation::processDocumentationCode($code);
			}
		}

		/**
		 * perform event filter
		 */
		public function eventPreSaveFilter($context)
		{
			anti_spam_question::eventFilter($context);
		}

	}
