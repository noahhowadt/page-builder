/** Row shape for the components index table (Inertia may include more fields). */
export interface ComponentListItem {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

/** Props for the component builder editor screen. */
export interface ComponentForEditor {
  id: number;
  name: string;
}

/** Saved component row for the builder sidebar library. Structure is JSON-encoded. */
export interface LibraryComponent {
  id: number;
  name: string;
  structure: string;
}
