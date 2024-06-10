<?php

namespace Cu\Translation\Service;

use P2er\Service\Service;
use P2er\Translation\Database\DeepLDatabase;
use P2er\Translation\Database\DeepLRow;

require_once(__DIR__ . "/../../Service/Service.php");
require_once(__DIR__ . "/../Database/DeepLRow.php");

class DeepLService extends Service
{
    /**
     * @var DeepLDatabase
     */
    public DeepLDatabase $deepLDatabase;

    /**
     * TODO: potential to remove and create interface for WP language set in header by installation
     * specify languages to be used in case of request
     * @var string[]
     */
    public array $languageMap = ['en' => 'EN-GB', 'de' => 'DE', 'de_DE'=> 'DE', 'es' => 'ES', 'fr' => 'FR', 'nl' => 'NL'];

    /**
     * List of allowed formal values
     * @var string[]
     */
    public array $formal = ['default', 'more', 'less', 'prefer_more', 'prefer_less'];

    /**
     * Public interface to load translation
     * @param string $text
     * @param string $lang
     * @param string $formal
     * @return string
     */
    public function translate(string $text = '', string $lang = 'EN-GB', string $formal = 'default'): string
    {
        $translation = $this->execute('executeLoadTranslation', ['text' => $text, 'lang' => $lang, 'formal' => $formal]);
        return $translation['text'] ?? '';
    }

    /**
     * Can be run by combine requests (Service)
     * Allow json response
     *
     * @param array $params
     * @return string[]|null
     */
    protected function executeLoadTranslation(array $params = ['text' => '', 'lang' => 'en', 'formal' => 'default']): ?array
    {
        $text = $params['text'] ?? '';
        $lang = $params['lang'] ?? 'en';
        $formal = $params['formal'] ?? 'default';
        return $this->loadTranslation($text, $lang, $formal);
    }

    /**
     * See: https://www.deepl.com/docs-api/translate-text
     * @param string $text
     * @param string $lang
     * The language into which the text should be translated. Options currently available:
     * BG - Bulgarian
     * CS - Czech
     * DA - Danish
     * DE - German
     * EL - Greek
     * EN - English (unspecified variant for backward compatibility; please select EN-GB or EN-US instead)
     * EN-GB - English (British)
     * EN-US - English (American)
     * ES - Spanish
     * ET - Estonian
     * FI - Finnish
     * FR - French
     * HU - Hungarian
     * ID - Indonesian
     * IT - Italian
     * JA - Japanese
     * KO - Korean
     * LT - Lithuanian
     * LV - Latvian
     * NB - Norwegian (Bokmål)
     * NL - Dutch
     * PL - Polish
     * PT - Portuguese (unspecified variant for backward compatibility; please select PT-BR or PT-PT instead)
     * PT-BR - Portuguese (Brazilian)
     * PT-PT - Portuguese (all Portuguese varieties excluding Brazilian Portuguese)
     * RO - Romanian
     * RU - Russian
     * SK - Slovak
     * SL - Slovenian
     * SV - Swedish
     * TR - Turkish
     * UK - Ukrainian
     * ZH - Chinese (simplified)
     * @param string $formal
     * default (default)
     * more - for a more formal language
     * less - for a more informal language
     * prefer_more - for a more formal language if available, otherwise fallback to default formality
     * prefer_less - for a more informal language if available, otherwise fallback to default formality
     * @return array
     */
    protected function loadTranslation(string $text = '', string $lang = 'EN-GB', string $formal = 'default'): array
    {
        if ($text === '') {
            return ['text' => $text, 'message' => 'empty text'];
        }
        $lang = $this->languageMap[$lang] ?? $lang;

        // Fallback to allowed formal value
        if (!in_array($formal, $this->formal)) {
            $formal = 'default';
        }

        // Avoid running out of quota by doing multiple requests
        $dbId = md5($lang . $formal . $text);
        $row = $this->deepLDatabase->getByText($text, $lang);
        if ($row !== null) {
            return ['text' => $row->translation, 'message' => 'from database'];
        }

        $query = [
            //'auth_key' => '746a752d-4cd0-6404-f329-7d1cce338c2c',
            'auth_key' => '62819743-0288-932e-edcb-d9006e61b137:fx',
            //'auth_key' => '4cb4ca82-63fb-1ab5-529b-ec99d0ba8edb:fx',
            //'auth_key' => '1234abc-1234-abcd-ef56-123456abcdef:fx',

        ];

        // Target language
        if ($lang) {
            $query['target_lang'] = $lang;
        }

        // Text for translation
        if ($text) {
            $query['text'] = $text;
        }

        // Select whether formal or informal language should be used. Valid options are "more" for formal language, "less" for informal language or "default"
        if ($formal) {
            $query['formal'] = $formal;
        }

        // Fetch
        $requestUrl = 'https://api-free.deepl.com/v2/translate'; // 'https://api.deepl.com/v2/translate'
        $requestUrl .= '?' . http_build_query($query);
        $curl = curl_init($requestUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        curl_close($curl);

        // data e.g. {"translations":[{"detected_source_language":"DE","text":"What do side effects mean?"}]}
        $decoded = json_decode($data);
        $translations = ((array)($decoded ?? []))['translations'] ?? [];
        $message = ((array)($decoded ?? []))['message'] ?? '';
        $translation = (array)($translations[0] ?? []);

        // Update cache
        $translatedText = $translation['text'] ?? '';
        $this->addToDB($dbId, $translatedText, $text, $lang, $formal);

        // Result will be empty if quote is exceeded
        return ['text' => $translatedText, 'message' => 'from api'];
    }

    /**
     * @param string $dbId
     * @param string $translatedText
     * @param string $text
     * @param string $lang
     * @param string $formal
     * @return bool
     */
    private function addToDB(string $dbId, string $translatedText, string $text, string $lang, string $formal): bool
    {
        if ($translatedText === '') {
            return false;
        }
        $row = new DeepLRow();
        $row->id = $dbId;
        $row->text = $text;
        $row->translation = $translatedText;
        $row->language = $lang;
        $row->formal = $formal;
        return $this->deepLDatabase->insert($row, true);
    }

}
