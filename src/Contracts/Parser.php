<?php

namespace Anomalyce\Pai18n\Contracts;

interface Parser
{
  /**
   * Instantiate a new parser object.
   *
   * @param  string  $content
   * @return void
   */
  public function __construct(string $content);

  /**
   * Parse the content.
   *
   * @return array
   */
  public function handle(): array;
}
