/**
 * TribalConnect - Assignment 3
 * Full REST client: fetch() for all CRUD operations.
 * Session state tracked client-side via isLoggedIn flag.
 * No page reloads on any operation.
 */

// ============================================================
// State
// ============================================================
let allLessons = [];
let allQuizzes = [];
let allStudents = [];
let allScholarships = [];
let isLoggedIn = false;   // Tracks login state client-side

// ============================================================
// DOM helpers
// ============================================================
function show(id) { const el = document.getElementById(id); if (el) el.classList.remove('hidden'); }
function hide(id) { const el = document.getElementById(id); if (el) el.classList.add('hidden'); }

function showError(elId, msg) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('hidden');
}
function clearError(elId) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = '';
    el.classList.add('hidden');
}

// ============================================================
// Mustache rendering
// ============================================================
function renderSection(containerId, templateId, items) {
    const container = document.getElementById(containerId);
    const template = document.getElementById(templateId).innerHTML;
    container.innerHTML = items.length > 0
        ? Mustache.render(template, { items, isLoggedIn })
        : '';

    // Attach edit-button listeners if logged in
    if (isLoggedIn) {
        container.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => openEditModal(btn.dataset));
        });
    }
}

function renderAll(lessons, quizzes, students, scholarships) {
    renderSection('lessons-container', 'lesson-template', lessons);
    renderSection('quizzes-container', 'quiz-template', quizzes);
    renderSection('students-container', 'student-template', students);
    renderSection('scholarships-container', 'scholarship-template', scholarships);

    const total = lessons.length + quizzes.length + students.length + scholarships.length;
    document.getElementById('no-results').classList.toggle('hidden', total > 0);
}

// ============================================================
// Load data from API
// ============================================================
async function loadData(query) {
    const url = query ? `api/items.php?q=${encodeURIComponent(query)}` : 'api/items.php';

    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        const data = await res.json();

        if (res.status === 404) {
            // No search results
            allLessons = allQuizzes = allStudents = allScholarships = [];
            renderAll([], [], [], []);
            document.getElementById('searchStatus').textContent =
                `No results found for "${query}"`;
            return;
        }

        if (!res.ok) throw new Error(data.error || `Server error ${res.status}`);

        const graph = data.graph || [];
        allLessons = graph.filter(i => i.type === 'Lesson');
        allQuizzes = graph.filter(i => i.type === 'Quiz');
        allStudents = graph.filter(i => i.type === 'Student');
        allScholarships = graph.filter(i => i.type === 'Scholarship' || i.type === 'Skill Course');

        if (!query) {
            // Only reveal sections on initial full load
            hide('loading-msg');
            show('search-section');
            show('lessons');
            show('quizzes');
            show('students');
            show('scholarships');
        }

        renderAll(allLessons, allQuizzes, allStudents, allScholarships);

        if (query) {
            const total = allLessons.length + allQuizzes.length + allStudents.length + allScholarships.length;
            document.getElementById('searchStatus').textContent =
                total > 0 ? `Showing ${total} result(s) for "${query}"` : `No results for "${query}"`;
        } else {
            document.getElementById('searchStatus').textContent = '';
        }

    } catch (err) {
        hide('loading-msg');
        const errEl = document.getElementById('error-msg');
        errEl.textContent = '⚠️ Could not load content: ' + err.message;
        errEl.classList.remove('hidden');
        console.error('loadData error:', err);
    }
}

