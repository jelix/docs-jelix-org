<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
* @package    jelix
* @subpackage utils
* @author     Laurent Jouanneau
* @copyright  2008-2013 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
class jIniFileModifier{
	const TK_WS=0;
	const TK_COMMENT=1;
	const TK_SECTION=2;
	const TK_VALUE=3;
	const TK_ARR_VALUE=4;
	protected $content=array();
	protected $filename='';
	protected $modified=false;
	function __construct($filename){
		if(!file_exists($filename)||!is_file($filename))
			throw new Exception('(23)The file '.$filename.' doesn\'t exist');
		$this->filename=$filename;
		$this->parse(preg_split("/(\r\n|\n|\r)/",file_get_contents($filename)));
	}
	function getFileName(){
		return $this->filename;
	}
	protected function parse($lines){
		$this->content=array(0=>array());
		$currentSection=0;
		$multiline=false;
		$currentValue=null;
		$arrayContents=array();
		foreach($lines as $num=>$line){
			if($multiline){
				if(preg_match('/^(.*)"\s*$/',$line,$m)){
					$currentValue[2].=$m[1];
					$multiline=false;
					$this->content[$currentSection][]=$currentValue;
				}else{
					$currentValue[2].=$line."\n";
				}
			}elseif(preg_match('/^\s*([\\w0-9_.\\-]+)(\\[[^\\[\\]]*\\])?\s*=\s*(")?([^"]*)(")?(\s*)/ui',$line,$m)){
				list($all,$name,$foundkey,$firstquote,$value,$secondquote,$lastspace)=$m;
				if($foundkey!=''){
					$key=substr($foundkey,1,-1);
					if($key==''){
						if(isset($arrayContents[$currentSection][$name])){
							$key=count(
								$arrayContents[$currentSection][$name]
							);
						}else{
							$key=0;
						}
					}
					$currentValue=array(self::TK_ARR_VALUE,$name,$value,$key);
					$arrayContents[$currentSection][$name][$key]=$value;
				}
				else
					$currentValue=array(self::TK_VALUE,$name,$value);
				if($firstquote=='"'&&$secondquote==''){
					$multiline=true;
					$currentValue[2].="\n";
				}else{
					if($firstquote==''&&$secondquote=='')
						$currentValue[2]=trim($value);
					$this->content[$currentSection][]=$currentValue;
				}
			}elseif(preg_match('/^(\\s*;.*)$/',$line,$m)){
				$this->content[$currentSection][]=array(self::TK_COMMENT,$m[1]);
			}elseif(preg_match('/^(\\s*\\[([^\\]]+)\\]\\s*)/ui',$line,$m)){
				if(strpos($m[2],';')){
					throw new Exception("Invalid syntax for the section name: \"".$m[2].'"');
				}
				$currentSection=$m[2];
				$this->content[$currentSection]=array(
					array(self::TK_SECTION,$m[1]),
				);
			}else{
				$this->content[$currentSection][]=array(self::TK_WS,$line);
			}
		}
	}
	public function setValue($name,$value,$section=0,$key=null)
	{
		if(!preg_match('/^[^\\[\\]]*$/',$name)){
			throw new \Exception("Invalid value name $name");
		}
		if(is_array($value)){
			if($key!==null){
				throw new \Exception("You cannot indicate a key for an array value");
			}
			$this->_setArrayValue($name,$value,$section);
		}
		else{
			$this->_setValue($name,$value,$section,$key);
		}
	}
	protected function _setValue($name,$value,$section=0,$key=null){
		if(is_string($key)&&!preg_match('/^[^\\[\\]]*$/',$key)){
			throw new \Exception("Invalid key $key for the value $name");
		}
		$foundValue=false;
		$lastKey=-1;
		if(isset($this->content[$section])){
			$deleteMode=false;
			foreach($this->content[$section] as $k=>$item){
				if($deleteMode){
					if($item[0]==self::TK_ARR_VALUE&&$item[1]==$name){
						$this->content[$section][$k]=array(self::TK_WS,'--');
						$this->modified=true;
					}
					continue;
				}
				if(($item[0]!=self::TK_VALUE&&$item[0]!=self::TK_ARR_VALUE)
					||$item[1]!=$name)
					continue;
				if($item[0]==self::TK_ARR_VALUE&&$key!==null){
					if($item[3]!==$key||$key===''){
						if(is_numeric($item[3])){
							$lastKey=$item[3];
						}
						continue;
					}
				}
				if($key!==null){
					if($key===''){
						$key=0;
					}
					if($item[0]==self::TK_VALUE||!$this->_compareNewValue($item[2],$value)){
						$this->content[$section][$k]=array(self::TK_ARR_VALUE,$name,$value,$key);
						$this->modified=true;
					}
				}else{
					if(!$this->_compareNewValue($item[2],$value)){
						$this->content[$section][$k]=array(self::TK_VALUE,$name,$value);
						$this->modified=true;
					}
					$this->content[$section][$k]=array(self::TK_VALUE,$name,$value);
					if($item[0]==self::TK_ARR_VALUE){
						$deleteMode=true;
						$foundValue=true;
						continue;
					}
				}
				$foundValue=true;
				break;
			}
		}
		else{
			$this->content[$section]=array(array(self::TK_SECTION,'['.$section.']'));
		}
		if(!$foundValue){
			if($key===null){
				$this->content[$section][]=array(self::TK_VALUE,$name,$value);
			}else{
				if($key===''){
					if($lastKey!=-1){
						$key=++$lastKey;
					}else{
						$key=0;
					}
				}
				$this->content[$section][]=array(self::TK_ARR_VALUE,$name,$value,$key);
			}
			$this->modified=true;
		}
	}
	protected function _compareNewValue($iniValue,$newValue){
		$iniVal=$this->convertValue($iniValue);
		$newVal=$this->convertValue($newValue);
		return($iniVal==$newVal);
	}
	protected function _setArrayValue($name,$value,$section=0){
		$foundKeys=array_combine(array_keys($value),
									array_fill(0,count($value),false));
		if(isset($this->content[$section])){
			foreach($this->content[$section] as $k=>$item){
				if(($item[0]!=self::TK_VALUE&&$item[0]!=self::TK_ARR_VALUE)
					||$item[1]!=$name){
					continue;
				}
				if($item[0]==self::TK_ARR_VALUE){
					if(isset($value[$item[3]])){
						$foundKeys[$item[3]]=true;
						$this->content[$section][$k][2]=$value[$item[3]];
					}
					else{
						$this->content[$section][$k]=array(self::TK_WS,'--');
					}
				}
				else{
					$this->content[$section][$k]=array(self::TK_WS,'--');
				}
			}
		}else{
			$this->content[$section]=array(array(self::TK_SECTION,'['.$section.']'));
		}
		foreach($value as $k=>$v){
			if(!$foundKeys[$k]){
				$this->content[$section][]=array(self::TK_ARR_VALUE,$name,$v,$k);
			}
		}
		$this->modified=true;
	}
	public function setValues($values,$section=0){
		foreach($values as $name=>$val){
			$this->setValue($name,$val,$section);
		}
	}
	public function removeValue($name,$section=0,$key=null,$removePreviousComment=true){
		if($section===0&&$name=='')
			return;
		if($name==''){
			$this->removeSection($section,$removePreviousComment);
			return;
		}
		if(isset($this->content[$section])){
			$deleteMode=false;
			$previousComment=array();
			foreach($this->content[$section] as $k=>$item){
				if($deleteMode){
					if($item[0]==self::TK_ARR_VALUE&&$item[1]==$name)
						$this->content[$section][$k]=array(self::TK_WS,'--');
					continue;
				}
				if($item[0]==self::TK_COMMENT){
					if($removePreviousComment)
						$previousComment[]=$k;
					continue;
				}
				if($item[0]==self::TK_WS){
					if($removePreviousComment)
						$previousComment[]=$k;
					continue;
				}
				if($item[1]!=$name){
					$previousComment=array();
					continue;
				}
				if($item[0]==self::TK_ARR_VALUE&&$key!==null){
					if($item[3]!=$key){
						$previousComment=array();
						continue;
					}
				}
				$this->modified=true;
				if(count($previousComment)){
					$kc=array_pop($previousComment);
					while($kc!==null&&$this->content[$section][$kc][0]==self::TK_WS){
						$kc=array_pop($previousComment);
					}
					while($kc!==null&&$this->content[$section][$kc][0]==self::TK_COMMENT){
						if(strpos($this->content[$section][$kc][1],"<?")===false){
							$this->content[$section][$kc]=array(self::TK_WS,'--');
						}
						$kc=array_pop($previousComment);
					}
				}
				if($key!==null){
					$this->content[$section][$k]=array(self::TK_WS,'--');
				}else{
					$this->content[$section][$k]=array(self::TK_WS,'--');
					if($item[0]==self::TK_ARR_VALUE){
						$deleteMode=true;
						continue;
					}
				}
				break;
			}
		}
	}
	public function removeSection($section=0,$removePreviousComment=true)
	{
		if($section===0||!isset($this->content[$section])){
			return;
		}
		if($removePreviousComment){
			$previousSection=-1;
			foreach($this->content as $s=>$c){
				if($s===$section){
					break;
				}else{
					$previousSection=$s;
				}
			}
			if($previousSection!=-1){
				$s=$this->content[$previousSection];
				end($s);
				$tok=current($s);
				while($tok!==false){
					if($tok[0]!=self::TK_WS&&$tok[0]!=self::TK_COMMENT){
						break;
					}
					if($tok[0]==self::TK_COMMENT&&strpos($tok[1],'<?')===false){
						$this->content[$previousSection][key($s)]=array(self::TK_WS,'--');
					}
					$tok=prev($s);
				}
			}
		}
		unset($this->content[$section]);
		$this->modified=true;
	}
	public function getValue($name,$section=0,$key=null){
		if(!isset($this->content[$section])){
			return null;
		}
		$arrayValue=array();
		$isArray=false;
		foreach($this->content[$section] as $k=>$item){
			if(($item[0]!=self::TK_VALUE&&$item[0]!=self::TK_ARR_VALUE)
				||$item[1]!=$name)
				continue;
			if($item[0]==self::TK_ARR_VALUE){
				if($key!==null){
					if($item[3]!=$key)
						continue;
				}
				else{
					$isArray=true;
					$arrayValue[$item[3]]=$this->convertValue($item[2]);
					continue;
				}
			}
			return $this->convertValue($item[2]);
		}
		if($isArray)
			return $arrayValue;
		return null;
	}
	public function getValues($section=0){
		if(!isset($this->content[$section])){
			return array();
		}
		$values=array();
		foreach($this->content[$section] as $k=>$item){
			if($item[0]!=self::TK_VALUE&&$item[0]!=self::TK_ARR_VALUE)
				continue;
			$val=$this->convertValue($item[2]);
			if($item[0]==self::TK_VALUE){
				$values[$item[1]]=$val;
			}
			else{
				$values[$item[1]][$item[3]]=$val;
			}
		}
		return $values;
	}
	protected function convertValue($value)
	{
		if(!is_string($value)){
			return $value;
		}
		if(preg_match('/^-?[0-9]$/',$value)){
			return intval($value);
		}elseif(preg_match('/^-?[0-9\.]$/',$value)){
			return floatval($value);
		}elseif(strtolower($value)==='true'||strtolower($value)==='on'||strtolower($value)==='yes'){
			return true;
		}elseif(strtolower($value)==='false'||strtolower($value)==='off'||strtolower($value)==='no'||strtolower($value)==='none'){
			return false;
		}
		return $value;
	}
	public function save($chmod=null){
		if($this->modified){
			if(false===@file_put_contents($this->filename,$this->generateIni())){
				throw new Exception("Impossible to write into ".$this->filename);
			}
			else if($chmod){
				chmod($this->filename,$chmod);
			}
			$this->modified=false;
		}
	}
	public function saveAs($filename){
		file_put_contents($filename,$this->generateIni());
	}
	public function isModified(){
		return $this->modified;
	}
	public function isSection($name){
		return isset($this->content[$name]);
	}
	public function getSectionList(){
		$list=array_keys($this->content);
		array_shift($list);
		return $list;
	}
	protected function generateIni()
	{
		$content='';
		$lastToken=null;
		foreach($this->content as $sectionname=>$section){
			foreach($section as $item){
				$lastToken=$item[0];
				switch($item[0]){
				case self::TK_SECTION:
					if($item[1]!='0')
						$content.=$item[1]."\n";
					break;
				case self::TK_WS:
					if($item[1]=='--')
						break;
				case self::TK_COMMENT:
					$content.=$item[1]."\n";
					break;
				case self::TK_VALUE:
						$content.=$item[1].'='.$this->getIniValue($item[2])."\n";
					break;
				case self::TK_ARR_VALUE:
					if(is_numeric($item[3])){
						$content.=$item[1].'[]='.$this->getIniValue($item[2])."\n";
					}
					else{
						$content.=$item[1].'['.$item[3].']='.$this->getIniValue($item[2])."\n";
					}
					break;
				}
			}
		}
		if($lastToken===self::TK_WS){
			$content=substr($content,0,-1);
		}
		return $content;
	}
	protected function getIniValue($value){
		if(is_bool($value)){
			if($value===false){
				return "off";
			}else{
				return "on";
			}
		}
		if($value===''||
			is_numeric(trim($value))||
			(is_string($value)&&preg_match('/^[\\w\\-\\.]*$/u',$value)&&
				strpos("\n",$value)===false)
		){
			return $value;
		}else{
			$value='"'.$value.'"';
		}
		return $value;
	}
	public function import(jIniFileModifier $ini,$sectionPrefix='',$separator='_'){
		foreach($ini->content as $section=>$values){
			if($sectionPrefix){
				if($section=="0"){
					$realSection=$sectionPrefix;
				}
				else{
					$realSection=$sectionPrefix.$separator.$section;
				}
			}
			else $realSection=$section;
			if(isset($this->content[$realSection])){
				$this->mergeValues($values,$realSection);
			}
			else{
				if($values[0][0]==self::TK_SECTION)
					$values[0][1]='['.$realSection.']';
				else{
					array_unshift($values,array(self::TK_SECTION,'['.$realSection.']'));
				}
				$this->content[$realSection]=$values;
				$this->modified=true;
			}
		}
	}
	public function mergeSection($sectionSource,$sectionTarget){
		if(!isset($this->content[$sectionTarget]))
			return $this->renameSection($sectionSource,$sectionTarget);
		if(!isset($this->content[$sectionSource]))
			return false;
		$this->mergeValues($this->content[$sectionSource],$sectionTarget);
		if($sectionSource=="0")
			$this->content[$sectionSource]=array();
		else
			unset($this->content[$sectionSource]);
		$this->modified=true;
		return true;
	}
	protected function mergeValues($values,$sectionTarget)
	{
		$previousItems=array();
		$arrayValuesToReplace=array();
		foreach($values as $k=>$item){
			switch($item[0]){
				case self::TK_SECTION:
				break;
				case self::TK_WS:
				if($item[1]=='--')
					break;
				case self::TK_COMMENT:
				$previousItems []=$item;
				break;
				case self::TK_VALUE:
				case self::TK_ARR_VALUE:
					$found=false;
					$lastNonValues=-1;
					foreach($this->content[$sectionTarget] as $j=>$item2){
						if($item2[0]!=self::TK_VALUE&&$item2[0]!=self::TK_ARR_VALUE){
							if($lastNonValues==-1&&$item2[0]!=self::TK_SECTION)
								$lastNonValues=$j;
							continue;
						}
						if($item2[1]!=$item[1]){
							$lastNonValues=-1;
							continue;
						}
						if($item[0]==self::TK_ARR_VALUE&&$item2[0]==$item[0]){
							if($item[3]!==$item2[3]){
								$lastNonValues=-1;
								continue;
							}
						}
						$found=true;
						$this->modified=true;
						if($item2[0]!=$item[0]){
							if($item2[0]==self::TK_VALUE){
								$this->content[$sectionTarget][$j]=$item;
							}
							else{
								$arrayValuesToReplace[$item[1]]=$item[2];
							}
							continue;
						}
						$this->content[$sectionTarget][$j][2]=$item[2];
						break;
					}
					if(!$found){
						$previousItems[]=$item;
						if($lastNonValues > 0){
							$previousItems=array_splice($this->content[$sectionTarget],$lastNonValues,$j,$previousItems);
						}
						$this->content[$sectionTarget]=array_merge($this->content[$sectionTarget],$previousItems);
						$this->modified=true;
					}
					$previousItems=array();
					break;
			}
		}
		foreach($arrayValuesToReplace as $name=>$value){
			$this->setValue($name,$value,$sectionTarget);
		}
	}
	public function renameValue($name,$newName,$section=0){
		if(!isset($this->content[$section]))
			return false;
		foreach($this->content[$section] as $k=>$item){
			if($item[0]!=self::TK_VALUE&&$item[0]!=self::TK_ARR_VALUE){
				continue;
			}
			if($item[1]!=$name){
				continue;
			}
			$this->content[$section][$k][1]=$newName;
			$this->modified=true;
			if($item[0]==self::TK_VALUE){
				break;
			}
		}
		return true;
	}
	public function renameSection($oldName,$newName){
		if(!isset($this->content[$oldName]))
			return false;
		if(isset($this->content[$newName])){
			return $this->mergeSection($oldName,$newName);
		}
		$newcontent=array();
		foreach($this->content as $section=>$values){
			if((string)$oldName==(string)$section){
				if($section=="0"){
					$newcontent[0]=array();
				}
				if($values[0][0]==self::TK_SECTION)
					$values[0][1]='['.$newName.']';
				else{
					array_unshift($values,array(self::TK_SECTION,'['.$newName.']'));
				}
				$newcontent[$newName]=$values;
			}
			else
				$newcontent [$section]=$values;
		}
		$this->content=$newcontent;
		$this->modified=true;
		return true;
	}
}
