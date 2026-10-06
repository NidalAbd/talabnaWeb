<?php

namespace App\Services;

/**
 * Release C (2026-10-07): spots messages that look like common marketplace scams so the receiver sees a warning under
 * them. Text rules only (no AI, no cost). Returns the warning kind or null.
 *  - code:     asks for a verification / OTP code (account takeover)
 *  - advance:  asks for payment, a deposit or shipping fee before meeting
 *  - transfer: money-transfer services, gift cards or crypto
 *  - link:     a payment or "delivery" link outside the app
 */
class ChatSafety
{
    private const RULES = [
        'code' => [
            '/\b(otp|verification code|confirm(ation)? code|activation code|send (me )?the code|6[- ]digit code)\b/i',
            '/(رمز التحقق|كود التحقق|كود التفعيل|ارسل(ي)? (لي )?الكود|ابعت(لي)? الكود|رمز التفعيل|الرمز اللي وصلك|الكود اللي وصلك)/u',
        ],
        'advance' => [
            '/\b(pay (first|in advance|upfront)|advance payment|deposit (first|before)|shipping fee first|send (the )?money first)\b/i',
            '/(ادفع (اول|أول)|دفعة مقدمة|عربون|حول(ي)? (المبلغ|الفلوس) (اول|أول)|رسوم الشحن (اول|أول)|ادفع مقدما|مقدماً)/u',
        ],
        'transfer' => [
            '/\b(western union|moneygram|gift ?cards?|itunes card|google play card|steam card|bitcoin|usdt|crypto(currency)?)\b/i',
            '/(ويسترن يونيون|موني ?جرام|بطاقة (ايتونز|آيتونز|جوجل بلاي|هدية)|بيتكوين|عملة رقمية|يو ?اس ?دي ?تي)/u',
        ],
        'link' => [
            '/https?:\/\/[^\s]*(pay|payment|checkout|delivery|courier|aramex|dhl|fedex|bank|wallet|verify|login)[^\s]*/i',
        ],
    ];

    public static function warningFor(string $text): ?string
    {
        if (trim($text) === '') {
            return null;
        }
        foreach (self::RULES as $kind => $patterns) {
            foreach ($patterns as $p) {
                if (preg_match($p, $text) === 1) {
                    return $kind;
                }
            }
        }

        return null;
    }
}
