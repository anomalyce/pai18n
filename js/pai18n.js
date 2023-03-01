import { ref, inject } from 'vue'
export { Translation as VueComponent } from './VueComponent.js'

/**
 * Translate a message.
 *
 * @param  string  path
 * @param  object  params
 * @return string
 */
function translate(options, path, params) {
  const translations = options.translations[options.locale.value] || {}

  let message = translations[path] || path

  Object.entries(params || {}).map(([k, v]) => {
    message = message.replace(new RegExp(`:${k}`, 'i'), v)
  })

  return message
}

export const usePai18n = () => {
  const options = inject('pai18n')

  return {
    locale: options?.locale,
    translations: options?.translations,
    translate: (...args) => Reflect.apply(translate, undefined, [options, ...args]),
    __: (...args) => Reflect.apply(translate, undefined, [options, ...args]),
  }
}

export const createPai18n = {
  install(app, options) {
    const locale = ref(options.locale)

    app.provide('pai18n', {
      locale,
      translations: options.translations,
    })
  }
}

export default usePai18n
