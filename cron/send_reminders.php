<?php
// Basic cron script to queue notifications for due/overdue installments
require_once __DIR__ . '/../config/db.php';

$today = date('Y-m-d');
$stmt = safe_query("SELECT i.id as installment_id, u.id as user_id, u.email, s.id as sale_id, i.due_date, i.amount_due, i.amount_paid FROM installments i JOIN sales s ON s.id = i.sale_id JOIN users u ON u.id = s.customer_id WHERE (i.status IN ('pending','partial') AND i.due_date <= ?)", 's', [$today]);
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($rows as $r) {
    $subject = 'Installment Reminder';
    $message = 'Your installment for Sale #' . (int)$r['sale_id'] . ' is due on ' . $r['due_date'] . '. Amount due: ' . number_format((float)$r['amount_due'] - (float)$r['amount_paid'], 2);
    safe_query('INSERT INTO notifications (user_id, type, subject, message, scheduled_at) VALUES (?,?,?,?,NOW())', 'isss', [(int)$r['user_id'], 'installment_reminder', $subject, $message]);
}

echo 'Queued ' . count($rows) . ' reminders.' . PHP_EOL;

