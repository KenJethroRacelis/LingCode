<?php
session_start();

// 1. Updated Guard: Allow any authenticated role in the system to query the local AI
$allowed_roles = ['user', 'mod', 'admin'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access. Please log in first.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input from our frontend AJAX request
    $input = json_decode(file_get_contents('php://input'), true);
    $userPrompt = isset($input['prompt']) ? trim($input['prompt']) : '';

    if (empty($userPrompt)) {
        echo json_encode(['error' => 'Prompt cannot be empty']);
        exit();
    }

    // The endpoint where Ollama is listening locally
    $ollamaUrl = "http://127.0.0.1:11434/api/generate";

    // Prepare the payload data for the AI model
    $data = [
        "model" => "qwen2.5:0.5b", // Make sure this matches the model you downloaded
        "prompt" => $userPrompt,
        "stream" => false // Set to false so it gives us the full answer at once
    ];

    // Initialize cURL to communicate with Ollama
    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Could not connect to Ollama: ' . curl_error($ch)]);
        curl_close($ch);
        exit();
    }

    curl_close($ch);

    // Decode Ollama's response and send just the text back to our HTML page
    $responseData = json_decode($response, true);
    echo json_encode(['response' => $responseData['response'] ?? 'No response generated.']);
    exit();
}