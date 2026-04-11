<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Log file path
$logFile = __DIR__ . '/email_log.txt';

function writeLog($message)
{
    global $logFile;
    $date = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

function send_paid_email($order)
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
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = function ($str, $level) {
            writeLog("SMTP DEBUG: $str");
        };

        $mail->setFrom($mailAddress, 'ADIDEV MANUFACTURING');
        $mail->addAddress($order['customer_email']);

        $mail->isHTML(true);
        $mail->Subject = "ADIDEV Payment Confirmation";

        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        <title>ADIDEV Payment Confirmation</title>

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
        max-width:900px;
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

        .logo{
        display:block;
        margin:20px auto;
        width:120px;
        }

         /* TABLE */
        table{
        width:100%;
        margin:20px 0;
        border-collapse:collapse;
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
        <h1>ADIDEV Order Confirmation</h1>
        </div>

        <div class="content">
        <p class="greeting">Dear Customer,</p>
        <p>
            We are pleased to inform you that your payment for Order #' . $order['order_number'] . ' has been successfully processed. Thank you for choosing ADIDEV Manufacturing!
        </p>
        <table>
            <tr>
                <td><strong>Order Number:</strong></td>
                <td>' . $order['order_number'] . '</td>
            </tr>
            <tr>
                <td><strong>Amount Paid:</strong></td>
                <td>' . number_format($order['amount_paid'], 2) . '</td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td>' . ucfirst($order['payment_method']) . '</td>
            </tr>
        </table>
        <p>
        Your order is now being prepared for shipment. You will receive a notification with tracking details once your package is on its way.
        </p>
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
            writeLog("MAIL SENT SUCCESSFULLY to " . $order['customer_email']);
        } else {
            writeLog("MAIL FAILED: " . $mail->ErrorInfo);
        }

        writeLog("---- END MAIL PROCESS ----");
    } catch (Exception $e) {
        writeLog("EXCEPTION ERROR: " . $mail->ErrorInfo);
    }
}
