jQuery($ => {
	const $modal = $('#plt-quick-add');
	const $form = $modal.find('form:first');
	const $saveDraft = $('#plt-quick-add-save');
	const $publish = $('#plt-quick-add-publish');
	const $menuItem = $('a[href$="post_type=page&page=plt-add-page-link"]');
	const $message = $modal.find('.message');
	const $title = $modal.find('[name="title"]');
	const $url = $modal.find('[name="url"]');
	const $slug = $modal.find('[name="slug"]');
	const defaultSlugPlaceholder = $slug.prop('placeholder');
	const [yes, no] = [true, false];

	const modalAction = action => () => $modal.dialog(action);
	const isOpen = modalAction('isOpen');
	const open = modalAction('open');
	const close = modalAction('close');

	const makeSlug = (title = '') => {
		return title
			.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/-{2,}/g, '-')
			.replace(/[^a-z0-9-]/, '')
			.replace(/-$/, '')
			.replace(/^-/, '');
	};

	const setMessage = (message) => {
		$message.html(message);
	};

	const setExpiringMessage = (message, duration) => {
		setMessage(message);
		setTimeout(() => setMessage(''), duration);
	}

	const updateSlug = () => {
		const slug = makeSlug($title.val());

		$slug.prop('placeholder', slug.length ? slug : defaultSlugPlaceholder);
	};

	const noDefaultEvent = func => e => {
		e.preventDefault();
		func();
	};

	const reset = () => {
		$title.val('');
		$url.val('');
		$slug.val('');
	};

	const submit = ({ publish = yes } = {}) => {
		const title = $title.val();
		const url = $url.val();
		let slug = $slug.val() ? $slug.val() : makeSlug(title);

		$.post(ajaxurl, {
			action: 'plt_quick_add',
			plt_title: title,
			plt_url: url,
			plt_slug: slug,
			plt_publish: publish ? 1 : 0,
		},
		(response) => {
			const { status } = response.data;
			const statusText = 'publish' === status ? 'published' : 'saved';
			const delay = 1500;
			reset();
			setExpiringMessage(`Link ${statusText}!`, delay);
			setTimeout(close, delay);
		});
	};

	const clickMenuItem = noDefaultEvent(() => (isOpen() ? close() : open()));
	const saveDraft = noDefaultEvent(() => submit({ publish: no }));
	const publish = noDefaultEvent(() => submit({ publish: yes }));

	$modal.dialog({
		title: 'Add Page Link',
		dialogClass: 'wp-dialog',
		autoOpen: no,
		draggable: no,
		width: 'auto',
		modal: yes,
		resizable: no,
		closeOnEscape: yes,
		position: {
			my: 'center',
			at: 'center',
			of: window,
		},
		open: () => $('.ui-widget-overlay').bind('click', close),
		create: () => $('.ui-dialog-titlebar-close').addClass('ui-button'),
	});

	// Events.
	$title.keyup(updateSlug);
	$menuItem.click(clickMenuItem);
	$saveDraft.click(saveDraft);
	$publish.click(publish);
	$form.submit(publish);
});
