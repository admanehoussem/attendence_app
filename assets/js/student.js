/**
 * Student Home Page JavaScript
 */

$(document).ready(function() {
    loadCourses();
});

function loadCourses() {
    apiCall('courses.php')
        .then(data => {
            if (data.success) {
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
        container.html('<p>You are not enrolled in any courses.</p>');
        return;
    }
    
    courses.forEach(course => {
        const card = $(`
            <div class="course-card" onclick="viewAttendance(${course.id})">
                <h3>${course.name}</h3>
                <p><strong>Code:</strong> ${course.code}</p>
                <p><strong>Group:</strong> ${course.group_name || 'N/A'}</p>
            </div>
        `);
        container.append(card);
    });
}

function viewAttendance(courseId) {
    window.location.href = `attendance.php?course_id=${courseId}`;
}

