<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import type { ContainerBlock } from '@/types';
import { computed } from 'vue';
import BlockRenderer from '../BlockRenderer.vue';

const props = withDefaults(defineProps<{
    blockId: string;
    interactive?: boolean;
}>(), {
    interactive: true,
});

const store = usePageBuilderStore();
const block = computed(() => store.findBlock(props.blockId) as ContainerBlock | null);
const config = computed(() => block.value?.config ?? { direction: 'column', gap: 0, padding: 20 });
</script>

<template>
    <div
        class="relative flex min-h-25 w-full"
        :style="{
            display: 'flex',
            flexDirection: config.direction === 'row' ? 'row' : 'column',
            gap: `${config.gap}px`,
            padding: `${config.padding}px`,
        }"
    >
        <BlockRenderer :parent-id="blockId" :interactive="interactive" empty-zone-fills-parent />
    </div>
</template>
