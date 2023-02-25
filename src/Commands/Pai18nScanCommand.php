<?php

namespace Anomalyce\Pai18n\Commands;

use Illuminate\Console\Command;
use Anomalyce\Pai18n\{ Pai18n, Parsers };
use Illuminate\Support\{ Arr, LazyCollection, Facades\File };

class Pai18nScanCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = '
    pai18n:scan
      {locale                       : ISO-639-1 code for the target locale}
      {--p|--paths=                 : Comma-separated list of scannable file paths}
      {--x|--extensions=php,js,vue} : Comma-separated list of acceptable file extensions}
      {--b|--blank                  : Leave the values as blank}
      {--o|--overwrite              : Overwrite any pre-existing translation values}
  ';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Scan files for translation keys.';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $locale = $this->argument('locale');

    if (empty($directories = $this->getDirectories())) {
      return $this->error('At least one directory must be set. See --paths for more information.');
    }

    if (empty($extensions = $this->getExtensions())) {
      return $this->error('At least one extension must be set. See --extensions for more information.');
    }

    $keys = $this->scan($directories, $extensions);

    $this->saveAsFile(
      $locale, $this->mergeWithDefaults($keys)
    );

    return 0;
  }

  /**
   * Save the translation keys as a locale file.
   *
   * @param  string  $locale
   * @param  array  $translations
   * @return string
   */
  protected function saveAsFile(string $locale, array $translations): string
  {
    $filepath = $this->getFilepath($locale);

    $contents = $original = is_file($filepath) ? json_decode(file_get_contents($filepath), true) : [];

    foreach ($translations as $key => $translation) {
      if (isset($contents[$key]) and ! (bool) $this->option('overwrite')) {
        continue;
      }

      $contents[$key] = (bool) $this->option('blank') ? '' : $translation;
    }

    $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE;

    $this->info(vsprintf('Found <comment>%d</comment> translations (<comment>%d</comment> changes) and saved them to <comment>%s</comment>.', [
      count($translations),
      count(array_diff_assoc($contents, $original)),
      $filepath,
    ]));

    file_put_contents($filepath, json_encode($contents, $flags, 512));

    return $filepath;
  }

  /**
   * Scan an array of directories for files ending with the given extensions.
   *
   * @param  array  $directories
   * @param  array  $extensions
   * @return array
   */
  protected function scan(array $directories, array $extensions): array
  {
    $files = (new LazyCollection(array_map(fn ($d) => File::allFiles($d), $directories)))
      ->flatten()
      ->reject(fn ($x) => ! in_array($x->getExtension(), $extensions))
      ->map(fn ($x) => $x->getPathname())
      ->toArray();

    $pai18n = new Pai18n([
      Parsers\VueComponentParser::class,
      Parsers\HelperParser::class,
    ]);

    return $pai18n->extractFromFiles($files);
  }

  /**
   * Merge the translation keys with the default Laravel translations.
   *
   * @param  array  $keys
   * @return array
   */
  protected function mergeWithDefaults(array $keys): array
  {
    $fallback = app()->getFallbackLocale();

    $translations = function ($path, array $params = []) use ($fallback) {
      $items = trans($path, $params, $fallback, true);

      return $items !== $path ? $path : [];
    };

    $messages = Arr::dot([
      'auth'        => $translations('auth'),
      'pagination'  => $translations('pagination'),
      'password'    => $translations('password'),
      'validation'  => $translations('validation'),
      ...$translations('*'),
    ]);

    $messages = array_filter($messages, fn ($x) => ! is_array($x));

    return array_merge(array_combine($keys, $keys), $messages);
  }

  /**
   * Retrieve the file path.
   *
   * @param  string  $locale
   * @return string
   */
  protected function getFilepath(string $locale): string
  {
    return base_path("locale/${locale}.json");
  }

  /**
   * Retrieve the directories.
   *
   * @return array
   */
  protected function getDirectories(): array
  {
    return array_filter(preg_split('/,\s?/', $this->option('paths') ?? null));
  }

  /**
   * Retrieve the extensions.
   *
   * @return array
   */
  protected function getExtensions(): array
  {
    return array_filter(preg_split('/,\s?/', $this->option('extensions') ?? null));
  }
}
