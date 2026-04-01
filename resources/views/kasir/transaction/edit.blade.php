@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('page-title')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center my-0">
            Edit Transaksi #{{ $transaction->invoice_number }}
        </h1>
    </div>
@endsection

@push('styles')
<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

             <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 mt-2">
                <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center text-center my-0">
                    Transaksi Detail
                </h1>
            </div>

                <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" id="transactionForm">
                    @csrf
                    @method('PUT')
                    <!-- Header Info -->
                    <div class="card-body p-9">
                        <div class="row mb-5">
                            <div class="col-xl-3">
                                <label for="patient_name" class="fs-6 fw-bold mt-2 mb-3">Nama Pasien</label>
                            </div>
                            <div class="col-lg">
                                <input type="text" name="patient_name" value="{{ old('patient_name', $transaction->patient_name) }}"
                                    class="form-control @error('patient_name') is-invalid @enderror "
                                    placeholder="Nama Pasien" />
                                @error('patient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-xl-3">
                                <label class="fs-6 fw-bold mt-2 mb-3">Asuransi</label>
                            </div>
                            <div class="col-lg">
                                <select name="insurance_name" required
                                    class="form-select custom-placeholder @error('insurance_name') is-invalid @enderror "
                                    data-control="select2" data-placeholder="Pilih Asuransi">
                                    <option value="" disabled>-- Pilih Asuransi --</option>
                                    @foreach ($insurances as $ins)
                                        <option value="{{ $ins['name'] }}"
                                            {{ old('insurance_name', $transaction->insurance_name) == $ins['name'] ? 'selected' : '' }}>
                                            {{ $ins['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-6 overflow-x-auto">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">Daftar Tindakan Medis</h3>
                        <table class="min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden" id="itemsTable">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Prosedur</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase w-40">Harga</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase w-24">Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase w-40">Subtotal</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="itemsBody">
                                @foreach($transaction->details as $index => $detail)
                                <tr id="row-{{ $index }}">
                                    <td class="px-4 py-3">
                                        <select name="items[{{ $index }}][procedure_id]" required class="procedure-select w-full border-gray-300 rounded-md" onchange="updatePrice({{ $index }})">
                                            <option value="">-- Pilih Prosedur --</option>
                                            @foreach($procedures as $p)
                                                <option value="{{ $p['id'] }}" data-name="{{ $p['name'] }}" {{ $detail->procedure_id == $p['id'] ? 'selected' : '' }}>{{ $p['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="items[{{ $index }}][procedure_name]" value="{{ $detail->procedure_name }}" class="procedure-name">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $index }}][price]" value="{{ $detail->price }}" readonly class="price-input w-full bg-gray-100 border-gray-300 rounded-md text-right">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $index }}][qty]" value="{{ $detail->qty }}" min="1" required class="qty-input w-full border-gray-300 rounded-md text-center" oninput="calculateRow({{ $index }})">
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <input type="number" readonly class="subtotal-input w-full bg-gray-100 border-gray-300 rounded-md text-right font-bold" value="{{ $detail->subtotal }}">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" onclick="removeRow({{ $index }})" class="text-red-500 hover:text-red-700">Hapus</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            <button type="button" id="addItem" class="btn btn-primary">Tambah Tindakan</button>
                        </div>
                    </div>

                    <!-- Summary & Voucher -->
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="block text-sm font-semibold text-gray-700 uppercase mb-2">Voucher Diskon</label>
                                <select name="voucher_id" id="voucherSelect" class="block w-full border-gray-300 rounded-md">
                                    <option value="" data-type="nominal" data-value="0">-- Tanpa Voucher --</option>
                                    @foreach ($vouchers as $v)
                                        <option value="{{ $v->id }}" data-type="{{ $v->type }}" data-value="{{ $v->value }}" data-max="{{ $v->max_discount }}"
                                            {{ old('voucher_id', $transaction->voucher_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->insurance_name }} - {{ $v->type == 'percent' ? $v->value . '%' : 'Rp ' . number_format($v->value, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/3 flex flex-col space-y-3">
                                <div class="flex justify-between text-gray-600">
                                    <span class="font-medium">Total Harga:</span>
                                    <span id="displayTotalItem" class="font-bold">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-red-600 border-b pb-2">
                                    <span class="font-medium">Diskon:</span>
                                    <span id="displayDiscount" class="font-bold">- Rp {{ number_format($transaction->total_discount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-xl font-extrabold text-blue-800 pt-1">
                                    <span>GRAND TOTAL:</span>
                                    <span id="displayGrandTotal">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('transactions.index') }}" class="btn btn-light me-3">Batal</a>
                        <button type="submit" class="btn btn-primary">Update Transaksi (Draft)</button>
                    </div>
                </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowCount = {{ count($transaction->details) }};
        const procedures = @json($procedures);

        function addNewRow() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = `row-${rowCount}`;
            row.innerHTML = `
                <td class="px-4 py-3">
                    <select name="items[${rowCount}][procedure_id]" required class="procedure-select w-full border-gray-300 rounded-md" onchange="updatePrice(${rowCount})">
                        <option value="">-- Pilih Prosedur --</option>
                        ${procedures.map(p => `<option value="${p.id}" data-name="${p.name}">${p.name}</option>`).join('')}
                    </select>
                    <input type="hidden" name="items[${rowCount}][procedure_name]" class="procedure-name">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${rowCount}][price]" readonly class="price-input w-full bg-gray-100 border-gray-300 rounded-md text-right" placeholder="0">
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${rowCount}][qty]" value="1" min="1" required class="qty-input w-full border-gray-300 rounded-md text-center" oninput="calculateRow(${rowCount})">
                </td>
                <td class="px-4 py-3 text-right">
                    <input type="number" readonly class="subtotal-input w-full bg-gray-100 border-gray-300 rounded-md text-right font-bold" value="0">
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeRow(${rowCount})" class="text-red-500">Hapus</button>
                </td>
            `;
            tbody.appendChild(row);
            rowCount++;
        }

        function removeRow(id) {
            document.getElementById(`row-${id}`).remove();
            calculateGrandTotal();
        }

        async function updatePrice(id) {
            const row = document.getElementById(`row-${id}`);
            const select = row.querySelector('.procedure-select');
            const procId = select.value;
            if (!procId) return;

            row.querySelector('.procedure-name').value = select.options[select.selectedIndex].dataset.name;
            try {
                const response = await fetch(`/api/procedures/${procId}/price`);
                const data = await response.json();
                row.querySelector('.price-input').value = data.price;
                calculateRow(id);
            } catch (error) {
                alert('Gagal mengambil harga');
            }
        }

        function calculateRow(id) {
            const row = document.getElementById(`row-${id}`);
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            row.querySelector('.subtotal-input').value = price * qty;
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let totalItem = 0;
            document.querySelectorAll('.subtotal-input').forEach(input => totalItem += parseFloat(input.value) || 0);

            const voucher = document.getElementById('voucherSelect');
            const selected = voucher.options[voucher.selectedIndex];
            const type = selected.dataset.type;
            const val = parseFloat(selected.dataset.value) || 0;
            const maxDisc = parseFloat(selected.dataset.max) || 0;

            let discount = 0;
            if (type === 'percent') {
                discount = (val / 100) * totalItem;
                if (maxDisc > 0) discount = Math.min(discount, maxDisc);
            } else { discount = val; }

            const grandTotal = Math.max(0, totalItem - discount);
            document.getElementById('displayTotalItem').innerText = 'Rp ' + totalItem.toLocaleString('id-ID');
            document.getElementById('displayDiscount').innerText = '- Rp ' + discount.toLocaleString('id-ID');
            document.getElementById('displayGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        document.getElementById('voucherSelect').addEventListener('change', calculateGrandTotal);
        document.getElementById('addItem').addEventListener('click', addNewRow);
    </script>
@endpush
