export { Translation as VueComponent } from './VueComponent.js'

/**
 * Translate a message.
 *
 * @param  string  message
 * @param  object  params
 * @return string
 */
function translate(message, params) {
  Object.entries(params || {}).map(([k, v]) => {
    message = message.replace(new RegExp(`:${k}`, 'i'), v)
  })

  return message
}

export const usePai18n = () => ({
  translate,
  __: translate,
})

export default usePai18n
