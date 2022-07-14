<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
class Password extends \Core\Controller
{

	public $e_email = "";
	public $e_password_confirm = "";

	public function forgotAction()
	{
		View::renderTemplate('Password/forgot.html');
	}
	public function requestResetAction()
	{
		User::sendPasswordReset($_POST['email']);
		View::renderTemplate('Password/reset_requested.html');
	}
	//public function validateNewPassword($password)
	public function resetAction()
	{
		$token = $this->route_params['token'];
		$user = $this->getUserOrExit($token);
		$user = User::findByPasswordReset($token);

		if ($user){
			View::renderTemplate('Password/reset.html', [
				'token' => $token
			]);
		}
		else {
			echo "Nie można zresetować hasła.";
		}
	}
	public function resetPasswordAction()
	{
		$token = $_POST['token'];
		
		$user = $this->getUserOrExit($token);

		if ($user->resetPassword($_POST['password'], $_POST['password_confirmation'])) {
			
			View::renderTemplate('Password/reset_success.html');
		} 
			
		else {
			
			View::renderTemplate('Password/reset.html', [
				'token' => $token,
				'user' => $user]);
		}
		
	}
	protected function getUserOrExit($token)
	{
		$user = User::findByPasswordReset($token);
		
		if ($user) {
			
			return $user;
			
		} else {
			
			View::renderTemplate('Password/token_expired.html');
			exit;
		}
	}
}