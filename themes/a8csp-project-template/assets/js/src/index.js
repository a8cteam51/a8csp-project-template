const initializeHeaderState = () => {
	const header = document.querySelector('.site-header');

	if (!header) {
		return;
	}

	const updateHeaderState = () => {
		header.classList.toggle('is-scrolled', window.scrollY > 0);
	};

	updateHeaderState();
	window.addEventListener('scroll', updateHeaderState, { passive: true });
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initializeHeaderState, {
		once: true,
	});
} else {
	initializeHeaderState();
}
