<script setup lang="ts">
import BlockRenderer from '../BlockRenderer.vue';

defineProps<{
    blockId: string;
}>();
</script>

<template>
    <div class="relative flex min-h-25 w-full flex-col">
        <BlockRenderer :parent-id="blockId" empty-zone-fills-parent />
    </div>
</template>
