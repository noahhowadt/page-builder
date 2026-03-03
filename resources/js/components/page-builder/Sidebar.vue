<script setup lang="ts">
import { BlockType } from '@/types';
import { HeadingIcon, LucideIcon, RectangleHorizontalIcon, TextIcon } from 'lucide-vue-next';
import BlockLibraryItem from './BlockLibraryItem.vue';

const primitiveBlocks: Array<{
  type: BlockType;
  name: string;
  icon: LucideIcon;
}> = [{
  type: 'container',
  name: 'Box',
  icon: RectangleHorizontalIcon
}
    , {
  type: 'heading',
  name: 'Heading',
  icon: HeadingIcon
}, {
  type: 'paragraph',
  name: 'Paragraph',
  icon: TextIcon
}]

function onDragStart(e: DragEvent, blockType: string) {
  if (!e.dataTransfer) return;
  e.dataTransfer.effectAllowed = "copy";
  e.dataTransfer.setData("application/x-block-type", blockType);
}
</script>

<template>
  <aside
    class="flex w-72 shrink-0 flex-col border-r border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900/50"
  >
    <div class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
        Blocks
      </h2>
    </div>
    <div class="flex-1 overflow-auto p-3">
      <div class="grid grid-cols-2 gap-2">
        <BlockLibraryItem
          v-for="block in primitiveBlocks"
          :key="block.type"
          :block="block"
          draggable="true"
          @dragstart="onDragStart($event, block.type)"
        />
      </div>
    </div>
  </aside>
</template>