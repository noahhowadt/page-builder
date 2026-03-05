<script setup lang="ts">
import type { Block } from '@/types';
import BlockRenderer from '../BlockRenderer.vue';

defineProps<{
  block: Block;
  selectedId: string | null;
  onSelect: (id: string) => void;
}>();
</script>

<template>
  <div class="relative w-full min-h-25">
    <BlockRenderer :structure="block.children || []" :selected-id="selectedId" :on-select="onSelect" />
  </div>
</template>