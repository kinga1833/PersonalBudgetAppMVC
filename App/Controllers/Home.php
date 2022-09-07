<?php

namespace App\Controllers;

use \Core\View;

/**
 * Home controller
 *
 * PHP version 5.4
 */
class Home extends \Core\Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo "(before) ";
        //return false;
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
        //echo " (after)";
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
         //\App\Mail::send('kinkow31@gmail.com', 'blokowanie wysyłki  maili przez gmail', 'https://stackoverflow.com/questions/72470777/nodemailer-response-535-5-7-8-username-and-password-not-accepted', '<h1>https://stackoverflow.com/questions/72470777/nodemailer-response-535-5-7-8-username-and-password-not-accepted</h1>');

         if (!isset($_SESSION['id'])){
            View::renderTemplate('Home/index.html');
        }
        else{
            View::renderTemplate('menuPage/start.html');
        }
    }
}