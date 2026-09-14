<?php
declare(strict_types=1);require dirname(__DIR__).'/app/bootstrap.php';
global $db,$auth,$csrf;
$auth->requireAuth();
if($_SERVER['REQUEST_METHOD']!=='POST'||!$auth->can('students.manage')){http_response_code(403);exit;}
if(!$csrf->verify($_POST['csrf']??null)){http_response_code(419);exit('انتهت الجلسة.');}
$id=filter_var($_POST['student_id']??null,FILTER_VALIDATE_INT);$g=filter_var($_POST['group_id']??null,FILTER_VALIDATE_INT);
if($id){$q=$db->prepare("UPDATE students SET status='active',archived_at=NULL,archived_by=NULL,archive_reason=NULL WHERE id=? AND status='archived'");$q->execute([(int)$id]);$auth->audit((int)$auth->user()['id'],'student_restored',['student_id'=>(int)$id]);}
$_SESSION['group_flash']=['type'=>'success','message'=>'تمت استعادة الطالب إلى داتا المجموعة.'];header('Location: /?module=group-archive'.($g?'&group_id='.$g:''));exit;
