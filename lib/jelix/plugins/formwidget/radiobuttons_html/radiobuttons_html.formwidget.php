<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
* @package     jelix
* @subpackage  formwidgets
* @author      Claudio Bernardes
* @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
* @copyright   2012 Claudio Bernardes
* @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
require_once(__DIR__.'/../checkboxes_html/checkboxes_html.formwidget.php');
class radiobuttons_htmlFormWidget extends checkboxes_htmlFormWidget{
	function outputControl(){
		$attr=$this->getControlAttributes();
		$value=$this->getValue($this->ctrl);
		$id=$this->builder->getName().'_'.$this->ctrl->ref.'_';
		$attr['name']=$this->ctrl->ref;
		unset($attr['title']);
		if(is_array($value)){
			if(isset($value[0]))
				$value=$value[0];
			else
				$value='';
		}
		$value=(string) $value;
		$span='<span class="jforms-radio jforms-ctl-'.$this->ctrl->ref.'"><input type="radio"';
		$this->showRadioCheck($attr,$value,$span);
		$this->outputJs($this->ctrl->ref);
	}
}
