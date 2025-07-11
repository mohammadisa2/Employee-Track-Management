<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EmployeeLogController extends Controller
{
    /**
     * Store employee log data from Chrome extension
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:website_visit,keystroke,activity',
            'url' => 'nullable|string',
            'title' => 'nullable|string',
            'domain' => 'nullable|string',
            'content' => 'nullable|string',
            'activity' => 'nullable|array',
            'activities' => 'nullable|array',
            'timestamp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $user = $request->user(); // Get authenticated user from token
            
            // Parse timestamp
            $loggedAt = Carbon::parse($data['timestamp']);
            
            // Prepare log data
            $logData = [
                'employee_id' => $user->id, // Use authenticated user's ID
                'type' => $data['type'],
                'url' => $data['url'] ?? null,
                'title' => $data['title'] ?? null,
                'domain' => $data['domain'] ?? null,
                'logged_at' => $loggedAt,
            ];

            // Handle different types of logs
            switch ($data['type']) {
                case 'keystroke':
                    $logData['content'] = $data['content'] ?? null;
                    break;
                    
                case 'activity':
                    $logData['activity_data'] = $data['activity'] ?? $data['activities'] ?? null;
                    break;
                    
                case 'website_visit':
                    // Website visit data is already in the base logData
                    break;
            }

            // Create log entry
            $log = EmployeeLog::create($logData);

            return response()->json([
                'success' => true,
                'message' => 'Log stored successfully',
                'data' => $log
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee logs with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = EmployeeLog::query();

        // Filter by authenticated user's ID by default
        $query->byEmployee($user->id);

        // Allow admin to filter by specific employee ID
        if ($request->has('employee_id') && $user->hasRole('admin')) {
            $query->byEmployee($request->employee_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $logs = $query->orderBy('logged_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get employee statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->id; // Use authenticated user's ID
        
        // Allow admin to view other employee's stats
        if ($request->has('employee_id') && $user->hasRole('admin')) {
            $employeeId = $request->get('employee_id');
        }
        
        $startDate = $request->get('start_date', now()->startOfDay());
        $endDate = $request->get('end_date', now()->endOfDay());

        $query = EmployeeLog::query()
            ->byEmployee($employeeId)
            ->byDateRange($startDate, $endDate);

        $stats = [
            'total_logs' => $query->count(),
            'website_visits' => $query->clone()->websiteVisits()->count(),
            'keystroke_logs' => $query->clone()->keystrokes()->count(),
            'activity_logs' => $query->clone()->activities()->count(),
            'unique_domains' => $query->clone()->websiteVisits()
                ->distinct('domain')
                ->count('domain'),
            'top_domains' => $query->clone()->websiteVisits()
                ->selectRaw('domain, COUNT(*) as visits')
                ->groupBy('domain')
                ->orderBy('visits', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}