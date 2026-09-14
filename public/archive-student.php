<?php
declare(strict_types=1);require dirname(__DIR__).'/app/bootstrap.php';
global $db,$auth,$csrf;
$auth->requireAuth();
if($_SERVER['REQUEST_METHOD']!=='POST'||!$auth->can('students.manage')){http_response_code(403);exit('غير مصرح.');}
if(!$csrf->verify($_POST['csrf']??null)){http_response_code(419);exit('انتهت الجلسة.');}
$id=filter_var($_POST['student_id']??null,FILTER_VALIDATE_INT);$reason=mb_substr(trim((string)($_POST['archive_reason']??'حذف من المجموعة')),0,255);
if($id){$q=$db->prepare("UPDATE students SET status='archived',archived_at=NOW(),archived_by=?,archive_reason=? WHERE id=? AND status<>'archived'");$q->execute([(int)$auth->user()['id'],$reason,(int)$id]);$auth->audit((int)$auth->user()['id'],'student_archived',['student_id'=>(int)$id]);}
$_SESSION['group_flash']=['type'=>'success','message'=>'تم نقل الطالب إلى أرشيف المجموعة مع الاحتفاظ بكامل بياناته.'];header('Location: /?module=group-archive');exit;
