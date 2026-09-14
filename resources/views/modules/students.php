<?php require __DIR__.'/group-tabs.php'; ?>
<div class="page-head students-page-head">
  <div>
    <span class="eyebrow">إدارة المجموعة</span>
    <h1>داتا الطلاب</h1>
    <p>كل بيانات الطالب وإجراءاته في بطاقة واحدة واضحة وسريعة</p>
  </div>
  <span class="version-badge">V. 2.6</span>
</div>

<section class="group-identity" style="--group-color:#6c5ce7">
  <div class="group-title-wrap">
    <div class="group-icon">🎓</div>
    <div><small>الفرقة الحالية</small><h2>الصف الأول الثانوي — المجموعة (أ)</h2><p>الأحد والأربعاء • 05:00 مساءً</p></div>
  </div>
  <div class="group-controls">
    <label class="color-picker">لون الفرقة <input id="groupColor" type="color" value="#6c5ce7" aria-label="تغيير لون الفرقة"></label>
    <button class="btn btn-soft" data-ui-action="rename-group">✏️ تغيير الاسم</button>
  </div>
</section>

<?php
$importFlash = $_SESSION['students_import_flash'] ?? null;
unset($_SESSION['students_import_flash']);
$groupOptions = [];
try {
    $groupOptions = $db->query('SELECT g.id,g.name,gl.name AS grade_name FROM groups g JOIN grade_levels gl ON gl.id=g.grade_level_id ORDER BY gl.sort_order,g.name')->fetchAll();
} catch (Throwable) {}
?>
<section class="excel-import-card" id="import-excel">
  <div class="excel-import-heading">
    <div class="excel-logo">X</div>
    <div><span class="eyebrow excel">استيراد جماعي</span><h2>رفع أسماء الطلاب والرسوم من Excel</h2><p>أضف عشرات الطلاب دفعة واحدة، مع رسوم مختلفة لكل طالب.</p></div>
    <a class="template-download" href="/templates/students-import-template.xlsx" download>⬇ تحميل نموذج Excel</a>
  </div>
  <?php if ($importFlash): ?><div class="import-flash <?=htmlspecialchars($importFlash['type'])?>"><?=htmlspecialchars($importFlash['message'])?></div><?php endif; ?>
  <form class="excel-upload-form" action="/import-students.php" method="post" enctype="multipart/form-data">
    <?=$csrf->field()?>
    <label class="import-group-field"><span>الفرقة المستهدفة</span><select name="group_id" required><option value="">اختر الفرقة</option><?php foreach ($groupOptions as $g): ?><option value="<?=(int)$g['id']?>"><?=htmlspecialchars($g['grade_name'].' — '.$g['name'])?></option><?php endforeach; ?></select></label>
    <label class="excel-dropzone" id="excelDropzone">
      <input id="studentsExcel" type="file" name="students_excel" accept=".xlsx,.xls" required>
      <span class="upload-icon">📊</span><b>اسحب ملف Excel هنا أو اضغط للاختيار</b><small>الحد الأقصى 5MB • XLSX أو XLS</small><em id="excelFileName">لم يتم اختيار ملف</em>
    </label>
    <div class="import-columns"><b>الأعمدة:</b><span class="required">اسم الطالب *</span><span class="required">الرسوم المقررة *</span><span>الهاتف الأساسي</span><span>هاتف ولي الأمر</span><span>تاريخ الانضمام</span><span>إعفاء من الرسوم</span></div>
    <div class="import-submit-row"><p>🔒 يتم فحص الملف وتشفير أرقام الهواتف قبل الحفظ.</p><button class="btn excel-submit" type="submit">⬆ رفع واستيراد الطلاب</button></div>
  </form>
</section>

<div class="student-stats">
  <div class="student-stat purple"><span>👥 إجمالي الطلاب</span><b>3</b></div>
  <div class="student-stat green"><span>✅ النشطون</span><b>3</b></div>
  <div class="student-stat amber"><span>💰 رسوم الشهر</span><b>1,200 ج.م</b></div>
  <div class="student-stat rose"><span>🎁 إعفاءات</span><b>1</b></div>
</div>

