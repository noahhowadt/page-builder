<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { isContainerBlock } from '@/types';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        zoneId: string;
    }>(),
    {}
);

const store = usePageBuilderStore();
const el = ref<HTMLElement | null>(null);
const isHighlighted = computed(() => store.isDragging && store.activeDropZoneId === props.zoneId);

const isRow = computed(() => {
    const colonIndex = props.zoneId.lastIndexOf(':');
    const parentId = colonIndex > 0 ? props.zoneId.substring(0, colonIndex) : null;
    if (!parentId) return false;
    const parent = store.findBlock(parentId);
    if (!parent) return false;
    return isContainerBlock(parent) && parent.config.direction === 'row';
});

onMounted(() => {
    if (el.value) store.registerDropZone(props.zoneId, el.value);
});

onUnmounted(() => {
    store.unregisterDropZone(props.zoneId);
});

watch(
    () => props.zoneId,
    (newId, oldId) => {
        if (oldId) store.unregisterDropZone(oldId);
        if (el.value) store.registerDropZone(newId, el.value);
    },
);
</script>

<template>
    <div
        ref="el"
        class="relative z-10 shrink-0"
        :class="[
            isRow
                ? isHighlighted
                    ? 'w-0 min-w-0 self-stretch outline-1 outline-blue-400'
                    : 'min-w-0 w-0 self-stretch'
                : isHighlighted
                    ? 'h-0 min-h-0 outline-1 outline-blue-400'
                    : 'h-0 min-h-0',
        ]"
        @dragover.prevent
    >
    </div>
</template>
