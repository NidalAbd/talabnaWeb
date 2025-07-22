@extends('layouts.app')
@section('title', 'Terms of Service')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">{{('terms_of_service.terms_of_service') }}</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">{{('terms_of_service.1_acceptance_of_terms') }}</h4>
                <p>{{('terms_of_service.by_accessing_or_using_the_talabna_servic') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.2_service_description') }}</h4>
                <p>{{('terms_of_service.talabna_provides_a_platform_for_users_to') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.3_user_accounts') }}</h4>
                <p><strong>{{('terms_of_service.3_1_account_creation_') }}</strong> To use certain features of our Service, you must register for an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>

                <p><strong>{{('terms_of_service.3_2_account_responsibility_') }}</strong> You are responsible for safeguarding the password that you use to access the Service. You agree not to disclose your password to any third party and to take sole responsibility for any activities or actions under your account.</p>

                <p><strong>{{('terms_of_service.3_3_account_termination_') }}</strong> We reserve the right to suspend or terminate your account at any time for any reason, including but not limited to a violation of these Terms.</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.4_user_eligibility') }}</h4>
                <p>{{('terms_of_service.our_service_is_intended_for_users_who_ar') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.5_user_content') }}</h4>
                <p><strong>{{('terms_of_service.5_1_content_responsibility_') }}</strong> You are solely responsible for the content you post on our platform, including service listings, comments, messages, and any other information.</p>

                <p><strong>{{('terms_of_service.5_2_prohibited_content_') }}</strong> You agree not to post content that:</p>
                <ul>
                    <li>{{('terms_of_service.is_illegal_harmful_threatening_abusiv') }}</li>
                    <li>{{('terms_of_service.infringes_on_the_intellectual_property_r') }}</li>
                    <li>{{('terms_of_service.contains_software_viruses_or_any_other_c') }}</li>
                    <li>{{('terms_of_service.advertises_or_solicits_any_illegal_activ') }}</li>
                    <li>{{('terms_of_service.impersonates_any_person_or_entity_or_fa') }}</li>
                </ul>

                <p><strong>{{('terms_of_service.5_3_content_removal_') }}</strong> We reserve the right to remove any content that violates these Terms or that we find objectionable for any reason, without prior notice.</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.6_payment_terms') }}</h4>
                <p><strong>{{('terms_of_service.6_1_fees_') }}</strong> Some features of our service may require payment of fees. You agree to pay all applicable fees as described on our platform.</p>

                <p><strong>{{('terms_of_service.6_2_payment_processing_') }}</strong> We use third-party payment processors to bill you. By providing your payment information, you authorize us to charge your payment method for all applicable fees.</p>

                <p><strong>{{('terms_of_service.6_3_refunds_') }}</strong> All payments are non-refundable except as required by law or as explicitly stated in our refund policy.</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.7_points_system') }}</h4>
                <p><strong>{{('terms_of_service.7_1_points_') }}</strong> Our platform may utilize a points system for certain features or promotions. Points have no cash value and cannot be redeemed for cash.</p>

                <p><strong>{{('terms_of_service.7_2_points_expiration_') }}</strong> Points may expire as specified in our points policy.</p>

                <p><strong>{{('terms_of_service.7_3_points_revocation_') }}</strong> We reserve the right to revoke points if we determine that they were issued in error or obtained fraudulently.</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.8_intellectual_property') }}</h4>
                <p><strong>{{('terms_of_service.8_1_our_intellectual_property_') }}</strong> The Service and its original content, features, and functionality are and will remain the exclusive property of Talabna and its licensors.</p>

                <p><strong>{{('terms_of_service.8_2_your_license_to_us_') }}</strong> By posting content on our platform, you grant us a non-exclusive, transferable, sub-licensable, royalty-free, worldwide license to use, copy, modify, create derivative works based on, distribute, publicly display, publicly perform, and otherwise exploit such content in all formats and distribution channels now known or hereafter devised.</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.9_third_party_links') }}</h4>
                <p>{{('terms_of_service.our_service_may_contain_links_to_third_p') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.10_limitation_of_liability') }}</h4>
                <p>{{('terms_of_service.in_no_event_shall_talabna_its_directors') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.11_warranty_disclaimer') }}</h4>
                <p>{{('terms_of_service.your_use_of_the_service_is_at_your_sole_') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.12_governing_law') }}</h4>
                <p>{{('terms_of_service.these_terms_shall_be_governed_and_constr') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.13_changes_to_terms') }}</h4>
                <p>{{('terms_of_service.we_reserve_the_right_at_our_sole_discre') }}</p>

                <h4 class="fw-bold text-secondary mt-4">{{('terms_of_service.14_contact_us') }}</h4>
                <p>{{('terms_of_service.if_you_have_any_questions_about_these_te') }}</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:talbna@talbna.cloud" class="text-primary">{{('terms_of_service.talbna_talbna_cloud') }}</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">{{('terms_of_service.https_talbna_cloud_policy') }}</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Talabna. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection







