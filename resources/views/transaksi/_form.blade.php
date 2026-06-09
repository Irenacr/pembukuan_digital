@php
    $selectedCustomerId = old('customer_id', $transaksi->customer_id ?? '');
    $selectedKategori = old('kategori', $transaksi->kategori ?? '');
    $selectedStatus = old('status_pembayaran', $transaksi->status_pembayaran ?? '');
    $selectedMethod = old('metode_pembayaran', $transaksi->metode_pembayaran ?? '');
    $diskonTransaksi = old('diskon_transaksi', $transaksi->diskon_transaksi ?? 0);
    $lokasiPengiriman = old('lokasi_pengiriman', $transaksi->lokasi_pengiriman ?? '');
    $namaPenerima = old('nama_penerima', $transaksi->nama_penerima ?? '');
    $notaScan = $transaksi->nota_scan ?? null;
    $scanItems = $scanItems ?? [];
    $scanText = $scanText ?? null;

    $initialItems = [];
    if (old('barang_id')) {
        $oldBarangIds = old('barang_id', []);
        $oldHargaJual = old('harga_jual', []);
        $oldJumlah = old('jumlah', []);
        $oldItemName = old('item_name', []);
        foreach ($oldBarangIds as $index => $barangId) {
            $initialItems[] = [
                'barang_id' => $barangId,
                'harga_jual' => $oldHargaJual[$index] ?? '',
                'jumlah' => $oldJumlah[$index] ?? 1,
                'item_name' => $oldItemName[$index] ?? '',
            ];
        }
    } elseif (isset($transaksi)) {
        $initialItems = $transaksi->detailTransaksis->map(function ($detail) {
            return [
                'barang_id' => $detail->barang_id,
                'harga_jual' => $detail->harga_satuan,
                'jumlah' => $detail->qty,
                'item_name' => $detail->barang->nama_barang ?? '',
            ];
        })->toArray();
    } elseif (!empty($scanItems)) {
        $initialItems = $scanItems;
    }

    $barangItemsData = $barangs->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->nama_barang,
            'harga' => $item->harga,
        ];
    })->toArray();
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Transaksi</label>
        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $transaksi->tanggal ?? date('Y-m-d')) }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select" required>
            <option value="">-- Pilih Customer --</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                    {{ $customer->nama }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label class="form-label">Daftar Barang</label>
        <table class="table table-bordered" id="items-table">
            <thead class="table-light text-center">
                <tr>
                    <th>Barang</th>
                    <th>Harga Jual</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- rows akan ditambahkan oleh JavaScript --}}
            </tbody>
        </table>
        <button type="button" id="add-item" class="btn btn-secondary">Tambah Barang</button>
    </div>
</div>

