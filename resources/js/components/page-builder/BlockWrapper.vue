<script setup lang="ts">
import type { Block } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
  block: Block;
  selectedId?: string | null;
  onSelect?: (id: string) => void;
}>();

const isSelected = computed(() => props.selectedId === props.block.id);

function handleClick(e: MouseEvent) {
  e.stopPropagation();
  props.onSelect?.(props.block.id);
}
</script>

<template>
  <div
    class="relative border-2 border-transparent transition-colors hover:border-blue-400 hover:border-solid"
    :class="{
      'border-blue-500! bg-blue-50/30! dark:bg-blue-950/20!': isSelected
    }"
    @click="handleClick"
  >
    <slot />
  </div>
</template>
