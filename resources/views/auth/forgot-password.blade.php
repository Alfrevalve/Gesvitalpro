<x-app-layout>
<div class="container">
    <h1>Restablecer Contraseña</h1>
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-success">Enviar Enlace de Restablecimiento</button>
    </form>
</div>
</x-app-layout>
