<script setup lang="ts">
import { usePageBuilderStore } from '@/stores/pageBuilder';
import { HeadingBlock } from '@/types';
import { computed, ref } from 'vue';
import Text from './Text.vue';

const props = defineProps<{
    blockId: string;
}>();

const store = usePageBuilderStore();

const block = computed(() => store.findBlock(props.blockId) as HeadingBlock | null);
const level = computed(() => {
  return block.value?.config.level
});

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
        <Text ref="textRef" :content="block?.content" :on-update-text="onUpdateText" />
    </component>
</template>
