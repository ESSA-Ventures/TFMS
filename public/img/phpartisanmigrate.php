<?php
$cmd = 'cd /www/wwwroot/tfmsdemo.avts.com.my && php artisan migrate 2>&1';
$output = shell_exec($cmd);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Artisan Output</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background: #000;
            color: #00ff00; /* hijau macam terminal */
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

