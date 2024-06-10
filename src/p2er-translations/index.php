<?php
/**
 * Plugin Name:       p2er-translations
 * Description:       Provide Translations Capabilities for Code Use
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Jan Struck, P2ER GmbH
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       p2er-translations
 *
 * @package           p2er-translations
 */


require_once __DIR__ . '/P2er/Dependency/DependencyProvider.php';
require_once __DIR__ . '/P2er/Translation/Database/TranslationRow.php';
require_once __DIR__ . '/admin-page.php';
require_once __DIR__ . '/rest.php';

if (!function_exists('p2er_translate')) {
    function p2er_translate(string $text='', string  $language=''): string {
        $dep = \P2er\Dependency\DependencyProvider::getInstance();
        $deepLService = $dep->getDeepLService();
        if (!$language) {
            $language = get_locale();
        }
        $translation = $deepLService->translate($text, $language, 'default');
        if ($translation !== '') {
            return $translation . $language;
        }
        return $text . $language;
    }
}
if (!function_exists('_p2er_translation')) {
    function _p2er_translation(string $id='', string  $language='', $fallback=''): string {
        $dep = \P2er\Dependency\DependencyProvider::getInstance();
        $databaseConnector = $dep->getTranslationTable();
        if (!$language) {
            $language = get_locale();
        }

        $text = $databaseConnector->getByPrimaryKeys($id,$language)->translation;
        if(!$text){
            $text = $fallback;
            $deepLService = $dep->getDeepLService();
            $translation = $deepLService->translate($text, $language, 'default');
            if($translation !== '') {
                $text = $translation;
                $newRow = new \P2er\Translation\Database\TranslationRow();
                $newRow -> translation = $translation;
                $newRow -> fallback = $translation;
                $newRow -> parent = 'AUTO GENERATED FIELD';
                $newRow -> id = $id;
                $newRow -> index = '';
                $databaseConnector->insert($newRow, true);
                return $text;
            }

        }
        return $text;
    }
}
