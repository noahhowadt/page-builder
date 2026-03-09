<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import type { ContainerBlock } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
  blockId: string;
}>();

const store = usePageBuilderStore();
const block = computed(() => store.findBlock(props.blockId) as ContainerBlock | null);
const direction = computed({
  get: () => block.value?.config?.direction ?? 'column',
  set: (value: ContainerBlock['config']['direction']) => {
    store.updateBlockConfig(props.blockId, { direction: value });
  },
});
const gap = computed({
  get: () => block.value?.config?.gap ?? 0,
  set: (value: number) => {
    store.updateBlockConfig(props.blockId, { gap: value });
  },
});
const padding = computed({
  get: () => block.value?.config?.padding ?? 20,
  set: (value: number) => {
    store.updateBlockConfig(props.blockId, { padding: value });
  },
});
</script>

<template>
  <div class="space-y-3">
    <div>
      <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">
        Direction
      </label>
      <select
        :value="direction"
        class="mt-1 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"
        @change="direction = ($event.target as HTMLSelectElement).value as ContainerBlock['config']['direction']"
      >
        <option value="column">Column</option>
        <option value="row">Row</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">
        Gap
      </label>
      <input
        v-model.number="gap"
        type="number"
        min="0"
        class="mt-1 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"
      />
    </div>
    <div>
      <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">
        Padding
      </label>
      <input
        v-model.number="padding"
        type="number"
        min="0"
        class="mt-1 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"
      />
    </div>
  </div>
</template>
