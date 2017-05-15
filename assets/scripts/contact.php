<?php

require(__DIR__.'/../../vendor/autoload.php');

// instantiate mailer object
$mail = new PHPMailer();

// use gmail account to send mail

// telling the class to use SMTP
//http://stackoverflow.com/questions/5440026/php-on-godaddy-linux-shared-trying-to-send-through-gmail-smtp
//http://stackoverflow.com/questions/9253739/php-detect-if-document-index-is-localhost
if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
  $mail->IsSMTP(); 
}

// enables SMTP debug information (1 = errors and messages; 2= messages only)
$mail->SMTPDebug = 2;          
// enable SMTP authentication
$mail->SMTPAuth = true;
// sets the prefix to the server
$mail->SMTPSecure = 'tls';     
// sets gmail as the SMTP server      
$mail->Host = 'smtp.gmail.com';
// set the SMTP port for the gmail server
$mail->Port = 587;
// gmail username
$mail->Username = 'voynichapi@gmail.com';
// gmail password
$mail->Password = 'q0keedyda11n';

// get message data 
try {
   
  $nameFrom = $_POST['name'];
  $emailFrom = $_POST['email'];
  $message = $_POST['message'];
      
  // set who e-mail is from (ie site) and message
  $mail->SetFrom('voynichapi@gmail.com', 'Voynich API');
  $mail->Subject = 'A message from the Voynich API site';
  
  // compose body
  // set body of message
  $body = 'Name: '.$nameFrom.'<br>Email: '.$emailFrom.'<br>Message: '.$message.'<br>';     
  $mail->MsgHTML($body);
  
  // add reply-to
  $mail->AddReplyTo($emailFrom);
  
  // send to me
  $mail->AddAddress('voynichapi@gmail.com', 'Voynich API');

   // do send
  if(!$mail->Send()) {
    throw new Exception($mail->ErrorInfo);
    
  } else {
    echo 'Message sent!';

  }
  
} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' '.'Internal Server Error', true, 500);
  echo $e->getMessage();
  
}


    