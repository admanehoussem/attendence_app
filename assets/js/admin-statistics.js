/**
 * Admin Statistics Page JavaScript
 */

let attendanceChart, monthlyChart, courseChart;

$(document).ready(function() {
    loadStatistics();
});

function loadStatistics() {
    apiCall('statistics.php')
        .then(data => {
            if (data.success) {
                displayStatistics(data.data);
                createCharts(data.data);
            } else {
                alert('Failed to load statistics');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function displayStatistics(stats) {
    $('#totalStudents').text(stats.total_students);
    $('#totalProfessors').text(stats.total_professors);
    $('#totalCourses').text(stats.total_courses);
    $('#totalSessions').text(stats.total_sessions);
    $('#attendanceRate').text(stats.attendance_rate + '%');
}

function createCharts(stats) {
    // Attendance Status Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    attendanceChart = new Chart(attendanceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Excused'],
            datasets: [{
                data: [
                    stats.attendance.present_count,
                    stats.attendance.absent_count,
                    stats.attendance.late_count,
                    stats.attendance.excused_count
                ],
                backgroundColor: [
                    '#28a745',
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
    
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = stats.monthly_trend.reverse();
    monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Sessions',
                data: monthlyData.map(d => d.session_count),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Course Attendance Chart
    const courseCtx = document.getElementById('courseChart').getContext('2d');
    const courseData = stats.by_course.slice(0, 10); // Top 10 courses
    courseChart = new Chart(courseCtx, {
        type: 'bar',
        data: {
            labels: courseData.map(d => d.name),
            datasets: [{
                label: 'Attendance Rate (%)',
                data: courseData.map(d => {
                    return d.record_count > 0 ? ((d.present_count / d.record_count) * 100).toFixed(1) : 0;
                }),
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

