<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Income;
use \App\Auth;
use \App\Flash;

class AddIncome extends Authenticated
{
    public function incomeAction()
    {
		View::renderTemplate('AddIncome/income.html');
    }
	public function incomeCreateAction()
	{
		$income = new Income($_POST);

		if($income->save())
		{
			Flash::addMessage('Przychód został pomyślnie dodany');
			$this->redirect('/addIncome/income');
		} else {
			Flash::addMessage('Nie udało się dodać przychodu, spróbuj ponownie', 'warning');
			$this->redirect('/addIncome/income');
		}
	}
}