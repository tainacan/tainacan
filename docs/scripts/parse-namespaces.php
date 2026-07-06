<?php
/**
 * Parse PHP namespaces and classes from Tainacan source code
 * 
 * Scans PHP files to extract:
 * - All namespaces
 * - All classes with their namespace and file path
 * - Class relationships (extends, implements)
 * 
 * Usage: php parse-namespaces.php [classes_directory]
 * Output: JSON to stdout
 */

$classesDir = isset($argv[1]) ? $argv[1] : '/src/tainacan/src/classes';

if (!is_dir($classesDir)) {
    fwrite(STDERR, "Error: Directory not found: $classesDir\n");
    exit(1);
}

$namespaces = [];
$classes = [];
$relationships = [];

/**
 * Recursively scan directory for PHP files
 */
function scanDirectory($dir, &$namespaces, &$classes, &$relationships) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            parsePhpFile($file->getPathname(), $namespaces, $classes, $relationships);
        }
    }
}

/**
 * Parse a PHP file to extract namespace, class, and relationships
 */
function parsePhpFile($filePath, &$namespaces, &$classes, &$relationships) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        return;
    }
    
    $tokens = token_get_all($content);
    $namespace = '';
    $currentClass = null;
    $currentClassType = null;
    $inClass = false;
    $braceCount = 0;
    $namespaceFound = false;
    
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];
        
        if (!is_array($token)) {
            if ($token === '{' && $inClass) {
                $braceCount++;
            } elseif ($token === '}' && $inClass) {
                $braceCount--;
                if ($braceCount === 0) {
                    $inClass = false;
                    $currentClass = null;
                    $currentClassType = null;
                }
            }
            continue;
        }
        
        $tokenType = $token[0];
        $tokenValue = $token[1];
        $line = $token[2];
        
        // Parse namespace
        if ($tokenType === T_NAMESPACE && !$namespaceFound) {
            $namespace = '';
            $namespaceFound = true;
            $i++;
            // Skip whitespace
            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;
            }
            // Collect namespace parts
            while ($i < count($tokens)) {
                if (is_array($tokens[$i])) {
                    if ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR) {
                        $namespace .= $tokens[$i][1];
                    } elseif ($tokens[$i][0] === T_WHITESPACE || $tokens[$i][0] === T_COMMENT) {
                        // Continue
                    } else {
                        break;
                    }
                } else {
                    if ($tokens[$i] === ';' || $tokens[$i] === '{') {
                        break;
                    }
                }
                $i++;
            }
            $namespace = trim($namespace);
            
            // Debug: Log namespace parsing for specific files
            static $nsDebugCount = 0;
            if ($nsDebugCount < 10 && (
                strpos($filePath, 'exporter') !== false || 
                strpos($filePath, 'collections') !== false ||
                strpos($filePath, 'repositories') !== false ||
                strpos($filePath, 'api/endpoints') !== false
            )) {
                error_log("PARSER NS DEBUG: File=" . basename($filePath) . ", Parsed Namespace=[$namespace], Length=" . strlen($namespace));
                $nsDebugCount++;
            }
            
            if (!empty($namespace)) {
                if (!isset($namespaces[$namespace])) {
                    $namespaces[$namespace] = [];
                }
            }
        }
        
        // Parse class/interface/trait declarations
        if (($tokenType === T_CLASS || $tokenType === T_INTERFACE || $tokenType === T_TRAIT) && !$inClass) {
            // Check for abstract
            $isAbstract = false;
            $j = $i - 1;
            while ($j >= 0 && is_array($tokens[$j]) && 
                   ($tokens[$j][0] === T_WHITESPACE || $tokens[$j][0] === T_ABSTRACT)) {
                if ($tokens[$j][0] === T_ABSTRACT) {
                    $isAbstract = true;
                    break;
                }
                $j--;
            }
            
            $currentClassType = $tokenType === T_CLASS ? 'class' : 
                               ($tokenType === T_INTERFACE ? 'interface' : 'trait');
            
            // Get class name
            $i++;
            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;
            }
            
            if ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                $currentClass = $tokens[$i][1];
                $fullClassName = !empty($namespace) ? $namespace . '\\' . $currentClass : $currentClass;
                
                // Store class information - make sure we store the namespace correctly
                $storedNamespace = $namespace; // Capture namespace value at this point
                
                // Debug: Log namespace for specific classes to verify parsing
                static $debugCount = 0;
                if ($debugCount < 10 && (
                    strpos($currentClass, 'Exporter') !== false || 
                    strpos($currentClass, 'Collections') !== false ||
                    strpos($currentClass, 'REST_') !== false ||
                    strpos($currentClass, 'Item') !== false
                )) {
                    error_log("PARSER DEBUG: File=" . basename($filePath) . ", Class=$currentClass, Namespace=[$storedNamespace], FullName=$fullClassName, NamespaceFound=$namespaceFound");
                    $debugCount++;
                }
                
                // Store class information
                $classes[$fullClassName] = [
                    'name' => $currentClass,
                    'namespace' => $storedNamespace, // Store the captured namespace value
                    'type' => $currentClassType,
                    'abstract' => $isAbstract,
                    'file' => $filePath
                ];
                
                // Add to namespace
                if ($namespace) {
                    $namespaces[$namespace][] = $fullClassName;
                } else {
                    if (!isset($namespaces[''])) {
                        $namespaces[''] = [];
                    }
                    $namespaces[''][] = $fullClassName;
                }
                
                $inClass = true;
                $braceCount = 0;
            }
        }
        
        // Parse extends
        if ($tokenType === T_EXTENDS && $currentClass !== null) {
            $parent = '';
            $i++;
            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;
            }
            while ($i < count($tokens)) {
                if (is_array($tokens[$i])) {
                    if ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR) {
                        $parent .= $tokens[$i][1];
                    } elseif ($tokens[$i][0] === T_WHITESPACE) {
                        // Continue
                    } else {
                        break;
                    }
                } else {
                    if ($tokens[$i] === '{' || $tokens[$i] === 'implements') {
                        break;
                    }
                }
                $i++;
            }
            $parent = trim($parent);
            if (!empty($parent)) {
                // Resolve fully qualified name if it's a use statement
                $fullParentName = resolveClassName($parent, $namespace, $content);
                $fullClassName = $namespace ? $namespace . '\\' . $currentClass : $currentClass;
                $relationships[] = [
                    'type' => 'extends',
                    'from' => $fullClassName,
                    'to' => $fullParentName
                ];
            }
        }
        
        // Parse implements
        if ($tokenType === T_IMPLEMENTS && $currentClass !== null) {
            $i++;
            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                $i++;
            }
            
            $interface = '';
            while ($i < count($tokens)) {
                if (is_array($tokens[$i])) {
                    if ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR) {
                        $interface .= $tokens[$i][1];
                    } elseif ($tokens[$i][0] === T_WHITESPACE) {
                        // Continue
                    } else {
                        break;
                    }
                } else {
                    if ($tokens[$i] === '{' || $tokens[$i] === ',') {
                        if ($tokens[$i] === ',') {
                            // Multiple interfaces
                            $interface = trim($interface);
                            if (!empty($interface)) {
                                $fullInterfaceName = resolveClassName($interface, $namespace, $content);
                                $fullClassName = $namespace ? $namespace . '\\' . $currentClass : $currentClass;
                                $relationships[] = [
                                    'type' => 'implements',
                                    'from' => $fullClassName,
                                    'to' => $fullInterfaceName
                                ];
                            }
                            $interface = '';
                            $i++;
                            while ($i < count($tokens) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                                $i++;
                            }
                            continue;
                        } else {
                            break;
                        }
                    }
                }
                $i++;
            }
            $interface = trim($interface);
            if (!empty($interface)) {
                $fullInterfaceName = resolveClassName($interface, $namespace, $content);
                $fullClassName = $namespace ? $namespace . '\\' . $currentClass : $currentClass;
                $relationships[] = [
                    'type' => 'implements',
                    'from' => $fullClassName,
                    'to' => $fullInterfaceName
                ];
            }
        }
    }
}

