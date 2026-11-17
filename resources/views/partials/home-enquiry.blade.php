@php($isAudit = $variant === 'audit')
<section class="home-enquiry home-enquiry--{{ $variant }}" @if($isAudit) id="home-audit" @endif aria-label="{{ $isAudit ? 'Free marketing audit' : 'Get in touch' }}">
    <div class="home-enquiry__copy">
        @unless($isAudit)<p>In love with what we do?</p>@endunless
        <h2>{{ $isAudit ? 'Get your free marketing audit' : 'Let’s work together to have new approaches, new ideas and new ways of doing growing business.' }}</h2>
        <ul>
            @if($isAudit)
                <li>30-min strategy call</li><li>In-depth audit</li><li>Growth roadmap</li>
            @else
                <li>Business Analysis – Understanding your brand’s strengths and objectives.</li>
                <li>Audience Insights – Identifying and engaging the right customers.</li>
                <li>Market Evaluation – Keeping up with trends and seizing new opportunities.</li>
            @endif
        </ul>
    </div>
    <form class="home-enquiry__form" data-home-enquiry data-lead-form method="POST" action="{{ route('enquiries.store') }}">
        @csrf
        <input type="hidden" name="form" value="{{ $isAudit ? 'audit' : 'proposal' }}">
        @include('partials.enquiry-errors', ['formKey' => $isAudit ? 'audit' : 'proposal'])
        @include('partials.enquiry-honeypot')
        @unless($isAudit)<h3>Get In Touch</h3>@endunless
        <div class="home-enquiry__progress" aria-hidden="true"></div>
        <div class="home-enquiry__fields">
            <input type="text" name="name" maxlength="150" value="{{ old('form') === ($isAudit ? 'audit' : 'proposal') ? old('name') : '' }}" placeholder="Your name" aria-label="Your name" autocomplete="name" required>
            <input type="email" name="email" maxlength="254" value="{{ old('form') === ($isAudit ? 'audit' : 'proposal') ? old('email') : '' }}" placeholder="Email" aria-label="Email" autocomplete="email" required>
            <input type="tel" name="phone" maxlength="50" value="{{ old('form') === ($isAudit ? 'audit' : 'proposal') ? old('phone') : '' }}" placeholder="Phone number" aria-label="Phone number" autocomplete="tel" required>
            <input type="text" name="company" maxlength="255" value="{{ old('form') === ($isAudit ? 'audit' : 'proposal') ? old('company') : '' }}" placeholder="Company name" aria-label="Company name" autocomplete="organization">
        </div>
        <button type="submit">{{ $isAudit ? 'Request a free audit' : 'Send me a proposal' }} <span aria-hidden="true">→</span></button>
        <p class="home-enquiry__hint">We will contact you about your enquiry.</p>
    </form>
</section>
