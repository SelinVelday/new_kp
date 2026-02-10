@extends('layouts.master')

@section('title', $project->name . ' - Kanban Board')

@push('styles')
<style>
    /* Styling Container Board */
    .kanban-board-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding-bottom: 1rem;
        height: calc(100vh - 280px);
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
        min-height: 100px;
    }
    /* Scrollbar Cantik */
    .kanban-tasks::-webkit-scrollbar { width: 6px; }
    .kanban-tasks::-webkit-scrollbar-track { background: transparent; }
    .kanban-tasks::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    
    /* Drag & Drop Visuals */
    .sortable-ghost { opacity: 0.4; background-color: #f0f0f0; border: 2px dashed #ccc; } 
    .sortable-drag { cursor: grabbing; opacity: 1; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.15); transform: rotate(2deg); }
    
    .task-card { transition: all 0.2s ease; border-left: 3px solid transparent; }
    .task-card:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* Border warna prioritas */
    .priority-high { border-left-color: #ff3e1d !important; }
    .priority-medium { border-left-color: #ffab00 !important; }
    .priority-low { border-left-color: #03c3ec !important; }
</style>
@endpush

@section('content')
<div class="container-fluid h-100 d-flex flex-column">
    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER: Judul & Progress --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-primary">{{ $project->name }}</h4>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">{{ $project->description ?? 'Tidak ada deskripsi.' }}</span>
                        <span class="badge bg-label-success">Active <i class='bx bx-wifi'></i></span>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                            @foreach($project->members->take(5) as $member)
                            <li data-bs-toggle="tooltip" title="{{ $member->name }}" class="avatar avatar-xs pull-up">
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
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                        <i class="bx bx-user-plus me-1"></i> Invite
                    </button>
                </div>
            </div>

            {{-- PROGRESS BAR SECTION --}}
            @php
                $progress = $project->progress; 
            @endphp
            <div class="d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold text-muted">Progress Project</small>
                        {{-- ID untuk update via JS --}}
                        <small class="fw-bold text-primary" id="prog-percent-text">{{ $progress['percentage'] }}%</small>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #eceef1;">
                        {{-- ID untuk update via JS --}}
                        <div id="prog-bar" 
                             class="progress-bar bg-primary shadow-none" 
                             role="progressbar" 
                             style="width: {{ $progress['percentage'] }}%; transition: width 0.5s ease;" 
                             aria-valuenow="{{ $progress['percentage'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <div class="text-end ps-3 border-start">
                    {{-- ID untuk update via JS --}}
                    <h6 class="mb-0 fw-bold" id="prog-count-text">{{ $progress['done'] }}/{{ $progress['total'] }}</h6>
                    <small class="text-muted" style="font-size: 10px;">TASK SELESAI</small>
                </div>
            </div>
        </div>
    </div>

    {{-- KANBAN BOARD --}}
    <div class="kanban-board-container pb-3" id="kanban-wrapper">
        @foreach($project->columns as $column)
        <div class="kanban-column" id="column-wrapper-{{ $column->id }}">
            <div class="card h-100 shadow-sm border-0 bg-label-secondary" style="background-color: #f5f5f9;">
                <div class="card-header d-flex justify-content-between align-items-center p-3 bg-white border-bottom rounded-top">
                    <div class="d-flex align-items-center gap-2">
                        {{-- Class 'column-title' penting untuk deteksi progress bar --}}
                        <h6 class="m-0 fw-bold text-uppercase fs-7 text-dark column-title">{{ $column->name }}</h6>
                        <span class="badge bg-label-primary rounded-pill task-count-badge">{{ $column->tasks->count() }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0 text-muted" type="button" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger delete-column-btn" href="javascript:void(0);" data-id="{{ $column->id }}"><i class="bx bx-trash me-1"></i> Hapus Kolom</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body p-2 kanban-tasks" id="col-{{ $column->id }}" data-column-id="{{ $column->id }}">
                    @foreach($column->tasks->sortBy('position') as $task)
                        <div class="card shadow-sm border-0 cursor-pointer task-card mb-2 priority-{{ $task->priority }}" 
                             id="card-task-{{ $task->id }}"
                             data-task-id="{{ $task->id }}" 
                             onclick="openTaskDetail({{ $task->id }})">
                            <div class="card-body p-3 bg-white rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    @php
                                        $badgeClass = match($task->priority) {
                                            'high' => 'bg-label-danger',
                                            'medium' => 'bg-label-warning',
                                            default => 'bg-label-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill" style="font-size: 10px;">{{ strtoupper($task->priority) }}</span>
                                    @if($task->due_date)
                                        @php
                                            $isOverdue = \Carbon\Carbon::parse($task->due_date)->isPast() && !in_array(strtolower($column->name), ['done', 'selesai', 'complete']);
                                        @endphp
                                        <small class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 11px;">
                                            <i class="bx bx-calendar {{ $isOverdue ? 'bx-tada' : '' }}"></i> 
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('d M') }}
                                        </small>
                                    @endif
                                </div>
                                
                                <h6 class="mb-2 text-dark task-title fw-semibold" style="font-size: 0.95rem;">{{ $task->title }}</h6>
                                
                                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-dashed">
                                    <div class="d-flex gap-2">
                                        <small class="text-muted comment-indicator" style="{{ $task->comments_count > 0 ? '' : 'display:none' }}">
                                            <i class='bx bx-message-rounded'></i> <span class="comment-count">{{ $task->comments_count }}</span>
                                        </small>
                                        @if($task->attachments_count > 0)
                                        <small class="text-muted"><i class='bx bx-paperclip'></i> {{ $task->attachments_count }}</small>
                                        @endif
                                    </div>
                                    <div class="avatar avatar-xs assignee-avatar" style="{{ $task->assigned_to ? '' : 'display:none' }}">
                                        @if($task->assigned_to)
                                            <img src="{{ $task->assignee->avatar ? asset('storage/'.$task->assignee->avatar) : asset('assets/img/avatars/1.png') }}" class="rounded-circle" style="object-fit: cover;" title="{{ $task->assignee->name }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-footer p-2 bg-transparent border-top-0">
                    <button class="btn btn-label-secondary btn-sm w-100 fw-semibold" onclick="openAddTaskModal({{ $column->id }})"><i class="bx bx-plus"></i> Tambah Tugas</button>
                </div>
            </div>
        </div>
        @endforeach

        <div style="min-width: 300px;">
            <div class="card shadow-none bg-transparent border border-2 border-dashed h-auto d-flex justify-content-start">
                <div class="card-body p-3">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="colNameInput" placeholder="Tambah kolom...">
                        <button class="btn btn-primary btn-sm" type="button" onclick="submitColumnManual(this)"><i class="bx bx-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Tugas Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="column_id" id="modalColumnId">
                    <div class="mb-3"><label class="form-label">Judul</label><input type="text" class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">Prioritas</label><select class="form-select" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div>
                    <div class="mb-3"><label class="form-label">Assign</label><select class="form-select" name="assigned_to"><option value="">-- Pilih --</option>@foreach($project->members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Due Date</label><input type="date" class="form-control" name="due_date"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="taskDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="min-height: 500px;">
            <div class="modal-header border-bottom"><h5 class="modal-title">Detail Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <div class="col-md-8 p-4 border-end">
                        <form id="formEditTask" onsubmit="updateTaskAjax(event)">
                            <div class="mb-3"><label class="form-label text-muted small fw-bold">JUDUL</label><input type="text" name="title" id="detailTitle" class="form-control fw-bold" required></div>
                            <div class="row mb-3">
                                <div class="col-md-6"><label class="form-label text-muted small fw-bold">PRIORITAS</label><select name="priority" id="detailPriority" class="form-select form-select-sm"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select></div>
                                <div class="col-md-6"><label class="form-label text-muted small fw-bold">ASSIGNEE</label><select name="assigned_to" id="detailAssigneeInput" class="form-select form-select-sm"><option value="">Unassigned</option>@foreach($project->members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div>
                            </div>
                            <div class="mb-3"><label class="form-label text-muted small fw-bold">DESKRIPSI</label><textarea name="description" id="detailDescription" class="form-control" rows="3"></textarea></div>
                            <div class="d-flex justify-content-end mb-4"><button type="submit" class="btn btn-primary btn-sm" id="btnSaveTask"><i class="bx bx-save"></i> Simpan</button></div>
                        </form>
                        <hr class="my-4">
                        <div>
                            <h6 class="fw-bold mb-3"><i class='bx bx-chat'></i> Diskusi</h6>
                            <div id="commentList" class="bg-light rounded p-3 mb-3" style="max-height: 250px; overflow-y: auto;"></div>
                            <div class="d-flex gap-2"><input type="text" id="commentInput" class="form-control" placeholder="Tulis komentar..." onkeypress="if(event.key === 'Enter') sendComment()"><button type="button" class="btn btn-primary" onclick="sendComment()"><i class='bx bx-send'></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-4 p-4 bg-light">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">LAMPIRAN</label>
                            <ul class="list-group list-group-flush mb-2 bg-white rounded shadow-sm" id="attachmentList"></ul>
                            <div class="mt-2"><label for="fileInput" class="btn btn-outline-secondary btn-sm w-100 text-start"><i class='bx bx-paperclip me-1'></i> Upload File</label><input type="file" id="fileInput" class="d-none" onchange="uploadFile()"></div>
                            <div id="uploadStatus" class="text-muted small mt-1 fst-italic"></div>
                        </div>
                        <div class="mb-4"><label class="form-label small fw-bold text-muted">TENGGAT WAKTU</label><input type="text" class="form-control form-control-sm bg-white" id="detailDueDateDisplay" readonly></div>
                        <hr>
                        <form id="formDeleteTask" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-label-danger btn-sm w-100" onclick="return confirm('Hapus tugas?')"><i class='bx bx-trash'></i> Hapus Tugas</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Undang Anggota</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('projects.members.add', $project->id) }}" method="POST">@csrf<div class="modal-body"><input type="email" name="email" class="form-control mb-3" placeholder="Email..." required><button class="btn btn-primary w-100">Kirim</button></div></form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    const projectId = "{{ $project->id }}";
    const userId = "{{ Auth::id() }}";
    // PERBAIKAN 1: Ambil nama user untuk fallback check
    const userName = "{{ Auth::user()->name }}"; 
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentTaskId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Hitung progress saat awal load
        updateProgressBar();

        if (window.Echo) {
            console.log("Listening to channel: projects." + projectId);
            
            window.Echo.private('projects.' + projectId)
                .listen('.task.moved', (e) => {
                    moveTaskRealtime(e.task_id, e.column_id, e.new_position);
                })
                .listen('.comment.added', (e) => {
                    handleNewComment(e);
                });
        }
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) });

        // Init Sortable
        var containers = document.querySelectorAll('.kanban-tasks');
        containers.forEach(function (container) {
            new Sortable(container, {
                group: 'kanban-board', 
                animation: 150,
                delay: 100,
                delayOnTouchOnly: true,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function (evt) {
                    var taskId = evt.item.getAttribute('data-task-id');
                    var newColumnId = evt.to.getAttribute('data-column-id');
                    var newPosition = evt.newIndex + 1; 

                    updateTaskCounts(); 
                    updateProgressBar(); // UPDATE PROGRESS

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

    // --- FUNGSI UPDATE PROGRESS BAR REALTIME ---
    function updateProgressBar() {
        const totalCards = document.querySelectorAll('.task-card').length;
        let doneCards = 0;

        // Cari kolom yang mengandung kata 'done', 'selesai', 'complete', 'beres'
        document.querySelectorAll('.kanban-column').forEach(col => {
            const titleElement = col.querySelector('.column-title');
            if (titleElement) {
                const title = titleElement.innerText.toLowerCase();
                if (title.includes('done') || title.includes('selesai') || title.includes('complete') || title.includes('beres')) {
                    doneCards += col.querySelectorAll('.task-card').length;
                }
            }
        });

        let percent = 0;
        if (totalCards > 0) {
            percent = Math.round((doneCards / totalCards) * 100);
        }

        const bar = document.getElementById('prog-bar');
        const txtPercent = document.getElementById('prog-percent-text');
        const txtCount = document.getElementById('prog-count-text');

        if(bar) {
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', percent);
        }
        if(txtPercent) txtPercent.innerText = percent + '%';
        if(txtCount) txtCount.innerText = `${doneCards}/${totalCards}`;
    }

    function moveTaskRealtime(taskId, targetColumnId, newPosition) {
        const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
        const targetColumn = document.querySelector(`[data-column-id="${targetColumnId}"]`);

        // Cek jika kartu sudah ada di kolom tujuan (mencegah glitch visual jika event datang lambat)
        if (taskCard && targetColumn && taskCard.parentElement.getAttribute('data-column-id') == targetColumnId) {
            return;
        }

        if (taskCard && targetColumn) {
            targetColumn.appendChild(taskCard); 
            taskCard.classList.add('bg-label-warning');
            setTimeout(() => taskCard.classList.remove('bg-label-warning'), 1000);
            updateTaskCounts();
            updateProgressBar(); // Update progress bar saat event realtime
        }
    }

    function updateTaskCounts() {
        document.querySelectorAll('.kanban-tasks').forEach(col => {
            const count = col.children.length;
            const headerBadge = col.closest('.card').querySelector('.task-count-badge');
            if(headerBadge) headerBadge.innerText = count;
        });
    }

    // --- FIX DOUBLE CHAT ---
    function handleNewComment(data) {
        // PERBAIKAN 2: Cek apakah chat ini dari "Saya" (Selin Velday)?
        
        // Cek 1: Berdasarkan ID (Jika backend mengirim user_id)
        if (data.user_id && String(data.user_id) === String(userId)) {
            return; 
        }

        // Cek 2: Berdasarkan Nama (Fallback jika backend lupa ID)
        // Ini mengatasi masalah jika ID tidak cocok/kosong, tapi namanya sama.
        if (data.user_name && data.user_name === userName) {
            return;
        }

        // --- Proses render chat orang lain ---
        const card = document.querySelector(`[data-task-id="${data.task_id}"]`);
        if(card) {
            const indicator = card.querySelector('.comment-indicator');
            const counter = card.querySelector('.comment-count');
            indicator.style.display = 'inline-block';
            counter.innerText = parseInt(counter.innerText || 0) + 1;
        }

        if (currentTaskId == data.task_id) {
            const list = document.getElementById('commentList');
            if(list.innerText.includes('Belum ada diskusi')) list.innerHTML = '';

            // Tampilan chat orang lain (Kiri - Putih)
            const html = `
                <div class="d-flex mb-3 animate__animated animate__fadeInLeft">
                    <div class="avatar avatar-xs me-2">
                         <img src="${data.user_avatar ? '/storage/'+data.user_avatar : '/assets/img/avatars/1.png'}" class="rounded-circle">
                    </div>
                    <div class="flex-grow-1">
                        <div class="bg-white p-2 rounded shadow-sm border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-bold text-primary">${data.user_name}</small>
                                <small class="text-muted" style="font-size:10px;">Baru saja</small>
                            </div>
                            <p class="mb-0 small text-dark">${data.content}</p>
                        </div>
                    </div>
                </div>`;
            
            list.insertAdjacentHTML('beforeend', html);
            list.scrollTop = list.scrollHeight;
        }
    }

    // --- HELPER FUNCTIONS ---
    window.submitColumnManual = function(btn) {
        const input = document.getElementById('colNameInput');
        if(!input.value.trim()) { alert("Nama kosong!"); return; }
        btn.disabled = true;
        
        let fd = new FormData(); fd.append('name', input.value); fd.append('project_id', projectId);
        fetch("{{ route('columns.store') }}", { method:"POST", headers:{'X-CSRF-TOKEN':csrfToken}, body:fd })
        .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
    };

    document.addEventListener('click', function(e) {
        if(e.target.closest('.delete-column-btn')){
            if(!confirm("Hapus kolom?")) return;
            let id = e.target.closest('.delete-column-btn').dataset.id;
            fetch(`/columns/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken} })
            .then(r=>r.json()).then(d=>{ if(d.success) document.getElementById(`column-wrapper-${id}`).remove(); });
        }
    });

    window.openTaskDetail = function(taskId) {
        currentTaskId = taskId;
        document.getElementById('detailTitle').value = "Loading...";
        document.getElementById('commentList').innerHTML = '<div class="text-center p-3"><i class="bx bx-loader-alt bx-spin"></i></div>';
        new bootstrap.Modal(document.getElementById('taskDetailModal')).show();

        fetch(`/tasks/${taskId}`).then(r=>r.json()).then(data => {
            document.getElementById('detailTitle').value = data.title;
            document.getElementById('detailDescription').value = data.description || '';
            document.getElementById('detailPriority').value = data.priority;
            document.getElementById('detailAssigneeInput').value = data.assigned_to || '';
            document.getElementById('detailDueDateDisplay').value = data.due_date || '-';
            document.getElementById('formDeleteTask').action = `/tasks/${taskId}`;

            let attachHtml = '';
            if(data.attachments && data.attachments.length > 0) {
                data.attachments.forEach(file => {
                    attachHtml += `<li class="list-group-item d-flex justify-content-between px-2 py-1"><a href="/storage/${file.file_path}" target="_blank"><i class="bx bx-file"></i> ${file.file_name}</a></li>`;
                });
            } else { attachHtml = '<li class="list-group-item text-muted small fst-italic px-2">Tidak ada lampiran.</li>'; }
            document.getElementById('attachmentList').innerHTML = attachHtml;

            // RENDER CHAT HISTORY
            let html = '';
            if(data.comments && data.comments.length > 0) {
                data.comments.forEach(c => {
                    let isMe = String(c.user.id) === String(userId);
                    
                    if(isMe) {
                        // Tampilan Chat Saya (Kanan - Biru)
                        html += `
                        <div class="d-flex mb-3 justify-content-end">
                            <div class="flex-grow-1 text-end">
                                <div class="bg-primary text-white p-2 rounded shadow-sm d-inline-block text-start" style="max-width: 85%;">
                                    <p class="mb-0 small">${c.content}</p>
                                </div>
                                <div class="small text-muted mt-1" style="font-size:10px;">${new Date(c.created_at).toLocaleString()}</div>
                            </div>
                        </div>`;
                    } else {
                        // Tampilan Chat Orang Lain (Kiri - Putih)
                        html += `
                        <div class="d-flex mb-3">
                            <div class="avatar avatar-xs me-2">
                                <img src="${c.user.avatar ? '/storage/'+c.user.avatar : '/assets/img/avatars/1.png'}" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-white p-2 rounded shadow-sm border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-bold text-primary">${c.user.name}</small>
                                        <small class="text-muted" style="font-size:10px;">${new Date(c.created_at).toLocaleString()}</small>
                                    </div>
                                    <p class="mb-0 small text-dark">${c.content}</p>
                                </div>
                            </div>
                        </div>`;
                    }
                });
            } else { html = '<div class="text-center small text-muted mt-3">Belum ada diskusi.</div>'; }
            
            document.getElementById('commentList').innerHTML = html;
            setTimeout(() => {
                const list = document.getElementById('commentList');
                list.scrollTop = list.scrollHeight;
            }, 200);
        });
    };

    window.updateTaskAjax = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveTask');
        btn.innerHTML = '...'; btn.disabled = true;
        let fd = new FormData(document.getElementById('formEditTask')); fd.append('_method', 'PUT');
        
        fetch(`/tasks/${currentTaskId}`, { method:"POST", headers:{'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json'}, body:fd })
        .then(r=>r.json()).then(data => {
            btn.innerHTML = '<i class="bx bx-save"></i> Simpan'; btn.disabled = false;
            
            const card = document.getElementById(`card-task-${currentTaskId}`);
            if(card) {
                card.querySelector('.task-title').innerText = fd.get('title');
                
                const priority = fd.get('priority');
                const badge = card.querySelector('.badge');
                badge.className = 'badge rounded-pill'; // Reset class
                
                if(priority === 'high') badge.classList.add('bg-label-danger');
                else if(priority === 'medium') badge.classList.add('bg-label-warning');
                else badge.classList.add('bg-label-info');
                
                badge.innerText = priority.toUpperCase();
                
                card.classList.remove('priority-high', 'priority-medium', 'priority-low');
                card.classList.add('priority-' + priority);
            }
            showAlert("Berhasil disimpan!", "success");
        });
    };

    window.sendComment = function() {
        let input = document.getElementById('commentInput');
        if(!input.value.trim()) return;
        let fd = new FormData(); fd.append('content', input.value);
        
        let list = document.getElementById('commentList');
        if(list.innerText.includes('Belum ada diskusi')) list.innerHTML = '';
        
        // OPTIMISTIC UI: Langsung tampilkan chat "Saya" di kanan (Biru)
        let myHtml = `
            <div class="d-flex mb-3 justify-content-end animate__animated animate__fadeIn">
                <div class="flex-grow-1 text-end">
                    <div class="bg-primary text-white p-2 rounded shadow-sm d-inline-block text-start" style="max-width: 85%;">
                        <p class="mb-0 small">${input.value}</p>
                    </div>
                    <div class="small text-muted mt-1" style="font-size:10px;">Baru saja</div>
                </div>
            </div>`;
            
        list.insertAdjacentHTML('beforeend', myHtml);
        list.scrollTop = list.scrollHeight;
        input.value = '';

        fetch(`/tasks/${currentTaskId}/comments`, { method:"POST", headers:{'X-CSRF-TOKEN':csrfToken}, body:fd });
    };

    window.uploadFile = function() {
        let input = document.getElementById('fileInput');
        if(input.files.length === 0) return;
        let fd = new FormData(); fd.append('file', input.files[0]);
        document.getElementById('uploadStatus').innerText = "Uploading...";
        
        fetch(`/tasks/${currentTaskId}/attachments`, { method:"POST", headers:{'X-CSRF-TOKEN':csrfToken}, body:fd })
        .then(r=>r.json()).then(d => {
            document.getElementById('uploadStatus').innerText = "";
            let list = document.getElementById('attachmentList');
            if(list.innerText.includes('Tidak ada lampiran')) list.innerHTML = '';
            list.insertAdjacentHTML('beforeend', `<li class="list-group-item bg-label-success px-2 py-1"><a href="${d.url}" target="_blank"><i class="bx bx-check"></i> ${d.data.file_name}</a></li>`);
        });
    };

    window.openAddTaskModal = function(colId) {
        document.getElementById('modalColumnId').value = colId;
        new bootstrap.Modal(document.getElementById('addTaskModal')).show();
    };

    function showAlert(msg, type='success') {
        document.getElementById('alert-container').innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        setTimeout(() => document.getElementById('alert-container').innerHTML='', 3000);
    }
</script>
@endpush

@endsection