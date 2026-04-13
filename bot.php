<?php
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

$host = "db.jzolixisaneilbuourna.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "+7DTfkA.7kCPr_K";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Error logic here if needed
}

$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if ($chatId) {
    if ($message == "/start") {
        $response = "እንኳን ወደ ጥርስ ህክምና ክሊኒካችን በደህና መጡ! \n\nቀጠሮ ለመያዝ ስምዎን እና ስልክዎን እንዲህ አድርገው ይላኩ፦ \n\n*አበበ ካሳ 0911223344*";
        sendMessage($chatId, $response);
    } else {
        // ስም እና ስልክ መለየት (በቀላል መንገድ)
        $data = explode(" ", $message);
        $fullName = $data[0] . " " . ($data[1] ?? "");
        $phone = end($data);

        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name, phone_number) VALUES (?, ?, ?)");
            $stmt->execute([$chatId, $fullName, $phone]);
            
            sendMessage($chatId, "እናመሰግናለን $fullName! መረጃዎ በስኬት ተመዝግቧል።");
        } catch (Exception $e) {
            sendMessage($chatId, "ይቅርታ፣ ዳታቤዝ ግንኙነት ላይ ችግር ተፈጥሯል።");
        }
    }
}

function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
