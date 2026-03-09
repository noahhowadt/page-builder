<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { computed, ref } from 'vue';
import Text from './Text.vue';

const props = defineProps<{
    blockId: string;
}>();

const store = usePageBuilderStore();

const block = computed(() => store.findBlock(props.blockId));
const displayText = computed(() => {
    const textNode = block.value?.children?.find((n) => n.type === 'text');
    return textNode?.type === 'text' ? textNode.text : '';
});

const textRef = ref<{ startEditing: (e?: MouseEvent) => void } | null>(null);

function onDblclick(e: MouseEvent) {
    e.stopPropagation();
    textRef.value?.startEditing(e);
}

function onUpdateText(text: string) {
    store.updateBlockText(props.blockId, [{ type: 'text', text }]);
}
</script>

<template>
    <p class="min-h-[1.5em] cursor-default" @dblclick="onDblclick">
        <Text ref="textRef" :text="displayText" :on-update-text="onUpdateText" />
    </p>
</template>
