<script lang="ts">
import type { PropType } from 'vue';
import {
  createTextVNode,
  defineComponent,
  h,
  nextTick,
  ref,
  watch
} from 'vue';

export default defineComponent({
  name: 'Text',
  props: {
    text: { type: String, default: '' },
    onUpdateText: { type: Function as PropType<(text: string) => void> }
  },
  setup(props, { expose }) {
    const isEditing = ref(false);
    const editText = ref('');
    const textareaRef = ref<HTMLTextAreaElement | null>(null);
    const initialCursorOffset = ref<number | null>(null);

    function getCaretOffsetFromPoint(
      clientX: number,
      clientY: number
    ): number | null {
      const range = document.caretRangeFromPoint?.(clientX, clientY);
      if (!range) return null;
      const node = range.startContainer;
      if (node.nodeType !== Node.TEXT_NODE) return null;
      return range.startOffset;
    }

    function startEditing(e?: MouseEvent) {
      editText.value = props.text;
      isEditing.value = true;
      if (e) {
        const offset = getCaretOffsetFromPoint(e.clientX, e.clientY);
        const len = props.text.length;
        initialCursorOffset.value =
          offset !== null ? Math.min(Math.max(0, offset), len) : len;
      } else {
        initialCursorOffset.value = null;
      }
      nextTick(() => {
        const textarea = textareaRef.value;
        if (!textarea) return;
        textarea.focus();
        if (initialCursorOffset.value !== null) {
          const clamped = Math.min(
            Math.max(0, initialCursorOffset.value),
            editText.value.length
          );
          textarea.setSelectionRange(clamped, clamped);
          initialCursorOffset.value = null;
        }
      });
    }

    function finishEditing() {
      props.onUpdateText?.(editText.value);
      isEditing.value = false;
    }

    function fitTextareaHeight() {
      const el = textareaRef.value;
      if (!el) return;
      el.style.height = '0';
      el.style.height = `${Math.max(el.scrollHeight, 24)}px`;
    }

    watch(editText, () => nextTick(fitTextareaHeight));
    watch(isEditing, (editing) => {
      if (editing) nextTick(fitTextareaHeight);
    });
    watch(
      () => props.text,
      (val) => {
        if (!isEditing.value) editText.value = val;
      }
    );

    expose({ startEditing });

    return () => {
      if (!isEditing.value) {
        return createTextVNode(props.text || ' ');
      }
      return h('textarea', {
        ref: textareaRef,
        value: editText.value,
        class:
          'w-full min-h-[1.5em] resize-none overflow-hidden border-0 bg-transparent p-0 outline-none focus:ring-0',
        onInput: (e: Event) => {
          editText.value = (e.target as HTMLTextAreaElement).value;
        },
        onBlur: finishEditing,
        onKeydown: (e: KeyboardEvent) => {
          if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            finishEditing();
          }
        }
      });
    };
  }
});
</script>