/**
 * Resolve class name to fully qualified name
 * Handles use statements and relative namespaces
 */
function resolveClassName($className, $currentNamespace, $fileContent) {
    // If it starts with \, it's already fully qualified
    if (strpos($className, '\\') === 0) {
        return substr($className, 1);
    }
    
    // Check for use statements
    if (preg_match_all('/^use\s+([^;]+);/m', $fileContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $useStatement = trim($match[1]);
            // Handle "use Namespace\Class as Alias"
            if (preg_match('/^(.+?)\s+as\s+(.+)$/', $useStatement, $aliasMatch)) {
                if ($aliasMatch[2] === $className) {
                    return $aliasMatch[1];
                }
            } elseif (preg_match('/^(.+\\\\)?' . preg_quote($className, '/') . '$/', $useStatement)) {
                return $useStatement;
            }
        }
    }
    
    // If it's a simple name and we have a namespace, try current namespace
    if (strpos($className, '\\') === false && $currentNamespace) {
        return $currentNamespace . '\\' . $className;
    }
    
    // Return as-is (might be a built-in or global class)
    return $className;
}

// Scan the directory
scanDirectory($classesDir, $namespaces, $classes, $relationships);

// Output JSON
$output = [
    'namespaces' => $namespaces,
    'classes' => $classes,
    'relationships' => $relationships
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

