import 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle
    const toggleButton = document.getElementById('sidebar-toggle');
    const body = document.body;

    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
        });
    }
});
