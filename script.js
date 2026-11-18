document.addEventListener('DOMContentLoaded', () => {
    // 1. Мобильді мәзірді ашу/жабу
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    // 2. Кіру және Тіркелу формаларын ауыстыру (auth.html)
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

    // 3. Жаңа жазба қосу модальды терезесі және статикалық сүзу (diary.html)
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const entryModal = document.getElementById('entryModal');
    const newEntryForm = document.getElementById('newEntryForm');
    const entriesGrid = document.getElementById('entriesGrid');
    const searchInput = document.getElementById('searchInput');
    const moodFilter = document.getElementById('moodFilter');

    if (openModalBtn && entryModal) {
        openModalBtn.addEventListener('click', () => entryModal.classList.remove('hidden'));
        closeModalBtn.addEventListener('click', () => entryModal.classList.add('hidden'));

        newEntryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const title = document.getElementById('entryTitle').value;
            const moodSelect = document.getElementById('entryMood');
            const moodValue = moodSelect.value;
            const moodText = moodSelect.options[moodSelect.selectedIndex].text;
            const content = document.getElementById('entryContent').value;
            const today = new Date().toLocaleDateString('kk-KZ');

            const card = document.createElement('article');
            card.className = 'entry-card';
            card.dataset.mood = moodValue;
            card.innerHTML = `
                <div class="entry-meta">
                    <span class="entry-date">${today}</span>
                    <span class="mood-badge">${moodText}</span>
                </div>
                <h3>${title}</h3>
                <p>${content}</p>
                <div class="entry-actions">
                    <button class="btn-small btn-edit">Өңдеу</button>
                    <button class="btn-small btn-delete">Жою</button>
                </div>
            `;
            entriesGrid.prepend(card);
            newEntryForm.reset();
            entryModal.classList.add('hidden');
        });
    }

    // Клиенттік іздеу және көңіл-күй бойынша сүзу
    function filterEntries() {
        if (!entriesGrid) return;
        const query = searchInput.value.toLowerCase();
        const selectedMood = moodFilter.value;
        const cards = entriesGrid.querySelectorAll('.entry-card');

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const mood = card.dataset.mood;
            const matchesSearch = text.includes(query);
            const matchesMood = selectedMood === 'all' || mood === selectedMood;
            card.style.display = (matchesSearch && matchesMood) ? 'flex' : 'none';
        });
    }

    if (searchInput && moodFilter) {
        searchInput.addEventListener('input', filterEntries);
        moodFilter.addEventListener('change', filterEntries);
    }
});
