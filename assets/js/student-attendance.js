/**
 * Student Attendance Page JavaScript
 */

$(document).ready(function() {
    loadCourseInfo();
    loadAttendanceRecords();
    
    $('#justificationForm').on('submit', function(e) {
        e.preventDefault();
        submitJustification();
    });
});

function loadCourseInfo() {
    apiCall('courses.php')
        .then(data => {
            if (data.success) {
                const course = data.data.find(c => c.id == courseId);
                if (course) {
                    $('#courseInfo').html(`
                        <h2>${course.name}</h2>
                        <p><strong>Code:</strong> ${course.code}</p>
                        <p><strong>Group:</strong> ${course.group_name || 'N/A'}</p>
                    `);
                }
            }
        })
        .catch(error => {
            console.error('Error loading course info:', error);
        });
}

function loadAttendanceRecords() {
    apiCall(`attendance.php?course_id=${courseId}`)
        .then(data => {
            if (data.success) {
                displayAttendanceRecords(data.data);
                calculateStats(data.data);
            } else {
                showError('attendanceTable', 'Failed to load attendance records');
            }
        })
        .catch(error => {
            showError('attendanceTable', error.message);
        });
}

function displayAttendanceRecords(records) {
    const tbody = $('#attendanceTable tbody');
    tbody.empty();
    
    if (records.length === 0) {
        tbody.html('<tr><td colspan="4">No attendance records found</td></tr>');
        return;
    }
    
    records.forEach(record => {
        const justificationBtn = record.justification_id 
            ? `<span class="status-badge status-${record.justification_status}">${record.justification_status}</span>`
            : `<button class="btn btn-primary btn-sm" onclick="openJustificationModal(${record.id})">Submit</button>`;
        
        const row = $(`
            <tr>
                <td>${formatDateTime(record.session_date, record.session_time)}</td>
                <td><span class="status-badge status-${record.status}">${record.status.toUpperCase()}</span></td>
                <td>${justificationBtn}</td>
                <td>
                    ${record.status === 'absent' && !record.justification_id ? 
                        `<button class="btn btn-primary btn-sm" onclick="openJustificationModal(${record.id})">Submit Justification</button>` 
                        : ''}
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function calculateStats(records) {
    const totalSessions = records.length;
    const presentCount = records.filter(r => r.status === 'present').length;
    const absentCount = records.filter(r => r.status === 'absent').length;
    const attendanceRate = totalSessions > 0 ? ((presentCount / totalSessions) * 100).toFixed(1) : 0;
    
    $('#totalSessions').text(totalSessions);
    $('#presentCount').text(presentCount);
    $('#absentCount').text(absentCount);
    $('#attendanceRate').text(attendanceRate + '%');
}

function openJustificationModal(recordId) {
    $('#recordId').val(recordId);
    $('#justificationModal').show();
}

function submitJustification() {
    const recordId = $('#recordId').val();
    const reason = $('#reason').val();
    const fileInput = $('#justificationFile')[0];
    
    if (!reason) {
        alert('Please provide a reason');
        return;
    }
    
    // If file is selected, upload it first
    if (fileInput.files.length > 0) {
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        
        fetch('../api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(uploadData => {
            if (uploadData.success) {
                submitJustificationData(recordId, reason, uploadData.file_path);
            } else {
                alert('File upload failed: ' + uploadData.message);
            }
        })
        .catch(error => {
            alert('Error uploading file: ' + error.message);
        });
    } else {
        submitJustificationData(recordId, reason, null);
    }
}

function submitJustificationData(recordId, reason, filePath) {
    apiCall('justifications.php', 'POST', {
        record_id: parseInt(recordId),
        reason: reason,
        file_path: filePath
    })
    .then(data => {
        if (data.success) {
            alert('Justification submitted successfully');
            closeModal('justificationModal');
            $('#justificationForm')[0].reset();
            loadAttendanceRecords();
        } else {
            alert('Failed to submit justification: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

