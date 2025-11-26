/**
 * Admin Students Management JavaScript
 */

$(document).ready(function() {
    loadStudents();
    
    $('#addStudentBtn').on('click', function() {
        openModal('addStudentModal');
    });
    
    $('#importBtn').on('click', function() {
        openModal('importModal');
    });
    
    $('#addStudentForm').on('submit', function(e) {
        e.preventDefault();
        addStudent();
    });
    
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        importStudents();
    });
});

function loadStudents() {
    apiCall('students.php')
        .then(data => {
            if (data.success) {
                displayStudents(data.data);
            } else {
                alert('Failed to load students');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function displayStudents(students) {
    const tbody = $('#studentsTable tbody');
    tbody.empty();
    
    if (students.length === 0) {
        tbody.html('<tr><td colspan="7">No students found</td></tr>');
        return;
    }
    
    students.forEach(student => {
        const row = $(`
            <tr>
                <td>${student.id}</td>
                <td>${student.username}</td>
                <td>${student.email}</td>
                <td>${student.first_name}</td>
                <td>${student.last_name}</td>
                <td>${formatDate(student.created_at)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteStudent(${student.id})">Delete</button>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function addStudent() {
    const data = {
        username: $('#username').val(),
        email: $('#email').val(),
        first_name: $('#first_name').val(),
        last_name: $('#last_name').val(),
        password: $('#password').val()
    };
    
    apiCall('students.php', 'POST', data)
        .then(result => {
            if (result.success) {
                alert('Student added successfully');
                closeModal('addStudentModal');
                $('#addStudentForm')[0].reset();
                loadStudents();
            } else {
                alert('Failed to add student: ' + result.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function deleteStudent(studentId) {
    if (!confirm('Are you sure you want to delete this student?')) {
        return;
    }
    
    apiCall(`students.php?id=${studentId}`, 'DELETE')
        .then(result => {
            if (result.success) {
                alert('Student deleted successfully');
                loadStudents();
            } else {
                alert('Failed to delete student: ' + result.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function importStudents() {
    const fileInput = $('#importFile')[0];
    
    if (!fileInput.files.length) {
        alert('Please select a file');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    fetch('../api/import_export.php?action=import', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Import successful! ${data.imported} students imported.${data.errors.length > 0 ? '\nErrors: ' + data.errors.join(', ') : ''}`);
            closeModal('importModal');
            $('#importForm')[0].reset();
            loadStudents();
        } else {
            alert('Import failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

