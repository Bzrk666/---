const MFO_LIST = [
	{ slug: 'mfo-example-1', name: 'ООО МФО Пример 1' },
	{ slug: 'mfo-example-2', name: 'ООО МФО Пример 2' }
];

function populateMfoSelect(selectId) {
	const select = document.getElementById(selectId);
	if (!select) return;
	select.innerHTML = '';
	for (const item of MFO_LIST) {
		const opt = document.createElement('option');
		opt.value = item.slug;
		opt.textContent = item.name;
		select.appendChild(opt);
	}
}

document.addEventListener('DOMContentLoaded', () => {
	populateMfoSelect('company_slug');
});


