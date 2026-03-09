<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { isBlockWithBlockChildren, type Block } from '@/types';
import { HeadingIcon, LucideIcon, RectangleHorizontalIcon, TextIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import BlockLibraryItem from './BlockLibraryItem.vue';
import ContainerConfigForm from './config-forms/ContainerConfigForm.vue';
import HeadingConfigForm from './config-forms/HeadingConfigForm.vue';

const activeTab = ref<'blocks' | 'structure' | 'config'>('blocks');
const pageBuilder = usePageBuilderStore();

const primitiveBlocks: Array<{
  type: Block['type'];
  name: string;
  icon: LucideIcon;
}> = [
  { type: 'container', name: 'Container', icon: RectangleHorizontalIcon },
  { type: 'heading', name: 'Heading', icon: HeadingIcon },
  { type: 'paragraph', name: 'Paragraph', icon: TextIcon },
];

function flattenStructure(root: Block, depth = 0): Array<{ block: Block; depth: number }> {
  const result: Array<{ block: Block; depth: number }> = [{block: root, depth}];
  if(isBlockWithBlockChildren(root)) {
    for (const block of root.children) {
      result.push(...flattenStructure(block, depth + 1));
    }
  }

  return result;
}

const structureList = computed(() =>
  pageBuilder.structure ? flattenStructure(pageBuilder.structure, 0) : [],
);

const selectedBlock = computed(() =>
  pageBuilder.selectedBlockId ? pageBuilder.findBlock(pageBuilder.selectedBlockId) : null,
);

const selectedBlockHasConfig = computed(
  () =>
    selectedBlock.value?.type === 'heading' || selectedBlock.value?.type === 'container',
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
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'config'"
          class="flex-1 px-4 py-3 text-xs font-semibold uppercase tracking-wider transition-colors"
          :class="activeTab === 'config'
            ? 'border-b-2 border-neutral-900 text-neutral-900 dark:border-neutral-100 dark:text-neutral-100'
            : 'text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300'"
          @click="activeTab = 'config'"
        >
          Config
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
          <button
            v-for="{ block, depth } in structureList"
            :key="block.id"
            type="button"
            :style="{ paddingLeft: `${depth * 12}px` }"
            class="w-full rounded px-2 py-1.5 text-left transition-colors hover:bg-neutral-200 dark:hover:bg-neutral-700"
            :class="
              pageBuilder.selectedBlockId === block.id
                ? 'bg-blue-100 text-blue-900 dark:bg-blue-900/40 dark:text-blue-100'
                : ''
            "
            @click="pageBuilder.selectBlock(block.id)"
          >
            {{ block.type }}
          </button>
        </div>
      </template>
      <template v-else-if="activeTab === 'config'">
        <div v-if="!selectedBlock" class="text-sm text-neutral-500 dark:text-neutral-400">
          Select a block to edit its config.
        </div>
        <div v-else-if="!selectedBlockHasConfig" class="text-sm text-neutral-500 dark:text-neutral-400">
          This block has no config.
        </div>
        <template v-else>
          <HeadingConfigForm v-if="selectedBlock.type === 'heading'" :block-id="selectedBlock.id" />
          <ContainerConfigForm v-else-if="selectedBlock.type === 'container'" :block-id="selectedBlock.id" />
        </template>
      </template>
    </div>
  </aside>
</template>