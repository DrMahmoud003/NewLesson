document.addEventListener('click',e=>{const b=e.target.closest('[data-confirm]');if(b&&!confirm(b.dataset.confirm))e.preventDefault();});document.querySelectorAll('[data-money]').forEach(el=>el.textContent=Number(el.dataset.money).toLocaleString('ar-EG')+' ج.م');


// Student Data interactions — V2.3
(()=>{
 const search=document.getElementById('studentSearch');
 const cards=[...document.querySelectorAll('.student-card')];
 const empty=document.querySelector('.students-empty');
 const toast=document.getElementById('uiToast');
 const say=msg=>{if(!toast)return;toast.textContent=msg;toast.classList.add('show');clearTimeout(window.__toastTimer);window.__toastTimer=setTimeout(()=>toast.classList.remove('show'),2200)};
 if(search)search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();let shown=0;cards.forEach(c=>{const yes=c.dataset.studentSearch.toLowerCase().includes(q);c.hidden=!yes;if(yes)shown++});if(empty)empty.hidden=shown!==0});
 const group=document.querySelector('.group-identity'),picker=document.getElementById('groupColor');
 if(group&&picker){const saved=localStorage.getItem('alprof-group-color');if(saved){picker.value=saved;group.style.setProperty('--group-color',saved)}picker.addEventListener('input',()=>{group.style.setProperty('--group-color',picker.value);localStorage.setItem('alprof-group-color',picker.value)})}
 const labels={
  'add-student':'تم فتح نموذج إضافة طالب','group-whatsapp':'تم تجهيز رسالة المجموعة','group-report':'جاري تجهيز التقرير المفصل','apply-fees':'تم تطبيق قيمة الرسوم على الطلاب','clear-group':'تم تأكيد تفريغ المجموعة','rename-group':'يمكن الآن تعديل اسم الفرقة','edit-student':'تم فتح بيانات الطالب للتعديل','edit-phone':'تم فتح أرقام الهاتف للتعديل','fee-exempt':'تم تحديث حالة الإعفاء','student-attendance':'تم فتح سجل حضور الطالب','student-fees':'تم فتح كشف رسوم الطالب','transfer-student':'تم فتح قائمة المجموعات للنقل','delete-student':'تم تأكيد حذف الطالب'};
 document.addEventListener('click',e=>{const b=e.target.closest('[data-ui-action]');if(!b)return;if(b.dataset.confirm&&!confirm(b.dataset.confirm)){e.preventDefault();return}const a=b.dataset.uiAction;if(a==='fee-exempt'){b.classList.toggle('is-on');b.textContent=b.classList.contains('is-on')?'🎁 إلغاء الإعفاء':'🎁 إعفاء من الرسوم'}say(labels[a]||'تم تنفيذ الإجراء')});
})();


// Excel import interactions — V2.4
(()=>{const input=document.getElementById('studentsExcel'),name=document.getElementById('excelFileName'),zone=document.getElementById('excelDropzone');if(!input||!zone)return;input.addEventListener('change',()=>{name.textContent=input.files?.[0]?.name||'لم يتم اختيار ملف'});['dragenter','dragover'].forEach(x=>zone.addEventListener(x,e=>{e.preventDefault();zone.classList.add('drag')}));['dragleave','drop'].forEach(x=>zone.addEventListener(x,e=>{e.preventDefault();zone.classList.remove('drag')}));})();

// Archive search — V2.5
(()=>{const q=document.getElementById('archiveSearch');if(!q)return;q.addEventListener('input',()=>document.querySelectorAll('[data-archive-name]').forEach(r=>r.hidden=!r.dataset.archiveName.toLowerCase().includes(q.value.trim().toLowerCase())));})();

// Finance workspace — V2.6
(()=>{const tabs=[...document.querySelectorAll('[data-finance-tab]')],panels=[...document.querySelectorAll('[data-finance-panel]')];tabs.forEach(t=>t.addEventListener('click',()=>{tabs.forEach(x=>x.classList.toggle('active',x===t));panels.forEach(x=>x.classList.toggle('active',x.dataset.financePanel===t.dataset.financeTab))}));const search=document.getElementById('financeStudentSearch');if(search)search.addEventListener('input',()=>document.querySelectorAll('[data-finance-student]').forEach(r=>r.hidden=!r.dataset.financeStudent.toLowerCase().includes(search.value.trim().toLowerCase())));document.querySelectorAll('.paid-input input').forEach(i=>i.addEventListener('input',()=>{const row=i.closest('.finance-student-row'),total=Number(i.dataset.assessed)+Number(i.dataset.arrears),paid=Math.max(0,Number(i.value)||0),remain=Math.max(0,total-paid),out=row.querySelector('[data-remaining]'),state=row.querySelector('[data-payment-state]'),box=state.closest('.payment-state');out.textContent=remain.toLocaleString('ar-EG');box.classList.remove('paid','partial','unpaid');if(remain<=0){state.textContent='تم السداد';box.classList.add('paid')}else if(paid>0){state.textContent='سداد جزئي';box.classList.add('partial')}else{state.textContent='لم يسدد';box.classList.add('unpaid')}}));const chips=[...document.querySelectorAll('[data-report-filter]')],rows=[...document.querySelectorAll('[data-report-student]')];chips.forEach(c=>c.addEventListener('click',()=>{chips.forEach(x=>x.classList.remove('active'));c.classList.add('active');const f=c.dataset.reportFilter;rows.forEach(r=>r.hidden=(f==='unpaid'&&r.dataset.reportStatus!=='unpaid')||(f==='exempt'&&r.dataset.reportStatus!=='exempt'))}));const rs=document.getElementById('financeReportSearch');if(rs)rs.addEventListener('input',()=>rows.forEach(r=>r.hidden=!r.dataset.reportStudent.toLowerCase().includes(rs.value.trim().toLowerCase())));document.getElementById('resetFinanceFilters')?.addEventListener('click',()=>{chips.forEach(x=>x.classList.remove('active'));chips[0]?.classList.add('active');rows.forEach(r=>r.hidden=false);if(rs)rs.value=''})})();
function applyFinanceFilters(){const g=document.getElementById('financeGroup')?.value,m=document.getElementById('financeMonth')?.value;if(g&&m)location.href='/?module=finance&group_id='+encodeURIComponent(g)+'&month='+encodeURIComponent(m)}
