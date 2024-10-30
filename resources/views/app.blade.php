<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mini Project Tracker</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <style>
            .judul-pointer {
                cursor: pointer !important;
            }

            .card-project {
                margin-bottom: 1.5rem;
            }
        </style>
    </head>
    <body class="container">
        <div class="py-5 my-5">
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-primary" id="addProject">Add Project</button>
                    <button class="btn btn-dark" id="addTask">Add Task</button>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Project Tracker</h5>
                    <hr>
                    
                    <div class="" id="alertForm">
                    </div>

                    <div class="row" id="projectData">
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="modalFormLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" id="formContent">
            </div>
        </div>



        <!-- Bawaan dari bootstrap, tapi script js saya pakai vanila js -->
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        
        <!-- Javascript yang saya buat -->
        <script>
            // Global variabel
            let dataOptionProject = '';
            var contentModal = new bootstrap.Modal(document.getElementById("modalForm"), {});
            contentModal._config.backdrop = 'static';

            // Get data project
            getDataProject(`{{ url('/api/project') }}`);

            // Form add project
            const buttonAddProject = document.getElementById('addProject');
            buttonAddProject.addEventListener('click', function() {
                let html = `<form id="formAddProject">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalFormLabel">Add Project</h5>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="namaProject">Nama Project <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="namaProject"  autocomplete="off" placeholder="Masukkan nama project">
                                            <small id="errorNama" class="form-text text-danger"></small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </form>`;

                document.getElementById('formContent').innerHTML = html;
                contentModal.show();
                
                document.getElementById('formAddProject').addEventListener('submit', function(event) {
                    event.preventDefault();

                    const formData = {
                        nama: document.getElementById('namaProject').value,
                    };
    
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', `{{ url('/api/project') }}`, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 201) {
                                const dt = JSON.parse(xhr.responseText);
                                document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                            ${dt.message}
                                                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                        </div>`;

                                getDataProject(`{{ url('/api/project') }}`);

                                contentModal.hide();
                            } else if (xhr.status === 400) {
                                const dt = JSON.parse(xhr.responseText);
                                if (dt.errors.nama) {
                                    document.getElementById('errorNama').innerHTML = dt.errors.nama[0];
                                }
                            } else {
                                document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                            Terjadi kesalahan sistem
                                                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                        </div>`;
                                console.error('Error:', xhr.status, xhr.statusText);
                                contentModal.hide();
                            }
                        }
                    };
                    xhr.send(JSON.stringify(formData));
                });
            });

            // Form add task
            const buttonAddTask = document.getElementById('addTask');
            buttonAddTask.addEventListener('click', function() {
                if (dataOptionProject === "") {
                    document.getElementById('alertForm').innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                        Data project tidak ada
                                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>`;

                    contentModal.hide();
                    return;
                }

                let html = `<form id="formAddTask">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalFormLabel">Add Task</h5>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="namaTask">Nama Task <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="namaTask"  autocomplete="off" placeholder="Masukkan nama task">
                                            <small id="errorNama" class="form-text text-danger"></small>
                                        </div>
                                        <div class="form-group">
                                            <label for="projectId">Project <span class="text-danger">*</span></label>
                                            <select class="form-control" id="projectId">
                                                ${dataOptionProject}
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="statusTask">Status <span class="text-danger">*</span></label>
                                            <select class="form-control" id="statusTask">
                                                <option value="Draft">Draft</option>
                                                <option value="In Progress">In Progress</option>
                                                <option value="Done">Done</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </form>`;

                document.getElementById('formContent').innerHTML = html;
                contentModal.show();
                
                document.getElementById('formAddTask').addEventListener('submit', function(event) {
                    event.preventDefault();

                    const formData = {
                        nama: document.getElementById('namaTask').value,
                        projects_id: document.getElementById('projectId').value,
                        status: document.getElementById('statusTask').value,
                    };
    
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', `{{ url('/api/task') }}`, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 201) {
                                const dt = JSON.parse(xhr.responseText);
                                document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                            ${dt.message}
                                                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                        </div>`;

                                getDataProject(`{{ url('/api/project') }}`);

                                contentModal.hide();
                            } else if (xhr.status === 400) {
                                const dt = JSON.parse(xhr.responseText);
                                if (dt.errors.nama) {
                                    document.getElementById('errorNama').innerHTML = dt.errors.nama[0];
                                }
                            } else {
                                document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                            Terjadi kesalahan sistem
                                                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                        </div>`;
                                console.error('Error:', xhr.status, xhr.statusText);
                                contentModal.hide();
                            }
                        }
                    };
                    xhr.send(JSON.stringify(formData));
                });
            });
            
            // Project
            function getDataProject(url) {
                // Reset option
                dataOptionProject = '';

                const xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);
                xhr.onreadystatechange = function () {
                    // Jika berhasil
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const dt = JSON.parse(xhr.responseText);
                        let data = dt.data;
                        const elementProject = document.getElementById('projectData');

                        if (data.length === 0) {
                            elementProject.innerHTML = `<div class="col-md-12 text-center">
                                                            <span>Data project tidak ada</span>
                                                        </div>`;
                        } else {
                            let html = '';
                            data.forEach(val => {
                                dataOptionProject += `<option value="${val.id}">${val.nama}</option>`

                                let contentTask = '';
                                let task = val.tasks;
                                if (task.length == 0) {
                                    contentTask += '<li class="list-group-item">Tidak ada task</li>';
                                } else {
                                    val.tasks.forEach((task, index) => {
                                        contentTask += `<li class="list-group-item judul-pointer d-flex justify-content-between" onclick="showTask(${task.id})"><span>${index+1}. ${task.nama}</span><span>${task.status}</span></li>`;
                                    });
                                }

                                html += `<div class="col-md-4">
                                            <div class="card card-project">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-9">
                                                            <h5 class="card-title mb-0 judul-pointer" onclick="showProject(${val.id})">${val.nama}</h5>
                                                        </div>
                                                        <div class="col-md-3 text-right">
                                                            <button type="button" class="btn btn-outline-dark btn-sm" onclick="addProjectTask(${val.id})">+</button>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">Status</div>
                                                        <div class="col-md-8">: ${val.status ?? '-'}</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">Progress</div>
                                                        <div class="col-md-8">: ${val.progress ?? 0} %</div>
                                                    </div>
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    ${contentTask}
                                                </ul>
                                            </div>
                                        </div>`;
                            });

                            elementProject.innerHTML = html;
                        }
                    } else if (xhr.readyState === 4) {
                        console.error('Error :', xhr.status, xhr.statusText);
                    }
                };
                xhr.send();
            }

            function addProjectTask(id) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET',`{{ url('/api/project') }}/${id}` , true);
                xhr.onreadystatechange = function () {
                    // Jika berhasil
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const dt = JSON.parse(xhr.responseText);
                        let data = dt.data;

                        let html = `<div id="projectForm" class="modal-content">
                                        <form id="formAddTaskProject">
                                            <input type="hidden" id="idProject" value="${data.id}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalFormLabel">Add Task Project</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="namaTask">Nama Task <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="namaTask" autocomplete="off" placeholder="Masukkan nama task">
                                                        <small id="errorNama" class="form-text text-danger"></small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <input type="text" class="form-control" value="${data.nama}" autocomplete="off" placeholder="Masukkan nama project" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="statusTask">Status <span class="text-danger">*</span></label>
                                                        <select class="form-control" id="statusTask">
                                                            <option value="Draft">Draft</option>
                                                            <option value="In Progress">In Progress</option>
                                                            <option value="Done">Done</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>`;

                        document.getElementById('formContent').innerHTML = html;
                        contentModal.show();

                        // Add Task 
                        document.getElementById('formAddTaskProject').addEventListener('submit', function(event) {
                            event.preventDefault();

                            const formData = {
                                nama: document.getElementById('namaTask').value,
                                projects_id: document.getElementById('idProject').value,
                                status: document.getElementById('statusTask').value,
                            };
            
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', `{{ url('/api/task') }}`, true);
                            xhr.setRequestHeader('Content-Type', 'application/json');
            
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 201) {
                                        const dt = JSON.parse(xhr.responseText);
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                                    ${dt.message}
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;

                                        getDataProject(`{{ url('/api/project') }}`);

                                        contentModal.hide();
                                    } else if (xhr.status === 400) {
                                        const dt = JSON.parse(xhr.responseText);
                                        if (dt.errors.nama) {
                                            document.getElementById('errorNama').innerHTML = dt.errors.nama[0];
                                        }
                                    } else {
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                                    Terjadi kesalahan sistem
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;
                                        console.error('Error:', xhr.status, xhr.statusText);
                                        contentModal.hide();
                                    }
                                }
                            };
                            xhr.send(JSON.stringify(formData));
                        });

                    } else if (xhr.readyState === 4) {
                        console.error('Error :', xhr.status, xhr.statusText);
                    }
                };
                xhr.send();
            }

            function showProject(id) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET',`{{ url('/api/project') }}/${id}` , true);
                xhr.onreadystatechange = function () {
                    // Jika berhasil
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const dt = JSON.parse(xhr.responseText);
                        let data = dt.data;

                        let html = `<div class="modal-content" id="projectDetail" style="display: block;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalFormLabel">Detail Project</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3">Nama</div>
                                                <div class="col-md-9">: ${data.nama}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">Status</div>
                                                <div class="col-md-9">: ${data.status ?? '-'}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3">Progress</div>
                                                <div class="col-md-9">: ${data.progress ?? 0} %</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-warning" id="buttonPerbaruiProject">Perbarui</button>
                                            <button type="button" class="btn btn-danger" onclick="deleteProject(${data.id})">Hapus</button>
                                        </div>
                                    </div>
                                    <div id="projectForm" class="modal-content" style="display: none;">
                                        <form id="formUpdateProject">
                                            <input type="hidden" id="idProject" value="${data.id}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalFormLabel">Perbarui Project</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="namaProject">Nama Project <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="namaProject" value="${data.nama}" autocomplete="off" placeholder="Masukkan nama project">
                                                        <small id="errorNama" class="form-text text-danger"></small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>`;

                        document.getElementById('formContent').innerHTML = html;
                        contentModal.show();

                        // Perbarui 
                        document.getElementById('formUpdateProject').addEventListener('submit', function(event) {
                            event.preventDefault();

                            const idProject = document.getElementById('idProject').value;
                            const formData = {
                                nama: document.getElementById('namaProject').value,
                            };
            
                            const xhr = new XMLHttpRequest();
                            xhr.open('PUT', `{{ url('/api/project') }}/${idProject}`, true);
                            xhr.setRequestHeader('Content-Type', 'application/json');
            
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 200) {
                                        const dt = JSON.parse(xhr.responseText);
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                                    ${dt.message}
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;

                                        getDataProject(`{{ url('/api/project') }}`);

                                        contentModal.hide();
                                    } else if (xhr.status === 400) {
                                        const dt = JSON.parse(xhr.responseText);
                                        if (dt.errors.nama) {
                                            document.getElementById('errorNama').innerHTML = dt.errors.nama[0];
                                        }
                                    } else {
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                                    Terjadi kesalahan sistem
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;
                                        console.error('Error:', xhr.status, xhr.statusText);
                                        contentModal.hide();
                                    }
                                }
                            };
                            xhr.send(JSON.stringify(formData));
                        });

                        document.getElementById('buttonPerbaruiProject').addEventListener('click', function(event) {
                            document.getElementById('projectDetail').style.display = 'none';
                            document.getElementById('projectForm').style.display = 'block';
                        });

                    } else if (xhr.readyState === 4) {
                        console.error('Error :', xhr.status, xhr.statusText);
                    }
                };
                xhr.send();
            }

            function deleteProject(id) {
                // Pakai alert bawaan
                if (confirm("Yakin, akan menghapus data project ?")) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('DELETE',`{{ url('/api/project') }}/${id}` , true);
                    xhr.onreadystatechange = function () {
                        // Jika berhasil
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const dt = JSON.parse(xhr.responseText);
                            document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                        ${dt.message}
                                                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>`;
                
                            getDataProject(`{{ url('/api/project') }}`);
                
                            contentModal.hide();
                        } else if (xhr.readyState === 4) {
                            console.error('Error :', xhr.status, xhr.statusText);
                            document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                                Terjadi kesalahan sistem
                                                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                    <span aria-hidden="true">&times;</span>
                                                                                                </button>
                                                                                            </div>`;
                            contentModal.hide();
                        }
                    };
                    xhr.send();
                }
            }

            // Task 
            function showTask(id) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET',`{{ url('/api/task') }}/${id}` , true);
                xhr.onreadystatechange = function () {
                    // Jika berhasil
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const dt = JSON.parse(xhr.responseText);
                        let data = dt.data;

                        let html = `<div class="modal-content" id="taskDetail" style="display: block;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalFormLabel">Detail Task</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-3">Nama</div>
                                                <div class="col-md-9">: ${data.nama}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">Status</div>
                                                <div class="col-md-9">: ${data.status ?? '-'}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3">Bobot</div>
                                                <div class="col-md-9">: ${data.bobot}</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-warning" id="buttonPerbaruiTask">Perbarui</button>
                                            <button type="button" class="btn btn-danger" onclick="deleteTask(${data.id})">Hapus</button>
                                        </div>
                                    </div>
                                    <div id="taskForm" class="modal-content" style="display: none;">
                                        <form id="formUpdateProject">
                                            <input type="hidden" id="idTask" value="${data.id}">
                                            <input type="hidden" id="idProject" value="${data.projects_id}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalFormLabel">Perbarui Task</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="namaTask">Nama Task <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="namaTask" value="${data.nama}" autocomplete="off" placeholder="Masukkan nama task">
                                                        <small id="errorNama" class="form-text text-danger"></small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="statusTask">Status <span class="text-danger">*</span></label>
                                                        <select class="form-control" id="statusTask">
                                                            <option value="Draft" ${data.status == "Draft" ? 'selected' : ''}>Draft</option>
                                                            <option value="In Progress" ${data.status == "In Progress" ? 'selected' : ''}>In Progress</option>
                                                            <option value="Done" ${data.status == "Done" ? 'selected' : ''}>Done</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>`;

                        document.getElementById('formContent').innerHTML = html;
                        contentModal.show();

                        // Perbarui 
                        document.getElementById('formUpdateProject').addEventListener('submit', function(event) {
                            event.preventDefault();

                            const idTask = document.getElementById('idTask').value;
                            const formData = {
                                nama: document.getElementById('namaTask').value,
                                projects_id: document.getElementById('idProject').value,
                                status: document.getElementById('statusTask').value,
                            };
            
                            const xhr = new XMLHttpRequest();
                            xhr.open('PUT', `{{ url('/api/task') }}/${idTask}`, true);
                            xhr.setRequestHeader('Content-Type', 'application/json');
            
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 200) {
                                        const dt = JSON.parse(xhr.responseText);
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                                    ${dt.message}
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;

                                        getDataProject(`{{ url('/api/project') }}`);

                                        contentModal.hide();
                                    } else if (xhr.status === 400) {
                                        const dt = JSON.parse(xhr.responseText);
                                        if (dt.errors.nama) {
                                            document.getElementById('errorNama').innerHTML = dt.errors.nama[0];
                                        }
                                    } else {
                                        document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                                    Terjadi kesalahan sistem
                                                                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>`;
                                        console.error('Error:', xhr.status, xhr.statusText);
                                        contentModal.hide();
                                    }
                                }
                            };
                            xhr.send(JSON.stringify(formData));
                        });

                        document.getElementById('buttonPerbaruiTask').addEventListener('click', function(event) {
                            document.getElementById('taskDetail').style.display = 'none';
                            document.getElementById('taskForm').style.display = 'block';
                        });

                    } else if (xhr.readyState === 4) {
                        console.error('Error :', xhr.status, xhr.statusText);
                    }
                };
                xhr.send();
            }
            
            function deleteTask(id) {
                // Pakai alert bawaan
                if (confirm("Yakin, akan menghapus data task ?")) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('DELETE',`{{ url('/api/task') }}/${id}` , true);
                    xhr.onreadystatechange = function () {
                        // Jika berhasil
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const dt = JSON.parse(xhr.responseText);
                            document.getElementById('alertForm').innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                                                                                        ${dt.message}
                                                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>`;
                
                            getDataProject(`{{ url('/api/project') }}`);
                
                            contentModal.hide();
                        } else if (xhr.readyState === 4) {
                            console.error('Error :', xhr.status, xhr.statusText);
                            document.getElementById('alertForm').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                                                                Terjadi kesalahan sistem
                                                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                    <span aria-hidden="true">&times;</span>
                                                                                                </button>
                                                                                            </div>`;
                            contentModal.hide();
                        }
                    };
                    xhr.send();
                }
            }
        </script>

    </body>
</html>
