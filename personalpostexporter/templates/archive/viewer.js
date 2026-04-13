document.addEventListener("DOMContentLoaded", () => {
    const navList = document.getElementById("nav-list");
    const postList = document.getElementById("post-list");
    const searchInput = document.getElementById("searchInput");

    const manifest = window.FRIENDICA_MANIFEST || [];
    const searchIndex = window.FRIENDICA_SEARCH_INDEX || [];
    let currentData = []; // Full list (Month posts or Search results)
    let currentPage = 1;
    const pageSize = 20;
    let currentMode = "welcome"; // welcome, month, search
    let currentMonthData = null; // Store m object for month view
    
    function escapeHtml(s) {
        if (!s) return "";
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function getL10n(key, ...args) {
        const lang = window.FRIENDICA_L10N || {};
        let str = lang[key] || key;
        args.forEach((val, i) => {
            str = str.replace("%d", val);
        });
        return str;
    }

    window.archiveSearch = (tag) => {
        if (!tag) return;
        const q = tag.toLowerCase();
        searchInput.value = `#${tag}`;
        
        const filtered = searchIndex.filter(p => {
            const h = p.s.toLowerCase();
            const t = p.t ? p.t.toLowerCase() : "";
            return h.includes(`#${q}`) || h.includes(`tag=${q}`) || t.includes(q);
        });

        currentPage = 1;
        currentMode = "search";
        currentData = filtered;
        renderSearchResults(filtered, `${getL10n("res_for")} #${tag}`);
        window.scrollTo(0, 0);
    };

    function renderNav() {
        navList.innerHTML = "";
        
        // Group by year
        const years = {};
        manifest.forEach(m => {
            const year = m.key.split('-')[0];
            if (!years[year]) years[year] = [];
            years[year].push(m);
        });

        Object.keys(years).sort((a,b) => b-a).forEach((year, idx) => {
            const yearDiv = document.createElement("div");
            yearDiv.className = "year-group";
            if (idx === 0) yearDiv.classList.add("open");

            const yearHead = document.createElement("button");
            yearHead.className = "year-header";
            yearHead.textContent = year;
            yearHead.onclick = () => yearDiv.classList.toggle("open");
            yearDiv.appendChild(yearHead);

            const mList = document.createElement("div");
            mList.className = "month-list";
            
            years[year].forEach(m => {
                const btn = document.createElement("button");
                btn.className = "nav-item";
                const mLabel = m.label.split(' ')[0];
                btn.innerHTML = `<span>${escapeHtml(mLabel)}</span> <small>(${parseInt(m.count)})</small>`;
                btn.onclick = (e) => loadMonth(m, e.currentTarget);
                mList.appendChild(btn);
            });
            
            yearDiv.appendChild(mList);
            navList.appendChild(yearDiv);
        });
    }

    async function loadMonth(m, target, jumpToId = null) {
        document.querySelectorAll(".nav-item").forEach(i => i.classList.remove("active"));
        if (target) {
            target.classList.add("active");
        } else {
            // Find by label if no target passed
            const btn = Array.from(document.querySelectorAll(".nav-item")).find(b => b.textContent.includes(m.label));
            if (btn) btn.classList.add("active");
        }

        postList.innerHTML = `<div class="welcome">Loading ${escapeHtml(m.label)}...</div>`;

        if (!window.FRIENDICA_EXPORT_DATA[m.key]) {
            await new Promise((resolve) => {
                const script = document.createElement("script");
                script.src = `data/${m.key}.js`;
                script.onload = resolve;
                script.onerror = resolve; // Continue even if load fails to avoid hanging UI
                document.body.appendChild(script);
            });
        }

        if (!window.FRIENDICA_EXPORT_DATA[m.key]) {
            postList.innerHTML = `<div class="welcome">Error: Could not load data for ${escapeHtml(m.label)}. File might be missing or corrupt.</div>`;
            return;
        }

        currentMode = "month";
        currentMonthData = m;
        currentData = window.FRIENDICA_EXPORT_DATA[m.key] || [];
        currentPage = 1;

        if (jumpToId && currentData.length > 0) {
            const idx = currentData.findIndex(p => String(p.id) === String(jumpToId));
            if (idx !== -1) {
                currentPage = Math.floor(idx / pageSize) + 1;
            }
        }

        renderPosts(currentData);

        if (jumpToId) {
            setTimeout(() => {
                const el = document.getElementById(`post-${jumpToId}`);
                if (el) {
                    el.scrollIntoView({ behavior: "smooth" });
                    el.style.outline = "2px solid var(--accent)";
                    setTimeout(() => el.style.outline = "none", 2000);
                }
            }, 300);
        }
    }

    function renderPosts(posts, message = "") {
        postList.innerHTML = "";
        if (message) {
            const msgDiv = document.createElement("div");
            msgDiv.className = "post-meta";
            msgDiv.style.textAlign = "center";
            msgDiv.style.marginBottom = "40px";
            msgDiv.innerHTML = `${message} — <a href="javascript:void(0)" onclick="location.reload()">Clear</a>`;
            postList.appendChild(msgDiv);
        }
        if (!posts.length) {
            const noMatch = document.createElement("div");
            noMatch.className = "welcome";
            noMatch.textContent = "No matches found.";
            postList.appendChild(noMatch);
            return;
        }

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageItems = posts.slice(start, end);

        pageItems.forEach(post => {
            const art = document.createElement("article");
            art.className = "post";
            art.id = `post-${post.id}`;
            art.innerHTML = `
                <div class="meta-box">
                    <div class="meta-box-header">${getL10n("meta_title")}</div>
                    <div class="meta-box-content">
                        <div class="meta-field">
                            <span class="meta-field-label">${getL10n("meta_published")}</span>
                            <span class="meta-field-value">${escapeHtml(post.date)} ${post.private ? "🔒" : ""}</span>
                        </div>
                        <div class="meta-field">
                            <span class="meta-field-label">${getL10n("meta_source")}</span>
                            <a href="${escapeHtml(post.plink)}" class="meta-field-value" target="_blank" rel="noreferrer noopener nofollow">
                                ${getL10n("meta_view_on")}
                            </a>
                        </div>
                        ${post.gallery_url ? `
                        <div class="meta-field">
                            <span class="meta-field-label">MEDIA</span>
                            <a href="${escapeHtml(post.gallery_url)}" class="meta-field-value" target="_blank" rel="noreferrer noopener nofollow">
                                ${escapeHtml(post.gallery_label)}
                            </a>
                        </div>` : ""}
                    </div>
                </div>
                ${post.title ? `<h2>${post.title}</h2>` : ""}
                <div class="post-body">${post.html}</div>
            `;
            postList.appendChild(art);
        });

        renderPagination(posts.length);
        window.scrollTo(0, 0);
    }

    function renderPagination(totalItems) {
        if (totalItems <= pageSize) return;
        
        const totalPages = Math.ceil(totalItems / pageSize);
        const nav = document.createElement("div");
        nav.className = "pagination";
        
        const btnPrev = document.createElement("button");
        btnPrev.className = "page-btn";
        btnPrev.textContent = getL10n("prev");
        btnPrev.disabled = (currentPage === 1);
        btnPrev.onclick = () => { currentPage--; refreshView(); };

        const info = document.createElement("span");
        info.className = "page-info";
        info.textContent = getL10n("page_info", currentPage, totalPages);

        const btnNext = document.createElement("button");
        btnNext.className = "page-btn";
        btnNext.textContent = getL10n("next");
        btnNext.disabled = (currentPage === totalPages);
        btnNext.onclick = () => { currentPage++; refreshView(); };

        nav.appendChild(btnPrev);
        nav.appendChild(info);
        nav.appendChild(btnNext);
        postList.appendChild(nav);
    }

    function refreshView() {
        if (currentMode === "month") {
            renderPosts(currentData);
        } else if (currentMode === "search") {
            renderSearchResults(currentData, `${getL10n("res_for")} ...`);
        }
    }

    function renderSearchResults(results, message) {
        postList.innerHTML = "";
        const msgDiv = document.createElement("div");
        msgDiv.className = "search-info";
        msgDiv.innerHTML = `
            <span class="search-info-text">${message} <strong>(${results.length})</strong></span>
            <button onclick="location.reload()" class="clear-search-btn">${getL10n("clear_search")}</button>
        `;
        postList.appendChild(msgDiv);

        if (!results.length) {
            const noMsg = document.createElement("div");
            noMsg.className = "welcome";
            noMsg.textContent = "No messages match your search.";
            postList.appendChild(noMsg);
            return;
        }

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageItems = results.slice(start, end);

        pageItems.forEach(res => {
            const div = document.createElement("div");
            div.className = "search-result-item";
            div.innerHTML = `
                <div class="post-meta">${escapeHtml(res.d)}</div>
                <a href="javascript:void(0)" onclick="jumpToSearchResult('${String(res.m).replace(/'/g, "\\'")}', '${String(res.id).replace(/'/g, "\\'")}')" class="search-result-link">
                    ${res.t ? `<h3>${res.t}</h3>` : ""}
                    <div class="search-result-snippet">${res.s.substring(0, 180)}...</div>
                </a>
            `;
            postList.appendChild(div);
        });
        renderPagination(results.length);
        window.scrollTo(0, 0);
    }

    window.jumpToSearchResult = (monthKey, id) => {
        const m = manifest.find(item => item.key === monthKey);
        if (m) loadMonth(m, null, id);
    };

    searchInput.oninput = (e) => {
        const query = e.target.value.toLowerCase();
        if (!query) {
            currentMode = "welcome";
            postList.innerHTML = `<div class="welcome"><h3>Search everything...</h3><p>Type to search all years and months.</p></div>`;
            return;
        }
        
        const filtered = searchIndex.filter(p => {
            const h = p.s.toLowerCase();
            const t = p.t ? p.t.toLowerCase() : "";
            return h.includes(query) || t.includes(query);
        });

        currentPage = 1;
        currentMode = "search";
        currentData = filtered;
        renderSearchResults(filtered, `${getL10n("res_for")} "${query}"`);
    };

    // Handle delegated clicks on internal tags (hashtags)
    document.addEventListener("click", (e) => {
        const target = e.target.closest(".internal-tag");
        if (target && target.dataset.tag) {
            e.preventDefault();
            window.archiveSearch(target.dataset.tag);
        }
    });

    renderNav();
});
