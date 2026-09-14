<?php
use Alprof\Services\TwoFactorService;
$tfa = new TwoFactorService($db);
$user = $auth->user();
$isEnabled = $tfa->isEnabled((int)$user['id']);
$secret = null;
$qrUri = null;
$setupMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isManager()) {
    if (!$csrf->verify($_POST['csrf'] ?? null)) {
        http_response_code(419);
        $setupMessage = ['type' => 'error', 'text' => 'انتهت الجلسة.'];
    } elseif (($_POST['action'] ?? '') === 'enable-2fa') {
        $secret = $tfa->generateSecret();
        $code = trim($_POST['verify_code'] ?? '');
        if ($tfa->verifyCode($secret, $code)) {
            $tfa->enable((int)$user['id'], $secret);
            $isEnabled = true;
            $secret = null;
            $setupMessage = ['type' => 'success', 'text' => 'تم تفعيل المصادقة الثنائية بنجاح.'];
        } else {
            $qrUri = $tfa->getOtpauthUri($secret, $user['email']);
            $setupMessage = ['type' => 'error', 'text' => 'الرمز غير صحيح. تأكد من إدخال الرمز من التطبيق.'];
        }
    } elseif (($_POST['action'] ?? '') === 'disable-2fa') {
        $code = trim($_POST['verify_code'] ?? '');
        $currentSecret = $tfa->getSecret((int)$user['id']);
        if ($currentSecret && $tfa->verifyCode($currentSecret, $code)) {
            $tfa->disable((int)$user['id']);
            $isEnabled = false;
            $setupMessage = ['type' => 'success', 'text' => 'تم إيقاف المصادقة الثنائية.'];
        } else {
            $setupMessage = ['type' => 'error', 'text' => 'الرمز غير صحيح.'];
        }
    }
}

if (!$isEnabled && !$secret) {
    $secret = $tfa->generateSecret();
    $qrUri = $tfa->getOtpauthUri($secret, $user['email']);
}
?>
<div class="page-head">
  <div>
    <span class="eyebrow">الأمان والحماية</span>
    <h1>المصادقة الثنائية (2FA)</h1>
    <p>طبقة حماية إضافية لحسابك: رمز من هاتفك عند كل دخول</p>
  </div>
  <span class="version-badge">V. 2.6</span>
</div>

<?php if ($setupMessage): ?>
<div class="import-flash <?= htmlspecialchars($setupMessage['type']) ?>"><?= htmlspecialchars($setupMessage['text']) ?></div>
<?php endif; ?>

<section class="two-factor-card">
  <?php if ($isEnabled): ?>
    <div class="tfa-status enabled">
      <span class="tfa-icon">🔒</span>
      <div>
        <h2>المصادقة الثنائية مفعّلة</h2>
        <p>حسابك محمي بطبقة إضافية. عند تسجيل الدخول ستتطلب منك إدخال رمز من تطبيق المصادقة.</p>
      </div>
    </div>
    <form method="post" class="tfa-disable-form">
      <?=$csrf->field()?>
      <input type="hidden" name="action" value="disable-2fa">
      <label>أدخل رمز التحقق من التطبيق لإيقاف المصادقة الثنائية
        <input type="text" name="verify_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="000000">
      </label>
      <button class="btn btn-danger-soft" type="submit">إيقاف المصادقة الثنائية</button>
    </form>
  <?php else: ?>
    <div class="tfa-status disabled">
      <span class="tfa-icon">🔓</span>
      <div>
        <h2>المصادقة الثنائية غير مفعّلة</h2>
        <p>لتفعيلها، اتبع الخطوات التالية:</p>
      </div>
    </div>
    <ol class="tfa-steps">
      <li>حمّل تطبيق مصادقة مثل <b>Google Authenticator</b> أو <b>Authy</b> على هاتفك</li>
      <li>أضف حساب جديد في التطبيق واختر <b>إدخال المفتاح يدويًا</b></li>
      <li>أدخل المفتاح التالي (أو امسح رمز QR):</li>
    </ol>
    <div class="tfa-secret-box">
      <code><?= htmlspecialchars(chunk_split($secret, 4, ' ')) ?></code>
    </div>
    <?php if ($qrUri): ?>
    <div class="tfa-qr">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($qrUri) ?>" alt="QR Code للمصادقة الثنائية" width="200" height="200">
      <p>امسح الرمز بتطبيق المصادقة</p>
    </div>
    <?php endif; ?>
    <form method="post" class="tfa-enable-form">
      <?=$csrf->field()?>
      <input type="hidden" name="action" value="enable-2fa">
      <label>أدخل الرمز الظاهر في التطبيق للتأكيد
        <input type="text" name="verify_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="000000">
      </label>
      <button class="btn btn-add" type="submit">تفعيل المصادقة الثنائية</button>
    </form>
  <?php endif; ?>
</section>
