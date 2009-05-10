<?php 

require_once(TOOLKIT . '/class.administrationpage.php');

define_safe('BASE_URL', URL . '/symphony/extension/answerme/questions');

Class contentExtensionAnswerMeQuestions extends AdministrationPage{

    private $_driver;
	private $_page;
	private $_id;
	private $_flag;

    function __construct(&$parent){
        parent::__construct($parent);
		
        $this->_driver = $this->_Parent->ExtensionManager->create('answerme');

    }
	
	function view(){			
		$this->__switchboard();	
	}
	
	function action(){			
		$this->__switchboard('action');		
	}

	function __switchboard($type='view'){

		$this->_page = $this->_context['0'];
		$this->_id = $this->_context['1'];
		$this->_flag = $this->_context['2'];
	
		$function = ($type == 'action' ? '__action' : '__view') . (isset($this->_page) ? ucfirst($this->_page) : 'Index') ;
		
		if(!method_exists($this, $function)) {
			
			## If there is no action function, just return without doing anything
			if($type == 'action') return;
			
			$this->_Parent->errorPageNotFound();
			
		}
		
		$this->$function();

	}
	
	function __viewIndex(){			
        $this->setTitle('Symphony &ndash; Answer Me &ndash; Overview');
        $this->setPageType('table');

		$this->appendSubheading('Overview', Widget::Anchor('Create New', URL . '/symphony/extension/answerme/questions/new/', 'Create a new entry', 'create button'));

        ## Add stuff here
		
		$aTableHead = array(
			array('Question', 'col'),
			array('Answer', 'col')
		);			
		
		$entries = $this->_Parent->Database->fetch('SELECT * FROM `tbl_answerme`');

		if(!is_array($entries) || empty($entries))
		{
			$aTableBody = array(
				Widget::TableRow(array(Widget::TableData('None found.', 'inactive', NULL, count($aTableHead))))		
			);
		}
		else
		{
			$tableData = array();
			
			foreach($entries as $entry)
			{
				$tableData[] = Widget::TableData(Widget::Anchor(General::limitWords($entry['question'], '100'), $this->_Parent->getCurrentPageURL() . 'edit/' . $entry['id'] . '/', $entry['id'], 'content'));
				$tableData[] = Widget::TableData(General::limitWords($entry['answer'], '100'));
				$tableData[count($tableData) - 1]->appendChild(Widget::Input('items['.$entry['id'].']', NULL, 'checkbox'));
			
				$aTableBody[] = Widget::TableRow($tableData, ($bEven ? 'even' : NULL));

				$bEven = !$bEven;
					
				unset($tableData);		
			}
		}

		$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody));

		$this->Form->appendChild($table);

		$tableActions = new XMLElement('div');
		$tableActions->setAttribute('class', 'actions');

		$options = array(
			array(NULL, false, 'With Selected...'),
			array('delete', false, 'Delete')									
		);

		$tableActions->appendChild(Widget::Select('with-selected', $options));
		$tableActions->appendChild(Widget::Input('action[apply]', 'Apply', 'submit'));
		
        $this->Form->appendChild($tableActions); 
	}
	
	function __actionIndex(){			
		$checked = @array_keys($_POST['items']);

		if(is_array($checked) && !empty($checked))
		{
			if($_POST['with-selected'] == 'delete')
			{
				foreach($checked as $id)
				{
					$this->_Parent->Database->delete('tbl_answerme',  "`id` = '".$id."'");
				}

				redirect($_SERVER['REQUEST_URI']);
			}
		}
	}

	function __viewNew(){			
        $this->setTitle('Symphony &ndash; Answer Me &ndash; Add Question');
        $this->setPageType('form');

		$this->appendSubheading('Add Question');

		$primary = new XMLElement('fieldset');
		$primary->setAttribute('class', 'primary');
		$question = Widget::Label('Question');
		$question->appendChild(Widget::Input('fields[question]', $_POST['fields']['question'], 'text'));
		$primary->appendChild($question);

		$this->Form->appendChild($primary);
		
		$sidebar = new XMLElement('fieldset');
		$sidebar->setAttribute('class', 'secondary');
		$answer = Widget::Label('Answer');
		$answer->appendChild(Widget::Input('fields[answer]', $_POST['fields']['answer'], 'text'));
		$sidebar->appendChild($answer);
		
		$this->Form->appendChild($sidebar);

		$div = new XMLElement('div');
		$div->setAttribute('class', 'actions');
		$div->appendChild(Widget::Input('action[save]', 'Create Entry', 'submit', array('accesskey' => 's')));

		$this->Form->appendChild($div);
	}
	
	function __actionNew(){			
		if(!$this->_Parent->Database->insert($_POST['fields'], 'tbl_answerme'))
		{
			$this->pageAlert(__('Some errors were encountered while attempting to save.'), Alert::ERROR);
		}
		else
		{
  		    redirect(BASE_URL . '/edit/' . $this->_Parent->Database->getInsertID() . '/created/');
		}

	}

	function __viewEdit(){			
	
		$sql = "SELECT * FROM `tbl_answerme` WHERE `id` = '$this->_id' LIMIT 1";
		$entry = $this->_Parent->Database->fetchRow('0', $sql);

        $this->setTitle('Symphony &ndash; Answer Me &ndash; Edit Question');
        $this->setPageType('form');

		$this->appendSubheading('Edit Question');

		$primary = new XMLElement('fieldset');
		$primary->setAttribute('class', 'primary');
		$question = Widget::Label('Question');
		$question->appendChild(Widget::Input('fields[question]', $entry['question'], 'text'));
		$primary->appendChild($question);

		$this->Form->appendChild($primary);
		
		$sidebar = new XMLElement('fieldset');
		$sidebar->setAttribute('class', 'secondary');
		$answer = Widget::Label('Answer');
		$answer->appendChild(Widget::Input('fields[answer]', $entry['answer'], 'text'));
		$sidebar->appendChild($answer);
		
		$this->Form->appendChild($sidebar);

		$div = new XMLElement('div');
		$div->setAttribute('class', 'actions');
		$div->appendChild(Widget::Input('action[save]', 'Create Entry', 'submit', array('accesskey' => 's')));

		$this->Form->appendChild($div);

		if(isset($this->_flag))
		{
			switch($this->_flag){
				
				case 'saved':
					$this->pageAlert('Question updated successfully. <a href="'.BASE_URL.'/new/">Create another?</a>', Alert::ERROR);
					break;
					
				case 'created':
					$this->pageAlert('Question created successfully. <a href="'.BASE_URL.'/new/">Create another?</a>', Alert::ERROR);
					break;
				
			}
		}
	}
	
	function __actionEdit(){			
		if(!$this->_Parent->Database->update($_POST['fields'], 'tbl_answerme',  "`id` = '".$this->_id."'"))
		{
			$this->pageAlert(__('Some errors were encountered while attempting to save.'), Alert::ERROR);
		}
		else
		{
  		    redirect(BASE_URL . '/edit/' . $this->_id . '/saved/');
		}

	}

}
?>