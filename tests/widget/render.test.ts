import { afterEach, describe, expect, it, vi } from 'vitest';
import { fill, plural } from '../../resources/widget/labels';
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

function options(overrides: Partial<Options> = {}): Options {
  return {
    ...readOptions(document.createElement('div')),
    ...overrides,
  };
}

afterEach(() => {
  vi.useRealTimers();
  vi.restoreAllMocks();
  document.body.replaceChildren();
  sessionStorage.clear();
});

describe('cards', () => {
  it('renders text as text, never as html', () => {
    const root = render(feed([testimonial({ body: '<img src=x onerror="alert(1)">' }, { name: '<b>Eve</b>' })]), options());

    expect(root.querySelector('img[src="x"]')).toBeNull();
    expect(root.querySelector('b')).toBeNull();
    expect(root.querySelector('.body')!.textContent).toBe('<img src=x onerror="alert(1)">');
    expect(root.querySelector('.name')!.textContent).toBe('<b>Eve</b>');
  });

  it('only links http and https urls', () => {
    const unsafe = render(feed([testimonial({}, { url: 'javascript:alert(1)', avatar_url: 'javascript:alert(2)' })]), options());
    expect(unsafe.querySelector('a.name')).toBeNull();
    expect(unsafe.querySelector('img.avatar')).toBeNull();
    expect(unsafe.querySelector('.initials')!.textContent).toBe('MK');

    const safe = render(feed([testimonial({}, { url: 'https://example.com/maya' })]), options());
    const link = safe.querySelector<HTMLAnchorElement>('a.name')!;
    expect(link.href).toBe('https://example.com/maya');
    expect(link.rel).toContain('nofollow');
  });

  it('shows the rating as filled stars', () => {
    const root = render(feed([testimonial({ rating: 4 })]), options());

    expect(root.querySelectorAll('.star.on')).toHaveLength(4);
    expect(root.querySelector('.stars')!.getAttribute('aria-label')).toBe('4 out of 5 stars');
    expect(render(feed([testimonial({ rating: null })]), options()).querySelector('.stars')).toBeNull();
  });

  it('marks featured testimonials', () => {
    const root = render(feed([testimonial({ featured: true }), testimonial({ id: 2 })]), options());
    expect(root.querySelectorAll('.card.featured')).toHaveLength(1);
  });

  it('respects the limit', () => {
    const many = Array.from({ length: 8 }, (_, i) => testimonial({ id: i }));
    expect(render(feed(many), options({ limit: 3 })).querySelectorAll('.card')).toHaveLength(3);
  });

  it('collapses long testimonials behind a toggle', () => {
    const root = render(feed([testimonial({ body: 'word '.repeat(100) })]), options());
    const body = root.querySelector('.body')!;
    const toggle = root.querySelector<HTMLButtonElement>('.more')!;

    expect(body.classList.contains('clamped')).toBe(true);
    toggle.click();
    expect(body.classList.contains('clamped')).toBe(false);
    expect(toggle.getAttribute('aria-expanded')).toBe('true');
  });

  it('hides the parts asked for', () => {
    const root = render(feed([testimonial()]), options({ hide: ['rating', 'avatar', 'title', 'quote'] }));

    expect(root.querySelector('.stars')).toBeNull();
    expect(root.querySelector('.avatar')).toBeNull();
    expect(root.querySelector('.title')).toBeNull();
    expect(root.querySelector('.quote')).toBeNull();
    expect(root.querySelector('.name')!.textContent).toBe('Maya Kaya');
  });

  it('shows the date in the wall language when asked', () => {
    expect(render(feed([testimonial()]), options()).querySelector('time')).toBeNull();

    const root = render(feed([testimonial()], { locale: 'de' }), options({ date: true }));
    const time = root.querySelector('time')!;
    expect(time.getAttribute('datetime')).toBe('2026-11-18');
    expect(time.textContent).toBe(new Intl.DateTimeFormat('de', { month: 'short', year: 'numeric' }).format(new Date('2026-11-18T00:00:00')));
  });
});

