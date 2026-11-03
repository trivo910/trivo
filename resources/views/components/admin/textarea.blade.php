@props(['name', 'label', 'value' => '', 'help' => null])
<div class="field"><label for="{{ $name }}">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if($help || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif {{ $attributes->class(['admin-input']) }}>{{ $value }}</textarea>
    @error($name)<p id="{{ $name }}-help" class="field-error">{{ $message }}</p>@else @if($help)<p id="{{ $name }}-help" class="helper">{{ $help }}</p>@endif @enderror
</div>
