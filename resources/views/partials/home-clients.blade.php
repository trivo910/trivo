@php
    $headingId = $headingId ?? 'home-clients-title';
    $showTrustedPartnershipsBadge = $showTrustedPartnershipsBadge ?? false;
    $clients = [
        ['Sanjari-2.png', 'Sanjari'],
        ['Polla-Tech.png', 'Polla Tech'],
        ['Al-Bushra.png', 'Al Bushra'],
        ['Mumbai-Darshan.png', 'Mumbai Darshan'],
        ['Brady-Morris.png', 'Brady Morris'],
        ['Stock-Sovet.png', 'Stock Sovet'],
        ['Stop-Over.png', 'Stop Over'],
        ['Quba-Dates.png', 'Quba Dates'],
        ['Medikash.png', 'Medikash'],
        ['MYVN.png', 'MYVN'],
        ['Al-Arafat.png', 'Al-Arafat'],
        ['Al-Rida.png', 'Al-Rida'],
        ['Gulshan-Tours.png', 'Gulshan Tours'],
        ['Global-Duniya.png', 'Global Duniya'],
    ];
    $clientRows = array_chunk($clients, (int) ceil(count($clients) / 2));
@endphp

<section class="home-section home-section--clients" aria-labelledby="{{ $headingId }}">
    <div class="home-section__group">
        @if ($showTrustedPartnershipsBadge)
            <p class="home-clients__trusted-badge">Trusted Partnerships</p>
        @endif
        <h3 id="{{ $headingId }}">Clients we work with</h3>
        @foreach ($clientRows as $rowIndex => $clientRow)
            <div class="home-clients__row home-clients__row--{{ $rowIndex + 1 }}" aria-label="Client logos row {{ $rowIndex + 1 }}">
                @for ($copyIndex = 0; $copyIndex < 3; $copyIndex++)
                    <div class="home-clients__track" @if ($copyIndex > 0) aria-hidden="true" inert @endif>
                        @foreach ($clientRow as [$image, $name])
                            <figure>
                                <img src="{{ asset('wp-content/uploads/2026/03/'.$image) }}" alt="{{ $copyIndex === 0 ? $name : '' }}" width="300" height="135" loading="lazy">
                            </figure>
                        @endforeach
                    </div>
                @endfor
            </div>
        @endforeach
    </div>
</section>
