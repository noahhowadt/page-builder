<script lang="ts" setup>
import { BlockType } from '@/types';
import type { LucideIcon } from 'lucide-vue-next';

const props = defineProps<{
  block: {
    type: BlockType;
    name: string;
    icon: LucideIcon;
  };
}>();

/**
 * Handle drag start
 * We’ll pass the block type as drag data so the canvas knows what to insert.
 */
function onDragStart(e: DragEvent) {
  if (!e.dataTransfer) return;
  e.dataTransfer.effectAllowed = "copy";
  e.dataTransfer.setData("application/x-block-type", props.block.type);
}
</script>

<template>
  <div
    class="flex cursor-grab items-center gap-3 rounded-md border border-neutral-200 bg-white px-3 py-2.5 text-sm text-neutral-700 shadow-sm transition-colors hover:border-neutral-300 hover:bg-neutral-50 active:cursor-grabbing dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:border-neutral-600 dark:hover:bg-neutral-700"
    draggable="true"
    @dragstart="onDragStart"
  >
    <span
      class="flex size-8 shrink-0 items-center justify-center rounded bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400"
    >
      <component :is="block.icon" class="size-4" stroke-width="1.5" />
    </span>
    <span class="truncate font-medium">{{ block.name }}</span>
  </div>
</template>
