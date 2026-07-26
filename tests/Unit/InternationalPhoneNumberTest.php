<?php

namespace Tests\Unit;

use App\Rules\InternationalPhoneNumber;
use PHPUnit\Framework\TestCase;

class InternationalPhoneNumberTest extends TestCase
{
    /**
     * Run the rule against a value; returns the failure message or null when valid.
     */
    private function fails(string $value, bool $required = true): ?string
    {
        $rule = $required ? new InternationalPhoneNumber : InternationalPhoneNumber::nullable();
        $message = null;
        $rule->validate('phone', $value, function ($m) use (&$message) {
            $message = $m;
        });

        return $message;
    }

    public function test_accepts_algerian_mobile_shapes(): void
    {
        $this->assertNull($this->fails('0770308424'));
        $this->assertNull($this->fails('770308424'), 'bare national number (country selector UI)');
        $this->assertNull($this->fails('+213770308424'));
        $this->assertNull($this->fails('+2130770308424'), 'leading 0 kept after country code');
        $this->assertNull($this->fails('00213770308424'));
    }

    public function test_accepts_numbers_with_invisible_characters(): void
    {
        // Bidi marks + non-breaking space, as pasted from contacts on Arabic-locale phones
        $this->assertNull($this->fails("\u{200E}+213\u{00A0}770\u{200F}308 424"));
        $this->assertNull($this->fails('+33 6 76 08 54 41'));
        $this->assertNull($this->fails('770-308-424'));
    }

    public function test_accepts_eastern_arabic_digits(): void
    {
        $this->assertNull($this->fails('٠٧٧٠٣٠٨٤٢٤'));
        $this->assertNull($this->fails('٧٧٠٣٠٨٤٢٤'));
    }

    public function test_rejects_invalid_numbers(): void
    {
        $this->assertNotNull($this->fails('12345'));
        $this->assertNotNull($this->fails('abc'));
        $this->assertNotNull($this->fails('+999123456789'), 'unknown country code');
        $this->assertNotNull($this->fails(''));
        $this->assertNull($this->fails('', required: false));
    }

    public function test_split(): void
    {
        $this->assertSame(['+213', '0770308424'], InternationalPhoneNumber::split('770308424'));
        $this->assertSame(['+213', '0770308424'], InternationalPhoneNumber::split('0770308424'));
        $this->assertSame(['+213', '0770308424'], InternationalPhoneNumber::split('+213770308424'));
        $this->assertSame(['+213', '0770308424'], InternationalPhoneNumber::split('+2130770308424'));
        $this->assertSame(['+33', '676085441'], InternationalPhoneNumber::split('+33676085441'));
        $this->assertSame([null, ''], InternationalPhoneNumber::split(null));
    }

    public function test_normalize(): void
    {
        $this->assertSame('0770308424', InternationalPhoneNumber::normalize('770308424'));
        $this->assertSame('0770308424', InternationalPhoneNumber::normalize('+213770308424'));
        $this->assertSame('0770308424', InternationalPhoneNumber::normalize('+2130770308424'));
        $this->assertSame('+33676085441', InternationalPhoneNumber::normalize('+33 676 085 441'));
        $this->assertNull(InternationalPhoneNumber::normalize(null));
        $this->assertNull(InternationalPhoneNumber::normalize('  '));
    }
}
