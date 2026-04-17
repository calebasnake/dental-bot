<?php
$botToken = "8640297748:AAG7yey9RNEO8yX0hGNlTA6VBucwxcViN_U";
$website = "https://api.telegram.org/bot" . $botToken;

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;

if ($chatId) {
    // ለሙከራ ያህል መልዕክት መላክ
    $text = "ሰላም ካሌብ! ሰርቨሩ መልዕክትህን ተቀብሏል። አሁን ኮዱን ማሳደግ እንችላለን።";
    file_get_contents($website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text));
}
?>
