<form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <label class="gi-label">Foto de perfil</label>

    <div class="mb-3" style="display:flex; gap:12px; align-items:center;">
        @php $p = Auth::user()->profile_photo_path; @endphp

        @if($p)
            <img src="{{ Storage::url($p) }}" alt="Foto actual"
                 style="width:64px;height:64px;border-radius:999px;object-fit:cover;border:2px solid rgba(148,163,184,.65);">
        @else
            <div style="width:64px;height:64px;border-radius:999px;display:flex;align-items:center;justify-content:center;border:2px solid rgba(148,163,184,.65);background:rgba(255,255,255,.9);">
                <i class="fas fa-user text-muted"></i>
            </div>
        @endif

        <div style="flex:1;">
            <input type="file" name="photo" class="gi-input" accept="image/*">
            <div class="gi-help">PNG/JPG/WebP. Máx 2MB.</div>
            @error('photo') <div class="gi-help" style="color:#b91c1c;">{{ $message }}</div> @enderror
        </div>
    </div>

    <button class="gi-btn gi-btn-primary" type="submit">
        Guardar foto
    </button>
</form>
