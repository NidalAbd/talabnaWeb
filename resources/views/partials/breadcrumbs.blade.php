@php
    $segments = Request::segments();
    $breadcrumbs = [];
@endphp

<nav aria-label="breadcrumb" class="">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        @foreach($segments as $key => $segment)
            @php
                $url = url(implode('/', array_slice($segments, 0, $key + 1)));
                $name = ucfirst($segment);
                if ($key + 1 === count($segments)) {
                    $breadcrumbs[] = ['name' => $name, 'url' => $url, 'active' => true];
                } else {
                    $breadcrumbs[] = ['name' => $name, 'url' => $url, 'active' => false];
                }
            @endphp
        @endforeach

        @foreach($breadcrumbs as $breadcrumb)
            @if ($breadcrumb['active'])
                <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['name'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
