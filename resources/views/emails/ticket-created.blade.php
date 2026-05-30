<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Support Ticket Received</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:32px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

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
            <p style="margin:0 0 8px;font-size:18px;font-weight:700;color:#141414;">Ticket Received</p>
            <p style="margin:0 0 24px;font-size:14px;color:#6b6b6b;">
              Hello {{ $ticket->submitter_name }}, we have received your support request and will respond as soon as possible.
            </p>

            {{-- Ticket details table --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:24px;">
              <tr style="background:#f9fafb;">
                <td style="padding:10px 16px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;border-bottom:1px solid #e5e7eb;" colspan="2">Ticket Details</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;width:140px;border-bottom:1px solid #f3f4f6;">Ticket Number</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;font-family:monospace;font-weight:700;color:#059669;border-bottom:1px solid #f3f4f6;">{{ $ticket->ticket_number }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;border-bottom:1px solid #f3f4f6;">Subject</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #f3f4f6;">{{ $ticket->subject }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;border-bottom:1px solid #f3f4f6;">Category</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #f3f4f6;">{{ \App\Models\Ticket::CATEGORY_LABELS[$ticket->category] ?? $ticket->category }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;border-bottom:1px solid #f3f4f6;">Priority</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;border-bottom:1px solid #f3f4f6;">{{ \App\Models\Ticket::PRIORITY_LABELS[$ticket->priority] ?? $ticket->priority }}</td>
              </tr>
              <tr>
                <td style="padding:10px 16px;font-size:13px;font-weight:600;color:#374151;">Status</td>
                <td style="padding:10px 16px;font-size:13px;color:#111827;">{{ \App\Models\Ticket::STATUS_LABELS[$ticket->status] ?? $ticket->status }}</td>
              </tr>
            </table>

            {{-- Description --}}
            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#374151;">Your Message</p>
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:14px 16px;font-size:13px;color:#374151;line-height:1.7;margin-bottom:24px;white-space:pre-wrap;">{{ $ticket->description }}</div>

            {{-- Note --}}
            <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:6px;padding:14px 16px;">
              <p style="margin:0;font-size:13px;color:#065f46;line-height:1.6;">
                <strong>Keep this email for your records.</strong> Your reference number is
                <strong style="font-family:monospace;">{{ $ticket->ticket_number }}</strong>.
                You can check your ticket status anytime at <strong>{{ config('app.url') }}/tickets/status</strong>.
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
