<?php
namespace Alprof\Services; use DateTimeImmutable;
final class FeeCalculatorService { public function monthlyFee(float $base,string $joined,string $month):float{$j=new DateTimeImmutable($joined);$m=new DateTimeImmutable($month.'-01');if($j->format('Y-m')<$m->format('Y-m'))return round($base,2);if($j->format('Y-m')>$m->format('Y-m'))return 0;$d=(int)$j->format('j');$rate=$d<=7?1:($d<=14?.75:($d<=21?.5:.25));return round($base*$rate,2);} }
