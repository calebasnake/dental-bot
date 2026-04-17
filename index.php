<?php
// 1. መረጃዎችን ማግኘት
$botToken = getenv('BOT_TOKEN') ?: "8640297748:AAG7yey9RNEO8yX0hGNlTA6VBucwxcViN_U";
$supabase_key = getenv('SUPABASE_KEY') ?: "sb_publishable_8kjcvbAT4woXyVlfJkNDMg_pjsQkNgr";
$supabase_url = "https://jzolixisaneilbuourna.supabase.co/rest/v1/appointments";
$website = "https://api.telegram.org/bot" . $botToken;

// 2. የቴሌግራም መረጃ መቀበል
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

$chatId = $update["message"]["chat"]["id"] ?? $update["callback_query"]["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";
$contact = $update["message"]["contact"] ?? null;

if (!$chatId) exit;

// 3. ቦቱ ምላሽ እንዲሰጥ (Logic)
if ($message == "/start") {
    $keyboard = [
        'keyboard' => [[
            ['text' => "📲 ስልክ ቁጥርን ላክ", 'request_contact' => true]
        ]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ];
    $text = "እንኳን ወደ @nkdental_bot በደህና መጡ! 🦷\n\nቀጠሮ ለመያዝ በመጀመሪያ 'ስልክ ቁጥርን ላክ' የሚለውን በተን ይጫኑ።";
    sendMessage($chatId, $text, $keyboard);
} 
elseif ($contact) {
    $phone = $contact['phone_number'];
    $data = ["user_id" => (string)$chatId, "phone_number" => $phone];
    postToSupabase($supabase_url, $supabase_key, $data);
    sendMessage($chatId, "በጣም ጥሩ! አሁን ደግሞ ሙሉ ስምዎን ይላኩ።");
} 
elseif (!empty($message)) {
    // ስም መቀበል
    $check_url = $supabase_url . "?user_id=eq." . $chatId . "&order=created_at.desc&limit=1";
    $user_data = getFromSupabase($check_url, $supabase_key);

    if (!empty($user_data) && empty($user_data[0]['full_name'])) {
        $row_id = $user_data[0]['id'];
        $update_url = $supabase_url . "?id=eq." . $row_id;
        patchSupabase($update_url, $supabase_key, ["full_name" => $message]);
        sendMessage($chatId, "✅ ተሳክቷል! ስምዎ እና ስልክዎ ተመዝግቧል።");
    }
}

// --- Functions ---
function sendMessage($chatId, $text, $keyboard = null) {
    global $website;
    $postData = ['chat_id' => $chatId, 'text' => $text];
    if ($keyboard) $postData['reply_markup'] = json_encode($keyboard);
    $ch = curl_init($website . "/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_exec($ch);
    curl_close($ch);
}

function postToSupabase($url, $key, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $key", "Authorization: Bearer $key", "Content-Type: application/json"]);
    curl_exec($ch);
    curl_close($ch);
}

function getFromSupabase($url, $key) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $key", "Authorization: Bearer $key"]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function patchSupabase($url, $key, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $key", "Authorization: Bearer $key", "Content-Type: application/json"]);
    curl_exec($ch);
    curl_close($ch);
}
?>
