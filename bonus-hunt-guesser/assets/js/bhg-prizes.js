(function () {
    'use strict';

    function computeVisible(baseVisible, width) {
        if (width <= 640) {
            return 1;
        }

        if (width <= 1024) {
            return Math.min(baseVisible, 2);
        }

        return baseVisible;
    }

    function updateDots(dots, activeIndex, totalPages) {
        if (!dots) {
            return;
        }

        dots.forEach(function (dot, index) {
            var isActive = index === activeIndex;
            var isVisible = index < totalPages;

            dot.classList.toggle('active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            dot.setAttribute('tabindex', isActive ? '0' : '-1');
            dot.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            dot.style.display = isVisible ? '' : 'none';
        });
    }

    function initCarousel(container) {
        if (!container || container.dataset.bhgPrizeCarouselInit) {
            return;
        }

        var track = container.querySelector('.bhg-prize-track');
        var wrapper = container.querySelector('.bhg-prize-track-wrapper');
        if (!track || !wrapper) {
            return;
        }

        var slides = Array.prototype.slice.call(track.querySelectorAll('.bhg-prize-card'));
        if (!slides.length) {
            return;
        }

        container.dataset.bhgPrizeCarouselInit = '1';

        var dotsContainer = container.querySelector('.bhg-prize-dots');
        var dots = dotsContainer ? Array.prototype.slice.call(dotsContainer.querySelectorAll('.bhg-prize-dot')) : [];
        var prev = container.querySelector('.bhg-prize-prev');
        var next = container.querySelector('.bhg-prize-next');

        var baseVisible = Math.max(1, parseInt(container.getAttribute('data-visible'), 10) || 1);
        var autoplayEnabled = container.getAttribute('data-autoplay') === '1';
        var autoplayInterval = Math.max(1000, parseInt(container.getAttribute('data-interval'), 10) || 5000);

        var pageIndex = 0;
        var currentVisible = baseVisible;
        var cardWidth = 0;
        var totalPages = Math.max(1, Math.ceil(slides.length / currentVisible));
        var autoplayTimer = null;

        function recalcLayout() {
            var width = wrapper.clientWidth || container.clientWidth;
            if (!width) {
                return false;
            }

            currentVisible = computeVisible(baseVisible, width);
            cardWidth = width / currentVisible;

            slides.forEach(function (slide) {
                slide.style.flex = '0 0 ' + cardWidth + 'px';
                slide.style.maxWidth = cardWidth + 'px';
            });

            track.style.width = cardWidth * slides.length + 'px';
            totalPages = Math.max(1, Math.ceil(slides.length / currentVisible));
            container.setAttribute('data-pages', String(totalPages));

            return true;
        }

        function updatePosition() {
            if (!cardWidth && !recalcLayout()) {
                return;
            }

            var offset = pageIndex * cardWidth * currentVisible;
            track.style.transform = 'translateX(' + (-offset) + 'px)';
        }

        function updateUiState() {
            updateDots(dots, pageIndex, totalPages);

            if (dotsContainer) {
                dotsContainer.style.display = totalPages > 1 ? '' : 'none';
            }

            if (prev) {
                prev.style.display = totalPages > 1 ? '' : 'none';
                prev.setAttribute('aria-hidden', totalPages > 1 ? 'false' : 'true');
            }

            if (next) {
                next.style.display = totalPages > 1 ? '' : 'none';
                next.setAttribute('aria-hidden', totalPages > 1 ? 'false' : 'true');
            }
        }

        function goTo(newIndex) {
            if (totalPages <= 0) {
                return;
            }

            pageIndex = (newIndex + totalPages) % totalPages;
            updatePosition();
            updateUiState();
        }

        function stopAutoplay() {
            if (autoplayTimer) {
                window.clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function startAutoplay() {
            if (!autoplayEnabled || totalPages <= 1 || autoplayTimer) {
                return;
            }

            autoplayTimer = window.setInterval(function () {
                goTo(pageIndex + 1);
            }, autoplayInterval);
        }

        function restartAutoplay() {
            if (!autoplayEnabled) {
                return;
            }

            stopAutoplay();
            startAutoplay();
        }

        function handleResize() {
            var previousPages = totalPages;
            if (!recalcLayout()) {
                return;
            }

            if (pageIndex >= totalPages) {
                pageIndex = totalPages - 1;
            }

            updatePosition();
            updateUiState();

            if (autoplayEnabled) {
                if (totalPages <= 1) {
                    stopAutoplay();
                } else if (previousPages <= 1 && totalPages > 1) {
                    startAutoplay();
                }
            }
        }

        if (prev) {
            prev.addEventListener('click', function () {
                goTo(pageIndex - 1);
                restartAutoplay();
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                goTo(pageIndex + 1);
                restartAutoplay();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function (event) {
                var dotIndex = parseInt(event.currentTarget.getAttribute('data-index'), 10);
                if (!isNaN(dotIndex)) {
                    goTo(dotIndex);
                    restartAutoplay();
                }
            });
        });

        container.addEventListener('mouseenter', stopAutoplay);
        container.addEventListener('mouseleave', startAutoplay);

        window.addEventListener('resize', handleResize);
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAutoplay();
            } else {
                startAutoplay();
            }
        });

        recalcLayout();
        goTo(0);
        startAutoplay();
    }

    var lightboxInitialised = false;

    function initLightbox() {
        if (lightboxInitialised) {
            return;
        }

        lightboxInitialised = true;

        var overlay = document.createElement('div');
        overlay.className = 'bhg-prize-lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-hidden', 'true');

        var content = document.createElement('div');
        content.className = 'bhg-prize-lightbox__content';

        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'bhg-prize-lightbox__close';
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.innerHTML = '&times;';

        var image = document.createElement('img');
        image.alt = '';

        content.appendChild(closeButton);
        content.appendChild(image);
        overlay.appendChild(content);
        document.body.appendChild(overlay);

        function closeLightbox() {
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
            image.removeAttribute('src');
            image.alt = '';
            document.body.classList.remove('bhg-prize-lightbox-open');
        }

        function openLightbox(url, alt) {
            if (!url) {
                return;
            }

            image.src = url;
            image.alt = alt || '';
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('bhg-prize-lightbox-open');
            closeButton.focus();
        }

        closeButton.addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function (event) {
            if ('Escape' === event.key && overlay.classList.contains('is-active')) {
                closeLightbox();
            }
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest('[data-bhg-prize-popup="image"]');
            if (!link) {
                return;
            }

            var url = link.getAttribute('href');
            var alt = link.getAttribute('data-bhg-prize-alt') || link.getAttribute('title') || '';

            event.preventDefault();
            openLightbox(url, alt);
        });
    }

    function initPrizes() {
        var carousels = document.querySelectorAll('.bhg-prize-carousel');
        carousels.forEach(initCarousel);

        if (carousels.length || document.querySelector('[data-bhg-prize-popup="image"]')) {
            initLightbox();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPrizes);
    } else {
        initPrizes();
    }
})();
