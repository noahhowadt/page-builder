<script setup lang="ts">
import type { Block, Node } from '@/types';
import { ref } from 'vue';
import BlockWrapper from './BlockWrapper.vue';
import Container from './primitive-blocks/Container.vue';
import Heading from './primitive-blocks/Heading.vue';
import Paragraph from './primitive-blocks/Paragraph.vue';

defineProps<{
  structure: Array<Node>;
  selectedId: string | null;
  onSelect: (id: string) => void;
  onUpdateBlockText?: (blockId: string, text: string) => void;
}>();

const emit = defineEmits<{
  'drop-at': [index: number, e: DragEvent];
}>();

const components: Record<string, any> = {
  container: Container,
  heading: Heading,
  paragraph: Paragraph
};

const rootRef = ref<HTMLElement | null>(null);
const activeDropIndex = ref<number | null>(null);

function onZoneDragOver(index: number, e: DragEvent) {
  e.preventDefault();
  activeDropIndex.value = index;
}

function onRootDragLeave(e: DragEvent) {
  const target = e.relatedTarget as HTMLElement | null;
  if (!target || !rootRef.value?.contains(target)) {
    activeDropIndex.value = null;
  }
}

function onRootDrop() {
  activeDropIndex.value = null;
}

function onZoneDrop(index: number, e: DragEvent) {
  e.preventDefault();
  e.stopPropagation();
  activeDropIndex.value = null;
  emit('drop-at', index, e);
}
</script>

<template>
  <div
    ref="rootRef"
    class="flex flex-col"
    @dragleave="onRootDragLeave"
    @drop="onRootDrop"
  >
    <!-- Drop zone before first block -->
    <div
      class="relative z-10 min-h-3 shrink-0 border-t-2 transition-colors -my-1.5"
      :class="activeDropIndex === 0 ? 'border-blue-400' : 'border-transparent'"
      @dragover.prevent="onZoneDragOver(0, $event)"
      @drop="onZoneDrop(0, $event)"
    />
    <template v-for="(block, index) in structure" :key="'id' in block ? block.id : index">
      <BlockWrapper
        v-if="'id' in block"
        :block="block as Block"
        :selected-id="selectedId"
        :on-select="onSelect"
      >
        <component
          :is="components[(block as Block).type]"
          :block="block as Block"
          :selected-id="selectedId"
          :on-select="onSelect"
          :on-update-text="
            onUpdateBlockText
              ? (text: string) => onUpdateBlockText?.((block as Block).id, text)
              : undefined
          "
        />
      </BlockWrapper>
      <!-- Drop zone after each block -->
      <div
        class="relative z-10 min-h-3 shrink-0 border-t-2 transition-colors -my-1.5"
        :class="activeDropIndex === index + 1 ? 'border-blue-400' : 'border-transparent'"
        @dragover.prevent="onZoneDragOver(index + 1, $event)"
        @drop="onZoneDrop(index + 1, $event)"
      />
    </template>
  </div>
</template>