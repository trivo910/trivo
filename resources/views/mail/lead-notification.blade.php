<!doctype html>
<html lang="en"><body>
    <h1>New website enquiry #{{ $lead->id }}</h1>
    <p>Name: {{ $lead->name }}</p>
    <p>Email: {{ $lead->email }}</p>
    <p>Phone: {{ $lead->phone }}</p>
    <p>Company: {{ $lead->company ?: 'Not provided' }}</p>
    <p>Enquiry: {{ $lead->service }}</p>
    <p style="white-space: pre-wrap">{{ $lead->message }}</p>
    <p><a href="{{ route('admin.leads.show', $lead) }}">View enquiry in Admin Leads</a></p>
    <p>Reply to this email to contact the enquirer.</p>
</body></html>
