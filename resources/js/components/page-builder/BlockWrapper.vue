<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { computed } from 'vue';

const props = defineProps<{
    blockId: string;
}>();

const store = usePageBuilderStore();
const isSelected = computed(() => store.selectedBlockId === props.blockId);

function handleClick(e: MouseEvent) {
    e.stopPropagation();
    store.selectBlock(props.blockId);
}
</script>

<template>
    <div
        class="relative border-2 border-transparent transition-colors hover:border-blue-400 hover:border-solid"
        :class="{
            'border-blue-500! bg-blue-50/30! dark:bg-blue-950/20!': isSelected,
        }"
        @click="handleClick"
    >
        <slot />
    </div>
</template>
