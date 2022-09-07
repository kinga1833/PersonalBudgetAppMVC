<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Auth;
use \App\Flash;

class MenuPage extends Authenticated
{
	
	public function startAction()
    {
        View::renderTemplate('MenuPage/start.html');
    }
	
	// show balance current month
	public function balancecmAction()
    {
		$currentMonthIncomes = new Income();
		$currentMonthIncomes->loadIncomes(date('Y-m-01'), date('Y-m-t'));
		
		View::renderTemplate('MenuPage/balancecm.html');
    }
	
	// show balance last month
	public function balancelmAction()
    {
		View::renderTemplate('MenuPage/balancelm.html');
    }	
	
	// show balance current year
	public function balancecyAction()
    {
		View::renderTemplate('MenuPage/balancecy.html');
    }	
	
	// show balance custom
	public function balancecAction()
	{
		View::renderTemplate('MenuPage/balancec.html');
    }	
	
	public function userdataAction()
    {
		View::renderTemplate('MenuPage/userdata.html', [
			'user' => Auth::getUser()
		]);
    }
	public function incomescategoryAction()
    {
		View::renderTemplate('MenuPage/incomescategory.html' , [
			'user' => Auth::getUser(),
			'incomescategory' => User::getIncomesCategory('user->id')]
		);
    }		
	public function expensecategoryAction()
    {
		View::renderTemplate('MenuPage/expensecategory.html');
    }		
	public function paymentmethodsAction()
    {
		View::renderTemplate('MenuPage/paymentmethods.html');
    }
	public function edituserdataAction()
    {
		View::renderTemplate('MenuPage/edituserdata.html', [
			'user' => Auth::getUser()
		]);
    }
	public function editincomecategoryAction()
    {
		View::renderTemplate('MenuPage/editincomecategory.html');
    }		
	public function editexpensecategoryAction()
    {
		View::renderTemplate('MenuPage/editexpensecategory.html');
    }		
	public function editpaymentmethodsAction()
    {
		View::renderTemplate('MenuPage/editpaymentmethods.html');
    }		
	public function updateUserDataAction()
	{
		$user = Auth::getUser();

		if ($user->updateUserData($_POST)) {

			Flash::addMessage('Dane zostały zmienione');

			$this->redirect('/menuPage/userdata');
		} else {
			View::renderTemplate('menuPage/edituserdata.html', [
				'user' => $user
			]);
		}
	}						
	
}