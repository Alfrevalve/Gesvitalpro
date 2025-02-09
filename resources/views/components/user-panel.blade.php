<div class="user-panel">
    <button id="theme-toggle" class="btn">
        <i class="fas fa-moon"></i> <!-- Icono para modo oscuro -->
    </button>
    <button class="btn">
        <i class="fas fa-bell"></i> <!-- Icono de notificación -->
    </button>
    <button class="btn">
        <i class="fas fa-comment"></i> <!-- Icono de mensajes -->
    </button>
    <button class="btn">
        <img src="{{ asset('path/to/profile/image.jpg') }}" alt="Profile" class="profile-image"> <!-- Imagen de perfil -->
    </button>
</div>

<script>
    document.getElementById('theme-toggle').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
    });
</script>

<style>
    .user-panel {
        display: flex;
        align-items: center;
    }
    .dark-mode {
        background-color: #333;
        color: #fff;
    }
</style>
