<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, onBeforeMount, provide, watch } from 'vue';
import Canvas from '@/components/page-builder/Canvas.vue';
import Sidebar from '@/components/page-builder/Sidebar.vue';
import { usePageBuilderStore } from '@/stores/pageBuilder';
import type {
  Block,
  ComponentForEditor,
  LibraryComponent,
  LinkablePage,
  RootBlock,
} from '@/types';

const props = defineProps<{
  component: ComponentForEditor;
  structure: Block | null;
  updateUrl: string;
  linkablePages: LinkablePage[];
  libraryComponents: LibraryComponent[];
}>();

provide(
  'linkablePages',
  computed(() => props.linkablePages),
);

provide(
  'libraryComponents',
  computed(() => props.libraryComponents),
);

const store = usePageBuilderStore();

function init() {
  store.setRoot(props.structure as RootBlock);
  store.setLibraryComponents(props.libraryComponents);
}

onBeforeMount(init);
watch(
  () => [props.component.id, props.structure] as const,
  () => init(),
  { deep: true },
);

function save() {
  const stripped = store.getStructureForSave();
  if (!stripped) return;
  router.put(props.updateUrl, { structure: stripped } as never);
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
