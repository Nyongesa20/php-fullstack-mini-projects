//  All API calls go to /task-manager/api/*.php

const API = {
    tasks: 'api/tasks.php',
    status: 'api/task_status.php',
    delete: 'api/task_delete.php',
    report: 'api/report.php',
};

// State 
let currentFilter = 'all';

// Boot
document.addEventListener('DOMContentLoaded', () => {
    loadTasks();
    setupForm();
    setupFilters();
    setupReport();

    // Set today as default for due_date min & report date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('due_date').min = today;
    document.getElementById('due_date').value = today;
    document.getElementById('reportDate').value = today;
});

//  Toast 
function toast(msg, isError = false) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show' + (isError ? ' toast-error' : '');
    clearTimeout(el._timer);
    el._timer = setTimeout(() => { el.className = ''; }, 3500);
}

//  API helper 
async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);
    const json = await res.json();
    return { ok: res.ok, status: res.status, json };
}

//  Load & render tasks 
async function loadTasks() {
    const list = document.getElementById('taskList');
    list.innerHTML = '<div class="empty-state"><div class="icon">⏳</div>Loading tasks…</div>';

    const url = currentFilter === 'all' ?
        API.tasks :
        `${API.tasks}?status=${currentFilter}`;

    const { ok, json } = await apiFetch(url);

    if (!ok) {
        list.innerHTML = `<div class="empty-state"><div class="icon">⚠️</div>${json.message}</div>`;
        return;
    }

    if (!json.data || json.data.length === 0) {
        list.innerHTML = '<div class="empty-state"><div class="icon">📭</div>No tasks found.</div>';
        return;
    }

    list.innerHTML = json.data.map(renderTask).join('');
}

function renderTask(t) {
    const nextStatus = { pending: 'in_progress', in_progress: 'done', done: null }[t.status];
    const nextLabel = { in_progress: '▶ Start', done: '✓ Done' }[nextStatus] || '';

    const advanceBtn = nextStatus ?
        `<button class="btn btn-sm btn-advance" onclick="advanceStatus(${t.id},'${nextStatus}')">${nextLabel}</button>` :
        '';

    const deleteBtn = t.status === 'done' ?
        `<button class="btn btn-sm btn-delete" onclick="deleteTask(${t.id})">🗑 Delete</button>` :
        '';

    return `
    <div class="task-card" data-priority="${t.priority}" data-id="${t.id}">
      <div class="task-body">
        <div class="task-title">${escHtml(t.title)}</div>
        <div class="task-meta">
          <span>📅 ${t.due_date}</span>
          <span class="badge badge-${t.priority}">${t.priority}</span>
          <span class="badge badge-${t.status}">${t.status.replace('_', ' ')}</span>
        </div>
      </div>
      <div class="task-actions">
        ${advanceBtn}
        ${deleteBtn}
      </div>
    </div>`;
}

//  Create task form 
function setupForm() {
    document.getElementById('taskForm').addEventListener('submit', async(e) => {
        e.preventDefault();

        const btn = document.getElementById('submitBtn');
        const title = document.getElementById('title').value.trim();
        const due_date = document.getElementById('due_date').value.trim();
        const priority = document.getElementById('priority').value;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Creating…';

        const { ok, json } = await apiFetch(API.tasks, 'POST', { title, due_date, priority });

        btn.disabled = false;
        btn.innerHTML = '＋ Add Task';

        if (!ok) {
            const msg = json.errors ?
                Object.values(json.errors).join(' ') :
                json.message;
            toast(msg, true);
            return;
        }

        toast('Task created successfully!');
        document.getElementById('taskForm').reset();
        // Reset due_date to today again after reset
        document.getElementById('due_date').value = new Date().toISOString().split('T')[0];
        loadTasks();
    });
}

//  Status filter buttons 
function setupFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            currentFilter = btn.dataset.status;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadTasks();
        });
    });
}

//  Advance task status 
async function advanceStatus(id, newStatus) {
    const card = document.querySelector(`.task-card[data-id="${id}"]`);
    if (card) card.style.opacity = '.5';

    const { ok, json } = await apiFetch(`${API.status}?id=${id}`, 'PATCH', { status: newStatus });

    if (!ok) {
        if (card) card.style.opacity = '1';
        toast(json.message, true);
        return;
    }

    toast('Status updated!');
    loadTasks();
}

//  Delete task 
async function deleteTask(id) {
    if (!confirm('Delete this task? This cannot be undone.')) return;

    const { ok, json } = await apiFetch(`${API.delete}?id=${id}`, 'DELETE');

    if (!ok) {
        toast(json.message, true);
        return;
    }

    toast('Task deleted.');
    loadTasks();
}

//  Daily report 
function setupReport() {
    document.getElementById('reportBtn').addEventListener('click', async() => {
        const date = document.getElementById('reportDate').value;
        if (!date) { toast('Please select a date.', true); return; }

        const out = document.getElementById('reportOutput');
        out.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--text-muted)">Loading…</div>';

        const { ok, json } = await apiFetch(`${API.report}?date=${date}`);

        if (!ok) {
            out.innerHTML = `<div style="color:var(--danger)">${json.message}</div>`;
            return;
        }

        out.innerHTML = renderReport(json.date, json.summary);
    });
}

function renderReport(date, summary) {
    const priorities = ['high', 'medium', 'low'];
    const statuses = ['pending', 'in_progress', 'done'];
    const statusLabel = { pending: 'Pending', in_progress: 'In Progress', done: 'Done' };
    const prColors = { high: '#ef4444', medium: '#f59e0b', low: '#22c55e' };

    let html = `<p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem">Report for <strong>${date}</strong></p>`;

    priorities.forEach(p => {
        html += `<div class="report-priority-title" style="color:${prColors[p]}">${p} priority</div>`;
        html += '<div class="report-grid">';
        statuses.forEach(s => {
            const count = summary[p][s];
            html += `
        <div class="report-cell">
          <div class="count" style="color:${count > 0 ? prColors[p] : 'var(--text-muted)'}">${count}</div>
          <div class="label">${statusLabel[s]}</div>
        </div>`;
        });
        html += '</div>';
    });

    return html;
}

//  Utility 
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}