<?php

namespace Anomalyce\Pai18n\Parsers;

use Anomalyce\Pai18n\Contracts\Parser;

class VueComponentParser implements Parser
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
   * <code>
   *   <translation>
   *     This is the :translation key, with parameters.
   *
   *     <template #translation>
   *       <strong> i18n </strong>
   *     </template>
   *   </translation>
   * </code>
   *
   * @return array
   */
  public function handle(): array
  {
    preg_match_all("#<translation(.*)>([^<]+)<(template|\/?translation)#i", $this->content, $matches);

    $items = array_map(fn ($x) => str_replace(PHP_EOL, ' ', $x), $matches[2] ?? []);

    return array_values(array_unique(array_map('trim', $items)));
  }
}
