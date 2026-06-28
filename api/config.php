<?php
// api/config.php
// Update these details with your MilesWeb database credentials

$db_host = 'localhost';
$db_user = 'dealsfor2_sscrackers'; 
$db_pass = 'DBSScrackers@2026'; 
$db_name = 'dealsfor2_SSCrackers_DB'; 

// ================================================
// OWNER NOTIFICATION SETTINGS
// ================================================

// Email: Owner's email to receive order notifications
define('OWNER_EMAIL',      'it.dealsforloan@gmail.com');    // ← Owner's real email
define('OWNER_EMAIL_NAME', 'SS Crackers Owner');

// Store info shown in emails & WhatsApp
define('STORE_NAME',       'SS Crackers');
define('STORE_PHONE',      '+91 95855 58912');          // ← Contact phone number
define('STORE_URL',        'https://sscrackers.in');
define('ADMIN_URL',        'https://sscrackers.in/admin/');

// =====================================================
// WHATSAPP via META BUSINESS CLOUD API
// (Official Meta API — No QR Scanning, Free 1000 msgs/month)
//
// HOW TO SETUP (Step-by-step in the guide):
//  1. Go to https://developers.facebook.com/ and create an App.
//  2. Add "WhatsApp" product to your app.
//  3. Go to WhatsApp → API Setup in the left sidebar.
//  4. Copy the "Temporary Access Token" (or generate a permanent one).
//  5. Copy the "Phone Number ID" (NOT the phone number itself).
//  6. Add your owner WhatsApp number as a Test Recipient.
//  7. Paste the values below and set META_WA_ENABLED to true.
// =====================================================
define('META_WA_ACCESS_TOKEN',   'paste_your_access_token_here');   // ← From Meta Developer Console
define('META_WA_PHONE_NUMBER_ID','paste_your_phone_number_id_here'); // ← Phone Number ID (not the phone number)
define('META_WA_TARGET_PHONE',   '919585558912');                     // ← Owner's WhatsApp (international, no +)
define('META_WA_ENABLED',        false);                              // ← Set true after pasting credentials above

// ================================================
// DATABASE CONNECTION
// ================================================
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
    }
} catch (mysqli_sql_exception $e) {
    die("<h2>Database Connection Error</h2><p style='color:red;'>Access Denied. Please check your database credentials in <b>api/config.php</b>.</p><p>Error: " . $e->getMessage() . "</p>");
}

$conn->set_charset("utf8mb4");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
