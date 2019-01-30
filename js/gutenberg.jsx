const { FormToggle, PanelRow, TextControl } = wp.components;
const { withInstanceId, compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { Fragment, Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { registerPlugin } = wp.plugins;

class LinksTo extends Component {
	state = {
		enabled: false,
		prevValue: '',
	};

	render() {
		const { instanceId, meta, onUpdateLink } = this.props;
		const id = `plt-toggle-${instanceId}`;
		const textId = `plt-links-to-${instanceId}`;
		const url = meta._links_to || '';
		let initiallyEnabled = this.state.enabled === true || (url && url.length > 0);

		const updateLink = link => {
			console.log('updateLink', {link, meta});
			onUpdateLink(meta, link);
		};

		const toggleStatus = () => {
			initiallyEnabled = false; // No longer needed.
			console.log('toggleStatus', {state: this.state, meta});
			this.setState(prevState => ({
				enabled: !prevState.enabled,
			}));
			onUpdateLink(meta, null);
		};

		const enabled = this.state.enabled || initiallyEnabled;

		return (
			<Fragment>
				<PluginPostStatusInfo>
					<label htmlFor={id}>Custom Link</label>
					<FormToggle id={id} checked={!!enabled} onChange={toggleStatus} />
				</PluginPostStatusInfo>
				{enabled && (
					<PluginPostStatusInfo>
						<label htmlFor={textId}>Links to</label>
						<TextControl
							value={url}
							onChange={updateLink}
							placeholder="https://"
						/>
					</PluginPostStatusInfo>
				)}
			</Fragment>
		);
	}
}

const PageLinksTo = compose([
	withSelect(select => ({
		meta: select('core/editor').getEditedPostAttribute('meta'),
	})),
	withDispatch(dispatch => ({
		onUpdateLink: (meta, link) => {
			dispatch('core/editor').editPost({ meta: { ...meta, _links_to: link } });
		},
	})),
	withInstanceId,
])(LinksTo);

registerPlugin('page-links-to', {
	render: PageLinksTo,
});
