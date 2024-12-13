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

// For the item carousel (next and prev buttons)
let carouselIndex = 0;  // Renamed to avoid conflict with the image carousel's currentIndex

function nextSlide() {
    const items = document.querySelectorAll('.carousel-item');
    if (carouselIndex < items.length - 1) {
        carouselIndex++;
    } else {
        carouselIndex = 0;  // Loop back to the first slide
    }
    updateCarousel();
}

function prevSlide() {
    const items = document.querySelectorAll('.carousel-item');
    if (carouselIndex > 0) {
        carouselIndex--;
    } else {
        carouselIndex = items.length - 1;  // Loop to the last slide
    }
    updateCarousel();
}

function updateCarousel() {
  const items = document.querySelectorAll('.carousel-item');
  items.forEach((item, index) => {
      // Ensure the items for the video courses are not being hidden unintentionally
      item.style.display = (index === carouselIndex) ? 'block' : 'none';
  });

  // Ensure the video element inside the carousel items is displayed correctly
  const videos = document.querySelectorAll('video');
  videos.forEach(video => video.style.display = 'block');  // You can adjust this if needed
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

    function openChat(receiverId) {
      if (!receiverId) {
          alert('Receiver ID is missing');
          return;
      }
      document.getElementById('messageBox').style.display = 'block';
      loadMessages(receiverId);
  }
  
  function closeChat() {
      document.getElementById('messageBox').style.display = 'none';
  }

  document.querySelectorAll('.video-container').forEach(container => {
    let showTimeout, hideTimeout;

    container.addEventListener('mouseover', () => {
        clearTimeout(hideTimeout); // Prevent immediate hiding
        showTimeout = setTimeout(() => {
            const modal = container.querySelector('.hover-modal');
            if (modal) {
                modal.style.opacity = '1';
                modal.style.visibility = 'visible';
            }
        }, 500); // Delay before showing (500ms)
    });

    container.addEventListener('mouseout', () => {
        clearTimeout(showTimeout); // Prevent immediate showing
        hideTimeout = setTimeout(() => {
            const modal = container.querySelector('.hover-modal');
            if (modal) {
                modal.style.opacity = '0';
                modal.style.visibility = 'hidden';
            }
        }, 300); // Delay before hiding (300ms)
    });
});

function toggleWishlist(button, courseId) {
  const heartIcon = button.querySelector('.heart-icon');
  const isActive = button.classList.contains('active');

  if (isActive) {
      // Remove from wishlist
      button.classList.remove('active');
      button.textContent = ' Add to wishlist'; // Reset text
      button.prepend(heartIcon);

      // Optional: Add logic to remove from wishlist on the server
      console.log(`Course ${courseId} removed from wishlist.`);
  } else {
      // Add to wishlist
      button.classList.add('active');
      button.textContent = ' Remove from wishlist'; // Change text
      button.prepend(heartIcon);

      // Optional: Add logic to add to wishlist on the server
      console.log(`Course ${courseId} added to wishlist.`);
  }
}

function showModal(element) {
  const modal = element.querySelector('.modal-box');
  if (modal) {
      modal.style.display = 'block';
  }
}

function hideModal(element) {
  const modal = element.querySelector('.modal-box');
  if (modal) {
      modal.style.display = 'none';
  }
}

// Get references to the cart icon and modal
const cartIcon = document.getElementById('cart-icon');
const cartModal = document.getElementById('cart-modal');

// Function to toggle the modal
cartIcon.addEventListener('click', function() {
    cartModal.style.display = cartModal.style.display === 'none' ? 'block' : 'none';
});




