<?php
use Alprof\Services\FinanceService;
$finance=new FinanceService($db);$groups=$finance->groups();$groupId=(int)($_GET['group_id']??($groups[0]['id']??0));$month=(string)($_GET['month']??date('Y-m'));if(!preg_match('/^\d{4}-\d{2}$/',$month))$month=date('Y-m');$rows=$groupId?$finance->rows($groupId,$month.'-01'):[];$stats=$finance->stats($rows);$flash=$_SESSION['finance_flash']??null;unset($_SESSION['finance_flash']);
function feeState(array $r):array{if($r['status']==='exempt')return['معفى','exempt'];if((float)$r['remaining']<=0)return['تم السداد','paid'];if((float)$r['paid']>0)return['سداد جزئي','partial'];return['لم يسدد','unpaid'];}
?>
<div class="page-head finance-head"><div><span class="eyebrow finance">إدارة مالية دقيقة</span><h1>ماليات المجموعة</h1><p>تسجيل الرسوم، متابعة المتبقي، وإصدار التقارير المالية</p></div><span class="version-badge">V. 2.6</span></div>
<nav class="finance-tabs"><button class="active" data-finance-tab="entry">💳 تسجيل مصاريف الطلاب</button><button data-finance-tab="analytics">📊 التقارير والتحليلات</button></nav>
<?php if($flash):?><div class="import-flash <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="finance-panel active" data-finance-panel="entry">
 <div class="finance-filter-card"><div><label>المجموعة<select id="financeGroup" onchange="applyFinanceFilters()"><?php foreach($groups as $g):?><option value="<?=(int)$g['id']?>" <?=$groupId===(int)$g['id']?'selected':''?>><?=htmlspecialchars($g['grade_name'].' — '.$g['name'])?></option><?php endforeach;?></select></label><label>الشهر<input id="financeMonth" type="month" value="<?=htmlspecialchars($month)?>" onchange="applyFinanceFilters()"></label></div><p>💡 المبلغ المقرر يُجلب تلقائيًا من رسوم الطالب، أو من إعدادات المجموعة عند عدم وجود رسوم خاصة.</p></div>
 <div class="finance-kpis"><article><span>📋 إجمالي المقرر</span><b><?=number_format($stats['due'])?> ج.م</b></article><article><span>✅ إجمالي المسدد</span><b><?=number_format($stats['paid'])?> ج.م</b></article><article><span>⏳ إجمالي المتبقي</span><b><?=number_format($stats['remaining'])?> ج.م</b></article><article><span>🚫 غير المسددين</span><b><?=(int)$stats['unpaid']?> طلاب</b></article></div>
 <label class="finance-search">🔎 <input id="financeStudentSearch" type="search" placeholder="ابحث عن اسم الطالب لتسجيل رسومه..."><kbd><?=count($rows)?> طالب</kbd></label>
 <div class="finance-rows" id="financeRows"><?php if(!$rows):?><div class="empty-state">لا توجد بيانات طلاب لهذه المجموعة.</div><?php endif;?><?php foreach($rows as $i=>$r):[$stateLabel,$stateClass]=feeState($r);?>
  <form class="finance-student-row" method="post" action="/save-student-fee.php" data-finance-student="<?=htmlspecialchars($r['name'])?>">
   <?=$csrf->field()?><input type="hidden" name="student_id" value="<?=(int)$r['id']?>"><input type="hidden" name="group_id" value="<?=$groupId?>"><input type="hidden" name="month" value="<?=htmlspecialchars($month)?>">
   <div class="finance-student"><span><?=$i+1?></span><div><b><?=htmlspecialchars($r['name'])?></b><small>الرسوم الشهرية</small></div></div>
   <label class="money-box assessed"><small>المقرر</small><strong><?=number_format((float)$r['assessed'])?></strong><em>ج.م</em></label>
   <label class="money-box paid-input"><small>المسدد</small><input name="paid_amount" type="number" min="0" step="0.01" value="<?=htmlspecialchars((string)$r['paid'])?>" data-assessed="<?=$r['assessed']?>" data-arrears="<?=$r['arrears']?>"></label>
   <div class="money-box remaining"><small>المتبقي</small><strong data-remaining><?=number_format((float)$r['remaining'])?></strong><em>ج.م</em></div>
   <div class="payment-state <?=$stateClass?>"><small>الحالة</small><b data-payment-state><?=$stateLabel?></b></div>
   <label class="payment-date"><small>تاريخ السداد</small><input name="paid_date" type="date" value="<?=htmlspecialchars($r['paid_date']?:date('Y-m-d'))?>"></label>
   <label class="confirm-payment"><input name="confirm" type="checkbox" required><span>تأكيد</span></label>
   <button class="save-payment" type="submit">💾 حفظ وتحديث</button>
  </form><?php endforeach;?></div>
