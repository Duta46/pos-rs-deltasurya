@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('page-title')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center my-0">
            Tambah Transaksi
        </h1>
    </div>
@endsection

@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
                    @csrf
                    <!-- Header Info -->
                    <div class="card-body p-9">
                        <div class="row mb-5">
                            <div class="col-xl-3">
                                <label for="patient_name" class="fs-6 fw-bold mt-2 mb-3">Nama Pasien</label>
                            </div>
                            <div class="col-lg">
                                <input type="text" name="patient_name"
                                    class="form-control @error('patient_name') is-invalid @enderror "
                                    placeholder="Nama Pasien" />
                                @error('patient_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
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
                                    <option value="" disabled selected>-- Pilih Asuransi --</option>
                                    @foreach ($insurances as $ins)
                                        <option value="{{ $ins['name'] }}"
                                            {{ old('insurance_name') == $ins['name'] ? 'selected' : '' }}>
                                            {{ $ins['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @error('insurance_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-6 overflow-x-auto">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            Daftar Tindakan Medis
                        </h3>
                        <table class="min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden"
                            id="itemsTable">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                        Prosedur</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-40">
                                        Harga</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-24">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-40">
                                        Subtotal</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-20">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="itemsBody">
                                <!-- JavaScript will insert rows here -->
                            </tbody>
                        </table>
                        <div class="mt-4">
                            <button type="button" id="addItem" class="btn btn-primary ms-2">
                                Tambah Tindakan
                            </button>
                        </div>
                    </div>

                    <!-- Summary & Voucher -->
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between gap-6">
                            <!-- Voucher Selection -->
                            <div class="w-full md:w-1/2">
                                <label class="block text-sm font-semibold text-gray-700 uppercase mb-2">Voucher
                                    Diskon</label>
                                <select name="voucher_id" id="voucherSelect"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="" data-type="nominal" data-value="0">-- Tanpa Voucher --</option>
                                    @foreach ($vouchers as $v)
                                        <option value="{{ $v->id }}" data-type="{{ $v->type }}"
                                            data-value="{{ $v->value }}" data-max="{{ $v->max_discount }}">
                                            {{ $v->insurance_name }} -
                                            {{ $v->type == 'percent' ? $v->value . '%' : 'Rp ' . number_format($v->value, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Totals Display -->
                            <div class="w-full md:w-1/3 flex flex-col space-y-3">
                                <div class="flex justify-between text-gray-600">
                                    <span class="font-medium">Total Item:</span>
                                    <span id="displayTotalItem" class="font-bold">Rp 0</span>
                                </div>
                                <div class="flex justify-between text-red-600 border-b pb-2">
                                    <span class="font-medium">Diskon:</span>
                                    <span id="displayDiscount" class="font-bold">- Rp 0</span>
                                </div>
                                <div class="flex justify-between text-xl font-extrabold text-blue-800 pt-1">
                                    <span>GRAND TOTAL:</span>
                                    <span id="displayGrandTotal">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            Simpan Transaksi (Draft)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        let rowCount = 0;
        const procedures = @json($procedures);

        // Function to add a new row to the items table
        function addNewRow() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.id = `row-${rowCount}`;
            row.className = 'hover:bg-gray-50 transition';

            row.innerHTML = `
                <td class="px-4 py-3">
                    <select name="items[${rowCount}][procedure_id]" required
                        class="procedure-select w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        onchange="updatePrice(${rowCount})">
                        <option value="">-- Pilih Prosedur --</option>
                     ${procedures.map(p => {
                        const name = String(p.name).replace(/"/g, '&quot;');
                        return `<option value="${p.id}" data-name="${name}">${name}</option>`;
                        }).join('')}
                    </select>
                    <input type="hidden" name="items[${rowCount}][procedure_name]" class="procedure-name">
                </td>
                <td class="px-4 py-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                        <input type="number" name="items[${rowCount}][price]" readonly
                            class="price-input w-full pl-10 bg-gray-100 border-gray-300 rounded-md shadow-sm text-right font-mono" placeholder="0">
                    </div>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${rowCount}][qty]" value="1" min="1" required
                        class="qty-input w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center"
                        oninput="calculateRow(${rowCount})">
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                        <input type="number" readonly
                            class="subtotal-input w-full pl-10 bg-gray-100 border-gray-300 rounded-md shadow-sm text-right font-bold font-mono" value="0">
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeRow(${rowCount})" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            rowCount++;
        }

        function removeRow(id) {
            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.remove();
                calculateGrandTotal();
            }
        }

        async function updatePrice(id) {
            const row = document.getElementById(`row-${id}`);
            const select = row.querySelector('.procedure-select');
            const procId = select.value;
            const nameInput = row.querySelector('.procedure-name');
            const priceInput = row.querySelector('.price-input');

            if (!procId) {
                nameInput.value = '';
                priceInput.value = 0;
                calculateRow(id);
                return;
            }

            // Set procedure name from selected option data attribute
            nameInput.value = select.options[select.selectedIndex].dataset.name;

            // Show loading state
            priceInput.value = 0;
            priceInput.placeholder = 'Loading...';
            select.disabled = true;

            try {
                const response = await fetch(`/api/procedures/${procId}/price`);
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to fetch price');
                }

                priceInput.value = data.price;
                calculateRow(id);
            } catch (error) {
                console.error('Error fetching price:', error);
                alert('Gagal mengambil harga prosedur: ' + error.message);
                select.value = ''; // Reset select on error
                priceInput.value = '';
                priceInput.placeholder = '0';
            } finally {
                select.disabled = false;
            }
        }

        function calculateRow(id) {
            const row = document.getElementById(`row-${id}`);
            if (!row) return;

            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            const subtotal = price * qty;

            row.querySelector('.subtotal-input').value = subtotal;
            calculateGrandTotal();
        }

        function formatRupiah(amount) {
            return 'Rp ' + amount.toLocaleString('id-ID');
        }

        function calculateGrandTotal() {
            let totalItem = 0;
            document.querySelectorAll('.subtotal-input').forEach(input => {
                totalItem += parseFloat(input.value) || 0;
            });

            const voucher = document.getElementById('voucherSelect');
            const selected = voucher.options[voucher.selectedIndex];
            const type = selected.dataset.type;
            const val = parseFloat(selected.dataset.value) || 0;
            const maxDisc = parseFloat(selected.dataset.max) || 0;

            let discount = 0;
            if (type === 'percent') {
                discount = (val / 100) * totalItem;
                if (maxDisc > 0) discount = Math.min(discount, maxDisc);
            } else {
                discount = val;
            }

            const grandTotal = Math.max(0, totalItem - discount);

            document.getElementById('displayTotalItem').innerText = formatRupiah(totalItem);
            document.getElementById('displayDiscount').innerText = '- ' + formatRupiah(discount);
            document.getElementById('displayGrandTotal').innerText = formatRupiah(grandTotal);
        }

        // Listen for voucher change
        document.getElementById('voucherSelect').addEventListener('change', calculateGrandTotal);

        // Listen for add button
        document.getElementById('addItem').addEventListener('click', addNewRow);

        // Initial state: add one row and focus name
        window.onload = () => {
            addNewRow();
            document.querySelector('input[name="patient_name"]').focus();
        };

        document.getElementById('transactionForm').addEventListener('submit', function() {

            document.querySelectorAll('#itemsBody tr').forEach((row, index) => {

                const priceInput = row.querySelector('.price-input');
                const nameInput = row.querySelector('.procedure-name');
                const select = row.querySelector('.procedure-select');

                // FIX PRICE
                if (!priceInput.value || isNaN(priceInput.value)) {
                    priceInput.value = 0;
                }

                // FIX PROCEDURE NAME
                if (!nameInput.value && select.value) {
                    nameInput.value = select.options[select.selectedIndex].dataset.name || 'Unknown';
                }

            });

            console.log('Form aman dikirim 🚀');
        });
    </script>
@endpush
