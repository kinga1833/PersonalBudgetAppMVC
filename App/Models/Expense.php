<?php

namespace App\Models;

use PDO;
use \Core\View;

/**
 * User model
 *
 * PHP version 7.0
 */
class Expense extends \Core\Model
{
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
    public function save()
    {
        $expenseCategoryAssignedToUser = static::findExpenseCategoryAssignedToUser($this->expensecategory);
        $paymentMethodAssignedToUser = static::findPaymentMethodCategoryAssignedToUser($this->paymentmethod);
        $user_id = $_SESSION['id'];

        $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment) VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id , PDO::PARAM_INT);
        $stmt->bindValue(':expense_category_assigned_to_user_id', $expenseCategoryAssignedToUser->id, PDO::PARAM_INT);
        $stmt->bindValue(':payment_method_assigned_to_user_id', $paymentMethodAssignedToUser->id, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $this->amount, PDO::PARAM_INT);
        $stmt->bindValue(':date_of_expense', $this->date, PDO::PARAM_STR);
        $stmt->bindValue(':expense_comment', $this->comment, PDO::PARAM_STR);
        
        return $stmt->execute();

    }
    public static function findExpenseCategoryAssignedToUser($categoryName){

        $user_id = $_SESSION['id'];
        
        $sql = "SELECT * FROM expenses_category_assigned_to_users WHERE expenses_category_assigned_to_users.user_id = :user_id  AND expenses_category_assigned_to_users.name = :categoryName";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }
    public static function findPaymentMethodCategoryAssignedToUser($paymentMethodName){

        $user_id = $_SESSION['id'];
        
        $sql = "SELECT * FROM payment_methods_assigned_to_users WHERE payment_methods_assigned_to_users.user_id = :user_id  AND payment_methods_assigned_to_users.name = :paymentMethodName";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':paymentMethodName', $paymentMethodName, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }
}