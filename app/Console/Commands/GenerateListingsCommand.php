<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates native, ASO-optimized Google Play store listings for every Play
 * Console listing language (86 locales) using OpenAI. NOT a translation —
 * each locale is written natively for its market using that market's real
 * classifieds keywords and competitor vocabulary (see $locales hints).
 *
 *   php artisan listings:generate                 # all locales (skips en-US & ar masters)
 *   php artisan listings:generate --only=tr_TR,de_DE
 *   php artisan listings:generate --path=storage/app/listings
 *
 * Output: {NN}_{play_locale}_{language}.txt in the same format as the
 * hand-written masters, so store_listings/_verify.py validates them.
 */
class GenerateListingsCommand extends Command
{
    protected $signature = 'listings:generate
                            {--only= : Comma-separated play-locale codes (underscored, e.g. tr_TR)}
                            {--path=storage/app/listings : Output directory}
                            {--research=storage/app/listings_research.json : JSON map play_code => live-SERP keywords}
                            {--model=gpt-5.4 : OpenAI model (gpt-5.4 reasoning, or gpt-4o-mini chat)}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate native ASO store listings for all Play Console locales via OpenAI';

    private string $model = 'gpt-5.4';

    // play_code | language | country | dir | native keyword + competitor hints
    // en_US and ar are hand-written masters and are skipped.
    private array $locales = [
        'af|Afrikaans|South Africa|ltr|advertensies, tweedehands, koop en verkoop, OLX, Gumtree',
        'am|Amharic|Ethiopia|ltr|የተሸጠ, ግዢና ሽያጭ, market, Jiji, Engocha',
        'hy_AM|Armenian|Armenia|ltr|հայտարարություններ, գնել վաճառել, list.am, bu',
        'az_AZ|Azerbaijani|Azerbaijan|ltr|elanlar, al sat, ikinci əl, Tap.az, Lalafo',
        'bn_BD|Bangla|Bangladesh|ltr|বিজ্ঞাপন, কেনাবেচা, পুরাতন, Bikroy, OLX',
        'eu_ES|Basque|Spain|ltr|iragarkiak, erosi saldu, bigarren eskukoa, Wallapop, Milanuncios',
        'be|Belarusian|Belarus|ltr|аб’явы, купіць прадаць, барахолка, Kufar, OLX',
        'bg|Bulgarian|Bulgaria|ltr|обяви, купува продава, втора употреба, OLX, Bazar.bg',
        'my_MM|Burmese|Myanmar|ltr|ကြော်ငြာ, အရောင်းအဝယ်, တစ်ပတ်ရစ်, Shwe Property, OLX',
        'ca|Catalan|Spain|ltr|anuncis, comprar vendre, segona mà, Wallapop, Milanuncios',
        'zh_HK|Chinese (Traditional, HK)|Hong Kong|ltr|二手, 買賣, 分類廣告, Carousell, 28Hse, DCFever',
        'zh_CN|Chinese (Simplified)|China|ltr|二手, 买卖, 闲置, 闲鱼, 58同城, 转转',
        'zh_TW|Chinese (Traditional, TW)|Taiwan|ltr|二手, 買賣, 拍賣, Carousell, 蝦皮拍賣, 旋轉拍賣',
        'hr|Croatian|Croatia|ltr|oglasi, kupi prodaj, rabljeno, Njuškalo, Index Oglasi',
        'cs_CZ|Czech|Czechia|ltr|inzeráty, koupit prodat, bazar, Sbazar, Bazoš',
        'da_DK|Danish|Denmark|ltr|annoncer, køb og salg, brugt, DBA, GulogGratis',
        'nl_NL|Dutch|Netherlands|ltr|advertenties, kopen en verkopen, tweedehands, Marktplaats',
        'en_AU|English (Australia)|Australia|ltr|classifieds, buy and sell, second hand, Gumtree, Marketplace',
        'en_CA|English (Canada)|Canada|ltr|classifieds, buy and sell, used, Kijiji, Marketplace',
        'en_GB|English (UK)|United Kingdom|ltr|classifieds, buy and sell, second hand, Gumtree, Marketplace',
        'en_IN|English (India)|India|ltr|classifieds, buy and sell, used, OLX, Quikr',
        'en_SG|English (Singapore)|Singapore|ltr|classifieds, buy and sell, preloved, Carousell',
        'en_ZA|English (South Africa)|South Africa|ltr|classifieds, buy and sell, used, OLX, Gumtree',
        'et|Estonian|Estonia|ltr|kuulutused, ost müük, kasutatud, Osta.ee, Soov',
        'fil|Filipino|Philippines|ltr|classified ads, bili benta, segunda mano, Carousell, OLX',
        'fi_FI|Finnish|Finland|ltr|ilmoitukset, osta myy, käytetty, Tori, Huuto',
        'fr_CA|French (Canada)|Canada|ltr|petites annonces, acheter vendre, occasion, Kijiji, Marketplace',
        'fr_FR|French (France)|France|ltr|petites annonces, acheter vendre, occasion, Leboncoin',
        'gl_ES|Galician|Spain|ltr|anuncios, comprar vender, segunda man, Wallapop, Milanuncios',
        'ka_GE|Georgian|Georgia|ltr|განცხადებები, ყიდვა გაყიდვა, მეორადი, MyMarket, TKT',
        'de_DE|German|Germany|ltr|kleinanzeigen, kaufen und verkaufen, gebraucht, Kleinanzeigen',
        'el_GR|Greek|Greece|ltr|αγγελίες, αγορά πώληση, μεταχειρισμένα, Car.gr, Spitogatos',
        'gu|Gujarati|India|ltr|જાહેરાત, ખરીદી વેચાણ, જૂનું, OLX, Quikr',
        'iw_IL|Hebrew|Israel|rtl|לוח מודעות, קנייה ומכירה, יד שנייה, יד2, Facebook Marketplace',
        'hi_IN|Hindi|India|ltr|विज्ञापन, खरीदें बेचें, पुराना सामान, OLX, Quikr',
        'hu_HU|Hungarian|Hungary|ltr|apróhirdetés, vétel eladás, használt, Jófogás, Hardverapró',
        'is_IS|Icelandic|Iceland|ltr|smáauglýsingar, kaupa selja, notað, Bland.is',
        'id|Indonesian|Indonesia|ltr|iklan baris, jual beli, barang bekas, OLX, Carousell',
        'it_IT|Italian|Italy|ltr|annunci, compra vendi, usato, Subito, Kijiji',
        'ja_JP|Japanese|Japan|ltr|中古, 売買, フリマ, メルカリ, ジモティー, ヤフオク',
        'kn_IN|Kannada|India|ltr|ಜಾಹೀರಾತು, ಖರೀದಿ ಮಾರಾಟ, ಹಳೆಯ, OLX, Quikr',
        'kk|Kazakh|Kazakhstan|ltr|хабарландыру, сату сатып алу, қолданылған, OLX, Krisha',
        'km_KH|Khmer|Cambodia|ltr|ការផ្សាយពាណិជ្ជកម្ម, ទិញលក់, ជជុះ, Khmer24, OLX',
        'ko_KR|Korean|South Korea|ltr|중고거래, 사고팔기, 중고, 당근마켓, 중고나라, 번개장터',
        'ky_KG|Kyrgyz|Kyrgyzstan|ltr|жарнамалар, сатып алуу сатуу, колдонулган, Lalafo, OLX',
        'lo_LA|Lao|Laos|ltr|ການໂຄສະນາ, ຊື້ຂາຍ, ມືສອງ, One-X, Facebook',
        'lv|Latvian|Latvia|ltr|sludinājumi, pirkt pārdot, lietots, SS.lv, Andele',
        'lt|Lithuanian|Lithuania|ltr|skelbimai, pirkti parduoti, naudotas, Skelbiu, Autoplius',
        'mk_MK|Macedonian|North Macedonia|ltr|огласи, купи продај, половно, Pazar3, Reklama5',
        'ms|Malay|Malaysia|ltr|iklan, jual beli, terpakai, Mudah.my, Carousell',
        'ms_MY|Malay (Malaysia)|Malaysia|ltr|iklan, jual beli, terpakai, Mudah.my, Carousell',
        'ml_IN|Malayalam|India|ltr|പരസ്യം, വാങ്ങൽ വിൽപന, പഴയത്, OLX, Quikr',
        'mr_IN|Marathi|India|ltr|जाहिरात, खरेदी विक्री, जुने, OLX, Quikr',
        'mn_MN|Mongolian|Mongolia|ltr|зар, худалдан авах зарах, хуучин, Unegui, OLX',
        'ne_NP|Nepali|Nepal|ltr|विज्ञापन, किनबेच, पुरानो, Hamrobazaar, OLX',
        'no_NO|Norwegian|Norway|ltr|annonser, kjøp og salg, brukt, Finn.no',
        'fa|Persian|Iran|rtl|آگهی, خرید و فروش, دست دوم, دیوار, شیپور',
        'pl_PL|Polish|Poland|ltr|ogłoszenia, kup i sprzedaj, używane, OLX, Allegro Lokalnie',
        'pt_BR|Portuguese (Brazil)|Brazil|ltr|classificados, comprar e vender, usados, OLX, Enjoei',
        'pt_PT|Portuguese (Portugal)|Portugal|ltr|classificados, comprar e vender, usado, OLX, CustoJusto',
        'pa|Punjabi|India|ltr|ਇਸ਼ਤਿਹਾਰ, ਖਰੀਦੋ ਵੇਚੋ, ਪੁਰਾਣਾ, OLX, Quikr',
        'ro|Romanian|Romania|ltr|anunturi, vinde cumpara, second hand, OLX, Publi24',
        'rm|Romansh|Switzerland|ltr|annuncis, cumprar vender, segunda maun, Ricardo, Anibis',
        'ru_RU|Russian|Russia|ltr|объявления, купить продать, бу, Авито, Юла',
        'sr|Serbian|Serbia|ltr|огласи, купи продај, половно, KupujemProdajem, Polovni',
        'si_LK|Sinhala|Sri Lanka|ltr|දැන්වීම්, ගැනුම් විකුණුම්, පාවිච්චි කළ, ikman, Riyasewana',
        'sk|Slovak|Slovakia|ltr|inzeráty, kúpiť predať, bazár, Bazoš, Modrá kniha',
        'sl|Slovenian|Slovenia|ltr|oglasi, kupi prodaj, rabljeno, Bolha, Salomon',
        'es_419|Spanish (Latin America)|Mexico|ltr|clasificados, compra y venta, usado, MercadoLibre, Marketplace',
        'es_ES|Spanish (Spain)|Spain|ltr|anuncios, comprar y vender, segunda mano, Wallapop, Milanuncios',
        'es_US|Spanish (US)|United States|ltr|clasificados, compra y venta, usado, OfferUp, Marketplace',
        'sw|Swahili|Kenya/Tanzania|ltr|matangazo, nunua uza, mitumba, Jiji, OLX',
        'sv_SE|Swedish|Sweden|ltr|annonser, köp och sälj, begagnat, Blocket, Tradera',
        'ta_IN|Tamil|India|ltr|விளம்பரங்கள், வாங்க விற்க, பழைய, OLX, Quikr',
        'te_IN|Telugu|India|ltr|ప్రకటనలు, కొనుగోలు అమ్మకం, పాత, OLX, Quikr',
        'th|Thai|Thailand|ltr|ประกาศ, ซื้อขาย, มือสอง, Kaidee, Facebook Marketplace',
        'tr_TR|Turkish|Turkey|ltr|ilan, alışveriş, ikinci el, sahibinden, Letgo, Dolap',
        'uk|Ukrainian|Ukraine|ltr|оголошення, купити продати, бу, OLX, Prom',
        'ur|Urdu|Pakistan|rtl|اشتہار, خرید و فروخت, استعمال شدہ, OLX, Pakwheels',
        'vi|Vietnamese|Vietnam|ltr|rao vặt, mua bán, đồ cũ, Chợ Tốt, Facebook Marketplace',
        'zu|Zulu|South Africa|ltr|izikhangiso, thenga thengisa, okusetshenzisiwe, OLX, Gumtree',
    ];

