<?php

namespace App\Models;

use PDO;
use \Core\View;

/**
 * User model
 *
 * PHP version 7.0
 */
class Income extends \Core\Model
{
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
    public function save()
    {
        $incomeCategoryAssignedToUserId = static::findCategoryAssignedToUserId($this->category);
        $user_id = $_SESSION['id'];

        $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment) VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id , PDO::PARAM_INT);
        $stmt->bindValue(':income_category_assigned_to_user_id', $incomeCategoryAssignedToUserId->id, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $this->amount, PDO::PARAM_INT);
        $stmt->bindValue(':date_of_income', $this->date, PDO::PARAM_STR);
        $stmt->bindValue(':income_comment', $this->comment, PDO::PARAM_STR);
        
        return $stmt->execute();

    }
    public static function findCategoryAssignedToUserId($categoryName){

        $user_id = $_SESSION['id'];
        
        $sql = "SELECT * FROM incomes_category_assigned_to_users WHERE incomes_category_assigned_to_users.user_id = :user_id  AND incomes_category_assigned_to_users.name = :categoryName";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }
    public function loadIncomes ($beginOfPeriod, $endOfPeriod)
    {
        $user_id = $_SESSION['id'];

        $sql = "SELECT i.date_of_income, ic.name, i.income_comment, SUM(i.amount) FROM incomes AS i, incomes_category_assigned_to_users AS ic WHERE i.user_id= :user_id AND ic.user_id= :user_id AND ic.id=i.income_category_assigned_to_user_id AND i.date_of_income BETWEEN :beginOfPeriod AND :endOfPeriod GROUP BY income_category_assigned_to_user_id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':beginOfPeriod', $beginOfPeriod, PDO::PARAM_STR);
        $stmt->bindValue(':endOfPeriod', $endOfPeriod, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();

    }
}