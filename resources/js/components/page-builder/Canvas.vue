<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { onMounted, onUnmounted, ref } from 'vue';
import BlockRenderer from './BlockRenderer.vue';

const store = usePageBuilderStore();
const dragCounter = ref(0);

function onDragEnter(e: DragEvent) {
    e.preventDefault();
    dragCounter.value++;
    if (!store.isDragging) store.startDrag();
}

function onDragOver(e: DragEvent) {
    e.preventDefault();
    store.updateClosestDropZone(e.clientX, e.clientY);
}

function onDragLeave() {
    dragCounter.value--;
    if (dragCounter.value === 0) store.endDrag();
}

function onDrop(e: DragEvent) {
    e.preventDefault();
    dragCounter.value = 0;
    store.dropAtActiveZone(e);
}

function onDragEnd() {
    dragCounter.value = 0;
    store.endDrag();
}

onMounted(() => document.addEventListener('dragend', onDragEnd));
onUnmounted(() => document.removeEventListener('dragend', onDragEnd));
</script>

<template>
    <main
        class="flex-1 overflow-auto bg-gray-50 p-4"
        @dragenter="onDragEnter"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <div class="min-h-full rounded-lg border bg-white">
            <BlockRenderer :parent-id="null" empty-zone-fills-parent />
        </div>
    </main>
</template>
