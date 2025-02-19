<x-app-layout>
<div class="container">
    <h1>Confirmar Contraseña</h1>
    <form action="{{ route('password.confirm') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-success">Confirmar</button>
    </form>
</div>
</x-app-layout>
