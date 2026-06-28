@extends('layouts.admin')
@section('title', 'Manajemen User')
@push('styles')
<style>
.table>:not(caption)>*>*{background-color:transparent!important;color:#f0f0f0!important;border-color:#1e1e1e!important;}
.table tbody tr{background-color:#0f0f0f!important;}
.table tbody tr:hover>*{background-color:#1a1a1a!important;}
.table-responsive{background:#0f0f0f!important;border-radius:10px;overflow:hidden;}
.stat-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px!important;}
.stat-card small{color:#888;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;}
.stat-card h3{font-family:'Oswald',sans-serif;font-size:2rem;margin:6px 0 0;color:#d4af37;}
.search-bar{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:10px;padding:14px 18px;}
.page-item.active .page-link{background:#d4af37!important;border-color:#d4af37!important;color:#000!important;}
.page-link{background:#111!important;border-color:#2a2a2a!important;color:#888!important;}
.page-link:hover{background:#1a1a1a!important;color:#d4af37!important;}
</style>
@endpush
@section('content')

<div class="admin-header">
    <h4>👥 MANAJEMEN USER</h4>
    <span class="text-muted small">{{ now()->translatedFormat('d F Y') }}</span>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="stat-card p-3">
            <small>Total Pelanggan</small>
            <h3>{{ $totalAll }} <small style="font-size:.55em;color:#555">akun</small></h3>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card p-3">
            <small>Sedang Antri</small>
            <h3 style="color:#f0c030">{{ $totalAktif }} <small style="font-size:.55em;color:#555">user</small></h3>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="stat-card p-3">
            <small>Total Semua Booking</small>
            <h3 style="color:#2ecc71">{{ $totalBookingAll }} <small style="font-size:.55em;color:#555">transaksi</small></h3>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="search-bar mb-4">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-3">
        <div class="flex-grow-1" style="min-width:220px">
            <input type="text" name="q" class="form-control" placeholder="🔍 Cari nama atau email..."
                   value="{{ $search }}">
        </div>
        <button type="submit" class="btn btn-gold rounded-pill px-4 btn-sm">Cari</button>
        @if($search)
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3">Reset</a>
        @endif
        <span class="ms-auto text-muted small">{{ $users->total() }} user ditemukan</span>
    </form>
</div>

{{-- Tabel --}}
<div class="card shadow-lg p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-gold mb-0">Daftar Pelanggan Terdaftar</h5>
        <small class="text-muted">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>Pelanggan</th>
                    <th class="text-center">Total Booking</th>
                    <th class="text-center">Selesai</th>
                    <th class="text-center">Antrian Aktif</th>
                    <th>Booking Terakhir</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
            <tr>
                <td><span class="text-muted small">#{{ $u->id }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $u->foto_url }}" width="38" height="38" class="rounded-circle" style="object-fit:cover;border:1.5px solid #2a2a2a">
                        <div>
                            <div class="fw-semibold text-white">{{ $u->nama }}</div>
                            <small class="text-muted">{{ $u->email }}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center"><span class="fw-bold text-white">{{ $u->antrian_count }}</span></td>
                <td class="text-center"><span class="badge bg-success rounded-pill">{{ $u->selesai_count }}</span></td>
                <td class="text-center">
                    @if($u->aktif_count > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $u->aktif_count }} aktif</span>
                    @else
                    <span class="text-muted small">—</span>
                    @endif
                </td>
                <td>
                    @if($u->last_booking)
                    <span class="small text-muted">{{ \Carbon\Carbon::parse($u->last_booking)->translatedFormat('d M Y') }}</span>
                    @else
                    <span class="text-muted small">Belum pernah</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-outline-info btn-sm rounded-pill px-2"
                                data-bs-toggle="modal" data-bs-target="#resetPassModal"
                                data-id="{{ $u->id }}" data-nama="{{ $u->nama }}"
                                title="Reset password">🔑</button>
                        <form action="{{ route('admin.users.hapus', $u->id) }}" method="POST"
                              onsubmit="return confirm('Hapus user {{ $u->nama }}?\nSemua riwayat bookingnya juga akan dihapus!')" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Hapus">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div style="font-size:2.5rem;opacity:.2">👥</div>
                    <p class="text-muted mt-3 mb-0">{{ $search ? 'Tidak ada user yang cocok.' : 'Belum ada pelanggan.' }}</p>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="mt-4">{{ $users->appends(['q' => $search])->links() }}</div>
    @endif
</div>

{{-- Modal Reset Password --}}
<div class="modal fade" id="resetPassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="fw-bold text-gold mb-0">🔑 Reset Password</h5><small class="text-muted" id="modalUserNama">—</small></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formResetPass">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="text" name="pass_baru" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="off">
                        <small class="text-muted">Password akan langsung diperbarui di akun user.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4 fw-bold">🔑 Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('resetPassModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalUserNama').textContent = 'User: ' + btn.dataset.nama;
    document.getElementById('formResetPass').action = '/admin/users/' + btn.dataset.id + '/reset-pass';
});
</script>
@endpush
