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
  <h2 class="text-2xl font-bold">
    This is a heading.
    <BlockRenderer :structure="block.children || []" :selected-id="selectedId" :on-select="onSelect" />
  </h2>
</template>
