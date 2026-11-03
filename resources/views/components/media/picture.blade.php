@props(['media', 'sizes' => '100vw', 'priority' => false])
@if($media->processing_status === 'ready')
    @inject('assets', 'App\Services\Media\MediaAssets')
    <picture>
        @if($assets->srcset($media, 'image/avif'))<source type="image/avif" srcset="{{ $assets->srcset($media, 'image/avif') }}" sizes="{{ $sizes }}">@endif
        @if($assets->srcset($media, 'image/webp'))<source type="image/webp" srcset="{{ $assets->srcset($media, 'image/webp') }}" sizes="{{ $sizes }}">@endif
        <img {{ $attributes }} src="{{ $assets->originalUrl($media) }}" alt="{{ $media->alt }}" width="{{ $media->width }}" height="{{ $media->height }}"
            loading="{{ $priority ? 'eager' : 'lazy' }}" @if($priority) fetchpriority="high" @endif decoding="async">
    </picture>
@endif
