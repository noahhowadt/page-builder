<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { computed, ref } from 'vue';
import Text from './Text.vue';

const props = defineProps<{
    blockId: string;
}>();

const store = usePageBuilderStore();

const block = computed(() => store.findBlock(props.blockId));
const level = computed(() => {
  const b = block.value;
  return b?.type === 'heading' ? b.config.level : 1;
});
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
    <component
        :is="`h${level}`"
        class="min-h-[1.5em] cursor-default font-bold"
        :class="{
            'text-4xl': level === 1,
            'text-2xl': level === 2,
            'text-xl': level === 3,
            'text-lg': level === 4,
            'text-base': level === 5,
            'text-sm': level === 6,
        }"
        @dblclick="onDblclick"
    >
        <Text ref="textRef" :text="displayText" :on-update-text="onUpdateText" />
    </component>
</template>
