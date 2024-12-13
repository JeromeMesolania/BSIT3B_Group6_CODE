<?php
require 'vendor/autoload.php';

use Textalk\Websocket\Server;
use Textalk\Websocket\Connection;
use MongoDB\Client as MongoDBClient;

$host = 'localhost';
$port = 8080;

$clients = []; // Store all connected clients

// Connect to MongoDB
$mongoClient = new MongoDBClient("mongodb://localhost:27017");
$chatCollection = $mongoClient->CODE->messages; // Change 'CODE' to your database name

// Start the WebSocket server
$server = new Server("ws://$host:$port");

echo "WebSocket server started on ws://$host:$port\n";

// Function to broadcast messages to all connected clients
function broadcastMessage($message, $clients) {
    foreach ($clients as $client) {
        $client->send($message);
    }
}

// Handle incoming connections
$server->on('connection', function (Connection $conn) use (&$clients) {
    echo "New client connected: {$conn->getRemoteAddress()}\n";
    $clients[] = $conn;

    // Handle incoming messages
    $conn->on('message', function ($message) use (&$clients, $conn) {
        $data = json_decode($message, true);

        if (isset($data['senderId'], $data['receiverId'], $data['message'])) {
            // Prepare message for broadcasting
            $chatMessage = [
                'senderId' => $data['senderId'],
                'receiverId' => $data['receiverId'],
                'message' => $data['message'],
                'timestamp' => new \MongoDB\BSON\UTCDateTime(),
            ];

            // Save the message to MongoDB
            global $chatCollection;
            $chatCollection->insertOne($chatMessage);

            // Broadcast message to all connected clients
            $broadcastData = json_encode($chatMessage);
            broadcastMessage($broadcastData, $clients);
        }
    });

    // Handle disconnection
    $conn->on('close', function () use (&$clients, $conn) {
        echo "Client disconnected: {$conn->getRemoteAddress()}\n";
        $clients = array_filter($clients, fn($client) => $client !== $conn);
    });
});

// Start the WebSocket server
$server->run();
