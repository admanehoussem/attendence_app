/**
 * Professor Summary Page JavaScript
 */

$(document).ready(function() {
    loadCourses();
    if (courseId) {
        $('#courseFilter').val(courseId);
    }
    if (groupId) {
        $('#groupFilter').val(groupId);
    }
    loadSummary();
    
    $('#courseFilter, #groupFilter').on('change', function() {
        loadSummary();
    });
});

function loadCourses() {
    apiCall('courses.php')
        .then(data => {
            if (data.success) {
                const select = $('#courseFilter');
                data.data.forEach(course => {
                    select.append(`<option value="${course.id}">${course.name}</option>`);
                });
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
        });
}

function loadSummary() {
    const courseId = $('#courseFilter').val();
    const groupId = $('#groupFilter').val();
    
    if (!courseId) {
        $('#summaryTable tbody').html('<tr><td colspan="4">Please select a course</td></tr>');
        return;
    }
    
    apiCall(`attendance.php?course_id=${courseId}${groupId ? '&group_id=' + groupId : ''}`)
        .then(data => {
            if (data.success) {
                displaySummary(data.data);
                calculateStats(data.data);
            } else {
                showError('summaryTable', 'Failed to load summary');
            }
        })
        .catch(error => {
            showError('summaryTable', error.message);
        });
}

function displaySummary(records) {
    const tbody = $('#summaryTable tbody');
    tbody.empty();
    
    if (records.length === 0) {
        tbody.html('<tr><td colspan="4">No attendance records found</td></tr>');
        return;
    }
    
    records.forEach(record => {
        const row = $(`
            <tr>
                <td>${record.first_name} ${record.last_name}</td>
                <td>${formatDateTime(record.session_date, record.session_time)}</td>
                <td><span class="status-badge status-${record.status}">${record.status.toUpperCase()}</span></td>
                <td>${record.participation_score || 0}/10</td>
            </tr>
        `);
        tbody.append(row);
    });
}

function calculateStats(records) {
    const totalSessions = new Set(records.map(r => r.session_date)).size;
    const totalStudents = new Set(records.map(r => r.student_id || r.first_name + r.last_name)).size;
    const presentCount = records.filter(r => r.status === 'present').length;
    const attendanceRate = records.length > 0 ? ((presentCount / records.length) * 100).toFixed(1) : 0;
    
    $('#totalSessions').text(totalSessions);
    $('#totalStudents').text(totalStudents);
    $('#attendanceRate').text(attendanceRate + '%');
}

