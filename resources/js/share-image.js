// Draws a testimonial as a social media image on a canvas.

const SIZES = {
    square: [1080, 1080],
    landscape: [1200, 627],
    story: [1080, 1920],
};

const SANS = 'ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
const SERIF = '"Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif';

function mix(hex, other, amount) {
    const parse = (value) => [1, 3, 5].map((i) => parseInt(value.slice(i, i + 2), 16));
    const [a, b] = [parse(hex), parse(other)];
    return `rgb(${a.map((channel, i) => Math.round(channel + (b[i] - channel) * amount)).join(',')})`;
}

/** Background, text colors and whether the text sits on a white card. */
function template(name, accent) {
    const themes = {
        aurora: { background: [accent, '#db2777'], card: true },
        sunset: { background: ['#f97316', '#db2777'], card: true },
        ocean: { background: ['#0ea5e9', '#4f46e5'], card: true },
        midnight: { background: ['#09090b', '#18181b'], glow: accent, card: false, fg: '#fafafa', muted: '#a1a1aa', star: '#fbbf24' },
        paper: { background: ['#faf7f2', '#f3ede3'], card: false, fg: '#1c1917', muted: '#78716c', serif: true },
        brand: { background: [accent, mix(accent, '#000000', 0.35)], card: false, fg: '#ffffff', muted: 'rgba(255,255,255,0.75)', star: '#ffffff', starOff: 'rgba(255,255,255,0.3)' },
    };
    return { fg: '#18181b', muted: '#71717a', star: '#f5a524', starOff: '#e4e4e7', accent, ...themes[name] };
}

function roundRect(ctx, x, y, width, height, radius) {
    ctx.beginPath();
    ctx.roundRect(x, y, width, height, radius);
}

function wrap(ctx, text, maxWidth) {
    const lines = [];
    for (const paragraph of text.split(/\n+/)) {
        let line = '';
        for (const word of paragraph.split(/\s+/).filter(Boolean)) {
            const test = line ? `${line} ${word}` : word;
            if (ctx.measureText(test).width > maxWidth && line) {
                lines.push(line);
                line = word;
            } else {
                line = test;
            }
        }
        if (line) lines.push(line);
    }
    return lines;
}

/** The largest font size at which the text fits the box. */
function fitText(ctx, text, width, height, family, max, min) {
    for (let size = max; size >= min; size -= 2) {
        ctx.font = `500 ${size}px ${family}`;
        const lines = wrap(ctx, text, width);
        if (lines.length * size * 1.4 <= height) return { size, lines };
    }
    ctx.font = `500 ${min}px ${family}`;
    const lines = wrap(ctx, text, width);
    const fit = Math.max(1, Math.floor(height / (min * 1.4)));
    if (lines.length > fit) {
        lines.length = fit;
        lines[fit - 1] = lines[fit - 1].replace(/\s*\S*$/, '') + '…';
    }
    return { size: min, lines };
}

function star(ctx, x, y, size, color) {
    ctx.save();
    ctx.translate(x, y);
    ctx.scale(size / 20, size / 20);
    ctx.fillStyle = color;
    ctx.fill(new Path2D('M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.8L10 14.9l-5.25 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z'));
    ctx.restore();
}

const images = new Map();
function loadImage(url) {
    if (!url) return Promise.resolve(null);
    if (!images.has(url)) {
        images.set(
            url,
            new Promise((resolve) => {
                const image = new Image();
                image.onload = () => resolve(image);
                image.onerror = () => resolve(null);
                image.src = url;
            }),
        );
    }
    return images.get(url);
}

