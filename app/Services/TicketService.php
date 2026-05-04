<?php

namespace App\Services;

use InvalidArgumentException;

class TicketService
{
    /**
     * 36^6 => 2,176,782,336 possible tickets (6 chars base36).
     * Enough space for billions of bugs.
     */
    private const MOD = 2176782336; // 36 ** 6

    /**
     * Multiplier must be coprime with MOD (not divisible by 2 or 3).
     * This gives a reversible permutation when used with modular inverse.
     */
    // IMPORTANT: Must be coprime with MOD (= 2^6 * 3^6). So it must NOT be divisible by 2 or 3.
    private const MULTIPLIER = 1103515247; // odd, sum digits=29 => not divisible by 3

    /**
     * Generate a human friendly ticket from bug id.
     * Example: BUG-8F3K2L
     */
    public function fromBugId(int $bugId): string
    {
        if ($bugId <= 0 || $bugId >= self::MOD) {
            throw new InvalidArgumentException('Bug ID out of ticket range.');
        }

        $offset = $this->offset();
        $x = (int) ((($bugId * self::MULTIPLIER) + $offset) % self::MOD);

        $code = strtoupper(str_pad(base_convert((string) $x, 10, 36), 6, '0', STR_PAD_LEFT));

        return 'BUG-'.$code;
    }

    /**
     * Parse a ticket into bug id.
     * Accepts:
     * - BUG-8F3K2L / #BUG-8F3K2L (new)
     * - BUG-000123 / #BUG-000123 (legacy numeric)
     */
    public function toBugId(string $ticket): int
    {
        $normalized = trim($ticket);
        $normalized = ltrim($normalized, '#');

        // Support legacy numeric: BUG-000123
        if (preg_match('/^BUG\-(\d{1,10})$/i', $normalized, $m)) {
            $id = (int) ltrim($m[1], '0');
            if ($id <= 0) {
                throw new InvalidArgumentException('Invalid ticket format.');
            }

            return $id;
        }

        // New format: BUG-XXXXXX (base36, 6 chars)
        if (! preg_match('/^BUG\-([A-Za-z0-9]{6})$/', $normalized, $m)) {
            throw new InvalidArgumentException('Invalid ticket format.');
        }

        $code = strtoupper($m[1]);
        $x = (int) base_convert($code, 36, 10);

        $offset = $this->offset();
        $inv = $this->multiplierInverse();
        $id = (int) (((($x - $offset) % self::MOD + self::MOD) % self::MOD) * $inv % self::MOD);

        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid ticket format.');
        }

        return $id;
    }

    private function offset(): int
    {
        // Deterministic per-app secret offset derived from APP_KEY
        $key = (string) config('app.key');

        // crc32 returns unsigned int in string sometimes; normalize
        $crc = sprintf('%u', crc32($key));

        return (int) (((int) $crc) % self::MOD);
    }

    private function multiplierInverse(): int
    {
        // Extended Euclidean Algorithm to find modular inverse of MULTIPLIER mod MOD.
        $a = self::MULTIPLIER;
        $m = self::MOD;

        $t = 0;
        $newT = 1;
        $r = $m;
        $newR = $a;

        while ($newR !== 0) {
            $q = intdiv($r, $newR);
            [$t, $newT] = [$newT, $t - $q * $newT];
            [$r, $newR] = [$newR, $r - $q * $newR];
        }

        if ($r > 1) {
            throw new InvalidArgumentException('Ticket multiplier is not invertible.');
        }

        if ($t < 0) {
            $t += $m;
        }

        return (int) $t;
    }
}
