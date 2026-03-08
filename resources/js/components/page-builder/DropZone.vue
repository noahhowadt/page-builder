<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        zoneId: string;
        fillsParent?: boolean;
    }>(),
    { fillsParent: false },
);

const store = usePageBuilderStore();
const el = ref<HTMLElement | null>(null);
const isHighlighted = computed(() => store.isDragging && store.activeDropZoneId === props.zoneId);

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
            fillsParent
                ? 'absolute inset-0 flex items-center justify-center'
                : isHighlighted
                  ? 'h-0 min-h-0 -mb-0.5 outline-1 outline-blue-400'
                  : 'h-0 min-h-0',
        ]"
        @dragover.prevent
    >
        <div v-if="fillsParent && isHighlighted" class="w-full outline-1 outline-blue-400" />
    </div>
</template>
