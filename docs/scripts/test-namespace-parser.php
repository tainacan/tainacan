<?php
/**
 * Test script to debug namespace parsing
 */

$testFile = '/src/tainacan/src/classes/background-process/exporter/class-tainacan-exporter.php';

if (!file_exists($testFile)) {
    echo "File not found: $testFile\n";
    exit(1);
}

$content = file_get_contents($testFile);
$tokens = token_get_all($content);

$namespace = '';
$namespaceFound = false;

echo "Testing namespace parsing for: $testFile\n";
echo "First 50 tokens:\n";

for ($i = 0; $i < min(50, count($tokens)); $i++) {
    $token = $tokens[$i];
    if (is_array($token)) {
        $tokenName = token_name($token[0]);
        echo "[$i] $tokenName: " . substr($token[1], 0, 30) . "\n";
        
        if ($token[0] === T_NAMESPACE) {
            echo "  -> Found NAMESPACE token!\n";
            $namespaceFound = true;
            $namespace = '';
            $i++;
            // Skip whitespace
            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;
            }
            // Collect namespace parts
            echo "  -> Collecting namespace parts:\n";
            while ($i < count($tokens)) {
                if (is_array($tokens[$i])) {
                    $tokenName = token_name($tokens[$i][0]);
                    echo "    [$i] $tokenName: " . substr($tokens[$i][1], 0, 30) . "\n";
                    if ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR) {
                        $namespace .= $tokens[$i][1];
                        echo "      -> Added to namespace, current: [$namespace]\n";
                    } elseif ($tokens[$i][0] === T_WHITESPACE || $tokens[$i][0] === T_COMMENT) {
                        // Continue
                    } else {
                        echo "      -> Breaking (non-namespace token)\n";
                        break;
                    }
                } else {
                    echo "    [$i] Non-array: " . substr($tokens[$i], 0, 30) . "\n";
                    if ($tokens[$i] === ';' || $tokens[$i] === '{') {
                        echo "      -> Breaking (semicolon/brace)\n";
                        break;
                    }
                }
                $i++;
            }
            $namespace = trim($namespace);
            echo "  -> Final namespace: [$namespace]\n";
            break;
        }
    } else {
        echo "[$i] Non-array: " . substr($token, 0, 30) . "\n";
    }
}

echo "\nResult: Namespace = [$namespace]\n";

