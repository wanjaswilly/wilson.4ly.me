import '../css/app.css';

document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('mobile-menu-button')
    const menu = document.getElementById('mobile-menu')
    const iconOpen = document.getElementById('menu-open')
    const iconClose = document.getElementById('menu-close')

    button.addEventListener('click', () => {
        menu.classList.toggle('hidden')
        iconOpen.classList.toggle('hidden')
        iconClose.classList.toggle('hidden')
    });
});

