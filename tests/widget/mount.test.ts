import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount } from '../../resources/widget/widget';
import type { Feed } from '../../resources/widget/types';

const feed: Feed = {
  name: 'Orbit',
  accent_color: '#0ea5e9',
  logo_url: null,
  wall_url: 'https://example.com/w/orbit',
  collect_url: null,
  testimonials: [
    {
      id: 1,
      body: 'Lovely.',
      rating: 5,
      featured: false,
      date: null,
      author: { name: 'Ada', title: null, url: null, avatar_url: null, initials: 'A' },
    },
  ],
};

afterEach(() => {
  vi.unstubAllGlobals();
  vi.restoreAllMocks();
  document.body.replaceChildren();
});

function element(url: string): HTMLElement {
  const div = document.createElement('div');
  div.dataset.praisewall = url;
  document.body.append(div);
  return div;
}

describe('mount', () => {
  it('fetches the feed and renders into a shadow root', async () => {
    const fetchMock = vi.fn().mockResolvedValue({ ok: true, json: async () => feed });
    vi.stubGlobal('fetch', fetchMock);

    const target = element('https://example.com/api/walls/orbit');
    target.dataset.theme = 'dark';
    await mount(target);

    expect(fetchMock).toHaveBeenCalledWith('https://example.com/api/walls/orbit', expect.anything());
    expect(target.getAttribute('data-pw-theme')).toBe('dark');
    expect(target.getAttribute('data-pw-style')).toBe('classic');
    expect(target.shadowRoot!.querySelectorAll('.card')).toHaveLength(1);
    expect(target.shadowRoot!.querySelector('style')).not.toBeNull();
  });

  it('uses a feed passed in directly without fetching', async () => {
    const fetchMock = vi.fn();
    vi.stubGlobal('fetch', fetchMock);

    const target = element('');
    await mount(target, feed);

    expect(fetchMock).not.toHaveBeenCalled();
    expect(target.shadowRoot!.querySelectorAll('.card')).toHaveLength(1);
  });

  it("uses the wall's style unless the embed picks one", async () => {
    const own = element('');
    await mount(own, { ...feed, style: 'elegant' });
    expect(own.getAttribute('data-pw-style')).toBe('elegant');

    const picked = element('');
    picked.dataset.style = 'bold';
    await mount(picked, { ...feed, style: 'elegant' });
    expect(picked.getAttribute('data-pw-style')).toBe('bold');
  });

  it('renders nothing when the feed fails to load', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: false, status: 404 }));
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

    const target = element('https://example.com/api/walls/missing');
    await mount(target);

    expect(target.shadowRoot!.childNodes).toHaveLength(0);
    expect(warn).toHaveBeenCalled();
  });

  it('mounts an element only once', async () => {
    const fetchMock = vi.fn().mockResolvedValue({ ok: true, json: async () => feed });
    vi.stubGlobal('fetch', fetchMock);

    const target = element('https://example.com/api/walls/orbit');
    await Promise.all([mount(target), mount(target)]);

    expect(fetchMock).toHaveBeenCalledTimes(1);
  });
});
