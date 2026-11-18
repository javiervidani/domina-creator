<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p['collection']['name'] }}</title>
<style>
body{margin:0;padding:12px;font:15px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#1f1f1f;background:transparent}
h1{font-size:20px;margin:0 0 4px}.d{color:#676c72;margin:0 0 14px}
.items{display:{{ $p['layout'] === 'grid' ? 'grid' : 'block' }};grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
.item{background:#fff;border:1px solid #dde3ea;border-radius:14px;padding:14px;margin-bottom:{{ $p['layout'] === 'grid' ? '0' : '14px' }}}
.item h2{font-size:16px;margin:0 0 6px}.t{white-space:pre-wrap;margin:0 0 8px}
.item img{width:100%;max-height:220px;object-fit:cover;border-radius:10px;margin-bottom:8px;background:#f1f3f4}
a{color:{{ $p['accent'] }};font-size:13px}
</style>
</head>
<body>
<h1 dir="auto">{{ $p['collection']['name'] }}</h1>
@if ($p['collection']['description'])<p class="d" dir="auto">{{ $p['collection']['description'] }}</p>@endif
<div class="items">
@foreach ($p['items'] as $i)
    <div class="item">
        {{-- The picture address belongs to the network and can expire: a broken one hides, the link to the post stays. --}}
        @if ($i['media'])<img src="{{ $i['media']['thumbnail_url'] }}" alt="" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none'">@endif
        <h2 dir="auto">{{ $i['title'] }}</h2>
        <p class="t" dir="auto">{{ \Illuminate\Support\Str::limit($i['text'], 600) }}</p>
        @if ($i['url'])<a href="{{ $i['url'] }}" target="_blank" rel="noopener">View the original post</a>@endif
    </div>
@endforeach
</div>
<script>
// Lets the page that holds the frame size it to the content (see the embed code in the dashboard).
function send(){parent.postMessage({domina:'height',height:document.documentElement.scrollHeight},'*')}
addEventListener('load',send);addEventListener('resize',send);
</script>
</body>
</html>
