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

export type BlockType = 'heading' | 'container' | 'paragraph' | 'main' | 'link';
export interface Block {
  id: string;
  type: BlockType;
  config: any;
  children?: Array<Node>;
}

export type Node = Block | TextNode;
