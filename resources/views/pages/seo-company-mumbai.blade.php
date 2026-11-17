@extends('layouts.public')
@section('title', $record->seo_title ?: 'SEO Company in Mumbai | Trivo')
@section('description', $record->seo_description ?: '')
@section('canonical', $record->canonical_url ?: request()->url())
@section('content')
@php
$icons = ['funnel','cart','chart','refresh','megaphone','bulb'];
$paths = [
'funnel' => 'M3 4h18l-7 8v7l-4 2v-9z',
'cart' => 'M3 3h2l3 12h11l3-9H6 M9 20h.01 M18 20h.01',
'chart' => 'M4 3v17h17 M8 14l4-5 4 3 5-7',
'refresh' => 'M20 7a8 8 0 0 0-14-2L3 8 M3 3v5h5 M4 17a8 8 0 0 0 14 2l3-3 M21 21v-5h-5',
'megaphone' => 'M3 9h5l12-5v16L8 15H3z M8 15l2 6h4l-2-5',
'bulb' => 'M9 19h6 M10 22h4 M8 15a7 7 0 1 1 8 0l-1 3H9z',
];
$steps = ['Market Analysis','Website Audit','Keyword Research & Analysis','On-Page SEO','Content Optimization','Off-Page SEO & Link Building','Technical SEO','Performance Tracking & Reporting'];
$setup = ['Search Console – Setup & Review','Google Analytics – Setup & Review','Keyword to URL Mapping','Baseline Ranking Setup','Baseline Organic Search Report Setup','Competitor Analysis','Google Penalty Check'];
$commerce = ['Product Image Optimization','Product Page Schema Markups','Optimization of Category Pages','Rich Snippet Optimization','Brand Related Page Optimization','Product Titles & Meta Descriptions','Product Page Optimization Suggestions','Ratings / Review Schema Integration','Pagination Recommendations','Filtered / URL Parameter Handling','Out-of-stock Product Page Handling'];
@endphp
<article class="mumbai-page">
<section class="ms-hero">
 <span class="ms-pill">AI - Driven Growth Solution</span>
 <h1>SEO Company in Mumbai</h1>
 <h2>Applying dependable SEO techniques to generate<br class="ms-desktop"> qualified leads and customers.</h2>
 <p>We implement successful, efficient SEO to help businesses grow</p>
 <form class="ms-audit" action="{{ route('contact') }}" method="get"><label class="sr-only" for="ms-website">Website</label><input id="ms-website" name="website" placeholder="www.website.com"><button>GET FREE AUDIT</button></form>
 <div class="ms-awards">@foreach($awardImages as $src)<img src="{{ $src }}" alt="Search marketing award" width="140" height="45">@endforeach</div>
 <div class="ms-metrics">@foreach([['1.2B+','Organic Sessions'],['29M+','Leads Generated'],['130+','Case Studies'],['50+','Awards & Recognitions']] as [$number,$label])<div><span class="ms-icon"><svg viewBox="0 0 24 24"><path d="{{ $paths[$icons[$loop->index]] }}"/></svg></span><strong>{{ $number }}</strong><small>{{ $label }}</small></div>@endforeach</div>
</section>
<section class="ms-results ms-wrap">
 <span class="ms-eyebrow">CLIENT SUCCESS</span><h2>Real Results, Real Businesses</h2><p>Our clients don’t just grow — they dominate their categories online.</p>
 <div class="ms-case-grid">@foreach([['Luhaif Perfumes','Luhaif-Thumbnail.png','luhaif-perfumes'],['Mumbai Darshan','Mumbai-Thumbnail.png','mumbai-darshan'],['Sell Sold','Sell-Sold-Thumbnail.png','sell-sold']] as [$title,$image,$slug])<a href="{{ url('/'.$slug) }}"><img src="{{ asset('wp-content/uploads/2026/05/'.$image) }}" alt="{{ $title }}" loading="lazy"><div><small>May 2026</small><h3>{{ $title }}</h3></div></a>@endforeach</div>
 <div class="ms-dots" aria-hidden="true">●•••••••••••</div><a class="ms-button" href="{{ route('portfolio') }}">VIEW ALL &nbsp; →</a>
