<script lang="ts" setup>
import type { LibraryComponent } from '@/types';
import { Puzzle } from 'lucide-vue-next';

const props = defineProps<{
  item: LibraryComponent;
}>();

function onDragStart(e: DragEvent) {
  if (!e.dataTransfer) return;
  e.dataTransfer.effectAllowed = 'copy';
  e.dataTransfer.setData('application/x-component-id', String(props.item.id));
}
</script>

<template>
  <div
    class="flex cursor-grab items-center gap-3 rounded-md border border-neutral-200 bg-white px-3 py-2.5 text-sm text-neutral-700 shadow-sm transition-colors hover:border-neutral-300 hover:bg-neutral-50 active:cursor-grabbing dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:border-neutral-600 dark:hover:bg-neutral-700"
    draggable="true"
    @dragstart="onDragStart"
  >
    <span
      class="flex size-4 shrink-0 items-center justify-center rounded bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400"
    >
      <Puzzle class="size-3" stroke-width="1.5" />
    </span>
    <span class="truncate text-xs font-medium">{{ item.name }}</span>
  </div>
</template>
