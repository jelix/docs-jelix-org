<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 * @contributor Gwendal Jouannic, Thomas, Julien Issler
 *
 * @copyright  2005-2021 Laurent Jouanneau
 * @copyright  2008 Gwendal Jouannic, 2009 Thomas
 * @copyright  2009 Julien Issler
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jDbPDOResultSet extends PDOStatement{
	protected $_fetchMode=0;
	public function fetch($fetch_style=null,$cursor_orientation=PDO::FETCH_ORI_NEXT,$cursor_offset=0){
		if($fetch_style){
			$rec=parent::fetch($fetch_style,$cursor_orientation,$cursor_offset);
		}
		else{
			$rec=parent::fetch();
		}
		if($rec){
			$this->applyModifiers($rec);
		}
		return $rec;
	}
	public function fetchAll($fetch_style=null,...$args)
	{
		$final_style=($fetch_style ?: $this->_fetchMode);
		if(!$final_style){
			$records=parent::fetchAll(PDO::FETCH_OBJ);
		}elseif(isset($args[1])){
			$records=parent::fetchAll($final_style,$args[0],$args[1]);
		}elseif(isset($args[0])){
			$records=parent::fetchAll($final_style,$args[0]);
		}else{
			$records=parent::fetchAll($final_style);
		}
		if(count($this->modifier)){
			foreach($records as $rec){
				$this->applyModifiers($rec);
			}
		}
		return $records;
	}
	protected function applyModifiers($result){
		if(count($this->modifier)){
			foreach($this->modifier as $m){
				call_user_func_array($m,array($result,$this));
			}
		}
	}
	public function setFetchMode($mode,...$args)
	{
		$this->_fetchMode=$mode;
		if(count($args)===0){
			return parent::setFetchMode($mode);
		}
		if(count($args)===1||$args[1]===null||$args[1]==array()){
			return parent::setFetchMode($mode,$args[0]);
		}
		return parent::setFetchMode($mode,$args[0],$args[1]);
	}
	public function unescapeBin($text){
		return $text;
	}
	protected $modifier=array();
	public function addModifier($function){
		$this->modifier[]=$function;
	}
}
