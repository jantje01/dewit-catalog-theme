(function () {
	const toggle = document.querySelector('.menu-toggle');
	const nav = document.querySelector('.main-navigation');

	if (toggle && document.getElementById('catalog-sidebar')) {
		return;
	}

	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			const isOpen = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', String(isOpen));
		});
	}
}());

(function () {
	const toggle = document.querySelector('.dewit-mobile-menu-toggle');
	const menu = document.querySelector('#dewit-category-bar');

	if (!toggle || !menu) {
		return;
	}

	const closeMenu = function () {
		document.body.classList.remove('dewit-mobile-menu-open');
		toggle.setAttribute('aria-expanded', 'false');
	};

	toggle.addEventListener('click', function () {
		const isOpen = document.body.classList.toggle('dewit-mobile-menu-open');
		toggle.setAttribute('aria-expanded', String(isOpen));
	});

	menu.addEventListener('click', function (event) {
		if (event.target.closest('a')) {
			closeMenu();
		}
	});

	document.addEventListener('click', function (event) {
		if (!event.target.closest('.dewit-mobile-menu-toggle, #dewit-category-bar')) {
			closeMenu();
		}
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' === event.key) {
			closeMenu();
		}
	});
}());

(function () {
	const trigger = document.querySelector('.dewit-mega-menu__trigger');
	const item = document.querySelector('.dewit-mega-menu__products');
	const panel = document.querySelector('#dewit-mega-menu-panel');

	if (!trigger || !item || !panel) {
		return;
	}

	const closeMenu = function () {
		item.classList.remove('is-open');
		trigger.setAttribute('aria-expanded', 'false');
		panel.hidden = true;
	};

	trigger.addEventListener('click', function () {
		const isOpen = item.classList.toggle('is-open');
		trigger.setAttribute('aria-expanded', String(isOpen));
		panel.hidden = !isOpen;
	});

	document.addEventListener('click', function (event) {
		if (!item.contains(event.target)) {
			closeMenu();
		}
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' === event.key) {
			closeMenu();
			trigger.focus();
		}
	});
}());

(function () {
	const items = document.querySelectorAll('.dewit-category-bar__item.has-children');

	if (!items.length) {
		return;
	}

	const closeItems = function () {
		items.forEach(function (item) {
			item.classList.remove('is-open');
			const button = item.querySelector('.dewit-category-bar__trigger');
			if (button) {
				button.setAttribute('aria-expanded', 'false');
			}
		});
	};

	items.forEach(function (item) {
		const button = item.querySelector('.dewit-category-bar__trigger');

		if (!button) {
			return;
		}

		button.addEventListener('click', function (event) {
			event.preventDefault();
			const isOpen = item.classList.contains('is-open');
			closeItems();
			item.classList.toggle('is-open', !isOpen);
			button.setAttribute('aria-expanded', String(!isOpen));
		});
	});

	document.addEventListener('click', function (event) {
		if (!event.target.closest('.dewit-category-bar')) {
			closeItems();
		}
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' === event.key) {
			closeItems();
		}
	});
}());

