<?php
/**
 * Split large Mermaid class diagram into multiple smaller diagrams
 * 
 * Creates:
 * 1. Main overview diagram (classes + relationships only, no attributes/methods)
 * 2. Detailed diagrams grouped by namespace/folder
 * 
 * Usage: php split-mermaid-diagram.php input.mmd output_directory
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php split-mermaid-diagram.php <input.mmd> <output_directory>\n");
    exit(1);
}

$inputFile = $argv[1];
$outputDir = $argv[2];

if (!file_exists($inputFile)) {
    fwrite(STDERR, "Error: File not found: $inputFile\n");
    exit(1);
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$content = file_get_contents($inputFile);
if ($content === false) {
    fwrite(STDERR, "Error: Could not read file: $inputFile\n");
    exit(1);
}

$lines = explode("\n", $content);
$classes = [];
$relationships = [];
$currentClass = null;
$inClass = false;
$classAttributes = [];
$classMethods = [];

// Parse the Mermaid diagram
foreach ($lines as $line) {
    $line = trim($line);
    
    if (empty($line) || $line === 'classDiagram') {
        continue;
    }
    
    // Detect class start
    if (preg_match('/^\s*class\s+([a-zA-Z0-9_]+)\s*\{/', $line, $matches)) {
        $currentClass = $matches[1];
        $inClass = true;
        $classes[$currentClass] = [
            'name' => $currentClass,
            'type' => 'class',
            'attributes' => [],
            'methods' => []
        ];
        continue;
    }
    
    // Detect class end
    if ($line === '}' && $inClass) {
        $inClass = false;
        $currentClass = null;
        continue;
    }
    
    // Parse class content
    if ($inClass && $currentClass) {
        // Type annotations
        if (preg_match('/<<(interface|enumeration|trait)>>/', $line, $matches)) {
            $classes[$currentClass]['type'] = $matches[1];
            continue;
        }
        
        // Attributes (format: +name : type or -name : type)
        if (preg_match('/^([+#-~])([^:]+)\s*:\s*(.+)$/', $line, $matches)) {
            $classes[$currentClass]['attributes'][] = [
                'visibility' => $matches[1],
                'name' => trim($matches[2]),
                'type' => trim($matches[3])
            ];
            continue;
        }
        
        // Methods (format: +method() or -method())
        if (preg_match('/^([+#-~])(.+)$/', $line, $matches)) {
            $classes[$currentClass]['methods'][] = [
                'visibility' => $matches[1],
                'name' => trim($matches[2])
            ];
            continue;
        }
    }
    
    // Parse relationships
    if (preg_match('/^([a-zA-Z0-9_]+)\s+<\|--\s+([a-zA-Z0-9_]+)/', $line, $matches)) {
        $relationships[] = [
            'type' => 'extends',
            'from' => $matches[2],
            'to' => $matches[1]
        ];
    } elseif (preg_match('/^([a-zA-Z0-9_]+)\s+<\|\.\.\s+([a-zA-Z0-9_]+)/', $line, $matches)) {
        $relationships[] = [
            'type' => 'implements',
            'from' => $matches[2],
            'to' => $matches[1]
        ];
    }
}

// Group classes by namespace/folder (extract from class name patterns)
$groups = [];
foreach ($classes as $className => $class) {
    // Determine group based on class name patterns
    // Handle both direct names and prefixed names (e.g., "roles_Roles_Editor", "dashboard_Dashboard")
    $group = 'core'; // default
    
    // Extract base name (remove prefix like "roles_", "dashboard_", etc.)
    $baseName = $className;
    if (preg_match('/^[a-z]+_(.+)$/', $className, $matches)) {
        $baseName = $matches[1];
    }
    
    // Group by patterns (check both full name and base name)
    if (preg_match('/\b(Entities|Entity|Collection|Item|Taxonomy|Term|Filter|Metadatum|Metadata_Section|Log)\b/', $className) ||
        preg_match('/\b(Entities|Entity|Collection|Item|Taxonomy|Term|Filter|Metadatum|Metadata_Section|Log)\b/', $baseName)) {
        $group = 'entities';
    } elseif (preg_match('/\b(Repositories|Repository)\b/', $className) ||
              preg_match('/\b(Repositories|Repository)\b/', $baseName)) {
        $group = 'repositories';
    } elseif (preg_match('/\b(API|REST_|EndPoints|REST)\b/', $className) ||
              preg_match('/\b(API|REST_|EndPoints|REST)\b/', $baseName)) {
        $group = 'api';
    } elseif (preg_match('/\b(Metadata_Types|MetadataType|Metadata_Type)\b/', $className) ||
              preg_match('/\b(Metadata_Types|MetadataType|Metadata_Type)\b/', $baseName)) {
        $group = 'metadata-types';
    } elseif (preg_match('/\b(Filter_Types|FilterType|Filter_Type)\b/', $className) ||
              preg_match('/\b(Filter_Types|FilterType|Filter_Type)\b/', $baseName)) {
        $group = 'filter-types';
    } elseif (preg_match('/\b(Exposers|Exposer)\b/', $className) ||
              preg_match('/\b(Exposers|Exposer)\b/', $baseName)) {
        $group = 'exposers';
    } elseif (preg_match('/\b(Mappers|Mapper)\b/', $className) ||
              preg_match('/\b(Mappers|Mapper)\b/', $baseName)) {
        $group = 'mappers';
    } elseif (preg_match('/\b(Importer|Exporter|Background|Background_Process|Background_Generic_Process)\b/', $className) ||
              preg_match('/\b(Importer|Exporter|Background|Background_Process|Background_Generic_Process)\b/', $baseName)) {
        $group = 'background-process';
    } elseif (preg_match('/\b(Admin|Dashboard|Settings|Roles|System_Check|Mobile_App|Pages|roles_|dashboard_|settings_|system_check_|mobile_app_)\b/', $className) ||
              preg_match('/\b(Admin|Dashboard|Settings|Roles|System_Check|Mobile_App|Pages)\b/', $baseName)) {
        $group = 'admin-ui';
    } elseif (preg_match('/\b(Cli|CLI|cli_)\b/', $className) ||
              preg_match('/\b(Cli|CLI)\b/', $baseName)) {
        $group = 'cli';
    } elseif (preg_match('/\b(OAIPMH|OAI|OAIPMH_)\b/', $className) ||
              preg_match('/\b(OAIPMH|OAI)\b/', $baseName)) {
        $group = 'oaipmh';
    } elseif (preg_match('/\b(Traits|Trait|trait_)\b/', $className) ||
              preg_match('/\b(Traits|Trait)\b/', $baseName)) {
        $group = 'traits';
    } elseif (preg_match('/\b(Embed|Media|Private_Files|Search_Engine|Elastic_Press|Roles|Bulk_Edit)\b/', $className) ||
              preg_match('/\b(Embed|Media|Private_Files|Search_Engine|Elastic_Press|Roles|Bulk_Edit)\b/', $baseName)) {
        $group = 'core';
    }
    
    if (!isset($groups[$group])) {
        $groups[$group] = [];
    }
    $groups[$group][$className] = $class;
}

// 1. Generate main overview diagram (classes + relationships only, no details)
$overviewFile = $outputDir . '/tainacan-classes-overview.mmd';
$overview = fopen($overviewFile, 'w');
fwrite($overview, "classDiagram\n");

// Output all classes without attributes/methods
foreach ($classes as $className => $class) {
    fwrite($overview, "    class {$className} {\n");
    if ($class['type'] === 'interface') {
        fwrite($overview, "        <<interface>>\n");
    } elseif ($class['type'] === 'trait') {
        fwrite($overview, "        <<trait>>\n");
    } elseif ($class['type'] === 'enumeration') {
        fwrite($overview, "        <<enumeration>>\n");
    }
    fwrite($overview, "    }\n");
}

// Output all relationships
foreach ($relationships as $rel) {
    if ($rel['type'] === 'extends') {
        fwrite($overview, "    {$rel['to']} <|-- {$rel['from']}\n");
    } elseif ($rel['type'] === 'implements') {
        fwrite($overview, "    {$rel['to']} <|.. {$rel['from']} : implements\n");
    }
}
fclose($overview);

// 2. Generate detailed diagrams for each group
foreach ($groups as $groupName => $groupClasses) {
    if (empty($groupClasses)) {
        continue;
    }
    
    $groupFile = $outputDir . '/tainacan-classes-' . $groupName . '.mmd';
    $file = fopen($groupFile, 'w');
    fwrite($file, "classDiagram\n");
    
    // Output classes with full details
    foreach ($groupClasses as $className => $class) {
        fwrite($file, "    class {$className} {\n");
        
        if ($class['type'] === 'interface') {
            fwrite($file, "        <<interface>>\n");
        } elseif ($class['type'] === 'trait') {
            fwrite($file, "        <<trait>>\n");
        } elseif ($class['type'] === 'enumeration') {
            fwrite($file, "        <<enumeration>>\n");
        }
        
        // Output attributes
        foreach ($class['attributes'] as $attr) {
            fwrite($file, "        {$attr['visibility']}{$attr['name']} : {$attr['type']}\n");
        }
        
        // Output methods
        foreach ($class['methods'] as $method) {
            fwrite($file, "        {$method['visibility']}{$method['name']}\n");
        }
        
        fwrite($file, "    }\n");
    }
    
    // Track external classes we've already added to avoid duplicates
    $externalClassesAdded = [];
    
    // Output relationships - include all where at least one class is in this group
    // This shows how the group connects to other parts of the system
    foreach ($relationships as $rel) {
        $fromInGroup = isset($groupClasses[$rel['from']]);
        $toInGroup = isset($groupClasses[$rel['to']]);
        
        // Include if at least one class is in this group
        if ($fromInGroup || $toInGroup) {
            // If the other class is not in this group, we need to add it as a reference
            if ($fromInGroup && !$toInGroup && isset($classes[$rel['to']]) && !isset($externalClassesAdded[$rel['to']])) {
                // Add the external class as a reference (without details)
                fwrite($file, "    class {$rel['to']} {\n");
                if ($classes[$rel['to']]['type'] === 'interface') {
                    fwrite($file, "        <<interface>>\n");
                } elseif ($classes[$rel['to']]['type'] === 'trait') {
                    fwrite($file, "        <<trait>>\n");
                } elseif ($classes[$rel['to']]['type'] === 'enumeration') {
                    fwrite($file, "        <<enumeration>>\n");
                }
                fwrite($file, "    }\n");
                $externalClassesAdded[$rel['to']] = true;
            } elseif ($toInGroup && !$fromInGroup && isset($classes[$rel['from']]) && !isset($externalClassesAdded[$rel['from']])) {
                // Add the external class as a reference (without details)
                fwrite($file, "    class {$rel['from']} {\n");
                if ($classes[$rel['from']]['type'] === 'interface') {
                    fwrite($file, "        <<interface>>\n");
                } elseif ($classes[$rel['from']]['type'] === 'trait') {
                    fwrite($file, "        <<trait>>\n");
                } elseif ($classes[$rel['from']]['type'] === 'enumeration') {
                    fwrite($file, "        <<enumeration>>\n");
                }
                fwrite($file, "    }\n");
                $externalClassesAdded[$rel['from']] = true;
            }
            
            // Output the relationship
            if ($rel['type'] === 'extends') {
                fwrite($file, "    {$rel['to']} <|-- {$rel['from']}\n");
            } elseif ($rel['type'] === 'implements') {
                fwrite($file, "    {$rel['to']} <|.. {$rel['from']} : implements\n");
            }
        }
    }
    
    fclose($file);
}

echo "Generated overview diagram: tainacan-classes-overview.mmd\n";
echo "Generated detailed diagrams:\n";
foreach (array_keys($groups) as $groupName) {
    echo "  - tainacan-classes-{$groupName}.mmd\n";
}