describe('layouts', () => {
  it('loops the carousel with a hidden copy of each card', () => {
    const root = render(feed([testimonial({ id: 1 }), testimonial({ id: 2 })]), options({ layout: 'carousel' }));

    expect(root.querySelectorAll('.track .card')).toHaveLength(4);
    expect(root.querySelectorAll('.track .card[aria-hidden="true"]')).toHaveLength(2);
  });

  it('uses a grid with the chosen number of columns', () => {
    const root = render(feed([testimonial()]), options({ layout: 'grid', columns: 2 }));

    expect(root.querySelector('.grid')).not.toBeNull();
    expect(root.style.getPropertyValue('--columns')).toBe('2');
  });

  it('moves the slider with the arrows, the dots and the keyboard', () => {
    const root = render(feed([testimonial({ id: 1, body: 'First' }), testimonial({ id: 2, body: 'Second' }), testimonial({ id: 3, body: 'Third' })]), options({ layout: 'slider' }));
    const active = () => root.querySelector('.slide.active .body')!.textContent;

    expect(root.querySelectorAll('.slide')).toHaveLength(3);
    expect(active()).toBe('First');

    root.querySelector<HTMLButtonElement>('.arrow.next')!.click();
    expect(active()).toBe('Second');

    root.querySelector<HTMLButtonElement>('.arrow:not(.next)')!.click();
    root.querySelector<HTMLButtonElement>('.arrow:not(.next)')!.click();
    expect(active()).toBe('Third');

    root.querySelectorAll<HTMLButtonElement>('.dot')[0]!.click();
    expect(active()).toBe('First');

    root.querySelector('.slider')!.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight' }));
    expect(active()).toBe('Second');
    expect(root.querySelector('.slide.active')!.hasAttribute('aria-hidden')).toBe(false);
  });

  it('advances the slider on its own', () => {
    vi.useFakeTimers();
    const root = render(feed([testimonial({ id: 1, body: 'First' }), testimonial({ id: 2, body: 'Second' })]), options({ layout: 'slider', interval: 4 }));
    document.body.append(root);

    vi.advanceTimersByTime(4000);
    expect(root.querySelector('.slide.active .body')!.textContent).toBe('Second');
  });

  it('shows a popup that can be closed for the rest of the visit', () => {
    const root = render(feed([testimonial({ body: 'Popup text' })]), options({ layout: 'popup', position: 'bottom-right' }));
    const popup = root.querySelector('.popup')!;

    expect(popup.classList.contains('bottom-right')).toBe(true);
    expect(popup.querySelector('.body')!.textContent).toBe('Popup text');
    expect(root.querySelector('.footer')).toBeNull();

    root.querySelector<HTMLButtonElement>('.close')!.click();
    expect(root.querySelector('.popup')).toBeNull();
    expect(render(feed([testimonial()]), options({ layout: 'popup' })).querySelector('.popup')).toBeNull();
  });

  it('shows a rating badge that links to the wall', () => {
    const root = render(
      feed([testimonial(), testimonial({ id: 2 })], { summary: { count: 23, rated: 20, average: 4.8 } }),
      options({ layout: 'badge' }),
    );
    const badge = root.querySelector<HTMLAnchorElement>('a.badge')!;

    expect(badge.href).toBe('https://example.com/w/orbit');
    expect(badge.querySelector('b')!.textContent).toBe('4.8');
    expect(badge.querySelector('.badge-count')!.textContent).toBe('23 reviews');
    expect(badge.querySelectorAll('.faces .avatar')).toHaveLength(2);
    expect(badge.querySelectorAll('.star.on')).toHaveLength(5);
  });
});