export async function draw(canvas, data, choices) {
    const [width, height] = SIZES[choices.size];
    const t = template(choices.template, data.accent);
    const ctx = canvas.getContext('2d');
    canvas.width = width;
    canvas.height = height;
    const unit = Math.min(width, height) / 100;
    const [avatar, logo] = await Promise.all([choices.avatar ? loadImage(data.avatar) : null, choices.brand ? loadImage(data.logo) : null]);

    // Background
    const gradient = ctx.createLinearGradient(0, 0, width, height);
    gradient.addColorStop(0, t.background[0]);
    gradient.addColorStop(1, t.background[1]);
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, width, height);

    if (t.glow) {
        const glow = ctx.createRadialGradient(width * 0.85, height * 0.1, 0, width * 0.85, height * 0.1, Math.max(width, height) * 0.7);
        glow.addColorStop(0, mix(t.glow, '#09090b', 0.35));
        glow.addColorStop(1, 'rgba(9,9,11,0)');
        ctx.fillStyle = glow;
        ctx.fillRect(0, 0, width, height);
    }

    // Content box: a white card, or the whole canvas with a margin.
    const margin = unit * (choices.size === 'landscape' ? 6 : 8);
    let box = { x: margin, y: margin, w: width - margin * 2, h: height - margin * 2 };
    const brandHeight = choices.brand ? unit * 9 : 0;
    box.h -= brandHeight;

    if (t.card) {
        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,0.18)';
        ctx.shadowBlur = unit * 6;
        ctx.shadowOffsetY = unit * 1.5;
        ctx.fillStyle = '#ffffff';
        roundRect(ctx, box.x, box.y, box.w, box.h, unit * 4);
        ctx.fill();
        ctx.restore();
        const padding = unit * 7;
        box = { x: box.x + padding, y: box.y + padding, w: box.w - padding * 2, h: box.h - padding * 2 };
    }

    // Measure first, so the quote, stars and text can be centered above the author.
    const avatarSize = unit * 11;
    const authorY = box.y + box.h - avatarSize;
    const quoteHeight = unit * 7;
    const starSize = unit * 4.4;
    const starsHeight = choices.rating && data.rating ? starSize + unit * 3.5 : 0;
    const top = box.y;
    const available = authorY - unit * 6 - top - quoteHeight - unit * 4 - starsHeight;
    const family = t.serif ? SERIF : SANS;
    const maxSize = Math.round(unit * (choices.size === 'story' ? 7.6 : 5.6));
    const { size, lines } = fitText(ctx, data.body, box.w, available, family, maxSize, Math.round(unit * 3));
    const blockHeight = quoteHeight + unit * 4 + starsHeight + lines.length * size * 1.4;
    let y = top + Math.max(0, (authorY - unit * 6 - top - blockHeight) / 2);

    // Quote mark, the same shape as in the widget
    ctx.save();
    ctx.translate(box.x - unit * 0.8, y);
    ctx.scale(quoteHeight / 14, quoteHeight / 14);
    ctx.translate(-4.5, -5);
    ctx.fillStyle = t.card ? data.accent : t.fg === '#ffffff' ? 'rgba(255,255,255,0.4)' : (t.glow ?? data.accent);
    ctx.fill(new Path2D('M9.5 5C6.4 6.4 4.5 9 4.5 12.6V19h6.2v-6.2H7.6c0-2.2 1.2-3.8 3.1-4.7L9.5 5zm10 0c-3.1 1.4-5 4-5 7.6V19h6.2v-6.2h-3.1c0-2.2 1.2-3.8 3.1-4.7L19.5 5z'));
    ctx.restore();
    y += quoteHeight + unit * 4;
    ctx.textBaseline = 'top';

    // Stars
    if (starsHeight) {
        for (let i = 0; i < 5; i++) star(ctx, box.x + i * starSize * 1.15, y, starSize, i < data.rating ? t.star : t.starOff);
        y += starsHeight;
    }

    // The testimonial
    ctx.fillStyle = t.fg;
    ctx.font = `500 ${size}px ${family}`;
    lines.forEach((line, i) => ctx.fillText(line, box.x, y + i * size * 1.4));

    // Author
    let textX = box.x;
    if (choices.avatar) {
        ctx.save();
        ctx.beginPath();
        ctx.arc(box.x + avatarSize / 2, authorY + avatarSize / 2, avatarSize / 2, 0, Math.PI * 2);
        ctx.clip();
        if (avatar) {
            const scale = Math.max(avatarSize / avatar.width, avatarSize / avatar.height);
            ctx.drawImage(avatar, box.x + (avatarSize - avatar.width * scale) / 2, authorY + (avatarSize - avatar.height * scale) / 2, avatar.width * scale, avatar.height * scale);
        } else {
            ctx.fillStyle = t.card || t.fg !== '#ffffff' ? mix(data.accent, '#ffffff', 0.82) : 'rgba(255,255,255,0.2)';
            ctx.fillRect(box.x, authorY, avatarSize, avatarSize);
            ctx.fillStyle = t.card || t.fg !== '#ffffff' ? data.accent : '#ffffff';
            ctx.font = `700 ${avatarSize * 0.38}px ${SANS}`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(data.initials, box.x + avatarSize / 2, authorY + avatarSize / 2 + 1);
            ctx.textAlign = 'left';
            ctx.textBaseline = 'top';
        }
        ctx.restore();
        textX += avatarSize + unit * 3.5;
    }

    ctx.fillStyle = t.fg;
    ctx.font = `700 ${unit * 3.6}px ${SANS}`;
    ctx.fillText(data.name, textX, authorY + (data.title ? unit * 1.2 : unit * 3.6), box.x + box.w - textX);
    if (data.title) {
        ctx.fillStyle = t.muted;
        ctx.font = `400 ${unit * 3}px ${SANS}`;
        ctx.fillText(data.title, textX, authorY + unit * 6, box.x + box.w - textX);
    }

    // Brand line under the content
    if (choices.brand) {
        const brandY = height - margin - brandHeight + unit * 3;
        const onCard = t.card;
        let x = margin + (onCard ? 0 : 0);
        if (logo) {
            const logoSize = unit * 5;
            ctx.save();
            roundRect(ctx, x, brandY, logoSize, logoSize, unit * 1.2);
            ctx.clip();
            ctx.drawImage(logo, x, brandY, logoSize, logoSize);
            ctx.restore();
            x += logoSize + unit * 2;
        }
        ctx.fillStyle = onCard ? '#ffffff' : t.fg;
        ctx.globalAlpha = onCard ? 0.95 : 0.8;
        ctx.font = `650 ${unit * 3.2}px ${SANS}`;
        ctx.fillText(data.wall, x, brandY + unit * 0.8);
        ctx.globalAlpha = 1;
    }
}

export function init(root) {
    const data = JSON.parse(root.dataset.testimonial);
    const canvas = root.querySelector('[data-canvas]');
    const value = (name) => root.querySelector(`[name="${name}"]:checked`)?.value;
    const checked = (name) => root.querySelector(`[name="${name}"]`).checked;
    const choices = () => ({
        template: value('template'),
        size: value('size'),
        rating: checked('rating'),
        avatar: checked('avatar'),
        brand: checked('brand'),
    });

    root.querySelectorAll('[data-template-swatch]').forEach((swatch) => {
        const t = template(swatch.dataset.templateSwatch, data.accent);
        swatch.style.background = `linear-gradient(135deg, ${t.background[0]}, ${t.background[1]})`;
    });

    const update = () => draw(canvas, data, choices());
    root.addEventListener('change', update);
    update();

    root.querySelector('[data-download]').addEventListener('click', () => {
        canvas.toBlob((blob) => {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${data.filename}-${choices().template}-${choices().size}.png`;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 1000);
        }, 'image/png');
    });
}
