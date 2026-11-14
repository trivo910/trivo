@extends('layouts.public')

@section('title', isset($record) ? ($record->seo_title ?: $record->title.' | Trivo') : 'Return & Refund Policy | Trivo')
@section('description', isset($record) ? ($record->seo_description ?: 'Read Trivo’s cancellation and refund policy for digital marketing services and projects.') : 'Read Trivo’s cancellation and refund policy for digital marketing services and projects.')
@section('canonical', isset($record) ? ($record->canonical_url ?: request()->url()) : request()->url())
@section('robots', isset($record) ? ($record->robots ?: config('seo.default_robots')) : config('seo.default_robots'))

@section('content')
    <article class="return-refund-policy">
        <div class="return-refund-policy__content">
            <h1>Return &amp; Refund Policy</h1>
            <p>To provide our customers with the highest quality and customer experience, we have implemented a compassionate cancellation and refund policy. We Divide your work into milestones and plan each project.</p>
            <p>All projects begin with the complete discovery/analysis and creation of work scope documents. This is done so that both parties have a complete understanding of the work required and eliminate the possibility of project cancellation, cancellation, or dispute.</p>
            <p>It takes time to reach each milestone and complete each module, so work already done is not refundable. In the case of an amicable termination of the project, the client manages all completed work and there is no payment for further development. Previous payments or deposits will not be refunded.</p>
            <p>Payment will not be refunded once the client approves the model design and enters the development stage of the project. There are no partial refunds for projects in the middle of the Milestone Phase.</p>
            <p>Services provided by our marketing team on special occasions will not be canceled. These offers are limited in time and cannot be canceled.</p>
            <p>SEO and Digital Marketing Packages are non-refundable, but you can cancel with 30 days’ notice in writing.</p>
            <p>We do not offer deposits or refunds for projects that have been abandoned or have been suspended for more than 30 days.</p>
        </div>
    </article>
@endsection
