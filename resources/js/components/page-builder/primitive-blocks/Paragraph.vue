<script setup lang="ts">
import type { Block } from '@/types';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{
  block: Block;
  selectedId: string | null;
  onSelect: (id: string) => void;
  onUpdateText?: (text: string) => void;
}>();

const displayText = computed(() => {
  const textNode = props.block.children?.find((n) => n.type === 'text');
  return textNode?.type === 'text' ? textNode.text : '';
});

const isEditing = ref(false);
const editText = ref('');
const textareaRef = ref<HTMLTextAreaElement | null>(null);

function startEditing() {
  editText.value = displayText.value;
  isEditing.value = true;
  nextTick(() => textareaRef.value?.focus());
}

function finishEditing() {
  if (props.onUpdateText) {
    props.onUpdateText(editText.value);
  }
  isEditing.value = false;
}

function onDblclick(e: MouseEvent) {
  e.stopPropagation();
  startEditing();
}

watch(displayText, (val) => {
  if (!isEditing.value) editText.value = val;
});
</script>

<template>
  <p
    class="min-h-[1.5em] cursor-default"
    @dblclick="onDblclick"
  >
    <span v-if="!isEditing">{{ displayText || ' ' }}</span>
    <textarea
      v-else
      ref="textareaRef"
      v-model="editText"
      class="w-full resize-none border-0 bg-transparent p-0 outline-none focus:ring-0"
      rows="1"
      @blur="finishEditing"
      @keydown.enter.exact.prevent="finishEditing"
    />
  </p>
</template>
