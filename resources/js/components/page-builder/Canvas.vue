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

function onKeyDown(e: KeyboardEvent) {
    if (e.key !== 'Delete' && e.key !== 'Backspace') return;
    const target = e.target as HTMLElement;
    if (target.closest('input, textarea, [contenteditable="true"]')) return;
    if (!store.selectedBlockId) return;
    e.preventDefault();
    store.deleteBlock(store.selectedBlockId);
}

onMounted(() => {
    document.addEventListener('dragend', onDragEnd);
    document.addEventListener('keydown', onKeyDown);
});
onUnmounted(() => {
    document.removeEventListener('dragend', onDragEnd);
    document.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
    <main
        class="flex-1 overflow-auto bg-gray-50 p-4"
        @dragenter="onDragEnter"
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
    >
        <div v-if="store.structure" class="min-h-full rounded-lg border bg-white">
            <BlockRenderer :parent-id="store.structure.id" empty-zone-fills-parent />
        </div>
    </main>
</template>
