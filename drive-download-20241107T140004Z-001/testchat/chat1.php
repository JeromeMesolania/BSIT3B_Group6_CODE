<html>
<head>
    <title>Real-time Chat System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .chat-container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .chat-header {
            background-color: #007bff;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: 500;
        }
        .chat-messages {
            height: 400px;
            overflow-y: scroll;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .chat-message {
            margin-bottom: 15px;
        }
        .chat-message .message-content {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }
        .chat-message .message-time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .chat-input {
            display: flex;
            padding: 15px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        .chat-input button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            Real-time Chat
        </div>
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be dynamically added here -->
        </div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Type a message...">
            <button id="send-button"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-database.js"></script>
    <script>
        // Your web app's Firebase configuration
        var firebaseConfig = {
            apiKey: "AIzaSyBE4K8r6JI0ysFOcIymz2_O8KbDMARJyBg",
  authDomain: "chatsystem-e5fc1.firebaseapp.com",
  databaseURL: "https://chatsystem-e5fc1-default-rtdb.firebaseio.com",
  projectId: "chatsystem-e5fc1",
  storageBucket: "chatsystem-e5fc1.firebasestorage.app",
  messagingSenderId: "43933765889",
  appId: "1:43933765889:web:49e913d152099fd850cb00"
};
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        const db = firebase.database();
        const messagesRef = db.ref('messages');

        document.getElementById('send-button').addEventListener('click', sendMessage);
        document.getElementById('message-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            if (message) {
                const timestamp = new Date().toLocaleTimeString();
                messagesRef.push({
                    message: message,
                    time: timestamp
                });
                messageInput.value = '';
            }
        }

        messagesRef.on('child_added', function (snapshot) {
            const messageData = snapshot.val();
            displayMessage(messageData.message, messageData.time);
        });

        function displayMessage(message, time) {
            const chatMessages = document.getElementById('chat-messages');
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message');
            messageElement.innerHTML = `
                <div class="message-content">${message}</div>
                <div class="message-time">${time}</div>
            `;
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>