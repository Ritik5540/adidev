<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Log file path
$logFile = __DIR__ . '/email_log.txt';

function writeLog($message) {
    global $logFile;
    $date = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

function sendOTPEmail($recipientEmail, $name, $otp)
{   
    writeLog("---- START MAIL PROCESS ----");
    $mailAddress = 'support@adidevmanufacturing.com';
    $mailPassword = 'adidev@0Rs';
    $mail = new PHPMailer(true);
    try {

        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = 'smtp.hostinger.in';
        $mail->SMTPAuth = true;
        $mail->Username = $mailAddress;
        $mail->Password = $mailPassword;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Debug (optional but useful)
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            writeLog("SMTP DEBUG: $str");
        };

        $mail->setFrom($mailAddress, 'ADIDEV LOGIN');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = "ADIDEV LOGIN OTP";

        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        <title>ADIDEV Login OTP</title>

        <style>
        body{
        margin:0;
        padding:0;
        background:#f4f7f6;
        font-family:Arial, Helvetica, sans-serif;
        }

        .wrapper{
        width:100%;
        padding:30px 15px;
        }

        .container{
        max-width:600px;
        margin:auto;
        background:#ffffff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 10px 25px rgba(0,0,0,0.08);
        }

        /* HEADER */
        .header{
        background: linear-gradient(135deg,#1565c0,#0a0a2a);
        color:#ffffff;
        text-align:center;
        padding:30px 20px;
        position:relative;
        }

        .header h1{
        margin:0;
        font-size:22px;
        letter-spacing:0.5px;
        }

        /* CONTENT */
        .content{
        padding:30px 25px;
        color:#333;
        font-size:15px;
        line-height:1.6;
        }

        .greeting{
        font-size:16px;
        margin-bottom:10px;
        }

        /* OTP BOX */
        .otp-box{
        text-align:center;
        margin:30px 0;
        }

        .otp{
        display:inline-block;
        padding:16px 32px;
        font-size:32px;
        font-weight:bold;
        letter-spacing:6px;
        background:#0a0a2a;
        color:#ff6b35;
        border:2px dashed #ff6b35;
        border-radius:10px;
        }

        /* NOTE */
        .note{
        background:#fff3f8;
        padding:14px 16px;
        border-radius:6px;
        font-size:13px;
        color:#a9446a;
        margin-top:15px;
        border-left:4px solid #e91e8c;
        }

        /* BUTTON */
        .btn{
        display:inline-block;
        margin-top:20px;
        padding:12px 25px;
        background:#1565c0;
        color:#ffffff !important;
        text-decoration:none;
        border-radius:6px;
        font-weight:bold;
        }

        .btn:hover{
        background:#ff6b35;
        }

        /* FOOTER */
        .footer{
        text-align:center;
        font-size:12px;
        color:#888;
        padding:20px;
        background:#f9f9f9;
        }

        .footer a{
        color:#1565c0;
        text-decoration:none;
        font-weight:bold;
        }

        /* subtle glow */
        .header::after{
        content:"";
        position:absolute;
        width:200px;
        height:200px;
        background:radial-gradient(circle,#e91e8c33,transparent);
        top:-50px;
        right:-50px;
        }
        </style>
        </head>

        <body>

        <div class="wrapper">
        <div class="container">

        <div class="header">
        <h1>ADIDEV Secure Login</h1>
        </div>

        <div class="content">

        <p class="greeting">Hello ' . htmlspecialchars($name) . ',</p>

        <p>
        We received a request to log in to your ADIDEV account.  
        Use the One-Time Password (OTP) below to continue:
        </p>

        <div class="otp-box">
        <div class="otp">' . htmlspecialchars($otp) . '</div>
        </div>

        <p>
        This OTP is valid for <strong>5 minutes</strong>.  
        For your security, do not share this code with anyone.
        </p>

        <div class="note">
        If you didn’t try to log in, you can safely ignore this email.  
        Your account remains secure.
        </div>

        <p style="margin-top:25px;">
        Thanks,<br>
        <strong>ADIDEV Security Team</strong>
        </p>

        </div>

        <div class="footer">
        © 2026 ADIDEV Manufacturing<br>
        This is an automated message, please do not reply.
        </div>

        </div>
        </div>

        </body>
        </html>';
        if ($mail->send()) {
            writeLog("MAIL SENT SUCCESSFULLY to $recipientEmail");
        } else {
            writeLog("MAIL FAILED: " . $mail->ErrorInfo);
        }

        writeLog("---- END MAIL PROCESS ----");

    } catch (Exception $e) {
        writeLog("EXCEPTION ERROR: " . $mail->ErrorInfo);
    }
}
