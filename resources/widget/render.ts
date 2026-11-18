import { h, safeUrl, svg } from './dom';
import type { Feed, Options, Testimonial } from './types';

const STAR = 'M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z';
const QUOTE = 'M9.5 5C6.4 6.4 4.5 9 4.5 12.6V19h6.2v-6.2H7.6c0-2.2 1.2-3.8 3.1-4.7L9.5 5zm10 0c-3.1 1.4-5 4-5 7.6V19h6.2v-6.2h-3.1c0-2.2 1.2-3.8 3.1-4.7L19.5 5z';
const LONG_TEXT = 280;

function stars(rating: number): HTMLElement {
  const wrapper = h('div', { class: 'stars', role: 'img', 'aria-label': `${rating} out of 5 stars` });
  for (let i = 1; i <= 5; i++) {
    wrapper.append(svg('0 0 20 20', STAR, i <= rating ? 'star on' : 'star'));
  }
  return wrapper;
}

/** A stable pastel hue per name, so initials avatars don't all look the same. */
function hue(name: string): number {
  let hash = 0;
  for (const char of name) hash = (hash * 31 + char.codePointAt(0)!) % 360;
  return hash;
}

function avatar(testimonial: Testimonial): HTMLElement {
  const { author } = testimonial;
  const src = safeUrl(author.avatar_url);

  if (src) {
    return h('img', { class: 'avatar', src, alt: '', loading: 'lazy', width: '40', height: '40' });
  }

  const initials = h('span', { class: 'avatar initials', 'aria-hidden': 'true' }, author.initials || '?');
  initials.style.setProperty('--hue', String(hue(author.name)));
  return initials;
}

function body(text: string): HTMLElement {
  const paragraph = h('p', { class: 'body' }, text);

  if (text.length <= LONG_TEXT) return paragraph;

  paragraph.classList.add('clamped');
  const toggle = h('button', { type: 'button', class: 'more', 'aria-expanded': 'false' }, 'Read more');
  toggle.addEventListener('click', () => {
    const expanded = paragraph.classList.toggle('clamped') === false;
    toggle.textContent = expanded ? 'Show less' : 'Read more';
    toggle.setAttribute('aria-expanded', String(expanded));
  });

  return h('div', {}, paragraph, toggle);
}

export function card(testimonial: Testimonial): HTMLElement {
  const { author } = testimonial;
  const url = safeUrl(author.url);
  const name = url
    ? h('a', { class: 'name', href: url, target: '_blank', rel: 'nofollow noopener noreferrer' }, author.name)
    : h('span', { class: 'name' }, author.name);

  return h(
    'figure',
    { class: testimonial.featured ? 'card featured' : 'card' },
    svg('0 0 24 24', QUOTE, 'quote'),
    testimonial.rating ? stars(testimonial.rating) : null,
    h('blockquote', {}, body(testimonial.body)),
    h(
      'figcaption',
      {},
      avatar(testimonial),
      h('span', { class: 'who' }, name, author.title ? h('span', { class: 'title' }, author.title) : null),
    ),
  );
}

function carousel(cards: HTMLElement[]): HTMLElement {
  const track = h('div', { class: 'track' }, ...cards);

  // A second, hidden copy makes the marquee loop without a jump.
  if (cards.length > 1) {
    for (const original of cards) {
      const copy = original.cloneNode(true) as HTMLElement;
      copy.setAttribute('aria-hidden', 'true');
      copy.setAttribute('inert', '');
      track.append(copy);
    }
    track.style.setProperty('--duration', `${Math.max(20, cards.length * 7)}s`);
  }

  return h('div', { class: 'carousel', tabindex: '0', 'aria-label': 'Testimonials, scrolling' }, track);
}

function footer(feed: Feed, options: Options): HTMLElement | null {
  const collect = safeUrl(feed.collect_url);
  const parts: HTMLElement[] = [];

  if (options.cta && collect) {
    parts.push(h('a', { class: 'cta', href: collect, target: '_blank', rel: 'noopener' }, 'Leave a testimonial'));
  }

  if (options.branding) {
    parts.push(
      h(
        'a',
        { class: 'branding', href: 'https://github.com/Arda-dd/praisewall', target: '_blank', rel: 'noopener' },
        'Powered by Praisewall',
      ),
    );
  }

  return parts.length ? h('div', { class: 'footer' }, ...parts) : null;
}

export function render(feed: Feed, options: Options): HTMLElement {
  const testimonials = options.limit ? feed.testimonials.slice(0, options.limit) : feed.testimonials;
  const cards = testimonials.map(card);

  const content =
    cards.length === 0
      ? h('p', { class: 'empty' }, 'No testimonials yet.')
      : options.layout === 'carousel'
        ? carousel(cards)
        : h('div', { class: options.layout }, ...cards);

  const root = h('section', { class: 'root', 'aria-label': `What people say about ${feed.name}` }, content, footer(feed, options));
  root.style.setProperty('--accent', /^#[0-9a-f]{6}$/i.test(feed.accent_color) ? feed.accent_color : '#6d28d9');

  return root;
}
