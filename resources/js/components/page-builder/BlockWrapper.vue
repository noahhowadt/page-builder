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
        class="relative hover:outline-2 hover:outline-blue-400"
        :class="{
            'outline-blue-500!': isSelected,
        }"
        @click="handleClick"
    >
        <slot />
    </div>
</template>
