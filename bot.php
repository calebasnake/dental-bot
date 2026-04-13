<?php
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

// 1. መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if (!$chatId) exit;

// 2. ለ /start መልስ መስጠት (ይህ መጀመሪያ መስራት አለበት)
if ($message == "/start") {
    sendMessage($chatId, "እንኳን ደህና መጡ! ቦቱ አሁን እየሰራ ነው። ስምዎን እና ስልክዎን ይላኩ።");
    exit;
}

// 3. ዳታቤዝ ግንኙነት እና መረጃ ማስገባት
try {
    $host = "db.jzolixisaneilbuourna.supabase.co";
    $port = "5432";
    $dbname = "postgres";
    $user = "postgres";
    $password = "+7DTfkA.7kCPr_K";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Table ስሙ በፎቶህ መሰረት "Appointment" ነው
    $stmt = $pdo->prepare("INSERT INTO \"Appointment\" (user_id, full_name) VALUES (?, ?)");
    $stmt->execute([$chatId, $message]);

    sendMessage($chatId, "እናመሰግናለን! መረጃዎ በዳታቤዝ ውስጥ ገብቷል።");

} catch (Exception $e) {
    // ስህተት ካለ ዝም አይልም፣ ይናገራል
    sendMessage($chatId, "የዳታቤዝ ስህተት አጋጥሟል፦ " . $e->getMessage());
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
