<?php
// CHANGE THIS to your project directory
$projectDir = '/www/wwwroot/tfmsdemo.avts.com.my';

// Git command
$command = "cd $projectDir && git pull origin main 2>&1";

// Execute command
$output = shell_exec($command);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Git Pull Output</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background: #000;
            color: #00ff00; /* terminal green */
            font-family: Consolas, Monaco, monospace;
            font-size: 14px;
        }
        pre {
            white-space: pre-wrap;
            padding: 15px;
        }
    </style>
</head>
<body>
<pre><?php echo htmlspecialchars($output); ?></pre>
</body>
</html>