</section>
<section class="finance-panel" data-finance-panel="analytics">
 <div class="reports-hero"><div><span>📊</span><div><h2>تقارير الماليات والتحليلات</h2><p>اختر نوع التقرير ثم عاين النتائج قبل التصدير أو المشاركة.</p></div></div><button class="reset-report" id="resetFinanceFilters">↻ إعادة ضبط الفلاتر</button></div>
 <div class="report-filter-groups">
  <div><b>تقارير زمنية وتدفقية</b><div class="report-chips"><button class="active" data-report-filter="month">📅 تقرير شهر</button><button data-report-filter="daily">🗓️ تقرير يومي</button></div></div>
  <div><b>تقارير حسب الكيان</b><div class="report-chips"><button data-report-filter="student">👤 تقرير طالب</button><button data-report-filter="group">👥 تقرير المجموعة</button><button data-report-filter="grade">🏫 تقرير الفصل</button><button data-report-filter="parent">👨‍👩‍👧 تقرير ولي أمر</button></div></div>
  <div><b>تقارير الحالات المالية</b><div class="report-chips"><button data-report-filter="unpaid">❌ لم يسدد</button><button data-report-filter="exempt">✅ المعفيون من الرسوم</button><button data-report-filter="manual">🧾 كشف السداد اليدوي</button></div></div>
 </div>
 <div class="report-controls"><label>الشهر<input type="month" value="<?=htmlspecialchars($month)?>"></label><label>بحث<input id="financeReportSearch" placeholder="اسم الطالب..."></label><div class="export-tools"><a class="export-btn excel" href="/export-finance.php?format=xlsx&group_id=<?=$groupId?>&month=<?=urlencode($month)?>">Excel</a><a class="export-btn pdf" href="/export-finance.php?format=pdf&group_id=<?=$groupId?>&month=<?=urlencode($month)?>">PDF</a><button class="share-report" data-ui-action="share-finance-report">↗ مشاركة</button></div></div>
 <div class="report-summary"><article><small>المقرر</small><b><?=number_format($stats['due'])?> ج.م</b></article><article><small>المسدد</small><b><?=number_format($stats['paid'])?> ج.م</b></article><article><small>المتبقي</small><b><?=number_format($stats['remaining'])?> ج.م</b></article><article><small>نسبة التحصيل</small><b><?=$stats['due']?round($stats['paid']/$stats['due']*100,1):0?>%</b></article></div>
 <div class="finance-report-table"><table><thead><tr><th>الطالب</th><th>المقرر</th><th>المسدد</th><th>المتبقي</th><th>الحالة</th><th>تاريخ السداد</th></tr></thead><tbody><?php foreach($rows as $r):[$stateLabel,$stateClass]=feeState($r);?><tr data-report-student="<?=htmlspecialchars($r['name'])?>" data-report-status="<?=$stateClass?>"><td><?=htmlspecialchars($r['name'])?></td><td><?=number_format((float)$r['assessed'])?> ج.م</td><td><?=number_format((float)$r['paid'])?> ج.م</td><td><?=number_format((float)$r['remaining'])?> ج.م</td><td><span class="table-state <?=$stateClass?>"><?=$stateLabel?></span></td><td><?=htmlspecialchars($r['paid_date']??'—')?></td></tr><?php endforeach;?></tbody></table></div>
</section>
<div id="financeToast" class="ui-toast" role="status"></div>
