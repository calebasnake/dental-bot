<?php
$botToken = "8640297748:AAG7yey9RNEO8yX0hGNlTA6VBucwxcViN_U";
$website = "https://api.telegram.org/bot" . $botToken;

$supabase_url = "https://jzolixisaneilbuourna.supabase.co/rest/v1/appointments";
$supabase_key = "sb_publishable_8kjcvbAT4woXyVlfJkNDMg_pjsQkNgr"; 

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if (!$chatId) exit;

if ($message == "/start") {
    sendMessage($chatId, "እንኳን ወደ @nkdental_bot በደህና መጡ! \n\nቀጠሮ ለመያዝ በመጀመሪያ **ሙሉ ስምዎን** ይላኩ።");
    exit;
}

// 1. መጀመሪያ ተጠቃሚው ቀድሞ መመዝገቡን እናያለን (ስም ኖሮት ስልክ ቁጥር የሌለው መሆኑን)
$check_url = $supabase_url . "?user_id=eq." . $chatId . "&select=*&order=created_at.desc&limit=1";
$ch = curl_init($check_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $supabase_key",
    "Authorization: Bearer $supabase_key"
]);
$user_data = json_decode(curl_exec($ch), true);
curl_close($ch);

// 2. ተጠቃሚው ገና አዲስ ከሆነ ስሙን እንመዘግባለን
if (empty($user_data) || !empty($user_data[0]['phone_number'])) {
    $data = ["user_id" => (string)$chatId, "full_name" => $message];
    postToSupabase($supabase_url, $supabase_key, $data);
    sendMessage($chatId, "ተቀብያለሁ! አሁን ደግሞ **የስልክ ቁጥርዎን** ይላኩ።");
} 
// 3. ስሙ ካለ ግን ስልክ ቁጥር ከሌለው፣ የላከው ጽሁፍ ስልክ ነው ብለን እናስባለን
else {
    $row_id = $user_data[0]['id'];
    $update_url = $supabase_url . "?id=eq." . $row_id;
    $data = ["phone_number" => $message];
    
    $ch = curl_init($update_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); // መረጃን ለማደስ PATCH እንጠቀማለን
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json",
        "Prefer: return=minimal"
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    sendMessage($chatId, "✅ እናመሰግናለን! ስምዎ እና ስልክዎ ተመዝግቧል። በቅርቡ ቀጠሮ ለመያዝ እንደውልልዎታለን።");
}

function postToSupabase($url, $key, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Content-Type: application/json"
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
