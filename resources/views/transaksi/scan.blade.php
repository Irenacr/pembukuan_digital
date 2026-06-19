@extends('layout')

@section('content')

<div class="container">
    <h2 class="mb-4 fw-bold">Scan Nota Transaksi</h2>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <div class="row gx-4 gy-4">

            <div class="col-lg-5">

                <div class="p-4 rounded-4 border">

                    <label class="form-label text-muted">
                        Unggah Foto Nota
                    </label>

                    <input
                        type="file"
                        id="file"
                        class="form-control mb-3"
                        accept="image/*"
                        capture="environment">

                    <button
                        id="scan"
                        type="button"
                        class="btn btn-primary w-100 py-2 mb-3"
                        disabled>
                        Scan Nota
                    </button>

                    <div id="scan-status" class="text-muted small">
                        Pilih file nota terlebih dahulu lalu klik tombol Scan Nota.
                    </div>

                </div>

            </div>

            <div class="col-lg-7">

                <div
                    id="preview"
                    class="rounded-4 border p-3 d-flex align-items-center justify-content-center"
                    style="min-height:260px;">

                    <div class="text-center text-secondary">

                        <p class="mb-1">
                            Preview nota akan muncul di sini setelah memilih file.
                        </p>

                        <small>
                            Gunakan foto yang jelas dan tidak buram.
                        </small>

                    </div>

                </div>

            </div>

        </div>

        <pre id="raw" class="bg-light p-3 mt-4 rounded"></pre>

        <table class="table mt-3 d-none" id="table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>
</div>

</div>

<script>

let file = null;
let uploadFile = null;

const input = document.getElementById('file');
const btn = document.getElementById('scan');
const preview = document.getElementById('preview');
const raw = document.getElementById('raw');
const status = document.getElementById('scan-status');
const table = document.getElementById('table');
const tbody = table.querySelector('tbody');

const previewPlaceholder = `
<div class="text-center text-secondary">
    <p class="mb-1">
        Preview nota akan muncul di sini setelah memilih file.
    </p>
    <small>
        Gunakan foto yang jelas dan tidak buram.
    </small>
</div>
`;

input.addEventListener('change', () => {

    file = input.files[0];
    uploadFile = null;

    if (!file) {

        btn.disabled = true;

        preview.innerHTML = previewPlaceholder;

        status.textContent =
            'Pilih file nota terlebih dahulu lalu klik tombol Scan Nota.';

        status.classList.remove('text-danger');
        status.classList.add('text-muted');

        return;
    }

    btn.disabled = false;

    preview.innerHTML =
        `<img src="${URL.createObjectURL(file)}" class="img-fluid rounded">`;

    status.textContent =
        'File siap diproses. Klik Scan Nota untuk melanjutkan.';

    status.classList.remove('text-danger');
    status.classList.add('text-muted');
});

btn.addEventListener('click', async () => {

    if (!file) {

        status.textContent =
            'Pilih file nota terlebih dahulu.';

        status.classList.remove('text-muted');
        status.classList.add('text-danger');

        return;
    }

    btn.disabled = true;

    status.textContent =
        'Memproses scan nota... Mohon tunggu.';

    status.classList.remove('text-danger');
    status.classList.add('text-muted');

    const formData = new FormData();

    try {
        uploadFile = uploadFile || await resizeImageForUpload(file);
    } catch (err) {
        console.warn('Gagal resize gambar, memakai file asli.', err);
        uploadFile = file;
    }

    formData.append(
        'nota_scan',
        uploadFile,
        uploadFile.name || file.name
    );

    let response;

    try {

        console.log('{{ route("transaksi.scan.process") }}');

        response = await fetch(
            '{{ route("transaksi.scan.process") }}',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                },
                body: formData
            }
        );

} catch (err) {

        console.error(err);

        status.textContent =
            'Gagal menghubungi server.';

        status.classList.remove('text-muted');
        status.classList.add('text-danger');

        btn.disabled = false;

        return;
    }

    if (!response.ok) {

        let errorMessage =
            'Gagal memproses scan nota.';

        try {

            const data = await response.json();

            if (data.error) {
                errorMessage = data.error;
            }

        } catch {

            const text = await response.text();

            if (text) {
                errorMessage = text;
            }
        }

        console.error(errorMessage);

        status.textContent = errorMessage;

        status.classList.remove('text-muted');
        status.classList.add('text-danger');

        btn.disabled = false;

        return;
    }

    const data = await response.json();

    console.log(data);

    raw.textContent =
        data.ocr_text ||
        data.raw_text ||
        'Tidak ada teks terdeteksi.';

    if (data.redirect_url) {

        window.location.href =
            data.redirect_url;

        return;
    }

    if (data.items && data.items.length) {

        render(data.items);

        status.textContent =
            'Scan berhasil.';

        status.classList.remove('text-danger');
        status.classList.add('text-muted');

    } else {

        table.classList.add('d-none');

        status.textContent =
            'Tidak ada item yang berhasil dideteksi.';

        status.classList.remove('text-muted');
        status.classList.add('text-danger');
    }

    btn.disabled = false;
});

function render(items) {

    tbody.innerHTML = '';

    if (!items.length) {

        status.textContent =
            'Tidak ada item yang berhasil dideteksi.';

        status.classList.remove('text-muted');
        status.classList.add('text-danger');

        return;
    }

    items.forEach(item => {

        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${item.item_name || item.class_name || '-'}</td>
            <td>${item.jumlah ?? '-'}</td>
            <td>${item.harga_jual ? format(item.harga_jual) : '-'}</td>
            <td>${item.harga_jual && item.jumlah ? format(item.harga_jual * item.jumlah) : '-'}</td>
        `;

        tbody.appendChild(tr);
    });

    table.classList.remove('d-none');
}

function format(n) {

    return n
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function resizeImageForUpload(sourceFile) {

    const maxDimension = 1280;
    const quality = 0.82;

    if (!sourceFile.type.startsWith('image/')) {
        return Promise.resolve(sourceFile);
    }

    return new Promise((resolve, reject) => {

        const img = new Image();
        const objectUrl = URL.createObjectURL(sourceFile);

        img.onload = () => {

            URL.revokeObjectURL(objectUrl);

            const longest = Math.max(img.width, img.height);

            if (longest <= maxDimension && sourceFile.size <= 1500000) {
                resolve(sourceFile);
                return;
            }

            const scale = Math.min(1, maxDimension / longest);
            const canvas = document.createElement('canvas');

            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(blob => {

                if (!blob) {
                    reject(new Error('Canvas gagal membuat blob gambar.'));
                    return;
                }

                const resizedName =
                    sourceFile.name.replace(/\.[^.]+$/, '') + '.jpg';

                resolve(new File([blob], resizedName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }));

            }, 'image/jpeg', quality);
        };

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Gambar tidak bisa dibaca browser.'));
        };

        img.src = objectUrl;
    });
}

</script>

@endsection
