<?php
// 1. የቴሌግራም ቦት መረጃ
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

// 2. የ Supabase ዳታቤዝ መረጃ
$host = "db.jzolixisaneilbuourna.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "+7DTfkA.7kCPr_K";

// ዳታቤዝ ግንኙነት
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // ጸጥ እንዲል
}

// 3. መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if ($chatId) {
    if ($message == "/start") {
        $response = "እንኳን ደህና መጡ! \n\nቀጠሮ ለመያዝ ስም እና ስልክዎን ይላኩ። \nምሳሌ፦ አበበ ካሳ 0911223344";
        sendMessage($chatId, $response);
    } else {
        // ዳታቤዝ ውስጥ ማስገባት (Table ስሙ 'Appointment' ተብሎ ተስተካክሏል)
        try {
            $stmt = $pdo->prepare("INSERT INTO \"Appointment\" (user_id, full_name) VALUES (?, ?)");
            $stmt->execute([$chatId, $message]);
            
            sendMessage($chatId, "እናመሰግናለን! መረጃዎ ተመዝግቧል።");
        } catch (Exception $e) {
            // ስህተት ካለ ለቦቱ ይናገራል
            sendMessage($chatId, "ይቅርታ፣ መረጃውን መመዝገብ አልቻልኩም፦ " . $e->getMessage());
        }
    }
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
