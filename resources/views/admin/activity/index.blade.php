@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('page-title')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center my-0">
            Log Aktivitas
        </h1>
    </div>
@endsection

@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Daftar Log Aktivitas</h3>
        </div>
        <div class="card-body pt-0">
            <table id="activity-logs-table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="fw-semibold fs-6 text-muted">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-100px">Pengguna</th>
                        <th class="min-w-150px">Aksi</th>
                        <th class="min-w-200px">Deskripsi</th>
                        <th class="min-w-100px">Waktu</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#activity-logs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('activity.index') !!}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'user_name', name: 'user.name' },
                    { data: 'action', name: 'action' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at_formatted', name: 'created_at' },
                ],
                order: [[4, 'desc']] // Order by created_at_formatted (Waktu) descending
            });
        });
    </script>
@endpush
