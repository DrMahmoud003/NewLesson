<?php
namespace Alprof\Services;
use PDO;
final class FinanceService{
 public function __construct(private PDO $db){}
 public function groups():array{return$this->db->query('SELECT g.id,g.name,g.monthly_fee,gl.name grade_name FROM groups g JOIN grade_levels gl ON gl.id=g.grade_level_id ORDER BY gl.sort_order,g.name')->fetchAll()?:[];}
 public function rows(int $groupId,string $month):array{$q=$this->db->prepare("SELECT s.id,s.name,s.fee_exempt,COALESCE(s.monthly_fee_override,g.monthly_fee,0) assessed,COALESCE(mf.paid_amount,0) paid,COALESCE(mf.previous_arrears,0) arrears,COALESCE(mf.status,IF(s.fee_exempt=1,'exempt','unpaid')) status,(SELECT MAX(DATE(p.paid_at)) FROM payments p WHERE p.student_id=s.id AND p.reversed_at IS NULL AND DATE_FORMAT(p.paid_at,'%Y-%m')=DATE_FORMAT(?,'%Y-%m')) paid_date FROM students s JOIN groups g ON g.id=s.group_id LEFT JOIN monthly_fees mf ON mf.student_id=s.id AND mf.fee_month=? WHERE s.group_id=? AND s.status='active' ORDER BY s.name");$q->execute([$month,$month,$groupId]);return array_map(function($r){$r['remaining']=max(0,(float)$r['assessed']+(float)$r['arrears']-(float)$r['paid']);return$r;},$q->fetchAll()?:[]);}
 public function stats(array $rows):array{$due=$paid=$remaining=0;$unpaid=0;foreach($rows as $r){$due+=(float)$r['assessed']+(float)$r['arrears'];$paid+=(float)$r['paid'];$remaining+=(float)$r['remaining'];if(in_array($r['status'],['unpaid','partial'],true))$unpaid++;}return compact('due','paid','remaining','unpaid');}
}
