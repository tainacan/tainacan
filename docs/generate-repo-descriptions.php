<?php 
/**
 * File used to generate docs for the repositories classes 
 *
 * change the line below to the path to the wp-config of a WP installation 
 *
 * Dont forget to set the language of your WordPress to english
 */
//include('/path/to/wp-config.php');

$repos = [
    
    'collections' => [
        'instance' => \Tainacan\Repositories\Collections::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    'metadata' => [
        'instance' => \Tainacan\Repositories\Metadata::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'fetch_ids',
            ],
            [
                'name' => 'fetch_by_collection',
            ],
            [
                'name' => 'fetch_ids_by_collection',
            ],
            [
                'name' => 'fetch_metadata_types',
            ],
            [
                'name' => 'get_core_metadata',
            ],
            [
                'name' => 'get_core_title_metadatum',
            ],
            [
                'name' => 'get_core_description_metadatum',
            ],
            [
                'name' => 'fetch_all_metadatum_values',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    'filters' => [
        'instance' => \Tainacan\Repositories\Filters::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'fetch_ids',
            ],
            [
                'name' => 'fetch_by_collection',
            ],
            [
                'name' => 'fetch_ids_by_collection',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    'items' => [
        'instance' => \Tainacan\Repositories\Items::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'fetch_ids',
            ],
            [
                'name' => 'get_thumbnail_id_from_document',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    'taxonomies' => [
        'instance' => \Tainacan\Repositories\Taxonomies::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'fetch_by_collection',
            ],
            [
                'name' => 'term_exists',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    'terms' => [
        'instance' => \Tainacan\Repositories\Terms::get_instance(),
        'methods' => [
            [
                'name' => 'fetch',
            ],
            [
                'name' => 'fetch_one',
            ],
            [
                'name' => 'insert',
            ],
            [
                'name' => 'update',
            ],
            [
                'name' => 'delete',
            ],
            [
                'name' => 'trash',
            ]
        ]
    ],
    // 'item-metadata' => [
    //     'instance' => \Tainacan\Repositories\Item_Metadata::get_instance(),
    //     'methods' => [
    //         [
    //             'name' => 'fetch',
    //         ],
    //         [
    //             'name' => 'get_value',
    //         ],
    //         [
    //             'name' => 'insert',
    //         ],
    //         [
    //             'name' => 'delete',
    //         ],
    //         [
    //             'name' => 'trash',
    //         ]
    //     ]
    // ],
    
    
];

function get_method_doc($repo, $method) {
    
    //return '';
    
    $r = new ReflectionMethod($repo, $method);
    $doc = $r->getDocComment();
    $doc = str_replace('/**', '', $doc);
    $doc = str_replace('*/', '', $doc);
    $doc = str_replace(' * ', '', $doc);
    $doc = str_replace(' *', '', $doc);
    $doc = preg_replace('/\t+/', '', $doc );
    return $doc;
}

foreach ($repos as $name => $repo) {
    $i = $repo['instance'];
    $map = $i->get_map();
    $entity = $i->entities_type;
    $entity = strtolower( str_replace('\Tainacan\Entities\\', '', $entity) );
    $target_file = 'repository-' . $name . '.md';
    ob_start();
?>

# <?= $i->get_name(); ?> Repository

## Main Methods

These are the most used methods of this repository. For a complete list see [the repository file](../src/classes/repositories/class-tainacan-<?= $name; ?>.php).

<?php foreach ($repo['methods'] as $method): ?>

### <?= $method['name']; ?>()

<?= get_method_doc(get_class($i), $method['name']); ?>

<?php endforeach; ?>

## Usage 

```PHP
$repository = \Tainacan\Repositories\<?= $i->get_name(); ?>::get_instance();
```

## Entity Properties 

These are the Entity attributes for this repository. The Entity class is at [classes/entities folder](../src/classes/entities/class-tainacan-<?= $entity; ?>.php)

Property | Description | Slug | Getter | Setter | Stored as
--- | --- | --- | --- | --- | --- 
<?php foreach ($map as $s => $m): ?>
<?= $m['title']; ?>|<?= $m['description']; ?>|<?= $s; ?>|`$entity->get_<?= $s; ?>()`|`$entity->set_<?= $s; ?>()`|<?= $m['map'] . "\n"; ?>
<?php endforeach; ?>

### Entity usage


Create new

```PHP
$entity = new <?= $i->entities_type; ?>();
```

Get existing by ID
```PHP
$repository = \Tainacan\Repositories\<?= $i->get_name(); ?>::get_instance();
$entity = $repository->fetch(12);
echo 'My ID is ' . $entity->get_id(); // 12
```

<?php
file_put_contents($target_file, ob_get_clean());
}
