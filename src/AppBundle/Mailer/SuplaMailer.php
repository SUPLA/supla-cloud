<?php 
/*
 src/AppBundle/Mailer/Mailer.php

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace AppBundle\Mailer;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;

/**
 * @author Przemyslaw Zygmunt AC SOFTWARE SP. Z O.O. <p.zygmunt@acsoftware.pl>
 */
class SuplaMailer 
{
	protected $router;
	protected $templating;
	protected $mailer_from;
	protected $mailer;
	protected $email_admin;
	
	public function __construct($router, $templating, $mailer, $mailer_from, $email_admin)
	{
		$this->router = $router;
		$this->templating = $templating;
		$this->mailer_from = $mailer_from;
		$this->mailer = $mailer;
		$this->email_admin = $email_admin;
	}
	
	private function extractSubjectAndBody($template, $params, &$subject) 
	{
		if ( $template == '' ) 
			return '';
		
		$template = 'AppBundle:Email:'.$template;
	
		$body = $this->templating->render($template, $params);
		
		if ( $subject !== null ) {	
			$lines = explode("\n", trim($body));
			$subject = $lines[0];
			$body = implode("\n", array_slice($lines, 1));
		}
		
		return $body;		
	}
	
	private function sendEmailMessage($txtTmpl, $htmlTmpl, $fromEmail, $toEmail, $params)
	{
		$subject = null;
		$bodyHtml = $this->extractSubjectAndBody($htmlTmpl, $params, $subject);
		
		$subject = '';
		$bodyTxt = $this->extractSubjectAndBody($txtTmpl, $params, $subject);
		
	
		$message = \Swift_Message::newInstance()
		->setSubject($subject)
		->setFrom($fromEmail)
		->setTo($toEmail);
		
		if ( $bodyHtml == '' )
			$message->setBody($bodyTxt, 'text/plain');
		else {
			$message->setBody($bodyHtml, 'text/html');
			$message->addPart($bodyTxt, 'text/plain');
		}
			
	
		$this->mailer->send($message);
	}
	
	public function sendConfirmationEmailMessage(UserInterface $user)
	{
		$url = $this->router->generate('_account_confirmemail', array('token' => $user->getToken(), true), true);
		
		$this->sendEmailMessage('confirm.txt.twig', 
				                '', // 'confirm.html.twig'
				                $this->mailer_from, 
				                $user->getEmail(),
				                array(
			                     	'user' => $user,
				                    'confirmationUrl' =>  $url
		                        ));
	}
	
	public function sendResetPasswordEmailMessage(UserInterface $user)
	{
		$url = $this->router->generate('_account_reset_passwd', array('token' => $user->getToken(), true), true);
	
		$this->sendEmailMessage('resetpwd.txt.twig',
				'', // resetpwd.html.twig
				$this->mailer_from,
				$user->getEmail(),
				array(
						'user' => $user,
						'confirmationUrl' =>  $url
				));
	}
	
	public function sendActivationEmailMessage(UserInterface $user)
	{

		$this->sendEmailMessage('activation.txt.twig',
				'',
				$this->mailer_from,
				$this->email_admin,
				array(
						'user' => $user,
				));
	}
	

	public function test() {

	}
}


?>
