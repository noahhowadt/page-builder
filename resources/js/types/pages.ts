export interface Page {
  id: number;
  title: string;
  slug: string;
  is_published: boolean;
  published_at: string | null;
  created_at: string;
  updated_at: string;
}

export type TextMark = 'bold' | 'italic' | 'underline';
export interface TextNode {
  type: 'text';
  text: string;
  marks?: Array<TextMark>;
}

export interface RootBlock {
  id: string;
  type: 'root';
  children: Array<Block>;
}

export interface ContainerBlock {
  id: string;
  type: 'container';
  config: {
    direction: 'column' | 'row';
    gap: number;
    padding: number;
  }
  children: Array<Block>;
}

export interface HeadingBlock {
  id: string;
  type: 'heading';
  config: {
    level: 1 | 2 | 3 | 4 | 5 | 6;
  };
  content: Array<TextNode>;
}

export interface ParagraphBlock {
  id: string;
  type: 'paragraph';
  content: Array<TextNode>;
}

export type Block = RootBlock | ContainerBlock | HeadingBlock | ParagraphBlock;

/** Blocks that may only have other blocks as children (e.g. root, container). */
export type BlockWithBlockChildren = RootBlock | ContainerBlock;

/** Blocks that may only have text/inline nodes as content (e.g. heading, paragraph). */
export type BlockWithTextChildren = HeadingBlock | ParagraphBlock;

export type Node = TextNode | Block;

export function isBlockWithBlockChildren(
  block: Block,
): block is BlockWithBlockChildren {
  return block.type === 'root' || block.type === 'container';
}

export function isBlockWithTextChildren(
  block: Block,
): block is BlockWithTextChildren {
  return block.type === 'heading' || block.type === 'paragraph';
}

export function isContainerBlock(block: Block): block is ContainerBlock {
  return block.type === 'container';
}

export function isHeadingBlock(block: Block): block is HeadingBlock {
  return block.type === 'heading';
}

export function isParagraphBlock(block: Block): block is ParagraphBlock {
  return block.type === 'paragraph';
}


