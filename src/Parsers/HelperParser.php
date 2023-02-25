<?php

namespace Anomalyce\Pai18n\Parsers;

use Anomalyce\Pai18n\Contracts\Parser;

class HelperParser implements Parser
{
  /**
   * Instantiate a new parser object.
   *
   * @param  string  $content
   * @return void
   */
  public function __construct(protected string $content)
  {
    //
  }

  /**
   * Parse the content.
   *
   * <code lang="php">
   *   echo __('This is the :translation key, with parameters.', [
   *     'translation' => 'i18n',
   *   ]);
   * </code>
   *
   * <code lang="javascript">
   *   {{ __('This is the :translation key, with parameters.', {
   *     translation: 'i18n',
   *   }) }}
   * </code>
   *
   * @return array
   */
  public function handle(): array
  {
    $content = str_replace(PHP_EOL, ' ', $this->content);

    preg_match_all("#__ *\( *((['\"])((?:\\\\\\2|.)*?)\\2)#", $content, $matches);

    return array_values(array_unique(array_map('trim', $matches[3] ?? [])));
  }
}
