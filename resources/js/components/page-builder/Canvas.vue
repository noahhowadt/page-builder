<script setup lang="ts">
import { Block, BlockType } from '@/types';
import { createId } from '@paralleldrive/cuid2';
import { ref } from 'vue';
import BlockRenderer from './BlockRenderer.vue';

const structure = ref<Array<Block>>([]);
const selectedId = ref<string | null>(null);

function onSelect(id: string) {
  selectedId.value = id;
}

function createBlockFromDrop(e: DragEvent): Block | null {
  if (!e.dataTransfer) return null;
  const type = e.dataTransfer.getData('application/x-block-type');
  if (!type) return null;
  const children =
    type === 'paragraph'
      ? [{ type: 'text' as const, text: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.' }]
      : type === 'heading' ? [{type: 'text' as const, text: 'Heading'}] : [];
  return {
    id: createId(),
    type: type as BlockType,
    config: {},
    children
  };
}

function onDropAt(index: number, e: DragEvent) {
  e.preventDefault();
  const newBlock = createBlockFromDrop(e);
  if (!newBlock) return;
  structure.value.splice(index, 0, newBlock);
}

function onDrop(e: DragEvent) {
  e.preventDefault();
  const newBlock = createBlockFromDrop(e);
  if (!newBlock) return;
  structure.value.push(newBlock);
}

function onDragOver(e: DragEvent) {
  e.preventDefault(); // allow drop
}

function onUpdateBlockText(blockId: string, newText: string) {
  const block = structure.value.find((b) => b.id === blockId);
  if (!block) return;
  const children = block.children ?? [];
  const hasTextNode = children.some((n) => n.type === 'text');
  if (hasTextNode) {
    block.children = children.map((node) =>
      node.type === 'text' ? { ...node, text: newText } : node
    );
  } else {
    block.children = [...children, { type: 'text' as const, text: newText }];
  }
}
</script>

<template>
  <main class="flex-1 bg-gray-50 p-4 overflow-auto" @dragover="onDragOver" @drop="onDrop">
    <!-- Click background to select page -->
    <div class="min-h-full border bg-white rounded-lg">
      <BlockRenderer
        :structure="structure"
        :selected-id="selectedId"
        :on-select="onSelect"
        :on-update-block-text="onUpdateBlockText"
        @drop-at="onDropAt"
      />
    </div>
  </main>
</template>