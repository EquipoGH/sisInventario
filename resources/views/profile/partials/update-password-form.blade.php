<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group mb-3">
        <label for="update_password_current_password" class="gi-label">
            <i class="fas fa-lock mr-2" style="color:#7c3aed;"></i>
            Contraseña actual
        </label>

        <div style="position:relative;">
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="gi-input"
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <button type="button" class="gi-eye-btn" onclick="togglePassword('update_password_current_password')">
                <i class="fas fa-eye" id="update_password_current_password-eye"></i>
            </button>
        </div>

        @if ($errors->updatePassword->get('current_password'))
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $errors->updatePassword->first('current_password') }}
            </p>
        @endif
    </div>

    <div class="form-group mb-3">
        <label for="update_password_password" class="gi-label">
            <i class="fas fa-key mr-2" style="color:#7c3aed;"></i>
            Nueva contraseña
        </label>

        <div style="position:relative;">
            <input
                id="update_password_password"
                name="password"
                type="password"
                class="gi-input"
                autocomplete="new-password"
                placeholder="••••••••"
            />
            <button type="button" class="gi-eye-btn" onclick="togglePassword('update_password_password')">
                <i class="fas fa-eye" id="update_password_password-eye"></i>
            </button>
        </div>

        @if ($errors->updatePassword->get('password'))
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $errors->updatePassword->first('password') }}
            </p>
        @endif
    </div>

    <div class="form-group mb-4">
        <label for="update_password_password_confirmation" class="gi-label">
            <i class="fas fa-lock mr-2" style="color:#7c3aed;"></i>
            Confirmar nueva contraseña
        </label>

        <div style="position:relative;">
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="gi-input"
                autocomplete="new-password"
                placeholder="••••••••"
            />
            <button type="button" class="gi-eye-btn" onclick="togglePassword('update_password_password_confirmation')">
                <i class="fas fa-eye" id="update_password_password_confirmation-eye"></i>
            </button>
        </div>

        @if ($errors->updatePassword->get('password_confirmation'))
            <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $errors->updatePassword->first('password_confirmation') }}
            </p>
        @endif
    </div>

    <button type="submit" class="gi-btn gi-btn-primary">
        <i class="fas fa-shield-halved mr-2"></i>
        Guardar contraseña
    </button>
</form>
