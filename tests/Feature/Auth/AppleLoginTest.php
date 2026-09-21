<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Crypt;
use App\Services\Auth\AppleTokenRevoker;
use App\Services\Auth\AppleIdTokenVerifier;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/**
 * Sign in with Apple. Tokens are signed with a throwaway RSA key and Apple's JWKS is faked,
 * so no network or real Apple account is needed.
 */
class AppleLoginTest extends TestCase
{
    use MigratesTolerantly;

    private const KID = 'test-key-1';
    private $privateKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        Cache::flush();
        config(['services.apple.client_id' => 'com.talabna.talabna', 'services.apple.services_id' => null]);

        // Passport needs a personal access client to mint tokens.
        Artisan::call('passport:client', ['--personal' => true, '--name' => 'Test', '--no-interaction' => true]);

        $this->privateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $d = openssl_pkey_get_details($this->privateKey);
        Http::fake([AppleIdTokenVerifier::JWKS_URL => Http::response(['keys' => [[
            'kty' => 'RSA', 'kid' => self::KID, 'use' => 'sig', 'alg' => 'RS256',
            'n' => $this->b64url($d['rsa']['n']), 'e' => $this->b64url($d['rsa']['e']),
        ]]])]);
    }

    public function test_new_user_is_created_with_token(): void
    {
        $res = $this->postJson('/api/auth/apple', [
            'identity_token' => $this->token(['sub' => 'apple-1', 'email' => 'Parent@Example.com']),
            'name' => 'Jane Doe',
        ])->assertOk()->assertJsonStructure(['token', 'is_new_user', 'user' => ['id', 'name', 'email']]);

        $this->assertTrue($res->json('is_new_user'));
        $this->assertDatabaseHas('users', ['email' => 'parent@example.com', 'apple_id' => 'apple-1', 'name' => 'Jane Doe', 'auth_type' => 'apple']);
        $this->assertDatabaseHas('notifications', ['type' => 'login']);
        $this->assertArrayNotHasKey('apple_id', $res->json('user'));
    }

    public function test_returning_user_is_not_duplicated(): void
    {
        $first = $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-2', 'email' => 'back@example.com'])])->assertOk();
        $second = $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-2', 'email' => 'back@example.com'])])->assertOk();

        $this->assertFalse($second->json('is_new_user'));
        $this->assertSame($first->json('user.id'), $second->json('user.id'));
        $this->assertSame(1, User::where('apple_id', 'apple-2')->count());
    }

    public function test_returning_user_without_email_claim_still_signs_in(): void
    {
        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-7', 'email' => 'x@example.com'])])->assertOk();

        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-7', 'email' => null])])
            ->assertOk()->assertJsonPath('is_new_user', false);
    }

    public function test_links_to_existing_account_when_apple_verified_the_email(): void
    {
        $id = $this->insertUser(['email' => 'same@example.com', 'google_id' => 'g-1']);

        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-3', 'email' => 'same@example.com'])])
            ->assertOk()->assertJsonPath('user.id', $id)->assertJsonPath('is_new_user', false);

        $this->assertSame('apple-3', User::find($id)->apple_id);
    }

    public function test_does_not_link_by_email_when_apple_did_not_verify_it(): void
    {
        $id = $this->insertUser(['email' => 'victim@example.com']);

        $res = $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-4', 'email' => 'victim@example.com', 'email_verified' => 'false'])]);

        $res->assertStatus(409);
        $this->assertNull(User::find($id)->apple_id);
    }

    public function test_rejects_wrong_audience_expiry_issuer_and_foreign_signature(): void
    {
        $post = fn ($t) => $this->postJson('/api/auth/apple', ['identity_token' => $t]);

        $post($this->token(['aud' => 'com.someone.else']))->assertStatus(401);
        $post($this->token(['exp' => time() - 60]))->assertStatus(401);
        $post($this->token(['iss' => 'https://evil.example.com']))->assertStatus(401);
        $post($this->token([], openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA])))->assertStatus(401);
        $this->assertSame(0, User::count());
    }

    public function test_rejects_alg_none_and_hmac_tokens(): void
    {
        $claims = $this->claims([]);
        $none = $this->b64url(json_encode(['alg' => 'none', 'kid' => self::KID])).'.'.$this->b64url(json_encode($claims)).'.';
        $this->postJson('/api/auth/apple', ['identity_token' => $none])->assertStatus(401);

        $hs = $this->b64url(json_encode(['alg' => 'HS256', 'kid' => self::KID])).'.'.$this->b64url(json_encode($claims));
        $this->postJson('/api/auth/apple', ['identity_token' => $hs.'.'.$this->b64url(hash_hmac('sha256', $hs, 'x', true))])->assertStatus(401);
    }

    public function test_nonce_must_match(): void
    {
        $token = $this->token(['nonce' => hash('sha256', 'raw')]);

        $this->postJson('/api/auth/apple', ['identity_token' => $token, 'nonce' => 'raw'])->assertOk();
        $this->postJson('/api/auth/apple', ['identity_token' => $token, 'nonce' => 'other'])->assertStatus(401);
    }

    public function test_new_user_without_an_email_gets_a_clear_error(): void
    {
        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-6', 'email' => null])])->assertStatus(422);
        $this->assertSame(0, User::count());
    }

    public function test_banned_account_cannot_sign_in(): void
    {
        $this->insertUser(['email' => 'ban@example.com', 'apple_id' => 'apple-8', 'is_active' => 'banned']);

        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-8', 'email' => 'ban@example.com'])])->assertStatus(403);
    }

    public function test_requires_a_token(): void
    {
        $this->postJson('/api/auth/apple', [])->assertStatus(422);
    }

    // ── Apple token revocation on account deletion (guideline 5.1.1(v)) ──────────

    private function configureAppleKey(): void
    {
        $ec = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC, 'private_key_bits' => 2048]);
        openssl_pkey_export($ec, $pem);
        config([
            'services.apple.team_id' => 'TEAM123456',
            'services.apple.key_id' => 'KEY1234567',
            'services.apple.private_key' => $pem,
            'services.apple.private_key_path' => null,
        ]);
        $this->applePublicKey = openssl_pkey_get_details($ec)['key'];
    }

    private ?string $applePublicKey = null;

    public function test_client_secret_is_a_valid_es256_jwt(): void
    {
        $this->configureAppleKey();
        $jwt = app(AppleTokenRevoker::class)->clientSecret(1_700_000_000);

        [$h, $p, $s] = explode('.', $jwt);
        $header = json_decode(base64_decode(strtr($h, '-_', '+/')), true);
        $claims = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
        $this->assertSame('ES256', $header['alg']);
        $this->assertSame('KEY1234567', $header['kid']);
        $this->assertSame('TEAM123456', $claims['iss']);
        $this->assertSame('https://appleid.apple.com', $claims['aud']);
        $this->assertSame(config('services.apple.client_id'), $claims['sub']);
        $this->assertSame(1_700_000_300, $claims['exp']);

        $raw = base64_decode(strtr($s, '-_', '+/'));
        $this->assertSame(64, strlen($raw));
        $this->assertSame(1, openssl_verify("$h.$p", AppleTokenRevoker::class::rawToDer($raw), $this->applePublicKey, OPENSSL_ALGO_SHA256));
    }

    public function test_authorization_code_is_exchanged_and_the_refresh_token_stored_encrypted(): void
    {
        $this->configureAppleKey();
        Http::fake([AppleTokenRevoker::class::TOKEN_URL => Http::response(['refresh_token' => 'rt-1'])]);

        $res = $this->postJson('/api/auth/apple', [
            'identity_token' => $this->token(['sub' => 'apple-rev-1', 'email' => 'rev1@example.com']),
            'authorization_code' => 'code-abc',
        ])->assertOk();

        Http::assertSent(fn ($r) => $r->url() === AppleTokenRevoker::class::TOKEN_URL && $r['code'] === 'code-abc' && $r['grant_type'] === 'authorization_code');
        $user = User::where('apple_id', 'apple-rev-1')->first();
        $this->assertNotNull($user->apple_refresh_token);
        $this->assertNotSame('rt-1', $user->apple_refresh_token);
        $this->assertSame('rt-1', Crypt::decryptString($user->apple_refresh_token));
        $this->assertStringNotContainsString('apple_refresh_token', $res->getContent());
    }

    public function test_nothing_is_sent_to_apple_when_the_developer_key_is_not_configured(): void
    {
        Http::fake();

        $this->postJson('/api/auth/apple', [
            'identity_token' => $this->token(['sub' => 'apple-rev-2', 'email' => 'rev2@example.com']),
            'authorization_code' => 'code-abc',
        ])->assertOk();

        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'appleid.apple.com/auth/token'));
        $this->assertNull(User::where('apple_id', 'apple-rev-2')->first()->apple_refresh_token);
    }

    public function test_deleting_the_account_revokes_the_apple_token(): void
    {
        $this->configureAppleKey();
        Http::fake([
            AppleTokenRevoker::class::TOKEN_URL => Http::response(['refresh_token' => 'rt-9']),
            AppleTokenRevoker::class::REVOKE_URL => Http::response('', 200),
        ]);
        $this->postJson('/api/auth/apple', [
            'identity_token' => $this->token(['sub' => 'apple-rev-3', 'email' => 'rev3@example.com']),
            'authorization_code' => 'code-xyz',
        ])->assertOk();
        $user = User::where('apple_id', 'apple-rev-3')->first();

        Passport::actingAs($user);
        $this->deleteJson('/api/account')->assertOk();

        Http::assertSent(fn ($r) => $r->url() === AppleTokenRevoker::class::REVOKE_URL && $r['token'] === 'rt-9' && $r['token_type_hint'] === 'refresh_token');
    }

    public function test_deletion_still_succeeds_when_apple_is_unreachable(): void
    {
        $this->configureAppleKey();
        Http::fake([
            AppleTokenRevoker::class::TOKEN_URL => Http::response(['refresh_token' => 'rt-8']),
            AppleTokenRevoker::class::REVOKE_URL => Http::response('', 503),
        ]);
        $this->postJson('/api/auth/apple', [
            'identity_token' => $this->token(['sub' => 'apple-rev-4', 'email' => 'rev4@example.com']),
            'authorization_code' => 'code-xyz',
        ])->assertOk();
        $user = User::where('apple_id', 'apple-rev-4')->first();

        Passport::actingAs($user);
        $this->deleteJson('/api/account')->assertOk();

        $this->assertNull(User::where('apple_id', 'apple-rev-4')->first());
    }

    // ── Sign-in tracking for the admin statistics ────────────────────────────────

    public function test_an_apple_sign_up_from_an_iphone_is_recorded(): void
    {
        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-t1', 'email' => 't1@example.com']), 'platform' => 'ios'])->assertOk();

        $u = User::where('apple_id', 'apple-t1')->first();
        $this->assertSame(['apple', 'ios', 'apple', 'ios'], [$u->sign_up_method, $u->sign_up_platform, $u->last_login_method, $u->last_login_platform]);
        $this->assertNotNull($u->last_login_at);
        $this->assertDatabaseHas('auth_events', ['user_id' => $u->id, 'method' => 'apple', 'platform' => 'ios', 'is_signup' => 1]);
    }

    public function test_a_later_login_from_android_keeps_the_sign_up_platform(): void
    {
        $t = fn () => $this->token(['sub' => 'apple-t2', 'email' => 't2@example.com']);
        $this->postJson('/api/auth/apple', ['identity_token' => $t(), 'platform' => 'ios'])->assertOk();
        $this->postJson('/api/auth/apple', ['identity_token' => $t(), 'platform' => 'android'])->assertOk();

        $u = User::where('apple_id', 'apple-t2')->first();
        $this->assertSame('ios', $u->sign_up_platform);
        $this->assertSame('android', $u->last_login_platform);
        $this->assertSame(2, DB::table('auth_events')->where('user_id', $u->id)->count());
    }

    public function test_email_login_is_recorded_with_the_platform_header(): void
    {
        DB::table('users')->insert(['name' => 'Em', 'user_name' => 'em1', 'email' => 'em@example.com', 'gender' => 'ذكر', 'password' => bcrypt('secret123'),
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        $this->withHeaders(['X-App-Platform' => 'android'])->postJson('/api/login', ['email' => 'em@example.com', 'password' => 'secret123'])->assertOk();

        $u = User::where('email', 'em@example.com')->first();
        $this->assertSame(['email', 'android'], [$u->last_login_method, $u->last_login_platform]);
    }

    public function test_the_stats_summarise_methods_and_platforms(): void
    {
        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-s1', 'email' => 's1@example.com']), 'platform' => 'ios'])->assertOk();
        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-s2', 'email' => 's2@example.com']), 'platform' => 'android'])->assertOk();

        $stats = \App\Services\Auth\AuthTracker::stats();
        $this->assertSame(2, $stats['total']);
        $this->assertEquals(['ios' => 1, 'android' => 1], $stats['by_last_login_platform']);
        $this->assertEquals(['apple' => 2], $stats['by_signup_method']);
        $this->assertSame(2, $stats['linked']['apple']);
        $this->assertEquals(['ios' => 1, 'android' => 1], $stats['new_7d']);
    }

    public function test_a_tracking_failure_never_breaks_the_sign_in(): void
    {
        DB::statement('DROP TABLE auth_events');

        $this->postJson('/api/auth/apple', ['identity_token' => $this->token(['sub' => 'apple-t9', 'email' => 't9@example.com']), 'platform' => 'ios'])
            ->assertOk()->assertJsonStructure(['token']);
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function insertUser(array $o): int
    {
        return DB::table('users')->insertGetId(array_merge([
            'name' => 'Existing', 'user_name' => 'existing'.rand(1, 99999), 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ], $o));
    }

    private function claims(array $o): array
    {
        return array_merge([
            'iss' => AppleIdTokenVerifier::ISSUER, 'aud' => 'com.talabna.talabna', 'exp' => time() + 600, 'iat' => time(),
            'sub' => 'apple-default', 'email' => 'user@example.com', 'email_verified' => 'true',
        ], $o);
    }

    private function token(array $claims, $key = null): string
    {
        $key ??= $this->privateKey;
        $in = $this->b64url(json_encode(['alg' => 'RS256', 'kid' => self::KID])).'.'.$this->b64url(json_encode($this->claims($claims)));
        openssl_sign($in, $sig, $key, OPENSSL_ALGO_SHA256);

        return $in.'.'.$this->b64url($sig);
    }

    private function b64url(string $b): string
    {
        return rtrim(strtr(base64_encode($b), '+/', '-_'), '=');
    }
}
