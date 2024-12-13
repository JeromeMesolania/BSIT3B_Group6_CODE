<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Platform</title>
    <link rel="stylesheet" href="dashboard3.css">
</head>
<body>  

        <h1 class="welcome-message">Welcome, student <?php echo htmlspecialchars($studentName); ?>!</h1>

<section class="skill-categories">
    <h2>All the skills you need in one place</h2>
    <p>From critical skills to technical topics, Udemy supports your professional development.</p>
</section>

<div class="aligned-container">
    <section class="learning-section">
        <h3>Let's start learning</h3>
        <div class="learning-card">
            <a href="" target="_blank">
                <p>Web Development</p>
            </a>
        </div>
    </section>

    <section class="carousel-section">
    <h3>What to learn next</h3>
    <div class="carousel-container">
        <button class="carousel-btn" onclick="prevSlide()">&#10094;</button>
        <div class="carousel-items">
            <?php
            foreach ($cursor as $course) {
                if (!empty($course['videoUrl']) && !empty($course['title']) && !empty($course['price'])) {
                    // Simulate ratings and number of reviews for now
                    $simulatedRating = number_format(mt_rand(35, 50) / 10, 1); // Generate random ratings between 3.5 and 5.0
                    $simulatedReviews = mt_rand(50, 500); // Generate random review count

                    echo '<div class="carousel-item">';
                    echo '<a href="coursedetail.php?id=' . htmlspecialchars($course['_id']) . '" class="course-card">';
                    echo '<div class="thumbnail" style="background-image: url(\'thumbnail.jpg\');" onmouseover="showModal(this)" onmouseout="hideModal(this)"></div>';
                    
                    // Modal content that appears on hover
                    echo '<div class="modal-box" style="display: none;">';
                    echo '<h3>' . htmlspecialchars($course['title']) . '</h3>';
                    echo '<p><strong>Instructor:</strong> ' . htmlspecialchars($course['instructorName']) . '</p>';
                    echo '<p><strong>Price:</strong> ₱' . htmlspecialchars($course['price']) . '</p>';
                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($course['description']) . '</p>';
                    echo '<div class="rating">';
                    echo '<span class="rating">' . $simulatedRating . ' ★★★★★ (' . $simulatedReviews . ' reviews)</span>';
                    echo '</div>';
                    echo '</div>'; // End of modal box
                    
                    echo '<div class="course-info">';
                    echo '<h4 class="course-title">' . htmlspecialchars($course['title']) . '</h4>';
                    echo '<p class="instructor-name">' . htmlspecialchars($course['instructorName']) . '</p>';
                    echo '<div class="rating-price">';
                    echo '<span class="rating">' . $simulatedRating . ' ★★★★★ (' . $simulatedReviews . ' reviews)</span>';
                    echo '<span class="price">₱' . htmlspecialchars($course['price']) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="carousel-item">Missing data for a course</div>';
                }
            }
            ?>
        </div>
        <button class="carousel-btn" onclick="nextSlide()">&#10095;</button>
    </div>
</section>
</div>



<section class="carousel-section">
    <h3>Recommended to you based on ratings</h3>
    <div class="carousel-container">
        <button class="carousel-btn" onclick="prevSlide()">&#10094;</button>
        <div class="carousel-items">
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 2</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 3</p>
            </div>
        </div>
        <button class="carousel-btn" onclick="nextSlide()">&#10095;</button>
    </div>
</section>
<section class="carousel-section">
    <h3>Short and sweet courses for you</h3>
    <div class="carousel-container">
        <button class="carousel-btn" onclick="prevSlide()">&#10094;</button>
        <div class="carousel-items">
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 1</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 2</p>
            </div>
            <div class="carousel-item">
                <video width="300" controls>
                <source src="video.m3u8" type="application/x-mpegURL">
                    Your browser does not support the video tag.
                </video>
                <p>Learn Coding for Free Online - Course 3</p>
            </div>
        </div>
        <button class="carousel-btn" onclick="nextSlide()">&#10095;</button>
    </div>
</section>



<section class="topics">
    <h3 style=" text-align: left; 
    padding: 20px;
    max-width: 700px; 
    margin-left: 95px; 
    position: relative; ">Topics recommended for you</h3>
    <div class="topic-tags">
        <a href="#">React Native</a>
        <a href="#">React JS</a>
        <a href="#">React Hooks</a>
        <a href="#">MERN Stacks</a>
        <a href="#">Mobile App Development</a>
        <a href="#">Redux Framework</a>
        <a href="#">Google Maps</a>
        <a href="#">Typescript</a>
    </div>
</section>

<footer class="footer">
    <p>Teach the world online</p>
    <p>Create an online video course, reach students across the globe, and earn money.</p>
    <p>Top companies choose Code Business to build in-demand career skills.</p>
</footer>


<script src="dashboard.js"></script>
<script>
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

const userIcon = document.querySelector('.user-icon');
    const dropdown = document.getElementById('dropdown');
    const chatbox = document.getElementById('chatbox');
    const message1 = document.getElementById('message1');
    const chatInput = document.getElementById('chatInput');
    const chatboxBody = document.getElementById('chatboxBody');
    const sendButton = document.getElementById('sendButton');

   
    userIcon.addEventListener('mouseover', () => {
      dropdown.classList.add('active');
    });

   
    userIcon.addEventListener('mouseout', () => {
      dropdown.classList.remove('active');
    });

  
    message1.addEventListener('click', () => {
      chatbox.style.display = 'flex'; 
      dropdown.classList.remove('active'); 
    });

   
    sendButton.addEventListener('click', () => {
      const message = chatInput.value.trim();
      if (message) {
        const newMessage = document.createElement('p');
        newMessage.textContent = `You: ${message}`;
        chatboxBody.appendChild(newMessage);
        chatInput.value = ''; 
        chatboxBody.scrollTop = chatboxBody.scrollHeight; 
      }
    });

   
    document.addEventListener('click', (e) => {
      if (!chatbox.contains(e.target) && !dropdown.contains(e.target) && !userIcon.contains(e.target)) {
        chatbox.style.display = 'none';
      }
    });

    function toggleCartModal() {
    const cartModal = document.getElementById('cart-modal');
    // Toggle visibility of the cart modal
    cartModal.style.display = (cartModal.style.display === "none" || cartModal.style.display === "") ? "block" : "none";
}
</script>
</body>
</html>