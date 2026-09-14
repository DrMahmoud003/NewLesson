<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
global $db,$auth,$csrf;
use Alprof\Services\TwoFactorService;

$error = null;
$twoFactorRequired = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->verify($_POST['csrf'] ?? null)) {
        http_response_code(419);
        $error = 'انتهت الجلسة، أعد المحاولة.';
    } elseif (($_POST['action'] ?? '') === 'login') {
        if ($auth->attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            $user = $auth->user();
            $tfa = new TwoFactorService($db);
            if ($tfa->isEnabled((int)$user['id'])) {
                $_SESSION['2fa_pending'] = $user['id'];
                $twoFactorRequired = true;
            } else {
                header('Location: /?module=home');
                exit;
            }
        } else {
            $error = 'بيانات الدخول غير صحيحة.';
        }
    } elseif (($_POST['action'] ?? '') === 'verify-2fa') {
        $pendingId = $_SESSION['2fa_pending'] ?? null;
        if (!$pendingId) {
            $error = 'جلسة التحقق منتهية، سجّل الدخول مرة أخرى.';
        } else {
            $tfa = new TwoFactorService($db);
            $secret = $tfa->getSecret((int)$pendingId);
            $code = trim($_POST['totp_code'] ?? '');
            if ($secret && strlen($code) === 6 && ctype_digit($code) && $tfa->verifyCode($secret, $code)) {
                unset($_SESSION['2fa_pending']);
                $_SESSION['last_activity'] = time();
                header('Location: /?module=home');
                exit;
            } else {
                $error = 'رمز التحقق غير صحيح.';
                $twoFactorRequired = true;
            }
        }
    } elseif (($_POST['action'] ?? '') === 'logout') {
        $auth->logout();
        header('Location: /');
        exit;
    }
}

if ($twoFactorRequired) {
    require dirname(__DIR__).'/resources/views/layouts/2fa-verify.php';
    exit;
}

if (!$auth->check()) {
    require dirname(__DIR__).'/resources/views/layouts/auth.php';
    exit;
}

$allowed = ['home','group-dashboard','students','group-archive','attendance','exams','homework','recitation','finance','financial-summary','reports','parents','assistants','two-factor-setup'];
$module = $_GET['module'] ?? 'home';
if (!in_array($module, $allowed, true)) $module = 'home';
if ($module === 'assistants' && !$auth->isManager()) {
    $module = 'home';
    $error = 'هذا القسم متاح للمدير فقط.';
}
require dirname(__DIR__).'/resources/views/layouts/dashboard.php';
