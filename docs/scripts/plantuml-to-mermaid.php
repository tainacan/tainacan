<?php
/**
 * Convert PlantUML class diagram to Mermaid format
 * 
 * This script converts PlantUML class diagram syntax to Mermaid class diagram syntax
 * Usage: php plantuml-to-mermaid.php input.puml > output.mmd
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php plantuml-to-mermaid.php <plantuml_file>\n");
    exit(1);
}

$inputFile = $argv[1];
if (!file_exists($inputFile)) {
    fwrite(STDERR, "Error: File not found: $inputFile\n");
    exit(1);
}

$content = file_get_contents($inputFile);
if ($content === false) {
    fwrite(STDERR, "Error: Could not read file: $inputFile\n");
    exit(1);
}

// Start Mermaid class diagram
echo "classDiagram\n";

// Parse PlantUML content
$lines = explode("\n", $content);
$classes = [];
$relationships = [];
$currentPackage = null;
$inClass = false;
$currentClass = null;

foreach ($lines as $line) {
    $originalLine = $line;
    $line = trim($line);
    
    // Skip comments and empty lines
    if (empty($line) || strpos($line, "'") === 0 || strpos($line, "//") === 0 || strpos($line, "@startuml") === 0 || strpos($line, "@enduml") === 0) {
        continue;
    }
    
    // Handle package declarations (we'll flatten them for Mermaid)
    if (preg_match('/^package\s+([^\s{]+)/', $line, $matches)) {
        $currentPackage = $matches[1];
        continue;
    }
    
    // Handle class declarations with "as" alias (most common format: class "Name\nDescription" as Alias)
    if (preg_match('/^\s*class\s+"([^"]+)"\s+as\s+([^\s{]+)\s*\{/', $line, $matches)) {
        $className = trim($matches[1]);
        $alias = trim($matches[2]);
        
        // Extract class name from description (remove \n and HTML tags)
        $className = preg_replace('/\\n.*$/', '', $className);
        $className = strip_tags($className);
        $className = trim($className);
        
        $displayName = str_replace(['\\', '/', ' ', '-'], '_', $alias);
        $displayName = preg_replace('/[^a-zA-Z0-9_]/', '', $displayName);
        
        $classes[$className] = [
            'name' => $displayName,
            'type' => 'class',
            'abstract' => false,
            'package' => $currentPackage,
            'attributes' => [],
            'methods' => []
        ];
        $currentClass = $className;
        $inClass = true;
        continue;
    }
    
    // Handle class declarations without "as" (standard format)
    if (preg_match('/^(abstract\s+)?(class|interface|enum|trait)\s+([^\s{]+)/', $line, $matches)) {
        $type = $matches[2];
        $className = trim($matches[3]);
        
        // Clean up class name (remove quotes, namespace separators for display)
        $className = str_replace(['"', "'"], '', $className);
        $displayName = str_replace(['\\', '/', ' ', '-'], '_', $className);
        $displayName = preg_replace('/[^a-zA-Z0-9_]/', '', $displayName);
        
        $classes[$className] = [
            'name' => $displayName,
            'type' => $type,
            'abstract' => !empty($matches[1]),
            'package' => $currentPackage,
            'attributes' => [],
            'methods' => []
        ];
        $currentClass = $className;
        $inClass = true;
        continue;
    }
    
    // Parse attributes and methods when inside a class
    if ($inClass && $currentClass && $line !== '}') {
        // Handle attributes (format: +attribute : type or -attribute : type or +attribute :)
        if (preg_match('/^([+#-~])\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:\s*(.*)$/', $line, $matches)) {
            $visibility = $matches[1];
            $attrName = $matches[2];
            $attrType = trim($matches[3]);
            
            // If no type specified, use a default
            if (empty($attrType)) {
                $attrType = 'mixed';
            }
            
            // Convert PlantUML visibility to Mermaid
            $mermaidVis = '+'; // default public
            if ($visibility === '-') {
                $mermaidVis = '-'; // private
            } elseif ($visibility === '#') {
                $mermaidVis = '#'; // protected
            } elseif ($visibility === '~') {
                $mermaidVis = '~'; // package
            }
            
            if (!isset($classes[$currentClass]['attributes'])) {
                $classes[$currentClass]['attributes'] = [];
            }
            $classes[$currentClass]['attributes'][] = [
                'visibility' => $mermaidVis,
                'name' => $attrName,
                'type' => $attrType
            ];
            continue;
        }
        
        // Handle methods (format: +method() or +method(param) or {static} +method())
        if (preg_match('/^(\{static\}\s*)?([+#-~])\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)/', $line, $matches)) {
            $isStatic = !empty($matches[1]);
            $visibility = $matches[2];
            $methodName = $matches[3];
            
            // Extract parameters if present
            if (preg_match('/\(([^)]*)\)/', $line, $paramMatches)) {
                $params = $paramMatches[1];
            } else {
                $params = '';
            }
            
            // Convert PlantUML visibility to Mermaid
            $mermaidVis = '+'; // default public
            if ($visibility === '-') {
                $mermaidVis = '-'; // private
            } elseif ($visibility === '#') {
                $mermaidVis = '#'; // protected
            } elseif ($visibility === '~') {
                $mermaidVis = '~'; // package
            }
            
            // Mermaid uses $ prefix for static methods
            $methodDisplay = $methodName . '(' . $params . ')';
            if ($isStatic) {
                // Use $ prefix for static methods in Mermaid (e.g., +$methodName())
                $methodDisplay = '$' . $methodDisplay;
            }
            
            if (!isset($classes[$currentClass]['methods'])) {
                $classes[$currentClass]['methods'] = [];
            }
            $classes[$currentClass]['methods'][] = [
                'visibility' => $mermaidVis,
                'name' => $methodDisplay
            ];
            continue;
        }
    }
    
    // Handle class end
    if ($line === '}' && $inClass) {
        $inClass = false;
        $currentClass = null;
        continue;
    }
    
    // Handle inheritance (extends)
    if (preg_match('/^([^\s]+)\s+extends\s+([^\s]+)/', $line, $matches)) {
        $child = trim($matches[1]);
        $parent = trim($matches[2]);
        $relationships[] = [
            'type' => 'extends',
            'from' => str_replace(['"', "'"], '', $child),
            'to' => str_replace(['"', "'"], '', $parent)
        ];
        continue;
    }
    
    // Handle implementation (implements)
    if (preg_match('/^([^\s]+)\s+implements\s+([^\s]+)/', $line, $matches)) {
        $class = trim($matches[1]);
        $interface = trim($matches[2]);
        $relationships[] = [
            'type' => 'implements',
            'from' => str_replace(['"', "'"], '', $class),
            'to' => str_replace(['"', "'"], '', $interface)
        ];
        continue;
    }
    
    // Handle relationships with arrows
    if (preg_match('/^([^\s]+)\s+<\|--\s+([^\s]+)/', $line, $matches)) {
        $parent = trim($matches[1]);
        $child = trim($matches[2]);
        $relationships[] = [
            'type' => 'extends',
            'from' => str_replace(['"', "'"], '', $child),
            'to' => str_replace(['"', "'"], '', $parent)
        ];
        continue;
    }
    
    if (preg_match('/^([^\s]+)\s+<\|\.\.\s+([^\s]+)/', $line, $matches)) {
        $interface = trim($matches[1]);
        $class = trim($matches[2]);
        $relationships[] = [
            'type' => 'implements',
            'from' => str_replace(['"', "'"], '', $class),
            'to' => str_replace(['"', "'"], '', $interface)
        ];
        continue;
    }
    
    // Handle associations and dependencies (..>, -->, etc.)
    if (preg_match('/^([^\s]+)\s+\.\.\>\s+([^\s]+)/', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);
        $relationships[] = [
            'type' => 'association',
            'from' => str_replace(['"', "'"], '', $from),
            'to' => str_replace(['"', "'"], '', $to)
        ];
        continue;
    }
    
    if (preg_match('/^([^\s]+)\s+--\>\s+([^\s]+)/', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);
        $relationships[] = [
            'type' => 'association',
            'from' => str_replace(['"', "'"], '', $from),
            'to' => str_replace(['"', "'"], '', $to)
        ];
        continue;
    }
    
    // Handle class as declarations (alternative syntax: class "Name" as Alias)
    if (preg_match('/class\s+"?([^"]+)"?\s+as\s+([^\s{]+)/', $line, $matches)) {
        $className = trim($matches[1]);
        $alias = trim($matches[2]);
        $displayName = str_replace('\\', '_', $alias);
        
        // Extract class name from description if it contains \n
        $className = preg_replace('/\\n.*$/', '', $className);
        $className = trim($className);
        
        if (!isset($classes[$className])) {
            $classes[$className] = [
                'name' => $displayName,
                'type' => 'class',
                'abstract' => false,
                'package' => $currentPackage,
                'attributes' => [],
                'methods' => []
            ];
        }
        continue;
    }
    
    // Handle class declarations with description (class "Name\n<b>Description</b>" as Alias)
    // This is a duplicate check - already handled above, but keeping for safety
    if (preg_match('/^\s*class\s+"([^"]+)"\s+as\s+([^\s{]+)\s*\{/', $line, $matches)) {
        $className = trim($matches[1]);
        $alias = trim($matches[2]);
        $displayName = str_replace(['\\', '/', ' ', '-'], '_', $alias);
        $displayName = preg_replace('/[^a-zA-Z0-9_]/', '', $displayName);
        
        // Extract class name from description
        $className = preg_replace('/\\n.*$/', '', $className);
        $className = strip_tags($className);
        $className = trim($className);
        
        if (!isset($classes[$className])) {
            $classes[$className] = [
                'name' => $displayName,
                'type' => 'class',
                'abstract' => false,
                'package' => $currentPackage,
                'attributes' => [],
                'methods' => []
            ];
        }
        $currentClass = $className;
        $inClass = true;
        continue;
    }
}

// Output classes
foreach ($classes as $originalName => $class) {
    $name = str_replace(['\\', '/', ' ', '-'], '_', $class['name']);
    $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name); // Clean name for Mermaid
    
    echo "    class {$name} {\n";
    
    // Add type annotation
    if ($class['type'] === 'interface') {
        echo "        <<interface>>\n";
    } elseif ($class['type'] === 'enum') {
        echo "        <<enumeration>>\n";
    } elseif ($class['type'] === 'trait') {
        echo "        <<trait>>\n";
    }
    
    // Output attributes
    if (isset($class['attributes']) && !empty($class['attributes'])) {
        foreach ($class['attributes'] as $attr) {
            $attrName = $attr['name'];
            $attrType = $attr['type'];
            // Clean up type (remove HTML tags, extra spaces)
            $attrType = strip_tags($attrType);
            $attrType = trim($attrType);
            if (empty($attrType)) {
                $attrType = 'mixed';
            }
            echo "        {$attr['visibility']}{$attrName} : {$attrType}\n";
        }
    }
    
    // Output methods
    if (isset($class['methods']) && !empty($class['methods'])) {
        foreach ($class['methods'] as $method) {
            $methodName = $method['name'];
            // Clean up method name (remove HTML tags and any leftover {static} notation)
            $methodName = strip_tags($methodName);
            $methodName = preg_replace('/\{static\}\s*/', '', $methodName);
            // Ensure $ is only at the start if it's a static method (already handled in parsing, but clean up just in case)
            echo "        {$method['visibility']}{$methodName}\n";
        }
    }
    
    echo "    }\n";
}

// Output relationships
foreach ($relationships as $rel) {
    $from = str_replace(['\\', '/', ' ', '-', '"', "'"], '_', $rel['from']);
    $to = str_replace(['\\', '/', ' ', '-', '"', "'"], '_', $rel['to']);
    $from = preg_replace('/[^a-zA-Z0-9_]/', '', $from);
    $to = preg_replace('/[^a-zA-Z0-9_]/', '', $to);
    
    // Skip if class names are empty or invalid
    if (empty($from) || empty($to)) {
        continue;
    }
    
    if ($rel['type'] === 'extends') {
        echo "    {$to} <|-- {$from}\n";
    } elseif ($rel['type'] === 'implements') {
        echo "    {$to} <|.. {$from} : implements\n";
    } elseif ($rel['type'] === 'association') {
        echo "    {$from} ..> {$to}\n";
    }
}

