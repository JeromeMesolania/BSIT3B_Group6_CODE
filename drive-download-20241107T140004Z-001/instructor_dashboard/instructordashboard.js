// Get elements
const modal = document.getElementById('courseModal');
const createCourseBtn = document.querySelector('.create-course-btn');
const closeBtn = document.querySelector('.close-btn');

// Show modal
createCourseBtn.addEventListener('click', () => {
    modal.style.display = 'block';
});

// Hide modal when close button is clicked
closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Hide modal when clicking outside of the modal
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});




