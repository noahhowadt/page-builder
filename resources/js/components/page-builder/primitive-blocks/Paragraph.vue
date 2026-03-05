<script setup lang="ts">
import type { Block } from '@/types';
import { computed, ref } from 'vue';
import Text from './Text.vue';

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

const textRef = ref<{ startEditing: (e?: MouseEvent) => void } | null>(null);

function onDblclick(e: MouseEvent) {
  e.stopPropagation();
  textRef.value?.startEditing(e);
}
</script>

<template>
  <p
    class="min-h-[1.5em] cursor-default"
    @dblclick="onDblclick"
  >
    <Text
      ref="textRef"
      :text="displayText"
      :on-update-text="onUpdateText"
    />
  </p>
</template>
