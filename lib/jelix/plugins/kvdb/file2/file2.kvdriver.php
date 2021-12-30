<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
 * @package     jelix
 * @subpackage  kvdb_plugin
 * @author      Yannick Le Guédart
 * @contributor Laurent Jouanneau
 * @copyright   2009 Yannick Le Guédart, 2010 Laurent Jouanneau
 *
 * @link     http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class file2KVDriver extends jKVDriver{
	protected $dir;
		protected function _connect(){
		$cnx=new fileServer(jApp::tempPath('filekv'));
		return $cnx;
	}
		protected function _disconnect(){}
	public function get($key){
		return $this->_connection->get($key);
	}
	public function set($key,$value){
		return $this->_connection->set(
			$key,
			$value
		);
	}
	public function insert($key,$value)
	{
		$val=$this->_connection->get($key);
		if($val!==false){
			return false;
		}
		return $this->_connection->set(
			$key,
			$value
		);
	}
	public function replace($key,$value)
	{
		$val=$this->_connection->get($key);
		if($val===false){
			return false;
		}
		return $this->_connection->set(
			$key,
			$value
		);
	}
	public function delete($key){
		return $this->_connection->delete($key);
	}
	public function flush(){
		return $this->_connection->flush();
	}
	public function append($key,$value)
	{
		$val=$this->_connection->get($key);
		if($val===false){
			return false;
		}
		$val.=$value;
		if($this->_connection->set(
			$key,
			$val
		)){
			return $val;
		}
		return false;
	}
	public function prepend($key,$value)
	{
		$val=$this->_connection->get($key);
		if($val===false){
			return false;
		}
		$val=$value.$val;
		if($this->_connection->set(
			$key,
			$val
		)){
			return $val;
		}
		return false;
	}
	public function increment($key,$incr=1)
	{
		$val=$this->_connection->get($key);
		if($val===false||!is_numeric($val)){
			return false;
		}
		$val+=$incr;
		if($this->_connection->set(
			$key,
			$val
		)){
			return $val;
		}
		return false;
	}
	public function decrement($key,$decr=1)
	{
		$val=$this->_connection->get($key);
		if($val===false||!is_numeric($val)){
			return false;
		}
		$val-=$decr;
		if($this->_connection->set(
			$key,
			$val
		)){
			return $val;
		}
		return false;
	}
}
class fileServer{
	protected $dir;
	public function __construct($directory){
		$this->dir=$directory;
		if(! file_exists($this->dir)){
			jFile::createDir($this->dir);
		}
	}
	public function set($key,$value,$ttl=0){
		$r=false;
		if($fl=@fopen($this->dir . '/.flock','w+')){
			if(flock($fl,LOCK_EX)){
				$md5=md5($key);
				$subdir=$md5[0].$md5[1];
				if(! file_exists($this->dir . '/' . $subdir)){
					jFile::createDir($this->dir . '/' . $subdir);
				}
				$fn=$this->dir . '/' . $subdir . '/' . $md5;
				if($f=@gzopen($fn . '.tmp','w')){
					fputs($f,base64_encode(serialize($value)));
					fclose($f);
					@touch("$fn.tmp",time()+ $ttl);
					$r=@rename("$fn.tmp",$fn);
					chmod($fn,jApp::config()->chmodFile);
				}
				flock($fl,LOCK_UN);
			}
		}
		return $r;
	}
	public function get($key){
		$r=false;
		$md5=md5($key);
		$subdir=$md5[0].$md5[1];
		$fn=$this->dir . '/' . $subdir . '/' . $md5;
		if(! file_exists($fn)){
			return false;
		}
		if(@filemtime($fn)< time()){
			@unlink($fn);
			return false;
		}
		if($f=@gzopen($fn,'rb')){
			$r='';
			while($read=fread($f,1024)){
				$r.=$read;
			}
			fclose($f);
		}
		return @unserialize(base64_decode($r));
	}
	public function delete($key){
		$md5=md5($key);
		$subdir=$md5[0].$md5[1];
		$fn=$this->dir . '/' . $subdir . '/' . $md5;
		return @unlink($fn);
	}
	public function flush(){
		return @unlink($this->dir);
	}
}
