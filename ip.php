<?php
// Telegram Bot Token and Chat ID
$botToken = "7896580316:AAGjpOYGDPvE6ae2I_ntw7T5A9P4-IrzBrM";
$chatId = "7039202721";

// Get the visitor's IP address
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Use an IP Geolocation API to get location details
$geoData = file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,message,country,regionName,city,zip,lat,lon,timezone,isp,org,as,mobile,proxy");
$geoInfo = json_decode($geoData, true);

// Get additional information
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$requestTime = date("Y-m-d H:i:s");

// Prepare the message for Telegram with all possible details
$message = "🚨 *New Visitor Alert* 🚨\n";
$message .= "🕒 *Time:* {$requestTime}\n";
$message .= "🌐 *IP Address:* {$ipAddress}\n";

if ($geoInfo['status'] === 'success') {
    $message .= "📍 *City:* " . ($geoInfo['city'] ?? 'Unknown') . "\n";
    $message .= "📍 *Region:* " . ($geoInfo['regionName'] ?? 'Unknown') . "\n";
    $message .= "📍 *Country:* " . ($geoInfo['country'] ?? 'Unknown') . "\n";
    $message .= "📮 *ZIP Code:* " . ($geoInfo['zip'] ?? 'Unknown') . "\n";
    $message .= "🌍 *Latitude:* " . ($geoInfo['lat'] ?? 'Unknown') . "\n";
    $message .= "🌍 *Longitude:* " . ($geoInfo['lon'] ?? 'Unknown') . "\n";
    $message .= "🕰 *Timezone:* " . ($geoInfo['timezone'] ?? 'Unknown') . "\n";
    $message .= "📡 *ISP:* " . ($geoInfo['isp'] ?? 'Unknown') . "\n";
    $message .= "🏢 *Organization:* " . ($geoInfo['org'] ?? 'Unknown') . "\n";
    $message .= "🔗 *AS Number:* " . ($geoInfo['as'] ?? 'Unknown') . "\n";
    $message .= "📱 *Mobile Connection:* " . (($geoInfo['mobile'] ?? false) ? 'Yes' : 'No') . "\n";
    $message .= "🛡 *Proxy or VPN:* " . (($geoInfo['proxy'] ?? false) ? 'Yes' : 'No') . "\n";
} else {
    $message .= "❌ *Location Lookup Failed:* " . ($geoInfo['message'] ?? 'Unknown error') . "\n";
}

$message .= "🖥 *User Agent:* {$userAgent}\n";

// Send the message to Telegram
$telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$telegramData = [
    "chat_id" => $chatId,
    "text" => $message,
    "parse_mode" => "Markdown",
];

$options = [
    "http" => [
        "header" => "Content-type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($telegramData),
    ],
];
$context = stream_context_create($options);
$response = file_get_contents($telegramUrl, false, $context);

// Optionally log the details locally (you can remove this part if not needed)
$logFile = "ip_logs.txt";
$logData = [
    "ip" => $ipAddress,
    "city" => $geoInfo['city'] ?? 'Unknown',
    "region" => $geoInfo['regionName'] ?? 'Unknown',
    "country" => $geoInfo['country'] ?? 'Unknown',
    "zip" => $geoInfo['zip'] ?? 'Unknown',
    "latitude" => $geoInfo['lat'] ?? 'Unknown',
    "longitude" => $geoInfo['lon'] ?? 'Unknown',
    "timezone" => $geoInfo['timezone'] ?? 'Unknown',
    "isp" => $geoInfo['isp'] ?? 'Unknown',
    "organization" => $geoInfo['org'] ?? 'Unknown',
    "as" => $geoInfo['as'] ?? 'Unknown',
    "mobile" => $geoInfo['mobile'] ?? false,
    "proxy" => $geoInfo['proxy'] ?? false,
    "user_agent" => $userAgent,
    "time" => $requestTime,
];
file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);

// Show a message to the visitor (optional)
echo "Thank you for visiting!";
