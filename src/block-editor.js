import { PanelRow, TextControl, CheckboxControl } from '@wordpress/components';
import { withInstanceId, compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Fragment, Component } from '@wordpress/element';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';

function PanelGroup({ children }) {
	const style = {
		display: 'flex',
		flexDirection: 'column',
	};

	return <div style={style}>{children}</div>;
}

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
		const { prevUrl, prevNewTab } = this.state;

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
			// If it was enabled before they clicked, they are disabling it.
			this.updateLink(null);
			this.updateNewTab(false);

			// Hold on to the previous state, in case they change their mind.
			this.setState({
				prevUrl: this.getUrl(),
				prevNewTab: this.opensInNewTab(),
			});
		} else {
			// If it was disabled before thy clicked, they are enabling it.
			// We should restore the previous states of the url and new tab checkbox.
			this.updateLink(prevUrl);
			this.updateNewTab(prevNewTab);
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
			<PluginPostStatusInfo>
				<PanelGroup>
					<PanelRow>
						<CheckboxControl
							label="Custom Permalink"
							data-testid="plt-enabled"
							checked={this.enabled()}
							onChange={this.toggleStatus}
						/>
					</PanelRow>

					{this.enabled() && (
						<>
							<PanelRow>
								<TextControl
									label="Links to"
									data-testid="plt-url"
									value={this.getDisplayUrl()}
									onChange={this.updateLink}
									placeholder="https://"
								/>
							</PanelRow>
							<PanelRow>
								<CheckboxControl
									label="Open in new tab"
									data-testid="plt-newtab"
									checked={this.opensInNewTab()}
									onChange={this.toggleNewTab}
								/>
							</PanelRow>
						</>
					)}
				</PanelGroup>
			</PluginPostStatusInfo>
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
