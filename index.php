<?php
// 1. መረጃዎችን ከ Render Environment Variables ማግኘት
$botToken = getenv('BOT_TOKEN');
$supabase_key = getenv('SUPABASE_KEY');
$supabase_url = "https://jzolixisaneilbuourna.supabase.co/rest/v1/appointments";
$website = "https://api.telegram.org/bot" . $botToken;

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

// የቻት መለያ ቁጥር ማግኘት
$chatId = $update["message"]["chat"]["id"] ?? $update["callback_query"]["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";
$contact = $update["message"]["contact"] ?? null;

if (!$chatId) exit;

// --- የደህንነት ማረጋገጫ (Debug Mode) ---
if (!$botToken || !$supabase_key) {
    $missing = !$botToken ? "BOT_TOKEN" : "SUPABASE_KEY";
    sendMessage($chatId, "⚠️ ስህተት፡ Render ላይ '$missing' አልተገኘም! እባክዎ Settings -> Environment Variables ገጽ ላይ በትክክል መሙላትዎን ያረጋግጡ።");
    exit;
}

// 2. /start ሲባል በ Buttons ሰላምታ መስጠት
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
    exit;
}

// 3. ስልክ ቁጥር ሲላክ መቀበል
if ($contact) {
    $phone = $contact['phone_number'];
    
    $data = [
        "user_id" => (string)$chatId,
        "phone_number" => $phone
    ];
    
    if (postToSupabase($supabase_url, $supabase_key, $data)) {
        sendMessage($chatId, "በጣም ጥሩ! አሁን ደግሞ ሙሉ ስምዎን ይላኩ።");
    } else {
        sendMessage($chatId, "⚠️ ዳታቤዝ ላይ መመዝገብ አልተቻለም። እባክዎ ትንሽ ቆይተው ይሞክሩ።");
    }
    exit;
}

// 4. ስም ሲላክ ዳታቤዙን ማደስ (Update)
if (!empty($message) && $message != "/start") {
    // የመጨረሻውን የዚህን ሰው ሪከርድ መፈለግ (ስልክ ኖሮት ስም የሌለውን)
    $check_url = $supabase_url . "?user_id=eq." . $chatId . "&order=created_at.desc&limit=1";
    $user_data = getFromSupabase($check_url, $supabase_key);

    if (!empty($user_data) && empty($user_data[0]['full_name'])) {
        $row_id = $user_data[0]['id'];
        $update_url = $supabase_url . "?id=eq." . $row_id;
        
        $update_data = ["full_name" => $message];
        patchSupabase($update_url, $supabase_key, $update_data);
        
        sendMessage($chatId, "✅ ተሳክቷል! ስምዎ እና ስልክዎ ተመዝግቧል። በቅርቡ ቀጠሮ ለመያዝ እንደውልልዎታለን።");
    }
}

// --- ረዳት ፈንክሽኖች ---

function sendMessage($chatId, $text, $keyboard = null) {
    global $website;
    $postData = ['chat_id' => $chatId, 'text' => $text];
    if ($keyboard) $postData['reply_markup'] = json_encode($keyboard);
    
    $ch = curl_init($website . "/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_exec($ch);
    curl_close($ch);
}

function postToSupabase($url, $key, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $key", "Authorization: Bearer $key", "Content-Type: application/json"]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300);
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
