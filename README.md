# TribalConnect — Web Programming Assignments 1, 2 & 3

A standalone digital learning platform for tribal school students in Wayanad who face barriers to regular school attendance.

---

## Project Overview

**TribalConnect** allows tribal students to access teacher-assigned lessons, quizzes, notes and practice materials online. Teachers can log in to create and update lesson records.

---

## Assignment 1 — Static Client-Side App

**Folder:** `assignment1/`

| File | Purpose |
|------|---------|
| `index.html` | Semantic HTML5 page with Mustache.js templates |
| `style.css` | Responsive CSS — mobile to 8K |
| `app.js` | Client-side data + Mustache rendering + live search |

**How to run:**  
Open `assignment1/index.html` directly in Chrome or Firefox — no server needed.

**Features:**
- Data hardcoded as JSON graph (per RO-Crate schema)
- Dynamic rendering via Mustache.js
- Live search that filters without page reload
- "No results" message when search has no match
- Fully responsive layout

---

## Assignment 2 — PHP + JSON File Backend

**Folder:** `assignment2/`

| File | Purpose |
|------|---------|
| `index.html` | Same HTML structure with loading/error UI states |
| `style.css` | Same responsive CSS |
| `app.js` | Fetch API (async/await) — replaces hardcoded data |
| `api.php` | PHP endpoint serving data.json as JSON |
| `data.json` | Dataset in RO-Crate graph format |

**How to run:**
```bash
cd assignment2
php -S localhost:8000
```
Then open: http://localhost:8000

**Data flow:** `data.json` → `api.php` → HTTP JSON response → `fetch()` → DOM update

---

## Assignment 3 — MySQL + PHP Sessions + Full REST API

**Folder:** `assignment3/`

### Setup

1. Start MySQL and run the schema:
```bash
mysql -u root -p < schema.sql
```

2. Update DB credentials in `config.php` if needed:
```php
define('DB_USER', 'root');
define('DB_PASS', '');
```

3. Start PHP server:
```bash
cd assignment3
php -S localhost:8000
```

4. Open: http://localhost:8000

### Teacher Login
- **Username:** `teacher`
- **Password:** `pass123`

### File Structure

```
assignment3/
├── index.html          # UI with login modal, add/edit forms
├── style.css           # Extended styles (modals, forms, auth)
├── app.js              # Full REST client using fetch()
├── config.php          # DB credentials + teacher credentials
├── db.php              # PDO connection module
├── schema.sql          # MySQL schema + seed data
└── api/
    ├── items.php         # GET /api/items.php?q=...
    ├── login.php         # POST /api/login.php
    ├── items_create.php  # POST /api/items_create.php (protected)
    └── items_update.php  # PUT /api/items_update.php?id=... (protected)
```

### API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `api/items.php` | No | All records |
| GET | `api/items.php?q=keyword` | No | Search results |
| POST | `api/login.php` | No | Login (sets session) |
| POST | `api/items_create.php` | Yes (session) | Create lesson/student |
| PUT | `api/items_update.php?id=...` | Yes (session) | Update lesson/student |

### HTTP Status Codes
- `200 OK` — success
- `201 Created` — record created
- `400 Bad Request` — missing/invalid input
- `401 Unauthorized` — not authenticated
- `404 Not Found` — record or search result not found
- `500 Internal Server Error` — DB error

---

## Technologies Used

| Layer | Technology |
|-------|-----------|
| Markup | HTML5 |
| Styling | Vanilla CSS3 |
| Client JS | Vanilla JavaScript + Mustache.js |
| Backend | Vanilla PHP (PDO) |
| Database | MySQL |
| Auth | PHP `$_SESSION` |
