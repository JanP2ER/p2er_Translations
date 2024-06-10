<?php

use P2er\Translation\Database\DeepLRow;


//add_action('wp_head', 'p2er_translation_react_script');

function p2er_translation_react_script()
{
    $path          = __DIR__ . '/app/build/asset-manifest.json';
    $configJSON    = file_get_contents($path);
    $assetManifest = json_decode($configJSON);
    $entryPoints = ((array)$assetManifest)['entrypoints'];
    return '<script defer="defer" src="' . plugin_dir_url( __FILE__ ) . '/app/build/' . $entryPoints[1] . '"></script>';
}

add_action('admin_menu', 'p2er_translations_page_register');

function p2er_translations_page_register()
{
    add_menu_page(
        'P2ER Translations',     // page title
        'P2ER Translations',     // menu title
        'publish_posts',         // capability
        'p2er-translations',     // menu slug
        'p2er_translations_page_render' // callback function
    );
}

function p2er_translations_page_render()
{
    global $title;

    $dep = \P2er\Dependency\DependencyProvider::getInstance();

    // Make sure all tables are set up
    $dep->getSqlConnection()->setup();

    // Get Database Table Reflection instances
    $translationDatabase = $dep->getTranslationTable();
    $groupDatabase = $dep->getGroupTable();

    // Collect existing data
    $translationDatabaseRows = array_reverse($translationDatabase->getAll());
    $groupDatabaseRows = $groupDatabase->getAll();

    // Close connection to avoid excessive mysql connection
    $dep->getSqlConnection()->close();

    print '<div>';
    print "<h1>$title</h1>";
    print '</div>';
    print '<div id="p2er-translation-app"></div>';

    $nonce = wp_create_nonce( 'wp_rest' );
    $adminUrl = admin_url( 'admin-ajax.php' );
    print '
        <script>
            window.p2erWpNonce = "'.$nonce.'";
            window.p2erAdminAjaxUrl = "'.$adminUrl.'";
            window.p2erAdminAjaxTranslationAction = "p2er_translation_admin_ajax";
            window.p2erAdminAjaxGroupAction = "p2er_group_admin_ajax";
            window.p2erTranslationRows = '.json_encode($translationDatabaseRows, JSON_PRETTY_PRINT).'
            window.p2erGroupRows = '.json_encode($groupDatabaseRows, JSON_PRETTY_PRINT).'
        </script>';

    print p2er_translation_react_script();
}