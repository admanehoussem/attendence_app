/**
 * Professor Home Page JavaScript
 */

let coursesData = [];

$(document).ready(function() {
    loadCourses();
    
    $('#createSessionBtn').on('click', function() {
        openModal('createSessionModal');
        loadCoursesForSession();
    });
    
    $('#createSessionForm').on('submit', function(e) {
        e.preventDefault();
        createSession();
    });
    
    $('#sessionCourse').on('change', function() {
        loadGroupsForCourse($(this).val());
    });
    
    // Set default date to today
    $('#sessionDate').val(new Date().toISOString().split('T')[0]);
    // Set default time to current time
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    $('#sessionTime').val(`${hours}:${minutes}`);
});

function loadCourses() {
    apiCall('courses.php')
        .then(data => {
            if (data.success) {
                coursesData = data.data;
                displayCourses(data.data);
            } else {
                showError('coursesList', 'Failed to load courses');
            }
        })
        .catch(error => {
            showError('coursesList', error.message);
        });
}

function displayCourses(courses) {
    const container = $('#coursesList');
    container.empty();
    
    if (courses.length === 0) {
        container.html('<p>No courses found. Please contact administrator to add courses.</p>');
        return;
    }
    
    courses.forEach(course => {
        const card = $(`
            <div class="course-card">
                <h3>${course.name}</h3>
                <p><strong>Code:</strong> ${course.code}</p>
                <p><strong>Students:</strong> ${course.student_count || 0}</p>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="viewSessions(${course.id})">View Sessions</button>
                    <button class="btn btn-secondary" onclick="viewSummary(${course.id})">View Summary</button>
                </div>
            </div>
        `);
        container.append(card);
    });
}

function loadCoursesForSession() {
    const select = $('#sessionCourse');
    select.empty();
    select.append('<option value="">Select a course</option>');
    
    coursesData.forEach(course => {
        select.append(`<option value="${course.id}">${course.name} (${course.code})</option>`);
    });
}

function loadGroupsForCourse(courseId) {
    // For now, we'll leave groups empty. In a full implementation, you'd fetch groups from API
    const select = $('#sessionGroup');
    select.empty();
    select.append('<option value="">All Groups</option>');
}

function createSession() {
    const courseId = $('#sessionCourse').val();
    const groupId = $('#sessionGroup').val() || null;
    const sessionDate = $('#sessionDate').val();
    const sessionTime = $('#sessionTime').val();
    
    if (!courseId || !sessionDate || !sessionTime) {
        alert('Please fill in all required fields');
        return;
    }
    
    apiCall('sessions.php', 'POST', {
        course_id: parseInt(courseId),
        group_id: groupId ? parseInt(groupId) : null,
        session_date: sessionDate,
        session_time: sessionTime
    })
    .then(data => {
        if (data.success) {
            alert('Session created successfully!');
            closeModal('createSessionModal');
            $('#createSessionForm')[0].reset();
            // Redirect to session page
            window.location.href = `session.php?id=${data.id}`;
        } else {
            alert('Failed to create session: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function viewSessions(courseId) {
    window.location.href = `summary.php?course_id=${courseId}`;
}

function viewSummary(courseId) {
    window.location.href = `summary.php?course_id=${courseId}`;
}

