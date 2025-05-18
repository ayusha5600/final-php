<?php

// Define ASCII ranges for lowercase, uppercase, numbers, and symbols
const LOW_ALP = [[97, 122]];     // a-z
const UP_ALP = [[65, 90]];       // A-Z
const NUM = [[48, 57]];          // 0-9
const SYMBOL = [                 // Symbols from different ASCII ranges
    [33, 47],
    [58, 64],
    [91, 96],
    [123, 126]
];

// Function to generate a password based on selected character types and total length
function generatePassword($lowAlpB, $upAlpB, $numB, $symbolB, $length) {
    try {
        $allowedAscii = [];  // List of allowed ASCII codes to choose from

        // Add lowercase letters if requested
        if ($lowAlpB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(LOW_ALP[0][0], LOW_ALP[0][1]));
        }

        // Add uppercase letters if requested
        if ($upAlpB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(UP_ALP[0][0], UP_ALP[0][1]));
        }

        // Add numbers if requested
        if ($numB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(NUM[0][0], NUM[0][1]));
        }

        // Add symbols if requested
        if ($symbolB > 0) {
            foreach (SYMBOL as $range) {
                $allowedAscii = array_merge($allowedAscii, range($range[0], $range[1]));
            }
        }

        // Convert ASCII codes to characters
        $allowed = array_map('chr', $allowedAscii);

        // Initialize counters to keep track of how many of each type is used
        $counters = [
            'lowAlp' => 0,
            'upAlp' => 0,
            'num' => 0,
            'symbol' => 0
        ];

        $passwordGen = "";  // Final password
        $i = 0;

        // Generate password character by character
        while ($i < $length) {
            $randomElement = $allowed[rand(0, count($allowed) - 1)];  // Pick random character

            // Determine the type of character
            if (ctype_lower($randomElement)) {
                $type = 'lowAlp';
            } elseif (ctype_upper($randomElement)) {
                $type = 'upAlp';
            } elseif (ctype_digit($randomElement)) {
                $type = 'num';
            } else {
                $type = 'symbol';
            }

            // Only add character if its type hasn't exceeded the limit
            if ($counters[$type] < ${$type . 'B'}) {
                $passwordGen .= $randomElement;
                $counters[$type]++;
                $i++;
            }
        }

        return $passwordGen;

    } catch (Exception $exception) {
        return "Generation ERROR";  // Return error message if something goes wrong
    }
}

// Handle POST request to generate password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get parameters from the POST form
    $lowAlpB = intval($_POST["lowAlpB"]) ?? 0;
    $upAlpB = intval($_POST["upAlpB"]) ?? 0;
    $numB = intval($_POST["numB"]) ?? 0;
    $symbolB = intval($_POST["symbolB"]) ?? 0;
    $length = intval($_POST["length"]) ?? 10;

    // Call the function to generate password
    $password = generatePassword($lowAlpB, $upAlpB, $numB, $symbolB, $length);

    // Send back password in JSON format
    header('Content-Type: application/json');
    echo json_encode(['password' => $password]);
} else {
    // If the request method is not POST, return error
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
