<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Reply on Your Ticket</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" style="background:#f5f5f5;padding:32px 0;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        {{-- Header --}}
        <tr>
          <td style="background:#0f1117;padding:28px 32px;text-align:center;">
            <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;">Gundaling Farmstead</p>
            <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.55);">Support Center</p>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:32px;">
            <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#141414;">New Reply on Your Ticket</p>
            <p style="margin:0 0 24px;font-size:14px;color:#6b6b6b;">
              Hello {{ $ticket->submitter_name }}, a staff member has replied to your support ticket.
            </p>

            {{-- Ticket reference --}}
            <table role="presentation" width="100%" style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:24px;">
              <tr style="background:#f9fafb;">
                <td style="padding:10px 16px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;border-bottom:1px solid #e5e7eb;" colspan="2">Ticket Reference</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;width:140px;border-bottom:1px solid #f3f4f6;">Ticket Number</td>
                <td style="padding:10px 16px;font-size:13px;font-family:monospace;font-weight:700;color:#059669;border-bottom:1px solid #f3f4f6;">{{ $ticket->ticket_number }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;border-bottom:1px solid #f3f4f6;">Subject</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #f3f4f6;">{{ $ticket->subject }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;">Replied By</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;">{{ $reply->reply_by_name }}</td>
              </tr>
            </table>

            {{-- Reply message --}}
            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#374151;">Reply Message</p>
            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:14px 16px;font-size:13px;color:#166534;line-height:1.7;margin-bottom:24px;white-space:pre-wrap;">{{ $reply->message }}</div>

            {{-- CTA note --}}
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:14px 16px;">
              <p style="margin:0;font-size:13px;color:#374151;line-height:1.6;">
                To check your ticket status, visit
                <strong>{{ config('app.url') }}/tickets/status?number={{ $ticket->ticket_number }}</strong>
              </p>
            </div>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">
              &copy; {{ date('Y') }} Gundaling Farmstead &mdash; This is an automated message, please do not reply.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
