const toggle = document.querySelector('[data-nav-toggle]');
const nav = document.querySelector('[data-main-nav]');

if (toggle && nav) {
    toggle.addEventListener('click', () => {
        nav.classList.toggle('open');
    });
}
