let currentIndex = 0;
const images = document.querySelectorAll(".carousel-image");
const intervalTime = 3000; 

function showNextImage() {
    
    images[currentIndex].classList.remove("active");
    
    
    currentIndex = (currentIndex + 1) % images.length;
    
   
    images[currentIndex].classList.add("active");
}


images[currentIndex].classList.add("active");
setInterval(showNextImage, intervalTime);



function nextSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: 320, behavior: 'smooth' }); 
}

function prevSlide() {
    const carousel = document.querySelector('.carousel-items');
    carousel.scrollBy({ left: -320, behavior: 'smooth' });
}

document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating; // Get the rating from the clicked star
        const courseId = this.closest('.rating').getAttribute('data-course-id');  // Get course ID from the parent container's data attribute
        
        // Update the hidden input to store the selected rating
        document.getElementById('course-rating-' + courseId).value = rating;
        
        // Highlight the selected stars
        updateStars(courseId, rating);

        // Call the backend to submit the rating
        submitRating(courseId, rating);  // Pass rating and courseId
    });
});

function updateStars(courseId, rating) {
    const stars = document.querySelectorAll(`.rating[data-course-id="${courseId}"] .star`);
    stars.forEach(star => {
        if (star.dataset.rating <= rating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
}

function submitRating(courseId, rating) {
    fetch('submit_rating.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ courseId: courseId, rating: rating })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message); // Handle success message
    })
    .catch(error => {
        console.error('Error submitting rating:', error);
    });
}

