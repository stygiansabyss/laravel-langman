<?php

namespace Themsaid\Langman\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Themsaid\Langman\Manager;

abstract class BaseCommand extends Command
{
    /**
     * The Languages manager instance.
     *
     * @var \Themsaid\LangMan\Manager
     */
    protected $manager;

    /**
     * ListCommand constructor.
     *
     * @param \Themsaid\LangMan\Manager $manager
     *
     * @return void
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Generate strings for language values that could be arrays or strings.
     *
     * @param $output
     * @param $key
     * @param $language
     * @param $value
     *
     * @return array
     */
    protected function getLanguageValues($output, $key, $language, $value)
    {
        if (is_array($value)) {
            foreach ($value as $valueKey => $singleValue) {
                $newKey = $key . '.' . $valueKey;
                if (is_array($singleValue)) {
                    $output = $this->getLanguageValues($output, $newKey, $language, $singleValue);
                } else {
                    $output[$newKey]['key']     = $newKey;
                    $output[$newKey][$language] = $singleValue;
                }
            }

            return $output;
        }

        $output[$key]['key']     = $key;
        $output[$key][$language] = $value;

        return $output;
    }

    /**
     * Search for the string in language values that could be an array or a string.
     *
     * @param $output
     * @param $key
     * @param $fileName
     * @param $languageKey
     * @param $line
     *
     * @return array
     */
    protected function searchLanguageFiles($output, $key, $fileName, $languageKey, $line)
    {
        if (is_array($line)) {
            foreach ($line as $lineKey => $singleLine) {
                $newKey = $key . '.' . $lineKey;
                $output = $this->searchLanguageFiles($output, $newKey, $fileName, $languageKey, $singleLine);
            }

            return $output;
        }

        if (Str::contains($line, $this->argument('keyword'))) {
            $output[$key][$languageKey] = "<bg=yellow;fg=black>{$line}</>";
        }

        return $output;
    }

    /**
     * Get a file name and a key from a full key value.
     *
     * @param $fullKey
     *
     * @return array
     */
    protected function getFileAndKey($fullKey)
    {
        $keyParts = explode('.', $fullKey);
        $fileName = array_pop($keyParts);
        $key      = implode('.', $keyParts);

        return [$fileName, $key];
    }

    /**
     * Get a file name, language and a key from a full key value.
     *
     * @param $fullKey
     *
     * @return array
     */
    protected function getFileLanguageAndKey($fullKey)
    {
        $allLanguages = $this->manager->languages();
        $keyParts     = explode('.', $fullKey);

        $languageName  = head(array_intersect($keyParts, $allLanguages));
        $languageIndex = array_search($languageName, $keyParts);
        $languageKey   = $languageIndex ? array_pull($keyParts, $languageIndex) : null;


        $fileName = array_shift($keyParts);
        $key      = implode('.', $keyParts);

        return [$fileName, $languageKey, $key];
    }
}
