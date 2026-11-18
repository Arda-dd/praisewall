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

export interface Feed {
  name: string;
  accent_color: string;
  logo_url: string | null;
  wall_url: string;
  collect_url: string | null;
  testimonials: Testimonial[];
}

export type Layout = 'masonry' | 'carousel' | 'list';
export type Theme = 'light' | 'dark' | 'auto';

export interface Options {
  layout: Layout;
  theme: Theme;
  limit: number | null;
  cta: boolean;
  branding: boolean;
}
