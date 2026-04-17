<?php
// 1. የቴሌግራም ቦት መረጃ (አዲሱ Token)
$botToken = "8640297748:AAG7yey9RNEO8yX0hGNlTA6VBucwxcViN_U";
$website = "https://api.telegram.org/bot" . $botToken;

// 2. የ Supabase REST API መረጃ
// ማሳሰቢያ፦ ይህንን ANON_KEY ከ Supabase Settings -> API ማግኘት አለብህ
$supabase_url = "https://jzolixisaneilbuourna.supabase.co/rest/v1/appointments";
$supabase_key = "እዚህ_ጋር_የአንተን_SUPABASE_ANON_KEY_ለጥፍ"; 

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if (!$chatId) {
    echo "Bot is running...";
    exit;
}

if ($message == "/start") {
    sendMessage($chatId, "እንኳን ወደ @nkdental_bot በደህና መጡ! \nቀጠሮ ለመያዝ እባክዎ ሙሉ ስምዎን ይላኩ።");
    exit;
}

// 3. መረጃውን በ REST API ወደ Supabase መላክ (Driver አይፈልግም!)
$data = [
    "user_id" => (string)$chatId,
    "full_name" => $message
];

$ch = curl_init($supabase_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabase_key",
    "Authorization: Bearer $supabase_key",
    "Content-Type: application/json",
    "Prefer: return=minimal"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    sendMessage($chatId, "✅ ተሳክቷል! ስምዎ ተመዝግቧል።");
} else {
    // ስህተት ካለ ለደቨሎፐሩ እንዲታይ
    sendMessage($chatId, "❌ ስህተት፦ " . $response);
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
