import { describe, expect, it } from 'vitest';
import { readOptions } from '../../resources/widget/options';
import { render } from '../../resources/widget/render';
import type { Author, Feed, Options, Testimonial } from '../../resources/widget/types';

function testimonial(overrides: Partial<Omit<Testimonial, 'author'>> = {}, author: Partial<Author> = {}): Testimonial {
  return {
    id: 1,
    body: 'Great product, saved us hours every week.',
    rating: 5,
    featured: false,
    date: '2026-11-18',
    ...overrides,
    author: {
      name: 'Maya Kaya',
      title: 'CTO at Orbit',
      url: null,
      avatar_url: null,
      initials: 'MK',
      ...author,
    },
  };
}

function feed(testimonials: Testimonial[], overrides: Partial<Feed> = {}): Feed {
  return {
    name: 'Orbit',
    accent_color: '#6d28d9',
    logo_url: null,
    wall_url: 'https://example.com/w/orbit',
    collect_url: 'https://example.com/c/orbit',
    testimonials,
    ...overrides,
  };
}

const options: Options = { layout: 'masonry', theme: 'auto', limit: null, cta: true, branding: true };

describe('render', () => {
  it('renders text as text, never as html', () => {
    const root = render(feed([testimonial({ body: '<img src=x onerror="alert(1)">' }, { name: '<b>Eve</b>' })]), options);

    expect(root.querySelector('img[src="x"]')).toBeNull();
    expect(root.querySelector('b')).toBeNull();
    expect(root.querySelector('.body')!.textContent).toBe('<img src=x onerror="alert(1)">');
    expect(root.querySelector('.name')!.textContent).toBe('<b>Eve</b>');
  });

  it('only links http and https urls', () => {
    const unsafe = render(feed([testimonial({}, { url: 'javascript:alert(1)', avatar_url: 'javascript:alert(2)' })]), options);
    expect(unsafe.querySelector('a.name')).toBeNull();
    expect(unsafe.querySelector('img.avatar')).toBeNull();
    expect(unsafe.querySelector('.initials')!.textContent).toBe('MK');

    const safe = render(feed([testimonial({}, { url: 'https://example.com/maya' })]), options);
    const link = safe.querySelector<HTMLAnchorElement>('a.name')!;
    expect(link.href).toBe('https://example.com/maya');
    expect(link.rel).toContain('nofollow');
  });

  it('shows the rating as filled stars', () => {
    const root = render(feed([testimonial({ rating: 4 })]), options);

    expect(root.querySelectorAll('.star.on')).toHaveLength(4);
    expect(root.querySelector('.stars')!.getAttribute('aria-label')).toBe('4 out of 5 stars');
    expect(render(feed([testimonial({ rating: null })]), options).querySelector('.stars')).toBeNull();
  });

  it('marks featured testimonials', () => {
    const root = render(feed([testimonial({ featured: true }), testimonial({ id: 2 })]), options);
    expect(root.querySelectorAll('.card.featured')).toHaveLength(1);
  });

  it('respects the limit', () => {
    const many = Array.from({ length: 8 }, (_, i) => testimonial({ id: i }));
    expect(render(feed(many), { ...options, limit: 3 }).querySelectorAll('.card')).toHaveLength(3);
  });

  it('collapses long testimonials behind a toggle', () => {
    const root = render(feed([testimonial({ body: 'word '.repeat(100) })]), options);
    const body = root.querySelector('.body')!;
    const toggle = root.querySelector<HTMLButtonElement>('.more')!;

    expect(body.classList.contains('clamped')).toBe(true);
    toggle.click();
    expect(body.classList.contains('clamped')).toBe(false);
    expect(toggle.getAttribute('aria-expanded')).toBe('true');
  });

  it('loops the carousel with a hidden copy of each card', () => {
    const root = render(feed([testimonial({ id: 1 }), testimonial({ id: 2 })]), { ...options, layout: 'carousel' });

    expect(root.querySelectorAll('.track .card')).toHaveLength(4);
    expect(root.querySelectorAll('.track .card[aria-hidden="true"]')).toHaveLength(2);
  });

  it('shows the collect link and branding unless turned off', () => {
    const on = render(feed([testimonial()]), options);
    expect(on.querySelector<HTMLAnchorElement>('.cta')!.href).toBe('https://example.com/c/orbit');
    expect(on.querySelector('.branding')).not.toBeNull();

    const off = render(feed([testimonial()]), { ...options, cta: false, branding: false });
    expect(off.querySelector('.footer')).toBeNull();

    const closed = render(feed([testimonial()], { collect_url: null }), options);
    expect(closed.querySelector('.cta')).toBeNull();
  });

  it('falls back to the default accent for an invalid color', () => {
    const root = render(feed([testimonial()], { accent_color: 'red;background:url(x)' }), options);
    expect(root.style.getPropertyValue('--accent')).toBe('#6d28d9');
  });

  it('has an empty state', () => {
    expect(render(feed([]), options).querySelector('.empty')!.textContent).toBe('No testimonials yet.');
  });
});

describe('readOptions', () => {
  it('reads data attributes with safe defaults', () => {
    const element = document.createElement('div');
    expect(readOptions(element)).toEqual({ layout: 'masonry', theme: 'auto', limit: null, cta: true, branding: true });

    Object.assign(element.dataset, { layout: 'carousel', theme: 'dark', limit: '6', cta: 'false', branding: 'no' });
    expect(readOptions(element)).toEqual({ layout: 'carousel', theme: 'dark', limit: 6, cta: false, branding: false });

    Object.assign(element.dataset, { layout: 'spiral', theme: 'neon', limit: '-2' });
    expect(readOptions(element)).toMatchObject({ layout: 'masonry', theme: 'auto', limit: null });
  });
});
