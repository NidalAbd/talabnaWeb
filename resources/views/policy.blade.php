@extends('layouts.app')

@section('title', 'Child Safety Standards Policy')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">Child Safety Standards Policy</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">1. Age Restriction</h4>
                <p>To comply with Google Play's policies, our app is intended for users **18 years and older**. We do not allow users under 18 to register or interact within the platform.</p>

                <h4 class="fw-bold text-secondary mt-4">2. Content Moderation & Safety</h4>
                <p>Our platform employs **AI-driven content moderation** to detect inappropriate content, including but not limited to:</p>
                <ul>
                    <li>Explicit content</li>
                    <li>Hate speech</li>
                    <li>Harassment or bullying</li>
                </ul>
                <p>All reported content is reviewed by our team within **24 hours**.</p>

                <h4 class="fw-bold text-secondary mt-4">3. Parental Controls & Reporting</h4>
                <p>Parents and guardians can contact us if they suspect any violations. Users can report content directly in-app via the **Report** button.</p>

                <h4 class="fw-bold text-secondary mt-4">4. Data Protection & Privacy</h4>
                <p>We adhere to **GDPR & CCPA** regulations to protect children's privacy. No personal data is collected from users under 18.</p>

                <h4 class="fw-bold text-secondary mt-4">5. Contact Information</h4>
                <p>If you have any concerns about child safety, please reach out to our support team:</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:talbna@talbna.cloud" class="text-primary">talbna@talbna.cloudaaa</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">https://talbna.cloud/policy</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Your App Name. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
