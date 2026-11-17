@if(old('form') === $formKey && $errors->any())
    <div role="alert" style="grid-column: 1 / -1; color: #b42318">
        <p>Please check your enquiry:</p>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