describe('wall settings', () => {
  it('uses the labels sent with the feed', () => {
    const turkish = feed([testimonial({ rating: 4, body: 'word '.repeat(100) })], {
      locale: 'tr',
      labels: { read_more: 'Devamını oku', stars: '5 üzerinden :rating yıldız', reviews: [':count yorum', ':count yorum'] },
      summary: { count: 1, rated: 1, average: 4 },
    });

    const wall = render(turkish, options());
    expect(wall.querySelector('.more')!.textContent).toBe('Devamını oku');
    expect(wall.querySelector('.stars')!.getAttribute('aria-label')).toBe('5 üzerinden 4 yıldız');
    expect(render(turkish, options({ layout: 'badge' })).querySelector('.badge-count')!.textContent).toBe('1 yorum');
  });

  it('overrides the accent and ignores invalid colors', () => {
    expect(render(feed([testimonial()]), options({ accent: '#0ea5e9' })).style.getPropertyValue('--accent')).toBe('#0ea5e9');
    expect(render(feed([testimonial()], { accent_color: 'red;background:url(x)' }), options()).style.getPropertyValue('--accent')).toBe('#6d28d9');
  });

  it('shows the collect link and branding unless turned off', () => {
    const on = render(feed([testimonial()]), options());
    expect(on.querySelector<HTMLAnchorElement>('.cta')!.href).toBe('https://example.com/c/orbit');
    expect(on.querySelector('.branding')).not.toBeNull();

    expect(render(feed([testimonial()]), options({ cta: false, branding: false })).querySelector('.footer')).toBeNull();
    expect(render(feed([testimonial()], { collect_url: null }), options()).querySelector('.cta')).toBeNull();
  });

  it('has an empty state, except for the floating layouts', () => {
    expect(render(feed([]), options()).querySelector('.empty')!.textContent).toBe('No testimonials yet.');
    expect(render(feed([]), options({ layout: 'popup' })).childElementCount).toBe(0);
  });
});

describe('readOptions', () => {
  it('has safe defaults', () => {
    expect(readOptions(document.createElement('div'))).toEqual({
      layout: 'masonry',
      style: null,
      theme: 'auto',
      limit: null,
      columns: null,
      radius: 'medium',
      inheritFont: false,
      accent: null,
      hide: [],
      date: false,
      cta: true,
      branding: true,
      position: 'bottom-left',
      interval: 6,
    });
  });

  it('reads every data attribute', () => {
    const element = document.createElement('div');
    Object.assign(element.dataset, {
      layout: 'slider',
      style: 'glass',
      theme: 'dark',
      limit: '6',
      columns: '2',
      radius: 'large',
      font: 'inherit',
      accent: '#0EA5E9',
      hide: 'rating, avatar,nope,rating',
      date: 'true',
      cta: 'false',
      branding: 'no',
      position: 'bottom-right',
      interval: '9',
    });

    expect(readOptions(element)).toEqual({
      layout: 'slider',
      style: 'glass',
      theme: 'dark',
      limit: 6,
      columns: 2,
      radius: 'large',
      inheritFont: true,
      accent: '#0EA5E9',
      hide: ['rating', 'avatar'],
      date: true,
      cta: false,
      branding: false,
      position: 'bottom-right',
      interval: 9,
    });
  });

  it('falls back on unknown values', () => {
    const element = document.createElement('div');
    Object.assign(element.dataset, { layout: 'spiral', style: 'neon', theme: 'pink', limit: '-2', columns: '40', accent: 'red', interval: '1' });

    expect(readOptions(element)).toMatchObject({ layout: 'masonry', style: null, theme: 'auto', limit: null, columns: null, accent: null, interval: 6 });
  });
});

describe('labels', () => {
  it('fills placeholders and picks plural forms', () => {
    expect(fill(':rating of 5', { rating: 4 })).toBe('4 of 5');
    expect(fill('keeps :unknown', {})).toBe('keeps :unknown');
    expect(plural([':count review', ':count reviews'], 1, 'en')).toBe('1 review');
    expect(plural([':count review', ':count reviews'], 3, 'en')).toBe('3 reviews');
  });
});
