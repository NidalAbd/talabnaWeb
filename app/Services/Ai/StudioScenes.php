<?php

namespace App\Services\Ai;

/**
 * Photo Studio scenes (2026-10-08): settings that suit the kind of item, so a car, a phone and a perfume each get
 * the backdrop and framing that sells them. "auto" picks the best one for the item the photo check found.
 * Labels live in the app; this is the list and the prompts.
 */
class StudioScenes
{
    /** Item kinds the photo check reports. */
    public const KINDS = ['vehicle', 'phone', 'electronics', 'fashion', 'jewelry', 'beauty', 'furniture', 'appliance', 'food', 'tools', 'property', 'other'];

    /** key => [where the item goes and how it is framed, kinds it suits best] */
    public const SCENES = [
        // General (the first six, kept).
        'living_room' => ['a bright modern living room', ['furniture', 'appliance', 'other']],
        'outdoor' => ['an outdoor setting in soft daylight', ['other', 'tools']],
        'desk' => ['a clean modern desk', ['electronics', 'phone', 'other']],
        'kitchen' => ['a bright modern kitchen counter', ['appliance', 'food']],
        'showroom' => ['an elegant showroom', ['furniture', 'vehicle', 'appliance']],
        'nature' => ['a natural setting with soft greenery', ['beauty', 'other']],
        // Cars and motorbikes: the whole vehicle in frame, three-quarter front angle.
        'car_street' => ['a clean modern city street at golden hour, the whole vehicle in frame from a three-quarter front angle, reflections on the paint', ['vehicle']],
        'car_showroom' => ['a premium car showroom with polished floor and soft overhead lights, the whole vehicle in frame from a three-quarter front angle', ['vehicle']],
        'car_road' => ['an open scenic road with mountains far behind, the whole vehicle in frame, sharp and clean', ['vehicle']],
        'car_studio' => ['a dark studio with soft rim lights tracing the body lines, the whole vehicle in frame, glossy floor reflection', ['vehicle']],
        // Phones and small electronics: close, sharp, screen and edges readable.
        'tech_dark' => ['a dark matte surface with a subtle gradient and a soft reflection under the item, close-up, edges and screen crisp', ['phone', 'electronics']],
        'tech_minimal' => ['a minimal light workspace with soft shadows, close-up, the item centred and filling most of the frame', ['phone', 'electronics']],
        'in_hand' => ['being held naturally in a person\'s hand against a softly blurred background, the item fully visible and unchanged', ['phone', 'beauty', 'jewelry']],
        // Fashion.
        'fashion_flatlay' => ['a neat flat lay on a clean light fabric surface seen from above, with soft even light', ['fashion']],
        'boutique' => ['a bright boutique with soft warm lights, the item displayed as in a shop', ['fashion', 'jewelry', 'beauty']],
        // Perfume, jewellery, watches, cosmetics.
        'luxury_marble' => ['white marble with soft golden light and gentle shadows, close-up, premium look', ['jewelry', 'beauty']],
        'silk' => ['soft draped silk fabric with elegant folds and warm light, close-up', ['jewelry', 'beauty', 'fashion']],
        // Furniture and large items.
        'modern_home' => ['a spacious modern home interior with large windows and natural light, realistic scale', ['furniture', 'appliance']],
        'loft' => ['a stylish industrial loft with warm light, realistic scale', ['furniture']],
        // Food.
        'restaurant_table' => ['a restaurant table with warm ambient light and a softly blurred background', ['food']],
        // Tools and equipment.
        'workshop' => ['a clean organised workshop bench with good light', ['tools', 'appliance']],
        // Property photos: same room, better light.
        'bright_interior' => ['the same room made bright, airy and tidy with natural daylight through the windows; keep the room layout, walls and furniture as they are', ['property']],
        // Nature and outdoor sets product photographers use most (2026-10-08, Nidal: rock, waterfall, sea, desert...).
        'waterfall_rock' => ['standing on a mossy rock in front of a misty waterfall, fresh natural light, the item sharp and the water softly blurred', ['beauty', 'electronics', 'phone', 'other']],
        'sea_rock' => ['standing on a dark wet rock by the sea with gentle waves and soft sunset light', ['beauty', 'jewelry', 'electronics', 'other']],
        'desert_dunes' => ['golden desert sand dunes in warm late-afternoon light with long soft shadows', ['vehicle', 'beauty', 'fashion', 'other']],
        'beach_sand' => ['soft beach sand with a calm turquoise sea behind, bright summer light', ['beauty', 'fashion', 'other']],
        'stone_slate' => ['a natural dark stone slate surface with subtle texture and soft side light', ['jewelry', 'beauty', 'food', 'tools']],
        'water_splash' => ['on a calm water surface with gentle ripples and a fresh splash around it, crisp light', ['beauty', 'food', 'electronics']],
        'mountain_view' => ['on a rock at a mountain viewpoint with a wide valley far behind in clear morning light', ['vehicle', 'tools', 'other']],
        'snow' => ['on fresh white snow with soft winter light and a clean background', ['fashion', 'beauty', 'other']],
        'flowers' => ['surrounded by fresh flowers and soft petals in gentle daylight', ['beauty', 'jewelry', 'fashion']],
        'wood_rustic' => ['a rustic wooden table with warm natural light', ['food', 'tools', 'other']],
        // Studio sets.
        'podium' => ['on a minimal round podium with soft pastel geometric shapes and a smooth gradient backdrop', ['beauty', 'electronics', 'phone', 'jewelry']],
        'gradient' => ['in front of a smooth colour gradient backdrop that suits the item, with a soft floor shadow', ['electronics', 'phone', 'fashion', 'other']],
        'monochrome' => ['on a backdrop and surface in the same colour family as the item, a rich single-colour look', ['beauty', 'fashion', 'food']],
        'neon_night' => ['a night city scene with soft neon reflections on wet ground', ['vehicle', 'electronics', 'phone']],
        // Themes the leading AI product-photo tools offer most (Pebblely, Photoroom: cafe, wood log, pastel room...).
        'cafe' => ['a cosy cafe table with warm light and a softly blurred background', ['food', 'phone', 'electronics', 'beauty']],
        'wood_log' => ['standing on a natural wooden log slice with soft daylight and a blurred forest behind', ['beauty', 'jewelry', 'food', 'other']],
        'pastel_room' => ['a soft pastel-coloured room with gentle shadows and clean minimal decor', ['beauty', 'fashion', 'electronics']],
        'lavender' => ['surrounded by fresh lavender sprigs on a light surface with soft natural light', ['beauty', 'jewelry']],
        'on_model' => ['worn or held by a model, cropped to show the item clearly, the item exactly unchanged', ['fashion', 'jewelry']],
        // Seasonal.
        'ramadan' => ['a warm festive Ramadan setting with soft lantern light and gentle bokeh', ['food', 'beauty', 'fashion', 'other']],
    ];

