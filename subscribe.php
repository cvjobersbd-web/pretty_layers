<?php
$ROOT = '';
require __DIR__ . '/includes/bootstrap.php';
if (is_post()) {
    csrf_check();
    $email = strtolower(post('email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) flash('error', 'Please enter a valid email address.');
    else {
        if (!val('SELECT id FROM newsletter WHERE email = ?', [$email])) insert('newsletter', ['email' => $email, 'created_at' => now()]);
        flash('success', 'Thank you for subscribing! 💜');
    }
}
redirect($_SERVER['HTTP_REFERER'] ?? url('index.php'));
