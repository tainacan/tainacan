<?php
/**
 * Insert Mermaid diagrams into phpDocumentor .md files
 * 
 * For each .md file, if a corresponding .mmd file exists, insert it as a Mermaid code block
 * using Docsify's Mermaid plugin syntax.
 * 
 * Usage: php insert-diagrams-into-md.php <phpdoc_classes_dir>
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php insert-diagrams-into-md.php <phpdoc_classes_dir>\n");
    exit(1);
}

$phpdocDir = rtrim($argv[1], '/');

if (!is_dir($phpdocDir)) {
    fwrite(STDERR, "Error: phpDocumentor classes directory not found: $phpdocDir\n");
    exit(1);
}

// Scan all .md files in phpDocumentor output
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($phpdocDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$processedCount = 0;
$insertedCount = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'md') {
        $mdPath = $file->getPathname();
        $mmdPath = dirname($mdPath) . '/' . basename($mdPath, '.md') . '.mmd';
        
        $processedCount++;
        
        // Check if corresponding .mmd file exists
        if (file_exists($mmdPath) && is_file($mmdPath) && filesize($mmdPath) > 0) {
            $mdContent = file_get_contents($mdPath);
            if ($mdContent === false) {
                fwrite(STDERR, "Warning: Could not read $mdPath\n");
                continue;
            }
            
            // Check if diagram is already inserted (avoid duplicates)
            if (strpos($mdContent, '```mermaid') !== false) {
                // Diagram already exists, skip
                continue;
            }
            
            // Read Mermaid diagram content
            $mmdContent = file_get_contents($mmdPath);
            if ($mmdContent === false || trim($mmdContent) === '') {
                continue;
            }
            
            // Find the insertion point: after the "Full name" line (and optional parent class line)
            // Look for the pattern: * Full name: ... followed by optional parent class line, then ## or end
            // The pattern should match: * Full name: `...` followed by optional * Parent class: ... then newlines
            $insertionPattern = '/(\* Full name: `[^`]+`(?:\s*\*[^\n]+)?\n\n)/';
            
            if (preg_match($insertionPattern, $mdContent, $matches, PREG_OFFSET_CAPTURE)) {
                $insertPosition = $matches[0][1] + strlen($matches[0][0]);
                
                // Prepare the Mermaid diagram block
                $diagramBlock = "## Class Diagram\n\n```mermaid\n" . trim($mmdContent) . "\n```\n\n";
                
                // Insert the diagram
                $newContent = substr_replace($mdContent, $diagramBlock, $insertPosition, 0);
                
                // Write back to file
                if (file_put_contents($mdPath, $newContent) !== false) {
                    $insertedCount++;
                } else {
                    fwrite(STDERR, "Warning: Could not write to $mdPath\n");
                }
            } else {
                // If pattern not found, try inserting after the first ## heading (Properties or Methods)
                // Look for first ## heading after the metadata section
                if (preg_match('/(\n## [^\n]+\n)/', $mdContent, $matches, PREG_OFFSET_CAPTURE)) {
                    $insertPosition = $matches[0][1];
                    
                    // Prepare the Mermaid diagram block
                    $diagramBlock = "\n## Class Diagram\n\n```mermaid\n" . trim($mmdContent) . "\n```\n";
                    
                    // Insert the diagram
                    $newContent = substr_replace($mdContent, $diagramBlock, $insertPosition, 0);
                    
                    // Write back to file
                    if (file_put_contents($mdPath, $newContent) !== false) {
                        $insertedCount++;
                    } else {
                        fwrite(STDERR, "Warning: Could not write to $mdPath\n");
                    }
                } else {
                    fwrite(STDERR, "Warning: Could not find insertion point in $mdPath\n");
                }
            }
        }
    }
}

fwrite(STDERR, "Processed $processedCount .md files, inserted $insertedCount diagrams\n");

