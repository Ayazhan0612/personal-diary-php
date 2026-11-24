document.addEventListener('DOMContentLoaded', () => {
    // 1. Мобильді мәзірді ашу/жабу
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    // 2. Кіру және Тіркелу формаларын ауыстыру (auth.php)
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authError = document.getElementById('authError');

    if (loginTab && registerTab) {
        loginTab.addEventListener('click', () => {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        });

        registerTab.addEventListener('click', () => {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });

        registerForm.addEventListener('submit', (e) => {
            const pass = document.getElementById('regPassword').value;
            const confirm = document.getElementById('regConfirm').value;
            if (pass !== confirm) {
                e.preventDefault();
                authError.textContent = 'Құпиясөздер сәйкес келмейді!';
            }
        });
    }

    // 3. Жаңа жазба қосу модальды терезесін ашу/жабу (diary.php)
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const entryModal = document.getElementById('entryModal');

    if (openModalBtn && entryModal) {
        openModalBtn.addEventListener('click', () => entryModal.classList.remove('hidden'));
        closeModalBtn.addEventListener('click', () => entryModal.classList.add('hidden'));
    }
});
