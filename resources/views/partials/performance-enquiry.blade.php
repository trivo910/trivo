<form class="performance-marketing-form" data-lead-form method="POST" action="{{ route('enquiries.store') }}" aria-label="{{ $prefix === 'performance-hero' ? 'Discuss performance marketing in Nagpur' : 'Contact Trivo in Nagpur' }}">
@csrf
<input type="hidden" name="form" value="contact">
@include('partials.enquiry-errors', ['formKey' => 'contact'])
@include('partials.enquiry-honeypot')
@foreach([['name','Name','text','name',150],['email','Email','email','email',254],['company','Company name','text','organization',255],['phone','Phone number','tel','tel',50]] as [$name,$label,$type,$autocomplete,$limit])
<label class="sr-only" for="{{ $prefix }}-{{ $name }}">{{ $label }}</label><input id="{{ $prefix }}-{{ $name }}" type="{{ $type }}" name="{{ $name }}" placeholder="{{ $label }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $limit }}" value="{{ old('form') === 'contact' ? old($name) : '' }}" @if($name !== 'company') required @endif>
@endforeach
<label class="sr-only" for="{{ $prefix }}-message">What can we help you with?</label><textarea id="{{ $prefix }}-message" name="message" placeholder="What can we help you with?" rows="4" maxlength="10000" required>{{ old('form') === 'contact' ? old('message') : '' }}</textarea>
<button type="submit">GET IN TOUCH <span aria-hidden="true">→</span></button>
</form>
