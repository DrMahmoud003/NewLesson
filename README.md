# منصة البروف التعليمية — Modular cPanel Edition
نسخة منظمة من المنصة للعمل على PHP 8.1+ وMySQL 8/MariaDB 10.6+.

## المبادئ
- لا توجد كلمات سر داخل الكود.
- لوحة الإدارة لا تُحمّل قبل إنشاء جلسة موثقة.
- كل قسم في ملف مستقل.
- كلمات السر Hash باستخدام Argon2id عند توفره.
- استعلام ولي الأمر يعتمد على بصمة رقم الهاتف لا البحث بالنص الصريح.
- الصلاحيات تتحقق على الخادم.

راجع `DEPLOYMENT-CPANEL.md` و`SECURITY.md` قبل النشر.


## Repository
Target: `git@github.com:DrMahmoud003/NewLesson.git` (Private).
PHP target: 8.4. The CI workflow blocks deployment when dependency audit, syntax, static analysis, or secret scanning fails.
