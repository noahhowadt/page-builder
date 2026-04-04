<script setup lang="ts">
import BlockRenderer from '../BlockRenderer.vue';

defineProps<{
    blockId: string;
}>();
</script>

<template>
    <div>
        <BlockRenderer :parent-id="blockId" :interactive="false" />
    </div>
</template>
