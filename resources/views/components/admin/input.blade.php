@props(['name', 'label', 'type' => 'text', 'help' => null])
<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if($errors->has($name) || $help) aria-describedby="{{ $name }}-help" @endif
        {{ $attributes->class(['admin-input']) }}>
    @error($name)<p id="{{ $name }}-help" class="field-error">{{ $message }}</p>
    @else @if($help)<p id="{{ $name }}-help" class="helper">{{ $help }}</p>@endif @enderror
</div>
