@extends('layouts.admin')
@section('title', 'Manajemen Barber')
@push('styles')
<style>
.table>:not(caption)>*>*{background-color:transparent!important;color:#f0f0f0!important;border-color:#1e1e1e!important;}
.table tbody tr{background-color:#0f0f0f!important;}
.table tbody tr:hover>*{background-color:#1a1a1a!important;}
.table-responsive{background:#0f0f0f!important;border-radius:10px;overflow:hidden;}
.form-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px;}
</style>
@endpush
@section('content')

<div class="admin-header">
    <h4>🧑‍💼 MANAJEMEN BARBER</h4>
    <span class="text-muted small">{{ now()->translatedFormat('d F Y') }}</span>
</div>

<div class="row g-4">
    {{-- Form Tambah/Edit --}}
    <div class="col-lg-4">
        <div class="form-card p-4" id="formCard">
            <h6 class="text-gold fw-bold mb-3" id="formTitle">➕ Tambah Barber Baru</h6>
            <form method="POST" action="{{ route('admin.kapster.simpan') }}" id="formKapster">
                @csrf
                <input type="hidden" name="id_kapster" id="id_kapster" value="0">
                <div class="mb-3">
                    <label class="form-label">Nama Barber <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control" placeholder="cth: Budi" required>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="aktif" id="aktif" checked>
                        <label class="form-check-label text-muted" for="aktif">Barber Aktif</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2">💾 Simpan Barber</button>
                <button type="button" onclick="resetForm()" class="btn btn-outline-secondary w-100 rounded-pill mt-2 btn-sm">Reset Form</button>
            </form>
        </div>
    </div>

    {{-- Daftar Barber --}}
    <div class="col-lg-8">
        <div class="card shadow-lg p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-gold mb-1">Daftar Barber</h5>
                    <small class="text-muted">{{ $kapsters->count() }} barber terdaftar</small>
                </div>
            </div>

            @if($kapsters->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Barber</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($kapsters as $k)
                    <tr class="{{ !$k->aktif ? 'opacity-50' : '' }}">
                        <td>
                            <div class="fw-semibold text-white">{{ $k->nama }}</div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.kapster.toggle', $k->id) }}"
                               class="badge {{ $k->aktif ? 'bg-success' : 'bg-secondary' }} rounded-pill text-decoration-none">
                                {{ $k->aktif ? '✅ Aktif' : '⛔ Nonaktif' }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-outline-warning btn-sm rounded-pill px-2"
                                        onclick="editKapster({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ $k->aktif ? 1 : 0 }})"
                                        title="Edit">✏️</button>
                                <form action="{{ route('admin.kapster.hapus', $k->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus barber {{ $k->nama }}?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Hapus">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div style="font-size:3rem;opacity:.2">🧑‍💼</div>
                <p class="text-muted mt-3">Belum ada barber. Tambahkan barber pertama!</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editKapster(id, nama, aktif) {
    document.getElementById('id_kapster').value = id;
    document.getElementById('nama').value       = nama;
    document.getElementById('aktif').checked    = aktif == 1;
    document.getElementById('formTitle').textContent = '✏️ Edit Barber';
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('id_kapster').value = '0';
    document.getElementById('nama').value       = '';
    document.getElementById('aktif').checked    = true;
    document.getElementById('formTitle').textContent = '➕ Tambah Barber Baru';
}
</script>
@endpush
