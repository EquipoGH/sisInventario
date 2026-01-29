<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    {{-- DNI (nuevo) --}}
    <div class="form-group mb-3">
        <label for="dni_usuario" class="gi-label">
            <i class="fas fa-id-card mr-2" style="color:#7c3aed;"></i>
            DNI
        </label>

        <input
            id="dni_usuario"
            name="dni_usuario"
            type="text"
            class="gi-input"
            value="{{ old('dni_usuario', $user->dni_usuario ?? '') }}"
            maxlength="8"
            required
            placeholder="12345678"
            inputmode="numeric"
            autocomplete="off"
        />

        @error('dni_usuario')
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
        @enderror

        <div class="gi-help">Solo números, 8 dígitos.</div>
    </div>

    {{-- Nombre --}}
    <div class="form-group mb-3">
        <label for="name" class="gi-label">
            <i class="fas fa-user mr-2" style="color:#7c3aed;"></i>
            Nombre completo
        </label>

        <input
            id="name"
            name="name"
            type="text"
            class="gi-input"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
            autocomplete="name"
            placeholder="Tu nombre"
        />

        @error('name')
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
        @enderror
    </div>

    {{-- Email --}}
    <div class="form-group mb-3">
        <label for="email" class="gi-label">
            <i class="fas fa-envelope mr-2" style="color:#7c3aed;"></i>
            Correo electrónico
        </label>

        <input
            id="email"
            name="email"
            type="email"
            class="gi-input"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username"
            placeholder="correo@ejemplo.com"
        />

        @error('email')
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="gi-alert gi-alert-warn mt-3">
                <div style="font-weight:900;">
                    <i class="fas fa-circle-exclamation mr-2"></i>
                    Tu correo no está verificado.
                </div>

                <button type="submit" form="send-verification"
                        style="border:0;background:transparent;color:#4f46e5;font-weight:900;margin-top:6px;">
                    <i class="fas fa-paper-plane mr-1"></i>
                    Reenviar correo de verificación
                </button>
            </div>
        @endif
    </div>

    <button type="submit" class="gi-btn gi-btn-primary">
        <i class="fas fa-save mr-2"></i>
        Guardar cambios
    </button>
</form>
