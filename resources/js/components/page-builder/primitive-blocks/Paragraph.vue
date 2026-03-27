<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { ParagraphBlock } from '@/types';
import { computed, ref } from 'vue';
import Text from './Text.vue';

const props = defineProps<{
    blockId: string;
}>();

const store = usePageBuilderStore();

const block = computed(() => store.findBlock(props.blockId) as ParagraphBlock | null);

const textRef = ref<{ startEditing: (e?: MouseEvent) => void } | null>(null);

function onDblclick(e: MouseEvent) {
    e.stopPropagation();
    textRef.value?.startEditing(e);
}

function onUpdateText(text: string) {
    store.updateBlockContent(props.blockId, [{ type: 'text', text }]);
}
</script>

<template>
    <p class="min-h-[1.5em] cursor-default" @dblclick="onDblclick">
        <Text ref="textRef" :content="block?.content" :on-update-text="onUpdateText" />
    </p>
</template>
