@props(['name', 'label', 'options', 'value' => null, 'help' => null])
<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if($help || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif {{ $attributes->class(['admin-input']) }}>
        @foreach($options as $key => $text)<option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>@endforeach
    </select>
    @error($name)<p id="{{ $name }}-help" class="field-error">{{ $message }}</p>
    @else @if($help)<p id="{{ $name }}-help" class="helper">{{ $help }}</p>@endif @enderror
</div>
