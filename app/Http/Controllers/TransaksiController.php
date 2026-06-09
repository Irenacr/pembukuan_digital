<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TransaksiController extends Controller
{
    /**
     * Menampilkan semua transaksi
     */
    public function index()
    {
        $transaksis = Transaksi::with([
    'customer',
    'detailTransaksis.barang'
])->latest()->get();
        return view('transaksi.index', compact('transaksis'));
    }

    /**
     * Form tambah transaksi
     */
    public function create()
    {
        $customers = Customer::all();
        $barangs = Barang::all();

        $scanData = session()->pull('transaksi_scan', []);
        $scanItems = $scanData['items'] ?? [];
        $scanText = $scanData['ocr_text'] ?? null;

        return view('transaksi.create', compact('customers','barangs','scanItems','scanText'));
    }

    public function scanForm()
    {
        return view('transaksi.scan');
    }

    public function scanProcess(Request $request)
    {
        $validated = $request->validate([
            'nota_scan' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('nota_scan')->store('temp_nota', 'public');
        $fullPath = Storage::disk('public')->path($path);

        Log::info('Scan nota upload saved', [
            'disk' => 'public',
            'relative_path' => $path,
            'full_path' => $fullPath,
            'exists' => file_exists($fullPath),
            'readable' => is_readable($fullPath),
        ]);

        try {
            $result = $this->runNotaOcr($fullPath);
            $ocrText = $result['ocr_text'] ?? $result['raw_text'] ?? null;
            $detectedItems = $result['items'] ?? [];
            $parsedItems = [];

if (!empty($result['detections'])) {

    $parsedItems = $this->parseYoloDetections(
        $result['detections']
    );

} else {

    $parsedItems = $this->parseNotaTextToItems(
        $ocrText ?? ''
    );
}

            if (empty($parsedItems) && $ocrText) {
                $parsedItems = $this->parseNotaTextToItems($ocrText);
            }

            session(['transaksi_scan' => [
                'ocr_text' => $ocrText,
                'items' => $parsedItems,
            ]]);
        } catch (\Exception $e) {
            Log::error('Scan nota process error: ' . $e->getMessage(), ['exception' => $e]);
            Storage::disk('public')->delete($path);
            return response()->json(['error' => 'OCR gagal: ' . $e->getMessage()], 500);
        }

        Storage::disk('public')->delete($path);

        return response()->json([
            'success' => true,
            'ocr_text' => $ocrText,
            'items' => $parsedItems,
            'redirect_url' => route('transaksi.create'),
        ]);
    }

    private function runNotaOcr(string $filePath): array
{
    $pythonCommand = $this->getPythonBinary();
    $scriptPath = base_path('ocr/scan_nota.py');

    Log::info('OCR execution details', [
        'python_binary' => $pythonCommand,
        'script_path' => $scriptPath,
        'file_path' => $filePath,
        'file_exists' => file_exists($filePath),
        'file_readable' => is_readable($filePath),
    ]);

    $versionProcess = new Process([$pythonCommand, '--version']);
    $versionProcess->setTimeout(10);
    $versionProcess->run();

    Log::info('Python version check', [
        'stdout' => trim($versionProcess->getOutput()),
        'stderr' => trim($versionProcess->getErrorOutput()),
        'successful' => $versionProcess->isSuccessful(),
    ]);

    $envCheckScript = <<<'PY'
import os, sys
print('sys.executable=' + sys.executable)
print('PATH=' + os.environ.get('PATH', ''))
print('SystemRoot=' + os.environ.get('SystemRoot', ''))
print('WINDIR=' + os.environ.get('WINDIR', ''))
print('PYTHONPATH=' + os.environ.get('PYTHONPATH', ''))
print('PYTHONHOME=' + os.environ.get('PYTHONHOME', ''))
print('COMSPEC=' + os.environ.get('COMSPEC', ''))
print('PROCESSOR_ARCHITECTURE=' + os.environ.get('PROCESSOR_ARCHITECTURE', ''))
print('PROCESSOR_IDENTIFIER=' + os.environ.get('PROCESSOR_IDENTIFIER', ''))
PY;

    $envCheckProcess = new Process([
        $pythonCommand,
        '-c',
        $envCheckScript,
    ]);
    $envCheckProcess->setTimeout(10);
    $envCheckProcess->run();

    Log::info('Python env from Laravel process', [
        'stdout' => trim($envCheckProcess->getOutput()),
        'stderr' => trim($envCheckProcess->getErrorOutput()),
        'successful' => $envCheckProcess->isSuccessful(),
    ]);

    $homeDir = getenv('HOME') ?: getenv('USERPROFILE') ?: 'C:\\Users\\Ideapad slim 3';
    $processEnv = [
        'PATH' => getenv('PATH') ?: '',
        'HOME' => $homeDir,
        'USERPROFILE' => $homeDir,
        'HOMEDRIVE' => getenv('HOMEDRIVE') ?: 'C:',
        'HOMEPATH' => getenv('HOMEPATH') ?: '\\Users\\Ideapad slim 3',
        'APPDATA' => getenv('APPDATA') ?: $homeDir . '\\AppData\\Roaming',
        'LOCALAPPDATA' => getenv('LOCALAPPDATA') ?: $homeDir . '\\AppData\\Local',
        'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
        'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
        'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
        'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
        'PYTHONIOENCODING' => 'utf-8',
        'PYTHONUNBUFFERED' => '1',
    ];

    $process = new Process([
        $pythonCommand,
        $scriptPath,
        $filePath
    ],
    null,
    $processEnv
);
    $process->setTimeout(180);
    
    $process->run();

    Log::info('OCR process output', [
        'successful' => $process->isSuccessful(),
        'stdout' => $process->getOutput(),
        'stderr' => $process->getErrorOutput(),
    ]);

    if (!$process->isSuccessful()) {
        $error = trim($process->getErrorOutput() ?: $process->getOutput());

        throw new \RuntimeException(
            "OCR script failed using PHP Python binary [{$pythonCommand}]: {$error}"
        );
    }

    $output = $process->getOutput();
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('Invalid OCR JSON response', [
            'json_error' => json_last_error_msg(),
            'raw_output' => $output,
        ]);

        throw new \RuntimeException(
            'Invalid JSON response from OCR script: ' . json_last_error_msg()
        );
    }

    return $result;
}

    private function getPythonBinary(): string
    {
        $pythonBinary = env('PYTHON_BINARY');

        if ($pythonBinary) {
            $pythonBinary = trim($pythonBinary, " \t\n\r\0\x0B\"'");
            if (file_exists($pythonBinary)) {
                return $pythonBinary;
            }

            Log::warning('PYTHON_BINARY value set but executable not found', ['path' => $pythonBinary]);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $candidates = [
                'C:\\Users\\Ideapad slim 3\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
            ];

            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return 'python';
    }

    private function parseYoloDetections(array $detections): array
{
    $qtys = [];
    $names = [];
    $prices = [];

    foreach ($detections as $d) {

        $class = $d['class_name'] ?? '';
        $text = trim($d['text'] ?? '');

        if (!$text) {
            continue;
        }

        switch ($class) {

            case 'banyak_barang_satuan':
                $qtys[] = preg_replace('/\D/', '', $text);
                break;

            case 'nama_barang':
                $names[] = preg_replace('/\s+/', ' ', $text);
                break;

            case 'harga_satuan':
                $prices[] = preg_replace('/\D/', '', $text);
                break;
        }
    }

    $items = [];

    $count = max(
        count($names),
        count($qtys),
        count($prices)
    );

    for ($i = 0; $i < $count; $i++) {

        $items[] = [
            'barang_id' => '',
            'item_name' => $names[$i] ?? '',
            'jumlah' => max((int)($qtys[$i] ?? 1), 1),
            'harga_jual' => (int)($prices[$i] ?? 0),
        ];
    }

    return $items;
}
    private function mapOcrDetectedItems(array $ocrItems): array
    {
        $items = [];

        foreach ($ocrItems as $ocrItem) {
            $text = trim($ocrItem['text'] ?? '');
            $className = trim($ocrItem['class_name'] ?? '');
            $itemName = $text ?: $className;
            if (!$itemName) {
                continue;
            }

            preg_match_all('/\d+/', $itemName, $matches);
            $numbers = $matches[0] ?? [];

            $jumlah = 1;
            $harga = 0;

            if (count($numbers) >= 1) {
                $jumlah = (int) array_shift($numbers);
                if ($jumlah <= 0) {
                    $jumlah = 1;
                }
            }

            if (count($numbers) >= 1) {
                $harga = (int) array_pop($numbers);
            }

            $name = preg_replace('/\d+/', ' ', $itemName);
            $name = preg_replace('/[^\w\s]/u', ' ', $name);
            $name = trim(preg_replace('/\s+/', ' ', $name));
            if (strlen($name) < 2) {
                $name = $itemName;
            }

            $items[] = [
                'barang_id' => '',
                'harga_jual' => $harga,
                'jumlah' => $jumlah,
                'item_name' => $name,
            ];
        }

        return $items;
    }

    /**
     * Simpan transaksi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'kategori' => 'required|in:barang_baru,barang_bekas',
            'status_pembayaran' => 'required',
            'metode_pembayaran' => 'required',
            'diskon_transaksi' => 'nullable|numeric|min:0',
            'lokasi_pengiriman' => 'required',
            'nama_penerima' => 'required',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|integer|exists:barangs,id',
            'harga_jual' => 'required|array|min:1',
            'harga_jual.*' => 'required|numeric|min:0',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'nota_scan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $barangIds = $validated['barang_id'];
        $hargaJuals = $validated['harga_jual'];
        $jumlahs = $validated['jumlah'];

        if (count($barangIds) !== count($hargaJuals) || count($barangIds) !== count($jumlahs)) {
            return back()->with('error', 'Format input barang tidak valid');
        }

        $barangs = Barang::whereIn('id', $barangIds)->get()->keyBy('id');

        $stockNeeded = [];
        $items = [];
        $total = 0;

        foreach ($barangIds as $index => $barangId) {
            $qty = $jumlahs[$index];
            $hargaJual = $hargaJuals[$index];

            if (!isset($barangs[$barangId])) {
                return back()->with('error', 'Barang tidak ditemukan pada daftar');
            }

            $stockNeeded[$barangId] = ($stockNeeded[$barangId] ?? 0) + $qty;

            $subtotal = $hargaJual * $qty;
            $total += $subtotal;

            $items[] = [
                'barang_id' => $barangId,
                'qty' => $qty,
                'harga_satuan' => $hargaJual,
                'diskon' => 0,
                'subtotal' => $subtotal,
            ];
        }

        foreach ($stockNeeded as $barangId => $qtyNeeded) {
            if ($barangs[$barangId]->stok < $qtyNeeded) {
                return back()->with('error', 'Stok tidak cukup untuk barang: ' . $barangs[$barangId]->nama_barang);
            }
        }

        $diskon = $validated['diskon_transaksi'] ?? 0;
        $total -= $diskon;

        $notaScanPath = null;
        $notaText = null;
        if ($request->hasFile('nota_scan')) {
            $notaScanPath = $request->file('nota_scan')->store('nota', 'public');
            $fullPath = Storage::disk('public')->path($notaScanPath);
            try {
                $ocrResult = $this->runNotaOcr($fullPath);
                $notaText = $ocrResult['ocr_text'] ?? $ocrResult['raw_text'] ?? null;
            } catch (\Exception $e) {
                Log::error('Nota OCR error saat simpan transaksi: ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'customer_id' => $validated['customer_id'],
                'kategori' => $validated['kategori'],
                'total' => $total,
                'diskon_transaksi' => $diskon,
                'tanggal' => $validated['tanggal'],
                'lokasi_pengiriman' => $validated['lokasi_pengiriman'],
                'nama_penerima' => $validated['nama_penerima'],
                'status_pembayaran' => $validated['status_pembayaran'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'nota_scan' => $notaScanPath,
                'nota_text' => $notaText,
            ]);

            foreach ($items as $item) {
                DetailTransaksi::create(array_merge($item, [
                    'transaksi_id' => $transaksi->id,
                ]));
            }

            foreach ($stockNeeded as $barangId => $qtyNeeded) {
                $barang = $barangs[$barangId];
                $barang->stok -= $qtyNeeded;
                $barang->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaksi store error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi error saat menyimpan transaksi: ' . $e->getMessage());
        }

        return redirect('/transaksi')
            ->with('success', 'Transaksi berhasil disimpan');
    }

    public function show(Transaksi $transaksi)
    {
        //
    }

    public function edit(Transaksi $transaksi)
    {
        $customers = Customer::all();
        $barangs = Barang::all();

        return view('transaksi.edit', compact('transaksi','customers','barangs'));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'kategori' => 'required|in:barang_baru,barang_bekas',
            'status_pembayaran' => 'required',
            'metode_pembayaran' => 'required',
            'diskon_transaksi' => 'nullable|numeric|min:0',
            'lokasi_pengiriman' => 'required',
            'nama_penerima' => 'required',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|integer|exists:barangs,id',
            'harga_jual' => 'required|array|min:1',
            'harga_jual.*' => 'required|numeric|min:0',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'nota_scan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $barangIds = $validated['barang_id'];
        $hargaJuals = $validated['harga_jual'];
        $jumlahs = $validated['jumlah'];

        if (count($barangIds) !== count($hargaJuals) || count($barangIds) !== count($jumlahs)) {
            return back()->with('error', 'Format input barang tidak valid');
        }

        $barangs = Barang::whereIn('id', $barangIds)->get()->keyBy('id');
        $stockNeeded = [];
        $items = [];
        $total = 0;

        foreach ($barangIds as $index => $barangId) {
            $qty = $jumlahs[$index];
            $hargaJual = $hargaJuals[$index];

            if (!isset($barangs[$barangId])) {
                return back()->with('error', 'Barang tidak ditemukan pada daftar');
            }

            $stockNeeded[$barangId] = ($stockNeeded[$barangId] ?? 0) + $qty;
            $subtotal = $hargaJual * $qty;
            $total += $subtotal;

            $items[] = [
                'barang_id' => $barangId,
                'qty' => $qty,
                'harga_satuan' => $hargaJual,
                'diskon' => 0,
                'subtotal' => $subtotal,
            ];
        }

        $diskon = $validated['diskon_transaksi'] ?? 0;
        $total -= $diskon;

        DB::beginTransaction();
        try {
            // Kembalikan stok dari detail transaksi lama sebelum validasi stok baru
            foreach ($transaksi->detailTransaksis as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->stok += $detail->qty;
                    $barang->save();
                }
            }

            foreach ($stockNeeded as $barangId => $qtyNeeded) {
                if ($barangs[$barangId]->stok < $qtyNeeded) {
                    DB::rollBack();
                    return back()->with('error', 'Stok tidak cukup untuk barang: ' . $barangs[$barangId]->nama_barang);
                }
            }

            $notaScanPath = $transaksi->nota_scan;
            $notaText = $transaksi->nota_text;
            if ($request->hasFile('nota_scan')) {
                if ($transaksi->nota_scan) {
                    Storage::disk('public')->delete($transaksi->nota_scan);
                }
                $notaScanPath = $request->file('nota_scan')->store('nota', 'public');
                $fullPath = Storage::disk('public')->path($notaScanPath);
                try {
                    $ocrResult = $this->runNotaOcr($fullPath);
                    $notaText = $ocrResult['ocr_text'] ?? $ocrResult['raw_text'] ?? null;
                } catch (\Exception $e) {
                    Log::error('Nota OCR error saat update transaksi: ' . $e->getMessage(), ['exception' => $e]);
                }
            }

            $transaksi->update([
                'customer_id' => $validated['customer_id'],
                'kategori' => $validated['kategori'],
                'total' => $total,
                'diskon_transaksi' => $diskon,
                'tanggal' => $validated['tanggal'],
                'lokasi_pengiriman' => $validated['lokasi_pengiriman'],
                'nama_penerima' => $validated['nama_penerima'],
                'status_pembayaran' => $validated['status_pembayaran'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'nota_scan' => $notaScanPath,
                'nota_text' => $notaText,
            ]);

            $transaksi->detailTransaksis()->delete();
            foreach ($items as $item) {
                DetailTransaksi::create(array_merge($item, [
                    'transaksi_id' => $transaksi->id,
                ]));
            }

            foreach ($stockNeeded as $barangId => $qtyNeeded) {
                $barang = $barangs[$barangId];
                $barang->stok -= $qtyNeeded;
                $barang->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaksi update error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi error saat memperbarui transaksi: ' . $e->getMessage());
        }

        return redirect('/transaksi')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(Transaksi $transaksi)
    {
        DB::beginTransaction();
        try {
            foreach ($transaksi->detailTransaksis as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->stok += $detail->qty;
                    $barang->save();
                }
            }

            if ($transaksi->nota_scan) {
                Storage::disk('public')->delete($transaksi->nota_scan);
            }

            $transaksi->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaksi delete error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus transaksi.');
        }

        return redirect('/transaksi')->with('success','Data berhasil dihapus');
    }
}