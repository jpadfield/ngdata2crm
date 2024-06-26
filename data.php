<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$dbLog = "tmp/debug.log";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    
    // Log received input for debugging
    file_put_contents($dbLog, "Received input: $input\n", FILE_APPEND);

    // Create a temporary file to store JSON data
    $tempFile = tempnam(sys_get_temp_dir(), 'json_');
    $writeResult = file_put_contents($tempFile, $input);

    // Check if writing to the file was successful
    if ($writeResult === false) {
        file_put_contents($dbLog, "Failed to write to temp file\n", FILE_APPEND);
        http_response_code(500);
        echo "Error writing to temp file";
        exit;
    }

    // Log the temporary file name for debugging
    file_put_contents($dbLog, "Temporary file created: $tempFile\n", FILE_APPEND);

    // Command to execute the Python script
    $command = "python3 ngData2crm.py < " . escapeshellarg($tempFile);
    
    // Log command for debugging
    file_put_contents($dbLog, "Command: $command\n", FILE_APPEND);

    // Execute the command and capture the output
    $output = shell_exec($command);

    // Log output for debugging
    file_put_contents($dbLog, "Output: $output\n", FILE_APPEND);

    // Clean up the temporary file
    unlink($tempFile);

    if ($output === null) {
        file_put_contents($dbLog, "Python script returned null\n", FILE_APPEND);
        http_response_code(500);
        echo "Error processing data";
    } else {
        echo htmlspecialchars($output);
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>

