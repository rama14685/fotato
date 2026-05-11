<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Saya | Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin:0; background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); min-height:100vh; color:#e2e8f0; }
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(ellipse at 20% 20%, rgba(99,102,241,.12) 0%, transparent 50%), radial-gradient(ellipse at 80% 80%, rgba(139,92,246,.1) 0%, transparent 50%); pointer-events:none; z-index:0; }

        /* ── Glass ─────────────── */
        .glass  { background:rgba(255,255,255,.06); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,.1); border-radius:16px; position:relative; z-index:1; }
        .glass2 { background:rgba(255,255,255,.04); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.08); border-radius:14px; position:relative; z-index:1; }

        /* ── Stat Card ──────────── */
        .stat-card { text-align:center; padding:20px; }
        .stat-num  { font-size:2.5rem; font-weight:800; background: linear-gradient(135deg,#a5b4fc,#e879f9); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; }

        /* ── Photo Card ─────────── */
        .photo-card { position:relative; border-radius:12px; overflow:hidden; aspect-ratio:.75; background:#0a0a1a; cursor:pointer; transition: transform .25s, box-shadow .25s; }
        .photo-card:hover { transform:translateY(-4px) scale(1.02); box-shadow:0 16px 40px rgba(0,0,0,.5); }
        .photo-card img { width:100%; height:100%; object-fit:cover; display:block; }
        .photo-card .score-badge { position:absolute; top:8px; right:8px; font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:20px; letter-spacing:.04em; }
        .badge-excellent { background:rgba(16,185,129,.9); color:#fff; }
        .badge-good      { background:rgba(59,130,246,.9);  color:#fff; }
        .badge-fair      { background:rgba(245,158,11,.9);  color:#fff; }
        .photo-card .card-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,.8) 0%, transparent 50%); opacity:0; transition:opacity .25s; display:flex; align-items:flex-end; padding:12px; }
        .photo-card:hover .card-overlay { opacity:1; }

        /* ── Album Section ──────── */
        .album-section { margin-bottom:40px; }
        .album-header { display:flex; align-items:center; gap:12px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,.08); }
        .album-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; background:linear-gradient(135deg,rgba(99,102,241,.4),rgba(139,92,246,.3)); }

        /* ── Empty state ────────── */
        .empty-state { text-align:center; padding:60px 20px; }
        .empty-icon  { font-size:4rem; margin-bottom:16px; filter:drop-shadow(0 0 20px rgba(139,92,246,.5)); }

        /* ── Buttons ────────────── */
        .btn { display:inline-flex; align-items:center; gap:8px; border-radius:10px; padding:10px 20px; font-weight:600; font-size:.875rem; cursor:pointer; transition:all .2s; border:none; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; box-shadow:0 4px 15px rgba(99,102,241,.4); }
        .btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(99,102,241,.5); }
        .btn-ghost { background:rgba(255,255,255,.08); color:#cbd5e1; border:1px solid rgba(255,255,255,.12); }
        .btn-ghost:hover { background:rgba(255,255,255,.14); color:#fff; }

        /* ── Spinner ────────────── */
        @keyframes spin { to { transform:rotate(360deg) } }
        .spinner { animation:spin 1s linear infinite; }

        /* ── Photo modal ─────────── */
        #photoModal { position:fixed; inset:0; background:rgba(0,0,0,.85); backdrop-filter:blur(8px); z-index:1000; display:none; align-items:center; justify-content:center; padding:20px; }
        #photoModal.open { display:flex; }
        #modalImg { max-width:90vw; max-height:85vh; object-fit:contain; border-radius:12px; box-shadow:0 0 60px rgba(0,0,0,.8); }
        #modalClose { position:fixed; top:20px; right:24px; background:rgba(255,255,255,.1); border:none; color:#fff; width:40px; height:40px; border-radius:50%; font-size:1.5rem; cursor:pointer; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(6px); transition:.2s; }
        #modalClose:hover { background:rgba(255,255,255,.2); }
    </style>
</head>
<body>

{{-- NAV --}}
<nav style="background:rgba(0,0,0,.35); backdrop-filter:blur(20px); border-bottom:1px solid rgba(255,255,255,.07); position:sticky; top:0; z-index:100;">
    <div class="max-w-7xl mx-auto px-5 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-2xl">📸</span>
            <span class="text-xl font-bold text-white">Fotlist</span>
            <span class="hidden sm:inline text-xs font-medium px-3 py-1 rounded-full ml-2" style="background:rgba(99,102,241,.25); color:#a5b4fc; border:1px solid rgba(99,102,241,.3);">Buyer</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('buyer.register-face') }}" class="btn btn-ghost text-xs py-2 px-3">
                🔄 Perbarui Wajah
            </a>
            <span class="text-slate-400 text-sm hidden md:block">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost text-xs py-2 px-3">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-5 py-10" style="position:relative; z-index:1;">

    {{-- Hero Header --}}
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">
            👋 Hai, <span style="background:linear-gradient(135deg,#a5b4fc,#e879f9); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">{{ Auth::user()->name }}</span>
        </h1>
        <p class="text-slate-400 text-lg">Ini semua foto yang berisi wajah Anda, ditemukan dari seluruh album</p>
    </div>

    {{-- Error --}}
    @if(isset($error))
        <div class="glass p-4 mb-6 text-center" style="border-left:4px solid #ef4444;">
            <p class="text-red-400">⚠️ {{ $error }}</p>
        </div>
    @endif

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div class="glass stat-card">
            <div class="stat-num">{{ $totalMatched }}</div>
            <p class="text-slate-400 text-sm mt-2 font-medium">Foto Cocok</p>
        </div>
        <div class="glass stat-card">
            <div class="stat-num">{{ $groupedByAlbum->count() }}</div>
            <p class="text-slate-400 text-sm mt-2 font-medium">Album</p>
        </div>
        <div class="glass stat-card">
            <div class="stat-num">{{ $totalChecked }}</div>
            <p class="text-slate-400 text-sm mt-2 font-medium">Total Wajah Dicek</p>
        </div>
        <div class="glass stat-card">
            <div class="stat-num" style="font-size:1.8rem;">
                {{ $totalChecked > 0 ? round(($totalMatched / $totalChecked) * 100, 1) : 0 }}%
            </div>
            <p class="text-slate-400 text-sm mt-2 font-medium">Match Rate</p>
        </div>
    </div>

    {{-- Session messages --}}
    @if(session('info'))
        <div class="glass p-4 mb-6 text-center" style="border-left:4px solid #6366f1;">
            <p class="text-purple-300">ℹ️ {{ session('info') }}</p>
        </div>
    @endif

    {{-- Content --}}
    @if($groupedByAlbum->isEmpty())
        {{-- Empty State --}}
        <div class="glass py-16">
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h2 class="text-2xl font-bold text-white mb-3">Belum Ada Foto Cocok</h2>
                <p class="text-slate-400 max-w-md mx-auto mb-6">
                    Kami belum menemukan foto yang mengandung wajah Anda. Kemungkinan admin belum mengupload foto dari event Anda, atau wajah Anda perlu didaftarkan ulang.
                </p>
                <div class="flex gap-4 justify-center flex-wrap">
                    <a href="{{ route('buyer.register-face') }}" class="btn btn-primary">
                        🔄 Daftar Ulang Wajah
                    </a>
                    <a href="{{ route('albums.index') }}" class="btn btn-ghost">
                        📂 Lihat Semua Album
                    </a>
                </div>

                <div class="mt-10 p-5 rounded-xl mx-auto max-w-sm" style="background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.2);">
                    <p class="text-sm text-slate-400">
                        <strong class="text-purple-400">💡 Tips:</strong>
                        Pastikan wajah Anda terdaftar dengan baik dan pencahayaan cukup saat pendaftaran.
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Matched Photos grouped by Album --}}
        @foreach($groupedByAlbum as $group)
            @php $album = $group['album']; $photos = $group['photos']; @endphp
            <div class="album-section">
                {{-- Album Header --}}
                <div class="album-header">
                    <div class="album-icon">📁</div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-white font-bold text-lg truncate">{{ $album->title ?? 'Album' }}</h2>
                        <div class="flex items-center gap-3 text-xs text-slate-400 mt-1 flex-wrap">
                            @if($album->location)
                                <span>📍 {{ $album->location }}</span>
                            @endif
                            @if($album->event_date)
                                <span>📅 {{ \Carbon\Carbon::parse($album->event_date)->format('d M Y') }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,.25); color:#a5b4fc;">
                                {{ $photos->count() }} foto cocok
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('albums.show', $album->id) }}" class="btn btn-ghost text-xs py-2 px-3 hidden sm:flex">
                        Lihat Album →
                    </a>
                </div>

                {{-- Photo Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach($photos as $photo)
                        @php
                            $score = $photo->match_score ?? 0;
                            $badgeClass = $score >= 80 ? 'badge-excellent' : ($score >= 65 ? 'badge-good' : 'badge-fair');
                            $displayPath = $photo->watermark_path ?? $photo->original_path;
                            $imgUrl = $displayPath ? asset('storage/' . $displayPath) : 'https://via.placeholder.com/300x400?text=No+Image';
                        @endphp
                        <div class="photo-card" onclick="openModal('{{ $imgUrl }}', '{{ addslashes($album->title) }}', {{ $photo->id }})">
                            <img src="{{ $imgUrl }}" alt="Foto {{ $photo->id }}" loading="lazy">
                            <div class="score-badge {{ $badgeClass }}">
                                {{ $score }}% cocok
                            </div>
                            <div class="card-overlay">
                                <div class="w-full">
                                    <p class="text-white text-xs font-semibold mb-1">📸 Foto #{{ $photo->id }}</p>
                                    <p class="text-slate-300 text-xs">Klik untuk perbesar</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Footer info --}}
        <div class="glass2 p-5 text-center mt-6">
            <p class="text-slate-400 text-sm">
                Menampilkan <strong class="text-white">{{ $totalMatched }}</strong> foto yang cocok dari
                <strong class="text-white">{{ $totalChecked }}</strong> wajah yang dicek di semua album.
                Threshold pencocokan: <strong class="text-purple-400">Euclidean &lt; 0.5</strong>
            </p>
        </div>
    @endif

</div>

{{-- Photo Modal --}}
<div id="photoModal">
    <button id="modalClose" onclick="closeModal()" title="Tutup">✕</button>
    <img id="modalImg" src="" alt="Preview Foto">
</div>

<script>
function openModal(src, albumTitle, photoId) {
    document.getElementById('modalImg').src = src;
    document.getElementById('photoModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('photoModal').classList.remove('open');
    document.getElementById('modalImg').src = '';
    document.body.style.overflow = '';
}

// Close on backdrop click
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>
