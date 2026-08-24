<?php
/**
 * FitLife — Admin Notification Email Settings
 * --------------------------------------------
 * Fill in SMTP_USERNAME and SMTP_APP_PASSWORD below to enable
 * "new user registered" email alerts.
 *
 * How to get a Gmail App Password (needed because Gmail blocks
 * regular password login from apps like this):
 *   1. Go to https://myaccount.google.com/security
 *   2. Turn on 2-Step Verification if it isn't already on.
 *   3. Go to https://myaccount.google.com/apppasswords
 *   4. Create an app password (name it "FitLife" or similar).
 *   5. Google gives you a 16-character code — paste it below as
 *      SMTP_APP_PASSWORD (no spaces).
 *
 * SMTP_USERNAME is the Gmail address FitLife sends FROM. It can be the
 * same as ADMIN_NOTIFY_EMAIL, or a separate Gmail account you use just
 * for sending — either works.
 */

// The Gmail address that will receive "new user registered" alerts.
define('ADMIN_NOTIFY_EMAIL', 'devang.vyas130972@marwadiuniversity.ac.in');

// The Gmail account FitLife sends FROM (needs an App Password, see above).
define('SMTP_USERNAME', 'your-sending-gmail@gmail.com');
define('SMTP_APP_PASSWORD', 'your16charapppassword');

// Display name shown as the sender.
define('SMTP_FROM_NAME', 'FitLife');

// Gmail SMTP settings — these don't need to change.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Master switch: set to false to disable email notifications entirely
// without removing any code.
define('ADMIN_NOTIFICATIONS_ENABLED', true);
