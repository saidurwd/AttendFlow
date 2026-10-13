<?php

namespace App\Http\Controllers\SqlServer;

use App\Http\Controllers\Controller;
use App\Models\SqlServerAttendance;
use App\Models\SqlServerDepartment;
use App\Models\SqlServerDevice;
use App\Models\SqlServerUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
        $tz = config('app.timezone', 'UTC');

        $query = SqlServerAttendance::query()
            ->selectRaw("
                c.USERID,
                u.BADGENUMBER as employee_code,
                u.NAME as name,
                d.DEPTNAME as department,
                m.MachineAlias as device_name,
                m.sn as device_serial,
                CONVERT(date, c.CHECKTIME) as attendance_date,
                MIN(c.CHECKTIME) as clock_in,
                MAX(c.CHECKTIME) as clock_out,
                COUNT(*) as total_entries,
                DATEDIFF(MINUTE, MIN(c.CHECKTIME), MAX(c.CHECKTIME)) as total_minutes
            ")
            ->from('CHECKINOUT as c')
            ->join('USERINFO as u', 'c.USERID', '=', 'u.USERID')
            ->leftJoin('DEPARTMENTS as d', 'u.DEFAULTDEPTID', '=', 'd.DEPTID')
            ->leftJoin('Machines as m', 'c.sn', '=', 'm.sn')
            ->groupBy('c.USERID', 'u.BADGENUMBER', 'u.NAME', 'd.DEPTNAME', 'm.MachineAlias', 'm.sn', DB::raw('CONVERT(date, c.CHECKTIME)'))
            ->orderByDesc('attendance_date')
            ->orderBy('u.NAME');

        if ($request->filled('start_date')) {
            $query->whereDate('c.CHECKTIME', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('c.CHECKTIME', '<=', $request->end_date);
        }
        if ($request->filled('device_serial')) {
            $query->where('c.sn', $request->device_serial);
        }
        if ($request->filled('employee_id')) {
            $query->where('u.BADGENUMBER', $request->employee_id);
        }
        if ($request->filled('department')) {
            $query->where('d.DEPTNAME', $request->department);
        }

        // Handle CSV export
        if ($request->has('export')) {
            $filename = 'attendance_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($query, $tz) {
                $handle = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($handle, [
                    'Date',
                    'Employee ID',
                    'Name',
                    'Department',
                    'Device',
                    'Device Serial',
                    'Clock In',
                    'Clock Out',
                    'Total Entries',
                    'Total Hours'
                ]);

                // Stream data in chunks to avoid memory issues
                $query->chunk(500, function ($rows) use ($handle, $tz) {
                    foreach ($rows as $row) {
                        $hours = floor($row->total_minutes / 60);
                        $mins = $row->total_minutes % 60;
                        $totalHours = sprintf('%02d:%02d', $hours, $mins);
                        
                        fputcsv($handle, [
                            Carbon::parse($row->attendance_date)->timezone($tz)->format('Y-m-d'),
                            $row->employee_code,
                            $row->name,
                            $row->department ?? 'N/A',
                            $row->device_name ?? 'N/A',
                            $row->device_serial,
                            Carbon::parse($row->clock_in)->timezone($tz)->format('H:i:s'),
                            Carbon::parse($row->clock_out)->timezone($tz)->format('H:i:s'),
                            $row->total_entries,
                            $totalHours
                        ]);
                    }
                });
                
                fclose($handle);
            };

            return new StreamedResponse($callback, 200, $headers);
        }

        $reports = $query->paginate(50)->withQueryString();

        $employees = SqlServerUser::select('USERID', 'BADGENUMBER', 'NAME', 'DEFAULTDEPTID')
            ->orderBy('NAME')
            ->get();

        $departments = SqlServerDepartment::select('DEPTNAME')
            ->distinct()
            ->orderBy('DEPTNAME')
            ->pluck('DEPTNAME')
            ->filter()
            ->values();

        $devices = SqlServerDevice::select('sn', 'MachineAlias')
            ->where('Enabled', 1)
            ->orderBy('MachineAlias')
            ->get();

        return view('sqlserver.reports.attendance', compact(
            'reports',
            'employees',
            'departments',
            'devices',
            'tz'
        ));
    }
}