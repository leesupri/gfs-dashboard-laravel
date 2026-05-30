<?php

namespace App\Http\Controllers;

use App\Mail\TicketAssignedMail;
use App\Mail\TicketRepliedMail;
use App\Mail\TicketStatusChangedMail;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketManagementController extends Controller
{
    // ── List tickets ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {

        $query = Ticket::with(['assignedTo', 'staffUser'])
            ->orderByDesc('created_at');

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Priority filter
        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        // Category filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // Assigned-to filter
        if ($request->filled('assigned_to')) {
            if ($request->input('assigned_to') === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->input('assigned_to'));
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', '%' . $search . '%')
                  ->orWhere('subject', 'like', '%' . $search . '%')
                  ->orWhere('submitter_name', 'like', '%' . $search . '%')
                  ->orWhere('submitter_email', 'like', '%' . $search . '%');
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'open'          => Ticket::where('status', 'open')->count(),
            'in_progress'   => Ticket::where('status', 'in_progress')->count(),
            'resolved_today' => Ticket::where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count(),
        ];

        $staffList = StaffUser::where('is_active', true)->orderBy('name')->get();

        return view('tickets.index', [
            'title'           => 'Support Tickets',
            'tickets'         => $tickets,
            'stats'           => $stats,
            'staffList'       => $staffList,
            'statusLabels'    => Ticket::STATUS_LABELS,
            'priorityLabels'  => Ticket::PRIORITY_LABELS,
            'categoryLabels'  => Ticket::CATEGORY_LABELS,
            'filters'         => $request->only(['status', 'priority', 'category', 'assigned_to', 'search']),
        ]);
    }

    // ── Show a single ticket ──────────────────────────────────────────────────

    public function show($id)
    {
        $ticket = Ticket::with(['staffUser', 'assignedTo', 'replies.staffUser'])
            ->findOrFail($id);

        $staffList = StaffUser::where('is_active', true)->orderBy('name')->get();

        return view('tickets.show', [
            'title'          => 'Ticket — ' . $ticket->ticket_number,
            'ticket'         => $ticket,
            'staffList'      => $staffList,
            'statusLabels'   => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
        ]);
    }

    // ── Add a staff reply ─────────────────────────────────────────────────────

    public function reply(Request $request, $id)
    {
        $staffUser = app('currentStaffUser');

        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $reply = TicketReply::create([
            'ticket_id'      => $ticket->id,
            'staff_user_id'  => $staffUser->id,
            'reply_by_name'  => $staffUser->name,
            'message'        => $request->input('message'),
            'is_staff_reply' => true,
        ]);

        // If ticket is still open, bump it to in_progress
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Notify submitter
        try {
            Mail::to($ticket->submitter_email)->send(new TicketRepliedMail($ticket, $reply));
        } catch (\Throwable) {
            // Non-fatal
        }

        return redirect()
            ->route('support.tickets.show', $ticket->id)
            ->with('success', 'Reply sent successfully.');
    }

    // ── Update ticket status ──────────────────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Ticket::STATUS_LABELS)),
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $request->input('status');

        $data = ['status' => $newStatus];

        if ($newStatus === 'resolved' && $ticket->resolved_at === null) {
            $data['resolved_at'] = now();
        } elseif ($newStatus !== 'resolved') {
            $data['resolved_at'] = null;
        }

        $ticket->update($data);

        // Notify submitter of status change
        try {
            Mail::to($ticket->submitter_email)->send(
                new TicketStatusChangedMail($ticket, $oldStatus, $newStatus)
            );
        } catch (\Throwable) {
            // Non-fatal
        }

        return redirect()
            ->route('support.tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated to "' . Ticket::STATUS_LABELS[$newStatus] . '".');
    }

    // ── Assign ticket to staff ────────────────────────────────────────────────

    public function assign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'assigned_to' => 'nullable|exists:staff_users,id',
        ]);

        $newAssigneeId = $request->input('assigned_to') ?: null;
        $ticket->update(['assigned_to' => $newAssigneeId]);

        // Notify the newly assigned staff member if they have an email
        if ($newAssigneeId) {
            $assignee = StaffUser::find($newAssigneeId);
            if ($assignee && $assignee->email) {
                try {
                    Mail::to($assignee->email)->send(new TicketAssignedMail($ticket, $assignee));
                } catch (\Throwable) {
                    // Non-fatal
                }
            }
        }

        return redirect()
            ->route('support.tickets.show', $ticket->id)
            ->with('success', 'Ticket assignment updated.');
    }
}
