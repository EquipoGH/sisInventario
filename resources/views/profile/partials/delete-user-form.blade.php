<p class="mb-3" style="color:#991b1b;font-weight:900;font-size:13px;">
    Una vez eliminada tu cuenta, todos tus datos se borrarán permanentemente.
</p>

<button type="button" class="gi-btn gi-btn-danger" onclick="giToggleBox('gi-delete-box')">
    <i class="fas fa-trash mr-2"></i>
    Eliminar mi cuenta
</button>

<div id="gi-delete-box" class="d-none mt-3">
    <div class="gi-alert gi-alert-danger mb-3">
        <i class="fas fa-circle-exclamation mr-2"></i>
        Confirma tu contraseña para continuar.
    </div>

    <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <div class="form-group mb-3">
            <label for="password_delete" class="gi-label">
                <i class="fas fa-lock mr-2" style="color:#dc2626;"></i>
                Contraseña
            </label>

            <div style="position:relative;">
                <input
                    id="password_delete"
                    name="password"
                    type="password"
                    class="gi-input"
                    placeholder="••••••••"
                />
                <button type="button" class="gi-eye-btn" onclick="togglePassword('password_delete')">
                    <i class="fas fa-eye" id="password_delete-eye"></i>
                </button>
            </div>

            @if ($errors->userDeletion->get('password'))
                <p class="mt-2" style="color:#dc2626;font-weight:900;font-size:13px;">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ $errors->userDeletion->first('password') }}
                </p>
            @endif
        </div>

        <div class="row">
            <div class="col-6">
                <button type="button" class="gi-btn gi-btn-outline" onclick="giToggleBox('gi-delete-box')">
                    Cancelar
                </button>
            </div>
            <div class="col-6">
                <button type="submit" class="gi-btn gi-btn-danger">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </form>
</div>
