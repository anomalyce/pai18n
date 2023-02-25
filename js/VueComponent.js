import { defineComponent, h } from 'vue'

/**
 * Replace all of the substitute keywords with the values from their respective slot templates.
 *
 * @param  string  text
 * @param  object  context
 * @return array
 */
function replaceSubstitutes(text, context) {
  const substitutes = Object.keys(context.slots).filter(s => s !== 'default')

  return text.split(new RegExp('(:' + substitutes.join('|:') + ')'))
    .map(p => {
      if (p[0] === ':' && substitutes.includes(p.slice(1))) {
        return context.slots[p.slice(1)]()
      }

      return [p]
    })
    .flat(1)
}

export const Translation = defineComponent({
  props: ['as'],

  setup(props, context) {
    const nodes = context.slots.default()
    const key = nodes.map(x => x.children).join('').trim()

    return () => {
      const content = replaceSubstitutes(key, context)

      return h(props.as || 'div', {
        'data-pai18n-key': key,
        style: {
          display: 'contents',
        },
      }, content)
    }
  }
})
