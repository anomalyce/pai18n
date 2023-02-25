<?php

namespace Anomalyce\Pai18n;

class Pai18n
{
  /**
   * Instantiate a new Pai18n object.
   *
   * @param  array  $parsers
   * @return void
   */
  public function __construct(protected array $parsers)
  {
    //
  }

  /**
   * Extract translation keys from the given content.
   *
   * @param  string  $content
   * @return array
   */
  public function extract(string $content): array
  {
    $keys = array_merge(
      ...array_map(fn ($p) => (new $p($content))->handle(), $this->parsers)
    );

    $keys = array_map('stripslashes', $keys);

    return array_values(array_unique($keys));
  }

  /**
   * Extract translation keys from the given file.
   *
   * @param  string  $file
   * @return array
   */
  public function extractFromFile(string $file): array
  {
    return $this->extract(
      $content = $this->stripComments($file)
    );
  }

  /**
   * Extract translation keys from the given set of files.
   *
   * @param  array  $files
   * @return array
   */
  public function extractFromFiles(array $files): array
  {
    $keys = array_map(fn ($f) => $this->extractFromFile($f), $files);

    return array_values(array_unique(array_merge(...$keys)));
  }

  /**
   * Strip comments based on the file extension.
   *
   * @param  string  $file
   * @return string
   */
  protected function stripComments(string $file): string
  {
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    switch ($extension) {
      case 'php':
        return php_strip_whitespace($file);

      case 'js':
      case 'ts':
      case 'jsx':
      case 'tsx':
      case 'vue':
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';

        return preg_replace($pattern, '', file_get_contents($file));

      default:
        return file_get_contents($file);
    }
  }
}