    public function handle(): int
    {
        $apiKey = (string) config('services.openai.key', '');
        if ($apiKey === '') {
            $this->error('OPENAI_API_KEY is not set.');
            return self::FAILURE;
        }

        $this->model = $this->option('model') ?: 'gpt-5.4';

        $dir = $this->option('path');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $only = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : null;

        // Live Play Store SERP research: play_code => "verified keyword, keyword, ...".
        // When present for a locale it OVERRIDES the static hints (real harvested terms).
        $research = [];
        $researchPath = $this->option('research');
        if ($researchPath && File::exists($researchPath)) {
            $research = json_decode(File::get($researchPath), true) ?: [];
            $this->info('Loaded live SERP research for ' . count($research) . ' locales.');
        }

        $nn = 0;
        $done = 0;
        $failed = [];

        foreach ($this->locales as $row) {
            $nn++;
            [$code, $lang, $country, $dir2, $hints] = explode('|', $row);

            if ($only && ! in_array($code, $only, true)) {
                continue;
            }

            // Prefer live-SERP-harvested keywords when available.
            $live = isset($research[$code]) && trim((string) $research[$code]) !== '';
            if ($live) {
                $hints = trim((string) $research[$code]);
            }

            $file = sprintf('%s/%02d_%s_%s.txt', rtrim($dir, '/\\'), $nn, $code,
                strtolower(preg_replace('/[^a-zA-Z]/', '', $lang)));

            if (File::exists($file) && ! $this->option('force')) {
                $this->line("  • skip {$code} (exists)");
                continue;
            }

            $this->line("  → {$code} ({$lang} / {$country})");
            $listing = $this->generate($apiKey, $code, $lang, $country, $dir2, $hints);

            if (! $listing) {
                $failed[] = $code;
                $this->warn("    ! failed {$code}");
                continue;
            }

            File::put($file, $this->render($code, $lang, $country, $hints, $listing, $live));
            $done++;
        }

        $this->newLine();
        $this->info("Generated {$done} listings to {$dir}.");
        if ($failed) {
            $this->warn('Failed: ' . implode(', ', $failed) . ' — re-run with --only=' . implode(',', $failed));
        }
        return self::SUCCESS;
    }

