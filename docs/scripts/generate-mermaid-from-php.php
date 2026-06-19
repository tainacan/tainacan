<?php
/**
 * Generate Mermaid class diagram directly from PHP source code
 * 
 * This script uses PHP's Reflection API to analyze PHP classes and generate
 * Mermaid class diagrams directly, without needing PlantUML as an intermediate step.
 * 
 * Usage: php generate-mermaid-from-php.php /path/to/php/source > output.mmd
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php generate-mermaid-from-php.php <source_directory>\n");
    exit(1);
}

$sourceDir = $argv[1];
if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Error: Directory not found: $sourceDir\n");
    exit(1);
}

// Get all PHP files recursively
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$classes = [];
$relationships = [];

// First pass: collect all classes, interfaces, and traits
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        
        // Skip vendor, tests, node_modules
        if (strpos($filePath, '/vendor/') !== false || 
            strpos($filePath, '/tests/') !== false ||
            strpos($filePath, '/node_modules/') !== false) {
            continue;
        }
        
        try {
            $tokens = token_get_all(file_get_contents($filePath));
            $namespace = '';
            $currentClass = null;
            $currentType = null;
            
            for ($i = 0; $i < count($tokens); $i++) {
                if (is_array($tokens[$i])) {
                    $token = $tokens[$i][0];
                    $value = $tokens[$i][1];
                    
                    // Get namespace
                    if ($token === T_NAMESPACE) {
                        $namespace = '';
                        $i++;
                        while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] !== T_STRING && $tokens[$i][0] !== T_NS_SEPARATOR) {
                            $i++;
                        }
                        while ($i < count($tokens) && (is_array($tokens[$i]) && ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR))) {
                            if ($tokens[$i][0] === T_STRING) {
                                $namespace .= $tokens[$i][1];
                            } else {
                                $namespace .= '\\';
                            }
                            $i++;
                        }
                    }
                    
                    // Get class/interface/trait name
                    if (($token === T_CLASS || $token === T_INTERFACE || $token === T_TRAIT) && 
                        $i + 2 < count($tokens) && 
                        is_array($tokens[$i + 2]) && 
                        $tokens[$i + 2][0] === T_STRING) {
                        
                        $currentType = $token === T_CLASS ? 'class' : ($token === T_INTERFACE ? 'interface' : 'trait');
                        $className = $tokens[$i + 2][1];
                        $fullName = $namespace ? $namespace . '\\' . $className : $className;
                        
                        // Check for extends
                        $extends = null;
                        $implements = [];
                        $j = $i + 3;
                        while ($j < count($tokens) && $tokens[$j] !== '{' && $tokens[$j] !== ';') {
                            if (is_array($tokens[$j]) && $tokens[$j][0] === T_EXTENDS) {
                                $j++;
                                $extends = '';
                                while ($j < count($tokens) && $tokens[$j] !== '{' && $tokens[$j] !== T_IMPLEMENTS && $tokens[$j] !== ';') {
                                    if (is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR)) {
                                        $extends .= $tokens[$j][1];
                                    }
                                    $j++;
                                }
                            }
                            
                            if (is_array($tokens[$j]) && $tokens[$j][0] === T_IMPLEMENTS) {
                                $j++;
                                $implementsStr = '';
                                while ($j < count($tokens) && $tokens[$j] !== '{' && $tokens[$j] !== ';') {
                                    if (is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR)) {
                                        $implementsStr .= $tokens[$j][1];
                                    } elseif ($tokens[$j] === ',') {
                                        if ($implementsStr) {
                                            $implements[] = trim($implementsStr);
                                            $implementsStr = '';
                                        }
                                    }
                                    $j++;
                                }
                                if ($implementsStr) {
                                    $implements[] = trim($implementsStr);
                                }
                            }
                            $j++;
                        }
                        
                        $displayName = str_replace(['\\', '/', ' ', '-'], '_', $fullName);
                        $displayName = preg_replace('/[^a-zA-Z0-9_]/', '', $displayName);
                        
                        $classes[$fullName] = [
                            'name' => $displayName,
                            'type' => $currentType,
                            'namespace' => $namespace,
                            'extends' => $extends,
                            'implements' => $implements,
                            'file' => $filePath
                        ];
                        
                        break; // Only process first class/interface/trait per file
                    }
                }
            }
        } catch (Exception $e) {
            // Skip files with parse errors
            continue;
        }
    }
}

// Define ABSPATH early to bypass WordPress security checks
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
}

// Second pass: use Reflection API to get detailed class information
foreach ($classes as $fullName => &$class) {
    try {
        if (!file_exists($class['file'])) {
            continue;
        }
        
        // Try to require the file (ABSPATH is already defined to bypass security)
        try {
            ob_start(); // Suppress any output or errors
            @require_once $class['file'];
            ob_end_clean();
        } catch (Exception $e) {
            // Continue even if require fails
        }
        
        // Try to reflect the class
        if (($class['type'] === 'class' && class_exists($fullName)) ||
            ($class['type'] === 'interface' && interface_exists($fullName)) ||
            ($class['type'] === 'trait' && trait_exists($fullName))) {
            
            $reflection = new ReflectionClass($fullName);
            
            $class['attributes'] = [];
            $class['methods'] = [];
            
            // Get properties
            foreach ($reflection->getProperties() as $property) {
                // Only get properties declared in this class
                if ($property->getDeclaringClass()->getName() !== $fullName) {
                    continue;
                }
                
                $visibility = '+';
                if ($property->isPrivate()) {
                    $visibility = '-';
                } elseif ($property->isProtected()) {
                    $visibility = '#';
                }
                
                $type = 'mixed';
                // Try to get type from docblock
                $docComment = $property->getDocComment();
                if ($docComment && preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
                    $type = $matches[1];
                }
                
                $propName = $property->getName();
                if ($property->isStatic()) {
                    $propName = '$' . $propName;
                }
                
                $class['attributes'][] = [
                    'visibility' => $visibility,
                    'name' => $propName,
                    'type' => $type,
                    'static' => $property->isStatic()
                ];
            }
            
            // Get methods
            foreach ($reflection->getMethods() as $method) {
                // Skip methods from parent classes unless they're overridden
                if ($method->getDeclaringClass()->getName() !== $fullName && !$method->isAbstract()) {
                    continue;
                }
                
                $visibility = '+';
                if ($method->isPrivate()) {
                    $visibility = '-';
                } elseif ($method->isProtected()) {
                    $visibility = '#';
                }
                
                $params = [];
                foreach ($method->getParameters() as $param) {
                    $paramStr = $param->getName();
                    if ($param->hasType()) {
                        $type = $param->getType();
                        $paramStr = $type . ' $' . $paramStr;
                    } else {
                        $paramStr = '$' . $paramStr;
                    }
                    if ($param->isOptional()) {
                        $paramStr .= ' = ' . ($param->isDefaultValueAvailable() ? var_export($param->getDefaultValue(), true) : 'null');
                    }
                    $params[] = $paramStr;
                }
                
                $methodName = $method->getName();
                if ($method->isStatic()) {
                    $methodName = '$' . $methodName; // Mermaid uses $ for static
                }
                
                $class['methods'][] = [
                    'visibility' => $visibility,
                    'name' => $methodName . '(' . implode(', ', $params) . ')',
                    'static' => $method->isStatic()
                ];
            }
        }
        
        // Store relationships
        if ($class['extends']) {
            $parentName = str_replace(['\\', '/', ' ', '-'], '_', preg_replace('/[^a-zA-Z0-9_]/', '', $class['extends']));
            $relationships[] = [
                'type' => 'extends',
                'from' => $class['name'],
                'to' => $parentName
            ];
        }
        
        foreach ($class['implements'] as $interface) {
            $interfaceName = str_replace(['\\', '/', ' ', '-'], '_', preg_replace('/[^a-zA-Z0-9_]/', '', $interface));
            $relationships[] = [
                'type' => 'implements',
                'from' => $class['name'],
                'to' => $interfaceName
            ];
        }
        
    } catch (Exception $e) {
        // Skip classes that can't be processed
        continue;
    }
}

// Generate Mermaid diagram
echo "classDiagram\n";

// Output classes
foreach ($classes as $originalName => $class) {
    if (empty($class['name'])) {
        continue;
    }
    
    echo "    class {$class['name']} {\n";
    
    // Add type annotation
    if ($class['type'] === 'interface') {
        echo "        <<interface>>\n";
    } elseif ($class['type'] === 'trait') {
        echo "        <<trait>>\n";
    }
    
    // Output attributes
    if (isset($class['attributes']) && !empty($class['attributes'])) {
        foreach ($class['attributes'] as $attr) {
            $attrName = $attr['name'];
            if ($attr['static']) {
                $attrName = '$' . $attrName; // Static attributes use $ prefix
            }
            echo "        {$attr['visibility']}{$attrName} : {$attr['type']}\n";
        }
    }
    
    // Output methods
    if (isset($class['methods']) && !empty($class['methods'])) {
        foreach ($class['methods'] as $method) {
            echo "        {$method['visibility']}{$method['name']}\n";
        }
    }
    
    echo "    }\n";
}

// Output relationships
foreach ($relationships as $rel) {
    if (empty($rel['from']) || empty($rel['to'])) {
        continue;
    }
    
    if ($rel['type'] === 'extends') {
        echo "    {$rel['to']} <|-- {$rel['from']}\n";
    } elseif ($rel['type'] === 'implements') {
        echo "    {$rel['to']} <|.. {$rel['from']} : implements\n";
    }
}

