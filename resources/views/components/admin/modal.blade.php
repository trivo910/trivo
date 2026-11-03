@props(['title', 'label' => 'Open dialog', 'id' => 'admin-dialog'])
<div x-data="adminModal">
    <x-admin.button variant="secondary" @click="open" aria-haspopup="dialog">{{ $label }}</x-admin.button>
    <dialog x-ref="dialog" class="glass glass-strong admin-dialog" aria-labelledby="{{ $id }}-title">
        <h2 id="{{ $id }}-title">{{ $title }}</h2>{{ $slot }}
        <div class="dialog-actions"><x-admin.button @click="close">Close</x-admin.button></div>
    </dialog>
</div>
