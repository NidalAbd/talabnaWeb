<?php

namespace App\Services\Geo;

use App\Models\cities;
use App\Models\countries;
use Illuminate\Support\Facades\Cache;

/**
 * Turns what the phone knows about where the user is into our own country and city rows (2026-10-10).
 *
 * The phone gives an ISO country code (from GPS, or the device region) and place names from its own geocoder
 * ("Khan Younis", "خان يونس", "Gaza Governorate"). The country matches countries.iso_code exactly; the city is matched
 * by name against our cities of that country, in every language they carry, after normalising spelling (case, accents,
 * Arabic letter forms, "al-", "city", "governorate"). No match: no city, and the user picks one.
 */
class LocationResolver
{
    public function country(?string $iso): ?countries
    {
        $iso = strtoupper(trim((string) $iso));
        if (! preg_match('/^[A-Z]{2}$/', $iso)) {
            return null;
        }
        $id = Cache::remember("geo.country.$iso", 86400, fn () => countries::where('iso_code', $iso)->value('id') ?? 0);

        return $id ? countries::find($id) : null;
    }

    /** @param  string[]  $names  most specific first (locality, sub-area, area) */
    public function city(int $countryId, array $names): ?cities
    {
        $wanted = array_values(array_filter(array_map([self::class, 'normalize'], $names), fn ($n) => mb_strlen($n) >= 2));
        if (! $wanted) {
            return null;
        }
        $index = $this->index($countryId);

        // Exact name first, in the order the phone gave them (the town before its region)
        foreach ($wanted as $w) {
            if (isset($index[$w])) {
                return cities::find($index[$w]);
            }
        }
        // Then a name that contains the other ("Gaza" ~ "Gaza City"), only for names long enough to mean something
        foreach ($wanted as $w) {
            if (mb_strlen($w) < 4) {
                continue;
            }
            $best = null;
            foreach ($index as $name => $id) {
                if (mb_strlen($name) >= 4 && (str_contains($name, $w) || str_contains($w, $name))) {
                    $gap = abs(mb_strlen($name) - mb_strlen($w));
                    if (! $best || $gap < $best[1]) {
                        $best = [$id, $gap];
                    }
                }
            }
            if ($best) {
                return cities::find($best[0]);
            }
        }
        // Last, Latin spellings that differ only in vowels ("Khan Yunis" ~ "Khan Younis")
        $skeletons = $this->skeletons($countryId);
        foreach ($wanted as $w) {
            $k = self::skeleton($w);
            if (mb_strlen($k) >= 4 && isset($skeletons[$k])) {
                return cities::find($skeletons[$k]);
            }
        }

        return null;
    }

    /** consonant skeleton => city id; a skeleton two cities share is dropped (no guessing between them) */
    private function skeletons(int $countryId): array
    {
        return Cache::remember("geo.skeletons.$countryId.v1", 86400, function () use ($countryId) {
            $out = [];
            foreach ($this->index($countryId) as $name => $id) {
                $k = self::skeleton($name);
                if (mb_strlen($k) < 4) {
                    continue;
                }
                $out[$k] = (isset($out[$k]) && $out[$k] !== $id) ? 0 : $id;
            }

            return array_filter($out);
        });
    }

    /** Latin names without vowels after the first letter; non-Latin names are left out */
    public static function skeleton(string $normalized): string
    {
        if (! preg_match('/^[a-z0-9]+$/', $normalized)) {
            return '';
        }

        return $normalized[0].preg_replace('/[aeiouyw]/', '', substr($normalized, 1));
    }

    /** normalized name => city id, for one country (all languages), cached for a day */
    private function index(int $countryId): array
    {
        return Cache::remember("geo.cities.$countryId.v1", 86400, function () use ($countryId) {
            $out = [];
            cities::where('country_id', $countryId)->select(['id', 'name'])->orderBy('id')
                ->chunk(2000, function ($rows) use (&$out) {
                    foreach ($rows as $row) {
                        $raw = $row->getRawOriginal('name');
                        $names = is_string($raw) ? (json_decode($raw, true) ?: [$raw]) : (array) $raw;
                        foreach ($names as $n) {
                            $k = self::normalize((string) $n);
                            if ($k !== '' && ! isset($out[$k])) {
                                $out[$k] = (int) $row->id;
                            }
                        }
                    }
                });

            return $out;
        });
    }

    public static function normalize(?string $s): string
    {
        $s = mb_strtolower(trim((string) $s));
        if ($s === '') {
            return '';
        }
        // Latin accents away (Ürdün → urdun)
        if (class_exists(\Normalizer::class)) {
            $s = preg_replace('/\p{Mn}+/u', '', \Normalizer::normalize($s, \Normalizer::FORM_D));
        }
        // Arabic letter forms and harakat
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $s);
        $s = strtr($s, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي']);
        // Words that only say what kind of place it is
        $s = preg_replace('/\b(city|governorate|province|district|municipality|region|state|of|the)\b/u', ' ', $s);
        $s = preg_replace('/(^|\s)(مدينه|محافظه|منطقه|ولايه|بلديه|قضاء|لواء)(\s|$)/u', ' ', $s);
        // "al-"/"el-" and the Arabic article
        $s = preg_replace('/(^|\s)(al|el|ad|ar|as|ash|at|az)[\s\-]+/u', '$1', $s);
        $s = preg_replace('/(^|\s)ال/u', '$1', $s);
        // Keep letters and digits only
        $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s);

        return $s;
    }

    /**
     * Country and city from what the phone sent: ['iso' => 'PS', 'names' => [...]] (either may be missing).
     *
     * @return array{country: ?countries, city: ?cities}
     */
    public function resolve(?string $iso, array $names = []): array
    {
        $country = $this->country($iso);
        $city = $country ? $this->city((int) $country->id, $names) : null;

        return ['country' => $country, 'city' => $city];
    }

    /**
     * The place for a new account from the hints the app sent at sign-in (country_iso, place_names). Unknown: the old
     * default (the first country and its first city) so nothing breaks; the account stays unconfirmed and the app asks.
     *
     * @return array{country: ?countries, city: ?cities}
     */
    public function forNewUser(?string $iso, mixed $names = []): array
    {
        $r = $this->resolve($iso, is_array($names) ? array_slice(array_filter($names, 'is_string'), 0, 6) : []);
        if ($r['country']) {
            return $r;
        }
        $country = countries::orderBy('id')->first();

        return ['country' => $country, 'city' => $country ? cities::where('country_id', $country->id)->orderBy('id')->first() : null];
    }
}
