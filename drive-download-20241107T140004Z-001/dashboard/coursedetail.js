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
    
    function addToCart(courseId) {
      console.log("Course ID:", courseId); // Log courseId for debugging
      
      // Create a data object with course ID and user information (e.g., user ID)
      const userId = 'user123'; // This would ideally come from the logged-in user's session or profile
  
      // Send the data to the backend via Fetch API
      fetch('../addtocart/add_to_cart.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              userId: userId,
              courseId: courseId
          })
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              alert('Course added to cart!');
          } else {
              alert('Failed to add course to cart: ' + data.message);
          }
      })
      .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while adding to the cart.');
      });
  }