</section>
<section class="ms-benefits ms-wrap"><h2>SEO Services Helping Businesses Grow in Mumbai</h2><div class="ms-benefit-grid">@foreach($benefits as $benefit)<article><span class="ms-icon"><svg viewBox="0 0 24 24"><path d="{{ $paths[$icons[$loop->index % 6]] }}"/></svg></span><h3>{{ $benefit['title'] }}</h3><p>{{ $benefit['copy'] }}</p></article>@endforeach</div><a class="ms-button" href="{{ route('contact') }}">Get Free Audit</a></section>
<section class="ms-location"><div class="ms-wrap"><span class="ms-pill">Get Location</span><h2>SEO Services in Mumbai</h2><div class="ms-location-grid"><div class="ms-location-card"><p>Online presence matters more than ever. A team based in Mumbai helps companies grow without shortcuts. Results show up clearly when methods respect both users and search rules. Goals shape every move, not guesswork. Success comes from consistent effort, smart analysis, tracking what actually works.</p><div class="ms-location-meta"><div><small>Phone Number</small><a href="tel:+919029449582">+91 90294 49582</a></div><div>4.8 Rating<br><span class="ms-stars">★★★★★</span></div></div><div class="ms-address"><span aria-hidden="true">📍</span><p>Goregaon West, Mumbai,<br>Maharashtra 400104</p><a href="https://maps.google.com/?q=Trivo+Goregaon+West+Mumbai" target="_blank" rel="noopener">VIEW ON MAP</a></div></div><div class="ms-map" aria-label="Mumbai location map panel"></div></div></div></section>
<section class="ms-services ms-wrap"><h2>SEO &amp; Digital Growth Services We Offer</h2><div class="ms-service-grid">@foreach($services as $service)<article><h3>{{ $service['title'] }}</h3><p>{{ $service['copy'] }}</p></article>@endforeach</div></section>
<section class="ms-why"><div class="ms-wrap"><h2>Why Choose Our SEO Company in Mumbai?</h2><p>We focus on giving consistent, understandable and practical SEO results for businesses who want long-term viability over short-term engagement.</p><div class="ms-why-grid"><div>@foreach($reasons as $reason)<details @if($loop->first) open @endif><summary>{{ $reason['title'] }}</summary><p>{{ $reason['copy'] }}</p></details>@endforeach</div><div class="ms-why-panel" aria-hidden="true"></div></div></div></section>
<section class="ms-process ms-wrap"><h2>Our SEO Process to Drive Consistent Growth</h2><p>We implement the systematic and transparent SEO strategy to increase search rankings, attract targeted traffic, and promote long-term growth. As a focused SEO company in Mumbai, our approach is useful, data-driven, and consistent with business objectives.</p><div class="ms-steps">@foreach($steps as $step)<div><span class="ms-icon"><svg viewBox="0 0 24 24"><path d="{{ $paths[$icons[$loop->index % 6]] }}"/></svg></span><span>{{ $step }}</span></div>@endforeach</div></section>
<section class="ms-packages ms-wrap"><h2>What Our SEO Package Includes</h2><div class="ms-table-scroll"><table><thead><tr><th></th>@foreach(['Lite','Standard','Pro','Enterprise'] as $plan)<th>{{ $plan }}<br><a href="{{ route('contact') }}">Get Proposal</a></th>@endforeach</tr></thead><tbody><tr class="ms-table-group"><th colspan="5">First Month Activities</th></tr><tr><td>SEO Audit</td>@for($i=0;$i<4;$i++)<td>Advanced with Strategy</td>@endfor</tr>@foreach(['Initial Setup & Analysis'=>$setup,'E-commerce SEO'=>$commerce] as $group=>$rows)<tr class="ms-table-group"><th colspan="5">{{ $group }}</th></tr>@foreach($rows as $row)<tr><td>{{ $row }}</td>@for($i=0;$i<4;$i++)<td><span class="ms-check" aria-label="Included">✓</span></td>@endfor</tr>@endforeach @endforeach</tbody></table></div></section>
<section class="ms-faq"><div class="ms-faq-inner"><h2>FAQs on SEO Services</h2>@foreach($faqs as $faq)<details @if($loop->first) open @endif><summary>{{ $faq['title'] }}</summary><p>{{ $faq['copy'] }}</p></details>@endforeach</div></section>
</article>
@endsection
