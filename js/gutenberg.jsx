const { PanelRow, TextControl, CheckboxControl } = wp.components;
const { withInstanceId, compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { Fragment, Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { registerPlugin } = wp.plugins;

class LinksTo extends Component {
	constructor(props) {
		super(props);
		this.toggleStatus = this.toggleStatus.bind(this);
		this.toggleNewTab = this.toggleNewTab.bind(this);
		this.updateLink = this.updateLink.bind(this);
		this.state.enabled = this.hasUrl();
	}

	state = {
		prevUrl: '',
		prevNewTab: false,
	};

	getUrl() {
		return this.props.meta._links_to || '';
	}

	getDisplayUrl() {
		const { prevUrl } = this.state;
		return this.getUrl() || prevUrl;
	}

	hasUrl() {
		return this.getUrl().length > 0;
	}

	opensInNewTab() {
		return this.props.meta._links_to_target === '_blank';
	}

	enabled() {
		return this.state.enabled;
	}

	toggleStatus() {
		const { prevUrl } = this.state;

		this.setState(prevState => {
			const newState = {
				enabled: !prevState.enabled,
			};

			if (prevState.enabled) {
				newState.prevUrl = this.getUrl();
			}

			return newState;
		});

		if (this.enabled()) {
			this.updateLink(null);
			this.setState({
				prevUrl: this.getUrl(),
				prevNewTab: this.opensInNewTab(),
			});
		} else {
			this.updateLink(prevUrl);
		}
	}

	toggleNewTab() {
		this.updateNewTab(!this.opensInNewTab());
	}

	updateLink(link) {
		const { meta, onUpdateLink } = this.props;
		onUpdateLink(meta, link);
	}

	updateNewTab(enabled) {
		const { meta, onUpdateNewTab } = this.props;
		onUpdateNewTab(meta, enabled);
	}

	render() {
		return (
			<Fragment>
				<PluginPostStatusInfo>
					<CheckboxControl
						label="Custom Permalink"
						checked={this.enabled()}
						onChange={this.toggleStatus}
					/>
				</PluginPostStatusInfo>

				{this.enabled() && (
					<div>
						<PluginPostStatusInfo>
							<TextControl
								label="Links to"
								value={this.getDisplayUrl()}
								onChange={this.updateLink}
								placeholder="https://"
							/>
						</PluginPostStatusInfo>
						<PluginPostStatusInfo>
							<CheckboxControl
								label="Open in new tab"
								checked={this.opensInNewTab()}
								onChange={this.toggleNewTab}
							/>
						</PluginPostStatusInfo>
					</div>
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
		onUpdateNewTab: (meta, enabled) => {
			dispatch('core/editor').editPost({
				meta: { ...meta, _links_to_target: enabled ? '_blank' : '' },
			});
		},
	})),
	withInstanceId,
])(LinksTo);

registerPlugin('page-links-to', {
	render: PageLinksTo,
});
