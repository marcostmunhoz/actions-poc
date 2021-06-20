<?php

// definimos as pastas que serão excluídas da verificação
$finder = PhpCsFixer\Finder::create()
    ->exclude('node_modules')
    ->exclude('bootstrap/cache')
    ->exclude('public')
    ->exclude('resources')
    ->exclude('storage')
    ->exclude('vendor')
    ->exclude('_ide_helper.php')
    ->exclude('_ide_helper_models.php')
    ->exclude('.phpstorm.meta.php')
    ->in(__DIR__);

// definimos a configuração
return PhpCsFixer\Config::create()
    ->setFinder($finder) // configuramos o finder
    ->setRules([ // definimos as regras usadas
        '@Symfony:risky' => true,
        '@Symfony' => true,
        'no_superfluous_phpdoc_tags' => false, // evita que tags @param e @return sejam removidas de blocos PHPDoc,
        'single_line_comment_style' => false, // permite comentários # (region)
        'php_unit_method_casing' => false,
        'ordered_class_elements' => true,
    ])
    ->setLineEnding("\n")
    ->setRiskyAllowed(true);
