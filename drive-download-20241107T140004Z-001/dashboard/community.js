// Modal Management
const postInput = document.getElementById('postInput');
const postModal = document.getElementById('postModal');
const closeBtn = document.querySelector('.close-btn');

// Open modal when input is clicked
postInput.addEventListener('click', function() {
    postModal.style.display = "block";  // Show the modal
});

// Close modal when close button is clicked
closeBtn.addEventListener('click', function() {
    postModal.style.display = "none";  // Hide the modal
});

// Close modal when clicking outside the modal content
window.addEventListener('click', function(event) {
    if (event.target === postModal) {
        postModal.style.display = "none";  // Hide the modal
    }
});

// Open photo and file input dialogs when respective buttons are clicked
document.getElementById('photoButton').addEventListener('click', function() {
    document.getElementById('photoInput').click();
});

document.getElementById('fileButton').addEventListener('click', function() {
    document.getElementById('fileInput').click();
});

// Handle post submission (upload file and content)
const postButton = document.getElementById('postButton');
const modalText = document.getElementById('contentInput');
const photoInput = document.getElementById('photoInput');
const fileInput = document.getElementById('fileInput');

postButton.addEventListener('click', function() {
    const content = modalText.value;
    const photo = photoInput.files[0];  // Get the photo from the photo input
    const file = fileInput.files[0];    // Get the file from the file input

    // Ensure content or file/photo exists
    if (!content && !file && !photo) {
        alert("Please enter some text or choose a file/photo to post.");
        return;
    }

    // Create a FormData object to send content and file/photo
    const formData = new FormData();
    formData.append("content", content);
    if (file) formData.append("media", file);
    if (photo) formData.append("photo", photo);  // Include photo if selected

    // Send the form data via AJAX (using fetch API)
    fetch('community.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);  // Handle success or failure
        postModal.style.display = "none";  // Close the modal after posting
        location.reload();  // Reload to show the new post (or use a more optimized way to append)
    })
    .catch(error => console.error('Error:', error));
});

function likePost(postId) {
    fetch('community.php', {
        method: 'POST',
        body: new URLSearchParams({
            'action': 'like',
            'post_id': postId
        })
    }).then(response => response.text())
      .then(data => {
          alert('Liked!');
          location.reload(); // Reload page to update like count
      });
}

function dislikePost(postId) {
    fetch('community.php', {
        method: 'POST',
        body: new URLSearchParams({
            'action': 'dislike',
            'post_id': postId
        })
    }).then(response => response.text())
      .then(data => {
          alert('Disliked!');
          location.reload(); // Reload page to update dislike count
      });
}

function toggleCommentForm(postId) {
    // Here you can show a modal or a text box to add a comment
    const commentBox = document.getElementById('commentBox-' + postId);
    commentBox.style.display = commentBox.style.display === 'block' ? 'none' : 'block';
}

// Submit a comment
function submitComment(postId) {
    const comment = document.getElementById('commentInput-' + postId).value;
    fetch('community.php', {
        method: 'POST',
        body: new URLSearchParams({
            'action': 'comment',
            'post_id': postId,
            'comment': comment
        })
    }).then(response => response.text())
      .then(data => {
          alert('Comment posted!');
          location.reload(); // Reload page to show new comment
      });
}

function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
        commentsSection.style.display = 'block';
    } else {
        commentsSection.style.display = 'none';
    }
}

   // Add event listeners for like and dislike buttons
   function handleLikeDislike(action, postId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'your_php_file.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // Send the action and post ID to the server
    xhr.send('action=' + action + '&post_id=' + postId);
    
    xhr.onload = function () {
        if (xhr.status == 200) {
            // Parse the returned JSON response
            var response = JSON.parse(xhr.responseText);
            
            // Update the like and dislike counts
            document.getElementById('likeCount_' + postId).textContent = response.likeCount;
            document.getElementById('dislikeCount_' + postId).textContent = response.dislikeCount;
        }
    };
}

// Attach event listeners to like and dislike buttons
document.querySelectorAll('.like-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        var postId = this.getAttribute('data-post-id');
        handleLikeDislike('like', postId);
    });
});

document.querySelectorAll('.dislike-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        var postId = this.getAttribute('data-post-id');
        handleLikeDislike('dislike', postId);
    });
});


