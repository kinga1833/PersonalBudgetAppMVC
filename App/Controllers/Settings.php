<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;

class Settings extends Authenticated
{
    public function userdataAction()
    {
        View::renderTemplate('Settings/userdata.html');
    }
    public function incomescategoryAction()
    {
        View::renderTemplate('Settings/incomescategory.html');
    }
    public function expensescategoryAction()
    {
        View::renderTemplate('Settings/expensescategory.html');
    }
    public function paymentmethodsAction()
    {
        View::renderTemplate('Settings/paymentmethods.html');
    }
    
}