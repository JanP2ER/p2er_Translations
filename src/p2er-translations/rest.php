<?php

use P2er\Translation\Database\TranslationRow;
use P2er\Translation\Database\GroupRow;
require_once(__DIR__.'/P2er/Translation/Database/TranslationRow.php');
require_once(__DIR__.'/P2er/Translation/Database/GroupRow.php');

/**
 * Translations
 */
add_action('rest_api_init', 'register_p2er_translations');
function register_p2er_translations() {
    register_rest_route('p2er/v2', 'translations', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'p2er_translation_rest',
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        )
    );
}

/**
 * Select rows from translation database
 * @return array
 */
function p2er_get_translation_rows() {
    $dep = \P2er\Dependency\DependencyProvider::getInstance();
    $translationDatabase = $dep->getTranslationTable();
    $translationDatabase->sqlConnection->setup();
    $requestsDatabaseRows = array_reverse($translationDatabase->getAll());
    $translationDatabase->sqlConnection->close();
    return $requestsDatabaseRows;
}

/**
 * Select rows from database
 * @return array
 */
function p2er_get_group_rows() {
    $dep = \P2er\Dependency\DependencyProvider::getInstance();
    $groupDatabase = $dep->getGroupTable();
    $groupDatabase->sqlConnection->setup();
    $requestsDatabaseRows = array_reverse($groupDatabase->getAll());
    $groupDatabase->sqlConnection->close();
    return $requestsDatabaseRows;
}

/**
 * Rest API callback
 * @param $request_data
 * @return array
 */
function p2er_translation_rest($request_data) {
    return p2er_get_translation_rows();
}

/**
 * Rest API callback
 * @param $request_data
 * @return array
 */
function p2er_group_rest($request_data) {
    return p2er_get_group_rows();
}

/**
 * Admin ajax hook for updating text entries.
 *
 * @return array Warnings and error messages.
 */
function p2er_translation_admin_ajax(): array
{
    $reqData = json_decode(stripslashes($_REQUEST['text_data']));
    $changes = (array)$reqData;

    // {language:'de', rows:[{id:'abc', tex:'jan rochs', translations:'jan Rocks'}]}
    // ["language"=>"de", "rows"=>[["id"=>"abc", "text"=>"jan rochs", "translation"=>"jan Rocks"]]]

    // Send language, text to change, etc


    $dep = \P2er\Dependency\DependencyProvider::getInstance();
    $translationDatabaseConnector = $dep->getTranslationTable();
    $language = $changes['language'] ?? 'DE';
    $rowsToChange = $changes['rows'] ?? [];
    try {
        foreach ($rowsToChange as $index => $rowData) {
            $rowData = (array)$rowData;
            $id = $rowData['id'];
            $rows = $translationDatabaseConnector->getById($id);

            if (count($rows) > 0) {
                $row = $rows[0];
                $row->translation = $rowData['translation'] ?? '';
                $row->fallback = $rowData['fallback'] ?? '';
                $row->parent = $rowData['parent'] ?? '';
                $row->index = $rowData['index'] ?? "0";
            }else {
                $row = new TranslationRow($rowData);
            }
            $row->language = $language;
            $response = $translationDatabaseConnector->insert($row, true);
            echo(json_encode(['response'=> $response . 'test', 'row'=> $row, 'data'=> $rowData]));
            wp_die();
        }
    } catch (Error $error) {
        echo(json_encode(['error'=> $error->getMessage()]));
        wp_die();
    }

    $translationRows = p2er_get_translation_rows();
    echo json_encode($translationRows);
    wp_die();
}

add_action('wp_ajax_p2er_translation_admin_ajax', 'p2er_translation_admin_ajax');



/**
 * Admin ajax hook for updating Group entries.
 *
 * @return array Warnings and error messages.
 */
function p2er_group_admin_ajax(): array
{
    $reqData = json_decode(stripslashes($_REQUEST['text_data']));
    $changes = (array)$reqData;

    // {language:'de', rows:[{id:'abc', tex:'jan rochs', translations:'jan Rocks'}]}
    // ["language"=>"de", "rows"=>[["id"=>"abc", "text"=>"jan rochs", "translation"=>"jan Rocks"]]]



    $dep = \P2er\Dependency\DependencyProvider::getInstance();
    $groupDatabaseConnector = $dep->getGroupTable();
    $rowsToChange = $changes['rows'] ?? [];
    try {
        foreach ($rowsToChange as $index => $rowData) {
            $rowData = (array)$rowData;
            $id = $rowData['id'];
            $rows = $groupDatabaseConnector->getById($id);

            if (count($rows) > 0) {
                $row = $rows[0];
                $row->label = $rowData['label'] ?? '';
                $row->parent = $rowData['parent'] ?? '';
                $row->index = $rowData['index'] ?? "0";
            }else {
                $row = new GroupRow($rowData);
            }
            $groupDatabaseConnector->insert($row, true);
        }
    } catch (Error $error) {
        echo(json_encode(['error'=> $error->getMessage()]));
        wp_die();
    }

    $groupRows = p2er_get_group_rows();
    echo json_encode($groupRows);
    wp_die();
}

add_action('wp_ajax_p2er_group_admin_ajax', 'p2er_group_admin_ajax');