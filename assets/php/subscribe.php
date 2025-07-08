<?php
// Enable error reporting for development (remove or adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ENTER PATH TO FILE
$file_path = $_SERVER["DOCUMENT_ROOT"] . "/";

// ENTER NAME OF FILE 
$file_name = "pickpoint-subscribers.txt";

if($_POST) {
    
    $subscriber_email = $_POST['email'];
    $subscriber_fhp_input = $_POST['phone'];
    $array = array();
    
    // Check honeypot field for spam prevention
    if($subscriber_fhp_input != "") {
        $array["valid"] = 0;
        $array["message"] = "Invalid submission detected.";
        echo json_encode($array);
        exit;
    }
    
    if( $subscriber_email == "" ) {
        
        $array["valid"] = 0;
        $array["message"] = "Please enter your email address.";
        
    } else {

        if( !filter_var($subscriber_email, FILTER_VALIDATE_EMAIL) ) {

            $array["valid"] = 0;
            $array["message"] = "Please enter a valid email address.";

        } else {

            // Check if email already exists
            $full_file_path = $file_path . $file_name;
            $email_exists = false;
            
            if (file_exists($full_file_path)) {
                $existing_emails = file($full_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach($existing_emails as $line) {
                    $parts = explode(" | ", $line);
                    if(isset($parts[0]) && strtolower(trim($parts[0])) == strtolower($subscriber_email)) {
                        $email_exists = true;
                        break;
                    }
                }
            }
            
            if($email_exists) {
                $array["valid"] = 1;
                $array["message"] = "You are already subscribed to Pickpoint updates!";
            } else {
                // Add timestamp and IP for better tracking
                $timestamp = date('Y-m-d H:i:s');
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $subscriber_data = strtolower($subscriber_email) . " | " . $timestamp . " | " . $ip_address . "\r\n";
                
                // Use file locking for concurrent access
                if(file_put_contents($full_file_path, $subscriber_data, FILE_APPEND | LOCK_EX) !== false) {
                    $array["valid"] = 1;
                    $array["message"] = "Thank you for subscribing to Pickpoint updates!";
                } else {
                    $array["valid"] = 0;
                    $array["message"] = "Sorry, there was an error. Please try again.";
                }
            }
        }
    }
    
    echo json_encode($array);
}
?>
