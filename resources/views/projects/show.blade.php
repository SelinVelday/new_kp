@extends('layouts.master')

@section('title', $project->name . ' - Kanban Board')

@push('styles')
{{-- CSS Tambahan untuk Tampilan Kanban --}}
<style>
    .kanban-board-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding-bottom: 1rem;
        height: calc(100vh - 200px);
    }
    .kanban-column {
        min-width: 300px;
        width: 300px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .kanban-tasks {
        flex-grow: 1;
        overflow-y: auto;
        min-height: 100px; /* Area drop minimal */
    }
    /* Scrollbar Cantik */
    .kanban-tasks::-webkit-scrollbar { width: 6px; }
    .kanban-tasks::-webkit-scrollbar-track { background: transparent; }
    .kanban-tasks::-webkit-scrollbar-thumb { background: #d3d3d3; border-radius: 3px; }
    
    /* Efek Dragging SortableJS */
    .sortable-ghost { opacity: 0.4; background-color: #f0f0f0; border: 2px dashed #ccc; } 
    .sortable-drag { cursor: grabbing; opacity: 1; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.15); transform: rotate(2deg); }
    
    .task-card { transition: transform 0.2s, box-shadow 0.2s; }
    .task-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    
    {{-- ALERT CONTAINER (Untuk Feedback AJAX) --}}
    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER PROJECT --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $project->name }}</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">{{ $project->description ?? 'Tidak ada deskripsi.' }}</span>
                <span class="badge bg-label-success">Active <i class='bx bx-wifi'></i></span>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            {{-- List Member --}}
            <div class="d-flex align-items-center">
                <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                    @foreach($project->members->take(5) as $member)
                    <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{ $member->name }}" class="avatar avatar-xs pull-up">
                        <img src="{{ $member->avatar ? asset('storage/' . $member->avatar) : asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" style="object-fit: cover;">
                    </li>
                    @endforeach
                    @if($project->members->count() > 5)
                        <li class="avatar avatar-xs">
                            <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" title="{{ $project->members->count() - 5 }} more">+{{ $project->members->count() - 5 }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Tombol Invite --}}
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                <i class="bx bx-user-plus me-1"></i> Invite
            </button>
        </div>
    </div>

    {{-- KANBAN BOARD AREA --}}
    <div class="kanban-board-container" id="kanban-wrapper">
        
        {{-- LOOPING KOLOM --}}
        @foreach($project->columns as $column)
        <div class="kanban-column" id="column-wrapper-{{ $column->id }}">
            <div class="card h-100 shadow-sm border-0 bg-label-secondary">
                
                {{-- HEADER KOLOM --}}
                <div class="card-header d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="m-0 fw-bold text-uppercase fs-7">{{ $column->name }}</h6>
                        <span class="badge bg-white text-primary rounded-pill task-count-badge">{{ $column->tasks->count() }}</span>
                    </div>
                    
                    {{-- DROPDOWN MENU --}}
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item text-danger delete-column-btn" href="javascript:void(0);" data-id="{{ $column->id }}">
                                   <i class="bx bx-trash me-1"></i> Hapus Kolom
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- BODY KOLOM (TASK LIST) --}}
                <div class="card-body p-2 kanban-tasks" id="col-{{ $column->id }}" data-column-id="{{ $column->id }}">
                    @foreach($column->tasks as $task)
                        <div class="card shadow-sm border bg-white cursor-pointer task-card mb-2" 
                             id="card-task-{{ $task->id }}"
                             data-task-id="{{ $task->id }}" 
                             onclick="openTaskDetail({{ $task->id }})">
                             
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    {{-- Badge Priority --}}
                                    @php
                                        $badgeClass = match($task->priority) {
                                            'high' => 'bg-label-danger',
                                            'medium' => 'bg-label-warning',
                                            default => 'bg-label-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill priority-badge" style="font-size: 0.7rem;">{{ ucfirst($task->priority) }}</span>
                                </div>

                                <h6 class="mb-2 text-dark task-title">{{ $task->title }}</h6>
                                
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <small class="text-muted">
                                        <i class="bx bx-calendar"></i> {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M') : '-' }}
                                    </small>
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Indikator Komentar --}}
                                        <small class="text-muted comment-indicator" style="{{ $task->comments_count > 0 ? '' : 'display:none' }}">
                                            <i class='bx bx-message-rounded'></i> <span class="comment-count">{{ $task->comments_count }}</span>
                                        </small>
                                        
                                        {{-- Avatar Assignee --}}
                                        <div class="avatar avatar-xs assignee-avatar" style="{{ $task->assigned_to ? '' : 'display:none' }}">
                                            @if($task->assigned_to)
                                                <img src="{{ $task->assignee->avatar ? asset('storage/'.$task->assignee->avatar) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" style="object-fit: cover;">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- FOOTER --}}
                <div class="card-footer p-2 bg-transparent border-top-0">
                    <button class="btn btn-outline-primary btn-sm w-100 fw-semibold" onclick="openAddTaskModal({{ $column->id }})">
                        <i class="bx bx-plus"></i> Tambah Tugas
                    </button>
                </div>
            </div>
        </div>
        @endforeach

        {{-- KOLOM INPUT BARU --}}
        <div style="min-width: 300px;">
            <div class="card shadow-none bg-transparent border-2 border-dashed h-100 d-flex justify-content-start">
                <div class="card-body p-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="colNameInput" placeholder="Nama Kolom Baru...">
                        <button class="btn btn-primary" type="button" onclick="submitColumnManual(this)">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ======================================================= --}}
{{-- MODALS AREA --}}
{{-- ======================================================= --}}

