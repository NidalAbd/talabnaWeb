@extends('layouts.app')
@section('title', 'Terms of Service')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">Terms of Service</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">1. Acceptance of Terms</h4>
                <p>By accessing or using the Talabna service, whether through our website or mobile application, you agree to be bound by these Terms of Service. If you do not agree to all the terms and conditions of this agreement, you may not access or use our services.</p>

                <h4 class="fw-bold text-secondary mt-4">2. Service Description</h4>
                <p>Talabna provides a platform for users to find and offer services. Our platform connects service providers with customers seeking their services. Users can browse, post, and interact with service listings in various categories.</p>

                <h4 class="fw-bold text-secondary mt-4">3. User Accounts</h4>
                <p><strong>3.1 Account Creation:</strong> To use certain features of our Service, you must register for an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>

                <p><strong>3.2 Account Responsibility:</strong> You are responsible for safeguarding the password that you use to access the Service. You agree not to disclose your password to any third party and to take sole responsibility for any activities or actions under your account.</p>

                <p><strong>3.3 Account Termination:</strong> We reserve the right to suspend or terminate your account at any time for any reason, including but not limited to a violation of these Terms.</p>

                <h4 class="fw-bold text-secondary mt-4">4. User Eligibility</h4>
                <p>Our service is intended for users who are at least 18 years of age. By using our service, you represent and warrant that you are at least 18 years old and that you have the right, authority, and capacity to enter into these Terms and to abide by all of the terms and conditions set forth herein.</p>

                <h4 class="fw-bold text-secondary mt-4">5. User Content</h4>
                <p><strong>5.1 Content Responsibility:</strong> You are solely responsible for the content you post on our platform, including service listings, comments, messages, and any other information.</p>

                <p><strong>5.2 Prohibited Content:</strong> You agree not to post content that:</p>
                <ul>
                    <li>Is illegal, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, or invasive of another's privacy</li>
                    <li>Infringes on the intellectual property rights of any party</li>
                    <li>Contains software viruses or any other code designed to interrupt, destroy, or limit the functionality of any computer software or hardware</li>
                    <li>Advertises or solicits any illegal activities or unauthorized commercial activities</li>
                    <li>Impersonates any person or entity, or falsely states or otherwise misrepresents your affiliation with a person or entity</li>
                </ul>

                <p><strong>5.3 Content Removal:</strong> We reserve the right to remove any content that violates these Terms or that we find objectionable for any reason, without prior notice.</p>

                <h4 class="fw-bold text-secondary mt-4">6. Payment Terms</h4>
                <p><strong>6.1 Fees:</strong> Some features of our service may require payment of fees. You agree to pay all applicable fees as described on our platform.</p>

                <p><strong>6.2 Payment Processing:</strong> We use third-party payment processors to bill you. By providing your payment information, you authorize us to charge your payment method for all applicable fees.</p>

                <p><strong>6.3 Refunds:</strong> All payments are non-refundable except as required by law or as explicitly stated in our refund policy.</p>

                <h4 class="fw-bold text-secondary mt-4">7. Points System</h4>
                <p><strong>7.1 Points:</strong> Our platform may utilize a points system for certain features or promotions. Points have no cash value and cannot be redeemed for cash.</p>

                <p><strong>7.2 Points Expiration:</strong> Points may expire as specified in our points policy.</p>

                <p><strong>7.3 Points Revocation:</strong> We reserve the right to revoke points if we determine that they were issued in error or obtained fraudulently.</p>

                <h4 class="fw-bold text-secondary mt-4">8. Intellectual Property</h4>
                <p><strong>8.1 Our Intellectual Property:</strong> The Service and its original content, features, and functionality are and will remain the exclusive property of Talabna and its licensors.</p>

                <p><strong>8.2 Your License to Us:</strong> By posting content on our platform, you grant us a non-exclusive, transferable, sub-licensable, royalty-free, worldwide license to use, copy, modify, create derivative works based on, distribute, publicly display, publicly perform, and otherwise exploit such content in all formats and distribution channels now known or hereafter devised.</p>

                <h4 class="fw-bold text-secondary mt-4">9. Third-Party Links</h4>
                <p>Our Service may contain links to third-party websites or services that are not owned or controlled by Talabna. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party websites or services.</p>

                <h4 class="fw-bold text-secondary mt-4">10. Limitation of Liability</h4>
                <p>In no event shall Talabna, its directors, employees, partners, agents, suppliers, or affiliates be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the Service.</p>

                <h4 class="fw-bold text-secondary mt-4">11. Warranty Disclaimer</h4>
                <p>Your use of the Service is at your sole risk. The Service is provided on an "AS IS" and "AS AVAILABLE" basis. The Service is provided without warranties of any kind, whether express or implied, including, but not limited to, implied warranties of merchantability, fitness for a particular purpose, non-infringement, or course of performance.</p>

                <h4 class="fw-bold text-secondary mt-4">12. Governing Law</h4>
                <p>These Terms shall be governed and construed in accordance with the laws of [Your Country/State], without regard to its conflict of law provisions.</p>

                <h4 class="fw-bold text-secondary mt-4">13. Changes to Terms</h4>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>

                <h4 class="fw-bold text-secondary mt-4">14. Contact Us</h4>
                <p>If you have any questions about these Terms, please contact us:</p>
                <ul class="list-unstyled">
                    <li>📧 Email: <a href="mailto:talbna@talbna.cloud" class="text-primary">talbna@talbna.cloud</a></li>
                    <li>🌍 Website: <a href="https://talbna.cloud/policy" class="text-primary">https://talbna.cloud/policy</a></li>
                </ul>

                <div class="border-top mt-5 pt-3 text-center">
                    <p class="text-muted">© {{ date('Y') }} Talabna. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
