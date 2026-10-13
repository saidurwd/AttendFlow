@extends('layouts.admin')

@section('title', 'DPL & ORCHID Attendance Report')

@section('content_header')
    <x-adminlte-content-header 
        title="DPL & ORCHID Attendance Report"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'DPL & ORCHID Attendance Report']]"
    />
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">DPL & ORCHID Attendance Report</h3>
                    <div class="card-tools">
                        <span class="badge bg-info">Live Data from SQL Server</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('sqlserver.reports.attendance') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="device_serial" class="form-label">Device</label>
                                    <select name="device_serial" id="device_serial" class="form-control">
                                        <option value="">All Devices</option>
                                        @foreach($devices as $device)
                                            <option value="{{ $device->sn }}" {{ request('device_serial') == $device->sn ? 'selected' : '' }}>
                                                {{ $device->MachineAlias }} ({{ $device->sn }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-control">
                                        <option value="">All Employees</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->BADGENUMBER }}" {{ request('employee_id') == $emp->BADGENUMBER ? 'selected' : '' }}>
                                                {{ $emp->BADGENUMBER }} - {{ $emp->NAME }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <select name="department" id="department" class="form-control">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                                {{ $dept }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <a href="{{ route('sqlserver.reports.attendance') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                                <a href="{{ route('sqlserver.reports.attendance') }}?export=1" class="btn btn-success ms-2">
                                    <i class="bi bi-download"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Device</th>
                                    <th>Device Serial</th>
                                    <th>First Punch</th>
                                    <th>Last Punch</th>
                                    <th>Total Entries</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reports as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->attendance_date)->timezone($tz)->format('Y-m-d') }}</td>
                                        <td>{{ $row->employee_code }}</td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ $row->department ?? 'N/A' }}</td>
                                        <td>{{ $row->device_name ?? 'N/A' }}</td>
                                        <td>{{ $row->device_serial }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->clock_in)->timezone($tz)->format('H:i:s') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->clock_out)->timezone($tz)->format('H:i:s') }}</td>
                                        <td>{{ $row->total_entries }}</td>
                                        <td>
                                            @php
                                                $hours = floor($row->total_minutes / 60);
                                                $mins = $row->total_minutes % 60;
                                                echo sprintf('%02d:%02d', $hours, $mins);
                                            @endphp
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($reports->hasPages())
                        <div class="mt-3">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop