<?php
// የቴሌግራም ቦት መረጃ
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

// መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

// ማን እንደላከ እና ምን እንደተላከ መለየት
$chatId = $update["message"]["chat"]["id"] ?? $update["callback_query"]["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if ($chatId) {
    if ($message == "/start") {
        $response = "እንኳን ወደ ጥርስ ህክምና ክሊኒካችን በደህና መጡ! \n\nቦቱ አሁን በ Render ላይ በተሳካ ሁኔታ እየሰራ ነው። ዳታቤዙን ደግሞ ቀጥለን እናገናኛለን።";
        sendMessage($chatId, $response);
    } else {
        sendMessage($chatId, "መልዕክትዎ ደርሶኛል፦ " . $message);
    }
}

// መልዕክት መላኪያ Function
function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
