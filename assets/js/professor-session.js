/**
 * Professor Session Page JavaScript
 */

$(document).ready(function() {
    loadSessionInfo();
    loadAttendanceRecords();
    
    $('#closeSessionBtn').on('click', function() {
        if (confirm('Are you sure you want to close this session? You will not be able to modify attendance after closing.')) {
            closeSession();
        }
    });
});

function loadSessionInfo() {
    apiCall(`sessions.php?course_id=${sessionId}`)
        .then(data => {
            if (data.success && data.data.length > 0) {
                const session = data.data.find(s => s.id == sessionId);
                if (session) {
                    displaySessionInfo(session);
                }
            }
        })
        .catch(error => {
            console.error('Error loading session info:', error);
        });
}

function displaySessionInfo(session) {
    const info = $('#sessionInfo');
    info.html(`
        <h2>${session.course_name}</h2>
        <p><strong>Date:</strong> ${formatDate(session.session_date)}</p>
        <p><strong>Time:</strong> ${session.session_time}</p>
        <p><strong>Group:</strong> ${session.group_name || 'All Groups'}</p>
        <p><strong>Status:</strong> <span class="status-badge status-${session.status}">${session.status.toUpperCase()}</span></p>
    `);
    
    if (session.status === 'closed') {
        $('#closeSessionBtn').hide();
    }
}

function loadAttendanceRecords() {
    apiCall(`attendance.php?session_id=${sessionId}`)
        .then(data => {
            if (data.success) {
                displayAttendanceRecords(data.data);
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
    
    records.forEach(record => {
        const row = $(`
            <tr>
                <td>${record.first_name} ${record.last_name}</td>
                <td>
                    <select class="status-select" data-record-id="${record.id}">
                        <option value="present" ${record.status === 'present' ? 'selected' : ''}>Present</option>
                        <option value="absent" ${record.status === 'absent' ? 'selected' : ''}>Absent</option>
                        <option value="late" ${record.status === 'late' ? 'selected' : ''}>Late</option>
                        <option value="excused" ${record.status === 'excused' ? 'selected' : ''}>Excused</option>
                    </select>
                </td>
                <td>
                    <input type="number" min="0" max="10" class="participation-input" 
                           data-record-id="${record.id}" value="${record.participation_score || 0}">
                </td>
                <td>
                    <textarea class="behavior-notes" data-record-id="${record.id}" 
                              rows="2" placeholder="Behavior notes...">${record.behavior_notes || ''}</textarea>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="saveRecord(${record.id})">Save</button>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

function saveRecord(recordId) {
    const status = $(`.status-select[data-record-id="${recordId}"]`).val();
    const participation = $(`.participation-input[data-record-id="${recordId}"]`).val();
    const behaviorNotes = $(`.behavior-notes[data-record-id="${recordId}"]`).val();
    
    apiCall('attendance.php', 'PUT', {
        record_id: recordId,
        status: status,
        participation_score: parseInt(participation),
        behavior_notes: behaviorNotes
    })
    .then(data => {
        if (data.success) {
            alert('Attendance record updated successfully');
        } else {
            alert('Failed to update record: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function closeSession() {
    apiCall('sessions.php', 'PUT', {
        session_id: sessionId,
        action: 'close'
    })
    .then(data => {
        if (data.success) {
            alert('Session closed successfully');
            location.reload();
        } else {
            alert('Failed to close session: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

