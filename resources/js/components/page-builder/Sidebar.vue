<script setup lang="ts">
import type { Block } from '@/types';
import { BlockType } from '@/types';
import { HeadingIcon, LucideIcon, RectangleHorizontalIcon, TextIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { usePageBuilderStore } from '@/stores/pageBuilder';
import BlockLibraryItem from './BlockLibraryItem.vue';

const activeTab = ref<'blocks' | 'structure'>('blocks');
const pageBuilder = usePageBuilderStore();

const primitiveBlocks: Array<{
  type: BlockType;
  name: string;
  icon: LucideIcon;
}> = [
  { type: 'container', name: 'Box', icon: RectangleHorizontalIcon },
  { type: 'heading', name: 'Heading', icon: HeadingIcon },
  { type: 'paragraph', name: 'Paragraph', icon: TextIcon },
];

function flattenStructure(blocks: Block[], depth = 0): Array<{ block: Block; depth: number }> {
  const result: Array<{ block: Block; depth: number }> = [];
  for (const block of blocks) {
    result.push({ block, depth });
    if (block.children?.length) {
      const childBlocks = block.children.filter((n): n is Block => 'id' in n);
      result.push(...flattenStructure(childBlocks, depth + 1));
    }
  }
  return result;
}

const structureList = computed(() =>
  flattenStructure(pageBuilder.structure, 0),
);

function onDragStart(e: DragEvent, blockType: string) {
  if (!e.dataTransfer) return;
  e.dataTransfer.effectAllowed = 'copy';
  e.dataTransfer.setData('application/x-block-type', blockType);
}
</script>

<template>
  <aside
    class="flex w-72 shrink-0 flex-col border-r border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900/50"
  >
    <div class="border-b border-neutral-200 dark:border-neutral-800">
      <div class="flex" role="tablist">
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'blocks'"
          class="flex-1 px-4 py-3 text-xs font-semibold uppercase tracking-wider transition-colors"
          :class="activeTab === 'blocks'
            ? 'border-b-2 border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
            : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300'"
          @click="activeTab = 'blocks'"
        >
          Blocks
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'structure'"
          class="flex-1 px-4 py-3 text-xs font-semibold uppercase tracking-wider transition-colors"
          :class="activeTab === 'structure'
            ? 'border-b-2 border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
            : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300'"
          @click="activeTab = 'structure'"
        >
          Structure
        </button>
      </div>
    </div>
    <div class="flex-1 overflow-auto p-3">
      <template v-if="activeTab === 'blocks'">
        <div class="grid grid-cols-2 gap-2">
          <BlockLibraryItem
            v-for="block in primitiveBlocks"
            :key="block.type"
            :block="block"
            draggable="true"
            @dragstart="onDragStart($event, block.type)"
          />
        </div>
      </template>
      <template v-else-if="activeTab === 'structure'">
        <div class="font-mono text-xs text-neutral-600 dark:text-neutral-400">
          <template v-if="structureList.length === 0">
            <p class="text-neutral-500 dark:text-neutral-500">No blocks yet.</p>
          </template>
          <div
            v-for="{ block, depth } in structureList"
            :key="block.id"
            :style="{ paddingLeft: `${depth * 12}px` }"
            class="py-0.5"
          >
            {{ block.type }}
          </div>
        </div>
      </template>
    </div>
  </aside>
</template>