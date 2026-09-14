<?php
namespace Alprof\Core; final class Csrf { public function token(): string {return $_SESSION['csrf']??=bin2hex(random_bytes(32));} public function field(): string{return '<input type="hidden" name="csrf" value="'.htmlspecialchars($this->token(),ENT_QUOTES).'">';} public function verify(?string $token): bool{return is_string($token)&&hash_equals($this->token(),$token);}}
