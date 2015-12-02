<?php
/*
 src/AppBundle/Controller/AccountController.php

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

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Form\Type\ResetPasswordType;
use AppBundle\Form\Type\RegistrationType;
use AppBundle\Form\Type\ForgotPasswordType;
use AppBundle\Form\Model\Registration;
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\Model\ResetPassword;
use AppBundle\Form\Model\ForgotPassword;
use AppBundle\Entity\User;
use AppBundle\Model\UserManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 * @Route("/account")
 */
class AccountController extends Controller
{

	/**
	 * @Route("/register", name="_account_register")
	 */
    public function registerAction(Request $request)
    {    	
        $registration = new Registration();
        $form = $this->createForm(new RegistrationType(), $registration, array(
            'action' => $this->generateUrl('_account_create'),
            'validation_groups' => array('_registration')
        ));

        return $this->render(
            'AppBundle:Account:register.html.twig',
            array('form' => $form->createView(),
            	  'locale' => $request->getLocale()
            )
        );
    }
    
    /**
     * @Route("/checkemail", name="_account_checkemail")
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('_registration_email');
        $this->container->get('session')->remove('_registration_email');
        
        if ( $email === null ) 
        	return $this->redirectToRoute("_auth_login");
        
        return $this->render(
        		'AppBundle:Account:checkemail.html.twig',
        		array('email' => $email)
        );

    }   
    
    /**
     * @Route("/confirmemail/{token}", name="_account_confirmemail")
     */
    public function confirmEmailAction($token)
    {    
    	$user_manager = $this->get('user_manager');
    	
    	if ( ($user = $user_manager->Confirm($token)) !== null ) {
    		
    		$mailer = $this->get('supla_mailer');
    		$mailer->sendActivationEmailMessage($user);
    		
    		$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'Account has been activated. You can Sign In now.'));
    		
    	} else {
    		$this->get('session')->getFlashBag()->add('error', array('title' => 'Error', 'message' => 'Token does not exist'));
    	}
    	
    	return $this->redirectToRoute("_auth_login");
    
    }
    

    /**
     * @Route("/create", name="_account_create")
     */
    public function createAction(Request $request)
    {
   
    	$form = $this->createForm(new RegistrationType(), new Registration(), array('language'=>$request->getLocale()));    
    
    	$form->handleRequest($request);
    
    	if ($form->isValid()) {
    		
    		$registration = $form->getData();
    		$user_manager = $this->get('user_manager');
    		$user = $registration->getUser();
    		$user_manager->Create($user);
    		
    		$mailer = $this->get('supla_mailer');
    		$mailer->sendConfirmationEmailMessage($user);
    		
    		$this->container->get('session')->set('_registration_email', $user->getEmail());
    		
    		return $this->redirectToRoute("_account_checkemail");
    	}

  
    	return $this->render(
    			'AppBundle:Account:register.html.twig',
    			array('form_ca' => $form->createView(),
    				  'locale' => $request->getLocale()
    			)
    	);
    }
    
   
    
    /**
     * @Route("/view", name="_account_view")
     */
    public function viewAction()
    {
    	$user = $this->get('security.token_storage')->getToken()->getUser();

    	return $this->render('AppBundle:Account:view.html.twig',
    			array('user' => $user
    			)
    	);
    }
    
        
    /**
     * @Route("/reset_passwd/{token}", name="_account_reset_passwd")
     */
    public function resetPasswordAction(Request $request, $token)
    {
    	$user_manager = $this->get('user_manager');
    	 
    	if ( ($user = $user_manager->userByPasswordToken($token)) !== null ) {
    	
    		$form = $this->createForm(new ResetPasswordType(),
    				new ResetPassword());
    		
    		$form->handleRequest($request);
    		 
    		if ($form->isSubmitted() && $form->isValid()) {
    			
    			$user->setToken(null);
    			$user->setPasswordRequestedAt(null);
    			$user_manager->setPassword($form->getData()->getNewPassword(), $user, true);
    			$this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'Password has been changed!'));
    			
    			return $this->redirectToRoute("_auth_login");
    		}
    		
    		return $this->render('AppBundle:Account:resetpassword.html.twig',
    			array('form' => $form->createView(),
    			));
    	
    	} else {
    		$this->get('session')->getFlashBag()->add('error', array('title' => 'Error', 'message' => 'Token does not exist'));
    	}
    	 
    	return $this->redirectToRoute("_auth_login");
    }
    
    /**
     * @Route("/ajax/changepassword", name="_account_ajax_changepassword")
     */
    public function ajaxChangePassword(Request $request)
    {
    	$data = json_decode($request->getContent());
    	$translator = $this->get('translator');
    	$validator = $this->get('validator');

    	$cp = new ChangePassword();
    	$cp->setOldPassword(@$data->old_password);
    	$cp->setNewPassword(@$data->new_password);
    	$cp->setConfirmPassword(@$data->confirm_password);
    	
    	$errors = $validator->validate($cp);
    	
    	if (count($errors) > 0) {
    	
    		$result = array('flash'=> array('title' => $translator->trans('Error'),
    				'message' => $translator->trans($errors[0]->getMessage()),
    				'type' => 'error')
    		);
    		
    		return AjaxController::jsonResponse(false, $result);
    	};
    	

    	$user = $this->get('security.token_storage')->getToken()->getUser();
    	
    	$this->get('user_manager')->setPassword($data->new_password, $user, false);
    	    
        return AjaxController::itemEdit($validator, $translator, $this->get('doctrine'), $user, 'Password has been changed!', '');

    }
    
    /**
     * @Route("/ajax/forgot_passwd", name="_account_ajax_forgot_passwd")
     */
    public function forgotPasswordAction(Request $request)
    {
    	sleep(2); // Delay
    	
    	$translator = $this->get('translator');
    	$user_manager = $this->get('user_manager');
    	
    	$data = json_decode($request->getContent());
    	
    	if ( preg_match('/@/', @$data->email) 
    		 && null !== ( $user = $user = $user_manager->userByEmail($data->email) ) 
    		 &&  $user_manager->paswordRequest($user) === true ) {

    		 $mailer = $this->get('supla_mailer');
    		 $mailer->sendResetPasswordEmailMessage($user);
    	}

    	return AjaxController::jsonResponse(true, null);
    	
    	
    }
}