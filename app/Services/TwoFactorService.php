<?php
namespace Alprof\Services;

use PDO;
use RuntimeException;
use ParagonIE\ConstantTime\Base32;

final class TwoFactorService
{
    public function __construct(private PDO $db) {}

    public function generateSecret(): string
    {
        return Base32::encodeUpper(random_bytes(20));
    }

    public function getOtpauthUri(string $secret, string $email, string $issuer = 'AlProf'): string
    {
        return sprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s&digits=6&period=30', $issuer, $email, $secret, $issuer);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $time = (int) (time() / 30);
        for ($offset = -1; $offset <= 1; $offset++) {
            $counter = $time + $offset;
            $hash = hash_hmac('sha1', pack('N*', 0, $counter), $this->base32Decode($secret), true);
            $pack = unpack('N', substr($hash, -4));
            $otp = ($pack[1] & 0x7fffffff) % 1000000;
            if (hash_equals(str_pad((string) $otp, 6, '0', STR_PAD_LEFT), $code)) {
                return true;
            }
        }
        return false;
    }

    private function base32Decode(string $input): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper(rtrim($input, '='));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }
        return $output;
    }

    public function isEnabled(int $userId): bool
    {
        $q = $this->db->prepare('SELECT two_factor_enabled FROM users WHERE id = ?');
        $q->execute([$userId]);
        return (int) ($q->fetchColumn() ?: 0) === 1;
    }

    public function getSecret(int $userId): ?string
    {
        $q = $this->db->prepare('SELECT two_factor_secret FROM users WHERE id = ?');
        $q->execute([$userId]);
        return $q->fetchColumn() ?: null;
    }

    public function enable(int $userId, string $secret): void
    {
        $q = $this->db->prepare('UPDATE users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE id = ?');
        $q->execute([$secret, $userId]);
    }

    public function disable(int $userId): void
    {
        $q = $this->db->prepare('UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = ?');
        $q->execute([$userId]);
    }
}
