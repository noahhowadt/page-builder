<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePageBuilderStore } from '@/stores/pageBuilder';
import type { ParagraphBlock, TextNode } from '@/types';
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

function onUpdateContent(content: TextNode[]) {
    store.updateBlockContent(props.blockId, content);
}
</script>

<template>
    <p class="min-h-[1.5em] cursor-default" @dblclick="onDblclick">
        <Text ref="textRef" :content="block?.content" :on-update-content="onUpdateContent" />
    </p>
</template>
