<div class="home-blog-cards">
    @foreach($posts as $post)
        @php($image = $media->get($post->featured_media_id))
        <a class="home-blog-card" href="{{ url($post->path) }}">
            @if($image)
                <img src="{{ $image->isLegacy() ? asset(ltrim($image->path, '/')) : Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt ?: $post->title }}" loading="lazy">
            @elseif(preg_match('~<img[^>]+src=[\x22\x27](/wp-content/uploads/[^\x22\x27]+)~i', $post->body ?? '', $match))
                <img src="{{ asset(ltrim($match[1], '/')) }}" alt="{{ $post->title }}" loading="lazy">
            @endif
            <h3>{{ $post->title }}</h3>
        </a>
    @endforeach
</div>
