<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(EXTENSIONS . '/anti_spam_question/lib/class.anti_spam_question.php');

	class contentExtensionAnti_spam_questionQuestions extends AdministrationPage {

		private $_driver;
		private $_page;
		private $_id;
		private $_flag;

		function view()
		{
			$this->__switchboard();
		}

		function action()
		{
			$this->__switchboard('action');
		}

		function __switchboard($type='view')
		{
			$this->_page = $this->_context['0'];
			$this->_id = $this->_context['1'];
			$this->_flag = $this->_context['2'];

			$function = ($type == 'action' ? '__action' : '__view') . (isset($this->_page) ? ucfirst($this->_page) : 'Index');

			if(!method_exists($this, $function)) {

				// If there is no action function, just return without doing anything
				if($type == 'action') return;

				$this->_Parent->errorPageNotFound();

			}

			$this->$function();

		}

		function __viewIndex()
		{
			$this->setTitle(anti_spam_question::EXT_NAME . ' &ndash; ' . __('Symphony'));
			$this->setPageType('index');
			$this->appendSubheading(anti_spam_question::EXT_NAME, Widget::Anchor('Create New', SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/new/', __('Create a new entry'), 'create button'));

			$aTableHead = array(
				array(__('Question'), 'col'),
				array(__('Answer'), 'col')
			);

			$tbl = anti_spam_question::EXT_TBL_NAME;
			$sql = "SELECT * FROM `$tbl`";
			$entries = Symphony::Database()->fetch($sql);

			if(!is_array($entries) || empty($entries)) {

				$aTableBody = array(
					Widget::TableRow(array(Widget::TableData('None found.', 'inactive', NULL, count($aTableHead))))
				);

			} else {

				$tableData = array();

				foreach($entries as $entry) {

					$tableData[] = Widget::TableData(Widget::Anchor(General::limitWords($entry['question'], '100'), SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/edit/' . $entry['id'] . '/', $entry['id'], 'content'));
					$tableData[] = Widget::TableData(General::limitWords($entry['answer'], '100'));
					$tableData[count($tableData) - 1]->appendChild(Widget::Input('items['.$entry['id'].']', NULL, 'checkbox'));

					$aTableBody[] = Widget::TableRow($tableData, ($bEven ? 'even' : NULL));

					$bEven = !$bEven;

					unset($tableData);
				}
			}

			$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody));
			$table->setAttribute('class', 'selectable');
			$table->setAttribute('data-interactive', 'data-interactive');

			$this->Form->appendChild($table);

			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');

			$options = array(
				array(NULL, false, 'With Selected...'),
				array('delete', false, __('Delete'), 'confirm', null, array(
					'data-message' => __('Are you sure you want to delete the selected entries?')
				))
			);

			$tableActions->appendChild(Widget::Apply($options));
			$this->Form->appendChild($tableActions);
		}

		function __actionIndex()
		{
			$checked = @array_keys($_POST['items']);

			if(is_array($checked) && !empty($checked)) {
				if($_POST['with-selected'] == 'delete') {
					foreach($checked as $id) {
						Symphony::Database()->delete(anti_spam_question::EXT_TBL_NAME,  "`id` = '".$id."'");
					}
					redirect($_SERVER['REQUEST_URI']);
				}
			}
		}

		function __viewNew()
		{
			$this->setTitle(__('New Question') . ' &ndash; ' . anti_spam_question::EXT_NAME . ' &ndash; ' . __('Symphony'));
			$this->setPageType('form');
			$this->Form->setAttribute('class', 'two columns');

			// page context
			$this->appendSubheading(__('New Question'));
			$breadcrumbs = array(
				Widget::Anchor(anti_spam_question::EXT_NAME, SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/' )
			);
			$this->insertBreadcrumbs($breadcrumbs);

			$primary = new XMLElement('fieldset');
			$primary->setAttribute('class', 'primary column');
			$question = Widget::Label('Question');
			$question->appendChild(Widget::Input('fields[question]', $_POST['fields']['question'], 'text'));
			$primary->appendChild($question);

			$this->Form->appendChild($primary);

			$sidebar = new XMLElement('fieldset');
			$sidebar->setAttribute('class', 'secondary column');
			$answer = Widget::Label('Answer');
			$answer->appendChild(Widget::Input('fields[answer]', $_POST['fields']['answer'], 'text'));
			$sidebar->appendChild($answer);

			$this->Form->appendChild($sidebar);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', 'Create Entry', 'submit', array('accesskey' => 's')));

			$this->Form->appendChild($div);

		}

		function __actionNew() {

			if(!Symphony::Database()->insert($_POST['fields'], anti_spam_question::EXT_TBL_NAME)) {
				define_safe('__SYM_DB_INSERT_FAILED__', true);
				$this->pageAlert(NULL, AdministrationPage::PAGE_ALERT_ERROR);
			} else {
				redirect(SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/edit/' . Symphony::Database()->getInsertID() . '/created/');
			}
		}

		function __viewEdit()
		{
			$tbl = anti_spam_question::EXT_TBL_NAME;
			$sql = "SELECT * FROM `$tbl` WHERE `id` = '$this->_id' LIMIT 1";
			$entry = Symphony::Database()->fetchRow('0', $sql);

			$this->setTitle($entry['question'] . ' &ndash; ' . anti_spam_question::EXT_NAME . ' &ndash; ' . __('Symphony'));
			$this->setPageType('form');
			$this->Form->setAttribute('class', 'two columns');

			$this->appendSubheading($entry['question']);
			$breadcrumbs = array(
				Widget::Anchor(anti_spam_question::EXT_NAME, SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/' )
			);
			$this->insertBreadcrumbs($breadcrumbs);

			$primary = new XMLElement('fieldset');
			$primary->setAttribute('class', 'primary column');
			$question = Widget::Label('Question');
			$question->appendChild(Widget::Input('fields[question]', $entry['question'], 'text'));
			$primary->appendChild($question);

			$this->Form->appendChild($primary);

			$sidebar = new XMLElement('fieldset');
			$sidebar->setAttribute('class', 'secondary column');
			$answer = Widget::Label('Answer');
			$answer->appendChild(Widget::Input('fields[answer]', $entry['answer'], 'text'));
			$sidebar->appendChild($answer);

			$this->Form->appendChild($sidebar);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', 'Save Entry', 'submit', array('accesskey' => 's')));

			$this->Form->appendChild($div);

			if(isset($this->_flag)) {

				switch($this->_flag) {

					case 'saved':
						$this->pageAlert(__('Question updated successfully. <a href="%s">Create another?</a>', array(SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/new/')), Alert::SUCCESS);
						break;

					case 'created':
						$this->pageAlert(__('Question created successfully. <a href="%s">Create another?</a>', array(SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/new/')), Alert::SUCCESS);
						break;

				}
			}
		}

		function __actionEdit()
		{
			if(!Symphony::Database()->update($_POST['fields'], anti_spam_question::EXT_TBL_NAME,  "`id` = '".$this->_id."'")) {
				define_safe('__SYM_DB_INSERT_FAILED__', true);
				$this->pageAlert(NULL, Alert::ERROR);
			} else {
				redirect(SYMPHONY_URL . anti_spam_question::EXT_CONTENT_PATH . '/edit/' . $this->_id . '/saved/');
			}
		}
	}
