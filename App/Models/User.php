<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;

/**
 * User model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public $e_name = "";
	public $e_password = "";
	public $e_email = "";
	public $e_password_confirm = "";
    protected $password_hash="";

    /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();

        if ( empty($this->e_name) & empty($this->e_password) & empty($this->e_email) & empty($this->e_password_confirm)) {

             $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $sql = 'INSERT INTO users (username, email, password_hash, activation_hash)
                  VALUES (:username, :email, :password_hash, :activation_hash)';
			 
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            if ($stmt->execute())
            {
                $user = static::findByEmail($this->email);
                self::copyTables($user->id);
                return true; 
            }
            return false; 			
        }

        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
        // Name
        if (strlen($this->username) < 2) {
            $this->e_name = 'Imię musi posiadać od 2 do 20 znaków!';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->e_email = 'Podaj poprawny adres e-mail';
        }
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->e_email = 'Podany adres email istnieje w bazie';
        }

        // Password
        if (isset($this->password)) {
			
			if ($this->password != $this->password_confirmation) {
				$this->e_password_confirm = "Hasła muszą być identyczne";
			}

            if (strlen($this->password) < 8) {
                $this->e_password= 'Hasło musi posiadać conajmniej 8 znaków';
            }

            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->e_password = 'Hasło musi posiadać conajmniej jedną literę';
            }

            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->e_password = 'Hasło musi posiadać conajmniej jedną cyfrę';
            }
        }
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     * @param string $ignore_id Return false anyway if the record found has this ID
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
	/*protected function emailExists($email)
	{
		$sql ='SELECT * FROM users WHERE email = :email';
		
		$db = static::getDB();
		$stmt = $db->prepare($sql);
		$stmt -> bindParam('email', $email, PDO::PARAM_STR);
		
		$stmt->execute();
		
		return $stmt ->fetch() !== false;
	}*/
	
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     //* Authenticate a user by email and password.
     * Authenticate a user by email and password. User account has to be active.
     *
     * @param string $email email address
     * @param string $password password
     *
     * @return mixed  The user object or false if authentication fails
     */
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }
		else return false;
    }

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions to the user specified
     *
     * @param string $email The email address
     *
     * @return void
     */
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user) {

            if ($user->startPasswordReset()) {

                $user->sendPasswordResetEmail();

            }
        }
    }

    /**
     * Start the password reset process by generating a new token and expiry
     *
     * @return void
     */
    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;  // 2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions in an email to the user
     *
     * @return void
     */
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        //$text = "Kliknij link, aby zresetować hasło : $url ";
        //$html = "Kliknij link, aby zresetować hasło <a href=\"url\">tutaj</a>";

        Mail::send($this->email, 'Resetowanie hasła', $text, $html);
    }

    /**
     * Find a user model by password reset token and expiry
     *
     * @param string $token Password reset token sent to user
     *
     * @return mixed User object if found and the token hasn't expired, null otherwise
     */
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {
            
            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {

                return $user;
            }
        }
    }

    /**
     * Reset the password
     *
     * @param string $password The new password
     *
     * @return boolean  True if the password was updated successfully, false otherwise
     */
    public function resetPassword($password, $password_confirmation)
    {
        $this->password = $password;
        $this->password_confirmation = $password_confirmation;

        $this->validate();

        if(empty($this->e_password) & empty($this->e_password_confirm))
        {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);
                                                  
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                                          
            return $stmt->execute();
        }

        return false;
    }

    /**
     * Send an email to the user containing the activation link
     *
     * @return void
     */
    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Aktywacja konta', $text, $html);
    }

    /**
     * Activate the user account with the specified activation token
     *
     * @param string $value Activation token from the URL
     *
     * @return void
     */
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }
    
    public function updateUserData($data)
    {
        $this->name = $data['username'];
        $this->email = $data['email'];

        if ($data['password'] != '' & $data['password_confirmation'] !='' ) {
             $this->password = $data['password'];
             $this->password_confirmation = $data['password_confirmation'];
        }

        $this->validate();

        if (empty($this->e_name) & empty($this->e_password) & empty($this->e_email) & empty($this->e_password_confirm)){

            $sql = 'UPDATE users 
                    SET username = :username, 
                        email = :email';

                    if (isset($this->password)) {
                        $sql .=', password_hash = :password_hash';
                    }
                    $sql .= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            }

            return $stmt->execute();

        }
        return false;
    }
    public function copyTables($id)
    {
        self::copyIncomesCategoryTable($id);
        self::copyExpensesCategoryTable( $id);
        self::copyPaymentMethodsCategoryTable($id);

    }
    private function copyIncomesCategoryTable( $id)
    {
        $sql = "INSERT INTO incomes_category_assigned_to_users(id, name, user_id) SELECT NULL, 
        incomes_category_default.name, $id FROM incomes_category_default";

        self::dbconnection($sql);
    }
    private function copyExpensesCategoryTable( $id)
    {
        $sql = "INSERT INTO expenses_category_assigned_to_users(id, name, user_id) SELECT NULL, expenses_category_default.name, $id FROM expenses_category_default";

        self::dbconnection($sql);
    }

    private function copyPaymentMethodsCategoryTable($id)
    {
        $sql ="INSERT INTO payment_methods_assigned_to_users(id, name, user_id) SELECT NULL, payment_methods_default.name, $id FROM payment_methods_default ";

        self::dbconnection($sql);
    }
    private function dbconnection($sql)
    {
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->execute();
    }

    public static function getIncomesCategory($id)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = "$id"';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->execute();
        
    }
    /*public function addNewIncome()
    {
        $sql = "INSERT INTO incomes VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':income_category_assigned_to_user_id', $this->, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            if ($stmt->execute())
    }*/
}