    private function generate(string $apiKey, string $code, string $lang, string $country, string $dir, string $hints): ?array
    {
        $rtl = $dir === 'rtl';
        $system = <<<P
You are an expert App Store Optimization copywriter and a NATIVE speaker of {$lang} living in {$country}.
Write the Google Play store listing for "Talabna" — a FREE local classifieds & services marketplace:
buy & sell used and new CARS, REAL ESTATE, DEVICES/ELECTRONICS, JOBS, and SERVICES; free ad posting in
under a minute; contact sellers directly by call/WhatsApp/in-app chat; video reels for ads; points & badges
to feature ads; works in 70+ languages; faster startup in the new version.

ABSOLUTE RULES:
- Write 100% NATIVELY in {$lang} for the {$country} market. DO NOT translate from English or any other language.
- Use the REAL words people in {$country} type into Google Play to find buy/sell & classifieds apps. Native keyword hints (use the native-script equivalents, harvested from local competitors): {$hints}.
- Keep the brand "Talabna" (use its natural local form/spelling if one exists).
- Sound like a local competitor app's listing, not a translated foreign one.
- Use the native script of {$lang} for ALL content.{$this->rtlNote($rtl)}

Return ONLY a JSON object: {"title": string, "short": string, "full": string, "keywords": string}.
- "title": MAX 30 characters. Brand + the single highest-volume local buy/sell keyword.
- "short": MAX 80 characters. One punchy sentence; lead with an action verb; include 2-3 top local keywords.
- "full": HARD REQUIREMENT — BETWEEN 3700 AND 4000 characters (aim for ~3900). This is mandatory: write detailed,
  benefit-rich blocks so the text reaches at least 3700 characters — do NOT stop short. Hook (first 160 chars
  repeat the #1 local keyword twice), then emoji-headed blocks for CARS, REAL ESTATE, DEVICES, JOBS, SERVICES
  (each 3-5 full sentences), a "why Talabna" block, a "how it works" (post/connect/deal) block, and end with a
  comma-separated keyword footer line. Natural, detailed, not spammy and not repetitive.
- "keywords": 15-20 comma-separated native keywords.
Counts are characters, not words. Respect the title (30) and short (80) limits strictly.
P;

        // GPT-5 / o-series are reasoning models: use max_completion_tokens,
        // no custom temperature, and a low reasoning effort (copywriting, not math).
        $isReasoning = (bool) preg_match('/^(gpt-5|o\d)/', $this->model);
        $payload = [
            'model' => $this->model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Generate the Talabna listing for {$lang} ({$country}) now."],
            ],
        ];
        if ($isReasoning) {
            // Full GPT-5 writing performance: maximum reasoning + high verbosity
            // for the richest 3700-4000 char native copy. Generous token ceiling
            // so reasoning tokens never starve the JSON output.
            // High ceiling: high reasoning + long non-Latin output (Amharic,
            // Burmese, Khmer…) are very token-heavy; too low truncates the JSON.
            $payload['max_completion_tokens'] = 24000;
            $payload['reasoning_effort'] = 'medium';
            $payload['verbosity'] = 'high';
        } else {
            $payload['temperature'] = 0.7;
            $payload['max_tokens'] = 4096;
        }

        try {
            $resp = Http::withToken($apiKey)->timeout(300)->retry(2, 3000)
                ->post('https://api.openai.com/v1/chat/completions', $payload);
            if (! $resp->successful()) {
                Log::warning('listings:generate http error', ['code' => $code, 'status' => $resp->status()]);
                return null;
            }
            $content = $resp->json('choices.0.message.content');
            $j = is_string($content) ? json_decode($content, true) : null;
            if (! is_array($j) || empty($j['title']) || empty($j['full'])) {
                return null;
            }
            // Floor: if the full description is under 3700, expand it (up to 2x).
            $tries = 0;
            while ($this->utf16len($j['full']) < 3650 && $tries < 2) {
                $expanded = $this->expand($apiKey, $j['full'], $lang, $country, $hints);
                if ($expanded && $this->utf16len($expanded) > $this->utf16len($j['full'])) {
                    $j['full'] = $expanded;
                    $tries++;
                } else {
                    break; // no improvement — stop expanding
                }
            }
            // Enforce char limits (UTF-16 code units, like Play Console).
            $j['title'] = $this->trimUtf16($j['title'], 30);
            $j['short'] = $this->trimUtf16($j['short'] ?? '', 80);
            $j['full']  = $this->trimUtf16($j['full'], 4000);
            return $j;
        } catch (\Throwable $e) {
            Log::error('listings:generate exception', ['code' => $code, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /** Expand a too-short full description to 3700-4000 chars, natively. */
    private function expand(string $apiKey, string $current, string $lang, string $country, string $hints): ?string
    {
        $len = $this->utf16len($current);
        $sys = "You are a native {$lang} App Store Optimization copywriter for the {$country} market. "
            . "The following Google Play FULL DESCRIPTION for 'Talabna' (a free local classifieds & marketplace "
            . "app: buy & sell cars, real estate, devices, jobs, services) is TOO SHORT — it is only {$len} "
            . "characters and MUST reach at least 3700. Rewrite it natively in "
            . "{$lang} so it is BETWEEN 3700 and 4000 characters (aim ~3900): expand every category block with more "
            . "concrete detail and benefits, add a 'why Talabna' and a 'how it works' (post/connect/deal) section, "
            . "and a comma-separated keyword footer using these local terms: {$hints}. Do NOT translate from English; "
            . "use natural native phrasing. Keep emojis as block headers. Return ONLY JSON: {\"full\": \"...\"}.";

        $payload = [
            'model' => $this->model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $sys],
                ['role' => 'user', 'content' => "Current draft to expand:\n\n{$current}"],
            ],
        ];
        if (preg_match('/^(gpt-5|o\d)/', $this->model)) {
            $payload['max_completion_tokens'] = 32000;
            $payload['reasoning_effort'] = 'medium';
            $payload['verbosity'] = 'high';
        } else {
            $payload['temperature'] = 0.7;
            $payload['max_tokens'] = 4096;
        }

        try {
            $r = Http::withToken($apiKey)->timeout(300)->retry(1, 2000)
                ->post('https://api.openai.com/v1/chat/completions', $payload);
            $c = $r->json('choices.0.message.content');
            $j = is_string($c) ? json_decode($c, true) : null;
            return (is_array($j) && ! empty($j['full']) && is_string($j['full'])) ? trim($j['full']) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function rtlNote(bool $rtl): string
    {
        return $rtl
            ? "\n- This is an RTL language; keep any Latin brand tokens (Talabna) readable within the RTL text."
            : '';
    }

    private function utf16len(string $s): int
    {
        return intdiv(strlen(mb_convert_encoding($s, 'UTF-16LE', 'UTF-8')), 2);
    }

    /** Trim to <= max UTF-16 units, preferring a word/sentence boundary. */
    private function trimUtf16(string $s, int $max): string
    {
        $s = trim($s);
        if ($this->utf16len($s) <= $max) {
            return $s;
        }
        // Cut by characters until within budget.
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';
        foreach ($chars as $ch) {
            if ($this->utf16len($out . $ch) > $max) {
                break;
            }
            $out .= $ch;
        }
        // Prefer to end at the last space for title/short (short strings only).
        if ($max <= 80) {
            $sp = mb_strrpos($out, ' ');
            if ($sp !== false && $sp > $max * 0.5) {
                $out = mb_substr($out, 0, $sp);
            }
        }
        return rtrim($out);
    }

    private function render(string $code, string $lang, string $country, string $hints, array $l, bool $live = false): string
    {
        $title = $l['title'];
        $short = $l['short'] ?? '';
        $full  = $l['full'];
        $kw    = $l['keywords'] ?? '';
        $bar = str_repeat('═', 67);
        $source = $live
            ? 'LIVE Play Store SERP research (in-country top-app titles harvested)'
            : 'curated real-market keyword hints (local competitors + native terms)';

        return <<<TXT
Language: {$lang} ({$code})
Market: {$country}
Keyword source: {$source}
Native keywords used (no translation): {$hints}
Generated: June 2026 via listings:generate (OpenAI {$this->model}), native ASO copy — NOT translated.
Top extra keywords: {$kw}
Update angle (v1.4.0): 70+ language UI, faster cold start, video reels for ads, points & badges, in-app contact (call/WhatsApp).

{$bar}
APP TITLE (paste in Play Console — 30 chars max)
{$bar}
{$title}

{$bar}
SHORT DESCRIPTION (paste in Play Console — 80 chars max)
{$bar}
{$short}

{$bar}
FULL DESCRIPTION (paste in Play Console — 4000 chars max)
{$bar}
{$full}

TXT;
    }
}
