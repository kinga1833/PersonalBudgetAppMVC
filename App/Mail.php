<?php

namespace App;

use App\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mail
 *
 * PHP version 7.0
 */
class Mail
{

    public static function send($to, $subject, $text, $html)
    {

		require(dirname(__DIR__) ."/vendor/autoload.php");
			
				try {
					$Mail = new PHPMailer(true);  
					$Mail->SMTPDebug = 0;
					$Mail->IsSMTP(); 
					$Mail->SMTPAuth = true;
					$Mail->Host = "smtp.gmail.com";
					$Mail->SMTPSecure = "ssl"; 
					$Mail->Port = 465; 
					$Mail->CharSet = 'UTF-8';
					$Mail->Username = 'kinkow31@gmail.com';
					$Mail->Password = 'xxx';
					$Mail->Encoding = '8bit';
					$Mail->ContentType = 'text/html; charset=utf-8\r\n';
					$Mail->From = "kinkow31@gmail.com";
					$Mail->FromName = "mojeFinanse.pl";
					$Mail->AddReplyTo($to);
					$Mail->IsHTML(true); 
					$Mail->AltBody = $text; 
					$Mail->Body = $html;
					$Mail->Subject = $subject;
					$Mail->AddAddress($to);
					$Mail->Send(); 
				} catch (phpmailerException $e) {
					echo $e->errorMessage(); 
				} catch (Exception $e) {
				echo $e->getMessage(); 
				}
 
	}
}

