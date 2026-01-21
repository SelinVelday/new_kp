@extends('layouts.master')

@section('title', $project->name . ' - Kanban Board')

@push('styles')
{{-- CSS Tambahan untuk Tampilan Kanban yang Rapi --}}
<style>
    .kanban-board-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding-bottom: 1rem;
        height: calc(100vh - 200px); /* Sesuaikan tinggi agar pas di layar */
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
    /* Styling Scrollbar agar tipis & cantik */
    .kanban-tasks::-webkit-scrollbar { width: 6px; }
    .kanban-tasks::-webkit-scrollbar-track { background: transparent; }
    .kanban-tasks::-webkit-scrollbar-thumb { background: #d3d3d3; border-radius: 3px; }
    
    /* Efek saat Dragging */
    .gu-mirror { position: fixed !important; margin: 0 !important; z-index: 9999 !important; opacity: 0.8; }
    .gu-hide { display: none !important; }
    .gu-transit { opacity: 0.2; }
    .task-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s; }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    
    {{-- ALERT PESAN SUKSES/ERROR --}}
    <div id="alert-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

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
    <div class="kanban-board-container">
        
        {{-- LOOPING KOLOM --}}
        @foreach($project->columns as $column)
        <div class="kanban-column" data-id="{{ $column->id }}">
            <div class="card h-100 shadow-sm border-0 bg-label-secondary">
                
                {{-- HEADER KOLOM --}}
                <div class="card-header d-flex justify-content-between align-items-center p-3">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="m-0 fw-bold text-uppercase fs-7">{{ $column->name }}</h6>
                        <span class="badge bg-white text-primary rounded-pill">{{ $column->tasks->count() }}</span>
                    </div>
                    
                    {{-- DROPDOWN HAPUS --}}
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

                {{-- BODY KOLOM (TASK LIST - DRAGGABLE) --}}
                <div class="card-body p-2 kanban-tasks" id="col-{{ $column->id }}" data-column-id="{{ $column->id }}">
                    @foreach($column->tasks as $task)
                        <div class="card shadow-sm border bg-white cursor-pointer task-card mb-2" 
                             data-task-id="{{ $task->id }}" 
                             onclick="openTaskDetail({{ $task->id }})">
                             
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    @php
                                        $badge = match($task->priority) {
                                            'high' => 'bg-label-danger',
                                            'medium' => 'bg-label-warning',
                                            default => 'bg-label-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }} rounded-pill" style="font-size: 0.7rem;">{{ ucfirst($task->priority) }}</span>
                                </div>

                                <h6 class="mb-2 text-dark">{{ $task->title }}</h6>
                                
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <small class="text-muted">
                                        <i class="bx bx-calendar"></i> {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M') : '-' }}
                                    </small>
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        @if($task->comments_count > 0)
                                            <small class="text-muted"><i class='bx bx-message-rounded'></i> {{ $task->comments_count }}</small>
                                        @endif
                                        @if($task->assigned_to)
                                            <div class="avatar avatar-xs">
                                                <img src="{{ $task->assignee->avatar ? asset('storage/'.$task->assignee->avatar) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" style="object-fit: cover;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- FOOTER (ADD TASK) --}}
                <div class="card-footer p-2 bg-transparent border-top-0">
                    <button class="btn btn-outline-primary btn-sm w-100 fw-semibold" onclick="openAddTaskModal({{ $column->id }})">
                        <i class="bx bx-plus"></i> Tambah Tugas
                    </button>
                </div>
            </div>
        </div>
        @endforeach

        {{-- INPUT KOLOM BARU (MANUAL FIX) --}}
        <div style="min-width: 300px;">
            <div class="card shadow-none bg-transparent border-2 border-dashed">
                <div class="card-body p-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="colNameInput" placeholder="Nama Kolom Baru...">
                        <button class="btn btn-primary" type="button" onclick="submitColumnManual()">
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

{{-- 2. MODAL DETAIL TASK (EDIT, COMMENT, ATTACH) --}}
<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row h-100">
                    {{-- KIRI: EDIT FORM & CHAT --}}
                    <div class="col-md-8 border-end">
                        <form id="formEditTask" method="POST">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label text-muted small">Judul</label>
                                <input type="text" name="title" id="detailTitle" class="form-control fw-bold" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Deskripsi</label>
                                <textarea name="description" id="detailDescription" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="text-end mb-3">
                                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                            </div>
                        </form>

                        <hr>
                        
                        {{-- KOMENTAR AREA --}}
                        <div class="d-flex flex-column" style="height: 300px;">
                            <label class="form-label fw-bold"><i class='bx bx-chat'></i> Diskusi</label>
                            <div id="commentList" class="flex-grow-1 mb-2 p-2 bg-light rounded" style="overflow-y: auto;">
                                {{-- Komentar dimuat via AJAX --}}
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" id="commentInput" class="form-control form-control-sm" placeholder="Tulis komentar...">
                                <button type="button" class="btn btn-primary btn-sm" onclick="sendComment()">
                                    <i class='bx bx-send'></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: META DATA & ATTACHMENT --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Prioritas</label>
                            <select id="detailPriority" class="form-select form-select-sm" disabled>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <small class="text-muted" style="font-size: 10px;">*Edit di panel kiri untuk mengubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted">Assignee</label>
                            <input type="text" id="detailAssignee" class="form-control form-control-sm" readonly>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Lampiran</label>
                            <ul class="list-group list-group-flush mb-2" id="attachmentList" style="font-size: 0.8rem;"></ul>
                            <div class="d-flex gap-1">
                                <input type="file" id="fileInput" class="form-control form-control-sm">
                                <button class="btn btn-secondary btn-sm" onclick="uploadFile()"><i class='bx bx-upload'></i></button>
                            </div>
                            <div id="uploadStatus" class="text-muted small mt-1"></div>
                        </div>
                        
                        <hr>
                        <form id="formDeleteTask" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Hapus tugas ini?')">
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
            {{-- Pastikan Route ini ada di web.php --}}
            <form action="{{ route('projects.members.add', $project->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email teman..." required>
                    <button class="btn btn-primary w-100">Kirim Undangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{{-- Library Drag & Drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    let currentTaskId = null;
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. CREATE COLUMN MANUAL (Agar tidak error JSON)
    window.submitColumnManual = function() {
        const nameInput = document.getElementById('colNameInput');
        const btn = nameInput.nextElementSibling;
        
        if(!nameInput.value.trim()) { alert("Nama kosong!"); return; }

        btn.innerHTML = '...';
        btn.disabled = true;

        let formData = new FormData();
        formData.append('name', nameInput.value);
        formData.append('project_id', "{{ $project->id }}");

        fetch("{{ route('columns.store') }}", {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) location.reload();
            else { alert("Gagal"); btn.innerHTML = '<i class="bx bx-plus"></i>'; btn.disabled = false; }
        });
    };

    // 2. DELETE COLUMN
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-column-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-column-btn');
            if(!confirm("Hapus kolom ini?")) return;
            
            fetch(`/columns/${btn.getAttribute('data-id')}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(res => res.json()).then(data => {
                if(data.success) location.reload();
            });
        }
    });

    // 3. OPEN TASK DETAIL (AJAX Load)
    window.openTaskDetail = function(taskId) {
        currentTaskId = taskId;
        
        // Reset Modal
        document.getElementById('detailTitle').value = "Loading...";
        document.getElementById('commentList').innerHTML = "Loading...";
        
        let detailModal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
        detailModal.show();

        // Fetch Data
        fetch(`/tasks/${taskId}`)
        .then(res => res.json())
        .then(data => {
            // Isi Data Form
            document.getElementById('detailTitle').value = data.title;
            document.getElementById('detailDescription').value = data.description || '';
            document.getElementById('detailPriority').value = data.priority;
            document.getElementById('detailAssignee').value = data.assignee ? data.assignee.name : 'Unassigned';
            
            // Setup Action Form
            document.getElementById('formEditTask').action = `/tasks/${taskId}`;
            document.getElementById('formDeleteTask').action = `/tasks/${taskId}`;

            // Render Attachment
            let attachHtml = '';
            if(data.attachments && data.attachments.length > 0) {
                data.attachments.forEach(file => {
                    attachHtml += `<li class="list-group-item px-0 py-1"><a href="/storage/${file.file_path}" target="_blank"><i class="bx bx-file"></i> ${file.file_name}</a></li>`;
                });
            } else { attachHtml = '<li class="text-muted small fst-italic">Tidak ada lampiran.</li>'; }
            document.getElementById('attachmentList').innerHTML = attachHtml;

            // Render Comments
            loadComments(data.comments);
        });
    };

    // 4. COMMENTS LOGIC
    function loadComments(comments) {
        let html = '';
        if(comments && comments.length > 0) {
            comments.forEach(c => {
                html += `
                    <div class="mb-2 pb-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold text-primary">${c.user ? c.user.name : 'User'}</small>
                            <small class="text-muted" style="font-size:10px;">${new Date(c.created_at).toLocaleTimeString()}</small>
                        </div>
                        <p class="mb-0 small text-dark">${c.content}</p>
                    </div>
                `;
            });
        } else { html = '<div class="text-center small text-muted mt-3">Belum ada diskusi.</div>'; }
        document.getElementById('commentList').innerHTML = html;
    }

    window.sendComment = function() {
        let input = document.getElementById('commentInput');
        let content = input.value;
        if(!content) return;

        let formData = new FormData();
        formData.append('content', content);
        
        fetch(`/tasks/${currentTaskId}/comments`, {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        }).then(res => res.json()).then(data => {
            if(data.success) {
                // Append manual tanpa reload full
                let list = document.getElementById('commentList');
                let newHtml = `
                    <div class="mb-2 pb-2 border-bottom bg-label-success p-1 rounded">
                        <small class="fw-bold">Saya</small>
                        <p class="mb-0 small">${data.data.content}</p>
                    </div>`;
                list.insertAdjacentHTML('beforeend', newHtml);
                input.value = '';
                list.scrollTop = list.scrollHeight;
            }
        });
    };

    // 5. UPLOAD FILE
    window.uploadFile = function() {
        let fileInput = document.getElementById('fileInput');
        if(fileInput.files.length === 0) return;

        let formData = new FormData();
        formData.append('file', fileInput.files[0]);

        document.getElementById('uploadStatus').innerText = "Uploading...";

        fetch(`/tasks/${currentTaskId}/attachments`, {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        }).then(res => res.json()).then(data => {
            document.getElementById('uploadStatus').innerText = "Berhasil!";
            let list = document.getElementById('attachmentList');
            let item = `<li class="list-group-item px-0 py-1 bg-label-success"><a href="${data.url}" target="_blank"><i class="bx bx-file"></i> ${data.data.file_name}</a></li>`;
            list.insertAdjacentHTML('beforeend', item);
        });
    };

    // 6. INITIALIZE DRAG & DROP
    document.addEventListener('DOMContentLoaded', function() {
        var containers = document.querySelectorAll('.kanban-tasks');
        containers.forEach(function (container) {
            new Sortable(container, {
                group: 'kanban-board', 
                animation: 150,
                ghostClass: 'bg-label-primary',
                onEnd: function (evt) {
                    var taskId = evt.item.getAttribute('data-task-id');
                    var newColumnId = evt.to.getAttribute('data-column-id');
                    var newPosition = evt.newIndex + 1; 

                    // Kirim update posisi ke server
                    let formData = new FormData();
                    formData.append('task_id', taskId);
                    formData.append('column_id', newColumnId);
                    formData.append('new_position', newPosition);

                    fetch("{{ route('tasks.move') }}", {
                        method: "POST",
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                }
            });
        });
    });

    // Helper Modal Task
    window.openAddTaskModal = function(colId) {
        document.getElementById('modalColumnId').value = colId;
        new bootstrap.Modal(document.getElementById('addTaskModal')).show();
    }
</script>
@endpush

@endsection