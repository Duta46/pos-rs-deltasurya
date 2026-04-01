<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LogActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $activityLogs = ActivityLog::with('user')->latest()->get();

            return DataTables::of($activityLogs)
                ->addIndexColumn()
                ->addColumn('user_name', function ($log) {
                    return $log->user ? $log->user->name : 'N/A';
                })
                ->addColumn('created_at_formatted', function ($log) {
                    return $log->created_at->format('d M Y H:i:s');
                })
                ->rawColumns(['user_name'])
                ->make(true);
        }

        return view('admin.activity.index');
    }
}
