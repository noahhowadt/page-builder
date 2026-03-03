<script setup lang="ts">
import Canvas from '@/components/page-builder/Canvas.vue';
import Sidebar from '@/components/page-builder/Sidebar.vue';
import { Block, Page } from '@/types';
import { computed, ref } from "vue";

defineProps<{
  page: Page
}>();

const structure = ref();

// Track selected element (null = page)
const selectedId = ref<string | null>(null);

function selectPage() {
  selectedId.value = null;
}

function selectBlock(id: string) {
  selectedId.value = id;
}

const selectedBlock = computed(() =>
  structure.value.find((b: Block) => b.id === selectedId.value)
);

</script>

<template>
  <div class="flex h-screen">
    <!-- Left Sidebar (Block Library placeholder) -->
    <Sidebar />

    <!-- Canvas -->
    <Canvas :structure="structure" />
  </div>
</template>
