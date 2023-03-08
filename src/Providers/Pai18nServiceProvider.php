<?php

namespace Anomalyce\Pai18n\Providers;

use Illuminate\Support\Arr;
use Anomalyce\Pai18n\Commands;
use Illuminate\Support\Facades\{ Lang, Blade };
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
    Blade::directive('pai18n', function ($expression) {
      return "<?php echo app('pai18n')->generateHtml($expression); ?>";
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
          $locales = (array) config('pai18n.locales', []);

          $this->locale = $this->app->getLocale();
          $this->fallbackLocale = $this->app->getFallbackLocale();
          $this->locales = array_unique(array_merge($locales, [$this->fallbackLocale]));
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

        public function toArray(array $locales = []): array {
          $this->locales = array_unique(array_merge($this->locales, $locales));

          return [
            'locale' => $this->locale,
            'locales' => [$this->locale, ...$this->locales],
            'translations' => $this->getMessages(),
          ];
        }

        public function toJson(array $locales = []): string {
          return json_encode($this->toArray($locales), JSON_UNESCAPED_UNICODE);
        }

        public function generateJavaScript(array $locales = []) {
          return <<<JS
            const pai18n = {$this->toJson($locales)};
          JS;
        }

        public function generateHtml(...$locales) {
          return <<<HTML
            <script>
              {$this->generateJavaScript($locales)}
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
