<?php

namespace Anomalyce\Pai18n\Providers;

use Illuminate\Support\Arr;
use Anomalyce\Pai18n\Commands;
use Illuminate\Support\Facades\Blade;
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
    Blade::directive('pai18n', function ($string) {
      return "<?php echo app('pai18n')->generateHtml(); ?>";
    });
  }

  /**
   * Register bindings in the container.
   *
   * @return void
   */
  public function register()
  {
    $this->app->singleton('pai18n', function ($app) {
      return new class($app) {
        protected string $locale;
        protected string $fallbackLocale;
        protected array $locales;

        public function __construct(protected $app) {
          $this->locale = $this->app->getLocale();
          $this->fallbackLocale = $this->app->getFallbackLocale();
          $this->locales = array_unique([$this->fallbackLocale]);
        }

        protected function getTranslations(string $path, ?string $locale = null): array
        {
          $items = trans($path, [], $locale ?: $this->fallbackLocale, true);

          return $items !== $path ? $items : [];
        }

        protected function getMessages(): array
        {
          $messages = [];

          foreach ($this->locales as $locale) {
            $messages[$locale] = array_filter($this->getTranslations('*', $locale), fn ($value) => ! empty($value));
          }

          return $messages;
        }

        public function toArray(): array {
          return [
            'locale' => $this->locale,
            'translations' => $this->getMessages(),
          ];
        }

        public function toJson(): string {
          return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
        }

        public function generateJavaScript() {
          return <<<JS
            const pai18n = {$this->toJson()};
          JS;
        }

        public function generateHtml() {
          return <<<HTML
            <script>
              {$this->generateJavaScript()}
            </script>
          HTML;
        }
      };
    });

    $this->commands([
      Commands\Pai18nScanCommand::class,
    ]);
  }
}
