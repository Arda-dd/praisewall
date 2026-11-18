import type { Feed, Labels } from './types';

const DEFAULTS: Labels = {
  read_more: 'Read more',
  show_less: 'Show less',
  leave: 'Leave a testimonial',
  empty: 'No testimonials yet.',
  stars: ':rating out of 5 stars',
  reviews: [':count review', ':count reviews'],
  previous: 'Previous',
  next: 'Next',
  close: 'Close',
  about: 'What people say',
};

export function labels(feed: Feed): Labels {
  const given = feed.labels ?? {};
  const merged = { ...DEFAULTS };

  for (const key of Object.keys(DEFAULTS) as (keyof Labels)[]) {
    const value = given[key];
    if (key === 'reviews') {
      if (Array.isArray(value) && value.length === 2) merged.reviews = [String(value[0]), String(value[1])];
    } else if (typeof value === 'string') {
      (merged[key] as string) = value;
    }
  }

  return merged;
}

export function fill(text: string, values: Record<string, string | number>): string {
  return text.replace(/:(\w+)/g, (match, key: string) => (key in values ? String(values[key]) : match));
}

export function plural(forms: [string, string], count: number, locale: string): string {
  const rule = new Intl.PluralRules(locale).select(count);
  return fill(rule === 'one' ? forms[0] : forms[1], { count });
}
