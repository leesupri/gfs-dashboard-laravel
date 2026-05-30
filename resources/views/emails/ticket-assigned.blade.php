<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body    { font-family: Arial, sans-serif; background:#f5f4f1; margin:0; padding:24px; color:#141414; }
    .card   { background:#fff; border-radius:12px; max-width:560px; margin:0 auto; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
    .header { background:#0f1117; padding:28px 32px; text-align:center; }
    .header img { height:40px; width:auto; }
    .header h2  { color:#fff; margin:12px 0 4px; font-size:18px; }
    .header p   { color:rgba(255,255,255,0.5); margin:0; font-size:13px; }
    .body   { padding:28px 32px; }
    .badge  { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:16px; }
    .open        { background:#dbeafe; color:#1d4ed8; }
    .in_progress { background:#fef3c7; color:#b45309; }
    .resolved    { background:#d1fae5; color:#065f46; }
    .closed      { background:#f3f4f6; color:#374151; }
    .info-table  { width:100%; border-collapse:collapse; margin:16px 0; font-size:14px; }
    .info-table td { padding:8px 0; border-bottom:1px solid #f3f4f6; vertical-align:top; }
    .info-table td:first-child { color:#6b6b6b; width:38%; }
    .info-table td:last-child   { font-weight:500; }
    .cta { display:inline-block; background:#22c55e; color:#fff !important; text-decoration:none; padding:12px 28px; border-radius:10px; font-weight:600; font-size:14px; margin:16px 0; }
    .footer { text-align:center; padding:16px 32px 20px; font-size:12px; color:#a3a3a3; }
    .divider { border:none; border-top:1px solid #f3f4f6; margin:20px 0; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h2>🎫 Ticket Assigned to You</h2>
      <p>PIMS Gundaling — Support System</p>
    </div>

    <div class="body">
      <p>Hi <strong>{{ $assignee->name }}</strong>,</p>
      <p>A support ticket has been assigned to you. Please review and respond as soon as possible.</p>

      <span class="badge {{ $ticket->status }}">
        {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
      </span>

      <table class="info-table">
        <tr>
          <td>Ticket Number</td>
          <td><strong>{{ $ticket->ticket_number }}</strong></td>
        </tr>
        <tr>
          <td>Subject</td>
          <td>{{ $ticket->subject }}</td>
        </tr>
        <tr>
          <td>From</td>
          <td>{{ $ticket->submitter_name }} &lt;{{ $ticket->submitter_email }}&gt;</td>
        </tr>
        <tr>
          <td>Category</td>
          <td>{{ ucfirst($ticket->category) }}</td>
        </tr>
        <tr>
          <td>Priority</td>
          <td>{{ ucfirst($ticket->priority) }}</td>
        </tr>
        <tr>
          <td>Submitted</td>
          <td>{{ $ticket->created_at->format('d M Y H:i') }} WIB</td>
        </tr>
      </table>

      <hr class="divider">

      <p style="font-size:14px; color:#6b6b6b;"><strong>Message from submitter:</strong></p>
      <div style="background:#f5f4f1; border-radius:8px; padding:14px 16px; font-size:14px; line-height:1.6;">
        {{ $ticket->description }}
      </div>

      <p style="margin-top:20px;">
        <a href="{{ url('/support/tickets/' . $ticket->id) }}" class="cta">Open Ticket in Dashboard →</a>
      </p>
    </div>

    <div class="footer">
      PIMS Gundaling Farmstead &mdash; This is an automated notification, please do not reply to this email.
    </div>
  </div>
</body>
</html>
