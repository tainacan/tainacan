<?php
/**
 * Insert namespace overview diagrams into Home.md
 * 
 * For each namespace section in Home.md, inserts the corresponding
 * namespace overview diagram if it exists.
 * 
 * Usage: php insert-namespace-diagrams-into-home.php <home_md_file> <diagrams_dir>
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php insert-namespace-diagrams-into-home.php <home_md_file> <diagrams_dir>\n");
    exit(1);
}

$homeMdFile = $argv[1];
$diagramsDir = rtrim($argv[2], '/');

if (!file_exists($homeMdFile)) {
    fwrite(STDERR, "Error: Home.md file not found: $homeMdFile\n");
    exit(1);
}

if (!is_dir($diagramsDir)) {
    fwrite(STDERR, "Error: Diagrams directory not found: $diagramsDir\n");
    exit(1);
}

$content = file_get_contents($homeMdFile);
if ($content === false) {
    fwrite(STDERR, "Error: Could not read Home.md file\n");
    exit(1);
}

// Find all namespace sections: ### \Namespace or ### \ (root namespace)
// Pattern: ### \Tainacan\Exporter or ### \
$pattern = '/^(### \\\\[^\n]*)$/m';
$matches = [];
preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

$insertedCount = 0;
$offset = 0;
$insertedDiagrams = []; // Track which diagrams we've already inserted to avoid duplicates

// Process matches in reverse order to maintain offsets
$matches[0] = array_reverse($matches[0]);

foreach ($matches[0] as $match) {
    $namespaceLine = $match[0];
    $position = $match[1];
    
          // Extract namespace from the line: ### \Tainacan\Exporter -> Tainacan\Exporter
          // Or: ### \ -> Tainacan (root namespace)
          // Or: ### \Tainacan -> Tainacan (also root namespace)
          if (preg_match('/^### (\\\\)(.*)$/', $namespaceLine, $nsMatches)) {
              $namespacePart = $nsMatches[2]; // Part after the backslash
              
              // Handle root namespace (just \ or \Tainacan - both are the same)
              if (empty($namespacePart) || $namespacePart === 'Tainacan') {
                  $namespace = 'Tainacan';
              } else {
                  $namespace = $namespacePart; // Already without leading backslash
              }
              
              // Convert namespace to diagram filename: Tainacan\Exporter -> Tainacan-Exporter
              // For root: Tainacan -> Tainacan
              $diagramName = str_replace('\\', '-', $namespace);
              $diagramFile = $diagramsDir . '/tainacan-namespace-' . $diagramName . '.mmd';
              
              // Skip if we've already inserted this diagram
              if (isset($insertedDiagrams[$diagramFile])) {
                  continue;
              }
        
        // Check if diagram exists
        if (file_exists($diagramFile) && is_file($diagramFile) && filesize($diagramFile) > 0) {
            // Read diagram content
            $diagramContent = file_get_contents($diagramFile);
            if ($diagramContent !== false && trim($diagramContent) !== '') {
                // Check if diagram is already inserted (avoid duplicates)
                // Look for the diagram after this namespace header
                $sectionStart = $position;
                $nextSection = strpos($content, "\n### ", $sectionStart + 1);
                $sectionEnd = $nextSection !== false ? $nextSection : strlen($content);
                $sectionContent = substr($content, $sectionStart, $sectionEnd - $sectionStart);
                
                // Check if diagram already exists in this section
                // Only skip if BOTH the mermaid block and the header exist (meaning diagram is already inserted)
                if (strpos($sectionContent, '#### Namespace Diagram') === false || 
                    strpos($sectionContent, '```mermaid') === false) {
                    
                    // Find insertion point: after the namespace header, before the first subsection
                    // Look for "#### Classes" or "#### Traits" after the namespace header
                    $insertPos = $position + strlen($namespaceLine);
                    
                    // Skip any empty lines after the header
                    while ($insertPos < strlen($content) && 
                           ($content[$insertPos] === "\n" || $content[$insertPos] === "\r")) {
                        $insertPos++;
                    }
                    
                    // Prepare the diagram block
                    // Ensure proper formatting: trim and add newline at end
                    $cleanContent = trim($diagramContent);
                    if (!empty($cleanContent)) {
                        $diagramBlock = "\n\n#### Namespace Diagram\n\n```mermaid\n" . $cleanContent . "\n```\n";
                    } else {
                        // Skip empty diagrams
                        continue;
                    }
                    
                    // Insert the diagram
                    $content = substr_replace($content, $diagramBlock, $insertPos, 0);
                    $insertedCount++;
                    $insertedDiagrams[$diagramFile] = true; // Mark as inserted
                }
            }
        }
    }
}

// Write back to file
if (file_put_contents($homeMdFile, $content) === false) {
    fwrite(STDERR, "Error: Could not write to Home.md file\n");
    exit(1);
}

fwrite(STDERR, "Inserted $insertedCount namespace diagrams into Home.md\n");