@if($scanText)
    <div class="alert alert-info">
        <strong>Hasil Scan OCR:</strong>
        <pre class="mb-0" style="white-space: pre-wrap;">{{ $scanText }}</pre>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Kategori Barang</label>
        <select name="kategori" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="barang_baru" {{ $selectedKategori === 'barang_baru' ? 'selected' : '' }}>Barang Baru</option>
            <option value="barang_bekas" {{ $selectedKategori === 'barang_bekas' ? 'selected' : '' }}>Barang Bekas</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold text-primary">📄 Upload Nota Scan</label>
        <input type="file" name="nota_scan" class="form-control" accept=".jpg,.jpeg,.png,.pdf" id="nota_scan">
        <small class="form-text text-muted d-block mt-2">Format: JPG, JPEG, PNG, PDF | Maksimal: 5MB</small>
        <div class="mt-2 d-flex gap-2">
            <a href="{{ route('transaksi.scan') }}" target="_blank" class="btn btn-sm btn-outline-primary">🔍 Scan Nota</a>
            @if($notaScan)
                <a href="{{ asset('storage/' . $notaScan) }}" target="_blank" class="btn btn-sm btn-info">📥 Lihat Nota Saat Ini</a>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Status Pembayaran</label>
        <select name="status_pembayaran" class="form-select" required>
            <option value="">-- Pilih Status --</option>
            <option value="lunas" {{ $selectedStatus == 'lunas' ? 'selected' : '' }}>Lunas</option>
            <option value="DP" {{ $selectedStatus == 'DP' ? 'selected' : '' }}>DP</option>
            <option value="belum_bayar" {{ $selectedStatus == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Metode Pembayaran</label>
        <select name="metode_pembayaran" class="form-select" required>
            <option value="">-- Pilih Metode --</option>
            <option value="Tunai" {{ $selectedMethod == 'Tunai' ? 'selected' : '' }}>Tunai</option>
            <option value="Transfer" {{ $selectedMethod == 'Transfer' ? 'selected' : '' }}>Transfer</option>
            <option value="QRIS" {{ $selectedMethod == 'QRIS' ? 'selected' : '' }}>QRIS</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Diskon Transaksi</label>
        <input type="number" name="diskon_transaksi" class="form-control" value="{{ $diskonTransaksi }}" min="0">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Grand Total</label>
        <input type="text" id="grand-total" class="form-control" readonly value="Rp 0">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Lokasi Pengiriman</label>
    <input type="text" name="lokasi_pengiriman" class="form-control" value="{{ $lokasiPengiriman }}">
</div>

<div class="mb-3">
    <label class="form-label">Nama Penerima</label>
    <input type="text" name="nama_penerima" class="form-control" value="{{ $namaPenerima }}">
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Kembali</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Simpan' }}</button>
</div>

<script>
    const barangItems = @json($barangItemsData);
    const initialItems = @json($initialItems);

    const itemsTableBody = document.querySelector('#items-table tbody');
    const addItemButton = document.getElementById('add-item');

    function formatCurrency(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function buildOptions(selectedId = '') {
        let options = '<option value="">-- Pilih Barang --</option>';
        barangItems.forEach(item => {
            options += `<option value="${item.id}" data-harga="${item.harga}" ${item.id == selectedId ? 'selected' : ''}>${item.name}</option>`;
        });
        return options;
    }

    function calculateSubtotal(row) {
        const harga = Number(row.querySelector('.harga-jual').value || 0);
        const jumlah = Number(row.querySelector('.jumlah-item').value || 0);
        const subtotal = harga * jumlah;
        row.querySelector('.item-subtotal').textContent = formatCurrency(subtotal);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('#items-table tbody tr').forEach(row => {
            const harga = Number(row.querySelector('.harga-jual').value || 0);
            const jumlah = Number(row.querySelector('.jumlah-item').value || 0);
            total += harga * jumlah;
        });
        const diskon = Number(document.querySelector('input[name="diskon_transaksi"]').value || 0);
        total -= diskon;
        document.getElementById('grand-total').value = formatCurrency(total >= 0 ? total : 0);
    }

    function createRow(data = {}) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="barang_id[]" class="form-select item-barang" required>
                    ${buildOptions(data.barang_id || '')}
                </select>
                <input type="hidden" name="item_name[]" class="item-name" value="${data.item_name ?? ''}">
                ${data.item_name ? `<small class="text-muted">Hasil scan: ${data.item_name}</small>` : ''}
            </td>
            <td>
                <input type="number" name="harga_jual[]" class="form-control harga-jual" min="0" value="${data.harga_jual ?? ''}" required>
            </td>
            <td>
                <input type="number" name="jumlah[]" class="form-control jumlah-item" min="1" value="${data.jumlah ?? 1}" required>
            </td>
            <td class="item-subtotal text-end">${formatCurrency((data.harga_jual || 0) * (data.jumlah || 0))}</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row">Hapus</button>
            </td>
        `;

        const barangSelect = row.querySelector('.item-barang');
        const hargaInput = row.querySelector('.harga-jual');
        const jumlahInput = row.querySelector('.jumlah-item');
        const removeButton = row.querySelector('.btn-remove-row');

        barangSelect.addEventListener('change', event => {
            const selected = barangItems.find(item => item.id == event.target.value);
            if (selected) {
                hargaInput.value = selected.harga;
            }
            calculateSubtotal(row);
        });
        hargaInput.addEventListener('input', () => calculateSubtotal(row));
        jumlahInput.addEventListener('input', () => calculateSubtotal(row));
        removeButton.addEventListener('click', () => {
            row.remove();
            if (itemsTableBody.children.length === 0) {
                addDefaultRow();
            }
            calculateGrandTotal();
        });
        itemsTableBody.appendChild(row);
        return row;
    }

    function addDefaultRow() {
        createRow({});
    }

    addItemButton.addEventListener('click', () => createRow({}));
    document.querySelector('input[name="diskon_transaksi"]').addEventListener('input', calculateGrandTotal);

    if (initialItems.length > 0) {
        initialItems.forEach(item => createRow(item));
    } else {
        addDefaultRow();
    }

    calculateGrandTotal();
</script>
