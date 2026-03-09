<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import type { HeadingBlock } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
  blockId: string;
}>();

const store = usePageBuilderStore();
const block = computed(() => store.findBlock(props.blockId) as HeadingBlock | null);
const level = computed({
  get: () => block.value?.config?.level ?? 1,
  set: (value: HeadingBlock['config']['level']) => {
    store.updateBlockConfig(props.blockId, { level: value });
  },
});
</script>

<template>
  <div class="space-y-3">
    <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">
      Level
    </label>
    <select
      :value="level"
      class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"
      @change="level = Number(($event.target as HTMLSelectElement).value) as HeadingBlock['config']['level']"
    >
      <option v-for="n in 6" :key="n" :value="n">
        Heading {{ n }}
      </option>
    </select>
  </div>
</template>
