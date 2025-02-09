document.addEventListener('DOMContentLoaded', function() {
    const notificationElement = document.getElementById('notification');

    if (notificationElement) {
        const message = notificationElement.dataset.message;
        if (message) {
            // Mostrar la notificación en pantalla
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerText = message;

            document.body.appendChild(notification);

            // Ocultar la notificación después de 3 segundos
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
});