<section class="students-commandbar">
  <div class="primary-actions">
    <button class="btn btn-add" data-ui-action="add-student">＋ إضافة طالب</button>
    <button class="btn btn-whatsapp" data-ui-action="group-whatsapp">💬 رسالة واتساب جماعية</button>
    <button class="btn btn-report" data-ui-action="group-report">📄 تقرير مفصل</button>
  </div>
  <div class="fee-actions">
    <b>💰 تطبيق رسوم شهرية على الكل</b>
    <div class="fee-input"><input type="number" value="400" min="0" aria-label="قيمة الرسوم"><span>ج.م</span></div>
    <button class="btn btn-apply" data-ui-action="apply-fees">✓ تطبيق على الكل</button>
    <button class="btn btn-danger-soft" data-confirm="هل تريد تفريغ بيانات المجموعة؟" data-ui-action="clear-group">🗑 تفريغ الكل</button>
  </div>
</section>

<div class="student-search-wrap">
  <span>🔎</span>
  <input id="studentSearch" type="search" placeholder="ابحث بالاسم أو رقم الهاتف أو الفرقة..." autocomplete="off">
  <kbd>3 طلاب</kbd>
</div>

<div class="students-list" id="studentsList">
  <article class="student-card" data-student-search="أحمد محمد البنا 01026564408 الصف الأول الثانوي المجموعة أ">
    <div class="student-number">1</div>
    <div class="student-main">
      <div class="student-avatar blue">أم</div>
      <div class="student-name"><h3>أحمد محمد البنا</h3><div class="student-tags"><span class="tag grade">🎓 الصف الأول الثانوي</span><span class="tag group">المجموعة أ</span><span class="tag active">نشط</span></div></div>
    </div>
    <div class="student-data-grid">
      <div class="data-cell phone"><small>رقم الهاتف الأساسي</small><b dir="ltr">01026564408</b></div>
      <div class="data-cell phone"><small>رقم ولي الأمر</small><b dir="ltr">01099321994</b></div>
      <div class="data-cell date"><small>تاريخ الانضمام</small><b>15 أغسطس 2026</b></div>
      <div class="data-cell fee"><small>الرسوم الشهرية</small><b>400 ج.م</b></div>
      <div class="data-cell payment"><small>حالة السداد</small><b class="paid">مسدد بالكامل</b></div>
    </div>
    <div class="student-actions">
      <a class="student-action whatsapp" href="https://wa.me/201026564408" target="_blank" rel="noopener">💬 واتساب</a>
      <button class="student-action edit" data-ui-action="edit-student">✏️ تعديل</button>
      <button class="student-action phone-edit" data-ui-action="edit-phone">📞 تعديل الهاتف</button>
      <button class="student-action exempt" data-ui-action="fee-exempt">🎁 إعفاء من الرسوم</button>
      <button class="student-action attendance" data-ui-action="student-attendance">🧑‍🏫 سجل الحضور</button>
      <button class="student-action fees" data-ui-action="student-fees">💰 الرسوم</button>
      <button class="student-action transfer" data-ui-action="transfer-student">↔ نقل</button>
      <form class="inline-archive-form" method="post" action="/archive-student.php"><?=$csrf->field()?><input type="hidden" name="student_id" value="1"><input type="hidden" name="archive_reason" value="حذف من داتا المجموعة"><button class="student-action delete" data-confirm="سيتم نقل الطالب إلى الأرشيف مع الاحتفاظ ببياناته. هل أنت متأكد؟">🗄️ أرشفة</button></form>
    </div>
  </article>

  <article class="student-card" data-student-search="جنة أيمن مجدي 01009274894 01556021783 الصف الأول الثانوي المجموعة أ">
    <div class="student-number">2</div>
    <div class="student-main">
      <div class="student-avatar violet">جم</div>
      <div class="student-name"><h3>جنة أيمن مجدي</h3><div class="student-tags"><span class="tag grade">🎓 الصف الأول الثانوي</span><span class="tag group">المجموعة أ</span><span class="tag active">نشط</span></div></div>
    </div>
    <div class="student-data-grid">
      <div class="data-cell phone"><small>رقم الهاتف الأساسي</small><b dir="ltr">01009274894</b></div>
      <div class="data-cell phone"><small>رقم ولي الأمر</small><b dir="ltr">01556021783</b></div>
      <div class="data-cell date"><small>تاريخ الانضمام</small><b>21 أغسطس 2026</b></div>
      <div class="data-cell fee"><small>الرسوم الشهرية</small><b>400 ج.م</b></div>
      <div class="data-cell payment"><small>حالة السداد</small><b class="partial">جزئي • باقي 150</b></div>
    </div>
    <div class="student-actions">
      <a class="student-action whatsapp" href="https://wa.me/201009274894" target="_blank" rel="noopener">💬 واتساب</a>
      <button class="student-action edit" data-ui-action="edit-student">✏️ تعديل</button>
      <button class="student-action phone-edit" data-ui-action="edit-phone">📞 تعديل الهاتف</button>
      <button class="student-action exempt" data-ui-action="fee-exempt">🎁 إعفاء من الرسوم</button>
      <button class="student-action attendance" data-ui-action="student-attendance">🧑‍🏫 سجل الحضور</button>
      <button class="student-action fees" data-ui-action="student-fees">💰 الرسوم</button>
      <button class="student-action transfer" data-ui-action="transfer-student">↔ نقل</button>
      <form class="inline-archive-form" method="post" action="/archive-student.php"><?=$csrf->field()?><input type="hidden" name="student_id" value="2"><input type="hidden" name="archive_reason" value="حذف من داتا المجموعة"><button class="student-action delete" data-confirm="سيتم نقل الطالب إلى الأرشيف مع الاحتفاظ ببياناته. هل أنت متأكد؟">🗄️ أرشفة</button></form>
    </div>
  </article>

  <article class="student-card" data-student-search="جودي أحمد عيد 01016626347 الصف الأول الثانوي المجموعة أ">
    <div class="student-number">3</div>
    <div class="student-main">
      <div class="student-avatar coral">جع</div>
      <div class="student-name"><h3>جودي أحمد عيد</h3><div class="student-tags"><span class="tag grade">🎓 الصف الأول الثانوي</span><span class="tag group">المجموعة أ</span><span class="tag active">نشط</span></div></div>
    </div>
    <div class="student-data-grid">
      <div class="data-cell phone"><small>رقم الهاتف الأساسي</small><b dir="ltr">01016626347</b></div>
      <div class="data-cell phone"><small>رقم ولي الأمر</small><b>غير مسجل</b></div>
      <div class="data-cell date"><small>تاريخ الانضمام</small><b>28 أغسطس 2026</b></div>
      <div class="data-cell fee"><small>الرسوم الشهرية</small><b>400 ج.م</b></div>
      <div class="data-cell payment"><small>حالة السداد</small><b class="exempted">معفاة هذا الشهر</b></div>
    </div>
    <div class="student-actions">
      <a class="student-action whatsapp" href="https://wa.me/201016626347" target="_blank" rel="noopener">💬 واتساب</a>
      <button class="student-action edit" data-ui-action="edit-student">✏️ تعديل</button>
      <button class="student-action phone-edit" data-ui-action="edit-phone">📞 تعديل الهاتف</button>
      <button class="student-action exempt is-on" data-ui-action="fee-exempt">🎁 إلغاء الإعفاء</button>
      <button class="student-action attendance" data-ui-action="student-attendance">🧑‍🏫 سجل الحضور</button>
      <button class="student-action fees" data-ui-action="student-fees">💰 الرسوم</button>
      <button class="student-action transfer" data-ui-action="transfer-student">↔ نقل</button>
      <form class="inline-archive-form" method="post" action="/archive-student.php"><?=$csrf->field()?><input type="hidden" name="student_id" value="3"><input type="hidden" name="archive_reason" value="حذف من داتا المجموعة"><button class="student-action delete" data-confirm="سيتم نقل الطالب إلى الأرشيف مع الاحتفاظ ببياناته. هل أنت متأكد؟">🗄️ أرشفة</button></form>
    </div>
  </article>
  <div class="students-empty" hidden>لا يوجد طالب مطابق للبحث</div>
</div>
<div id="uiToast" class="ui-toast" role="status" aria-live="polite"></div>
