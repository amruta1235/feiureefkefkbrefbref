<?php
// Start or resume the session
session_start();

// Function to send OTP via Twilio
function sendOTP($mobile_number) {
    // Replace 'YOUR_TWILIO_ACCOUNT_SID', 'YOUR_TWILIO_AUTH_TOKEN', and 'YOUR_TWILIO_PHONE_NUMBER' with your actual Twilio credentials
    $account_sid = 'YOUR_TWILIO_ACCOUNT_SID';
    $auth_token = 'YOUR_TWILIO_AUTH_TOKEN';
    $twilio_number = 'YOUR_TWILIO_PHONE_NUMBER';

    // Generate OTP
    $otp = mt_rand(100000, 999999);

    // Save the OTP in session for verification later
    $_SESSION['otp'] = $otp;

    // Initialize Twilio client
    $client = new Twilio\Rest\Client($account_sid, $auth_token);

    // Send OTP via Twilio
    $message = $client->messages->create(
        $mobile_number,
        array(
            'from' => $twilio_number,
            'body' => "Your OTP is: $otp"
        )
    );

    // Check if the message was sent successfully
    if ($message) {
        return true;
    } else {
        return false;
    }
}

// Function to send email with attachment
function sendEmailWithAttachment($to, $subject, $message, $attachment) {
    // Replace 'your_email@example.com' with your email address
    $from_email = 'your_email@example.com';

    // Create a boundary for the email
    $boundary = md5(time());

    // Construct email headers
    $headers = "From: $from_email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Construct email body
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "\r\n";
    $body .= chunk_split(base64_encode($message)) . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: application/octet-stream; name=\"$attachment\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$attachment\"\r\n";
    $body .= "\r\n";
    $body .= chunk_split(base64_encode(file_get_contents($attachment))) . "\r\n";
    $body .= "--$boundary--";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        return true;
    } else {
        return false;
    }
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle OTP generation and Twilio integration
    if (isset($_POST['Mobile'])) {
        $mobile_number = $_POST['Mobile'];
        $otp_sent = sendOTP($mobile_number);
        if ($otp_sent) {
            echo json_encode(array('success' => true, 'message' => 'OTP sent successfully!'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to send OTP. Please try again later.'));
        }
    }

    // Handle email sending
    if (isset($_POST['email'])) {
        $to = $_POST['email'];
        $subject = 'Receipt';
        $message = 'Please find your receipt attached.';
        $attachment = 'receipt.pdf'; // Assuming this is the name of your PDF attachment
        $email_sent = sendEmailWithAttachment($to, $subject, $message, $attachment);
        if ($email_sent) {
            echo json_encode(array('success' => true, 'message' => 'Email sent successfully!'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to send email. Please try again later.'));
        }
    }
}
?>
