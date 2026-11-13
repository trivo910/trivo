<?php

namespace App\Services\Leads;

use App\Models\Lead;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class LeadInbox
{
    public const STATUSES = ['new' => 'New', 'in_progress' => 'In progress', 'contacted' => 'Contacted',
        'qualified' => 'Qualified', 'closed' => 'Closed', 'spam' => 'Spam'];

    public const EXPORT_LIMIT = 1000;

    /** @param array<string, mixed> $filters
     * @return Builder<Lead>
     */
    public function query(array $filters): Builder
    {
        $query = Lead::query();
        if (isset($filters['q']) && $filters['q'] !== '') {
            $needle = '%'.$filters['q'].'%';
            $query->where(fn (Builder $query) => $query->where('name', 'like', $needle)->orWhere('email', 'like', $needle)
                ->orWhere('phone', 'like', $needle)->orWhere('company', 'like', $needle));
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['service']) && $filters['service'] !== '') {
            $query->where('service', $filters['service']);
        }
        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from'].' 00:00:00');
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<', CarbonImmutable::createFromFormat('!Y-m-d', $filters['to'], 'UTC')->addDay()->format('Y-m-d H:i:s'));
        }

        return $query;
    }

    public function csvCell(?string $value): string
    {
        $value ??= '';

        return preg_match('/^(?:[=+\-@\t\r\n]|[\s\p{Z}\x{FEFF}\x00-\x1F]*[=+\-@])/u', $value) ? "'".$value : $value;
    }
}
