<?php
declare(strict_types=1);

use Alprof\Services\EncryptionService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

require dirname(__DIR__) . '/app/bootstrap.php';
global $db,$auth,$csrf;
$auth->requireAuth();

function importRedirect(string $type, string $message): never
{
    $_SESSION['students_import_flash'] = ['type' => $type, 'message' => $message];
    header('Location: /?module=students#import-excel');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
if (!$auth->can('students.manage')) {
    http_response_code(403);
    exit('ليس لديك صلاحية إدارة الطلاب.');
}
if (!$csrf->verify($_POST['csrf'] ?? null)) {
    importRedirect('error', 'انتهت الجلسة؛ أعد فتح الصفحة وحاول مرة أخرى.');
}

$groupId = filter_var($_POST['group_id'] ?? null, FILTER_VALIDATE_INT);
if (!$groupId) importRedirect('error', 'اختر الفرقة التي ستُضاف إليها البيانات.');
$groupQuery = $db->prepare('SELECT id, monthly_fee FROM groups WHERE id = ? LIMIT 1');
$groupQuery->execute([$groupId]);
$group = $groupQuery->fetch();
if (!$group) importRedirect('error', 'الفرقة المختارة غير موجودة.');

$file = $_FILES['students_excel'] ?? null;
if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    importRedirect('error', 'تعذر رفع الملف. تأكد من اختيار ملف Excel صالح.');
}
if (($file['size'] ?? 0) > 5 * 1024 * 1024) importRedirect('error', 'حجم الملف أكبر من 5 ميجابايت.');
$extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['xlsx', 'xls'], true)) importRedirect('error', 'المسموح فقط ملفات XLSX أو XLS.');
if (!is_uploaded_file((string)$file['tmp_name'])) importRedirect('error', 'مصدر الملف غير صالح.');
if (!class_exists(IOFactory::class)) importRedirect('error', 'مكتبة قراءة Excel غير مثبتة. نفّذ composer install أولًا.');

try {
    $reader = IOFactory::createReaderForFile((string)$file['tmp_name']);
    $reader->setReadDataOnly(true);
    $sheet = $reader->load((string)$file['tmp_name'])->getActiveSheet();
    $highestColumn = $sheet->getHighestDataColumn();
    $headers = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true)[1] ?? [];
    $normalize = static fn($v): string => trim(preg_replace('/\\s+/u', ' ', (string)$v));
    $map = [];
    foreach ($headers as $column => $header) $map[$normalize($header)] = $column;
    foreach (['اسم الطالب', 'الرسوم المقررة'] as $required) {
        if (!isset($map[$required])) throw new RuntimeException('العمود المطلوب غير موجود: ' . $required);
    }

    $insertStudent = $db->prepare('INSERT INTO students(group_id,name,joined_at,monthly_fee_override,fee_exempt,status) VALUES(?,?,?,?,?,\'active\')');
    $insertPhone = $db->prepare('INSERT INTO student_phones(student_id,phone_encrypted,phone_hash,is_primary) VALUES(?,?,?,?)');
    $encryption = new EncryptionService();
    $today = date('Y-m-d');
    $created = 0; $skipped = 0; $errors = [];
    $db->beginTransaction();

    for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
        $value = static function (string $header) use ($sheet, $map, $row) {
            if (!isset($map[$header])) return null;
            return $sheet->getCell($map[$header] . $row)->getValue();
        };
        $name = $normalize($value('اسم الطالب'));
        $feeRaw = $value('الرسوم المقررة');
        if ($name === '' && ($feeRaw === null || $feeRaw === '')) continue;
        if ($name === '' || mb_strlen($name) > 160) { $skipped++; $errors[] = "صف {$row}: اسم الطالب غير صالح"; continue; }
        if (!is_numeric($feeRaw) || (float)$feeRaw < 0 || (float)$feeRaw > 1000000) { $skipped++; $errors[] = "صف {$row}: الرسوم غير صالحة"; continue; }
        $fee = round((float)$feeRaw, 2);
        $joinedRaw = $value('تاريخ الانضمام');
        $joinedAt = $today;
        if (is_numeric($joinedRaw)) $joinedAt = ExcelDate::excelToDateTimeObject((float)$joinedRaw)->format('Y-m-d');
        elseif (is_string($joinedRaw) && trim($joinedRaw) !== '') {
            $timestamp = strtotime(str_replace('/', '-', trim($joinedRaw)));
            if ($timestamp !== false) $joinedAt = date('Y-m-d', $timestamp);
        }
        $exemptText = mb_strtolower($normalize($value('إعفاء من الرسوم')));
        $exempt = in_array($exemptText, ['نعم', 'yes', '1', 'معفى', 'معفاة'], true) ? 1 : 0;
        $insertStudent->execute([(int)$groupId, $name, $joinedAt, $fee, $exempt]);
        $studentId = (int)$db->lastInsertId();
        foreach ([['الهاتف الأساسي', 1], ['هاتف ولي الأمر', 0]] as [$heading, $primary]) {
            $phone = preg_replace('/\\D+/', '', (string)$value($heading));
            if ($phone === '') continue;
            if (strlen($phone) < 10 || strlen($phone) > 15) { $errors[] = "صف {$row}: تم تجاهل {$heading}"; continue; }
            $insertPhone->execute([$studentId, $encryption->encrypt($phone), $encryption->blindIndex($phone), $primary]);
        }
        $created++;
    }
    $db->commit();
    $auth->audit((int)($auth->user()['id'] ?? 0), 'students_excel_import', ['group_id' => $groupId, 'created' => $created, 'skipped' => $skipped]);
    $summary = "تم استيراد {$created} طالب بنجاح" . ($skipped ? "، وتخطي {$skipped} صف" : '');
    if ($errors) $summary .= ' — ' . implode(' | ', array_slice($errors, 0, 3));
    importRedirect($created ? 'success' : 'error', $summary);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    $auth->audit((int)($auth->user()['id'] ?? 0), 'students_excel_import_failed', ['group_id' => $groupId, 'error' => $e->getMessage()]);
    importRedirect('error', 'لم يتم الاستيراد: ' . $e->getMessage());
}
