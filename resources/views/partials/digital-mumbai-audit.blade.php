<form class="dm-audit-form" data-lead-form action="{{ route('enquiries.store') }}" method="POST">
    @csrf
    <input type="hidden" name="form" value="audit">
    @include('partials.enquiry-errors', ['formKey' => 'audit'])
    @include('partials.enquiry-honeypot')
    <label class="sr-only" for="dm-audit-name">Your name</label><input id="dm-audit-name" name="name" placeholder="Your name" autocomplete="name" maxlength="150" value="{{ old('form') === 'audit' ? old('name') : '' }}" required>
    <label class="sr-only" for="dm-audit-email">Email</label><input id="dm-audit-email" type="email" name="email" placeholder="Email" autocomplete="email" maxlength="254" value="{{ old('form') === 'audit' ? old('email') : '' }}" required>
    <label class="sr-only" for="dm-audit-phone">Phone number</label><input id="dm-audit-phone" type="tel" name="phone" placeholder="Phone number" autocomplete="tel" maxlength="50" value="{{ old('form') === 'audit' ? old('phone') : '' }}" required>
    <button type="submit">Request My Free Audit →</button>
</form>
