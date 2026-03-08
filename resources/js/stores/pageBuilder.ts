import { isBlockWithBlockChildren, isBlockWithTextChildren, TextNode, type Block, type Node } from '@/types';
import { createId } from '@paralleldrive/cuid2';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePageBuilderStore = defineStore('pageBuilder', () => {
    const structure = ref<Block | null>(null);

    function createRootBlock(): Block {
        return {
            id: createId(),
            type: 'root',
            children: [],
        };
    }

    function setRoot(block: Block): void {
        structure.value = block;
    }
    const selectedBlockId = ref<string | null>(null);
    const activeDropZoneId = ref<string | null>(null);
    const isDragging = ref(false);

    const dropZoneElements = new Map<string, HTMLElement>();

    function findBlock(blockId: string, root: Block | null = structure.value): Block | null {
        if (!root) return null;
        if (blockId === root.id) return root;
        if (isBlockWithBlockChildren(root)) {
            for (const block of root.children) {
                const found = findBlock(blockId, block);
                if (found) return found;
            }
        }

        return null;
    }

    function getChildBlocks(parentId: string): Array<Node> {
        const parent = findBlock(parentId);
        if (!parent) throw new Error(`Parent block not found: ${parentId}`);
        return parent.children
    }

    function selectBlock(id: string): void {
        selectedBlockId.value = id;
    }

    function clearSelection(): void {
        selectedBlockId.value = null;
    }

    function createBlock(type: Block['type']): Block {
        switch (type) {
            case 'container':
                return { id: createId(), type, children: [] };
            case 'heading':
                return {
                    id: createId(), type, config: { level: 1 }, children: [{
                        type: 'text',
                        text: 'Heading',
                    }]
                };
            case 'paragraph':
                return {
                    id: createId(), type, children: [{
                        type: 'text',
                        text: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
                    }]
                };
            default:
                throw new Error(`Invalid block type: ${type}`);
        }
    }

    function insertBlock(parentId: string, index: number, type: Block['type']): void {
        const newBlock = createBlock(type);

        const parent = findBlock(parentId);
        if (!parent) throw new Error(`Trying to insert block into non-existent parent: ${parentId}`);
        parent.children.splice(index, 0, newBlock);
    }

    function updateBlockText(blockId: string, newText: Array<TextNode>): void {
        const block = findBlock(blockId);
        if (!block) throw new Error(`Block not found: ${blockId}`);
        if (!isBlockWithTextChildren(block)) throw new Error(`Block is not a text block: ${blockId}`);

        block.children = newText;
    }

    function parseZoneId(zoneId: string): { parentId: string | null; index: number } {
        console.log('parseZoneId', zoneId);
        const colonIndex = zoneId.lastIndexOf(':');
        const parentPart = zoneId.substring(0, colonIndex);
        return {
            parentId: parentPart,
            index: parseInt(zoneId.substring(colonIndex + 1), 10),
        };
    }

    function dropAtActiveZone(e: DragEvent): void {
        if (!activeDropZoneId.value || !e.dataTransfer) return;

        const type = e.dataTransfer.getData('application/x-block-type') as Block['type'];
        if (!type) return;

        const { parentId, index } = parseZoneId(activeDropZoneId.value);
        if (!parentId) throw new Error(`Trying to drop at root, but block is not a root: ${activeDropZoneId.value}`);
        console.log('dropAtActiveZone', parentId, index, type);

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
        createRootBlock,
        setRoot,
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
