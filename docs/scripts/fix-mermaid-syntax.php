<?php
/**
 * Fix common Mermaid class diagram syntax issues
 * 
 * Fixes issues like:
 * - Ensures class names in relationships match class definitions exactly
 * - Removes trailing whitespace
 * - Ensures proper spacing
 * 
 * Usage: php fix-mermaid-syntax.php <input_mmd_file> > <output_mmd_file>
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php fix-mermaid-syntax.php <input_mmd_file>\n");
    exit(1);
}

$inputFile = $argv[1];

if (!file_exists($inputFile)) {
    fwrite(STDERR, "Error: Mermaid file not found: $inputFile\n");
    exit(1);
}

$content = file_get_contents($inputFile);
if ($content === false) {
    fwrite(STDERR, "Error: Could not read file: $inputFile\n");
    exit(1);
}

$lines = explode("\n", $content);
$fixedLines = [];
$definedClasses = [];

// First pass: collect all defined class names
foreach ($lines as $line) {
    if (preg_match('/^\s*class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\{/', $line, $matches)) {
        $definedClasses[$matches[1]] = true;
    }
}

// Second pass: fix relationships to use exact class names
foreach ($lines as $line) {
    $originalLine = $line;
    $trimmedLine = trim($line);
    
    // Keep classDiagram header and class definitions as-is
    if (strpos($trimmedLine, 'classDiagram') === 0 || 
        preg_match('/^\s*class\s+[a-zA-Z_][a-zA-Z0-9_]*\s*\{/', $trimmedLine) ||
        preg_match('/^\s*\}/', $trimmedLine) ||
        empty($trimmedLine)) {
        $fixedLines[] = $originalLine;
        continue;
    }
    
    // Fix relationship lines
    // Pattern: ClassA <|-- ClassB or ClassA ..> ClassB
    if (preg_match('/^\s*([a-zA-Z_][a-zA-Z0-9_]*)\s+([<>\-\.\|]+)\s+([a-zA-Z_][a-zA-Z0-9_]*)(?:\s*:.*)?$/', $trimmedLine, $matches)) {
        $fromClass = $matches[1];
        $arrow = $matches[2];
        $toClass = $matches[3];
        
        // Check if both classes are defined
        $fromExists = isset($definedClasses[$fromClass]);
        $toExists = isset($definedClasses[$toClass]);
        
        if ($fromExists && $toExists) {
            // Both classes exist, keep the relationship
            // Preserve original indentation
            $indent = '';
            if (preg_match('/^(\s*)/', $originalLine, $indentMatch)) {
                $indent = $indentMatch[1];
            }
            $fixedLines[] = $indent . $fromClass . ' ' . $arrow . ' ' . $toClass;
        } else {
            // Skip relationships with undefined classes (they would cause errors)
            // But log a warning
            if (!$fromExists) {
                fwrite(STDERR, "Warning: Class '$fromClass' not defined, skipping relationship\n");
            }
            if (!$toExists) {
                fwrite(STDERR, "Warning: Class '$toClass' not defined, skipping relationship\n");
            }
        }
        continue;
    }
    
    // Keep other lines as-is
    $fixedLines[] = $originalLine;
}

// Output fixed content
echo implode("\n", $fixedLines);

