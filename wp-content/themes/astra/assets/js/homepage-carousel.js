/**
 * Homepage Carousel Functionality
 * Converts homepage-grid into a carousel
 */

(function() {
	'use strict';

	function initHomepageCarousel() {
		const carousel = document.querySelector('.homepage-grid');
		
		if (!carousel) {
			return;
		}

		// Check if already initialized
		if (carousel.dataset.carouselInitialized === 'true') {
			return;
		}

		const items = carousel.querySelectorAll('.homepage-tile');
		
		if (items.length === 0) {
			return;
		}

		// Create carousel track wrapper
		const track = document.createElement('div');
		track.className = 'homepage-carousel-track';
		track.style.display = 'flex';
		track.style.gap = '1.5rem';
		track.style.transition = 'transform 0.5s ease-in-out';

		// Move all items into track
		items.forEach(item => {
			track.appendChild(item);
		});

		// Clear carousel and add track
		carousel.innerHTML = '';
		carousel.appendChild(track);

		// Create navigation buttons
		const prevButton = document.createElement('button');
		prevButton.className = 'homepage-carousel-nav prev';
		prevButton.innerHTML = '‹';
		prevButton.setAttribute('aria-label', 'Previous slide');
		prevButton.type = 'button';

		const nextButton = document.createElement('button');
		nextButton.className = 'homepage-carousel-nav next';
		nextButton.innerHTML = '›';
		nextButton.setAttribute('aria-label', 'Next slide');
		nextButton.type = 'button';

		carousel.appendChild(prevButton);
		carousel.appendChild(nextButton);

		// Carousel state - hero carousel shows one at a time
		let currentIndex = 0;
		let autoPlayInterval = null;
		const autoPlayDelay = 5000; // 5 seconds

		function updateCarousel() {
			const maxIndex = items.length - 1;
			currentIndex = Math.min(currentIndex, maxIndex);
			currentIndex = Math.max(0, currentIndex);

			// Calculate translateX - each item is 100% width
			const carouselWidth = carousel.offsetWidth;
			const translateX = -currentIndex * carouselWidth;
			track.style.transform = `translateX(${translateX}px)`;

			// Update button states
			prevButton.disabled = currentIndex === 0;
			nextButton.disabled = currentIndex >= maxIndex;

			// Update indicators
			updateIndicators();
		}

		function nextSlide() {
			const maxIndex = items.length - 1;
			if (currentIndex < maxIndex) {
				currentIndex++;
			} else {
				currentIndex = 0; // Loop back to start
			}
			updateCarousel();
			resetAutoPlay();
		}

		function prevSlide() {
			const maxIndex = items.length - 1;
			if (currentIndex > 0) {
				currentIndex--;
			} else {
				currentIndex = maxIndex; // Loop to end
			}
			updateCarousel();
			resetAutoPlay();
		}

		function goToSlide(index) {
			if (index >= 0 && index < items.length) {
				currentIndex = index;
				updateCarousel();
				resetAutoPlay();
			}
		}

		// Create indicators
		const indicatorsContainer = document.createElement('div');
		indicatorsContainer.className = 'homepage-carousel-indicators';
		const indicators = [];

		for (let i = 0; i < items.length; i++) {
			const indicator = document.createElement('button');
			indicator.className = 'homepage-carousel-indicator';
			if (i === 0) indicator.classList.add('active');
			indicator.setAttribute('aria-label', 'Go to slide ' + (i + 1));
			indicator.type = 'button';
			indicator.addEventListener('click', function() {
				goToSlide(i);
			});
			indicatorsContainer.appendChild(indicator);
			indicators.push(indicator);
		}

		carousel.appendChild(indicatorsContainer);

		function updateIndicators() {
			indicators.forEach((indicator, index) => {
				if (index === currentIndex) {
					indicator.classList.add('active');
				} else {
					indicator.classList.remove('active');
				}
			});
		}

		// Auto-play functionality
		function startAutoPlay() {
			autoPlayInterval = setInterval(function() {
				nextSlide();
			}, autoPlayDelay);
		}

		function stopAutoPlay() {
			if (autoPlayInterval) {
				clearInterval(autoPlayInterval);
				autoPlayInterval = null;
			}
		}

		function resetAutoPlay() {
			stopAutoPlay();
			startAutoPlay();
		}

		// Pause on hover
		carousel.addEventListener('mouseenter', stopAutoPlay);
		carousel.addEventListener('mouseleave', startAutoPlay);

		// Event listeners
		nextButton.addEventListener('click', nextSlide);
		prevButton.addEventListener('click', prevSlide);

		// Handle window resize
		let resizeTimeout;
		window.addEventListener('resize', function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				updateCarousel();
			}, 250);
		});

		// Initialize
		updateCarousel();
		startAutoPlay();
		carousel.dataset.carouselInitialized = 'true';

		// Recalculate on images load
		const images = track.querySelectorAll('img');
		let imagesLoaded = 0;
		images.forEach(img => {
			if (img.complete) {
				imagesLoaded++;
			} else {
				img.addEventListener('load', function() {
					imagesLoaded++;
					if (imagesLoaded === images.length) {
						updateCarousel();
					}
				});
			}
		});

		if (imagesLoaded === images.length) {
			updateCarousel();
		}
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHomepageCarousel);
	} else {
		initHomepageCarousel();
	}

	// Re-initialize if content is loaded dynamically
	if (typeof MutationObserver !== 'undefined') {
		const observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.addedNodes.length) {
					const hasCarousel = Array.from(mutation.addedNodes).some(node => {
						return node.nodeType === 1 && (
							node.classList.contains('homepage-grid') ||
							node.querySelector('.homepage-grid')
						);
					});
					if (hasCarousel) {
						setTimeout(initHomepageCarousel, 100);
					}
				}
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}
})();

