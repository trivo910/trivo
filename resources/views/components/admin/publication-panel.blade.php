@props(['model', 'history', 'routePrefix', 'label' => 'page'])
<x-admin.glass-card strong>
    <h2>Publication</h2><p>{{ ucfirst($model->status) }}{{ $model->published_at ? ' · '.$model->published_at->format('Y-m-d H:i').' UTC' : '' }}</p>
    <p class="helper">{{ $model instanceof \App\Models\PortfolioItem && isset($model->details['case_study']) ? 'Publishing makes this profile available at its URL. Published profiles remain editable; saving updates the live content.' : ($model instanceof \App\Models\Page ? 'Publishing makes this page available at its URL. Published pages remain editable.' : 'Manage publication and scheduling here. Availability on the public website depends on the content template.') }}</p>
    @if($model->publication_error)<p class="field-error" role="status">Scheduled publication is blocked. Review content, SEO, images and URL conflicts, then cancel or reschedule.</p>@endif
    @can('publish', $model)
        <form method="POST" action="{{ route($routePrefix.'.publication', $model) }}" class="form-stack">@csrf<input type="hidden" name="content_version" value="{{ $model->content_version }}">
            <x-admin.input name="scheduled_for" label="Scheduled date and time (UTC)" type="datetime-local" :value="old('scheduled_for', $model->status === 'scheduled' ? $model->published_at?->format('Y-m-d\TH:i') : '')" help="Choose a future UTC date to schedule. Scheduling permanently reserves this path, even after cancellation." />
            <div class="section-actions"><x-admin.button type="submit" name="operation" value="publish">Publish now</x-admin.button><x-admin.button type="submit" name="operation" value="schedule" variant="secondary">Schedule {{ $label }}</x-admin.button>
                @can('cancelPublication', $model)<x-admin.button type="submit" name="operation" value="cancel" variant="secondary">Cancel schedule</x-admin.button>@endcan
            </div>
        </form>
    @endcan
    @if($history->isNotEmpty())<h3>Recent publication changes</h3><ul>@foreach($history as $change)<li>{{ str_replace('_', ' ', ucfirst($change->snapshot['event'])) }} · {{ $change->created_at->format('Y-m-d H:i:s') }} UTC</li>@endforeach</ul>@endif
</x-admin.glass-card>
