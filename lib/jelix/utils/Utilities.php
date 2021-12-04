<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Utilities;
function is_resource($value)
{
	if(\is_resource($value)){
		return true;
	}
	if(!\is_object($value)){
		return false;
	}
	$o=new \ReflectionObject($value);
	if($o->getConstructor()===null&&
		count($o->getMethods())===0&&
		$o->isInternal()&&
		$o->isFinal()&&
		!$o->isCloneable()
	){
		return true;
	}
	return false;
}
