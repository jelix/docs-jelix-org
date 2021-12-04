<?php
/* comments & extra-whitespaces have been removed by jBuildTools*/
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author       Laurent Jouanneau
 *
 * @copyright    2020 Laurent Jouanneau
 *
 * @link         https://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */
class mailerCtrl extends \jControllerCmdLine
{
	protected $allowed_options=array(
	);
	protected $allowed_parameters=array(
		'test'=>array(
			'email'=>true,
			'appname'=>false
		),
	);
	public function test()
	{
		$rep=$this->getResponse('cmdline');
		$email=$this->param('email');
		$mail=new \jMailer();
		$mail->From=\jApp::config()->mailer['webmasterEmail'];
		$mail->FromName=\jApp::config()->mailer['webmasterName'];
		$mail->Sender=\jApp::config()->mailer['webmasterEmail'];
		$mail->Subject='Email test';
		$mail->AddAddress($email);
		$mail->isHtml(true);
		$domain=$this->param('appname');
		if($domain==''){
			$domain=jServer::getDomainName();
			if($domain==''){
				$domain=gethostname();
				if($domain==''){
					$domain='Unknown app';
				}
			}
		}
		$tpl=new \jTpl();
		$tpl->assign('domain_name',$domain);
		$body=$tpl->fetch('jelix~email_test');
		$mail->msgHTML($body,'',array($mail,'html2textKeepLinkSafe'));
		if(!$mail->Send()){
			$rep->addContent("It seems something goes wrong during the message sending.\n");
			$rep->setExitCode(1);
		}
		else{
			$rep->addContent("Message has been sent.\n");
		}
		return $rep;
	}
}
