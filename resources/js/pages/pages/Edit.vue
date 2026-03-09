<script setup lang="ts">
import Canvas from '@/components/page-builder/Canvas.vue';
import Sidebar from '@/components/page-builder/Sidebar.vue';
import type { Page } from '@/types';
import type { Block } from '@/types';
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { router } from '@inertiajs/vue3';
import { onBeforeMount, watch } from 'vue';

const props = defineProps<{
    page: Page;
    structure: Block | null;
    updateUrl: string;
}>();

const store = usePageBuilderStore();

function initStructure() {
    if (props.structure && props.structure.type === 'root') {
        store.setRoot(props.structure);
    } else {
        store.setRoot(store.createRootBlock());
    }
}

onBeforeMount(initStructure);
watch(
    () => [props.page.id, props.structure] as const,
    () => initStructure(),
    { deep: true },
);

function save() {
    if (!store.structure) return;
    router.put(props.updateUrl, { structure: store.structure });
}
</script>

<template>
    <div class="flex h-screen flex-col">
        <header
            class="flex shrink-0 items-center justify-end gap-2 border-b border-neutral-200 bg-white px-4 py-2 dark:border-neutral-800 dark:bg-neutral-900"
        >
            <button
                type="button"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                @click="save"
            >
                Save
            </button>
        </header>
        <div class="flex flex-1 overflow-hidden">
            <Sidebar />
            <Canvas />
        </div>
    </div>
</template>
