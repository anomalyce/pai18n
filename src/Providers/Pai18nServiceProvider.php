<?php

namespace Anomalyce\Pai18n\Providers;

use Anomalyce\Pai18n\Commands;
use Illuminate\Support\AggregateServiceProvider;

class Pai18nServiceProvider extends AggregateServiceProvider
{
  /**
   * Perform post-registration booting of services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }

  /**
   * Register bindings in the container.
   *
   * @return void
   */
  public function register()
  {
    $this->commands([
      Commands\Pai18nScanCommand::class,
    ]);
  }
}
