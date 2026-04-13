<?php
// 1. የቴሌግራም ቦት መረጃ
$botToken = "8301010392:AAHooXc-s0kgn8ghVlazHSfxT3AKV2mQ1DE";
$website = "https://api.telegram.org/bot" . $botToken;

// 2. የ Supabase ዳታቤዝ መረጃ (አንተ በሰጠኸኝ መሰረት)
$host = "db.jzolixisaneilbuourna.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "+7DTfkA.7kCPr_K"; // የሰጠኸኝ ፓስዋርድ

// ከዳታቤዝ ጋር ግንኙነት መፍጠር (PDO)
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // ግንኙነቱ ካልተሳካ ለጊዜው ዝም ይላል
}

// 3. መረጃ መቀበያ
$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);

$chatId = $update["message"]["chat"]["id"] ?? null;
$message = $update["message"]["text"] ?? "";

if ($chatId) {
    if ($message == "/start") {
        $response = "እንኳን ወደ ጥርስ ህክምና ክሊኒካችን በደህና መጡ! \n\nእባክዎ ቀጠሮ ለመያዝ ስምዎን እና ስልክዎን በዚህ መልኩ ይላኩ፦ \n\n*ስም፦ ሙሉ ስም*\n*ስልክ፦ 09...*";
        sendMessage($chatId, $response);
    } 
    // ተጠቃሚው መረጃ ሲልክ (ለምሳሌ፡ ስም እና ስልክ የያዘ መልዕክት)
    else {
        // መረጃውን ዳታቤዝ ውስጥ ማስገባት
        try {
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name) VALUES (?, ?)");
            $stmt->execute([$chatId, $message]);
            
            $reply = "እናመሰግናለን! መረጃዎ ተመዝግቧል። በቅርቡ እናገኝዎታለን።";
            sendMessage($chatId, $reply);
        } catch (Exception $e) {
            sendMessage($chatId, "ይቅርታ፣ መረጃውን መመዝገብ አልቻልኩም። እባክዎ ቆይተው ይሞክሩ።");
        }
    }
}

// መልዕክት መላኪያ Function
function sendMessage($chatId, $text) {
    global $website;
    $url = $website . "/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    file_get_contents($url);
}
?>
