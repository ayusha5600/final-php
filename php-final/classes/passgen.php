<?php

const LOW_ALP = [[97, 122]];
const UP_ALP = [[65, 90]];
const NUM = [[48, 57]];
const SYMBOL = [
    [33, 47],
    [58, 64],
    [91, 96],
    [123, 126]
];

// Function to generate a password based on provided parameters
function generatePassword($lowAlpB, $upAlpB, $numB, $symbolB, $length) {
    try{
        $allowedAscii = [];

        if ($lowAlpB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(LOW_ALP[0][0], LOW_ALP[0][1]));
        } 
    
        if ($upAlpB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(UP_ALP[0][0], UP_ALP[0][1]));
        }
    
        if ($numB > 0) {
            $allowedAscii = array_merge($allowedAscii, range(NUM[0][0], NUM[0][1]));
        }
    
        if ($symbolB > 0) {
            foreach (SYMBOL as $range) {
                $allowedAscii = array_merge($allowedAscii, range($range[0], $range[1]));
            }
        }
    
        $allowed = array_map('chr', $allowedAscii);

        // Initialize counters for each character type
        $counters = [
            'lowAlp' => 0,
            'upAlp' => 0,
            'num' => 0,
            'symbol' => 0
        ];

        $passwordGen = "";
        $i = 0;
    
        while ($i < $length) {
            // Select a random character
            $randomElement = $allowed[rand(0, count($allowed) - 1)];

            // Determine the type of the random character
            if (ctype_lower($randomElement)) {
                $type = 'lowAlp';
            } elseif (ctype_upper($randomElement)) {
                $type = 'upAlp';
            } elseif (ctype_digit($randomElement)) {
                $type = 'num';
            } else {
                $type = 'symbol';
            }

            // Check if adding this character exceeds the limit
            if ($counters[$type] < ${$type . 'B'}) {
                $passwordGen .= $randomElement;
                $counters[$type]++;
                $i++;
            }
        }
    
        return $passwordGen;
    } catch(Exception $exception) {
        return "Generation ERROR";
    }
}

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve parameters from POST request
    $lowAlpB = intval($_POST["lowAlpB"]) ?? 0;
    $upAlpB = intval($_POST["upAlpB"]) ?? 0;
    $numB = intval($_POST["numB"]) ?? 0;
    $symbolB = intval($_POST["symbolB"]) ?? 0;
    $length = intval($_POST["length"]) ?? 10;

    // Generate password
    $password = generatePassword($lowAlpB, $upAlpB, $numB, $symbolB, $length);

    // Return the generated password as JSON response
    header('Content-Type: application/json');
    echo json_encode(['password' => $password]);
} else {
    // Return an error if not a POST request
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
