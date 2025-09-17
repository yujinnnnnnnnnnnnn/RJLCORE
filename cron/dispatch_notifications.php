<?php
// Dispatch unsent notifications via email
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/mail.php';

$stmt = safe_query('SELECT n.id, u.email, n.subject, n.message FROM notifications n JOIN users u ON u.id = n.user_id WHERE n.is_sent = 0 ORDER BY n.created_at ASC LIMIT 100');
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$sentCount = 0;
foreach ($rows as $n) {
    if (!$n['email']) { continue; }
    $ok = send_email($n['email'], $n['subject'], nl2br(htmlspecialchars($n['message'])));
    if ($ok) {
        safe_query('UPDATE notifications SET is_sent = 1, sent_at = NOW() WHERE id = ?', 'i', [(int)$n['id']]);
        $sentCount++;
    }
}

echo 'Sent ' . $sentCount . ' notifications.' . PHP_EOL;

