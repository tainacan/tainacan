<?php
/**
 * Filter child classes from Mermaid class diagrams
 * 
 * For per-class diagrams, removes classes that extend the target class,
 * keeping only parent classes and interfaces.
 * 
 * Usage: php filter-child-classes-from-mermaid.php <mmd_file> <target_class_name>
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php filter-child-classes-from-mermaid.php <mmd_file> <target_class_name>\n");
    exit(1);
}

$mmdFile = $argv[1];
$targetClassName = $argv[2];

if (!file_exists($mmdFile)) {
    fwrite(STDERR, "Error: Mermaid file not found: $mmdFile\n");
    exit(1);
}

$content = file_get_contents($mmdFile);
if ($content === false) {
    fwrite(STDERR, "Error: Could not read file: $mmdFile\n");
    exit(1);
}

// Parse Mermaid class diagram
// We need to:
// 1. Find all class definitions
// 2. Find all inheritance relationships (--|> or <|--)
// 3. Remove classes that extend the target class
// 4. Remove relationships involving removed classes

$lines = explode("\n", $content);
$filteredLines = [];
$classesToKeep = [];
$classesToRemove = [];

// First pass: identify classes and relationships
$classDefinitions = [];
$inheritanceRelations = [];

foreach ($lines as $line) {
    $line = trim($line);
    
    // Skip empty lines and comments
    if (empty($line) || strpos($line, '//') === 0) {
        continue;
    }
    
    // Match class definitions: class ClassName { ... }
    // Handle both single-line and multi-line class definitions
    if (preg_match('/^\s*class\s+(\w+)\s*\{/', $line, $matches)) {
        $className = $matches[1];
        $classDefinitions[$className] = $line;
        
        // Always keep the target class
        if ($className === $targetClassName) {
            $classesToKeep[$className] = true;
        }
    }
    
    // Match inheritance relationships: ClassA --|> ClassB (ClassA extends ClassB)
    // or ClassA <|-- ClassB (ClassB extends ClassA)
    if (preg_match('/^(\w+)\s+--\|>\s+(\w+)/', $line, $matches)) {
        // $matches[1] extends $matches[2]
        $childClass = $matches[1];
        $parentClass = $matches[2];
        $inheritanceRelations[] = ['child' => $childClass, 'parent' => $parentClass, 'line' => $line];
        
        // If target class is the parent, mark child for removal
        if ($parentClass === $targetClassName) {
            $classesToRemove[$childClass] = true;
        }
        
        // If target class is the child, keep the parent
        if ($childClass === $targetClassName) {
            $classesToKeep[$parentClass] = true;
        }
    } elseif (preg_match('/^(\w+)\s+<\|--\s+(\w+)/', $line, $matches)) {
        // $matches[2] extends $matches[1]
        $childClass = $matches[2];
        $parentClass = $matches[1];
        $inheritanceRelations[] = ['child' => $childClass, 'parent' => $parentClass, 'line' => $line];
        
        // If target class is the parent, mark child for removal
        if ($parentClass === $targetClassName) {
            $classesToRemove[$childClass] = true;
        }
        
        // If target class is the child, keep the parent
        if ($childClass === $targetClassName) {
            $classesToKeep[$parentClass] = true;
        }
    }
    
    // Match interface implementation: ClassA ..|> InterfaceB
    if (preg_match('/^(\w+)\s+\.\.\|>\s+(\w+)/', $line, $matches)) {
        $implementingClass = $matches[1];
        $interface = $matches[2];
        
        // If target class implements an interface, keep the interface
        if ($implementingClass === $targetClassName) {
            $classesToKeep[$interface] = true;
        }
        
        // If a child class implements an interface, we'll remove it when we remove the child
    }
}

// Recursively find all classes to keep (parents of parents, etc.)
$changed = true;
while ($changed) {
    $changed = false;
    foreach ($inheritanceRelations as $rel) {
        $child = $rel['child'];
        $parent = $rel['parent'];
        
        // If child is kept and not the target, keep its parent
        if (isset($classesToKeep[$child]) && $child !== $targetClassName) {
            if (!isset($classesToKeep[$parent])) {
                $classesToKeep[$parent] = true;
                $changed = true;
            }
        }
    }
}

// Build final list: target class + kept classes - removed classes
$finalClasses = array_merge([$targetClassName => true], $classesToKeep);
$finalClasses = array_diff_key($finalClasses, $classesToRemove);

// Second pass: filter lines, preserving class body content
$insideClass = null;
$braceCount = 0;
$inClassBody = false;

// Always keep the classDiagram header
if (!empty($lines) && strpos(trim($lines[0]), 'classDiagram') === 0) {
    $filteredLines[] = $lines[0];
    array_shift($lines); // Remove from processing
}

foreach ($lines as $line) {
    $trimmedLine = trim($line);
    $originalLine = $line;
    
    // Keep empty lines and comments
    if (empty($trimmedLine) || strpos($trimmedLine, '//') === 0) {
        if ($insideClass === null || isset($finalClasses[$insideClass])) {
            $filteredLines[] = $originalLine;
        }
        continue;
    }
    
    // Check if it's a class definition start: class ClassName {
    // Handle indented class definitions (Mermaid uses indentation)
    if (preg_match('/^\s*class\s+(\w+)\s*\{/', $trimmedLine, $matches)) {
        $className = $matches[1];
        if (isset($finalClasses[$className])) {
            $insideClass = $className;
            $inClassBody = true;
            $braceCount = substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
            $filteredLines[] = $originalLine;
        } else {
            $insideClass = $className;
            $inClassBody = true;
            $braceCount = substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
            // Don't add this line, we're skipping this class
        }
        continue;
    }
    
    // If we're inside a class body, keep all content until the closing brace
    if ($inClassBody && $insideClass !== null) {
        if (isset($finalClasses[$insideClass])) {
            // We're keeping this class, so keep all its content
            $filteredLines[] = $originalLine;
            
            // Update brace count
            $braceCount += substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
            
            // Check if class body is closed
            if ($braceCount <= 0) {
                $insideClass = null;
                $inClassBody = false;
                $braceCount = 0;
            }
        } else {
            // We're skipping this class, don't add content
            $braceCount += substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
            
            // Check if class body is closed
            if ($braceCount <= 0) {
                $insideClass = null;
                $inClassBody = false;
                $braceCount = 0;
            }
        }
        continue;
    }
    
    // Check if it's a relationship line (only process if not inside a class)
    $keepLine = false;
    
    // Check inheritance relationships
    foreach ($inheritanceRelations as $rel) {
        if ($trimmedLine === $rel['line']) {
            // Keep if both classes are in finalClasses
            if (isset($finalClasses[$rel['child']]) && isset($finalClasses[$rel['parent']])) {
                $keepLine = true;
            }
            break;
        }
    }
    
    // Check interface implementation
    if (!$keepLine && preg_match('/^(\w+)\s+\.\.\|>\s+(\w+)/', $trimmedLine, $matches)) {
        $implementingClass = $matches[1];
        $interface = $matches[2];
        if (isset($finalClasses[$implementingClass]) && isset($finalClasses[$interface])) {
            $keepLine = true;
        }
    }
    
    // Check other relationship types (association, composition, etc.)
    if (!$keepLine && preg_match('/^(\w+)\s+[<>\-\.]+>\s+(\w+)/', $trimmedLine, $matches)) {
        $class1 = $matches[1];
        $class2 = $matches[2];
        if (isset($finalClasses[$class1]) && isset($finalClasses[$class2])) {
            $keepLine = true;
        }
    }
    
    if ($keepLine) {
        $filteredLines[] = $originalLine;
    }
}

// Output filtered content
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

