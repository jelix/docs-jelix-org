<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/

/**
 * @author    Laurent Jouanneau
 * @copyright 2019-2020 Laurent Jouanneau
 *
 * @see      https://jelix.org
 *
 * @license MIT
 */
class autocompleteajax_htmlFormWidget  extends \jelix\forms\HtmlWidget\WidgetBase{
	protected function outputJs($source){
		$ctrl=$this->ctrl;
		$jFormsJsVarName=$this->builder->getjFormsJsVarName();
		$this->parentWidget->addJs("c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n");
		if($ctrl instanceof jFormsControlDatasource
			&&$ctrl->datasource instanceof jIFormsDynamicDatasource){
			$dependentControls=$ctrl->datasource->getCriteriaControls();
			if($dependentControls){
				$this->parentWidget->addJs("c.dependencies = ['".implode("','",$dependentControls)."'];\n");
				$this->parentWidget->addFinalJs("jFormsJQ.tForm.declareDynamicFill('".$ctrl->ref."');\n");
			}
		}
		$this->commonJs();
		$searchInId=(strpos($this->getCSSClass(),'autocomplete-search-in-id')!==false);
		$this->parentWidget->addFinalJs('$(\'#'.$this->getId().
			'_autocomplete\').jAutocompleteAjax({source:"'.$source.
			'", searchInId: '.($searchInId?'true':'false').'});');
		$resp=jApp::coord()->response;
		if($resp instanceof jResponseHtml){
			$config=jApp::config();
			$www=$config->urlengine['jelixWWWPath'];
			$resp->addJSLink($www.'js/jforms/jAutocompleteAjax.jqueryui.js');
		}
	}
	function outputControl(){
		$attr=$this->getControlAttributes();
		$value=$this->getValue();
		if(isset($attr['readonly'])){
			$attr['disabled']='disabled';
			unset($attr['readonly']);
		}
		$attr['class'].=' autocomplete-value';
		$attr['value']=$value;
		$attr['title']=$this->ctrl->getDisplayValue($value);
		$attrAutoComplete=array(
			'placeholder'=>jLocale::get('jelix~jforms.autocomplete.placeholder'),
		);
		if(isset($attr['attr-autocomplete'])){
			$attrAutoComplete=array_merge($attrAutoComplete,$attr['attr-autocomplete']);
			unset($attr['attr-autocomplete']);
		}
		if(isset($attrAutoComplete['class'])){
			$attrAutoComplete['class'].=' autocomplete-input';
		}
		else{
			$attrAutoComplete['class']=' autocomplete-input';
		}
		if(isset($attrAutoComplete['style'])){
			$attrAutoComplete['style'].='display:none';
		}
		else{
			$attrAutoComplete['style']='display:none';
		}
		$attrAutoComplete['id']=$this->getId().'_autocomplete';
		$source=isset($attrAutoComplete['source'])?$attrAutoComplete['source']:'';
		echo '<div class="autocomplete-box"><input type="text" ';
		$this->_outputAttr($attrAutoComplete);
		echo '> <span class="autocomplete-no-search-results" style="display:none">'.jLocale::get('jelix~jforms.autocomplete.no.results').'</span> 
                <button class="autocomplete-trash btn btn-mini" title="Effacer" type="button"><i class="icon-trash"></i></button>
                <input type="hidden" ';
		$this->_outputAttr($attr);
		echo '/>';
		echo "</div>\n";
		$this->outputJs($source);
	}
}
