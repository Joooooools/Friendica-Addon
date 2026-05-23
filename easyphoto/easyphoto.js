/**
 * EasyPhoto - Adds a simple image description editor below the post textarea for easier accessibility.
 * Author: Jools <https://friendica.de/profile/jools>
 * License: AGPL-3.0-or-later
 *
 * SPDX-FileCopyrightText: 2026 [Jools]
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
(function () {

    // Stable counter for unique input IDs - no Date.now(), no collision risk.
    let _epIdCounter = 0;

    // ReDoS protection: inputs above this size will not be parsed.
    // Friendica posts are typically < 10k characters; 100k is a generous safety buffer.
    const MAX_PARSE_LENGTH = 100000;

    // WeakMap for state storage per textarea - clean isolation,
    // no collisions with other addons, automatic garbage collection.
    const stateMap = new WeakMap();

    const findImages = (text) => {
        // Defensive: do not parse excessively long inputs (ReDoS protection, tempered greedy tokens).
        if (typeof text !== 'string' || text.length > MAX_PARSE_LENGTH) {
            return [];
        }

        const results = [];
        const counts = {};

        try {
            // Pattern 1: Complex (linked) [url=...][img=...]...[/img][/url]
            const pattern1 = /\[url=([^\]]*?)\]\[img=([^\]]*?)\]((?:(?!\[url=|\[img).)*?)\[\/img\]\[\/url\]/gi;
            let match;
            while ((match = pattern1.exec(text)) !== null) {
                const imgUrl = match[2];
                counts[imgUrl] = (counts[imgUrl] || 0) + 1;

                results.push({
                    full: match[0],
                    url: match[1],
                    img: imgUrl,
                    desc: match[3],
                    index: match.index,
                    length: match[0].length,
                    rank: counts[imgUrl],
                    type: 'complex'
                });
            }

            // Pattern 2: Simple [img]URL|Desc[/img]
            const pattern2 = /\[img\]((?:(?!\[img).)*?)\[\/img\]/gi;
            while ((match = pattern2.exec(text)) !== null) {
                const content = match[1];
                const pipeIndex = content.indexOf('|');
                const imgUrl = pipeIndex !== -1 ? content.substring(0, pipeIndex) : content;

                counts[imgUrl] = (counts[imgUrl] || 0) + 1;

                results.push({
                    full: match[0],
                    img: imgUrl,
                    desc: pipeIndex !== -1 ? content.substring(pipeIndex + 1) : "",
                    index: match.index,
                    length: match[0].length,
                    rank: counts[imgUrl],
                    type: 'simple'
                });
            }

            // Pattern 3: Alt-Simple [img=URL]Description[/img]
            const pattern3 = /\[img=([^\]]*?)\]((?:(?!\[img).)*?)\[\/img\]/gi;
            while ((match = pattern3.exec(text)) !== null) {
                const imgUrl = match[1];
                counts[imgUrl] = (counts[imgUrl] || 0) + 1;

                results.push({
                    full: match[0],
                    img: imgUrl,
                    desc: match[2],
                    index: match.index,
                    length: match[0].length,
                    rank: counts[imgUrl],
                    type: 'alt_simple'
                });
            }
        } catch (e) {
            // Safety net: return an empty result on unexpected regex errors
            // instead of blocking the UI.
            console.warn('EasyPhoto: parsing error', e);
            return [];
        }

        // Sort and filter overlaps (prevents double matches between Pattern 1 and 3).
        const sorted = results.sort((a, b) => a.index - b.index);
        const filtered = [];
        let lastEnd = -1;

        for (const item of sorted) {
            if (item.index >= lastEnd) {
                filtered.push(item);
                lastEnd = item.index + item.length;
            }
        }

        return filtered;
    };

    const getImages = (textarea) => {
        const state = stateMap.get(textarea);
        if (state && state.lastValue === textarea.value && state.lastImages) {
            return state.lastImages;
        }
        const images = findImages(textarea.value);
        if (state) {
            state.lastValue = textarea.value;
            state.lastImages = images;
        }
        return images;
    };

    const updateTextarea = (textarea, imgIdentity, newDesc, listContainer) => {
        // SECURITY: Remove square brackets to prevent BBCode injection.
        // Quotes remain - BBCode is not HTML, entities would appear literally.
        const sanitizedDesc = newDesc.replace(/[\[\]]/g, '');
        const currentText = textarea.value;
        const images = getImages(textarea);

        const target = images.find(img => img.img === imgIdentity.img && img.rank === imgIdentity.rank);
        if (!target) return;

        let newTag;
        if (target.type === 'complex') {
            newTag = `[url=${target.url}][img=${target.img}]${sanitizedDesc}[/img][/url]`;
        } else if (target.type === 'alt_simple') {
            newTag = `[img=${target.img}]${sanitizedDesc}[/img]`;
        } else {
            newTag = `[img]${target.img}|${sanitizedDesc}[/img]`;
        }

        const newContent = currentText.substring(0, target.index) +
            newTag +
            currentText.substring(target.index + target.length);

        if (currentText !== newContent) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;

            textarea.value = newContent;

            // Invalidate cache - MUST happen before renderList().
            const state = stateMap.get(textarea);
            if (state) {
                state.lastValue = null;
            }

            if (listContainer) {
                renderList(textarea, listContainer);
            }

            // Standard event for Friendica core (character counter, preview, etc.).
            textarea.dispatchEvent(new CustomEvent('input', {
                bubbles: true,
                detail: { source: 'easyphoto' }
            }));

            const diff = newTag.length - target.length;
            if (start > target.index) {
                textarea.setSelectionRange(start + diff, end + diff);
            } else {
                textarea.setSelectionRange(start, end);
            }
        }
    };

    // Allowed image extensions for thumbnail display.
    // Defense-in-depth in addition to origin check: prevents endpoints with
    // side effects from being loaded as <img src>.
    // SVG is deliberately NOT allowed: although it would not execute inside <img src>,
    // it is irrelevant as a thumbnail format here and avoids any discussion about inline scripts in SVGs.
    const IMAGE_EXTENSION_RE = /\.(jpe?g|png|gif|webp|avif|bmp)(\?.*)?$/i;

    const isLocal = (url) => {
        try {
            const currentOrigin = window.location.origin;
            const testUrl = new URL(url, currentOrigin);
            return testUrl.origin === currentOrigin && testUrl.protocol.startsWith('http');
        } catch (e) {
            return false;
        }
    };

    const isSafeLocalImage = (url) => {
        if (!isLocal(url)) return false;
        try {
            const parsed = new URL(url, window.location.origin);
            // Path must look like an image - or allow typical Friendica photo paths
            // (/photo/, /photos/, /proxy/) which often do not have an extension.
            const path = parsed.pathname;
            if (IMAGE_EXTENSION_RE.test(path)) return true;
            if (/^\/(photo|photos|proxy)\//i.test(path)) return true;
            return false;
        } catch (e) {
            return false;
        }
    };

    const renderList = (textarea, listContainer) => {
        const images = getImages(textarea);
        if (images.length === 0) {
            listContainer.style.display = 'none';
            listContainer.dataset.fingerprint = '';
            return;
        }

        listContainer.style.display = 'block';

        const newFingerprint = images.map(img => `${img.img}|${img.type}|${img.url || ''}`).join('##');
        const oldFingerprint = listContainer.dataset.fingerprint || '';

        const currentInputs = listContainer.querySelectorAll('.ep-input');
        const shouldRebuild = currentInputs.length !== images.length || newFingerprint !== oldFingerprint;

        if (!shouldRebuild) {
            images.forEach((img, i) => {
                if (document.activeElement !== currentInputs[i]) {
                    currentInputs[i].value = img.desc;
                }
            });
            return;
        }

        listContainer.dataset.fingerprint = newFingerprint;
        listContainer.innerHTML = '';

        const PRIVACY_PLACEHOLDER = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4ODgiIHN0cm9rZS13aWR0aD0iMSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cmVjdCB4PSIzIiB5PSIzIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHJ4PSIyIiByeT0iMiIvPjxjaXJjbGUgY3g9IjguNSIgY3k9IjguNSIgcj0iMS41Ii8+PHBhdGggZD0iTTIxIDE1bC01LTUtNCA0LTQtNC00IDQiLz48L3N2Zz4=';

        images.forEach((img, i) => {
            const row = document.createElement('div');
            row.className = 'ep-row';

            const thumbContainer = document.createElement('div');
            thumbContainer.className = 'ep-thumb-container';
            const thumb = document.createElement('img');
            thumb.className = 'ep-thumb';

            if (isSafeLocalImage(img.img)) {
                thumb.src = img.img;
            } else {
                thumb.src = PRIVACY_PLACEHOLDER;
                const privacyText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.privacy : 'External image (privacy protection)';
                thumb.title = privacyText;
                thumb.alt = privacyText;
            }

            thumbContainer.appendChild(thumb);

            const inputContainer = document.createElement('div');
            inputContainer.className = 'ep-input-container';

            // Stable counter instead of Date.now() - no collisions possible.
            const inputId = `ep-input-${++_epIdCounter}-${i}`;
            const label = document.createElement('label');
            label.htmlFor = inputId;
            const labelText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.image : 'Image';
            label.textContent = `${labelText} ${i + 1}`;

            const input = document.createElement('input');
            input.id = inputId;
            input.className = 'ep-input';
            input.type = 'text';
            input.value = img.desc;
            const placeholderText = (typeof easyphoto_l10n !== 'undefined') ? easyphoto_l10n.placeholder : 'Enter image description here...';
            input.placeholder = placeholderText;

            input.dataset.img = img.img;
            input.dataset.rank = img.rank;

            inputContainer.appendChild(label);
            inputContainer.appendChild(input);
            row.appendChild(thumbContainer);
            row.appendChild(inputContainer);
            listContainer.appendChild(row);
        });
    };

    const init = (addedNodes) => {
        const targetNodes = addedNodes || [document];

        targetNodes.forEach(root => {
            if (root.nodeType !== 1 && root.nodeType !== 9) return;
            const textareas = root.tagName === 'TEXTAREA' ? [root] : root.querySelectorAll('textarea');

            textareas.forEach(textarea => {
                if (textarea.classList.contains('ep-processed')) return;
                if (!textarea.isConnected || !textarea.parentNode) return;
                textarea.classList.add('ep-processed');

                const listContainer = document.createElement('div');
                listContainer.className = 'ep-list';
                listContainer.style.display = 'none';

                textarea.parentNode.insertBefore(listContainer, textarea.nextSibling);

                // State container for this textarea - clean in WeakMap instead of
                // directly on the DOM element.
                const state = {
                    lastValue: null,
                    lastImages: null,
                    lastSeenValue: textarea.value,
                    intervalId: null,
                    debounceTimer: null,
                    visibilityHandler: null,
                    cleanup: null
                };
                stateMap.set(textarea, state);

                const safeRender = () => {
                    if (!textarea.isConnected) {
                        if (state.cleanup) state.cleanup();
                        return;
                    }

                    // VISIBILITY: Optimization for performance and position:fixed dialogs.
                    if (document.hidden || textarea.getBoundingClientRect().width === 0) {
                        return;
                    }

                    if (textarea.value !== state.lastSeenValue) {
                        state.lastSeenValue = textarea.value;
                        renderList(textarea, listContainer);
                    }
                };

                // Debouncing on input event: prevents regex runs on every keystroke.
                const debouncedRender = (e) => {
                    if (e && e.detail && e.detail.source === 'easyphoto') return;
                    clearTimeout(state.debounceTimer);
                    state.debounceTimer = setTimeout(safeRender, 300);
                };

                // Polling tick: truly pause on tab switch instead of just running empty loops.
                const startPolling = () => {
                    if (state.intervalId === null) {
                        state.intervalId = setInterval(safeRender, 1500);
                    }
                };
                const stopPolling = () => {
                    if (state.intervalId !== null) {
                        clearInterval(state.intervalId);
                        state.intervalId = null;
                    }
                };

                state.visibilityHandler = () => {
                    if (document.hidden) {
                        stopPolling();
                    } else {
                        startPolling();
                        // Render once directly upon return if the value has changed.
                        safeRender();
                    }
                };
                document.addEventListener('visibilitychange', state.visibilityHandler);

                state.cleanup = () => {
                    stopPolling();
                    if (state.debounceTimer) clearTimeout(state.debounceTimer);
                    if (state.visibilityHandler) {
                        document.removeEventListener('visibilitychange', state.visibilityHandler);
                    }
                    if (listContainer.parentNode) listContainer.remove();
                    textarea.removeEventListener('input', debouncedRender);
                    textarea.classList.remove('ep-processed');
                    stateMap.delete(textarea);
                };

                textarea.addEventListener('input', debouncedRender);

                listContainer.addEventListener('input', (e) => {
                    if (e.target.classList.contains('ep-input')) {
                        const imgIdentity = {
                            img: e.target.dataset.img,
                            rank: parseInt(e.target.dataset.rank, 10)
                        };
                        updateTextarea(textarea, imgIdentity, e.target.value, listContainer);
                    }
                });

                const stopEvents = (e) => e.stopPropagation();
                listContainer.addEventListener('dragover', stopEvents);
                listContainer.addEventListener('drop', stopEvents);
                listContainer.addEventListener('paste', stopEvents);

                renderList(textarea, listContainer);

                // Only start polling if tab is visible - otherwise wait for visibilitychange.
                if (!document.hidden) {
                    startPolling();
                }
            });
        });
    };

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) {
                const relevantAdded = Array.from(mutation.addedNodes).filter(node => {
                    if (node.nodeType !== 1) return false;
                    return node.tagName === 'TEXTAREA' || node.querySelectorAll('textarea').length > 0;
                });
                if (relevantAdded.length > 0) init(relevantAdded);
            }
            if (mutation.removedNodes.length > 0) {
                mutation.removedNodes.forEach(node => {
                    if (node.nodeType !== 1) return;
                    const textareas = node.tagName === 'TEXTAREA' ? [node] : node.querySelectorAll('.ep-processed');
                    textareas.forEach(ta => {
                        const state = stateMap.get(ta);
                        if (state && state.cleanup) state.cleanup();
                    });
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
    document.addEventListener("postprocess_liveupdate", () => init());

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init());
    } else {
        init();
    }
})();
