<script setup lang="ts">
import Canvas from '@/components/page-builder/Canvas.vue';
import Sidebar from '@/components/page-builder/Sidebar.vue';
import type { Page } from '@/types';

defineProps<{
    page: Page;
}>();
</script>

<template>
    <div class="flex h-screen">
        <Sidebar />
        <Canvas />
    </div>
</template>
