<?php

namespace App\Http\Controllers;

use App\Mail\TicketCreatedMail;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    // ── Public: show the ticket submission form ────────────────────────────────

    public function create()
    {
        return view('tickets.create', [
            'title'      => 'Submit a Support Ticket',
            'categories' => Ticket::CATEGORY_LABELS,
            'priorities' => Ticket::PRIORITY_LABELS,
        ]);
    }

    // ── Public: handle ticket submission ──────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'submitter_name'  => 'required|string|max:100',
            'submitter_email' => 'required|email|max:150',
            'subject'         => 'required|string|max:255',
            'category'        => 'required|in:' . implode(',', array_keys(Ticket::CATEGORY_LABELS)),
            'priority'        => 'required|in:' . implode(',', array_keys(Ticket::PRIORITY_LABELS)),
            'description'     => 'required|string|max:5000',
        ]);

        $validated['ticket_number'] = Ticket::generateNumber();
        $validated['status']        = 'open';

        $ticket = Ticket::create($validated);

        // 1. Confirmation to the person who submitted the ticket
        // 2. Alert copy to the support inbox so staff know a new ticket arrived
        try {
            Mail::to($ticket->submitter_email)->send(new TicketCreatedMail($ticket));

            $supportInbox = config('mail.support_notify_email');
            if ($supportInbox && $supportInbox !== $ticket->submitter_email) {
                Mail::to($supportInbox)->send(new TicketCreatedMail($ticket));
            }
        } catch (\Throwable) {
            // Mail failure is non-fatal
        }

        return redirect()
            ->route('tickets.status', ['number' => $ticket->ticket_number])
            ->with('success', 'Your ticket has been submitted! Reference number: ' . $ticket->ticket_number);
    }

    // ── Public: ticket status lookup form / display ────────────────────────────

    public function status(Request $request)
    {
        $ticket = null;
        $number = trim((string) $request->get('number', ''));

        if ($number !== '') {
            $ticket = Ticket::where('ticket_number', $number)->first();
        }

        return view('tickets.status', [
            'title'        => 'Check Ticket Status',
            'ticket'       => $ticket,
            'number'       => $number,
            'statusLabels' => Ticket::STATUS_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
        ]);
    }

    // ── Public: POST handler for the status lookup form ───────────────────────

    public function statusCheck(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string|max:30',
        ]);

        $number = strtoupper(trim($request->input('ticket_number')));

        return redirect()->route('tickets.status', ['number' => $number]);
    }
}
