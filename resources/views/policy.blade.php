@extends('layouts.app')
@section('title', 'Child Safety Standards Policy')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">{{ __('policy.child_safety_standards_policy') }}</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">{{ __('policy.1_age_restriction') }}</h4>
                <p>{{ __('policy.to_comply_with_google_play_s_policies_o') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('policy.2_content_moderation_safety') }}</h4>
                <p>{{ __('policy.our_platform_employs_ai_driven_content') }}</p>
                <ul>
                    <li>{{ __('policy.explicit_content') }}</li>
                    <li>{{ __('policy.hate_speech') }}</li>
                    <li>{{ __('policy.harassment_or_bullying') }}</li>
                </ul>
                <p>{{ __('policy.all_reported_content_is_reviewed_by_our_') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('policy.3_parental_controls_reporting') }}</h4>
                <p>{{ __('policy.parents_and_guardians_can_contact_us_if_') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('policy.4_data_protection_privacy') }}</h4>
                <p>{{ __('policy.we_adhere_to_gdpr_ccpa_regulations') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('policy.5_contact_information') }}</h4>
                <p>{{ __('policy.if_you_have_any_concerns_about_child_saf') }}</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:talbna@talbna.cloud" class="text-primary">{{ __('policy.talbna_talbna_cloud') }}</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">{{ __('policy.https_talbna_cloud_policy') }}</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Talabna. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
