<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
global $db,$auth,$csrf;

use Alprof\Services\EncryptionService;
use Alprof\Services\ParentRateLimiter;

$student = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $csrf->verify($_POST['csrf'] ?? null)) {
    $rateLimiter = new ParentRateLimiter();
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (!$rateLimiter->consume($clientIp)) {
        http_response_code(429);
        $error = 'محاولات كثيرة جدًا، حاول بعد 15 دقيقة.';
    } else {
        $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');

        if ($phone === '') {
            $error = 'أدخل رقم هاتف صحيح.';
        } else {
            try {
                $enc = new EncryptionService();
                $hash = $enc->blindIndex($phone);
            } catch (Throwable) {
                $error = 'خدمة الاستعلام غير متاحة الآن، حاول لاحقًا.';
                $hash = '';
            }

            if ($hash !== '') {
                $q = $db->prepare(
                    "SELECT s.id, s.name, g.name group_name, gl.name grade_name
                     FROM student_phones sp
                     JOIN students s ON s.id = sp.student_id
                     JOIN groups g ON g.id = s.group_id
                     JOIN grade_levels gl ON gl.id = g.grade_level_id
                     WHERE sp.phone_hash = ? AND s.status = 'active'
                     LIMIT 1"
                );
                $q->execute([$hash]);
                $student = $q->fetch() ?: null;

                if (!$student) {
                    $error = 'تعذر العثور على تقرير مطابق.';
                }
            }
        }
    }
}

require dirname(__DIR__) . '/resources/views/layouts/parent.php';
