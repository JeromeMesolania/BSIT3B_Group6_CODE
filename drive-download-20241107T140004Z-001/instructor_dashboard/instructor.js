// Get the modal and video elements
var modal = document.getElementById("videoModal");
var videoPlayer = document.getElementById("videoPlayer");
var videoSource = document.getElementById("videoSource");
var closeModal = document.getElementById("closeModal");
var fullscreenBtn = document.getElementById("fullscreenBtn");

// Function to open the video modal and set the video source
function openVideoModal(videoUrl) {
    modal.style.display = "block";
    videoSource.src = videoUrl;
    videoPlayer.load();  // Reload the video element with the new source
    videoPlayer.play();
}

// Close the modal when the close button is clicked
closeModal.onclick = function() {
    modal.style.display = "none";
    videoPlayer.pause();  // Pause the video when closing
    videoPlayer.currentTime = 0;  // Reset the video to the beginning
}

// Fullscreen button functionality
fullscreenBtn.onclick = function() {
    if (videoPlayer.requestFullscreen) {
        videoPlayer.requestFullscreen();
    } else if (videoPlayer.webkitRequestFullscreen) { // Safari
        videoPlayer.webkitRequestFullscreen();
    } else if (videoPlayer.msRequestFullscreen) { // IE/Edge
        videoPlayer.msRequestFullscreen();
    }
};

// Automatically close the modal and go back to the dashboard after the video finishes
videoPlayer.onended = function() {
    setTimeout(function() {
        modal.style.display = "none";  // Close the modal
        window.location.href = 'dashboard.php';  // Redirect to the dashboard after the video finishes
    }, 2000);  // 2-second delay before redirect
};

 // Function to open the video modal with the video URL
 function openVideoModal(url) {
    var modal = document.getElementById('videoModal');
    var videoPlayer = document.getElementById('videoPlayer');
    var videoSource = document.getElementById('videoSource');
    videoSource.src = url;  // Set the video source to the URL passed in
    videoPlayer.load();      // Load the video
    modal.style.display = "block"; // Show the modal
}

// Close the modal when clicking on the close button
document.getElementById('closeModal').onclick = function() {
    var modal = document.getElementById('videoModal');
    modal.style.display = "none"; // Hide the modal
}

// Optional: Close the modal if clicked outside the modal content
window.onclick = function(event) {
    var modal = document.getElementById('videoModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

