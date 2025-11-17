<?php
require_once __DIR__ . '/twilio_config.php';

/**
 * Send a WhatsApp message via Twilio REST API.
 * @param string $to Full WhatsApp number like 'whatsapp:+919999999999'
 * @param string $body Message body
 * @return array ['success'=>bool, 'response'=>string]
 */
function send_whatsapp_message($to, $body) {
    if (!LOW_STOCK_ALERTS_ENABLED) {
        return ['success' => false, 'response' => 'alerts_disabled'];
    }

    $sid = TWILIO_ACCOUNT_SID;
    $token = TWILIO_AUTH_TOKEN;
    $from = TWILIO_WHATSAPP_FROM;

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

    $post = http_build_query([
        'To' => $to,
        'From' => $from,
        'Body' => $body
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($http >= 200 && $http < 300) {
        return ['success' => true, 'response' => $resp];
    }
    $msg = $err ?: trim($resp);
    return ['success' => false, 'response' => $msg];
}
