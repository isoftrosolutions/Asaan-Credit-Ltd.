<?php
/**
 * Email template definitions for Asaan Capital.
 * Each template has: name, subject, body (HTML with {{placeholders}}), variables.
 * Design tokens match the app's design system:
 *   primary:       #6B1D22
 *   primary-vivid: #98202A
 *   secondary:     #1E4866
 *   success:       #1E7A4D
 *   warning:       #C77A12
 *   error:         #98202A
 */

return [
    'email_verification' => [
        'name'      => 'Email Verification',
        'subject'   => 'Verify your email — Asaan Capital',
        'variables' => ['user_name', 'verification_link'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Verify Your Email</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">One last step to activate your account.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Welcome to Asaan Capital — Nepal\'s premier marketplace for business investment and matchmaking. To start exploring opportunities and connecting with verified investors, please confirm your email address.</p>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{verification_link}}" style="display:inline-block;padding:16px 36px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,72,102,0.2);">Verify My Email</a>
            </div>
            <p style="font-size:14px;color:#C3C6C5;margin-bottom:32px;text-align:center;">This link expires in 24 hours. If you did not create this account, please ignore this email.</p>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'password_reset' => [
        'name'      => 'Password Reset',
        'subject'   => 'Reset your password — Asaan Capital',
        'variables' => ['user_name', 'reset_link'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Password Reset</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">We received a request to reset your password.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Click the button below to choose a new password. This link is valid for <strong>24 hours</strong>.</p>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{reset_link}}" style="display:inline-block;padding:16px 36px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,72,102,0.2);">Reset Password</a>
            </div>
            <div style="background:#F8F8F8;padding:20px;border-radius:12px;margin:24px 0;border:1px solid #ECECEC;">
                <p style="margin:0;font-size:14px;color:#5A5A5A;line-height:1.5;">If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
            </div>
            <p style="font-size:14px;color:#C3C6C5;margin-bottom:0;text-align:center;">Security Team — Asaan Capital</p>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;margin-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'password_changed' => [
        'name'      => 'Password Changed Confirmation',
        'subject'   => 'Password changed successfully — Asaan Capital',
        'variables' => ['user_name'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Password Changed</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Your account security has been updated.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Your password was changed successfully. If you made this change, no further action is required.</p>
            <div style="background:#fff9f0;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid #fde68a;">
                <p style="margin:0;font-size:14px;color:#92400e;line-height:1.5;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <strong>Did not change your password?</strong> Please contact our support team immediately at <a href="mailto:support@asaancapital.com" style="color:#98202A;font-weight:600;">support@asaancapital.com</a>.</p>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'welcome' => [
        'name'      => 'Welcome New User',
        'subject'   => 'Welcome to Asaan Capital — {{user_name}}',
        'variables' => ['user_name', 'login_url', 'role', 'user_email'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Welcome Aboard!</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Your account is ready to explore.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Thank you for joining Asaan Capital. You are now part of Nepal\'s most trusted marketplace where investors, entrepreneurs, and business owners connect to grow together.</p>
            <div style="background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;">
                <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#C3C6C5;margin-top:0;margin-bottom:16px;">Your account details</h3>
                <p style="margin:8px 0;font-size:14px;"><strong>Role :</strong> {{role}}</p>
                <p style="margin:8px 0;font-size:14px;"><strong>Email :</strong> {{user_email}}</p>
            </div>
            <div style="background:linear-gradient(135deg, #1E4866 0%, #205880 100%);padding:28px;border-radius:16px;margin-bottom:32px;color:#ffffff;box-shadow:0 8px 20px rgba(30,72,102,0.2);">
                <h3 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-top:0;margin-bottom:16px;opacity:0.9;">What you can do next</h3>
                <div style="font-size:14px;line-height:1.8;">
                    <div><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> <strong>Discover</strong> — Browse vetted businesses and pitches</div>
                    <div><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> <strong>Connect</strong> — Express interest and match with partners</div>
                    <div><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg> <strong>Grow</strong> — Find the right capital or acquisition opportunity</div>
                </div>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#98202A;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:800;font-size:15px;box-shadow:0 4px 15px rgba(152,32,42,0.3);">Explore the Marketplace</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'interest_received' => [
        'name'      => 'Interest Request Received',
        'subject'   => 'New interest received for your {{listing_type}} — Asaan Capital',
        'variables' => ['user_name', 'sender_name', 'sender_role', 'listing_type', 'listing_name', 'message', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">New Interest Received</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Someone is interested in your {{listing_type}}.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;"><strong>{{sender_name}}</strong> ({{sender_role}}) has expressed interest in your {{listing_type}}: <strong>{{listing_name}}</strong>.</p>
            <div style="background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;">
                <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#C3C6C5;margin-top:0;margin-bottom:12px;">Message</h3>
                <p style="margin:0;font-size:15px;color:#2a2a2a;line-height:1.6;font-style:italic;">{{message}}</p>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);">View & Respond</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'match_confirmed' => [
        'name'      => 'Match Confirmed',
        'subject'   => 'You have a new match! — Asaan Capital',
        'variables' => ['user_name', 'matched_user_name', 'matched_user_role', 'context_type', 'context_name', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <div style="text-align:center;margin-bottom:16px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1E7A4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 style="color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">It\'s a Match!</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Both sides are interested — time to connect.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Great news! <strong>{{matched_user_name}}</strong> ({{matched_user_role}}) has also expressed interest in your {{context_type}}. You are now connected and can view each other\'s contact details.</p>
            <div style="background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid #bbf7d0;">
                <p style="margin:0;font-size:15px;color:#166534;line-height:1.5;"><strong>Context :</strong> {{context_name}}</p>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);">Start Conversation</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'interest_accepted' => [
        'name'      => 'Interest Request Accepted',
        'subject'   => 'Your interest was accepted! — Asaan Capital',
        'variables' => ['user_name', 'responder_name', 'listing_type', 'listing_name', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Interest Accepted!</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">{{responder_name}} wants to connect with you.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;"><strong>{{responder_name}}</strong> has accepted your interest request for <strong>{{listing_name}}</strong>. You can now exchange contact details and move forward.</p>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);">View Connection</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'broadcast' => [
        'name'      => 'Admin Broadcast',
        'subject'   => '{{subject}} — Asaan Capital',
        'variables' => ['user_name', 'subject', 'message', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:24px;">
                <h2 style="color:#1E4866;font-size:22px;font-weight:800;margin:0;letter-spacing:-0.5px;">{{subject}}</h2>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <div style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">
                {{message}}
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:14px 32px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:14px;box-shadow:0 4px 15px rgba(30,72,102,0.2);">Go to Dashboard</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'verification_approved' => [
        'name'      => 'Verification Approved',
        'subject'   => 'Your account has been verified — Asaan Capital',
        'variables' => ['user_name', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Verification Approved &#10003;</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Your identity has been verified.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">Good news! Your account has been verified. You now have full access to all features including posting listings, sending interest requests, and connecting with potential partners.</p>
            <div style="background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:32px;border:1px solid #bbf7d0;">
                <p style="margin:0;font-size:14px;color:#166534;line-height:1.5;">A verified badge will appear on your profile, increasing trust and credibility with other members.</p>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);">Go to Dashboard</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'verification_rejected' => [
        'name'      => 'Verification Rejected',
        'subject'   => 'Verification document needs attention — Asaan Capital',
        'variables' => ['user_name', 'rejection_reason', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#98202A;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">Verification Update</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">Your document requires attention.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <p style="font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;">We reviewed your verification document and found an issue. Please see the feedback below and resubmit.</p>
            <div style="background:#fef2f2;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #fecaca;">
                <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#98202A;margin-top:0;margin-bottom:12px;">Reason</h3>
                <p style="margin:0;font-size:15px;color:#991b1b;line-height:1.6;">{{rejection_reason}}</p>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#98202A;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(152,32,42,0.2);">Re-upload Document</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],

    'new_message' => [
        'name'      => 'New Message Notification',
        'subject'   => 'New message from {{sender_name}} — Asaan Capital',
        'variables' => ['user_name', 'sender_name', 'message_preview', 'login_url'],
        'body'      => '<div style="font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <div style="text-align:center;margin-bottom:32px;">
                <span style="font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;">Asaan<span style="color:#98202A;">Capital</span></span>
            </div>
            <div style="text-align:center;margin-bottom:32px;">
                <h2 style="color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;">New Message</h2>
                <p style="color:#5A5A5A;margin-top:8px;font-size:15px;">You have a new message from {{sender_name}}.</p>
            </div>
            <p style="font-size:16px;margin-bottom:24px;line-height:1.6;">Hello <strong style="color:#1E4866;">{{user_name}}</strong>,</p>
            <div style="background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;">
                <p style="margin:0;font-size:15px;color:#2a2a2a;line-height:1.6;font-style:italic;">"{{message_preview}}"</p>
            </div>
            <div style="text-align:center;margin:32px 0;">
                <a href="{{login_url}}" style="display:inline-block;padding:16px 36px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,72,102,0.2);">View Message</a>
            </div>
            <div style="border-top:1px solid #ECECEC;padding-top:24px;text-align:center;">
                <p style="margin:0;font-size:13px;color:#5A5A5A;">Asaan Capital Ltd — Kathmandu, Nepal</p>
            </div>
        </div>',
    ],
];
