<?php
/**
 * Remove methods and attributes from Mermaid class diagrams
 * 
 * Keeps only class names and relationships (inheritance, implementation, associations)
 * Removes all class body content (attributes and methods)
 * 
 * Usage: php remove-methods-attributes-from-mermaid.php <input_mmd_file> > <output_mmd_file>
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php remove-methods-attributes-from-mermaid.php <input_mmd_file>\n");
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
$filteredLines = [];
$inClassBody = false;
$braceCount = 0;

foreach ($lines as $line) {
    $trimmedLine = trim($line);
    $originalLine = $line;
    
    // Keep the classDiagram header
    if (strpos($trimmedLine, 'classDiagram') === 0) {
        $filteredLines[] = $originalLine;
        continue;
    }
    
    // Check if it's a class definition start: class ClassName {
    if (preg_match('/^\s*class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\{/', $trimmedLine, $matches)) {
        $className = $matches[1];
        $inClassBody = true;
        $braceCount = substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
        
        // For empty classes, we'll output: class ClassName { } on one line
        // Preserve indentation
        $indent = '';
        if (preg_match('/^(\s*)/', $originalLine, $indentMatch)) {
            $indent = $indentMatch[1];
        }
        
        // Check if class closes on same line
        if (strpos($trimmedLine, '}') !== false || $braceCount <= 0) {
            // Empty class on one line
            $filteredLines[] = $indent . 'class ' . $className . ' { }';
            $inClassBody = false;
            $braceCount = 0;
        } else {
            // Class opens on this line, will close later
            // Store for later combination
            $filteredLines[] = $indent . 'class ' . $className . ' {';
        }
        continue;
    }
    
    // If we're inside a class body, skip all content until closing brace
    if ($inClassBody) {
        $braceCount += substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
        
        // Check if class body is closed
        if ($braceCount <= 0) {
            // Class body is closed - combine opening and closing into one line
            // Format: class ClassName { }
            if (strpos($trimmedLine, '}') !== false) {
                // Get the last line (should be the class opening: "    class ClassName {")
                $lastLineIndex = count($filteredLines) - 1;
                if ($lastLineIndex >= 0) {
                    $lastLine = $filteredLines[$lastLineIndex];
                    // Extract class name and indentation
                    if (preg_match('/^(\s*)class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\{$/', trim($lastLine), $classMatch)) {
                        // Replace the last line with combined format
                        $indent = '';
                        if (preg_match('/^(\s*)/', $lastLine, $indentMatch)) {
                            $indent = $indentMatch[1];
                        }
                        $filteredLines[$lastLineIndex] = $indent . 'class ' . $classMatch[2] . ' { }';
                    } else {
                        // Fallback: just add the closing brace on same line if possible
                        // This shouldn't happen, but handle it
                        if (preg_match('/^(\s*)\}/', $originalLine, $braceMatch)) {
                            $filteredLines[] = $braceMatch[1] . '}';
                        }
                    }
                }
            }
            $inClassBody = false;
            $braceCount = 0;
        }
        // Skip all other lines inside class body (attributes and methods)
        continue;
    }
    
    // Keep relationship lines (inheritance, implementation, associations)
    // Mermaid syntax examples:
    //   ClassA <|-- ClassB (inheritance)
    //   ClassA <|.. ClassB (implementation)
    //   ClassA --> ClassB (association)
    //   ClassA ..> ClassB (dependency)
    //   ClassA <|.. ClassB : implements (with label)
    // Pattern: class name, whitespace, arrow pattern (can contain <, >, |, -, .), whitespace, class name, optional label
    if (preg_match('/^\s*([a-zA-Z_][a-zA-Z0-9_]*)\s+([<>\-\.\|]+)\s+([a-zA-Z_][a-zA-Z0-9_]*)(?:\s*:.*)?$/', $trimmedLine)) {
        $filteredLines[] = $originalLine;
        continue;
    }
    
    // Keep empty lines and comments
    if (empty($trimmedLine) || strpos($trimmedLine, '//') === 0) {
        $filteredLines[] = $originalLine;
        continue;
    }
    
    // Skip any other lines (shouldn't happen in valid Mermaid diagrams)
}

// Output filtered content
// Ensure there's a trailing newline
$output = implode("\n", $filteredLines);

// Add direction TB (top to bottom) for more compact vertical layout
// This helps prevent ultra-wide horizontal layouts
if (strpos($output, 'classDiagram') === 0) {
    // Check if direction is already specified
    if (strpos($output, 'direction') === false) {
        // Insert direction TB after classDiagram line
        $lines = explode("\n", $output);
        $newLines = [];
        foreach ($lines as $index => $line) {
            $newLines[] = $line;
            // Add direction after classDiagram header
            if ($index === 0 && trim($line) === 'classDiagram') {
                $newLines[] = '    direction TB';
            }
        }
        $output = implode("\n", $newLines);
    }
}

if (substr($output, -1) !== "\n") {
    $output .= "\n";
}
echo $output;

