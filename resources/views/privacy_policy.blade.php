@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">Privacy Policy</h2>
                <p class="text-muted text-center">Last Updated: October 2, 2026</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">1. Introduction</h4>
                <p>Talabna is a marketplace app and website where you can post and browse listings for jobs, real estate, cars, devices and services, share short video reels, and chat with other users. This policy explains what information we collect, why we collect it, who we share it with, how long we keep it, and the choices you have.</p>

                <h4 class="fw-bold text-secondary mt-4">2. Information We Collect</h4>
                <p>We collect only the information needed to run the marketplace:</p>
                <ul>
                    <li><strong>Account data:</strong> name, email address, phone number, profile photo, and your Google or Apple sign-in identifier if you use one.</li>
                    <li><strong>Listings and reels:</strong> titles, descriptions, prices, categories, photos and videos you publish. These are visible to other users.</li>
                    <li><strong>Location:</strong> the location you attach to a listing, and your device location only when you allow it, to show listings near you.</li>
                    <li><strong>Messages:</strong> chats you exchange with other users, stored so both sides can read them.</li>
                    <li><strong>Points and purchases:</strong> your points balance and history, and App Store or Google Play purchase records used to credit points and subscriptions. We never receive your card details.</li>
                    <li><strong>Device data:</strong> device model, operating system, app version, IP address and a push-notification token.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">3. How We Use Your Information</h4>
                <p>We use this information to:</p>
                <ul>
                    <li>Create and manage your account and sign you in.</li>
                    <li>Publish your listings and reels and show them to people browsing by category or location.</li>
                    <li>Deliver chat messages and notify you about activity on your account and listings.</li>
                    <li>Credit purchased points and subscriptions and keep your points history.</li>
                    <li>Run the optional AI tools you choose to use, such as improving a description, translating a listing, suggesting a category or price, or generating an image.</li>
                    <li>Prevent spam, fraud and content that breaks our rules, review content and users you report, and stop users you block from contacting you.</li>
                    <li>Answer your support requests and fix problems.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">4. Sharing Your Information</h4>
                <p>We do not sell your personal information and we do not use it for third-party advertising. We share it only in these cases:</p>
                <ul>
                    <li><strong>Other users:</strong> your listings, reels, public profile and any contact details you add to a listing are visible to other users.</li>
                    <li><strong>Service providers:</strong> Google Firebase (notifications), Google Maps (maps and location), Google and Apple (sign-in), Apple App Store and Google Play (payments), and our hosting provider (Hostinger).</li>
                    <li><strong>AI provider:</strong> when you use an AI tool, the text or image you submit is sent to OpenAI only to produce that result.</li>
                    <li><strong>Legal reasons:</strong> when the law requires it, or to protect users from fraud or harm.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">5. Data Retention and Account Deletion</h4>
                <p>We keep your information while your account is active. You can delete your account at any time in the app from <strong>Settings → Delete Account</strong>, or by emailing us. Deleting your account removes your profile, listings, reels and points history, except records we must keep by law (for example purchase records).</p>

                <h4 class="fw-bold text-secondary mt-4">6. Security</h4>
                <p>We protect your data with encrypted connections (HTTPS) and access controls on our servers. No system is perfectly secure, so please do not share passwords or payment details in chats.</p>

                <h4 class="fw-bold text-secondary mt-4">7. Children</h4>
                <p>Talabna is not intended for children under 13, and we do not knowingly collect information from them. If you believe a child has given us personal information, contact us and we will delete it.</p>

                <h4 class="fw-bold text-secondary mt-4">8. Your Choices</h4>
                <ul>
                    <li>Edit or delete your listings, reels and profile at any time in the app.</li>
                    <li>Report a listing, reel or user, and block users you don't want to hear from; you can manage blocked users in Settings.</li>
                    <li>Turn location access and notifications on or off in your device settings.</li>
                    <li>Request a copy of your data or its deletion by contacting us.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">9. Changes to This Privacy Policy</h4>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date at the top of this page.</p>

                <h4 class="fw-bold text-secondary mt-4">10. Contact Us</h4>
                <p>If you have questions about this Privacy Policy, or want to request access to or deletion of your data, contact us:</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:support@talbna.cloud" class="text-primary">support@talbna.cloud</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">https://talbna.cloud/policy</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Talabna. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
