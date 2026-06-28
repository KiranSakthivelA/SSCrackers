<?php
/**
 * send_notification.php
 * SS Crackers — Order Notification Module
 * Sends order details to owner via:
 *   1. HTML Email (via PHP mail())
 *   2. WhatsApp message (via Meta WhatsApp Business Cloud API)
 *
 * Meta Cloud API Setup:
 *   - No QR scanning needed
 *   - Free: 1,000 conversations/month
 *   - Official & reliable (Meta/Facebook)
 *   - Configure credentials in config.php
 */

require_once __DIR__ . '/generate_pdf.php';

// ================================================
// SEND ORDER EMAIL TO OWNER
// ================================================
function sendOrderEmail($order_number, $customer, $items, $total) {
    $to      = OWNER_EMAIL;
    $subject = "=?UTF-8?B?" . base64_encode("🛒 New Order {$order_number} — " . STORE_NAME) . "?=";

    // Build item rows for HTML email
    $item_rows_html = '';
    $sno = 1;
    foreach ($items as $item) {
        $line_total = $item['price'] * $item['qty'];
        $item_rows_html .= "
        <tr style='border-bottom:1px solid #f0ede0;'>
          <td style='padding:12px 14px;text-align:center;color:#7F0000;font-weight:700;'>{$sno}</td>
          <td style='padding:12px 14px;font-weight:600;color:#1a1a2e;'>" . htmlspecialchars($item['name']) . "</td>
          <td style='padding:12px 14px;text-align:center;'>₹" . number_format($item['price'], 2) . "</td>
          <td style='padding:12px 14px;text-align:center;font-weight:700;'>{$item['qty']}</td>
          <td style='padding:12px 14px;text-align:right;font-weight:800;color:#D32F2F;'>₹" . number_format($line_total, 2) . "</td>
        </tr>";
        $sno++;
    }

    $date_str    = date('d M Y, h:i A');
    $total_fmt   = number_format($total, 2);
    $item_count  = count($items);
    $item_suffix = $item_count > 1 ? 's' : ''; // pre-compute — ternary not allowed inside heredoc

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Order — SS Crackers</title>
</head>
<body style="margin:0;padding:0;background:#f5f0e8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8;padding:30px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(127,0,0,0.12);max-width:620px;width:100%;">

      <!-- HEADER -->
      <tr>
        <td style="background:linear-gradient(135deg,#7F0000 0%,#D32F2F 100%);padding:32px 36px;border-bottom:4px solid #D4AF37;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <div style="font-size:24px;font-weight:900;color:#F0D060;letter-spacing:-0.5px;">🎉 SS Crackers</div>
                <div style="font-size:12px;color:rgba(240,208,96,0.7);font-weight:600;letter-spacing:2px;text-transform:uppercase;margin-top:3px;">Admin Order Notification</div>
              </td>
              <td align="right">
                <div style="background:rgba(212,175,55,0.2);border:1.5px solid rgba(212,175,55,0.5);border-radius:10px;padding:10px 16px;display:inline-block;">
                  <div style="font-size:11px;color:rgba(240,208,96,0.7);text-transform:uppercase;letter-spacing:1px;">Order</div>
                  <div style="font-size:20px;font-weight:900;color:#F0D060;">{$order_number}</div>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- ALERT BANNER -->
      <tr>
        <td style="background:#FFF8E7;padding:16px 36px;border-bottom:1px solid #F0D060;">
          <p style="margin:0;font-size:14px;color:#8a6500;font-weight:700;">
            🔔 A new order was just placed on {$date_str}. Please review and confirm.
          </p>
        </td>
      </tr>

      <!-- CUSTOMER DETAILS -->
      <tr>
        <td style="padding:28px 36px 0;">
          <h2 style="margin:0 0 16px;font-size:14px;font-weight:800;color:#7F0000;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid #f5f0e8;padding-bottom:8px;">👤 Customer Details</h2>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td width="50%" style="padding:6px 0;font-size:13px;"><strong style="color:#374151;">Name:</strong> <span style="color:#1a1a2e;">{$customer['name']}</span></td>
              <td width="50%" style="padding:6px 0;font-size:13px;"><strong style="color:#374151;">Phone:</strong> <a href="tel:{$customer['phone']}" style="color:#D32F2F;font-weight:700;">{$customer['phone']}</a></td>
            </tr>
            <tr>
              <td colspan="2" style="padding:6px 0;font-size:13px;"><strong style="color:#374151;">Email:</strong> <span style="color:#1a1a2e;">{$customer['email']}</span></td>
            </tr>
            <tr>
              <td colspan="2" style="padding:6px 0;font-size:13px;"><strong style="color:#374151;">Address:</strong> <span style="color:#1a1a2e;">{$customer['address']}, {$customer['city']} – {$customer['pin']}</span></td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- ORDER ITEMS -->
      <tr>
        <td style="padding:24px 36px 0;">
          <h2 style="margin:0 0 16px;font-size:14px;font-weight:800;color:#7F0000;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid #f5f0e8;padding-bottom:8px;">🛒 Order Items ({$item_count} products)</h2>
          <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:10px;overflow:hidden;border:1px solid #f0ede0;">
            <thead>
              <tr style="background:linear-gradient(135deg,#7F0000,#D32F2F);">
                <th style="padding:11px 14px;text-align:center;color:#F0D060;font-size:11px;font-weight:800;text-transform:uppercase;">#</th>
                <th style="padding:11px 14px;text-align:left;color:#F0D060;font-size:11px;font-weight:800;text-transform:uppercase;">Product</th>
                <th style="padding:11px 14px;text-align:center;color:#F0D060;font-size:11px;font-weight:800;text-transform:uppercase;">Price</th>
                <th style="padding:11px 14px;text-align:center;color:#F0D060;font-size:11px;font-weight:800;text-transform:uppercase;">Qty</th>
                <th style="padding:11px 14px;text-align:right;color:#F0D060;font-size:11px;font-weight:800;text-transform:uppercase;">Total</th>
              </tr>
            </thead>
            <tbody>
              {$item_rows_html}
            </tbody>
          </table>
        </td>
      </tr>

      <!-- GRAND TOTAL -->
      <tr>
        <td style="padding:20px 36px 0;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#7F0000,#D32F2F);border-radius:10px;">
            <tr>
              <td style="padding:18px 20px;color:rgba(240,208,96,0.85);font-size:14px;font-weight:700;">Grand Total ({$item_count} item{$item_suffix})</td>
              <td style="padding:18px 20px;text-align:right;color:#F0D060;font-size:22px;font-weight:900;">₹{$total_fmt}</td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- ACTION BUTTON -->
      <tr>
        <td style="padding:24px 36px;" align="center">
          <a href="{ADMIN_URL}" style="display:inline-block;background:linear-gradient(135deg,#D4AF37,#F0D060);color:#7F0000;font-weight:800;font-size:14px;padding:14px 32px;border-radius:50px;text-decoration:none;letter-spacing:0.5px;">
            🔗 View in Admin Panel
          </a>
        </td>
      </tr>

      <!-- FOOTER -->
      <tr>
        <td style="background:#f5f0e8;padding:20px 36px;text-align:center;border-top:2px solid #D4AF37;">
          <p style="margin:0;font-size:12px;color:#8a6b6b;">
            This is an automated notification from <strong>SS Crackers</strong>.<br>
            {STORE_PHONE} &nbsp;|&nbsp; {STORE_URL}
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    $html = str_replace('{ADMIN_URL}',   ADMIN_URL,   $html);
    $html = str_replace('{STORE_PHONE}', STORE_PHONE, $html);
    $html = str_replace('{STORE_URL}',   STORE_URL,   $html);

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . STORE_NAME . " <" . OWNER_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . OWNER_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $sent = @mail($to, $subject, $html, $headers);
    return $sent;
}

