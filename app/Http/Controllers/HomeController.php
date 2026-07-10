<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Kapster;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $layanan = Layanan::aktif()->orderBy('harga')->get();
        $kapsters = Kapster::aktif()->orderBy('nama')->get();
        return view('home', compact('layanan', 'kapsters'));
    }

    public function booking(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Silakan login terlebih dahulu untuk melakukan booking.');
        }

        $request->validate([
            'layanan'  => 'required|string',
            'kapster'  => 'required|exists:kapsters,id',
            'tanggal'  => 'required|date|after_or_equal:today',
            'jam'      => 'required',
        ], [
            'layanan.required'  => 'Pilih layanan terlebih dahulu.',
            'kapster.required'  => 'Pilih kapster terlebih dahulu.',
            'tanggal.required'  => 'Tanggal booking wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'jam.required'      => 'Jam booking wajib diisi.',
        ]);

        $user = Auth::user();

        if ($user->is_blocked) {
            return back()->with('error', 'Akun Anda sedang diblokir sementara. Silakan hubungi admin.');
        }

        if ($user->hasPenalti()) {
            return back()->with('error', 'Akun Anda sedang ditangguhkan karena terlalu sering membatalkan antrian.');
        }

        $layananNama = $request->layanan;
        $kapsterId = $request->kapster;
        $tanggal = $request->tanggal;
        $jam = $request->jam;

        // Validasi jam operasional 09:00–20:30
        [$h, $m] = explode(':', $jam);
        $h = (int) $h; $m = (int) $m;
        if ($h < 9 || ($h == 20 && $m > 30) || $h > 20) {
            return back()->with('error', 'Jam booking harus antara 09:00 – 20:30 WIB.');
        }

        // Ambil harga dan durasi dari DB
        $layananData = Layanan::where('nama_layanan', $layananNama)->where('aktif', true)->first();
        if (!$layananData) {
            return back()->with('error', 'Layanan tidak valid.');
        }
        $waktuSelesai = \Carbon\Carbon::parse($jam)->addMinutes($layananData->durasi_menit)->format('H:i');

        // Cek jadwal bentrok untuk kapster tersebut
        $bentrok = Antrian::where('kapster_id', $kapsterId)
            ->where('tanggal_booking', $tanggal)
            ->whereIn('status', ['Menunggu', 'Dipanggil'])
            ->where(function($query) use ($jam, $waktuSelesai) {
                $query->where('jam_booking', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>', $jam);
            })
            ->exists();

        if ($bentrok) {
            return back()->with('error', 'Maaf, kapster tersebut sudah memiliki jadwal di waktu yang dipilih. Silakan pilih kapster atau jam lain.');
        }

        // Cek booking aktif di tanggal yang sama
        $bookingAktif = Antrian::where('user_id', $user->id)
            ->where('tanggal_booking', $tanggal)
            ->whereIn('status', ['Menunggu', 'Dipanggil'])
            ->exists();

        if ($bookingAktif) {
            return back()->with('error', 'Anda sudah memiliki booking aktif di tanggal tersebut.');
        }

        // Generate nomor antrian
        $maxNo = Antrian::where('tanggal_booking', $tanggal)->max('nomor_antrian') ?? 0;
        $nomorAntrian = $maxNo + 1;

        $antrian = Antrian::create([
            'user_id'         => $user->id,
            'kapster_id'      => $kapsterId,
            'nama'            => $user->nama,
            'layanan'         => $layananNama,
            'harga'           => $layananData->harga,
            'nomor_antrian'   => $nomorAntrian,
            'status'          => 'Menunggu',
            'tanggal_booking' => $tanggal,
            'jam_booking'     => $jam,
            'waktu_selesai'   => $waktuSelesai,
            'notif'           => true,
            'payment_status'  => 'pending',
        ]);

        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => 'ANTRIAN-' . $antrian->id,
                'gross_amount' => $layananData->harga,
            ],
            'customer_details' => [
                'first_name' => $user->nama,
                'email' => $user->email,
            ],
            'custom_expiry' => [
                'expiry_duration' => 15,
                'unit' => 'minute',
            ],
            'callbacks' => [
                'finish' => route('user.profil'),
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $antrian->update(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran. ' . $e->getMessage());
        }

        return redirect()->route('user.profil')
            ->with('success', "Booking Berhasil! Silakan lakukan pembayaran.")
            ->with('snap_token', $snapToken);
    }
}
