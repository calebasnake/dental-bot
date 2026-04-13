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
    // ግንኙነት ካልተሳካ እዚህ ጋር ሎግ ማድረግ ይቻላል
}

// 3. መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if ($chatId) {
    if ($message == "/start") {
        $response = "እንኳን ወደ ጥርስ ህክምና ክሊኒካችን በደህና መጡ! \n\nቀጠሮ ለመያዝ ስምዎን እና ስልክዎን እንዲህ አድርገው ይላኩ፦ \n\nአበበ ካሳ 0911223344";
        sendMessage($chatId, $response);
    } else {
        // ስም እና ስልክ መለየት
        $parts = explode(" ", trim($message));
        if (count($parts) >= 2) {
            $phone = end($parts);
            $fullName = trim(str_replace($phone, "", $message));

            try {
                $stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name, phone_number) VALUES (?, ?, ?)");
                $stmt->execute([$chatId, $fullName, $phone]);
                
                sendMessage($chatId, "እናመሰግናለን $fullName! መረጃዎ ተመዝግቧል።");
            } catch (Exception $e) {
                sendMessage($chatId, "ይቅርታ፣ መረጃውን መመዝገብ አልቻልኩም።");
            }
        } else {
            sendMessage($chatId, "እባክዎ ስም እና ስልክዎን በትክክል ያስገቡ (ለምሳሌ፦ አበበ ካሳ 0911223344)");
        }
    }
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
