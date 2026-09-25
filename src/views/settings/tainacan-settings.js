(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var form = document.querySelector('form.tainacan-settings');
		var toc = document.getElementById('tainacan-settings-toc');
		var list = document.getElementById('tainacan-settings-toc-list');
		var scroller = document.querySelector('.tainacan-page-container-content');

		if (!form || !toc || !list || !scroller)
			return;

		var headings = Array.prototype.slice.call(form.querySelectorAll(':scope > h2'));
		if (headings.length < 2)
			return;

		function scrollToHeading(heading) {
			var previousPosition = heading.style.position;
			heading.style.position = 'static';
			var top = heading.getBoundingClientRect().top - scroller.getBoundingClientRect().top + scroller.scrollTop;
			heading.style.position = previousPosition;

			var scrollMargin = parseFloat(window.getComputedStyle(heading).scrollMarginTop) || 0;
			var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			scroller.scrollTo({
				top: Math.max(0, top - scrollMargin),
				behavior: reduceMotion ? 'auto' : 'smooth'
			});

			if (!heading.hasAttribute('tabindex'))
				heading.setAttribute('tabindex', '-1');
			heading.focus({ preventScroll: true });
		}

		var pairs = headings.map(function (heading, index) {
			if (!heading.id)
				heading.id = 'tainacan-settings-section-' + (index + 1);

			var item = document.createElement('li');
			var link = document.createElement('a');
			link.href = '#' + heading.id;
			link.textContent = heading.textContent.trim();
			item.appendChild(link);
			list.appendChild(item);

			link.addEventListener('click', function (event) {
				event.preventDefault();
				scrollToHeading(heading);
				if (window.history && window.history.pushState)
					window.history.pushState(null, '', '#' + heading.id);
			});

			return { heading: heading, link: link };
		});

		toc.hidden = false;

		function setActive() {
			var rootTop = scroller.getBoundingClientRect().top;
			var current = pairs[0];

			pairs.forEach(function (pair) {
				if (pair.heading.getBoundingClientRect().top - rootTop < 140)
					current = pair;
			});

			pairs.forEach(function (pair) {
				var isCurrent = pair === current;
				pair.link.classList.toggle('is-active', isCurrent);
				if (isCurrent)
					pair.link.setAttribute('aria-current', 'location');
				else
					pair.link.removeAttribute('aria-current');
			});
		}

		scroller.addEventListener('scroll', setActive, { passive: true });
		setActive();

		if (window.location.hash) {
			var target = document.getElementById(decodeURIComponent(window.location.hash.slice(1)));
			if (target)
				scrollToHeading(target);
		}
	});
}());
