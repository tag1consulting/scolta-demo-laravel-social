/**
 * Rich post cards for MyStream's Scolta search.
 *
 * Registers two Scolta renderers: a result renderer that paints the author's
 * avatar, handle, star count and hashtags alongside the post, and a suggestion
 * renderer that puts the same avatar on the search-as-you-type rows. Everything
 * they need comes from the search index — the avatar URL, handle, star count
 * and hashtag labels ride along in the fragment's meta map, put there by
 * Post::toSearchableContent() — so neither a card nor a suggestion costs a
 * per-result server call.
 *
 * The card is laid out like the post cards the feed already draws: round
 * avatar on the left, author line, then the post. Someone scanning search
 * results and someone scanning their feed are looking at the same object, and
 * they should not have to learn it twice.
 *
 * Load order matters. scolta.js defines window.Scolta when it executes and
 * calls Scolta.init() on DOMContentLoaded. This file is pushed onto the
 * layout's script stack, which renders after the search component's own
 * <script defer>, and defer preserves document order while still running
 * everything before DOMContentLoaded — exactly the window the renderers have
 * to register in.
 */
(function (global) {
  'use strict';

  if (!global.Scolta || typeof global.Scolta.setResultRenderer !== 'function') {
    // A bundle without the render seam is not something to work around here.
    console.warn('[mystream] Scolta.setResultRenderer unavailable; leaving the built-in card in place.');
    return;
  }

  var ENTITIES = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  };

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/[&<>"']/g, function (c) { return ENTITIES[c]; });
  }

  /**
   * Escapes a URL for an attribute and neutralizes non-http(s) schemes.
   *
   * The avatar URL is a remote DiceBear address written by the seeder, but it
   * arrives here as raw index data, so it gets the same treatment Scolta gives
   * the result href rather than an assumption about who wrote it.
   */
  function safeImageUrl(value) {
    var url = String(value === null || value === undefined ? '' : value).trim();
    if (url === '') {
      return '';
    }
    if (/^[a-z][a-z0-9+.-]*:/i.test(url) && !/^https?:/i.test(url)) {
      return '';
    }
    return escapeHtml(url);
  }

  /**
   * Marks a card whose avatar failed to load.
   *
   * The circle stays rather than collapsing. These are remote DiceBear URLs,
   * so a blocked or offline load is a real path, not a theoretical one, and
   * every card keeping its grey circle still reads as a list of posts — where
   * cards that individually gained and lost their gutter would not.
   */
  global.mystreamScoltaAvatarFailed = function (img) {
    var card = img.closest ? img.closest('.mystream-result') : null;
    if (card) {
      card.classList.add('mystream-result--avatar-failed');
    }
    if (img.parentNode) {
      img.parentNode.removeChild(img);
    }
  };

  global.mystreamScoltaSaytAvatarFailed = function (img) {
    if (img.parentNode) {
      img.parentNode.removeChild(img);
    }
  };

  /**
   * How many hashtag badges a card paints. Mirrors the indexer's own cap,
   * which is what actually bounds the string; this is the client-side belt.
   */
  var BADGE_LIMIT = 2;

  /**
   * Renders a post's hashtag badges.
   *
   * data.meta.badges is raw index data: a JSON-encoded array of hashtag names,
   * already capped by Post::toSearchableContent(). JSON and not a delimited
   * string because a hashtag is user-entered text, so there is no separator a
   * future one provably cannot contain.
   *
   * The names are stored without the sigil, so the "#" is written here. It is
   * literal text in the markup, not part of the escaped value.
   *
   * Anything that does not parse into an array counts as no badges. Only 1202
   * of the 12541 indexed posts carry any, so most cards show none — the same
   * graceful path a missing avatar takes, not a broken card.
   */
  function badges(encoded) {
    if (!encoded) {
      return '';
    }
    var labels;
    try {
      labels = JSON.parse(encoded);
    } catch (e) {
      return '';
    }
    if (!Array.isArray(labels)) {
      return '';
    }
    var out = '';
    for (var i = 0; i < labels.length && i < BADGE_LIMIT; i++) {
      var label = String(labels[i] === null || labels[i] === undefined ? '' : labels[i]).trim();
      if (label !== '') {
        out += '<span class="mystream-result__badge">#' + escapeHtml(label) + '</span>';
      }
    }
    return out;
  }

  /**
   * The star count, written the way the rest of the site writes counts.
   *
   * Present only above zero: 8046 of the 12541 posts have at least one star,
   * and printing "0 stars" on the other 4495 would spend a slot saying
   * nothing. The value is a digit string from the index, so it is checked as
   * one before being used to pick a plural.
   */
  function starsHtml(value) {
    var raw = String(value === null || value === undefined ? '' : value).trim();
    if (!/^\d+$/.test(raw) || raw === '0') {
      return '';
    }
    var n = parseInt(raw, 10);
    return '<span class="mystream-result__stars">' + escapeHtml(raw)
      + (n === 1 ? ' star' : ' stars') + '</span>';
  }

  /**
   * Renders one result.
   *
   * Escaping: every ctx value used here ends in Html, Attr or Text, or is
   * safeUrl, so Scolta has already escaped it exactly as its own card would.
   * Everything read from data.meta is raw index data and is escaped here.
   * ctx.query and ctx.highlightTerms are raw and never reach the markup.
   *
   * A post whose author has no avatar gets the same card with the empty circle
   * rather than Scolta's built-in one. Every one of the 104 authors has one, so
   * that path is a guard rather than a common case.
   */
  global.Scolta.setResultRenderer(function (data, ctx) {
    var meta = (data && data.meta) || {};
    var avatarUrl = safeImageUrl(meta.image);
    var handle = String(meta.handle || '').trim();
    var badgeHtml = badges(meta.badges);
    var stars = starsHtml(meta.stars);

    var parts = (handle !== '' ? '<span class="mystream-result__handle">' + escapeHtml(handle) + '</span>' : '')
      + stars + badgeHtml;
    var metaRow = parts === '' ? '' : '<div class="mystream-result__meta">' + parts + '</div>';

    // Decorative: the handle is written out beside it and the title link goes
    // to the same post, so the avatar stays out of the tab order and out of
    // the accessible tree. It carries no alt for the same reason — it would
    // announce the author's name a second time.
    var avatar = '<a class="mystream-result__avatar" href="' + ctx.safeUrl + '" target="_blank" rel="noopener"'
      + ' tabindex="-1" aria-hidden="true">'
      + (avatarUrl === '' ? ''
        : '<img src="' + avatarUrl + '" alt="" loading="lazy" decoding="async"'
          + ' onerror="mystreamScoltaAvatarFailed(this)">')
      + '</a>';

    // target/rel match the built-in card: within one result list, no card may
    // open differently from its neighbour.
    return '<div class="scolta-result-card mystream-result">'
      + avatar
      + '<div class="mystream-result__body">'
      + '<a class="scolta-result-title mystream-result__title" href="' + ctx.safeUrl + '"'
      + ' target="_blank" rel="noopener" title="' + ctx.titleAttr + '">' + ctx.titleHtml + '</a>'
      + metaRow
      + '<div class="scolta-result-excerpt mystream-result__excerpt">' + ctx.excerptHtml + '</div>'
      + '</div>'
      + '</div>';
  });

  // Behind its own guard rather than the file-level one: this seam landed
  // after setResultRenderer, so a bundle old enough to lack it still gets the
  // rich cards above, and the dropdown degrades to the themed but avatarless
  // rows instead of throwing.
  if (typeof global.Scolta.setSuggestionRenderer !== 'function') {
    return;
  }

  /**
   * Renders one search-as-you-type suggestion row.
   *
   * Returns the row's INNER markup only. The option element around it is the
   * bundle's, and it is what carries the combobox contract — role="option",
   * the stable id the input's aria-activedescendant points at, aria-selected,
   * the data-scolta-sayt-index the keyboard and click handlers dispatch on,
   * and the href in navigate mode. None of that is restated here, because a
   * renderer cannot break by omission what it never writes.
   *
   * Escaping: ctx.titleHtml and ctx.excerptHtml arrive pre-escaped, escaped
   * exactly as the built-in row escapes them. suggestion.meta.* is raw index
   * data and is escaped here. ctx.query is raw and never reaches the markup.
   *
   * A recent search is handed back to the built-in row by returning null: it
   * has no fragment, no avatar and nothing to add, and the built-in row is
   * already the themed glyph treatment this dropdown wants for history.
   */
  global.Scolta.setSuggestionRenderer(function (suggestion, ctx) {
    if (!suggestion || suggestion.type !== 'title') {
      return null;
    }

    var avatarUrl = safeImageUrl((suggestion.meta || {}).image);

    // Decorative and aria-hidden, for the same reason as on the card. The
    // circle's space is kept even when there is no image, so every row's title
    // starts on the same line.
    var avatar = '<span class="mystream-sayt__avatar' + (avatarUrl === '' ? ' mystream-sayt__avatar--empty' : '')
      + '" aria-hidden="true">'
      + (avatarUrl === '' ? ''
        : '<img src="' + avatarUrl + '" alt="" loading="lazy" decoding="async"'
          + ' onerror="mystreamScoltaSaytAvatarFailed(this)">')
      + '</span>';

    return '<span class="mystream-sayt">'
      + avatar
      // Both classes on purpose. The scolta-* one carries the look the theme
      // already gives a suggestion's title and excerpt, so a title row and a
      // recent-search row stay typographically identical; the mystream-* one
      // adds only the layout this row needs. Two classes at the same
      // specificity, resolved by source order, rather than a nested selector.
      + '<span class="mystream-sayt__text">'
      + '<span class="scolta-sayt-title mystream-sayt__title">' + ctx.titleHtml + '</span>'
      + (ctx.excerptHtml
        ? '<span class="scolta-sayt-excerpt mystream-sayt__excerpt">' + ctx.excerptHtml + '</span>'
        : '')
      + '</span>'
      + '</span>';
  });

})(window);