// ============================================================
// Auth: Login
// ============================================================
async function doLogin(username, password) {
    clearError('login-error');

    const res = await fetch('api/login.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    const data = await res.json();

    if (res.status === 200) {
        isLoggedIn = true;

        // Update UI
        hide('login-overlay');
        hide('btn-show-login');
        show('auth-status');
        show('teacher-toolbar');

        // Re-render so edit buttons appear
        renderAll(allLessons, allQuizzes, allStudents, allScholarships);

        // Clear form
        document.getElementById('login-form').reset();
    } else {
        showError('login-error', data.error || 'Login failed. Please try again.');
    }
}

// ============================================================
// Auth: Logout
// ============================================================
function doLogout() {
    isLoggedIn = false;
    show('btn-show-login');
    hide('auth-status');
    hide('teacher-toolbar');
    // Re-render to remove edit buttons
    renderAll(allLessons, allQuizzes, allStudents, allScholarships);
}

// ============================================================
// Add Lesson Modal
// ============================================================
function openAddModal() {
    document.getElementById('modal-form-heading').textContent = 'Add New Lesson';
    document.getElementById('edit-lesson-id').value = '';
    document.getElementById('add-lesson-form').reset();
    clearError('add-form-error');

    // Default date to today
    document.getElementById('f-date').value = new Date().toISOString().split('T')[0];

    show('add-lesson-overlay');
}

function openEditModal(data) {
    document.getElementById('modal-form-heading').textContent = 'Edit Lesson';
    document.getElementById('edit-lesson-id').value = data.id;
    document.getElementById('f-title').value = data.title || '';
    document.getElementById('f-subject').value = data.subject || '';
    document.getElementById('f-grade').value = data.grade || '';
    document.getElementById('f-teacher').value = data.teacher || '';
    document.getElementById('f-date').value = data.date || '';
    document.getElementById('f-description').value = data.desc || '';
    clearError('add-form-error');

    show('add-lesson-overlay');
}

// ============================================================
// Save Lesson (Create or Update)
// ============================================================
async function saveLesson(e) {
    e.preventDefault();
    clearError('add-form-error');

    const lessonId = document.getElementById('edit-lesson-id').value.trim();
    const title = document.getElementById('f-title').value.trim();
    const subject = document.getElementById('f-subject').value.trim();
    const grade = document.getElementById('f-grade').value.trim();
    const teacher = document.getElementById('f-teacher').value.trim();
    const date = document.getElementById('f-date').value.trim();
    const desc = document.getElementById('f-description').value.trim();

    if (!title || !subject || !grade || !teacher || !date || !desc) {
        showError('add-form-error', 'All fields are required.');
        return;
    }

    const payload = {
        itemType: 'lesson', title, subject, description: desc,
        grade, teacher, date
    };

    const isEdit = lessonId !== '';
    const url = isEdit
        ? `api/items_update.php?id=${encodeURIComponent(lessonId)}`
        : 'api/items_create.php';
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (res.status === 401) {
            showError('add-form-error', 'Session expired. Please log in again.');
            return;
        }
        if (!res.ok) {
            showError('add-form-error', data.error || 'Operation failed.');
            return;
        }

        // Success: close modal, refresh data
        hide('add-lesson-overlay');
        document.getElementById('add-lesson-form').reset();
        await loadData('');  // Refresh full list

    } catch (err) {
        showError('add-form-error', 'Network error: ' + err.message);
    }
}

// ============================================================
// Live Search
// ============================================================
let searchDebounce = null;

function onSearch(query) {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        loadData(query.trim());
    }, 300);
}

// ============================================================
// Bootstrap
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    // Load data initially
    loadData('');

    // Search
    document.getElementById('searchInput').addEventListener('input', function () {
        onSearch(this.value);
    });

    // Nav active
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function () {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Show login modal
    document.getElementById('btn-show-login').addEventListener('click', () => {
        clearError('login-error');
        show('login-overlay');
        document.getElementById('login-username').focus();
    });

    // Cancel login
    document.getElementById('btn-cancel-login').addEventListener('click', () => {
        hide('login-overlay');
        document.getElementById('login-form').reset();
    });

    // Submit login
    document.getElementById('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const u = document.getElementById('login-username').value.trim();
        const p = document.getElementById('login-password').value;
        if (!u || !p) {
            showError('login-error', 'Please enter username and password.');
            return;
        }
        doLogin(u, p);
    });

    // Logout
    document.getElementById('btn-logout').addEventListener('click', doLogout);

    // Open Add modal
    document.getElementById('btn-add-lesson').addEventListener('click', openAddModal);

    // Cancel Add/Edit modal
    document.getElementById('btn-cancel-add').addEventListener('click', () => {
        hide('add-lesson-overlay');
    });

    // Submit Add/Edit form
    document.getElementById('add-lesson-form').addEventListener('submit', saveLesson);

    // Close modals on overlay click
    document.getElementById('login-overlay').addEventListener('click', function (e) {
        if (e.target === this) { hide('login-overlay'); }
    });
    document.getElementById('add-lesson-overlay').addEventListener('click', function (e) {
        if (e.target === this) { hide('add-lesson-overlay'); }
    });
});
