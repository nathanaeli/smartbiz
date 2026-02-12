<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $query = \App\Models\AuditLog::forTenant($user->tenant_id)->with(['causer', 'subject', 'duka']);

        // Filtering options
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event', $request->event_type);
        }

        if ($request->filled('duka_id')) {
            $query->where('duka_id', $request->duka_id);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('causer', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                })
                    ->orWhere('event', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('subject', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Metrics
        $totalToday = (clone $query)->whereDate('created_at', now())->count();

        $mostActiveUser = (clone $query)
            ->select('causer_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id')
            ->orderByDesc('total')
            ->first();

        $activeUserName = $mostActiveUser ? \App\Models\User::find($mostActiveUser->causer_id)->name ?? 'Unknown' : 'N/A';

        $lastEvent = (clone $query)->latest()->first();
        $lastActiveTime = $lastEvent ? $lastEvent->created_at->diffForHumans() : 'N/A';

        $logs = $query->latest()->paginate(20);

        // Get filter options for dropdowns
        $users = \App\Models\User::where('tenant_id', $user->tenant_id)->orderBy('name')->get()->keyBy('id');
        $dukas = \App\Models\Duka::where('tenant_id', $user->tenant_id)->orderBy('name')->get()->keyBy('id');
        $eventTypes = $query->select('event')->distinct()->orderBy('event')->pluck('event');

        return view('tenant.audit.index', compact('logs', 'totalToday', 'activeUserName', 'lastActiveTime', 'users', 'dukas', 'eventTypes'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if (!$user->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $query = \App\Models\AuditLog::forTenant($user->tenant_id)->with(['causer', 'subject', 'duka']);

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event', $request->event_type);
        }

        if ($request->filled('duka_id')) {
            $query->where('duka_id', $request->duka_id);
        }

        $logs = $query->latest()->get();

        $format = $request->input('format', 'csv');

        if ($format === 'csv') {
            return $this->exportCsv($logs);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($logs);
        }

        return back()->with('error', 'Invalid export format');
    }

    protected function exportCsv($logs)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Date', 'User', 'Event', 'Subject', 'Duka', 'IP Address', 'Description']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->causer->name ?? 'System',
                    ucfirst($log->event),
                    class_basename($log->subject_type),
                    $log->duka->name ?? 'System',
                    $log->ip_address,
                    $log->description ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPdf($logs)
    {
        $data = [
            'logs' => $logs,
            'title' => 'Audit Logs Report',
            'date' => now()->format('Y-m-d H:i:s'),
        ];

        $pdf = \PDF::loadView('tenant.audit.pdf', $data);
        return $pdf->download('audit_logs_' . now()->format('Y-m-d') . '.pdf');
    }
}
