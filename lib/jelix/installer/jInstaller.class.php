<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2021 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php');
require_once(JELIX_LIB_PATH.'installer/jIInstallerComponent.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerException.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerBase.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerModuleInfos.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerComponentBase.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerComponentModule.class.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerEntryPoint.class.php');
require_once(JELIX_LIB_PATH.'core/jConfigCompiler.class.php');
require_once(JELIX_LIB_PATH.'utils/jIniFile.class.php');
require_once(JELIX_LIB_PATH.'utils/jIniFileModifier.class.php');
require_once(JELIX_LIB_PATH.'utils/jIniMultiFilesModifier.class.php');
require(JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php');
class textInstallReporter implements jIInstallReporter{
	protected $level;
	function __construct($level='notice'){
		$this->level=$level;
	}
	function start(){
		if($this->level=='notice')
			echo "Installation start..\n";
	}
	function message($message,$type=''){
		if(($type=='error'&&$this->level!='')
			||($type=='warning'&&$this->level!='notice'&&$this->level!='')
			||(($type=='notice'||$type=='')&&$this->level=='notice'))
		echo($type!=''?'['.$type.'] ':'').$message."\n";
	}
	function end($results){
		if($this->level=='notice')
			echo "Installation ended.\n";
	}
}
class ghostInstallReporter implements jIInstallReporter{
	function start(){
	}
	function message($message,$type=''){
	}
	function end($results){
	}
}
class jInstaller{
	const STATUS_UNINSTALLED=0;
	const STATUS_INSTALLED=1;
	const ACCESS_FORBIDDEN=0;
	const ACCESS_PRIVATE=1;
	const ACCESS_PUBLIC=2;
	const INSTALL_ERROR_MISSING_DEPENDENCIES=1;
	const INSTALL_ERROR_CIRCULAR_DEPENDENCY=2;
	const FLAG_INSTALL_MODULE=1;
	const FLAG_UPGRADE_MODULE=2;
	const FLAG_ALL=3;
	const FLAG_MIGRATION_11X=66;
	public $installerIni=null;
	protected $entryPoints=array();
	protected $epId=array();
	protected $modules=array();
	protected $allModules=array();
	public $reporter;
	public $messages;
	public $nbError=0;
	public $nbOk=0;
	public $nbWarning=0;
	public $nbNotice=0;
	public $mainConfig;
	public $localConfig;
	public $liveConfig;
	public $localFrameworkConfig;
	protected $newEntryPoints=array();
	function __construct($reporter,$lang=''){
		$this->reporter=$reporter;
		$this->messages=new jInstallerMessageProvider($lang);
		$this->mainConfig=new jIniFileModifier(jApp::mainConfigFile());
		$localConfig=jApp::configPath('localconfig.ini.php');
		if(!file_exists($localConfig)){
			$localConfigDist=jApp::configPath('localconfig.ini.php.dist');
			if(file_exists($localConfigDist)){
			copy($localConfigDist,$localConfig);
			}
			else{
			file_put_contents($localConfig,';<'.'?php die(\'\');?'.'> static local configuration');
			}
		}
		$liveConfig=jApp::configPath('liveconfig.ini.php');
		if(!file_exists($liveConfig)){
			file_put_contents($liveConfig,';<'.'?php die(\'\');?'.'> live configuration');
		}
		$localFramework=jApp::configPath('localframework.ini.php');
		if(!file_exists($localFramework)){
			file_put_contents($localFramework,';<'.'?php die(\'\');?'.'> framework configuration');
		}
		$this->localConfig=new jIniMultiFilesModifier($this->mainConfig,$localConfig);
		$this->liveConfig=new jIniFileModifier($liveConfig);
		$this->localFrameworkConfig=new jIniFileModifier($localFramework);
		$this->installerIni=$this->getInstallerIni();
		$this->readEntryPointData(simplexml_load_file(jApp::appPath('project.xml')));
		$this->installerIni->save();
	}
	protected function getInstallerIni(){
		if(!file_exists(jApp::configPath('installer.ini.php')))
			if(false===@file_put_contents(jApp::configPath('installer.ini.php'),";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

"))
				throw new Exception('impossible to create var/config/installer.ini.php');
		return new jIniFileModifier(jApp::configPath('installer.ini.php'));
	}
	protected function readEntryPointData($xml)
	{
		$configFileList=array();
		foreach($xml->entrypoints->entry as $entrypoint){
			$configFile=(string)$entrypoint['config'];
			if(isset($configFileList[$configFile])){
				continue;
			}
			$configFileList[$configFile]=true;
			$file=(string)$entrypoint['file'];
			if(isset($entrypoint['type'])){
				$type=(string)$entrypoint['type'];
			}else{
				$type="classic";
			}
			$this->setupEntryPointObject($file,$configFile,$type);
		}
		foreach($this->localFrameworkConfig->getSectionList()as $sectionName){
			if(!preg_match('/^entrypoint:(.*)$/',$sectionName,$m)){
				continue;
			}
			$configFile=$this->localFrameworkConfig->getValue('config',$sectionName);
			$type=$this->localFrameworkConfig->getValue('type',$sectionName);
			$this->setupEntryPointObject($m[1],$configFile,$type);
		}
	}
	protected function setupEntryPointObject($file,$configFile,$type)
	{
		$ep=$this->getEntryPointObject($configFile,$file,$type);
		$ep->localConfigIni=new jIniMultiFilesModifier($this->localConfig,$ep->getEpConfigIni());
		$ep->liveConfigIni=$this->liveConfig;
		$epId=$ep->getEpId();
		$this->epId[$file]=$epId;
		$this->entryPoints[$epId]=$ep;
		$this->modules[$epId]=array();
		$modulesList=$ep->getModulesList();
		foreach($modulesList as $name=>$path){
			$module=$ep->getModule($name);
			$this->installerIni->setValue($name.'.installed',$module->isInstalled,$epId);
			$this->installerIni->setValue($name.'.version',$module->version,$epId);
			if(!isset($this->allModules[$path])){
				$this->allModules[$path]=$this->getComponentModule($name,$path,$this);
			}
			$m=$this->allModules[$path];
			$m->addModuleInfos($epId,$module);
			$this->modules[$epId][$name]=$m;
		}
		$modules=$this->installerIni->getValues($epId);
		foreach($modules as $key=>$value){
			$l=explode('.',$key);
			if(count($l)<=1){
				continue;
			}
			if(!isset($modulesList[$l[0]])){
				$this->installerIni->removeValue($key,$epId);
			}
		}
	}
	protected function getEntryPointObject($configFile,$file,$type){
		return new jInstallerEntryPoint($this->mainConfig,$configFile,$file,$type);
	}
	protected function getComponentModule($name,$path,$installer){
		return new jInstallerComponentModule($name,$path,$installer);
	}
	public function getEntryPoint($epId){
		return $this->entryPoints[$epId];
	}
	public function forceModuleVersion($moduleName,$version){
		foreach(array_keys($this->entryPoints)as $epId){
			if(isset($this->modules[$epId][$moduleName])){
				$this->modules[$epId][$moduleName]->setInstalledVersion($epId,$version);
			}
		}
	}
	public function setModuleParameters($moduleName,$parameters,$entrypoint=null){
		if($entrypoint!==null){
			if(!isset($this->epId[$entrypoint]))
				return;
			$epId=$this->epId[$entrypoint];
			if(isset($this->entryPoints[$epId])&&isset($this->modules[$epId][$moduleName])){
				$this->modules[$epId][$moduleName]->setInstallParameters($epId,$parameters);
			}
		}
		else{
			foreach(array_keys($this->entryPoints)as $epId){
				if(isset($this->modules[$epId][$moduleName])){
					$this->modules[$epId][$moduleName]->setInstallParameters($epId,$parameters);
				}
			}
		}
	}
	public function installApplication($flags=false){
		if($flags===false){
			$flags=self::FLAG_ALL;
		}
		$this->startMessage();
		$result=true;
		foreach(array_keys($this->entryPoints)as $epId){
			$result=$result & $this->installEntryPointModules($epId,$flags);
			if(!$result){
				break;
			}
		}
		foreach($this->newEntryPoints as $epId=>$ep){
			$result=$result & $this->installEntryPointModules($epId,$flags);
			if(!$result){
				break;
			}
		}
		$this->installerIni->save();
		$this->endMessage();
		return $result;
	}
	public function installEntryPoint($entrypoint){
		$this->startMessage();
		if(!isset($this->epId[$entrypoint])){
			throw new Exception("unknown entry point");
		}
		$epId=$this->epId[$entrypoint];
		$result=$this->installEntryPointModules($epId);
		$this->installerIni->save();
		$this->endMessage();
		return $result;
	}
	protected function installEntryPointModules($epId,$flags=3){
		$modules=array();
		foreach($this->modules[$epId] as $name=>$module){
			$access=$module->getAccessLevel($epId);
			if($access!=1&&$access!=2){
				if($module->isInstalled($epId)){
					$this->installerIni->removeValue($name.'.installed',$epId);
					$this->installerIni->removeValue($name.'.version',$epId);
					$this->installerIni->removeValue($name.'.version.date',$epId);
					$this->installerIni->removeValue($name.'.firstversion',$epId);
					$this->installerIni->removeValue($name.'.firstversion.date',$epId);
				}
			}
			else{
				$modules[$name]=$module;
			}
		}
		if(count($modules)){
			$result=$this->_installModules($modules,$epId,true,$flags);
			if(!$result){
				return false;
			}
		}
		return true;
	}
	public function installModules($modulesList,$entrypoint=null){
		$this->startMessage();
		if($entrypoint==null){
			$entryPointList=array_keys($this->entryPoints);
		}
		else if(isset($this->epId[$entrypoint])){
			$entryPointList=array($this->epId[$entrypoint]);
		}
		else{
			throw new Exception("unknown entry point");
		}
		$result=true;
		foreach($entryPointList as $epId){
			$allModules=&$this->modules[$epId];
			$modules=array();
			array_unshift($modulesList,'jelix');
			foreach($modulesList as $name){
				if(!isset($allModules[$name])){
					$this->error('module.unknown',$name);
				}
				else
					$modules[]=$allModules[$name];
			}
			$result=$this->_installModules($modules,$epId,false);
			if(!$result)
				break;
			$this->installerIni->save();
		}
		foreach($this->newEntryPoints as $epId=>$ep){
			$allModules=&$this->modules[$epId];
			$modules=array();
			array_unshift($modulesList,'jelix');
			foreach($modulesList as $name){
				if(!isset($allModules[$name])){
					$this->error('module.unknown',$name);
				}
				else
					$modules[]=$allModules[$name];
			}
			$result=$this->_installModules($modules,$epId,false);
			if(!$result)
				break;
			$this->installerIni->save();
		}
		$this->endMessage();
		return $result;
	}
	protected function _installModules(&$modules,$epId,$installWholeApp,$flags=3){
		$this->notice('install.entrypoint.start',$epId);
		$ep=$this->entryPoints[$epId];
		jApp::setConfig($ep->config);
		if($ep->config->disableInstallers)
			$this->notice('install.entrypoint.installers.disabled');
		$result=$this->checkDependencies($modules,$epId);
		if(!$result){
			$this->error('install.bad.dependencies');
			$this->ok('install.entrypoint.bad.end',$epId);
			return false;
		}
		$this->ok('install.dependencies.ok');
		$componentsToInstall=array();
		foreach($this->_componentsToInstall as $item){
			list($component,$toInstall)=$item;
			try{
				if($flags==self::FLAG_MIGRATION_11X){
					$this->installerIni->setValue($component->getName().'.installed',
													1,$epId);
					$this->installerIni->setValue($component->getName().'.version',
													$component->getSourceVersion(),$epId);
					if($ep->config->disableInstallers){
						$upgraders=array();
					}
					else{
						$upgraders=$component->getUpgraders($ep);
						foreach($upgraders as $upgrader){
							$upgrader->preInstall();
						}
					}
					$componentsToInstall[]=array($upgraders,$component,false);
				}
				else if($toInstall){
					if($ep->config->disableInstallers)
						$installer=null;
					else
						$installer=$component->getInstaller($ep,$installWholeApp);
					$componentsToInstall[]=array($installer,$component,$toInstall);
					if($flags & self::FLAG_INSTALL_MODULE&&$installer)
						$installer->preInstall();
				}
				else{
					if($ep->config->disableInstallers){
						$upgraders=array();
					}
					else{
						$upgraders=$component->getUpgraders($ep);
					}
					if($flags & self::FLAG_UPGRADE_MODULE&&count($upgraders)){
						foreach($upgraders as $upgrader){
							$upgrader->preInstall();
						}
					}
					$componentsToInstall[]=array($upgraders,$component,$toInstall);
				}
			}catch(jInstallerException $e){
				$result=false;
				$this->error($e->getLocaleKey(),$e->getLocaleParameters());
			}catch(Exception $e){
				$result=false;
				$this->error('install.module.error',array($component->getName(),$e->getMessage()));
			}
		}
		if(!$result){
			$this->warning('install.entrypoint.bad.end',$epId);
			return false;
		}
		$installedModules=array();
		try{
			foreach($componentsToInstall as $item){
				list($installer,$component,$toInstall)=$item;
				if($toInstall){
					if($installer&&($flags & self::FLAG_INSTALL_MODULE))
						$installer->install();
					$this->installerIni->setValue($component->getName().'.installed',
													1,$epId);
					$this->installerIni->setValue($component->getName().'.version',
													$component->getSourceVersion(),$epId);
					$this->installerIni->setValue($component->getName().'.version.date',
													$component->getSourceDate(),$epId);
					$this->installerIni->setValue($component->getName().'.firstversion',
													$component->getSourceVersion(),$epId);
					$this->installerIni->setValue($component->getName().'.firstversion.date',
													$component->getSourceDate(),$epId);
					$this->ok('install.module.installed',$component->getName());
					$installedModules[]=array($installer,$component,true);
				}
				else{
					$lastversion='';
					foreach($installer as $upgrader){
						if($flags & self::FLAG_UPGRADE_MODULE)
							$upgrader->install();
						$this->installerIni->setValue($component->getName().'.version',
													$upgrader->version,$epId);
						$this->installerIni->setValue($component->getName().'.version.date',
													$upgrader->date,$epId);
						$this->ok('install.module.upgraded',
								array($component->getName(),$upgrader->version));
						$lastversion=$upgrader->version;
					}
					if($lastversion!=$component->getSourceVersion()){
						$this->installerIni->setValue($component->getName().'.version',
													$component->getSourceVersion(),$epId);
						$this->installerIni->setValue($component->getName().'.version.date',
													$component->getSourceDate(),$epId);
						$this->ok('install.module.upgraded',
								array($component->getName(),$component->getSourceVersion()));
					}
					$installedModules[]=array($installer,$component,false);
				}
				if($ep->configIni->isModified()||
					$ep->localConfigIni->isModified()||
					$ep->liveConfigIni->isModified()
				){
					$ep->configIni->save();
					$ep->localConfigIni->save();
					$ep->liveConfigIni->save();
					$ep->config=
						jConfigCompiler::read($ep->configFile,true,
							$ep->isCliScript,
							$ep->scriptName);
					jApp::setConfig($ep->config);
				}
			}
		}catch(jInstallerException $e){
			$result=false;
			$this->error($e->getLocaleKey(),$e->getLocaleParameters());
		}catch(Exception $e){
			$result=false;
			$this->error('install.module.error',array($component->getName(),$e->getMessage()));
		}
		if(!$result){
			$this->warning('install.entrypoint.bad.end',$epId);
			return false;
		}
		foreach($installedModules as $item){
			try{
				list($installer,$component,$toInstall)=$item;
				if($toInstall){
					if($installer&&($flags & self::FLAG_INSTALL_MODULE)){
						$installer->postInstall();
						$component->installFinished($ep);
						$this->declareNewEntryPoints($installer);
					}
				}
				else if($flags & self::FLAG_UPGRADE_MODULE){
					foreach($installer as $upgrader){
						$upgrader->postInstall();
						$component->upgradeFinished($ep,$upgrader);
						$this->declareNewEntryPoints($upgrader);
					}
				}
				if($ep->configIni->isModified()||
					$ep->localConfigIni->isModified()||
					$ep->liveConfigIni->isModified()
				){
					$ep->configIni->save();
					$ep->localConfigIni->save();
					$ep->liveConfigIni->save();
					$ep->config=
						jConfigCompiler::read($ep->configFile,true,
							$ep->isCliScript,
							$ep->scriptName);
					jApp::setConfig($ep->config);
				}
				if($this->localFrameworkConfig->isModified()){
					$this->localFrameworkConfig->save();
				}
			}catch(jInstallerException $e){
				$result=false;
				$this->error($e->getLocaleKey(),$e->getLocaleParameters());
			}catch(Exception $e){
				$result=false;
				$this->error('install.module.error',array($component->getName(),$e->getMessage()));
			}
		}
		$this->ok('install.entrypoint.end',$epId);
		return $result;
	}
	protected function declareNewEntryPoints($installer)
	{
		$newEps=$installer->getNewEntrypoints();
		if(count($newEps)==0){
			return;
		}
		$this->newEntryPoints=array_merge($this->newEntryPoints,$newEps);
		foreach($newEps as $epId=>$ep){
			$section='entrypoint:'.$ep['file'];
			$this->localFrameworkConfig->setValue('config',$ep['config'],$section);
			$this->localFrameworkConfig->setValue('type',$ep['type'],$section);
			$this->setupEntryPointObject($ep['file'],$ep['config'],$ep['type']);
			file_put_contents(jApp::configPath('config.'.$ep['file']),'<?php '."\n\$var=".var_export($this->getEntryPoint($epId)->getConfigObj(),true));
		}
	}
	protected $_componentsToInstall=array();
	protected $_checkedComponents=array();
	protected $_checkedCircularDependency=array();
	protected function checkDependencies($list,$epId){
		$this->_checkedComponents=array();
		$this->_componentsToInstall=array();
		$result=true;
		foreach($list as $component){
			$this->_checkedCircularDependency=array();
			if(!isset($this->_checkedComponents[$component->getName()])){
				try{
					$component->init();
					$this->_checkDependencies($component,$epId);
					if($this->entryPoints[$epId]->config->disableInstallers
						||!$component->isInstalled($epId)){
						$this->_componentsToInstall[]=array($component,true);
					}
					else if(!$component->isUpgraded($epId)){
						$this->_componentsToInstall[]=array($component,false);
					}
				}catch(jInstallerException $e){
					$result=false;
					$this->error($e->getLocaleKey(),$e->getLocaleParameters());
				}catch(Exception $e){
					$result=false;
					$this->error($e->getMessage(). " comp=".$component->getName(),null,true);
				}
			}
		}
		return $result;
	}
	protected function _checkDependencies($component,$epId){
		if(isset($this->_checkedCircularDependency[$component->getName()])){
			$component->inError=self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
			throw new jInstallerException('module.circular.dependency',$component->getName());
		}
		$this->_checkedCircularDependency[$component->getName()]=true;
		$compNeeded='';
		foreach($component->dependencies as $compInfo){
			if($compInfo['type']!='module')
				continue;
			$name=$compInfo['name'];
			$comp=null;
			if(isset($this->modules[$epId][$name])){
				$comp=$this->modules[$epId][$name];
			}
			if(!$comp){
				if(! $compInfo['optional']){
					$compNeeded.=$name.', ';
				}
			}
			else{
				if(!isset($this->_checkedComponents[$comp->getName()])){
					$comp->init();
				}
				if(!$comp->checkVersion($compInfo['minversion'],$compInfo['maxversion'])){
					if($name=='jelix'){
						$args=$component->getJelixVersion();
						array_unshift($args,$component->getName());
						throw new jInstallerException('module.bad.jelix.version',$args);
					}
					else
						throw new jInstallerException('module.bad.dependency.version',array($component->getName(),$comp->getName(),$compInfo['minversion'],$compInfo['maxversion']));
				}
				if(!isset($this->_checkedComponents[$comp->getName()])){
					$this->_checkDependencies($comp,$epId);
					if($this->entryPoints[$epId]->config->disableInstallers
						||!$comp->isInstalled($epId)){
						$this->_componentsToInstall[]=array($comp,true);
					}
					else if(!$comp->isUpgraded($epId)){
						$this->_componentsToInstall[]=array($comp,false);
					}
				}
			}
		}
		$this->_checkedComponents[$component->getName()]=true;
		unset($this->_checkedCircularDependency[$component->getName()]);
		if($compNeeded){
			$component->inError=self::INSTALL_ERROR_MISSING_DEPENDENCIES;
			throw new jInstallerException('module.needed',array($component->getName(),$compNeeded));
		}
	}
	protected function startMessage(){
		$this->nbError=0;
		$this->nbOk=0;
		$this->nbWarning=0;
		$this->nbNotice=0;
		$this->reporter->start();
	}
	protected function endMessage(){
		$this->reporter->end(array('error'=>$this->nbError,'warning'=>$this->nbWarning,'ok'=>$this->nbOk,'notice'=>$this->nbNotice));
	}
	protected function error($msg,$params=null,$fullString=false){
		if($this->reporter){
			if(!$fullString)
				$msg=$this->messages->get($msg,$params);
			$this->reporter->message($msg,'error');
		}
		$this->nbError ++;
	}
	protected function ok($msg,$params=null,$fullString=false){
		if($this->reporter){
			if(!$fullString)
				$msg=$this->messages->get($msg,$params);
			$this->reporter->message($msg,'');
		}
		$this->nbOk ++;
	}
	protected function warning($msg,$params=null,$fullString=false){
		if($this->reporter){
			if(!$fullString)
				$msg=$this->messages->get($msg,$params);
			$this->reporter->message($msg,'warning');
		}
		$this->nbWarning ++;
	}
	protected function notice($msg,$params=null,$fullString=false){
		if($this->reporter){
			if(!$fullString)
				$msg=$this->messages->get($msg,$params);
			$this->reporter->message($msg,'notice');
		}
		$this->nbNotice ++;
	}
}
