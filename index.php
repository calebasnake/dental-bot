<?php
// 1. የቴሌግራም ቦት መረጃ
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

// 2. መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if (!$chatId) {
    echo "Bot is running..."; // ለ Render ማረጋገጫ
    exit;
}

// 3. /start ሲላክ ወዲያውኑ መልስ መስጠት
if ($message == "/start") {
    sendMessage($chatId, "እንኳን ደህና መጡ! ቦቱ አሁን በትክክል እየሰራ ነው። እባክዎ ስምዎን ይላኩ።");
    exit;
}

// 4. ዳታቤዝ ግንኙነት
try {
    $host = "db.jzolixisaneilbuourna.supabase.co";
    $port = "5432";
    $dbname = "postgres";
    $user = "postgres";
    $password = "+7DTfkA.7kCPr_K";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ስሙን ወደ "Appointment" ሰንጠረዥ ማስገባት
$stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name) VALUES (?, ?)");    $stmt->execute([$chatId, $message]);

    sendMessage($chatId, "እናመሰግናለን! መረጃዎ ተመዝግቧል።");

} catch (Exception $e) {
    // ዳታቤዙ ባይሰራም እንኳ ቦቱ ይናገራል
    sendMessage($chatId, "ዳታቤዝ ግንኙነት ላይ ችግር አለ ግን መልዕክትዎ ደርሶኛል! ስህተቱ፦ " . $e->getMessage());
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
