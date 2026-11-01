/**
 * TribalConnect - Assignment 2
 * Fetches data from api.php using Fetch API (async/await)
 * and renders the UI using Mustache.js.
 * No page reload on search or data load.
 */

// ============================================================
// Globals
// ============================================================
let allLessons = [];
let allQuizzes = [];
let allStudents = [];
let allScholarships = [];

// ============================================================
// DOM helpers
// ============================================================
function show(id) { document.getElementById(id).classList.remove('hidden'); }
function hide(id) { document.getElementById(id).classList.add('hidden'); }
function setText(id, text) { document.getElementById(id).textContent = text; }

// ============================================================
// Mustache rendering
// ============================================================
function renderSection(containerId, templateId, items) {
    const container = document.getElementById(containerId);
    const template = document.getElementById(templateId).innerHTML;
    container.innerHTML = items.length > 0
        ? Mustache.render(template, { items })
        : '';
}

function renderAll(lessons, quizzes, students, scholarships) {
    renderSection('lessons-container', 'lesson-template', lessons);
    renderSection('quizzes-container', 'quiz-template', quizzes);
    renderSection('students-container', 'student-template', students);
    renderSection('scholarships-container', 'scholarship-template', scholarships);

    const total = lessons.length + quizzes.length + students.length + scholarships.length;
    const noResults = document.getElementById('no-results');
    noResults.classList.toggle('hidden', total > 0);
}

// ============================================================
// Fetch data from PHP endpoint
// ============================================================
async function loadData() {
    const loadingEl = document.getElementById('loading-msg');
    const errorEl = document.getElementById('error-msg');

    // Show loading state
    loadingEl.classList.remove('hidden');
    hide('error-msg');

    try {
        const response = await fetch('api.php');

        if (!response.ok) {
            throw new Error(`Server returned status ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (!data.graph || !Array.isArray(data.graph)) {
            throw new Error('Unexpected data format received from server.');
        }

        // Separate items by type
        allLessons = data.graph.filter(i => i.type === 'Lesson');
        allQuizzes = data.graph.filter(i => i.type === 'Quiz');
        allStudents = data.graph.filter(i => i.type === 'Student');
        allScholarships = data.graph.filter(i => i.type === 'Scholarship' || i.type === 'SkillCourse');

        // Hide loading, reveal UI
        hide('loading-msg');
        show('search-section');
        show('lessons');
        show('quizzes');
        show('students');
        show('scholarships');

        // Initial render
        renderAll(allLessons, allQuizzes, allStudents, allScholarships);

    } catch (err) {
        // Show error, hide loading
        hide('loading-msg');
        errorEl.classList.remove('hidden');
        errorEl.textContent = '⚠️ Could not load content: ' + err.message
            + '. Please check your connection or try again later.';
        console.error('Fetch error:', err);
    }
}

// ============================================================
// Live Search
// ============================================================
function normalise(str) {
    return (str || '').toLowerCase();
}

function matchesQuery(item, query) {
    if (!query) return true;
    const q = normalise(query);
    const fields = [
        item.title, item.name, item.subject, item.description,
        item.grade, item.teacher, item.school, item.village,
        item.category, item.eligibility
    ];
    return fields.some(f => normalise(f).includes(q));
}

function filterAndRender(query) {
    const lessons = allLessons.filter(i => matchesQuery(i, query));
    const quizzes = allQuizzes.filter(i => matchesQuery(i, query));
    const students = allStudents.filter(i => matchesQuery(i, query));
    const scholarships = allScholarships.filter(i => matchesQuery(i, query));

    renderAll(lessons, quizzes, students, scholarships);

    const total = lessons.length + quizzes.length + students.length + scholarships.length;
    const status = document.getElementById('searchStatus');
    if (query) {
        status.textContent = total > 0
            ? `Showing ${total} result(s) for "${query}"`
            : `No results found for "${query}"`;
    } else {
        status.textContent = '';
    }
}

// ============================================================
// Event Listeners
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    loadData();

    document.getElementById('searchInput').addEventListener('input', function () {
        filterAndRender(this.value.trim());
    });

    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
