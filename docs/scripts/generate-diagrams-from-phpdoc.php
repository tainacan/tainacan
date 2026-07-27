<?php
/**
 * Extract class information from phpDocumentor output
 * 
 * Scans phpDocumentor's generated .md files and extracts class names and locations.
 * Outputs JSON mapping .md files to class names for diagram generation.
 * 
 * Usage: php generate-diagrams-from-phpdoc.php <phpdoc_classes_dir>
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php generate-diagrams-from-phpdoc.php <phpdoc_classes_dir>\n");
    exit(1);
}

$phpdocDir = rtrim($argv[1], '/');

if (!is_dir($phpdocDir)) {
    fwrite(STDERR, "Error: phpDocumentor classes directory not found: $phpdocDir\n");
    exit(1);
}

/**
 * Extract full class name from phpDocumentor .md file
 */
function extractClassNameFromMd($mdFile) {
    $content = file_get_contents($mdFile);
    if ($content === false) {
        return null;
    }
    
    // Look for "Full name: `\Namespace\Class`" pattern
    // Pattern matches: * Full name: `\Namespace\Class`
    // The backslash before the namespace is literal, so we need \\ to match it
    if (preg_match('/\* Full name: `\\\\([^`]+)`/', $content, $matches)) {
        return $matches[1]; // Return without leading backslash
    }
    
    return null;
}

// Scan all .md files in phpDocumentor output
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($phpdocDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$classes = [];
$namespaces = [];
$processedCount = 0;
$foundCount = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'md') {
        $processedCount++;
        $mdPath = $file->getPathname();
        $fullClassName = extractClassNameFromMd($mdPath);
        
        if ($fullClassName) {
            $foundCount++;
            // Extract namespace and class name
            $parts = explode('\\', $fullClassName);
            $className = end($parts);
            $namespace = implode('\\', array_slice($parts, 0, -1));
            
            // Get relative path from phpdocDir (this matches the namespace structure)
            // This will be used to place diagrams next to .md files
            $relPath = substr($mdPath, strlen($phpdocDir) + 1);
            $relDir = dirname($relPath);
            $mdFileName = basename($relPath, '.md');
            
            // The diagram should be placed next to the .md file
            // So if .md is at Tainacan/Repositories/Collections.md
            // The diagram should be at Tainacan/Repositories/Collections.mmd
            $diagramPath = $relDir . '/' . $mdFileName . '.mmd';
            if ($relDir === '.') {
                $diagramPath = $mdFileName . '.mmd';
            }
            
            $classes[] = [
                'fullName' => $fullClassName,
                'name' => $className,
                'namespace' => $namespace,
                'mdFile' => $mdPath,
                'mdRelPath' => $relPath,
                'mdRelDir' => $relDir,
                'mdFileName' => $mdFileName,
                'diagramPath' => $diagramPath  // Where the diagram should be placed (relative to phpdoc/classes)
            ];
            
            // Track namespaces
            if (!empty($namespace)) {
                if (!isset($namespaces[$namespace])) {
                    $namespaces[$namespace] = [];
                }
                $namespaces[$namespace][] = $fullClassName;
            }
        }
    }
}

// Debug output to stderr
fwrite(STDERR, "Processed $processedCount .md files, found $foundCount classes\n");

// Output JSON with class information
$output = [
    'classes' => $classes,
    'namespaces' => $namespaces
];

$json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($json === false) {
    fwrite(STDERR, "Error: Failed to encode JSON: " . json_last_error_msg() . "\n");
    exit(1);
}

// Output JSON to stdout (not stderr)
echo $json;

