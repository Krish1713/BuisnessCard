<?php

    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    require 'config.php';
 

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
 

   $mail = new PHPMailer(true);
   
   $mail->isSMTP();
   $mail->SMTPAuth = true;
   
   $mail->Host = MAILHOST;
   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
   $mail->Port = 587;
   $mail->Username = USERNAME;
   $mail->Password = PASSWORD; 
   $mail->IsHTML(true);
   
   return $mail;

 