<?php
// 1. መረጃዎችን ማግኘት
$botToken = getenv('BOT_TOKEN') ?: "8640297748:AAG7yey9RNEO8yX0hGNlTA6VBucwxcViN_U";
$supabase_key = getenv('SUPABASE_KEY') ?: "sb_publishable_8kjcvbAT4woXyVlfJkNDMg_pjsQkNgr";
$supabase_url = "https://jzolixisaneilbuourna.supabase.co/rest/v1/appointments";
$website = "https://api.telegram.org/bot" . $botToken;

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";
$contact = $update["message"]["contact"] ?? null;

if (!$chatId) exit;

// 2. /start - ስልክ ቁጥር መጠየቂያ
if ($message == "/start") {
    $keyboard = [
        'keyboard' => [[
            ['text' => "📲 ስልክ ቁጥርን ላክ", 'request_contact' => true]
        ]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ];
    sendMessage($chatId, "እንኳን ደህና መጡ! 🦷\nቀጠሮ ለመያዝ በመጀመሪያ 'ስልክ ቁጥርን ላክ' የሚለውን በተን ይጫኑ።", $keyboard);
} 

// 3. ስልክ ቁጥር ሲላክ - Supabase ላይ መመዝገብ
elseif ($contact) {
    $phone = $contact['phone_number'];
    $data = ["user_id" => (string)$chatId, "phone_number" => $phone];
    
    // ወደ Supabase መላክ (POST)
    $ch = curl_init($supabase_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json"
    ]);
    curl_exec($ch);
    curl_close($ch);

    sendMessage($chatId, "ተቀብያለሁ! አሁን ደግሞ **ሙሉ ስምዎን** ይላኩ።");
} 

// 4. ስም ሲላክ - ዳታቤዙን ማደስ
elseif (!empty($message)) {
    // የመጨረሻውን ሪከርድ መፈለግ
    $check_url = $supabase_url . "?user_id=eq." . $chatId . "&order=created_at.desc&limit=1";
    $ch = curl_init($check_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $supabase_key", "Authorization: Bearer $supabase_key"]);
    $res = curl_exec($ch);
    curl_close($ch);
    $user_data = json_decode($res, true);

    if (!empty($user_data) && empty($user_data[0]['full_name'])) {
        $row_id = $user_data[0]['id'];
        $update_url = $supabase_url . "?id=eq." . $row_id;
        
        $ch = curl_init($update_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["full_name" => $message]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key", 
            "Authorization: Bearer $supabase_key", 
            "Content-Type: application/json"
        ]);
        curl_exec($ch);
        curl_close($ch);
        
        sendMessage($chatId, "✅ ተሳክቷል! ስምዎ እና ስልክዎ ተመዝግቧል። በቅርቡ እንደውልልዎታለን።");
    } else {
        sendMessage($chatId, "አዲስ ቀጠሮ ለመጀመር እባክዎ /start ይበሉ።");
    }
}

function sendMessage($chatId, $text, $keyboard = null) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    if ($keyboard) {
        $url .= "&reply_markup=" . urlencode(json_encode($keyboard));
    }
    file_get_contents($url);
}
?>
