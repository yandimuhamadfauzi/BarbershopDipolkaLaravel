@extends('layouts.admin')
@section('title', 'Manajemen Layanan')
@push('styles')
<style>
.table>:not(caption)>*>*{background-color:transparent!important;color:#f0f0f0!important;border-color:#1e1e1e!important;}
.table tbody tr{background-color:#0f0f0f!important;}
.table tbody tr:hover>*{background-color:#1a1a1a!important;}
.table-responsive{background:#0f0f0f!important;border-radius:10px;overflow:hidden;}
.form-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px;}
.emoji-pick{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
.emoji-pick button{background:#1a1a1a;border:1px solid #2a2a2a;color:#fff;border-radius:8px;padding:4px 10px;font-size:1.1rem;cursor:pointer;transition:all .2s;}
.emoji-pick button:hover{background:rgba(212,175,55,.2);border-color:#d4af37;}
</style>
@endpush
@section('content')

<div class="admin-header">
    <h4>✂️ MANAJEMEN LAYANAN</h4>
    <span class="text-muted small">{{ now()->translatedFormat('d F Y') }}</span>
</div>

<div class="row g-4">
    {{-- Form Tambah/Edit --}}
    <div class="col-lg-4">
        <div class="form-card p-4" id="formCard">
            <h6 class="text-gold fw-bold mb-3" id="formTitle">➕ Tambah Layanan Baru</h6>
            <form method="POST" action="{{ route('admin.layanan.simpan') }}" id="formLayanan">
                @csrf
                <input type="hidden" name="id_layanan" id="id_layanan" value="0">
                <div class="mb-3">
                    <label class="form-label">Emoji / Ikon</label>
                    <input type="text" name="emoji" id="emoji" class="form-control" value="✂️" maxlength="4"
                           style="font-size:1.4rem;width:70px;text-align:center">
                    <div class="emoji-pick mt-2">
                        @foreach(['✂️','🪒','🎨','💫','💆','🧴','👑','🔥','💎','🪙'] as $em)
                        <button type="button" onclick="document.getElementById('emoji').value='{{ $em }}'">{{ $em }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_layanan" id="nama_layanan" class="form-control" placeholder="cth: Cukur Rambut" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga" id="harga_input" class="form-control" placeholder="25000" min="1000" step="500" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" id="deskripsi" class="form-control" placeholder="Penjelasan singkat layanan">
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="aktif" id="aktif" checked>
                        <label class="form-check-label text-muted" for="aktif">Layanan Aktif</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2">💾 Simpan Layanan</button>
                <button type="button" onclick="resetForm()" class="btn btn-outline-secondary w-100 rounded-pill mt-2 btn-sm">Reset Form</button>
            </form>
        </div>
    </div>

    {{-- Daftar Layanan --}}
    <div class="col-lg-8">
        <div class="card shadow-lg p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-gold mb-1">Daftar Layanan</h5>
                    <small class="text-muted">{{ $layanan->count() }} layanan terdaftar</small>
                </div>
            </div>

            @if($layanan->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($layanan as $l)
                    <tr class="{{ !$l->aktif ? 'opacity-50' : '' }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:1.4rem">{{ $l->emoji }}</span>
                                <div>
                                    <div class="fw-semibold text-white">{{ $l->nama_layanan }}</div>
                                    @if($l->deskripsi)<small class="text-muted">{{ $l->deskripsi }}</small>@endif
                                </div>
                            </div>
                        </td>
                        <td class="text-end text-gold fw-bold">{{ $l->harga_format }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.layanan.toggle', $l->id) }}"
                               class="badge {{ $l->aktif ? 'bg-success' : 'bg-secondary' }} rounded-pill text-decoration-none">
                                {{ $l->aktif ? '✅ Aktif' : '⛔ Nonaktif' }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-outline-warning btn-sm rounded-pill px-2"
                                        onclick="editLayanan({{ $l->id }}, '{{ addslashes($l->nama_layanan) }}', '{{ $l->emoji }}', {{ $l->harga }}, '{{ addslashes($l->deskripsi ?? '') }}', {{ $l->aktif ? 1 : 0 }})"
                                        title="Edit">✏️</button>
                                <form action="{{ route('admin.layanan.hapus', $l->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus layanan {{ $l->nama_layanan }}?')" class="d-inline">
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
                <div style="font-size:3rem;opacity:.2">✂️</div>
                <p class="text-muted mt-3">Belum ada layanan. Tambahkan layanan pertama!</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editLayanan(id, nama, emoji, harga, deskripsi, aktif) {
    document.getElementById('id_layanan').value  = id;
    document.getElementById('emoji').value        = emoji;
    document.getElementById('nama_layanan').value = nama;
    document.getElementById('harga_input').value  = harga;
    document.getElementById('deskripsi').value    = deskripsi;
    document.getElementById('aktif').checked      = aktif == 1;
    document.getElementById('formTitle').textContent = '✏️ Edit Layanan';
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('id_layanan').value  = '0';
    document.getElementById('emoji').value        = '✂️';
    document.getElementById('nama_layanan').value = '';
    document.getElementById('harga_input').value  = '';
    document.getElementById('deskripsi').value    = '';
    document.getElementById('aktif').checked      = true;
    document.getElementById('formTitle').textContent = '➕ Tambah Layanan Baru';
}
</script>
@endpush
