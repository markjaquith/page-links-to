// Copy to clipboard.
jQuery($ => {
	const $clipboardLinks = $('.plt-copy-short-url');
	if (ClipboardJS.isSupported()) {
		$clipboardLinks.click(e => e.preventDefault());
	} else {
		$clipboardLinks.hide();
	}

	$('.plt-links-to button').click(e => {
		$('#cws-links-to').focus();
	});

	const clipboard = new ClipboardJS('.plt-copy-short-url');
	const copied = pltVars.copied;
	const browserNoSupportCopying = pltVars.browserNoSupportCopying;

	clipboard.on('success', e => {
		const $trigger = $(e.trigger);

		$trigger.text(copied);
		setTimeout(() => $trigger.text($trigger.data('original-text')), 4000);
	});

	clipboard.on('error', e => {
		const $trigger = $(e.trigger);

		$trigger.text(browserNoSupportCopying);
		setTimeout(() => $clipboardLinks.hide(), 4000);
	});
});

// Quick Add.
jQuery($ => {
	const $modal = $('#plt-quick-add');
	const $form = $modal.find('form:first');
	const $saveDraft = $('#plt-quick-add-save');
	const $publish = $('#plt-quick-add-publish');
	const $menuItem = $(
		'a[href$="post_type=page&page=plt-add-page-link"], a[href$="#new-page-link"]'
	);
	const $messages = $modal.find('.messages');
	const $shortUrlMessage = $modal.find('.short-url-message');
	const $title = $modal.find('[name="title"]');
	const $url = $modal.find('[name="url"]');
	const $slug = $modal.find('[name="slug"]');
	const nonce = $modal.find('[name="plt_nonce"]').val();
	const defaultSlugPlaceholder = $slug.prop('placeholder');
	const fancyUrls = window.pltVars.fancyUrls;
	const [yes, no] = [true, false];

	const modalAction = action => () => $modal.dialog(action);
	const isOpen = modalAction('isOpen');
	const open = modalAction('open');
	const close = modalAction('close');

	const makeSlugFromTitle = (title = '') => {
		return title
			.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/-{2,}/g, '-')
			.replace(/[^a-z0-9-]/, '')
			.replace(/-$/, '')
			.replace(/^-/, '');
	};

	const makeSlugFromSlug = (slug = '') => {
		return slug
			.toLowerCase()
			.replace(/\s+/g, '-')
			.replace(/-{2,}/g, '-')
			.replace(/[^a-z0-9-]/, '')
			.replace(/^-/, '');
	};

	const addMessage = message => {
		const $newMessage = $(`<p>${message}</p>`);
		$messages.append($newMessage);
		return $newMessage;
	};

	const addExpiringMessage = (message, duration) => {
		const $newMessage = addMessage(message);
		setTimeout(() => $newMessage.fadeOut(), duration);
		return $newMessage;
	};

	const displayShortUrlMessage = show => $shortUrlMessage.toggle(show);

	const updateSlug = () => {
		const placeholderSlug = makeSlugFromTitle($title.val());
		const slug = makeSlugFromSlug($slug.val());

		$slug.prop(
			'placeholder',
			placeholderSlug.length ? placeholderSlug : defaultSlugPlaceholder
		);
		$slug.val(slug);
	};

	const noDefaultEvent = func => e => {
		e.preventDefault();
		func();
	};

	const hardUpdateSlug = noDefaultEvent(() =>
		$slug.val(makeSlugFromTitle($slug.val()))
	);

	const handleShowSlugMessage = noDefaultEvent(() =>
		displayShortUrlMessage(
			$slug.prop('placeholder').length > 16 && !$slug.val().length
		)
	);

	const reset = () => {
		$title.val('');
		$url.val('');
		$slug.val('');
		$slug.prop('placeholder', defaultSlugPlaceholder);
		maybeUpdateButtons();
	};

	const updateButtons = (enabled = yes) => {
		$publish.prop('disabled', !enabled);
		$saveDraft.prop('disabled', !enabled);
	};

	const maybeUpdateButtons = () =>
		updateButtons($title.val().length && $url.val().length);

	const submit = ({ publish = yes } = {}) => {
		const title = $title.val();
		const url = $url.val();
		let slug = $slug.val() ? $slug.val() : makeSlugFromTitle(title);

		$.post(
			ajaxurl,
			{
				action: 'plt_quick_add',
				plt_title: title,
				plt_url: url,
				plt_slug: slug,
				plt_publish: publish ? 1 : 0,
				plt_nonce: nonce,
			},
			response => {
				const { message } = response.data;
				const delay = 5000;
				reset();
				$title.focus();
				addExpiringMessage(message, delay);
			}
		);
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
	if (fancyUrls) {
		$title.keyup(updateSlug);
		$title.keyup(handleShowSlugMessage);
		$slug.keyup(updateSlug);
		$slug.keyup(handleShowSlugMessage);
		$form.change(hardUpdateSlug);
	}

	$menuItem.click(clickMenuItem);
	$saveDraft.click(saveDraft);
	$publish.click(publish);
	$form.submit(publish);
	$title.keyup(maybeUpdateButtons);
	$url.keyup(maybeUpdateButtons);
	$form.change(maybeUpdateButtons);

	// Init.
	reset();
});