(function () {
	let searchRequestController = null;
	let searchDebounceTimer = null;
	const productCardViewStorageKey = 'dewitProductCardViewV4';

	function getThemeConfig() {
		return window.dewitTheme || (typeof dewitTheme !== 'undefined' ? dewitTheme : {});
	}

	function normalizeDisplayText(value) {
		const normalized = String(value || '').replace(/&(\d{2,6});/g, '&#$1;');
		const parser = document.createElement('textarea');
		parser.innerHTML = normalized;

		return parser.value;
	}

	function initPullRefreshGuard() {
		const isMobileViewport = window.matchMedia && window.matchMedia('(max-width: 766px)').matches;
		const productPage = document.querySelector('.dewit-product-page');
		const rootScroller = document.scrollingElement || document.documentElement;
		const scrollContainers = productPage ? [productPage] : [rootScroller];

		if (!isMobileViewport || !scrollContainers.length) {
			return;
		}

		scrollContainers.forEach(function (scrollContainer) {
			if (!scrollContainer || scrollContainer.dataset.dewitPullGuardReady === 'true') {
				return;
			}

			let startY = 0;

			scrollContainer.dataset.dewitPullGuardReady = 'true';
			scrollContainer.addEventListener('touchstart', function (event) {
				if (!event.touches || !event.touches.length) {
					return;
				}

				startY = event.touches[0].clientY;
			}, { passive: true });

			scrollContainer.addEventListener('touchmove', function (event) {
				if (!event.touches || !event.touches.length) {
					return;
				}

				const currentY = event.touches[0].clientY;
				const isPullingDown = currentY > startY;

				if (isPullingDown && scrollContainer.scrollTop <= 0) {
					event.preventDefault();
				}
			}, { passive: false });
		});
	}

	function initFixedHeaderWheelScroll() {
		const isNarrowViewport = window.matchMedia && window.matchMedia('(max-width: 766px)').matches;
		const header = document.querySelector('.site-header');

		if (!isNarrowViewport || !header || header.dataset.dewitWheelScrollReady === 'true') {
			return;
		}

		header.dataset.dewitWheelScrollReady = 'true';
		header.addEventListener('wheel', function (event) {
			if (document.body.classList.contains('dewit-mobile-filter-open') || (!event.deltaY && !event.deltaX)) {
				return;
			}

			window.scrollBy({
				left: event.deltaX,
				top: event.deltaY,
				behavior: 'auto'
			});
			event.preventDefault();
		}, { passive: false });
	}

	function getProductCardViewMode() {
		const defaultMode = window.matchMedia && window.matchMedia('(min-width: 767px)').matches ? 'horizontal' : 'grid';

		try {
			const storedMode = window.localStorage.getItem(productCardViewStorageKey);

			if (storedMode === 'horizontal' || storedMode === 'grid' || storedMode === 'table') {
				return storedMode;
			}

			return defaultMode;
		} catch (error) {
			return defaultMode;
		}
	}

	function replayGroupedProductCardAnimation() {
		const cards = document.querySelectorAll('.dewit-grouped-product-card');

		document.body.classList.add('dewit-animate-grouped-products');

		cards.forEach(function (card, index) {
			card.style.animation = 'none';
			card.style.setProperty('--dewit-card-index', String(Math.min(index, 24)));
		});

		if (cards.length) {
			cards[0].offsetHeight;
		}

		cards.forEach(function (card) {
			card.style.animation = '';
		});

		window.setTimeout(function () {
			document.body.classList.remove('dewit-animate-grouped-products');
		}, 720);
	}

	function setProductCardViewMode(mode, replayCards) {
		const nextMode = mode === 'horizontal' || mode === 'table' ? mode : 'grid';
		const currentMode = document.body.classList.contains('dewit-table-product-cards')
			? 'table'
			: (document.body.classList.contains('dewit-horizontal-product-cards') ? 'horizontal' : 'grid');

		document.body.classList.toggle('dewit-horizontal-product-cards', nextMode === 'horizontal');
		document.body.classList.toggle('dewit-table-product-cards', nextMode === 'table');
		document.documentElement.classList.toggle('dewit-horizontal-product-cards', nextMode === 'horizontal');
		document.documentElement.classList.toggle('dewit-table-product-cards', nextMode === 'table');
		document.querySelectorAll('.dewit-product-view-switch__button').forEach(function (button) {
			const isActive = button.getAttribute('data-view') === nextMode;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-pressed', String(isActive));
		});

		try {
			window.localStorage.setItem(productCardViewStorageKey, nextMode);
		} catch (error) {
			// localStorage can be disabled in some browser privacy modes.
		}

		if (replayCards && currentMode !== nextMode) {
			window.requestAnimationFrame(replayGroupedProductCardAnimation);
		}
	}

	function getActiveParentCategorySlugForLabel() {
		const url = new URL(window.location.href);
		const config = getThemeConfig();

		return url.searchParams.get('dewit_parent_cat') || config.defaultParentCategory || '';
	}

	function getActiveParentCategoryLabel() {
		const activeSlug = getActiveParentCategorySlugForLabel();
		const categories = window.dewitProductCategories || [];
		const match = categories.find(function (category) {
			return category && category.slug === activeSlug;
		});
		const activeTrigger = document.querySelector('.dewit-category-trigger[aria-pressed="true"] .dewit-category-label');

		if (match && match.name) {
			return normalizeDisplayText(match.name);
		}

		if (activeTrigger && activeTrigger.textContent.trim()) {
			return normalizeDisplayText(activeTrigger.textContent.trim());
		}

		return 'Alle producten';
	}

	function routeSidebarLogoToHome() {
		const config = getThemeConfig();
		const homeUrl = config.homeUrl || window.location.origin + '/';
		const sidebar = document.getElementById('catalog-sidebar');

		if (!sidebar) {
			return;
		}

		sidebar.querySelectorAll('.custom-logo-link, .dewit-sidebar-logo-link').forEach(function (link) {
			link.href = homeUrl;
			link.setAttribute('aria-label', 'Terug naar hoofdcategorieën');
		});

		sidebar.querySelectorAll('.custom-logo, .dewit-catalog-logo').forEach(function (logo) {
			if (logo.closest('a')) {
				logo.closest('a').href = homeUrl;
				return;
			}

			const link = document.createElement('a');
			link.className = 'dewit-sidebar-logo-link';
			link.href = homeUrl;
			link.setAttribute('aria-label', 'Terug naar hoofdcategorieën');
			logo.parentNode.insertBefore(link, logo);
			link.appendChild(logo);
		});
	}

	function normalizeGroupedContainerAccessibility(container) {
		if (!container) {
			return;
		}

		container.removeAttribute('role');
		container.removeAttribute('aria-live');
		container.removeAttribute('aria-label');
	}

	function updateProductViewSwitchLabel() {
		const label = document.querySelector('.dewit-product-view-switch__label');

		if (label) {
			label.textContent = getActiveParentCategoryLabel();
		}
	}

	function injectProductViewSwitch(content, grid) {
		if (content.querySelector('.dewit-product-view-switch')) {
			updateProductViewSwitchLabel();
			setProductCardViewMode(getProductCardViewMode());
			return;
		}

		const switcher = document.createElement('div');
		const label = document.createElement('span');
		const controls = document.createElement('span');
		const gridButton = document.createElement('button');
		const horizontalButton = document.createElement('button');
		const tableButton = document.createElement('button');

		switcher.className = 'dewit-product-view-switch';
		switcher.setAttribute('aria-label', 'Productweergave');

		label.className = 'dewit-product-view-switch__label';
		label.textContent = getActiveParentCategoryLabel();

		controls.className = 'dewit-product-view-switch__controls';

		gridButton.className = 'dewit-product-view-switch__button';
		gridButton.type = 'button';
		gridButton.setAttribute('data-view', 'grid');
		gridButton.setAttribute('aria-label', 'Gridweergave');
		gridButton.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="3" rx="1"></rect><rect width="7" height="7" x="3" y="14" rx="1"></rect><rect width="7" height="7" x="14" y="14" rx="1"></rect></svg>';

		horizontalButton.className = 'dewit-product-view-switch__button';
		horizontalButton.type = 'button';
		horizontalButton.setAttribute('data-view', 'horizontal');
		horizontalButton.setAttribute('aria-label', 'Horizontale productcards');
		horizontalButton.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="5" x="3" y="4" rx="1"></rect><rect width="18" height="5" x="3" y="15" rx="1"></rect></svg>';

		tableButton.className = 'dewit-product-view-switch__button';
		tableButton.type = 'button';
		tableButton.setAttribute('data-view', 'table');
		tableButton.setAttribute('aria-label', 'Tabelweergave');
		tableButton.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M3 12h18"></path><path d="M3 18h18"></path><path d="M8 6v12"></path></svg>';

		[gridButton, horizontalButton, tableButton].forEach(function (button) {
			button.addEventListener('click', function () {
				setProductCardViewMode(button.getAttribute('data-view'), true);
			});
			controls.appendChild(button);
		});

		switcher.appendChild(label);
		switcher.appendChild(controls);

		const toolbar = content.querySelector('.dewit-shop-toolbar');

		if (toolbar) {
			toolbar.appendChild(switcher);
		} else {
			content.insertBefore(switcher, grid);
		}

		setProductCardViewMode(getProductCardViewMode());
	}

	function clearLiveSearchResults(results) {
		results.classList.remove('is-visible', 'is-loading');
		results.innerHTML = '';
	}

	function renderLiveSearchResults(results, items, searchTerm) {
		results.classList.remove('is-loading');
		results.innerHTML = '';

		if (!items.length) {
			const empty = document.createElement('div');
			empty.className = 'dewit-shop-search-results__empty';
			empty.textContent = 'Geen directe resultaten gevonden';
			results.appendChild(empty);
			results.classList.add('is-visible');
			return;
		}

		items.forEach(function (item) {
			const link = document.createElement('a');
			const image = document.createElement('span');
			const content = document.createElement('span');
			const title = document.createElement('span');
			const meta = document.createElement('span');
			const metaParts = [
				{ label: item.sku, type: 'sku' },
				{ label: item.categories, type: 'category' },
			].filter(function (part) {
				return part.label;
			});

			link.className = 'dewit-shop-search-result';
			link.href = item.url;

			image.className = 'dewit-shop-search-result__image';

			if (item.image) {
				const img = document.createElement('img');
				img.alt = '';
				img.loading = 'lazy';
				img.src = item.image;
				image.appendChild(img);
			}

			content.className = 'dewit-shop-search-result__content';
			title.className = 'dewit-shop-search-result__title';
			title.textContent = normalizeDisplayText(item.title || searchTerm);
			meta.className = 'dewit-shop-search-result__meta';

			metaParts.forEach(function (part) {
				const itemElement = document.createElement('span');
				itemElement.className = 'dewit-shop-search-result__meta-item is-' + part.type;
				itemElement.textContent = normalizeDisplayText(part.label);
				meta.appendChild(itemElement);
			});

			content.appendChild(title);

			if (metaParts.length) {
				content.appendChild(meta);
			}

			link.appendChild(image);
			link.appendChild(content);
			results.appendChild(link);
		});

		results.classList.add('is-visible');
	}

	function updateLiveSearch(input, results) {
		const searchTerm = input.value.trim();
		const config = getThemeConfig();

		window.clearTimeout(searchDebounceTimer);

		if (searchRequestController) {
			searchRequestController.abort();
			searchRequestController = null;
		}

		if (searchTerm.length < 2 || !config.ajaxUrl) {
			clearLiveSearchResults(results);
			return;
		}

		searchDebounceTimer = window.setTimeout(function () {
			const url = new URL(config.ajaxUrl);
			url.searchParams.set('action', 'dewit_product_search');
			url.searchParams.set('term', searchTerm);

			searchRequestController = new AbortController();
			results.classList.add('is-visible', 'is-loading');
			results.innerHTML = '<div class="dewit-shop-search-results__empty">Zoeken...</div>';

			window.fetch(url.toString(), {
				credentials: 'same-origin',
				signal: searchRequestController.signal,
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					renderLiveSearchResults(results, payload && payload.data ? payload.data : [], searchTerm);
				})
				.catch(function (error) {
					if (error.name !== 'AbortError') {
						clearLiveSearchResults(results);
					}
				});
		}, 180);
	}

	function injectShopToolbar() {
		if (document.querySelector('.dewit-catalog')) {
			return;
		}

		const content = document.querySelector('.dewit-catalog__content');
		const grid = content ? content.querySelector('.dewit-grouped-products') : null;

		if (!content || !grid || content.querySelector('.dewit-shop-toolbar')) {
			return;
		}

		const toolbar = document.createElement('div');
		const form = document.createElement('form');
		const label = document.createElement('span');
		const input = document.createElement('input');
		const button = document.createElement('button');
		const results = document.createElement('div');
		const categoryToggle = document.createElement('button');
		const email = document.createElement('a');
		const phone = document.createElement('a');
		const params = new URL(window.location.href).searchParams;

		toolbar.className = 'dewit-shop-toolbar';

		form.className = 'dewit-shop-search';
		form.method = 'get';
		form.action = window.location.origin + '/';

		label.className = 'screen-reader-text';
		label.textContent = 'Zoeken naar producten';

		input.type = 'search';
		input.name = 's';
		input.placeholder = 'Zoeken naar producten';
		input.value = params.get('s') || '';

		button.type = 'submit';
		button.setAttribute('aria-label', 'Zoeken');
		button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>';

		results.className = 'dewit-shop-search-results';
		results.setAttribute('role', 'region');
		results.setAttribute('aria-label', 'Directe zoekresultaten');

		input.setAttribute('autocomplete', 'off');
		input.setAttribute('aria-autocomplete', 'list');

		input.addEventListener('input', function () {
			updateLiveSearch(input, results);
		});

		input.addEventListener('focus', function () {
			updateLiveSearch(input, results);
		});

		document.addEventListener('click', function (event) {
			if (!form.contains(event.target)) {
				clearLiveSearchResults(results);
			}
		});

		form.addEventListener('submit', function () {
			const postType = form.querySelector('input[name="post_type"]');

			if (!postType) {
				const hidden = document.createElement('input');
				hidden.type = 'hidden';
				hidden.name = 'post_type';
				hidden.value = 'product';
				form.appendChild(hidden);
			}
		});

		email.className = 'dewit-shop-email';
		email.href = 'mailto:info@dewitbouwmachines.nl';
		email.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg><span>info@dewitbouwmachines.nl</span>';

		phone.className = 'dewit-shop-phone';
		phone.href = 'tel:+31412634969';
		phone.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.59 2.61a2 2 0 0 1-.45 2.11L8 9.69a16 16 0 0 0 6.31 6.31l1.25-1.25a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.61.59A2 2 0 0 1 22 16.92Z"></path></svg><span>0412 - 63 49 69</span>';

		categoryToggle.className = 'dewit-category-toggle';
		categoryToggle.type = 'button';
		categoryToggle.setAttribute('aria-expanded', 'false');
		categoryToggle.setAttribute('aria-controls', 'catalog-sidebar');
		categoryToggle.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M7 12h10"></path><path d="M10 18h4"></path></svg><span>Categorieën</span>';

		form.appendChild(label);
		form.appendChild(input);
		form.appendChild(button);
		form.appendChild(results);
		toolbar.appendChild(form);
		toolbar.appendChild(email);
		toolbar.appendChild(categoryToggle);
		toolbar.appendChild(phone);
		content.insertBefore(toolbar, grid);
		injectProductViewSwitch(content, grid);
	}

	function initHeaderSearch() {
		document.querySelectorAll('.catalog-header-search').forEach(function (form) {
			if (form.dataset.liveSearchInitialized === 'true') {
				return;
			}

			const input = form.querySelector('input[type="search"]');
			const results = form.querySelector('.dewit-shop-search-results');

			if (!input || !results) {
				return;
			}

			form.dataset.liveSearchInitialized = 'true';
			input.setAttribute('autocomplete', 'off');
			input.setAttribute('aria-autocomplete', 'list');
			input.addEventListener('input', function () {
				updateLiveSearch(input, results);
			});
			input.addEventListener('focus', function () {
				updateLiveSearch(input, results);
			});
			document.addEventListener('click', function (event) {
				if (!form.contains(event.target)) {
					clearLiveSearchResults(results);
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', injectShopToolbar);
		document.addEventListener('DOMContentLoaded', initHeaderSearch);
		document.addEventListener('DOMContentLoaded', routeSidebarLogoToHome);
		document.addEventListener('DOMContentLoaded', initPullRefreshGuard);
		document.addEventListener('DOMContentLoaded', initFixedHeaderWheelScroll);
	} else {
		injectShopToolbar();
		initHeaderSearch();
		routeSidebarLogoToHome();
		initPullRefreshGuard();
		initFixedHeaderWheelScroll();
	}

	window.addEventListener('load', routeSidebarLogoToHome);
	window.addEventListener('load', initPullRefreshGuard);
	window.addEventListener('load', initFixedHeaderWheelScroll);
	window.addEventListener('resize', initPullRefreshGuard);
	window.addEventListener('resize', initFixedHeaderWheelScroll);
	window.addEventListener('dewit/products-updated', function () {
		updateProductViewSwitchLabel();
		setProductCardViewMode(getProductCardViewMode());
		initPullRefreshGuard();
	});
}());

(function () {
	let groupedProductsController = null;

	function getThemeConfig() {
		return window.dewitTheme || (typeof dewitTheme !== 'undefined' ? dewitTheme : {});
	}

	function normalizeDisplayText(value) {
		const normalized = String(value || '').replace(/&(\d{2,6});/g, '&#$1;');
		const parser = document.createElement('textarea');
		parser.innerHTML = normalized;

		return parser.value;
	}

	function setNonLinkedText(element, value) {
		const text = normalizeDisplayText(value || '');
		element.textContent = '';
		element.classList.add('dewit-no-link-text');
		element.setAttribute('aria-label', text);

		Array.from(text).forEach(function (character) {
			const characterElement = document.createElement('i');
			characterElement.setAttribute('aria-hidden', 'true');
			characterElement.textContent = character;
			element.appendChild(characterElement);
		});
	}

	function normalizeGroupedContainerAccessibility(container) {
		if (!container) {
			return;
		}

		container.removeAttribute('role');
		container.removeAttribute('aria-live');
		container.removeAttribute('aria-label');
	}

	function getActiveFilterParamNames() {
		return Array.from(new URL(window.location.href).searchParams.keys())
			.filter(function (key) {
				return key.indexOf('e-filter-') === 0 || key === 'product_cat' || key === 'dewit_parent_cat';
			});
	}

	function getActiveCategorySlug() {
		const url = new URL(window.location.href);
		const activeParam = Array.from(url.searchParams.entries())
			.find(function (entry) {
				return (entry[0].indexOf('e-filter-') === 0 || entry[0] === 'product_cat') && entry[1];
			});

		return activeParam ? activeParam[1] : '';
	}

	function getActiveParentCategorySlug() {
		return new URL(window.location.href).searchParams.get('dewit_parent_cat') ||
			(getThemeConfig().defaultParentCategory || '');
	}

	function getProductGridWidget() {
		return document.querySelector('.elementor-widget-loop-grid');
	}

	function getProductLoopContainer() {
		const widget = getProductGridWidget();

		if (!widget) {
			return null;
		}

		let container = widget.querySelector('.elementor-loop-container');

		if (!container) {
			container = document.createElement('div');
			container.className = 'elementor-loop-container elementor-grid dewit-generated-loop-container';
			widget.appendChild(container);
		}

		return container;
	}

	function disableGroupedElementorLoadMore() {
		const widget = getProductGridWidget();

		if (!widget) {
			return;
		}

		widget.classList.add('dewit-grouped-widget');
		widget.querySelectorAll('.e-load-more-anchor, .e-load-more-spinner, .e-load-more-message')
			.forEach(function (element) {
				element.hidden = true;
				element.setAttribute('aria-hidden', 'true');
			});
	}

	function updateGroupedCategoryUrl(slug, mode) {
		const url = new URL(window.location.href);

		getActiveFilterParamNames().forEach(function (key) {
			url.searchParams.delete(key);
		});

		url.searchParams.delete('product-page');
		url.searchParams.set('dewit_parent_cat', slug);

		if (url.toString() === window.location.href) {
			return;
		}

		if (mode === 'replace') {
			window.history.replaceState({}, '', url.toString());
			return;
		}

		if (mode !== 'none') {
			window.history.pushState({}, '', url.toString());
		}
	}

	function getGroupedCategoryUrl(slug) {
		const config = getThemeConfig();
		const url = new URL(config.homeUrl || '/', window.location.origin + '/');

		getActiveFilterParamNames().forEach(function (key) {
			url.searchParams.delete(key);
		});

		url.searchParams.delete('product-page');
		url.searchParams.set('dewit_parent_cat', slug);

		return url.toString();
	}

	function getCategorySectionId(slug) {
		return 'dewit-cat-' + String(slug || '').replace(/[^a-z0-9_-]/gi, '-');
	}

	function scrollToCategorySection(slug) {
		const section = document.getElementById(getCategorySectionId(slug));

		if (!section) {
			return false;
		}

		section.scrollIntoView({
			block: 'start',
			behavior: 'smooth',
		});

		return true;
	}

	function setActiveSidebarChild(slug) {
		document.querySelectorAll('.dewit-category-child[data-filter]').forEach(function (item) {
			item.setAttribute('aria-pressed', String(item.getAttribute('data-filter') === slug));
		});
	}

	function getGroupedProductsView() {
		const container = getProductLoopContainer();
		let view = container ? container.querySelector('.dewit-grouped-products') : null;

		if (!container) {
			return null;
		}

		if (!view) {
			view = document.createElement('div');
			view.className = 'dewit-grouped-products';
			container.innerHTML = '';
			container.appendChild(view);
		}

		container.classList.add('dewit-grouped-mode');
		container.style.display = 'block';
		normalizeGroupedContainerAccessibility(container);
		view.style.display = 'grid';
		disableGroupedElementorLoadMore();

		return view;
	}

	function setGroupedProductsLoading(view) {
		view.classList.add('is-visible', 'is-loading');
		view.setAttribute('aria-busy', 'true');
		view.style.display = 'grid';

		if (!view.children.length) {
			view.innerHTML = '<div class="dewit-grouped-products__status">Producten laden...</div>';
		}
	}

	function renderGroupedProductCard(product, index) {
		const card = document.createElement('a');
		const imageWrap = document.createElement('span');
		const body = document.createElement('span');
		const sku = document.createElement('span');
		const title = document.createElement('span');
		const isPriorityImage = index < 2;
		const isLcpCandidate = index < 2;

		card.className = 'dewit-grouped-product-card';
		card.href = product.url;
		card.style.setProperty('--dewit-card-index', String(Math.min(index, 24)));

		imageWrap.className = 'dewit-grouped-product-card__image';

		if (product.image) {
			const image = document.createElement('img');
			image.alt = '';
			image.decoding = 'async';
			image.loading = isPriorityImage ? 'eager' : 'lazy';
			image.fetchPriority = isLcpCandidate ? 'high' : 'low';
			image.width = product.image_width || 300;
			image.height = product.image_height || 300;
			if (product.image_srcset) {
				image.srcset = product.image_srcset;
			}
			image.sizes = product.image_sizes || '(max-width: 766px) calc((100vw - 40px) / 2), 220px';
			image.src = product.image;
			imageWrap.appendChild(image);
		}

		body.className = 'dewit-grouped-product-card__body';
		sku.className = 'dewit-grouped-product-card__sku';
		setNonLinkedText(sku, product.sku || '');
		title.className = 'dewit-grouped-product-card__title';
		title.textContent = normalizeDisplayText(product.title || '');

		body.appendChild(sku);
		body.appendChild(title);
		card.appendChild(imageWrap);
		card.appendChild(body);

		return card;
	}

	function renderGroupedProducts(groups) {
		const view = getGroupedProductsView();
		const container = getProductLoopContainer();
		const fragment = document.createDocumentFragment();
		const shouldAnimate = view && view.classList.contains('is-visible') && !view.classList.contains('is-loading');

		if (!view) {
			return;
		}

		view.classList.remove('is-loading');
		view.removeAttribute('aria-busy');
		view.classList.add('is-visible');
		view.style.display = 'grid';

		if (container) {
			container.classList.add('dewit-grouped-mode');
			normalizeGroupedContainerAccessibility(container);
		}

		if (!groups.length) {
			const status = document.createElement('div');

			status.className = 'dewit-grouped-products__status';
			status.textContent = 'Geen producten gevonden';
			view.replaceChildren(status);
			window.dispatchEvent(new CustomEvent('dewit/products-updated'));
			return;
		}

		let productIndex = 0;

		groups.forEach(function (group) {
			const section = document.createElement('section');
			const heading = document.createElement('h2');
			const grid = document.createElement('div');

			section.className = 'dewit-grouped-products__section';
			section.id = getCategorySectionId(group.slug);
			section.setAttribute('data-category-slug', group.slug || '');
			heading.className = 'dewit-grouped-products__heading';
			heading.textContent = normalizeDisplayText(group.name);
			grid.className = 'dewit-grouped-products__grid';

			if ((group.products || []).length === 1) {
				grid.classList.add('is-single');
			}

			(group.products || []).forEach(function (product) {
				grid.appendChild(renderGroupedProductCard(product, productIndex));
				productIndex += 1;
			});

			section.appendChild(heading);
			section.appendChild(grid);
			fragment.appendChild(section);
		});

		view.replaceChildren(fragment);
		window.dispatchEvent(new CustomEvent('dewit/products-updated'));

		if (shouldAnimate) {
			window.requestAnimationFrame(replayGroupedProductCardAnimation);
		}
	}

	function renderServerGroupedProducts() {
		if (document.querySelector('.dewit-catalog')) {
			return;
		}

		const grouped = window.dewitGroupedCategory;

		if (!grouped || !grouped.html) {
			return;
		}

		const container = getProductLoopContainer();
		const view = getGroupedProductsView();

		if (!container || !view) {
			return;
		}

		if (container.querySelector('.dewit-grouped-products.is-visible')) {
			container.classList.add('dewit-grouped-mode');
			container.style.display = 'block';
			disableGroupedElementorLoadMore();
			updateGroupedCategoryUrl(grouped.slug, 'replace');
			window.dispatchEvent(new CustomEvent('dewit/products-updated'));
			return;
		}

		container.classList.add('dewit-grouped-mode');
		container.style.display = 'block';
		container.innerHTML = grouped.html;
		updateGroupedCategoryUrl(grouped.slug, 'replace');
		disableGroupedElementorLoadMore();
		window.dispatchEvent(new CustomEvent('dewit/products-updated'));
	}

	function loadGroupedCategoryProducts(group, options) {
		const config = getThemeConfig();
		const view = getGroupedProductsView();
		const container = getProductLoopContainer();
		const ajaxUrl = config.ajaxUrl || (window.location.origin + '/wp-admin/admin-ajax.php');
		const loadOptions = options || {};

		if (!view) {
			return;
		}

		if (groupedProductsController) {
			groupedProductsController.abort();
		}

		if (container) {
			container.classList.add('dewit-grouped-mode');
		}

		disableGroupedElementorLoadMore();

		setGroupedProductsLoading(view);

		const url = new URL(ajaxUrl);
		url.searchParams.set('action', 'dewit_category_grouped_products');
		url.searchParams.set('category', group.parentSlug);

		groupedProductsController = new AbortController();

		window.fetch(url.toString(), {
			credentials: 'same-origin',
			signal: groupedProductsController.signal,
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				const groups = payload && payload.data ? payload.data : [];

				window.requestAnimationFrame(function () {
					updateGroupedCategoryUrl(group.parentSlug, loadOptions.history || 'push');
					renderGroupedProducts(groups);
				});
			})
			.catch(function (error) {
				if (error.name !== 'AbortError') {
					view.classList.remove('is-loading');
					view.innerHTML = '<div class="dewit-grouped-products__status">Producten konden niet geladen worden</div>';
				}
			});
	}

	function closeMobileCategories() {
		document.body.classList.remove('dewit-mobile-filter-open');
		document.querySelectorAll('.dewit-category-toggle, .dewit-header-category-toggle').forEach(function (button) {
			button.setAttribute('aria-expanded', 'false');
		});
	}

	function getParentCategorySlugFromTrigger(trigger) {
		try {
			return new URL(trigger.href, window.location.href).searchParams.get('dewit_parent_cat') || '';
		} catch (error) {
			return '';
		}
	}

	function activateSidebarParentTrigger(trigger) {
		const filter = trigger.closest('.dewit-category-filter');
		const groupElement = trigger.closest('.dewit-category-group');

		if (!filter || !groupElement) {
			return;
		}

		filter.querySelectorAll('.dewit-category-group.is-open').forEach(function (openGroup) {
			const openTrigger = openGroup.querySelector('.dewit-category-trigger');
			const openPanel = openGroup.querySelector('.dewit-category-panel');

			openGroup.classList.remove('is-open');

			if (openTrigger) {
				openTrigger.setAttribute('aria-pressed', 'false');
			}

			if (openPanel) {
				openPanel.hidden = true;
			}
		});

		groupElement.classList.add('is-open');
		trigger.setAttribute('aria-pressed', 'true');

		const panel = groupElement.querySelector('.dewit-category-panel');

		if (panel) {
			panel.hidden = false;
		}
	}

	function findParentCategoryTrigger(slug) {
		return Array.from(document.querySelectorAll('#catalog-sidebar .dewit-category-trigger')).find(function (trigger) {
			return getParentCategorySlugFromTrigger(trigger) === slug;
		}) || null;
	}

	function getParentCategoryLabelFromTrigger(trigger, fallbackSlug) {
		if (!trigger) {
			return fallbackSlug || '';
		}

		const label = trigger.querySelector('.dewit-category-label');

		return label ? label.textContent.trim() : trigger.textContent.trim();
	}

	function handleCategoryHistoryNavigation() {
		if (document.body.classList.contains('single-product')) {
			return;
		}

		const slug = getActiveParentCategorySlug();

		if (!slug) {
			return;
		}

		const trigger = findParentCategoryTrigger(slug);
		const label = getParentCategoryLabelFromTrigger(trigger, slug);

		window.addEventListener('dewit/products-updated', function () {
			if (trigger) {
				activateSidebarParentTrigger(trigger);
			}

			window.dispatchEvent(new CustomEvent('dewit/category-selected', {
				detail: {
					label: label,
					slug: slug,
				},
			}));
		}, { once: true });

		loadGroupedCategoryProducts({
			label: label,
			name: label,
			parentSlug: slug,
		}, { history: 'none' });
	}

	function bindDelegatedParentCategorySwitches() {
		if (document.body.classList.contains('dewit-parent-switch-delegation-ready')) {
			return;
		}

		let switchId = 0;

		document.body.classList.add('dewit-parent-switch-delegation-ready');
		document.addEventListener('click', function (event) {
			const trigger = event.target.closest('#catalog-sidebar .dewit-category-trigger');

			if (!trigger || document.body.classList.contains('single-product')) {
				return;
			}

			const parentSlug = getParentCategorySlugFromTrigger(trigger);

			if (!parentSlug) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			const nextSwitchId = switchId + 1;
			const label = trigger.querySelector('.dewit-category-label') ?
				trigger.querySelector('.dewit-category-label').textContent.trim() :
				trigger.textContent.trim();
			switchId = nextSwitchId;

			window.addEventListener('dewit/products-updated', function () {
				if (nextSwitchId !== switchId) {
					return;
				}

				activateSidebarParentTrigger(trigger);
				window.dispatchEvent(new CustomEvent('dewit/category-selected', {
					detail: {
						label: label,
						slug: parentSlug,
					},
				}));
			}, { once: true });

			try {
				loadGroupedCategoryProducts({
					label: label,
					name: label,
					parentSlug: parentSlug,
				});
			} catch (error) {
				window.location.href = trigger.href;
			}

			closeMobileCategories();
		}, true);
	}

	function getMobileCategoryToggleButtons() {
		const buttons = Array.from(document.querySelectorAll('.dewit-category-toggle'));
		const sidebar = document.getElementById('catalog-sidebar');
		let headerToggle = document.querySelector('.dewit-header-category-toggle');
		const productHeaderToggle = null;
		const headerHost = document.querySelector('.site-header');

		if (!sidebar) {
			if (headerToggle && headerToggle.classList.contains('dewit-category-toggle-ready')) {
				headerToggle.remove();
			}

			return buttons;
		}

		if (!headerToggle && productHeaderToggle) {
			headerToggle = productHeaderToggle;
		}

		if (!headerToggle && headerHost) {
			headerToggle = document.createElement('button');
			headerToggle.className = 'menu-toggle dewit-header-category-toggle';
			headerToggle.type = 'button';
			headerToggle.innerHTML = '<span class="menu-toggle__bar"></span><span class="screen-reader-text">Categorieën openen</span>';
			headerHost.insertBefore(headerToggle, headerHost.firstChild);
		}

		if (headerToggle && sidebar) {
			headerToggle.classList.add('dewit-header-category-toggle');
			headerToggle.setAttribute('aria-controls', 'catalog-sidebar');
			headerToggle.setAttribute('aria-expanded', String(document.body.classList.contains('dewit-mobile-filter-open')));
			headerToggle.setAttribute('aria-label', 'Categorieën openen');
			buttons.push(headerToggle);
		}

		return buttons;
	}

	function injectMobileCategoryControls() {
		const toggles = getMobileCategoryToggleButtons();

		if (!toggles.length) {
			return;
		}

		let overlay = document.querySelector('.dewit-category-overlay');

		if (!overlay) {
			overlay = document.createElement('button');
			overlay.className = 'dewit-category-overlay';
			overlay.type = 'button';
			overlay.setAttribute('aria-label', 'Categorieën sluiten');
			document.body.appendChild(overlay);
		}

		toggles.forEach(function (toggle) {
			if (toggle.classList.contains('dewit-category-toggle-ready')) {
				return;
			}

			toggle.classList.add('dewit-category-toggle-ready');
		});

		if (!document.body.classList.contains('dewit-category-toggle-delegation-ready')) {
			document.body.classList.add('dewit-category-toggle-delegation-ready');
			document.addEventListener('click', function (event) {
				const closeButton = event.target.closest('.dewit-product__sidebar-close');

				if (closeButton) {
					event.preventDefault();
					closeMobileCategories();
					return;
				}

				const toggle = event.target.closest('.dewit-category-toggle, .dewit-header-category-toggle');

				if (!toggle || !document.getElementById('catalog-sidebar')) {
					return;
				}

				event.preventDefault();

				const isOpen = document.body.classList.toggle('dewit-mobile-filter-open');

				getMobileCategoryToggleButtons().forEach(function (button) {
					button.setAttribute('aria-expanded', String(isOpen));
					button.setAttribute('aria-label', isOpen ? 'Categorieën sluiten' : 'Categorieën openen');
				});
			});
		}

		if (!overlay.classList.contains('dewit-category-overlay-ready')) {
			overlay.classList.add('dewit-category-overlay-ready');
			overlay.addEventListener('click', closeMobileCategories);
			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					closeMobileCategories();
				}
			});
		}
	}

	function enhanceShopNavigation() {
		bindDelegatedParentCategorySwitches();
		injectMobileCategoryControls();
	}

	function initServerGroupedProducts() {
		renderServerGroupedProducts();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', enhanceShopNavigation);
		document.addEventListener('DOMContentLoaded', initServerGroupedProducts);
	} else {
		enhanceShopNavigation();
		initServerGroupedProducts();
	}

	window.addEventListener('load', enhanceShopNavigation);
	window.addEventListener('popstate', handleCategoryHistoryNavigation);
	window.dewitGetActiveCategorySlug = getActiveCategorySlug;
	window.dewitGetActiveParentCategorySlug = getActiveParentCategorySlug;
	window.dewitGetGroupedCategoryUrl = getGroupedCategoryUrl;
	window.dewitLoadGroupedCategoryProducts = loadGroupedCategoryProducts;
	window.addEventListener('dewit/close-mobile-categories', closeMobileCategories);
}());

(function () {
	let productCardClickDelegationReady = false;

	function markImageLoaded(card, image) {
		function setLoaded() {
			card.classList.add('dewit-product-image-loaded');
		}

		if (image.complete) {
			setLoaded();
			return;
		}

		image.addEventListener('load', setLoaded, { once: true });
		image.addEventListener('error', setLoaded, { once: true });
	}

	function prepareProductCards(root) {
		const scope = root || document;
		const cards = [];

		if (scope.matches && scope.matches('.dewit-grouped-product-card')) {
			cards.push(scope);
		}

		if (scope.querySelectorAll) {
			scope.querySelectorAll('.dewit-grouped-product-card').forEach(function (card) {
				cards.push(card);
			});
		}

		cards.forEach(function (card) {
			const image = card.querySelector('img');
			const productUrl = getProductUrlFromCard(card);

			if (image) {
				image.loading = image.loading || 'lazy';
				image.decoding = 'async';
				markImageLoaded(card, image);
			}

			if (productUrl && !card.classList.contains('dewit-product-card-click-ready')) {
				card.classList.add('dewit-product-card-click-ready');
				card.setAttribute('role', 'link');
				card.tabIndex = 0;
				card.addEventListener('keydown', function (event) {
					if (event.key === 'Enter' || event.key === ' ') {
						event.preventDefault();
						window.location.href = getProductUrlFromCard(card);
					}
				});
			}

			if (card.classList.contains('dewit-product-card-ready')) {
				return;
			}

			card.classList.add('dewit-product-card-ready');

			window.requestAnimationFrame(function () {
				card.classList.add('is-visible');
			});
		});
	}

	function getProductUrlFromCard(card) {
		const productLink = Array.from(card.querySelectorAll('a[href]'))
			.find(function (link) {
				const href = link.getAttribute('href') || '';

				return href && href !== '#';
			});
		const productId = getProductIdFromCard(card);

		if (productLink) {
			return productLink.href;
		}

		return productId ? '/?post_type=product&p=' + productId : '';
	}

	function getClosestProductCard(target) {
		const element = target && target.nodeType === Node.ELEMENT_NODE ? target : target.parentElement;

		return element ? element.closest('.dewit-grouped-product-card') : null;
	}

	function getProductIdFromCard(card) {
		const match = String(card.className).match(/(?:^|\s)post-(\d+)(?:\s|$)/);

		return match ? match[1] : '';
	}

	function isInteractiveCardTarget(target) {
		const element = target && target.nodeType === Node.ELEMENT_NODE ? target : target.parentElement;

		return Boolean(element && element.closest('a, button, input, select, textarea, label'));
	}

	function installProductCardClickDelegation() {
		if (productCardClickDelegationReady) {
			return;
		}

		productCardClickDelegationReady = true;
		document.addEventListener('click', function (event) {
			const card = getClosestProductCard(event.target);

			if (!card || isInteractiveCardTarget(event.target)) {
				return;
			}

			const productUrl = getProductUrlFromCard(card);

			if (!productUrl) {
				return;
			}

			event.preventDefault();
			window.location.href = productUrl;
		}, true);
	}

	function watchProductGrid() {
		if (document.querySelector('.dewit-catalog')) {
			return;
		}

		if (document.querySelector('.dewit-grouped-products.is-visible')) {
			return;
		}

		const containers = document.querySelectorAll('.dewit-grouped-products');

		installProductCardClickDelegation();
		prepareProductCards(document);

		containers.forEach(function (container) {
			if (container.classList.contains('dewit-product-grid-watched')) {
				return;
			}

			container.classList.add('dewit-product-grid-watched');

			const observer = new MutationObserver(function (mutations) {
				mutations.forEach(function (mutation) {
					mutation.addedNodes.forEach(function (node) {
						if (node.nodeType === Node.ELEMENT_NODE) {
							prepareProductCards(node);
							window.dispatchEvent(new CustomEvent('dewit/products-updated'));
						}
					});
				});
			});

			observer.observe(container, {
				childList: true,
				subtree: true,
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', watchProductGrid);
	} else {
		watchProductGrid();
	}

	window.addEventListener('load', watchProductGrid);
}());

(function () {
	function preserveProductCategoryContext() {
		const parentSlug = new URL(window.location.href).searchParams.get('dewit_parent_cat');

		if (!parentSlug) {
			return;
		}

		document.querySelectorAll('body.single-product .related.products a[href*="/product/"]').forEach(function (link) {
			try {
				const url = new URL(link.href);

				url.searchParams.set('dewit_parent_cat', parentSlug);
				link.href = url.toString();
			} catch (error) {
				// Ignore malformed links from third-party markup.
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', preserveProductCategoryContext);
	} else {
		preserveProductCategoryContext();
	}
}());

(function () {
	let categoryTreePromise = null;
	const categoryCacheKey = 'dewitProductCategoriesWithProducts';
	const categoryCacheMaxAge = 12 * 60 * 60 * 1000;

	function getThemeConfig() {
		return window.dewitTheme || (typeof dewitTheme !== 'undefined' ? dewitTheme : {});
	}

	function normalizeDisplayText(value) {
		const normalized = String(value || '').replace(/&(\d{2,6});/g, '&#$1;');
		const parser = document.createElement('textarea');
		parser.innerHTML = normalized;

		return parser.value;
	}

	function getCachedCategoryGroups() {
		try {
			const cached = JSON.parse(window.localStorage.getItem(categoryCacheKey) || 'null');

			if (!cached || !Array.isArray(cached.categories) || Date.now() - cached.timestamp > categoryCacheMaxAge) {
				return [];
			}

			return buildGroupsFromCategories(cached.categories);
		} catch (error) {
			return [];
		}
	}

	function cacheCategories(categories) {
		try {
			window.localStorage.setItem(categoryCacheKey, JSON.stringify({
				categories: categories,
				timestamp: Date.now(),
			}));
		} catch (error) {
			// Storage can be unavailable in private browsing; the live API remains the fallback.
		}
	}

	function getInlineCategoryGroups() {
		const categories = Array.isArray(window.dewitProductCategories)
			? window.dewitProductCategories
			: Object.values(window.dewitProductCategories || {});

		if (!categories.length) {
			return [];
		}

		return buildGroupsFromCategories(categories);
	}

	function fetchCategoryTree() {
		if (categoryTreePromise) {
			return categoryTreePromise;
		}

		categoryTreePromise = fetch('/wp-json/wc/store/v1/products/categories?per_page=100', {
			credentials: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Could not load product categories.');
				}

				return response.json();
			})
			.then(function (categories) {
				cacheCategories(categories);
				return buildGroupsFromCategories(categories);
			})
			.catch(function () {
				return [];
			});

		return categoryTreePromise;
	}

	function buildGroupsFromCategories(categories) {
		const categoriesWithProducts = getCategoriesWithProducts(categories)
			.filter(function (category) {
				return category.slug !== 'alle' && String(category.name || '').trim().toLowerCase() !== 'alle';
			});
		const childrenByParent = new Map();
		const categoryBySlug = new Map();

		categoriesWithProducts.forEach(function (category) {
			categoryBySlug.set(category.slug, category);

			if (!childrenByParent.has(category.parent)) {
				childrenByParent.set(category.parent, []);
			}

			childrenByParent.get(category.parent).push(category);
		});

		childrenByParent.forEach(function (children) {
			children.sort(function (a, b) {
				return a.name.localeCompare(b.name, 'nl');
			});
		});

		return (childrenByParent.get(0) || [])
			.map(function (parent) {
				const children = (childrenByParent.get(parent.id) || []).filter(function (child) {
					return child.slug !== 'alle' && String(child.name || '').trim().toLowerCase() !== 'alle';
				});

				return {
					label: parent.name,
					parentSlug: parent.slug,
					count: parent.count,
					productCount: children.reduce(function (total, child) {
						return total + Number(child.count || 0);
					}, Number(parent.count || 0)),
					image: getCategoryImage(parent),
					slugs: getDescendantSlugs(parent.id, childrenByParent),
					children: children.map(function (child) {
						return {
							name: child.name,
							slug: child.slug,
							count: child.count,
						};
					}),
					categoriesBySlug: categoryBySlug,
				};
			})
			.filter(function (group) {
				return group.slugs.length;
			});
	}

	function getCategoriesWithProducts(categories) {
		const childrenByParent = new Map();

		categories.forEach(function (category) {
			if (!childrenByParent.has(category.parent)) {
				childrenByParent.set(category.parent, []);
			}

			childrenByParent.get(category.parent).push(category);
		});

		function hasProducts(category) {
			const children = childrenByParent.get(category.id) || [];

			return Number(category.count) > 0 || children.some(hasProducts);
		}

		return categories.filter(hasProducts);
	}

	function getCategoryImage(category) {
		const image = category ? category.image : null;
		const placeholder = (window.dewitTheme && window.dewitTheme.placeholderImage) || null;

		if (!image) {
			return placeholder;
		}

		if (typeof image === 'string') {
			return {
				src: image,
				width: 300,
				height: 300,
			};
		}

		return {
			src: image.src || image.thumbnail || image.url || (placeholder ? placeholder.src : ''),
			width: image.width || 300,
			height: image.height || 300,
		};
	}

	function getDescendantSlugs(parentId, childrenByParent) {
		const children = childrenByParent.get(parentId) || [];

		return children.flatMap(function (child) {
			return [child.slug].concat(getDescendantSlugs(child.id, childrenByParent));
		});
	}

	function appendCategoryTriggerContent(trigger, group) {
		const label = document.createElement('span');

		label.className = 'dewit-category-label';
		label.textContent = group.label || group.name || '';

		trigger.appendChild(label);
	}

	function normalizeDisplayText(value) {
		const normalized = String(value || '').replace(/&(\d{2,6});/g, '&#$1;');
		const parser = document.createElement('textarea');
		parser.innerHTML = normalized;

		return parser.value;
	}

	function getActiveCategorySlug() {
		if (window.dewitGetActiveCategorySlug) {
			return window.dewitGetActiveCategorySlug();
		}

		const url = new URL(window.location.href);
		const activeParam = Array.from(url.searchParams.entries())
			.find(function (entry) {
				return (entry[0].indexOf('e-filter-') === 0 || entry[0] === 'product_cat') && entry[1];
			});

		return activeParam ? activeParam[1] : '';
	}

	function getActiveParentCategorySlug() {
		if (window.dewitGetActiveParentCategorySlug) {
			return window.dewitGetActiveParentCategorySlug();
		}

		return new URL(window.location.href).searchParams.get('dewit_parent_cat') ||
			((window.dewitTheme && window.dewitTheme.defaultParentCategory) || '');
	}

	function getGroupedCategoryUrl(slug) {
		if (window.dewitGetGroupedCategoryUrl) {
			return window.dewitGetGroupedCategoryUrl(slug);
		}

		const homeUrl = (window.dewitTheme && window.dewitTheme.homeUrl) || '/';
		const url = new URL(homeUrl, window.location.origin + '/');
		url.searchParams.delete('product-page');
		url.searchParams.set('dewit_parent_cat', slug);

		return url.toString();
	}

	function getCategorySectionId(slug) {
		return 'dewit-cat-' + String(slug || '').replace(/[^a-z0-9_-]/gi, '-');
	}

	function scrollToCategorySection(slug) {
		const section = document.getElementById(getCategorySectionId(slug));

		if (!section) {
			return false;
		}

		section.scrollIntoView({
			block: 'start',
			behavior: 'smooth',
		});

		return true;
	}

	function setActiveSidebarChild(slug) {
		document.querySelectorAll('.dewit-category-child[data-filter]').forEach(function (item) {
			item.setAttribute('aria-pressed', String(item.getAttribute('data-filter') === slug));
		});
	}

	function loadGroupedCategoryProducts(group) {
		if (window.dewitLoadGroupedCategoryProducts) {
			window.dewitLoadGroupedCategoryProducts(group);
		}
	}

	function hasServerGroupedProductsForActiveParent(group) {
		const grouped = window.dewitGroupedCategory;

		return Boolean(
			group &&
			grouped &&
			grouped.html &&
			grouped.slug &&
			grouped.slug === group.parentSlug
		);
	}

	function getFilterParamName(filter) {
		const widget = filter.closest('.dewit-catalog-category-filter');
		let loopId = '';
		let taxonomy = 'product_cat';

		if (widget) {
			try {
				const settings = JSON.parse(widget.getAttribute('data-settings') || '{}');
				loopId = settings.selected_element || '';
				taxonomy = settings.taxonomy || taxonomy;
			} catch (error) {
				loopId = '';
			}
		}

		if (loopId) {
			return 'e-filter-' + loopId + '-' + taxonomy;
		}

		const existingParam = Array.from(new URL(window.location.href).searchParams.keys())
			.find(function (key) {
				return key.indexOf('e-filter-') === 0 && key.indexOf('-' + taxonomy) > -1;
			});

		return existingParam || 'e-filter-' + taxonomy;
	}

	function createGeneratedFilterItem(category, filter) {
		const item = document.createElement('button');
		const filterParamName = getFilterParamName(filter);

		item.className = 'e-filter-item dewit-generated-filter';
		item.type = 'button';
		item.setAttribute('data-filter', category.slug);
		item.setAttribute('aria-pressed', String(isSlugActive(category.slug)));
		item.textContent = category.name;

		item.addEventListener('click', function () {
			updateSelectedCategoryContext(category.slug, category.name);
			closeMobileCategoryDrawer();

			const url = new URL(window.location.href);
			url.searchParams.set(filterParamName, category.slug);
			url.searchParams.delete('product-page');
			window.location.href = url.toString();
		});

		return item;
	}

	function createCategoryChildItem(category, group) {
		const item = document.createElement('button');

		item.className = 'dewit-category-child';
		item.type = 'button';
		item.setAttribute('data-filter', category.slug);
		item.setAttribute('aria-pressed', 'false');
		item.textContent = normalizeDisplayText(category.name);

		item.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			closeMobileCategoryDrawer();
			setActiveSidebarChild(category.slug);

			if (document.body.classList.contains('single-product')) {
				window.location.href = getGroupedCategoryUrl(group.parentSlug) + '#' + getCategorySectionId(category.slug);
				return;
			}

			if (getActiveParentCategorySlug() === group.parentSlug && scrollToCategorySection(category.slug)) {
				return;
			}

			window.addEventListener('dewit/products-updated', function () {
				scrollToCategorySection(category.slug);
			}, { once: true });
			loadGroupedCategoryProducts(group);
		});

		return item;
	}

	function closeMobileCategoryDrawer() {
		window.dispatchEvent(new CustomEvent('dewit/close-mobile-categories'));
	}

	function updateSelectedCategoryContext(slug, label) {
		window.dispatchEvent(new CustomEvent('dewit/category-selected', {
			detail: {
				label: label,
				slug: slug,
			},
		}));
	}

	function closeDrawerOnFilterClick(item) {
		if (item.classList.contains('dewit-close-drawer-ready')) {
			return;
		}

		item.classList.add('dewit-close-drawer-ready');
		item.addEventListener('click', function () {
			updateSelectedCategoryContext(item.getAttribute('data-filter'), item.textContent.trim());
			closeMobileCategoryDrawer();
		});
	}

	function isItemActive(item) {
		return isSlugActive(item.getAttribute('data-filter')) ||
			item.getAttribute('aria-pressed') === 'true';
	}

	function isSlugActive(slug) {
		const url = new URL(window.location.href);

		return Array.from(url.searchParams.values()).includes(slug);
	}

	function renderCategoryFilters(groups) {
		if (document.querySelector('.dewit-catalog-category-filter')) {
			return;
		}

		const filters = document.querySelectorAll('.dewit-catalog-category-filter');

		filters.forEach(function (filter) {
			if (filter.classList.contains('dewit-category-dropdowns-ready')) {
				return;
			}

			let categorySwitchId = 0;
			const items = Array.from(filter.children)
				.filter(function (item) {
					return item.classList && item.classList.contains('dewit-category-child');
				})
				.filter(function (item) {
					const slug = item.getAttribute('data-filter') || '';
					const label = item.textContent.trim().toLowerCase();

					return slug !== '__all' && slug !== '*' && label !== 'alle' && label !== 'alle producten';
				});

			const itemsBySlug = new Map();
			items.forEach(function (item) {
				itemsBySlug.set(item.getAttribute('data-filter'), item);
			});

			const fragment = document.createDocumentFragment();

			groups.forEach(function (group) {
				const groupElement = document.createElement('div');
				const trigger = document.createElement('a');
				const activeSlug = getActiveCategorySlug();
				const startsOpen = getActiveParentCategorySlug() === group.parentSlug || group.slugs.indexOf(activeSlug) > -1;

				groupElement.className = 'dewit-category-group';
				groupElement.classList.toggle('is-open', startsOpen);

				trigger.className = 'dewit-category-trigger';
				trigger.href = getGroupedCategoryUrl(group.parentSlug);
				trigger.setAttribute('aria-pressed', startsOpen ? 'true' : 'false');
				appendCategoryTriggerContent(trigger, group);

				trigger.addEventListener('click', function (event) {
					event.preventDefault();

					if (window.dewitLoadGroupedCategoryProducts && !document.body.classList.contains('single-product')) {
						const label = group.label || group.name || trigger.textContent.trim();
						const switchId = categorySwitchId + 1;
						categorySwitchId = switchId;

						window.addEventListener('dewit/products-updated', function () {
							if (switchId !== categorySwitchId) {
								return;
							}

							activateParentGroup(filter, groupElement, trigger);
							updateSelectedCategoryContext(group.parentSlug, label);
						}, { once: true });

						try {
							loadGroupedCategoryProducts(group);
							closeMobileCategoryDrawer();
						} catch (error) {
							window.location.href = trigger.href;
						}

						return;
					}

					activateParentGroup(filter, groupElement, trigger);
					window.location.href = trigger.href;
				});

				groupElement.appendChild(trigger);

				if (Array.isArray(group.children) && group.children.length) {
					const panel = document.createElement('div');
					panel.className = 'dewit-category-panel';
					panel.hidden = !startsOpen;

					group.children.forEach(function (child) {
						panel.appendChild(createCategoryChildItem(child, group));
					});

					groupElement.appendChild(panel);
				}

				fragment.appendChild(groupElement);
			});

			filter.innerHTML = '';
			filter.appendChild(fragment);
			filter.classList.add('dewit-category-dropdowns-ready');
			window.dispatchEvent(new CustomEvent('dewit/categories-ready'));

			const activeParentSlug = getActiveParentCategorySlug() || getActiveCategorySlug();
			const activeParentGroup = activeParentSlug
				? groups.find(function (group) {
					return group.parentSlug === activeParentSlug || group.slugs.indexOf(activeParentSlug) > -1;
				})
				: null;

			if (activeParentGroup) {
				if (hasServerGroupedProductsForActiveParent(activeParentGroup)) {
					if (window.dewitLoadGroupedCategoryProducts) {
						window.dispatchEvent(new CustomEvent('dewit/products-updated'));
					}

					return;
				}

				loadGroupedCategoryProducts(activeParentGroup);
			}
		});
	}

	function activateParentGroup(filter, groupElement, trigger) {
		filter.querySelectorAll('.dewit-category-group.is-open').forEach(function (openGroup) {
			openGroup.classList.remove('is-open');
			const openTrigger = openGroup.querySelector('.dewit-category-trigger');
			const openPanel = openGroup.querySelector('.dewit-category-panel');

			if (openTrigger) {
				openTrigger.setAttribute('aria-pressed', 'false');
			}

			if (openPanel) {
				openPanel.hidden = true;
			}
		});

		groupElement.classList.add('is-open');
		trigger.setAttribute('aria-pressed', 'true');

		const panel = groupElement.querySelector('.dewit-category-panel');

		if (panel) {
			panel.hidden = false;
		}
	}

	function revealCategoryFilters() {
		document.querySelectorAll('#catalog-sidebar .dewit-catalog-category-filter')
			.forEach(function (filter) {
				filter.classList.add('dewit-category-dropdowns-ready');
			});
	}

	function buildCategoryDropdowns() {
		if (document.querySelector('.dewit-catalog-category-filter')) {
			return;
		}

		const filters = document.querySelectorAll('.dewit-catalog-category-filter');

		if (!filters.length) {
			return;
		}

		const cachedGroups = getCachedCategoryGroups();
		const inlineGroups = getInlineCategoryGroups();

		if (inlineGroups.length) {
			renderCategoryFilters(inlineGroups);
		} else if (cachedGroups.length) {
			renderCategoryFilters(cachedGroups);
		}

		if (!inlineGroups.length && !cachedGroups.length) {
			fetchCategoryTree().then(function (groups) {
				if (!groups.length) {
					return;
				}

				renderCategoryFilters(groups);
			});
		}
	}

	function getCleanCategoryUrl() {
		const url = new URL(window.location.href);

		Array.from(url.searchParams.keys()).forEach(function (key) {
			if (key.indexOf('e-filter-') === 0 || key === 'product_cat' || key === 'dewit_parent_cat') {
				url.searchParams.delete(key);
			}
		});

		url.searchParams.delete('product-page');

		return url.toString();
	}

	const sidebarPreviewCache = new Map();
	let sidebarPreviewController = null;
	let sidebarPreviewTimer = null;
	let sidebarPreviewActiveSlug = '';
	let sidebarPreviewRequestTimer = null;

	function getSidebarPreviewPanel() {
		let panel = document.querySelector('.dewit-sidebar-preview');

		if (panel) {
			return panel;
		}

		panel = document.createElement('aside');
		panel.className = 'dewit-sidebar-preview';
		panel.setAttribute('aria-hidden', 'true');
		panel.innerHTML = '<div class="dewit-sidebar-preview__inner"></div>';
		document.body.appendChild(panel);

		panel.addEventListener('mouseenter', function () {
			window.clearTimeout(sidebarPreviewTimer);
		});

		panel.addEventListener('mouseleave', hideSidebarPreview);

		return panel;
	}

	function hideSidebarPreview() {
		window.clearTimeout(sidebarPreviewTimer);
		sidebarPreviewTimer = window.setTimeout(function () {
			const panel = document.querySelector('.dewit-sidebar-preview');

			if (panel) {
				panel.classList.remove('is-visible', 'is-loading');
				panel.setAttribute('aria-hidden', 'true');
			}
		}, 120);
	}

	function positionSidebarPreview(panel, trigger) {
		const sidebar = document.getElementById('catalog-sidebar');
		const triggerRect = trigger.getBoundingClientRect();
		const sidebarRect = sidebar ? sidebar.getBoundingClientRect() : triggerRect;
		const panelHeight = 248;
		const top = Math.max(16, Math.min(triggerRect.top - 12, window.innerHeight - panelHeight - 16));

		panel.style.left = Math.round(sidebarRect.right + 12) + 'px';
		panel.style.top = Math.round(top) + 'px';
	}

	function renderSidebarPreviewItems(panel, label, slug, items) {
		const config = getThemeConfig();
		const placeholder = config.placeholderImage && config.placeholderImage.src ? config.placeholderImage.src : '';
		const inner = panel.querySelector('.dewit-sidebar-preview__inner');
		const productItems = Array.isArray(items) ? items : [];

		if (!inner) {
			return;
		}

		inner.innerHTML = '';

		const heading = document.createElement('div');
		heading.className = 'dewit-sidebar-preview__heading';
		heading.textContent = normalizeDisplayText(label || slug || 'Categorie');
		inner.appendChild(heading);

		if (!productItems.length) {
			const empty = document.createElement('div');
			empty.className = 'dewit-sidebar-preview__empty';
			empty.textContent = 'Geen voorbeeldproducten gevonden';
			inner.appendChild(empty);
			return;
		}

		productItems.forEach(function (item) {
			const link = document.createElement('a');
			const image = document.createElement('img');
			const body = document.createElement('span');
			const sku = document.createElement('span');
			const title = document.createElement('span');

			link.className = 'dewit-sidebar-preview__item';
			link.href = item.url || '#';

			image.src = item.image || placeholder;
			image.alt = '';
			image.loading = 'lazy';
			image.decoding = 'async';

			body.className = 'dewit-sidebar-preview__body';
			sku.className = 'dewit-sidebar-preview__sku';
			sku.textContent = normalizeDisplayText(item.sku || '');
			title.className = 'dewit-sidebar-preview__title';
			title.textContent = normalizeDisplayText(item.title || '');

			body.appendChild(sku);
			body.appendChild(title);
			link.appendChild(image);
			link.appendChild(body);
			inner.appendChild(link);
		});
	}

	function getSidebarPreviewSlugFromTrigger(trigger) {
		try {
			return new URL(trigger.href, window.location.href).searchParams.get('dewit_parent_cat') || '';
		} catch (error) {
			return '';
		}
	}

	function getSidebarPreviewLabelFromTrigger(trigger, fallbackSlug) {
		if (!trigger) {
			return fallbackSlug || '';
		}

		const label = trigger.querySelector('.dewit-category-label');

		return label ? label.textContent.trim() : trigger.textContent.trim();
	}

	function showSidebarPreview(trigger) {
		if (!window.matchMedia || !window.matchMedia('(min-width: 1024px)').matches) {
			return;
		}

		if (document.body.classList.contains('dewit-mobile-filter-open')) {
			return;
		}

		const slug = getSidebarPreviewSlugFromTrigger(trigger);

		if (!slug) {
			return;
		}

		const label = getSidebarPreviewLabelFromTrigger(trigger, slug);
		const panel = getSidebarPreviewPanel();
		const inner = panel.querySelector('.dewit-sidebar-preview__inner');

		window.clearTimeout(sidebarPreviewTimer);
		positionSidebarPreview(panel, trigger);
		panel.classList.add('is-visible');
		panel.setAttribute('aria-hidden', 'false');

		if (sidebarPreviewActiveSlug === slug && panel.classList.contains('is-visible')) {
			return;
		}

		sidebarPreviewActiveSlug = slug;

		if (sidebarPreviewCache.has(slug)) {
			panel.classList.remove('is-loading');
			renderSidebarPreviewItems(panel, label, slug, sidebarPreviewCache.get(slug));
			return;
		}

		panel.classList.add('is-loading');

		if (inner) {
			const heading = document.createElement('div');
			const loading = document.createElement('div');

			inner.innerHTML = '';
			heading.className = 'dewit-sidebar-preview__heading';
			heading.textContent = normalizeDisplayText(label || slug);
			loading.className = 'dewit-sidebar-preview__empty';
			loading.textContent = 'Voorbeeldproducten laden...';
			inner.appendChild(heading);
			inner.appendChild(loading);
		}

		if (sidebarPreviewController) {
			sidebarPreviewController.abort();
		}

		if (sidebarPreviewRequestTimer) {
			window.clearTimeout(sidebarPreviewRequestTimer);
		}

		const config = getThemeConfig();
		const ajaxUrl = config.ajaxUrl || (window.location.origin + '/wp-admin/admin-ajax.php');
		const url = new URL(ajaxUrl);
		url.searchParams.set('action', 'dewit_category_preview_products');
		url.searchParams.set('category', slug);

		sidebarPreviewController = new AbortController();
		sidebarPreviewRequestTimer = window.setTimeout(function () {
			if (sidebarPreviewActiveSlug !== slug) {
				return;
			}

			if (sidebarPreviewController) {
				sidebarPreviewController.abort();
			}

			sidebarPreviewCache.set(slug, []);
			panel.classList.remove('is-loading');
			renderSidebarPreviewItems(panel, label, slug, []);
		}, 2800);

		window.fetch(url.toString(), {
			credentials: 'same-origin',
			signal: sidebarPreviewController.signal,
		})
			.then(function (response) {
				if (!response.ok) {
					return [];
				}

				return response.json();
			})
			.then(function (payload) {
				const items = Array.isArray(payload)
					? payload
					: (payload && Array.isArray(payload.data) ? payload.data : []);

				if (sidebarPreviewActiveSlug !== slug) {
					return;
				}

				if (sidebarPreviewRequestTimer) {
					window.clearTimeout(sidebarPreviewRequestTimer);
					sidebarPreviewRequestTimer = null;
				}

				sidebarPreviewCache.set(slug, items);
				panel.classList.remove('is-loading');
				renderSidebarPreviewItems(panel, label, slug, items);
				positionSidebarPreview(panel, trigger);
			})
			.catch(function (error) {
				if (sidebarPreviewActiveSlug !== slug) {
					return;
				}

				if (sidebarPreviewRequestTimer) {
					window.clearTimeout(sidebarPreviewRequestTimer);
					sidebarPreviewRequestTimer = null;
				}

				if (error.name !== 'AbortError') {
					sidebarPreviewCache.set(slug, []);
					panel.classList.remove('is-loading');
					renderSidebarPreviewItems(panel, label, slug, []);
				}
			});
	}

	function initSidebarCategoryPreviews() {
		const sidebar = document.getElementById('catalog-sidebar');

		if (!sidebar) {
			return;
		}

		function handlePreviewOpen(event) {
			const trigger = event.target.closest('.dewit-category-trigger');

			if (trigger && sidebar.contains(trigger)) {
				showSidebarPreview(trigger);
			}
		}

		function handlePreviewClose(event) {
			if (!event.relatedTarget || !sidebar.contains(event.relatedTarget)) {
				hideSidebarPreview();
			}
		}

		if (!sidebar.classList.contains('dewit-sidebar-previews-ready')) {
			sidebar.classList.add('dewit-sidebar-previews-ready');
			sidebar.addEventListener('pointerover', handlePreviewOpen);
			sidebar.addEventListener('mouseover', handlePreviewOpen);
			sidebar.addEventListener('focusin', handlePreviewOpen);
			sidebar.addEventListener('pointerout', handlePreviewClose);
			sidebar.addEventListener('mouseout', handlePreviewClose);
			sidebar.addEventListener('focusout', handlePreviewClose);
		}

		if (!document.documentElement.classList.contains('dewit-sidebar-previews-delegated')) {
			document.documentElement.classList.add('dewit-sidebar-previews-delegated');
			document.addEventListener('pointerover', function (event) {
				const currentSidebar = document.getElementById('catalog-sidebar');
				const trigger = event.target.closest('#catalog-sidebar .dewit-category-trigger');

				if (currentSidebar && trigger) {
					showSidebarPreview(trigger);
				}
			});
			window.addEventListener('scroll', hideSidebarPreview, { passive: true });
			window.addEventListener('resize', hideSidebarPreview);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', buildCategoryDropdowns);
		document.addEventListener('DOMContentLoaded', function () {
			window.setTimeout(revealCategoryFilters, 700);
		});
	} else {
		buildCategoryDropdowns();
		window.setTimeout(revealCategoryFilters, 700);
	}

	window.addEventListener('load', revealCategoryFilters);
}());
