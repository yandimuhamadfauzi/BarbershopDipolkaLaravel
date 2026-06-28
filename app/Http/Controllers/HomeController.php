<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $layanan = Layanan::aktif()->orderBy('harga')->get();
        return view('home', compact('layanan'));
    }

    public function booking(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Silakan login terlebih dahulu untuk melakukan booking.');
        }

        $request->validate([
            'layanan'  => 'required|string',
            'tanggal'  => 'required|date|after_or_equal:today',
            'jam'      => 'required',
        ], [
            'layanan.required'  => 'Pilih layanan terlebih dahulu.',
            'tanggal.required'  => 'Tanggal booking wajib diisi.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'jam.required'      => 'Jam booking wajib diisi.',
        ]);

        $user = Auth::user();
        $layananNama = $request->layanan;
        $tanggal = $request->tanggal;
        $jam = $request->jam;

        // Validasi jam operasional 09:00–20:30
        [$h, $m] = explode(':', $jam);
        $h = (int) $h; $m = (int) $m;
        if ($h < 9 || ($h == 20 && $m > 30) || $h > 20) {
            return back()->with('error', 'Jam booking harus antara 09:00 – 20:30 WIB.');
        }

        // Ambil harga dari DB
        $layananData = Layanan::where('nama_layanan', $layananNama)->where('aktif', true)->first();
        if (!$layananData) {
            return back()->with('error', 'Layanan tidak valid.');
        }

        // Cek jadwal bentrok
        $bentrok = Antrian::where('tanggal_booking', $tanggal)
            ->where('jam_booking', $jam)
            ->where('status', '!=', 'Batal')
            ->exists();

        if ($bentrok) {
            return back()->with('error', 'Maaf, jam tersebut sudah dipesan. Silakan pilih jam lain.');
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

        Antrian::create([
            'user_id'         => $user->id,
            'nama'            => $user->nama,
            'layanan'         => $layananNama,
            'harga'           => $layananData->harga,
            'nomor_antrian'   => $nomorAntrian,
            'status'          => 'Menunggu',
            'tanggal_booking' => $tanggal,
            'jam_booking'     => $jam,
            'notif'           => true,
        ]);

        return redirect()->route('user.profil')->with('success', "Booking Berhasil! Nomor Antrian Anda: #$nomorAntrian");
    }
}
