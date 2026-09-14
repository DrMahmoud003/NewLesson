# رفع المشروع على cPanel
1. أنشئ قاعدة MySQL ومستخدمًا بصلاحيات القاعدة فقط.
2. استورد `database/schema.sql` عبر phpMyAdmin.
3. ارفع المشروع خارج `public_html` إن أمكن: `/home/USER/alprof-platform`.
4. اجعل Document Root للنطاق يشير إلى `/home/USER/alprof-platform/public`.
5. انسخ `.env.example` إلى `.env` وأدخل بيانات القاعدة ومفتاحًا عشوائيًا 64 حرفًا.
6. اضبط صلاحيات `.env` إلى 600 والمجلدات 750 والملفات 640 حسب إعداد الخادم.
7. أنشئ المدير عبر Terminal في cPanel:
   `php database/create_admin.php admin@example.com 'A-Long-Random-Password' 'مدير النظام'`
8. فعّل SSL ثم اربط Cloudflare على Full (Strict).
9. لا ترفع `.env` إلى GitHub، ولا تضع أسرار cPanel في Actions مباشرة؛ استخدم GitHub Secrets.
10. اجعل `APP_DEBUG=false` في الإنتاج.

إذا تعذر تغيير Document Root، تواصل مع شركة الاستضافة؛ لا تنقل app/config/database إلى public_html.
