<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $companyName }} — Taarifa</title>
    <style>
        /* Base reset */
        html, body { margin: 0; padding: 0; height: 100%; }
        body { background: #f4f6f8; color: #2b2f38; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif; line-height: 1.6; }

        /* Container */
        .wrapper { width: 100%; background: #f4f6f8; padding: 24px 12px; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(27, 31, 35, 0.08); }

        /* Header */
        .header { background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); color: #ffffff; text-align: center; padding: 32px 24px; }
        .brand { display: inline-flex; align-items: center; gap: 12px; }
        .brand-badge { width: 56px; height: 56px; border-radius: 12px; background: rgba(255,255,255,0.15); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 20px; backdrop-filter: blur(2px); }
        .brand-name { font-size: 22px; font-weight: 700; letter-spacing: 0.2px; }
        .subtitle { margin-top: 6px; opacity: 0.9; font-size: 13px; }

        /* Body */
        .body { padding: 28px 24px; }
        .greeting { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 16px; }
        .content { font-size: 15px; color: #334155; line-height: 1.75; }
        .divider { height: 1px; background: #eef2f7; margin: 20px 0; }

        /* Footer */
        .footer { background: #0f172a; color: #cbd5e1; text-align: center; padding: 20px; }
        .footer .company { font-weight: 700; color: #ffffff; margin-bottom: 6px; }
        .footer p { margin: 4px 0; font-size: 12px; opacity: 0.9; }

        /* Utilities */
        .text-muted { color: #64748b; }
        .note { background: #f8fafc; border: 1px solid #eef2f7; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #475569; }

        /* Responsive */
        @media (max-width: 640px) {
            .header { padding: 24px 16px; }
            .brand-badge { width: 48px; height: 48px; font-size: 18px; }
            .brand-name { font-size: 20px; }
            .body { padding: 20px 16px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="brand">
                    <div class="brand-badge">{{ strtoupper(substr($companyName, 0, 2)) }}</div>
                    <div>
                        <div class="brand-name">{{ $companyName }}</div>
                        <div class="subtitle">INVITATION FOR 5-DAYS TRAINING ON FINANCIAL STATEMENTS</div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="body">
                <div class="greeting">Dear {{ $recipientName }},</div>
                <div class="content">
                    {!! nl2br(strip_tags($content, '<b><strong><i><em><u><br><ul><ol><li><p><a>')) !!}
                </div>
                <div class="divider"></div>
                <div class="note">
                    This email was sent from the <strong>{{ $companyName }}</strong> system.
                    We value your trust and strive to provide you with the best service every day.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="company">{{ $companyName }}</div>
                <p>&copy; {{ date('Y') }}. All rights reserved.</p>
                <p class="text-muted">SAFCO FINTECH Team</p>
            </div>
        </div>
    </div>
</body>
</html>