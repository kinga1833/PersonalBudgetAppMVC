<?php

namespace App\Models;

use PDO;
use \App\Date;

class Balance extends \Core\Model
{
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
    public function getCurrentMonthData()
    {
        $this -> startDate = Date::getCurrentMonthStartDate();
        $this -> endDate = Date::getCurrentMonthEndDate();

        $this -> getBalanceData();
    }
    public function getLastMonthData()
    {
        $this -> startDate = Date::getLastMonthStartDate();
        $this -> endDate = Date::getLastMonthEndDate();
        
        $this -> getBalanceData();
    }
    public function getCurrentYearData()
    {
        $this -> startDate = Date::getCurrentYearStartDate();
        $this -> endDate = Date::getCurrentYearEndDate();
        
        $this -> getBalanceData();
    }
    
    public function getCustomPeriodData()
    {
        if($this -> startDate > $this -> endDate) {
            
            $endDate = $this -> startDate;
            $this -> startDate = $this -> endDate;
            $this -> endDate = $endDate;
        }
        
        $this -> getBalanceData();
    }
    protected function getBalanceData()
    {
        $this -> getGroupedIncomes();
        $this -> getAllIncomes();
        $this -> countTotalIncome();

        $this -> getGroupedExpenses();
        $this -> getAllExpenses();
        $this -> countTotalExpense();  
    }
    protected function getGroupedIncomes()
    {
        $db = static::getDB();
        
        $sql = 'SELECT i.income_category_assigned_to_user_id, ic.name, SUM(i.amount) AS amount
                FROM incomes i INNER JOIN incomes_category_assigned_to_users ic ON i.income_category_assigned_to_user_id = ic.id
                WHERE i.user_id=:loggedUserId AND i.date_of_income BETWEEN :startDate AND :endDate
                GROUP BY i.income_category_assigned_to_user_id
                ORDER BY amount DESC';
        
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':loggedUserId', $_SESSION['id'], PDO::PARAM_INT);
        $stmt -> bindValue(':startDate', $this -> startDate, PDO::PARAM_STR);
        $stmt -> bindValue(':endDate', $this -> endDate, PDO::PARAM_STR);
        $stmt -> execute();
        
        $this -> groupedIncomes = $stmt -> fetchAll();
    }

    protected function getAllIncomes()
    {
        $db = static::getDB();
        
        $sql = "SELECT income_category_assigned_to_user_id, date_of_income, income_comment, amount FROM incomes WHERE user_id = :loggeduser_id  AND date_of_income BETWEEN :startDate AND :endDate ORDER BY date_of_income ASC, id";
        
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':loggeduser_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt -> bindValue(':startDate', $this -> startDate, PDO::PARAM_STR);
        $stmt -> bindValue(':endDate', $this -> endDate, PDO::PARAM_STR);
        $stmt -> execute();
        
        $this -> detailedIncomes = $stmt -> fetchAll();
    }
    protected function countTotalIncome()
    {
        $this -> totalIncome = 0;
        
        if(!empty($this -> groupedIncomes)) {
            
            foreach ($this -> groupedIncomes as $income) {
                
                $this -> totalIncome += $income['amount'];
            }
        }
    }
    protected function getGroupedExpenses()
    {
        $db = static::getDB();
        
        $sql = 'SELECT e.expense_category_assigned_to_user_id, ec.name, SUM(e.amount) AS amount
                FROM expenses e INNER JOIN expenses_category_assigned_to_users ec ON e.expense_category_assigned_to_user_id = ec.id
                WHERE e.user_id=:loggedUserId AND e.date_of_expense BETWEEN :startDate AND :endDate
                GROUP BY e.expense_category_assigned_to_user_id
                ORDER BY amount DESC';
        
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':loggedUserId', $_SESSION['id'], PDO::PARAM_INT);
        $stmt -> bindValue(':startDate', $this -> startDate, PDO::PARAM_STR);
        $stmt -> bindValue(':endDate', $this -> endDate, PDO::PARAM_STR);
        $stmt -> execute();
        
        $this -> groupedExpenses = $stmt -> fetchAll();
    }

    protected function getAllExpenses()
    {
        $db = static::getDB();
        
        $sql = "SELECT e.expense_category_assigned_to_user_id, date_of_expense, expense_comment, amount, pm.name FROM expenses e INNER JOIN payment_methods_assigned_to_users pm ON e.payment_method_assigned_to_user_id = pm.id WHERE e.user_id =:loggeduser_id  AND date_of_expense BETWEEN :startDate AND :endDate ORDER BY date_of_expense ASC, e.id";
        
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':loggeduser_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt -> bindValue(':startDate', $this -> startDate, PDO::PARAM_STR);
        $stmt -> bindValue(':endDate', $this -> endDate, PDO::PARAM_STR);
        $stmt -> execute();
        
        $this -> detailedExpenses = $stmt -> fetchAll();
    }
    protected function countTotalExpense()
    {
        $this -> totalExpense = 0;
        
        if(!empty($this -> groupedExpenses)) {
            
            foreach ($this -> groupedExpenses as $expense) {
                
                $this -> totalExpense += $expense['amount'];
            }
        }
    }

}