    /** The best scene per kind, for "auto". */
    public const AUTO = [
        'vehicle' => 'car_showroom', 'phone' => 'tech_dark', 'electronics' => 'tech_minimal', 'fashion' => 'boutique',
        'jewelry' => 'luxury_marble', 'beauty' => 'luxury_marble', 'furniture' => 'modern_home', 'appliance' => 'kitchen',
        'food' => 'restaurant_table', 'tools' => 'workshop', 'property' => 'bright_interior', 'other' => 'showroom',
    ];

    public static function keys(): array
    {
        return array_merge(['auto'], array_keys(self::SCENES));
    }

    /** The scene to use: the asked one, or for "auto" the best for the item's kind. */
    public static function resolve(?string $style, ?string $kind): string
    {
        if ($style && $style !== 'auto' && isset(self::SCENES[$style])) {
            return $style;
        }

        return self::AUTO[$kind ?? 'other'] ?? 'showroom';
    }

    public static function setting(string $key): string
    {
        return (self::SCENES[$key] ?? self::SCENES['showroom'])[0];
    }

    /** For the app: scenes that suit [kind] first. @return list<array{key:string, kinds:list<string>}> */
    public static function forApp(?string $kind = null): array
    {
        $list = [];
        foreach (self::SCENES as $key => [, $kinds]) {
            $list[] = ['key' => $key, 'kinds' => $kinds, 'suits' => $kind !== null && in_array($kind, $kinds, true)];
        }
        if ($kind !== null) {
            usort($list, fn ($a, $b) => $b['suits'] <=> $a['suits']);
        }

        return $list;
    }
}
