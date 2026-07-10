<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function profil(Request $request)
    {
        $user = Auth::user();

        // Fallback untuk localhost: Ambil status dari query parameter redirect/fetch Midtrans JS
        if ($request->has('order_id') && $request->has('transaction_status')) {
            $order_id = $request->query('order_id');
            $transaction_status = $request->query('transaction_status');
            
            $order_parts = explode('-', $order_id);
            if (count($order_parts) === 2 && $order_parts[0] === 'ANTRIAN') {
                $antrian_id = $order_parts[1];
                $antrian_check = Antrian::find($antrian_id);
                if ($antrian_check && $antrian_check->user_id == $user->id && $antrian_check->payment_status !== 'paid') {
                    if (in_array($transaction_status, ['capture', 'settlement'])) {
                        $antrian_check->update(['payment_status' => 'paid']);
                    } else if (in_array($transaction_status, ['deny', 'cancel', 'expire'])) {
                        $antrian_check->update(['payment_status' => 'failed', 'status' => 'Batal']);
                    }
                }
            }
            return redirect()->route('user.profil')->with('success', 'Pembayaran berhasil dikonfirmasi.');
        }

        $antrian = Antrian::where('user_id', $user->id)
            ->orderByDesc('tanggal_booking')
            ->orderByDesc('jam_booking')
            ->get();

        $notifCount = Antrian::where('user_id', $user->id)
            ->where('notif', true)
            ->whereIn('status', ['Dipanggil', 'Selesai', 'Batal'])
            ->count();

        return view('user.profil', compact('user', 'antrian', 'notifCount'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama'        => 'required|string|max:100',
            'pass_baru'   => ['nullable', Password::min(6)],
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'nama.required'    => 'Nama tidak boleh kosong.',
            'foto_profil.image' => 'File harus berupa gambar.',
            'foto_profil.max'  => 'Ukuran foto maksimal 2MB.',
        ]);

        $data = ['nama' => $request->nama];

        if ($request->filled('pass_baru')) {
            $data['password'] = Hash::make($request->pass_baru);
        }

        if ($request->hasFile('foto_profil')) {
            $file     = $request->file('foto_profil');
            $filename = 'profil_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/profil'), $filename);

            // Hapus foto lama
            if ($user->foto && file_exists(public_path('img/profil/' . $user->foto))) {
                unlink(public_path('img/profil/' . $user->foto));
            }

            $data['foto'] = $filename;
        }

        $user->update($data);

        return redirect()->route('user.profil')->with('success', 'Profil berhasil diperbarui!');
    }

    public function hapusAkun(Request $request)
    {
        $user = Auth::user();

        // Optional: you can delete their avatar file if it exists
        if ($user->foto && file_exists(public_path('img/profil/' . $user->foto))) {
            unlink(public_path('img/profil/' . $user->foto));
        }

        // Delete user's queues and user account
        Antrian::where('user_id', $user->id)->delete();
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Akun Anda telah berhasil dihapus secara permanen.');
    }

    public function batalAntrian(Request $request, $id)
    {
        $user = Auth::user();

        $antrian = Antrian::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'Menunggu')
            ->first();

        if (!$antrian) {
            return redirect()->route('user.profil')->with('error', 'Gagal: Antrian sudah diproses atau bukan milik Anda.');
        }

        $antrian->update(['status' => 'Batal', 'notif' => true]);

        return redirect()->route('user.profil')->with('success', 'Booking berhasil dibatalkan.');
    }

    public function clearNotif()
    {
        $user = Auth::user();
        Antrian::where('user_id', $user->id)
            ->where('notif', true)
            ->update(['notif' => false]);

        return response()->json(['ok' => true]);
    }

    public function cekNotif()
    {
        $user = Auth::user();
        $notif = Antrian::where('user_id', $user->id)
            ->where('tanggal_booking', now()->toDateString())
            ->where('status', 'Dipanggil')
            ->where('notif', false)
            ->first();

        if ($notif) {
            $notif->update(['notif' => true]);
            return response()->json(['status_ada' => 'ya', 'nomor_antrian' => $notif->nomor_antrian]);
        }
        return response()->json(['status_ada' => 'tidak']);
    }
}
