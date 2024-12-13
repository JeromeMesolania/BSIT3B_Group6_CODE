// chat.js
const senderId = "user_id";  // Replace with the actual sender's user ID
const receiverId = "receiver_id";  // Replace with the receiver's user ID

// Fetch messages when the page loads
window.onload = function() {
    loadMessages();
};

// Function to load messages
function loadMessages() {
    fetch("loadMessages.php", {
        method: "GET",
        headers: {
            "Content-Type": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        const chatBox = document.getElementById('chat-box');
        chatBox.innerHTML = "";  // Clear the chat box
        data.messages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.textContent = message.message;
            chatBox.appendChild(messageDiv);
        });
    });
}

// Function to send message
function sendMessage() {
    const message = document.getElementById('message-input').value;
    if (message) {
        fetch("sendMessage.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                senderId: senderId,
                receiverId: receiverId,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages();  // Reload messages after sending
                document.getElementById('message-input').value = '';  // Clear input field
            }
        });
    }
}
