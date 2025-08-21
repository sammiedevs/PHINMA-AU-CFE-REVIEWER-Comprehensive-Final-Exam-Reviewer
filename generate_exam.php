<?php
header('Content-Type: application/json');

// Get the raw JSON input
$input = file_get_contents('php://input');

// Decode the JSON input
$data = json_decode($input, true);

// Check if 'content' key exists and is not empty
if (!isset($data['content']) || empty($data['content'])) {
    echo json_encode(["error" => "No content provided"]);
    exit;
}

$content = $data['content'];

// Function to generate questions using OpenAI API
function generateQuestionsWithAI($content) {
    $apiKey = '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'; // Replace with your actual OpenAI API key
    $url = 'https://api.openai.com/v1/completions';

    // Define the prompt for the AI
    $prompt = "Generate 5 multiple-choice questions based on the following content. For each question, provide 4 options and indicate the correct answer.\n\nContent:\n$content\n\nQuestions:";

    // Define the API request data
    $data = [
        'model' => 'text-davinci-003', // Use the appropriate model
        'prompt' => $prompt,
        'max_tokens' => 500, // Adjust based on your needs
        'temperature' => 0.7 // Adjust for creativity
    ];

    // Set up the HTTP request options
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    // Create the context for the HTTP request
    $context = stream_context_create($options);

    // Send the request to the OpenAI API
    $response = file_get_contents($url, false, $context);

    // Handle API errors
    if ($response === FALSE) {
        return ["error" => "Failed to generate questions using OpenAI API"];
    }

    // Decode the API response
    $responseData = json_decode($response, true);

    // Extract the generated questions
    $generatedText = $responseData['choices'][0]['text'];

    // Parse the generated text into structured questions
    return parseGeneratedQuestions($generatedText);
}

// Function to parse the AI-generated text into structured questions
function parseGeneratedQuestions($generatedText) {
    // Split the generated text into individual questions
    $questions = explode("\n\n", trim($generatedText));

    $structuredQuestions = [];
    foreach ($questions as $question) {
        // Skip empty lines
        if (empty(trim($question))) {
            continue;
        }

        // Extract the question and options
        if (preg_match('/^(.*\?)\s*(.*)/', $question, $matches)) {
            $questionText = $matches[1];
            $options = explode("\n", $matches[2]);

            // Extract the correct answer (assuming it's marked with an asterisk)
            $correctAnswer = '';
            $cleanedOptions = [];
            foreach ($options as $option) {
                if (strpos($option, '*') !== false) {
                    $correctAnswer = str_replace('*', '', $option);
                }
                $cleanedOptions[] = str_replace('*', '', $option);
            }

            // Add the question to the structured array
            $structuredQuestions[] = [
                "question" => $questionText,
                "options" => $cleanedOptions,
                "correctAnswer" => $correctAnswer
            ];
        }
    }

    return $structuredQuestions;
}

// Generate questions using the OpenAI API
$questions = generateQuestionsWithAI($content);

// Return the questions as JSON
echo json_encode($questions);
?>