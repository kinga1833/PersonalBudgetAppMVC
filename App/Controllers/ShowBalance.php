<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Balance;
use \App\Controllers\AddIncome;
use \App\Models\AddExpense;
use \App\Auth;
use \App\Flash;
use \App\Date;

class ShowBalance extends Authenticated
{
    // show balance current month
	public function balancecmAction()
    {
		$currentMonth = new Balance();
		$currentMonth -> getCurrentMonthData();
		
		View::renderTemplate('Balance/balancecm.html', ['balance' => $currentMonth]);
    }
	
	// show balance last month
	public function balancelmAction()
    {
		$lastMonth = new Balance();
		$lastMonth -> getLastMonthData();
		View::renderTemplate('Balance/balancelm.html', ['balance' => $lastMonth]);
    }	
	
	// show balance current year
	public function balancecyAction()
    {
		$currentYear = new Balance();
        $currentYear -> getCurrentYearData();
		View::renderTemplate('Balance/balancecy.html', ['balance' => $currentYear]);
    }	
	
	// show balance custom
	public function balancec()
	{
		View::renderTemplate('Balance/balancec.html');
    }	
	public function balancecustomAction ()
	{
		$customDate = new Balance();
		$customDate->startDate = $_POST['startDate'];
		$customDate->endDate = $_POST['endDate'];
        $customDate -> getCustomPeriodData();

		View::renderTemplate('Balance/balancecustom.html',  ['balance' => $customDate]);
	}
}
	