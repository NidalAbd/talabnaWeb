<?php

namespace Tests\Unit;

use App\Services\Apple\SignedTransactionVerifier;
use PHPUnit\Framework\TestCase;
use Tests\Support\AppleJwsFactory;

/** StoreKit 2 signed transactions: only what Apple signed, for this app and product, is accepted. */
class AppleSignedTransactionVerifierTest extends TestCase
{
    private AppleJwsFactory $apple;
    private SignedTransactionVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apple = new AppleJwsFactory();
        $this->verifier = new SignedTransactionVerifier($this->apple->rootPem);
    }

    public function test_a_transaction_signed_through_the_trusted_chain_is_accepted_and_readable(): void
    {
        $r = $this->verifier->verify($this->apple->transaction(['productId' => 'points_25']));

        $this->assertTrue($r['ok'], $r['error'] ?? '');
        $this->assertSame('points_25', $r['payload']['productId']);
        $this->assertSame('com.example.app', $r['payload']['bundleId']);
    }

    public function test_it_recognises_a_jws_and_not_a_legacy_receipt(): void
    {
        $this->assertTrue(SignedTransactionVerifier::looksLikeJws($this->apple->transaction()));
        $this->assertFalse(SignedTransactionVerifier::looksLikeJws(base64_encode(str_repeat('receipt', 50))));
        $this->assertFalse(SignedTransactionVerifier::looksLikeJws(''));
    }

    public function test_a_tampered_payload_is_rejected(): void
    {
        [$h, $p, $s] = explode('.', $this->apple->transaction(['productId' => 'points_1']));
        $forged = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
        $forged['productId'] = 'points_100';
        $p2 = rtrim(strtr(base64_encode(json_encode($forged)), '+/', '-_'), '=');

        $this->assertFalse($this->verifier->verify("$h.$p2.$s")['ok']);
    }

    public function test_a_chain_that_does_not_lead_to_apple_is_rejected_even_if_the_signature_is_valid(): void
    {
        $attacker = new AppleJwsFactory(); // their own root, intermediate and leaf: perfectly signed, but not Apple's

        $r = $this->verifier->verify($attacker->transaction());

        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('not issued by Apple', $r['error']);
    }

    public function test_the_real_apple_root_does_not_trust_a_home_made_chain(): void
    {
        $real = new SignedTransactionVerifier(); // trusts the bundled Apple Root CA - G3

        $this->assertFalse($real->verify($this->apple->transaction())['ok']);
    }

    public function test_a_signature_from_another_key_is_rejected(): void
    {
        $other = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC, 'private_key_bits' => 2048]);
        openssl_pkey_export($other, $pem);

        $this->assertFalse($this->verifier->verify($this->apple->transaction([], [], $pem))['ok']);
    }

    public function test_only_es256_is_accepted_never_none_or_hs256(): void
    {
        foreach (['none', 'HS256', 'RS256'] as $alg) {
            $r = $this->verifier->verify($this->apple->transaction([], ['alg' => $alg]));
            $this->assertFalse($r['ok'], $alg);
        }
        $noChain = $this->apple->transaction([], ['x5c' => []]);
        $this->assertFalse($this->verifier->verify($noChain)['ok']);
    }

    public function test_garbage_is_rejected_without_an_error(): void
    {
        foreach (['', 'abc', 'a.b.c', 'eyJhbGciOiJFUzI1NiJ9.e30.', str_repeat('x', 5000)] as $bad) {
            $this->assertFalse($this->verifier->verify($bad)['ok']);
        }
    }

    public function test_a_certificate_that_was_not_valid_when_signed_is_rejected(): void
    {
        $tenYearsAgo = (int) ((time() - 10 * 365 * 86400) * 1000);

        $r = $this->verifier->verify($this->apple->transaction(['signedDate' => $tenYearsAgo]));

        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('not valid when', $r['error']);
    }

    public function test_business_checks(): void
    {
        $t = $this->verifier->verify($this->apple->transaction())['payload'];
        $check = fn (array $override = [], string $bundle = 'com.example.app', string $product = 'points_10', ?string $tx = '2000000111111111', bool $sub = false) => $this->verifier->checkTransaction(array_merge($t, $override), $bundle, $product, $tx, $sub);

        $this->assertNull($check());
        $this->assertSame('Purchase is not for this app', $check(bundle: 'com.other.app'));
        $this->assertSame('Purchase is for a different product', $check(product: 'points_100'));
        $this->assertSame('Transaction does not match', $check(tx: '999'));
        $this->assertSame('Purchase was refunded', $check(['revocationDate' => 1_700_000_000_000]));
        $this->assertNull($check(tx: null), 'no transaction id claimed: not compared');
    }

    public function test_subscription_checks_need_a_future_expiry(): void
    {
        $sub = $this->verifier->verify($this->apple->transaction(['type' => 'Auto-Renewable Subscription', 'expiresDate' => (int) ((time() + 86400) * 1000)]))['payload'];
        $this->assertNull($this->verifier->checkTransaction($sub, 'com.example.app', 'points_10', null, true));

        $sub['expiresDate'] = (int) ((time() - 86400) * 1000);
        $this->assertSame('Subscription has expired', $this->verifier->checkTransaction($sub, 'com.example.app', 'points_10', null, true));

        unset($sub['expiresDate']);
        $this->assertSame('Subscription has no expiry', $this->verifier->checkTransaction($sub, 'com.example.app', 'points_10', null, true));
    }
}
