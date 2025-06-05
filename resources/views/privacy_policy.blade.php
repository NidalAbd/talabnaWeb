@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4 text-primary fw-bold">Privacy Policy</h2>
                <p class="text-muted text-center">Last Updated: {{ date('F d, Y') }}</p>

                <div class="border-bottom mb-4"></div>

                <h4 class="fw-bold text-secondary">1. Introduction</h4>
                <p>Welcome to Talabna. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we look after your personal data when you visit our website or use our mobile application and tell you about your privacy rights and how the law protects you.</p>

                <h4 class="fw-bold text-secondary mt-4">2. Data We Collect</h4>
                <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                <ul>
                    <li><strong>Identity Data</strong> includes first name, last name, username or similar identifier, and date of birth.</li>
                    <li><strong>Contact Data</strong> includes email address, telephone numbers, and physical address.</li>
                    <li><strong>Technical Data</strong> includes internet protocol (IP) address, your login data, browser type and version, device identifier, location data, and other technology on the devices you use to access this website or app.</li>
                    <li><strong>Usage Data</strong> includes information about how you use our website, products, and services.</li>
                    <li><strong>Marketing and Communications Data</strong> includes your preferences in receiving marketing from us and our third parties and your communication preferences.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">3. How We Use Your Data</h4>
                <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                <ul>
                    <li>To register you as a new customer or user.</li>
                    <li>To deliver services to you according to the contract we have with you.</li>
                    <li>To manage our relationship with you.</li>
                    <li>To improve our website, products/services, marketing, or customer relationships.</li>
                    <li>To recommend products or services that may be of interest to you.</li>
                    <li>To comply with a legal or regulatory obligation.</li>
                </ul>

                <h4 class="fw-bold text-secondary mt-4">4. Data Sharing and Disclosure</h4>
                <p>We may share your personal data with the parties set out below:</p>
                <ul>
                    <li>Service providers who provide IT and system administration services.</li>
                    <li>Professional advisers including lawyers, bankers, auditors, and insurers.</li>
                    <li>Government bodies that require us to report processing activities.</li>
                    <li>Third parties to whom we may choose to sell, transfer, or merge parts of our business or our assets.</li>
                </ul>
                <p>We require all third parties to respect the security of your personal data and to treat it in accordance with the law.</p>

                <h4 class="fw-bold text-secondary mt-4">5. International Transfers</h4>
                <p>We may transfer, store, and process your information in countries other than your own. Our servers may be located outside your country of residence. These countries may have data protection laws that differ from the laws of your country.</p>

                <h4 class="fw-bold text-secondary mt-4">6. Data Security</h4>
                <p>We have implemented appropriate security measures to prevent your personal data from being accidentally lost, used, or accessed in an unauthorized way, altered, or disclosed. We also limit access to your personal data to those employees, agents, contractors, and other third parties who have a business need to know.</p>

                <h4 class="fw-bold text-secondary mt-4">7. Data Retention</h4>
                <p>We will only retain your personal data for as long as necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, accounting, or reporting requirements.</p>

                <h4 class="fw-bold text-secondary mt-4">8. Your Legal Rights</h4>
                <p>Under certain circumstances, you have rights under data protection laws in relation to your personal data, including:</p>
                <ul>
                    <li>The right to request access to your personal data.</li>
                    <li>The right to request correction of your personal data.</li>
                    <li>The right to request erasure of your personal data.</li>
                    <li>The right to object to processing of your personal data.</li>
                    <li>The right to request restriction of processing your personal data.</li>
                    <li>The right to request transfer of your personal data.</li>
                    <li>The right to withdraw consent.</li>
                </ul>
                <p>If you wish to exercise any of these rights, please contact us.</p>

                <h4 class="fw-bold text-secondary mt-4">9. Third-Party Links</h4>
                <p>Our service may contain links to other websites, apps, or services that are not operated by us. If you click on a third-party link, you will be directed to that third party's site. We strongly advise you to review the Privacy Policy of every site you visit.</p>

                <h4 class="fw-bold text-secondary mt-4">10. Social Media Integration</h4>
                <p>We may offer you the opportunity to use your social media login (such as Facebook or Google) when interacting with our Service. If you do so, please be aware that you may be sharing certain profile information with us. The specific information shared will depend on your social media provider's privacy policy.</p>
                <p>Social media features may collect your IP address and which page you're visiting on our site, and may set cookies to enable the feature to function properly. Your interactions with these features are governed by the privacy policy of the company providing them.</p>

                <h4 class="fw-bold text-secondary mt-4">11. Changes to This Privacy Policy</h4>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date at the top of this page.</p>

                <h4 class="fw-bold text-secondary mt-4">12. Contact Us</h4>
                <p>If you have any questions about this Privacy Policy, please contact us:</p>
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