// ================================================
// SEND WHATSAPP NOTIFICATION VIA META CLOUD API
// ================================================
function sendWhatsAppNotification($order_number, $customer, $items, $total) {

    // Guard: check credentials are configured in config.php
    if (!META_WA_ENABLED) {
        return ['sent' => false, 'reason' => 'Meta WhatsApp API disabled in config'];
    }
    if (META_WA_ACCESS_TOKEN === 'paste_your_access_token_here') {
        return ['sent' => false, 'reason' => 'Meta WhatsApp credentials not configured in config.php'];
    }

    // Build the WhatsApp message text
    $item_lines = '';
    foreach ($items as $item) {
        $line_total   = $item['price'] * $item['qty'];
        $item_lines  .= "\n  • " . $item['name'] . " x" . $item['qty'] . " = Rs." . number_format($line_total, 0);
    }

    $msg  = "🎉 *New Order — SS Crackers*\n";
    $msg .= "━━━━━━━━━━━━━━━━━\n";
    $msg .= "📋 Order No: *{$order_number}*\n";
    $msg .= "📅 Date: " . date('d M Y, h:i A') . "\n\n";
    $msg .= "👤 *Customer Details*\n";
    $msg .= "  Name  : " . $customer['name'] . "\n";
    $msg .= "  Phone : " . $customer['phone'] . "\n";
    $msg .= "  City  : " . $customer['city'] . " – " . $customer['pin'] . "\n";
    $msg .= "  Addr  : " . $customer['address'] . "\n\n";
    $msg .= "🛒 *Order Items*" . $item_lines . "\n\n";
    $msg .= "💰 *Grand Total: Rs." . number_format($total, 0) . "*\n";
    $msg .= "━━━━━━━━━━━━━━━━━\n";
    $msg .= "🔗 " . ADMIN_URL;

    // Meta Cloud API endpoint
    // Phone Number ID is found in Meta Developer Console → WhatsApp → API Setup
    $url = "https://graph.facebook.com/v20.0/" . META_WA_PHONE_NUMBER_ID . "/messages";

    // Recipient: must be in international format without '+' or spaces
    $to_number = preg_replace('/[^0-9]/', '', META_WA_TARGET_PHONE);

    $payload = [
        'messaging_product' => 'whatsapp',
        'to'                => $to_number,
        'type'              => 'text',
        'text'              => ['body' => $msg]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . META_WA_ACCESS_TOKEN
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return ['sent' => false, 'reason' => 'cURL error: ' . $curl_error];
    }

    $resp_json = json_decode($response, true);

    // Meta returns 200 and messages[0].id on success
    $success = ($http_code === 200 && isset($resp_json['messages'][0]['id']));

    return [
        'sent'      => $success,
        'http_code' => $http_code,
        'response'  => $resp_json ?? $response
    ];
}

// ================================================
// SEND ALL NOTIFICATIONS (Email + WhatsApp)
// ================================================
function sendOrderNotifications($order_number, $customer, $items, $total) {
    $results = [];

    // 1. Email
    $email_sent = sendOrderEmail($order_number, $customer, $items, $total);
    $results['email'] = ['sent' => $email_sent];

    // 2. WhatsApp via Meta Cloud API
    $wa_result = sendWhatsAppNotification($order_number, $customer, $items, $total);
    $results['whatsapp'] = $wa_result;

    return $results;
}
?>
