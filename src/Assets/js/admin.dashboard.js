document.addEventListener('DOMContentLoaded', () => {
	const root = document;
	root.querySelectorAll('[data-pluginname-count]').forEach((element) => {
		element.classList.add('pluginname-count-ready');
	});
});
