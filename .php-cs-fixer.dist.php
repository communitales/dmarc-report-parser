<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP85Migration' => true,
        'no_unused_imports' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'ordered_interfaces' => true,
        'protected_to_private' => true,
        'single_quote' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'location' => 'after_declare_strict',
            'header' => "@copyright Copyright (c) 2025 - 2026 Communitales GmbH (https://www.communitales.com/)\n\nFor the full copyright and license information, please view the LICENSE\nfile that was distributed with this source code.",
        ],
    ])
    ->setFinder($finder)
    ->setParallelConfig(ParallelConfigFactory::detect());
