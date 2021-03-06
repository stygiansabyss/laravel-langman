<?php

namespace Themsaid\Langman\Commands;

use Illuminate\Support\Str;

class ShowCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'langman:show {key} {--c|close}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $description = 'Show language lines for a given file or key.';

    /**
     * Filename to read from.
     *
     * @var string
     */
    protected $file;

    /**
     * Key name to show results for.
     *
     * @var string
     */
    protected $key;

    /**
     * Array of requested file in different languages.
     *
     * @var array
     */
    protected $files;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->parseKey();

        $this->files = $this->filesFromKey();

        $this->table(
            array_merge(['key'], $this->manager->languages()),
            $this->tableRows()
        );
    }

    /**
     * The output of the table rows.
     *
     * @return array
     */
    private function tableRows()
    {
        $allLanguages = $this->manager->languages();

        $output = [];

        foreach ($this->files as $language => $file) {
            foreach ($filesContent[$language] = $this->manager->getFileContent($file) as $key => $value) {
                if (! $this->shouldShowKey($key)) {
                    continue;
                }

                $output = $this->getLanguageValues($output, $key, $language, $value);
            }
        }

        // Now that we collected all existing values, we are going to
        // fill the missing ones with emptiness indicators to
        // balance the table structure & alert developers.
        foreach ($output as $key => $values) {
            $original = [];

            foreach ($allLanguages as $languageKey) {
                $original[$languageKey] = isset($values[$languageKey]) ? $values[$languageKey] : '<bg=red>  MISSING  </>';
            }

            // Sort the language values based on language name
            ksort($original);

            $output[$key] = array_merge(['key' => "<fg=yellow>$key</>"], $original);
        }

        return array_values($output);
    }

    /**
     * Array of requested file in different languages.
     *
     * @return array
     */
    private function filesFromKey()
    {
        try {
            return $this->manager->files()[$this->file];
        } catch (\ErrorException $e) {
            $this->error(sprintf('Language file %s.php not found!', $this->file));

            return [];
        }
    }

    /**
     * Parse the given key argument.
     *
     * @return void
     */
    private function parseKey()
    {
        try {
            list($this->file, $this->key) = explode('.', $this->argument('key'));
        } catch (\ErrorException $e) {
            $this->file = $this->argument('key');
            // If explosion resulted 1 array item then it's the file, we
            // leave the key as null.
            $this->file = $this->argument('key');
        }
    }

    /**
     * Determine if the given key should exist in the output.
     *
     * @param $key
     *
     * @return bool
     */
    private function shouldShowKey($key)
    {
        if ($this->key) {
            if (! $this->option('close') && $key != $this->key) {
                return false;
            }

            if ($this->option('close') && ! Str::contains($key, $this->key)) {
                return false;
            }
        }

        return true;
    }
}
