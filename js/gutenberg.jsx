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
		this.updateLink = this.updateLink.bind(this);
		this.state.enabled = this.hasUrl();
	}

	state = {
		prevUrl: '',
		// enabled: null,
	};

	getUrl() {
		const { meta } = this.props;
		return meta._links_to || '';
	}

	getDisplayUrl() {
		const { prevUrl } = this.state;
		return this.getUrl() || prevUrl;
	}

	hasUrl() {
		return this.getUrl().length > 0;
	}

	enabled() {
		return this.state.enabled;
	}

	toggleStatus() {
		const { prevUrl } = this.state;
		console.log({ prevUrl, enabled: this.enabled(), url: this.getUrl() });

		this.setState(prevState => {
			const newState = {
				enabled: !prevState.enabled,
			};

			if (prevState.enabled) {
				newState.prevUrl = this.getUrl();
			}

			return newState;
		});

		this.updateLink(this.enabled() ? null : prevUrl);
		this.enabled() && this.setState({ prevUrl: this.getUrl() });
	}

	updateLink(link) {
		const { meta, onUpdateLink } = this.props;
		onUpdateLink(meta, link);
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
					<PluginPostStatusInfo>
						<TextControl
							label="Links to"
							value={this.getDisplayUrl()}
							onChange={this.updateLink}
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
