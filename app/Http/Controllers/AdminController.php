<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $today     = now()->toDateString();
        $filterTgl = $request->get('tgl', $today);

        $stats = [
            'total'     => Antrian::where('tanggal_booking', $filterTgl)->count(),
            'menunggu'  => Antrian::where('tanggal_booking', $filterTgl)->where('status','Menunggu')->count(),
            'dipanggil' => Antrian::where('tanggal_booking', $filterTgl)->where('status','Dipanggil')->count(),
            'revenue'   => Antrian::where('tanggal_booking', $filterTgl)->where('status','Selesai')->sum('harga'),
        ];

        $antrian = Antrian::with('user')
            ->where('tanggal_booking', $filterTgl)
            ->orderByRaw("FIELD(status,'Dipanggil','Menunggu','Selesai','Batal')")
            ->orderBy('jam_booking')
            ->get();

        $dates = Antrian::select('tanggal_booking')
            ->distinct()->orderByDesc('tanggal_booking')->limit(30)->pluck('tanggal_booking');

        return view('admin.dashboard', compact('stats','antrian','filterTgl','dates'));
    }

    public function panggil($id, Request $request)
    {
        Antrian::findOrFail($id)->update(['status' => 'Dipanggil', 'notif' => false]);
        return redirect()->route('admin.dashboard', ['tgl' => $request->get('tgl')])->with('success','Antrian dipanggil.');
    }

    public function selesai($id, Request $request)
    {
        Antrian::findOrFail($id)->update(['status' => 'Selesai', 'notif' => true]);
        return redirect()->route('admin.dashboard', ['tgl' => $request->get('tgl')])->with('success','Antrian selesai.');
    }

    public function batalAntrian($id, Request $request)
    {
        Antrian::findOrFail($id)->update(['status' => 'Batal', 'notif' => true]);
        return redirect()->route('admin.dashboard', ['tgl' => $request->get('tgl')])->with('success','Antrian dibatalkan.');
    }

    public function hapusAntrian($id, Request $request)
    {
        Antrian::findOrFail($id)->delete();
        return redirect()->route('admin.dashboard', ['tgl' => $request->get('tgl')])->with('success','Antrian dihapus.');
    }

    // ── LAYANAN ──────────────────────────────────────────────────
    public function layanan()
    {
        $layanan = Layanan::orderByDesc('aktif')->orderBy('harga')->get();
        return view('admin.layanan', compact('layanan'));
    }

    public function simpanLayanan(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:100',
            'harga'        => 'required|integer|min:1000',
        ]);

        $data = [
            'nama_layanan' => $request->nama_layanan,
            'emoji'        => $request->emoji ?: '✂️',
            'harga'        => $request->harga,
            'deskripsi'    => $request->deskripsi,
            'aktif'        => $request->has('aktif'),
        ];

        if ($request->id_layanan) {
            Layanan::findOrFail($request->id_layanan)->update($data);
            $msg = 'Layanan berhasil diperbarui!';
        } else {
            Layanan::create($data);
            $msg = 'Layanan baru berhasil ditambahkan!';
        }

        return redirect()->route('admin.layanan')->with('success', $msg);
    }

    public function hapusLayanan($id)
    {
        $layanan = Layanan::findOrFail($id);
        $ada = Antrian::where('layanan', $layanan->nama_layanan)->whereIn('status',['Menunggu','Dipanggil'])->exists();
        if ($ada) return redirect()->route('admin.layanan')->with('error','Tidak bisa menghapus layanan yang masih ada antrian aktif!');
        $layanan->delete();
        return redirect()->route('admin.layanan')->with('success','Layanan berhasil dihapus.');
    }

    public function toggleLayanan($id)
    {
        $l = Layanan::findOrFail($id);
        $l->update(['aktif' => !$l->aktif]);
        return redirect()->route('admin.layanan')->with('success','Status layanan diperbarui.');
    }

    // ── USER ─────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $search = trim($request->get('q', ''));

        $users = User::where('is_admin', false)
            ->when($search, fn($q) => $q->where('nama','like',"%$search%")->orWhere('email','like',"%$search%"))
            ->withCount([
                'antrian as antrian_count',
                'antrian as selesai_count' => fn($q) => $q->where('status','Selesai'),
                'antrian as aktif_count'   => fn($q) => $q->whereIn('status',['Menunggu','Dipanggil']),
            ])
            ->addSelect(['last_booking' => \App\Models\Antrian::selectRaw('MAX(tanggal_booking)')->whereColumn('user_id', 'users.id')])
            ->orderByDesc('id')
            ->paginate(15);

        $totalAll      = User::where('is_admin', false)->count();
        $totalAktif    = Antrian::whereIn('status',['Menunggu','Dipanggil'])->distinct('user_id')->count('user_id');
        $totalBookingAll = Antrian::count();

        return view('admin.users', compact('users','search','totalAll','totalAktif','totalBookingAll'));
    }

    public function hapusUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->is_admin) return redirect()->route('admin.users')->with('error','Tidak dapat menghapus akun admin.');
        $user->delete();
        return redirect()->route('admin.users')->with('success','User berhasil dihapus.');
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate(['pass_baru' => 'required|min:6']);
        User::findOrFail($id)->update(['password' => Hash::make($request->pass_baru)]);
        return redirect()->route('admin.users')->with('success','Password user berhasil direset!');
    }

    // ── LAPORAN ──────────────────────────────────────────────────
    public function laporan(Request $request)
    {
        $bulanFilter = $request->get('bulan', now()->format('Y-m'));
        [$tahun, $bulan] = explode('-', $bulanFilter);

        $dataHarian = Antrian::selectRaw("
                tanggal_booking,
                COUNT(*) as total_antrian,
                SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status='Batal' THEN 1 ELSE 0 END) as batal,
                COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as pendapatan
            ")
            ->whereYear('tanggal_booking', $tahun)->whereMonth('tanggal_booking', $bulan)
            ->groupBy('tanggal_booking')->orderBy('tanggal_booking')->get();

        $ringkasan = Antrian::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status='Batal' THEN 1 ELSE 0 END) as batal,
                COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as total_pendapatan
            ")
            ->whereYear('tanggal_booking', $tahun)->whereMonth('tanggal_booking', $bulan)
            ->first();

        $layananPopuler = Antrian::selectRaw("layanan, COUNT(*) as jumlah, COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as pendapatan")
            ->whereYear('tanggal_booking', $tahun)->whereMonth('tanggal_booking', $bulan)
            ->where('status','Selesai')
            ->groupBy('layanan')->orderByDesc('jumlah')->get();

        return view('admin.laporan', compact('dataHarian','ringkasan','layananPopuler','bulanFilter'));
    }
}
