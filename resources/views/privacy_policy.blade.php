@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">{{ __('privacy_policy.privacy_policy') }}</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">{{ __('privacy_policy.1_introduction') }}</h4>
                <p>{{ __('privacy_policy.welcome_to_talabna_we_respect_your_priv') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.2_data_we_collect') }}</h4>
                <p>{{ __('privacy_policy.we_may_collect_use_store_and_transfer_') }}</p>
                <ul>
                    <li><strong>{{ __('privacy_policy.identity_data') }}</strong> includes first name, last name, username or similar identifier, and date of birth.</li>
                    <li><strong>{{ __('privacy_policy.contact_data') }}</strong> includes email address, telephone numbers, and physical address.</li>
                    <li><strong>{{ __('privacy_policy.technical_data') }}</strong> includes internet protocol (IP) address, your login data, browser type and version, device identifier, location data, and other technology on the devices you use to access this website or app.</li>
                    <li><strong>{{ __('privacy_policy.usage_data') }}</strong> includes information about how you use our website, products, and services.</li>
                    <li><strong>{{ __('privacy_policy.marketing_and_communications_data') }}</strong> includes your preferences in receiving marketing from us and our third parties and your communication preferences.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.3_how_we_use_your_data') }}</h4>
                <p>{{ __('privacy_policy.we_will_only_use_your_personal_data_when') }}</p>
                <ul>
                    <li>{{ __('privacy_policy.to_register_you_as_a_new_customer_or_use') }}</li>
                    <li>{{ __('privacy_policy.to_deliver_services_to_you_according_to_') }}</li>
                    <li>{{ __('privacy_policy.to_manage_our_relationship_with_you_') }}</li>
                    <li>{{ __('privacy_policy.to_improve_our_website_products_service') }}</li>
                    <li>{{ __('privacy_policy.to_recommend_products_or_services_that_m') }}</li>
                    <li>{{ __('privacy_policy.to_comply_with_a_legal_or_regulatory_obl') }}</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.4_data_sharing_and_disclosure') }}</h4>
                <p>{{ __('privacy_policy.we_may_share_your_personal_data_with_the') }}</p>
                <ul>
                    <li>{{ __('privacy_policy.service_providers_who_provide_it_and_sys') }}</li>
                    <li>{{ __('privacy_policy.professional_advisers_including_lawyers_') }}</li>
                    <li>{{ __('privacy_policy.government_bodies_that_require_us_to_rep') }}</li>
                    <li>{{ __('privacy_policy.third_parties_to_whom_we_may_choose_to_s') }}</li>
                </ul>
                <p>{{ __('privacy_policy.we_require_all_third_parties_to_respect_') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.5_international_transfers') }}</h4>
                <p>{{ __('privacy_policy.we_may_transfer_store_and_process_your') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.6_data_security') }}</h4>
                <p>{{ __('privacy_policy.we_have_implemented_appropriate_security') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.7_data_retention') }}</h4>
                <p>{{ __('privacy_policy.we_will_only_retain_your_personal_data_f') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.8_your_legal_rights') }}</h4>
                <p>{{ __('privacy_policy.under_certain_circumstances_you_have_ri') }}</p>
                <ul>
                    <li>{{ __('privacy_policy.the_right_to_request_access_to_your_pers') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_request_correction_of_your_') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_request_erasure_of_your_per') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_object_to_processing_of_you') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_request_restriction_of_proc') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_request_transfer_of_your_pe') }}</li>
                    <li>{{ __('privacy_policy.the_right_to_withdraw_consent_') }}</li>
                </ul>
                <p>{{ __('privacy_policy.if_you_wish_to_exercise_any_of_these_rig') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.9_third_party_links') }}</h4>
                <p>{{ __('privacy_policy.our_service_may_contain_links_to_other_w') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.10_social_media_integration') }}</h4>
                <p>{{ __('privacy_policy.we_may_offer_you_the_opportunity_to_use_') }}</p>
                <p>{{ __('privacy_policy.social_media_features_may_collect_your_i') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.11_changes_to_this_privacy_policy') }}</h4>
                <p>{{ __('privacy_policy.we_may_update_our_privacy_policy_from_ti') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{ __('privacy_policy.12_contact_us') }}</h4>
                <p>{{ __('privacy_policy.if_you_have_any_questions_about_this_pri') }}</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:talbna@talbna.cloud" class="text-primary">{{ __('privacy_policy.talbna_talbna_cloud') }}</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">{{ __('privacy_policy.https_talbna_cloud_policy') }}</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Talabna. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
