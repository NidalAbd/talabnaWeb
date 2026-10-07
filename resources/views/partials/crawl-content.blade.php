{{-- Server-rendered page content (see SeoController / BuildsCrawlContent). Plain, light markup: readable without JS
     and replaced by the Vue app when it mounts. --}}
<main class="crawl-content" style="max-width:1100px;margin:0 auto;padding:24px 16px;font-family:Cairo,system-ui,sans-serif;line-height:1.7">
    @if($crawl['h1'] !== '')<h1 style="font-size:1.6rem;margin:0 0 8px">{{ $crawl['h1'] }}</h1>@endif
    @if($crawl['intro'] !== '')<p style="margin:0 0 16px">{{ $crawl['intro'] }}</p>@endif
    @if(!empty($crawl['listings']))
    <ul style="list-style:none;padding:0;margin:0 0 24px">
        @foreach($crawl['listings'] as $item)
        <li style="padding:6px 0;border-bottom:1px solid #eee"><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
        @endforeach
    </ul>
    @endif
    @foreach($crawl['sections'] as $section)
    <section style="margin:0 0 20px">
        <h2 style="font-size:1.15rem;margin:0 0 8px">{{ $section['title'] }}</h2>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:6px 14px">
            @foreach($section['links'] as $link)
            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@if(!empty($link['count'])) <span style="color:#888">({{ $link['count'] }})</span>@endif</li>
            @endforeach
        </ul>
    </section>
    @endforeach
</main>
