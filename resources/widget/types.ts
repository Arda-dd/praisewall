export interface Author {
  name: string;
  title: string | null;
  url: string | null;
  avatar_url: string | null;
  initials: string;
}

export interface Testimonial {
  id: number;
  body: string;
  rating: number | null;
  featured: boolean;
  author: Author;
  date: string | null;
}

export interface Summary {
  count: number;
  rated: number;
  average: number | null;
}

export interface Labels {
  read_more: string;
  show_less: string;
  leave: string;
  empty: string;
  stars: string;
  reviews: [string, string];
  previous: string;
  next: string;
  close: string;
  about: string;
}

export interface Feed {
  name: string;
  accent_color: string;
  style?: Style;
  locale?: string;
  logo_url: string | null;
  wall_url: string | null;
  collect_url: string | null;
  summary?: Summary;
  labels?: Partial<Labels>;
  testimonials: Testimonial[];
}

export const LAYOUTS = ['masonry', 'grid', 'carousel', 'slider', 'list', 'popup', 'badge'] as const;
export const STYLES = ['classic', 'minimal', 'bubble', 'glass', 'bold', 'elegant'] as const;
export const THEMES = ['light', 'dark', 'auto'] as const;
export const RADII = ['none', 'small', 'medium', 'large'] as const;
export const PARTS = ['rating', 'avatar', 'title', 'quote'] as const;
export const POSITIONS = ['bottom-left', 'bottom-right'] as const;

export type Layout = (typeof LAYOUTS)[number];
export type Style = (typeof STYLES)[number];
export type Theme = (typeof THEMES)[number];
export type Radius = (typeof RADII)[number];
export type Part = (typeof PARTS)[number];
export type Position = (typeof POSITIONS)[number];

export interface Options {
  layout: Layout;
  /** null means the wall's own style. */
  style: Style | null;
  theme: Theme;
  limit: number | null;
  columns: number | null;
  radius: Radius;
  /** Use the host page's font instead of the system font. */
  inheritFont: boolean;
  accent: string | null;
  hide: Part[];
  date: boolean;
  cta: boolean;
  branding: boolean;
  position: Position;
  /** Seconds per testimonial in the slider and the popup. */
  interval: number;
}
