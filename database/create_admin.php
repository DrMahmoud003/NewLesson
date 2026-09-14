<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}require dirname(__DIR__).'/app/bootstrap.php';
global $db;
$email=$argv[1]??null;$password=$argv[2]??null;$name=$argv[3]??'مدير النظام';if(!$email||!$password||strlen($password)<12){fwrite(STDERR,"Usage: php database/create_admin.php email password(12+) [name]\n");exit(1);}$algo=defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT;$q=$db->prepare("INSERT INTO users(name,email,password_hash,role,status,created_at) VALUES(?,?,?,'manager','active',NOW())");$q->execute([$name,mb_strtolower($email),password_hash($password,$algo)]);echo "Manager created securely.\n";
