@extends('layouts.app')
@section('title', 'Master Voucher Diskon')
@section('page-title')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center my-0">
            Master Voucher Diskon
        </h1>
    </div>
@endsection
@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                            transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                        <path
                            d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                            fill="currentColor" />
                    </svg>
                </span>
                <input type="search" name="search" class="form-control form-control-solid w-250px ps-15" id="search"
                    placeholder="Cari.." />
            </div>
            <div class="d-flex flex-stack">
                <a type="button" class="btn btn-primary ms-2" href="{{ route('voucher.create') }}">
                    Tambah Voucher
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="vouchers-table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="fw-semibold fs-6 text-muted">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-150px">Nama Voucher</th>
                        <th class="min-w-100px">Asuransi</th>
                        <th class="min-w-100px">Tipe</th>
                        <th class="min-w-100px">Nilai</th>
                        <th class="min-w-100px">Max Diskon</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-end min-w-100px">Actions</th>
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
        var datatable = $('#vouchers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! url()->current() !!}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'insurance_name', name: 'insurance_name' },
                { data: 'type', name: 'type' },
                { data: 'value', name: 'value' },
                { data: 'max_discount', name: 'max_discount' },
                { data: 'is_active', name: 'is_active' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
            ]
        });

        $('#search').on('keyup', function() {
            datatable.search(this.value).draw();
        });

        $(document).on("click", ".delete-confirm", function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('voucher.index') }}/" + id,
                        type: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            datatable.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@endpush
