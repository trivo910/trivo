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
    <form class="home-enquiry__form" data-home-enquiry>
        @unless($isAudit)<h3>Get In Touch</h3>@endunless
        <div class="home-enquiry__progress" aria-hidden="true"></div>
        <div class="home-enquiry__fields">
            <input type="text" name="name" placeholder="Your name" aria-label="Your name" autocomplete="name" required>
            <input type="email" name="email" placeholder="Email" aria-label="Email" autocomplete="email" required>
            <input type="tel" name="phone" placeholder="Phone number" aria-label="Phone number" autocomplete="tel" required>
            <input type="text" name="company" placeholder="Company name" aria-label="Company name" autocomplete="organization">
        </div>
        <button type="submit">{{ $isAudit ? 'Request a free audit' : 'Send me a proposal' }} <span aria-hidden="true">→</span></button>
        <p class="home-enquiry__hint">Opens your email app to send your enquiry.</p>
    </form>
</section>