{{-- 1. MODAL CREATE TASK --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="column_id" id="modalColumnId">
                    <div class="mb-3">
                        <label class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" name="title" required placeholder="Contoh: Bug Fixing">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioritas</label>
                        <select class="form-select" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign ke</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tenggat Waktu</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. MODAL DETAIL TASK (EDIT & DISKUSI) --}}
<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="min-height: 500px;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Detail Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    
                    {{-- KIRI: FORM EDIT & KOMENTAR --}}
                    <div class="col-md-8 p-4 border-end">
                        {{-- Form Edit AJAX --}}
                        <form id="formEditTask" onsubmit="updateTaskAjax(event)">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Judul</label>
                                <input type="text" name="title" id="detailTitle" class="form-control fw-bold text-dark" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Prioritas</label>
                                    <select name="priority" id="detailPriority" class="form-select form-select-sm">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Assignee</label>
                                    <select name="assigned_to" id="detailAssigneeInput" class="form-select form-select-sm">
                                        <option value="">Unassigned</option>
                                        @foreach($project->members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Deskripsi</label>
                                <textarea name="description" id="detailDescription" class="form-control" rows="3" placeholder="Tambahkan deskripsi detail..."></textarea>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveTask">
                                    <i class="bx bx-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">
                        
                        {{-- Area Diskusi --}}
                        <div>
                            <h6 class="fw-bold mb-3"><i class='bx bx-chat'></i> Diskusi</h6>
                            <div id="commentList" class="bg-light rounded p-3 mb-3" style="max-height: 250px; overflow-y: auto;">
                                {{-- Komentar dimuat via JS --}}
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" id="commentInput" class="form-control" placeholder="Tulis komentar..." onkeypress="if(event.key === 'Enter') sendComment()">
                                <button type="button" class="btn btn-primary" onclick="sendComment()">
                                    <i class='bx bx-send'></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: META DATA & ATTACHMENT --}}
                    <div class="col-md-4 p-4 bg-light">
                        {{-- Attachment Section --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Lampiran</label>
                            <ul class="list-group list-group-flush mb-2 bg-white rounded shadow-sm" id="attachmentList"></ul>
                            
                            <div class="mt-2">
                                <label for="fileInput" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class='bx bx-paperclip me-1'></i> Upload File
                                </label>
                                <input type="file" id="fileInput" class="d-none" onchange="uploadFile()">
                            </div>
                            <div id="uploadStatus" class="text-muted small mt-1 fst-italic"></div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Tenggat Waktu</label>
                            <input type="text" class="form-control form-control-sm bg-white" id="detailDueDateDisplay" readonly>
                        </div>

                        <hr>
                        <form id="formDeleteTask" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-label-danger btn-sm w-100" onclick="return confirm('Yakin ingin menghapus tugas ini? Tindakan tidak bisa dibatalkan.')">
                                <i class='bx bx-trash'></i> Hapus Tugas
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. MODAL INVITE --}}
<div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Undang Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('projects.members.add', $project->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email pengguna..." required>
                    <button class="btn btn-primary w-100">Kirim Undangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    let currentTaskId = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 0. INISIALISASI TOOLTIP BOOTSTRAP
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });

    // 1. TAMBAH KOLOM MANUAL (AJAX)
    window.submitColumnManual = function(btn) {
        const input = document.getElementById('colNameInput');
        const name = input.value.trim();
        
        if(!name) { alert("Nama kolom tidak boleh kosong!"); return; }

        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

        let formData = new FormData();
        formData.append('name', name);
        formData.append('project_id', "{{ $project->id }}");

        fetch("{{ route('columns.store') }}", {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Reload agar struktur blade ter-render sempurna
            } else {
                alert("Gagal membuat kolom.");
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-plus"></i>';
            }
        });
    };

    // 2. HAPUS KOLOM (AJAX + DOM Remove)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-column-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-column-btn');
            const colId = btn.getAttribute('data-id');
            const colWrapper = document.getElementById(`column-wrapper-${colId}`);

            if(!confirm("Yakin hapus kolom ini beserta isinya?")) return;
            
            fetch(`/columns/${colId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(res => res.json()).then(data => {
                if(data.success && colWrapper) {
                    colWrapper.remove(); // Hapus elemen tanpa reload
                    showAlert('Kolom berhasil dihapus', 'success');
                }
            });
        }
    });

    // 3. BUKA DETAIL TASK
    window.openTaskDetail = function(taskId) {
        currentTaskId = taskId;
        
        // Reset UI Modal
        document.getElementById('detailTitle').value = "Loading...";
        document.getElementById('detailDescription').value = "";
        document.getElementById('commentList').innerHTML = '<div class="text-center p-3"><i class="bx bx-loader-alt bx-spin"></i></div>';
        
        let detailModal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
        detailModal.show();

        // Fetch Data
        fetch(`/tasks/${taskId}`)
        .then(res => res.json())
        .then(data => {
            // Isi Form Edit
            document.getElementById('detailTitle').value = data.title;
            document.getElementById('detailDescription').value = data.description || '';
            document.getElementById('detailPriority').value = data.priority;
            document.getElementById('detailAssigneeInput').value = data.assigned_to || '';
            document.getElementById('detailDueDateDisplay').value = data.due_date || '-';

            // Setup Form Action (Untuk Delete)
            document.getElementById('formDeleteTask').action = `/tasks/${taskId}`;

            // Render Attachments
            let attachHtml = '';
            if(data.attachments && data.attachments.length > 0) {
                data.attachments.forEach(file => {
                    attachHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-1">
                            <a href="/storage/${file.file_path}" target="_blank" class="text-decoration-none text-truncate" style="max-width: 85%;">
                                <i class="bx bx-file"></i> ${file.file_name}
                            </a>
                        </li>`;
                });
            } else { attachHtml = '<li class="list-group-item text-muted small fst-italic px-2">Tidak ada lampiran.</li>'; }
            document.getElementById('attachmentList').innerHTML = attachHtml;

            // Render Comments
            loadComments(data.comments);
        });
    };

    // 4. UPDATE TASK AJAX (Tanpa Reload)
    window.updateTaskAjax = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveTask');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        let formData = new FormData(document.getElementById('formEditTask'));
        // Method spoofing untuk Laravel PUT
        formData.append('_method', 'PUT');

        fetch(`/tasks/${currentTaskId}`, {
            method: "POST", // Menggunakan POST dengan _method PUT
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            // Update Tampilan Kartu di Board (Tanpa Reload)
            const card = document.getElementById(`card-task-${currentTaskId}`);
            if(card) {
                // Update Judul
                card.querySelector('.task-title').innerText = formData.get('title');
                
                // Update Badge Prioritas
                const priority = formData.get('priority');
                const badge = card.querySelector('.priority-badge');
                badge.className = 'badge rounded-pill priority-badge'; // Reset class
                if(priority === 'high') badge.classList.add('bg-label-danger');
                else if(priority === 'medium') badge.classList.add('bg-label-warning');
                else badge.classList.add('bg-label-info');
                badge.innerText = priority.charAt(0).toUpperCase() + priority.slice(1);
            }
            
            showAlert("Tugas berhasil diperbarui!", "success");
            
            // Tutup modal opsional (atau biarkan terbuka)
            // bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide();
        })
        .catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert("Terjadi kesalahan saat menyimpan.");
        });
    };

    // 5. COMMENTS LOGIC
    function loadComments(comments) {
        let html = '';
        if(comments && comments.length > 0) {
            comments.forEach(c => {
                html += `
                    <div class="d-flex mb-3 animate__animated animate__fadeIn">
                        <div class="avatar avatar-xs me-2">
                             <img src="${c.user.avatar ? '/storage/'+c.user.avatar : '/assets/img/avatars/1.png'}" class="rounded-circle">
                        </div>
                        <div class="flex-grow-1">
                            <div class="bg-white p-2 rounded shadow-sm border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-primary">${c.user ? c.user.name : 'User'}</small>
                                    <small class="text-muted" style="font-size:10px;">${new Date(c.created_at).toLocaleString()}</small>
                                </div>
                                <p class="mb-0 small text-dark">${c.content}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else { html = '<div class="text-center small text-muted mt-3">Belum ada diskusi. Jadilah yang pertama berkomentar!</div>'; }
        document.getElementById('commentList').innerHTML = html;
        scrollToBottomComments();
    }

    window.sendComment = function() {
        let input = document.getElementById('commentInput');
        let content = input.value.trim();
        if(!content) return;

        let formData = new FormData();
        formData.append('content', content);
        
        fetch(`/tasks/${currentTaskId}/comments`, {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        }).then(res => res.json()).then(data => {
            if(data.success) {
                let list = document.getElementById('commentList');
                // Hapus pesan kosong jika ada
                if(list.innerText.includes('Belum ada diskusi')) list.innerHTML = '';

                let newHtml = `
                    <div class="d-flex mb-3 animate__animated animate__fadeIn">
                        <div class="avatar avatar-xs me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">Me</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="bg-white p-2 rounded shadow-sm border" style="border-left: 3px solid #696cff !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-dark">Saya</small>
                                    <small class="text-muted" style="font-size:10px;">Baru saja</small>
                                </div>
                                <p class="mb-0 small text-dark">${data.data.content}</p>
                            </div>
                        </div>
                    </div>`;
                list.insertAdjacentHTML('beforeend', newHtml);
                input.value = '';
                scrollToBottomComments();

                // Update indikator komentar di kartu luar
                const card = document.getElementById(`card-task-${currentTaskId}`);
                if(card) {
                    const indicator = card.querySelector('.comment-indicator');
                    const counter = card.querySelector('.comment-count');
                    indicator.style.display = 'inline-block';
                    counter.innerText = parseInt(counter.innerText || 0) + 1;
                }
            }
        });
    };

    function scrollToBottomComments() {
        let list = document.getElementById('commentList');
        list.scrollTop = list.scrollHeight;
    }

    // 6. UPLOAD FILE
    window.uploadFile = function() {
        let fileInput = document.getElementById('fileInput');
        if(fileInput.files.length === 0) return;

        let formData = new FormData();
        formData.append('file', fileInput.files[0]);

        const status = document.getElementById('uploadStatus');
        status.innerText = "Mengunggah...";

        fetch(`/tasks/${currentTaskId}/attachments`, {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        }).then(res => res.json()).then(data => {
            status.innerText = "";
            let list = document.getElementById('attachmentList');
            // Hapus pesan kosong
            if(list.innerHTML.includes('Tidak ada lampiran')) list.innerHTML = '';

            let item = `
                <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-1 bg-label-success animate__animated animate__fadeIn">
                    <a href="${data.url}" target="_blank" class="text-decoration-none text-dark">
                        <i class="bx bx-check-circle text-success"></i> ${data.data.file_name}
                    </a>
                </li>`;
            list.insertAdjacentHTML('beforeend', item);
            fileInput.value = ''; // Reset input
        }).catch(err => {
            status.innerText = "Gagal mengunggah.";
        });
    };

    // 7. INISIALISASI DRAG & DROP
    document.addEventListener('DOMContentLoaded', function() {
        var containers = document.querySelectorAll('.kanban-tasks');
        containers.forEach(function (container) {
            new Sortable(container, {
                group: 'kanban-board', 
                animation: 150,
                delay: 100, // Cegah drag tidak sengaja saat klik
                delayOnTouchOnly: true,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function (evt) {
                    var itemEl = evt.item;
                    var taskId = itemEl.getAttribute('data-task-id');
                    var newColumnId = evt.to.getAttribute('data-column-id');
                    var newPosition = evt.newIndex + 1; 

                    let formData = new FormData();
                    formData.append('task_id', taskId);
                    formData.append('column_id', newColumnId);
                    formData.append('new_position', newPosition);

                    fetch("{{ route('tasks.move') }}", {
                        method: "POST",
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(!data.success) {
                            // Jika gagal, kembalikan kartu (Rollback UI)
                            alert("Gagal memindahkan tugas. Silakan refresh.");
                            location.reload(); 
                        }
                    });
                }
            });
        });
    });

    // Helper: Buka Modal Tambah Task
    window.openAddTaskModal = function(colId) {
        document.getElementById('modalColumnId').value = colId;
        new bootstrap.Modal(document.getElementById('addTaskModal')).show();
    }

    // Helper: Show Custom Alert
    function showAlert(message, type = 'success') {
        const container = document.getElementById('alert-container');
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        container.innerHTML = alertHtml;
        setTimeout(() => { container.innerHTML = ''; }, 3000);
    }
</script>
@endpush

@endsection