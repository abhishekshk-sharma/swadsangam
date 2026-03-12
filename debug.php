<?php
// Place this file as debug.php in your public_html root
// Access it via: https://swadsangam.store/debug.php
// DELETE THIS FILE after debugging!

echo "<h1>Laravel Deployment Diagnostics</h1>";
echo "<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Check PHP Version
echo "<h2>1. PHP Version</h2>";
$phpVersion = phpversion();
echo "PHP Version: <strong>$phpVersion</strong>";
if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo " <span class='success'>✓ OK</span><br>";
} else {
    echo " <span class='error'>✗ Need PHP 8.1+</span><br>";
}

// 2. Check Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$required = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
foreach ($required as $ext) {
    echo "$ext: ";
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ Loaded</span><br>";
    } else {
        echo "<span class='error'>✗ Missing</span><br>";
    }
}

// 3. Check File Paths
echo "<h2>3. File Structure</h2>";
echo "Current Directory: <strong>" . __DIR__ . "</strong><br>";
echo "Parent Directory: <strong>" . dirname(__DIR__) . "</strong><br>";

// 4. Check Laravel Files
echo "<h2>4. Laravel Files Check</h2>";

$laravelPaths = [
    'Vendor Autoload' => __DIR__.'/../vendor/autoload.php',
    'Bootstrap App' => __DIR__.'/../bootstrap/app.php',
    '.env File' => __DIR__.'/../.env',
    'Storage Directory' => __DIR__.'/../storage',
    'Bootstrap Cache' => __DIR__.'/../bootstrap/cache',
];

foreach ($laravelPaths as $name => $path) {
    echo "$name: ";
    if (file_exists($path)) {
        echo "<span class='success'>✓ Found</span> ($path)<br>";
    } else {
        echo "<span class='error'>✗ Not Found</span> ($path)<br>";
    }
}

// 5. Check Permissions
echo "<h2>5. Directory Permissions</h2>";
$checkPerms = [
    'Storage' => __DIR__.'/../storage',
    'Bootstrap Cache' => __DIR__.'/../bootstrap/cache',
];

foreach ($checkPerms as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "$name: <strong>$perms</strong>";
        if (is_writable($path)) {
            echo " <span class='success'>✓ Writable</span><br>";
        } else {
            echo " <span class='error'>✗ Not Writable</span><br>";
        }
    } else {
        echo "$name: <span class='error'>✗ Directory not found</span><br>";
    }
}

// 6. Check .env file
echo "<h2>6. Environment File</h2>";
$envPath = __DIR__.'/../.env';
if (file_exists($envPath)) {
    echo ".env file: <span class='success'>✓ Exists</span><br>";
    $envContent = file_get_contents($envPath);
    
    // Check critical variables (without showing values)
    $criticalVars = ['APP_KEY', 'APP_URL', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($criticalVars as $var) {
        if (strpos($envContent, $var.'=') !== false) {
            preg_match('/'.$var.'=(.*)/', $envContent, $matches);
            $value = isset($matches[1]) ? trim($matches[1]) : '';
            if (!empty($value)) {
                echo "$var: <span class='success'>✓ Set</span><br>";
            } else {
                echo "$var: <span class='error'>✗ Empty</span><br>";
            }
        } else {
            echo "$var: <span class='error'>✗ Not Found</span><br>";
        }
    }
} else {
    echo ".env file: <span class='error'>✗ Not Found</span><br>";
}

// 7. Try to load Laravel
echo "<h2>7. Laravel Bootstrap Test</h2>";
try {
    if (file_exists(__DIR__.'/../vendor/autoload.php')) {
        require __DIR__.'/../vendor/autoload.php';
        echo "Autoload: <span class='success'>✓ Loaded</span><br>";
        
        if (file_exists(__DIR__.'/../bootstrap/app.php')) {
            $app = require_once __DIR__.'/../bootstrap/app.php';
            echo "Bootstrap: <span class='success'>✓ Loaded</span><br>";
            
            // Check if APP_KEY is set
            if (class_exists('Illuminate\Support\Facades\Config')) {
                echo "Config Class: <span class='success'>✓ Available</span><br>";
            }
        } else {
            echo "Bootstrap: <span class='error'>✗ File not found</span><br>";
        }
    } else {
        echo "Autoload: <span class='error'>✗ File not found</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Error: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 8. Check Laravel Log
echo "<h2>8. Recent Laravel Errors</h2>";
$logPath = __DIR__.'/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Last 50 lines
    echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:300px;'>";
    echo htmlspecialchars(implode("\n", $recentLines));
    echo "</pre>";
} else {
    echo "<span class='warning'>Log file not found</span><br>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If any items show <span class='error'>✗</span>, fix those first</li>";
echo "<li>Check the Laravel log above for specific errors</li>";
echo "<li>Ensure storage and bootstrap/cache are writable (775 permissions)</li>";
echo "<li>Make sure .env file has APP_KEY set</li>";
echo "<li><strong>DELETE THIS debug.php FILE after fixing!</strong></li>";
echo "</ol>";
?>
