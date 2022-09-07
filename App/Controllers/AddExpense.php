<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Expense;
use \App\Auth;
use \App\Flash;

class AddExpense extends Authenticated
{
    public function expenseAction()
        {
            View::renderTemplate('AddExpense/expense.html');
        }
        public function expenseCreateAction()
        {
            $expense = new Expense($_POST);

            if($expense->save())
            {
                Flash::addMessage('Wydatek został pomyślnie dodany');
                $this->redirect('/addExpense/expense');
            } else {
                Flash::addMessage('Nie udało się dodać wydatku, spróbuj ponownie', 'warning');
                $this->redirect('/addExpense/expense');
            }
        }
}
	