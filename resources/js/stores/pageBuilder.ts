import type { Block, BlockType, Node } from '@/types';
import { createId } from '@paralleldrive/cuid2';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePageBuilderStore = defineStore('pageBuilder', () => {
    const structure = ref<Block[]>([]);
    const selectedBlockId = ref<string | null>(null);
    const activeDropZoneId = ref<string | null>(null);
    const isDragging = ref(false);

    const dropZoneElements = new Map<string, HTMLElement>();

    function findBlock(blockId: string, blocks: Block[] = structure.value): Block | null {
        for (const block of blocks) {
            if (block.id === blockId) return block;
            if (block.children) {
                const childBlocks = block.children.filter((n): n is Block => 'id' in n);
                const found = findBlock(blockId, childBlocks);
                if (found) return found;
            }
        }
        return null;
    }

    function getChildBlocks(parentId: string | null): Block[] {
        if (parentId === null) return structure.value;
        const parent = findBlock(parentId);
        if (!parent?.children) return [];
        return parent.children.filter((n): n is Block => 'id' in n);
    }

    function selectBlock(id: string): void {
        selectedBlockId.value = id;
    }

    function clearSelection(): void {
        selectedBlockId.value = null;
    }

    function createBlock(type: BlockType): Block {
        const children: Node[] =
            type === 'paragraph'
                ? [
                    {
                        type: 'text' as const,
                        text: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
                    },
                ]
                : type === 'heading'
                    ? [{ type: 'text' as const, text: 'Heading' }]
                    : [];

        return { id: createId(), type, config: {}, children };
    }

    function insertBlock(parentId: string | null, index: number, type: BlockType): void {
        const newBlock = createBlock(type);

        if (parentId === null) {
            structure.value.splice(index, 0, newBlock);
            return;
        }

        const parent = findBlock(parentId);
        if (!parent) return;
        if (!parent.children) parent.children = [];
        parent.children.splice(index, 0, newBlock);
    }

    function updateBlockText(blockId: string, newText: string): void {
        const block = findBlock(blockId);
        if (!block) return;

        const children = block.children ?? [];
        const hasTextNode = children.some((n) => n.type === 'text');

        if (hasTextNode) {
            block.children = children.map((node) => (node.type === 'text' ? { ...node, text: newText } : node));
        } else {
            block.children = [...children, { type: 'text' as const, text: newText }];
        }
    }

    function parseZoneId(zoneId: string): { parentId: string | null; index: number } {
        const colonIndex = zoneId.lastIndexOf(':');
        const parentPart = zoneId.substring(0, colonIndex);
        return {
            parentId: parentPart === 'root' ? null : parentPart,
            index: parseInt(zoneId.substring(colonIndex + 1), 10),
        };
    }

    function dropAtActiveZone(e: DragEvent): void {
        if (!activeDropZoneId.value || !e.dataTransfer) return;

        const type = e.dataTransfer.getData('application/x-block-type') as BlockType;
        if (!type) return;

        const { parentId, index } = parseZoneId(activeDropZoneId.value);
        insertBlock(parentId, index, type);
        endDrag();
    }

    function registerDropZone(id: string, element: HTMLElement): void {
        dropZoneElements.set(id, element);
    }

    function unregisterDropZone(id: string): void {
        dropZoneElements.delete(id);
    }

    function startDrag(): void {
        isDragging.value = true;
    }

    function endDrag(): void {
        isDragging.value = false;
        activeDropZoneId.value = null;
    }

    function updateClosestDropZone(mouseX: number, mouseY: number): void {
        let closestId: string | null = null;
        let closestDistance = Infinity;

        for (const [id, element] of dropZoneElements) {
            const rect = element.getBoundingClientRect();
            const dx = Math.max(rect.left - mouseX, 0, mouseX - rect.right);
            const dy = Math.max(rect.top - mouseY, 0, mouseY - rect.bottom);
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < closestDistance) {
                closestDistance = distance;
                closestId = id;
            }
        }

        activeDropZoneId.value = closestId;
    }

    return {
        structure,
        selectedBlockId,
        activeDropZoneId,
        isDragging,
        findBlock,
        getChildBlocks,
        selectBlock,
        clearSelection,
        insertBlock,
        updateBlockText,
        dropAtActiveZone,
        registerDropZone,
        unregisterDropZone,
        startDrag,
        endDrag,
        updateClosestDropZone,
    };
});
