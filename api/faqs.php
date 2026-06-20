<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$faqs = [
    [
        'id' => 1,
        'question' => 'What is Asaan Capital?',
        'answer' => 'Asaan Capital is Nepal\'s premier online marketplace connecting business owners, entrepreneurs, and franchisors with qualified investors. We facilitate business sales, investment opportunities, and franchise expansion across Nepal.',
    ],
    [
        'id' => 2,
        'question' => 'How do I create an account?',
        'answer' => 'Click the "Sign Up" button and choose your role — Investor, Business Owner, Entrepreneur, or Franchisor. Fill in your details and verify your email address to get started.',
    ],
    [
        'id' => 3,
        'question' => 'Is Asaan Capital free to use?',
        'answer' => 'Registration and browsing are free. Premium features such as unlimited interest requests, advanced analytics, and priority listing are available through our subscription plans.',
    ],
    [
        'id' => 4,
        'question' => 'How does the matching process work?',
        'answer' => 'Our smart matching algorithm connects investors with relevant opportunities based on sector preferences, investment range, location, and other criteria. You can also browse and send interest requests directly.',
    ],
    [
        'id' => 5,
        'question' => 'How do I verify my account?',
        'answer' => 'Upload your business registration certificate, citizenship/passport, or other relevant documents from your dashboard. Our team reviews submissions within 1-2 business days.',
    ],
    [
        'id' => 6,
        'question' => 'Can I list my business for sale?',
        'answer' => 'Yes. Business owners can create detailed listings including financials, media, and documents. Each listing is reviewed before going live to ensure quality and authenticity.',
    ],
    [
        'id' => 7,
        'question' => 'What types of investors use Asaan Capital?',
        'answer' => 'We host angel investors, venture capital firms, private equity investors, and high-net-worth individuals looking for opportunities in Nepal\'s growing economy across sectors like AgriTech, CleanTech, HealthTech, and more.',
    ],
    [
        'id' => 8,
        'question' => 'How do I contact the support team?',
        'answer' => 'You can reach us through the Contact page, email us at support@asaancapital.com, or use the inquiry form available on each listing. Premium members get priority support.',
    ],
];

json_success($faqs);
