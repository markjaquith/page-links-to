import {
	Panel,
	PanelBody,
	PanelRow,
	TextControl,
	RadioControl,
	CheckboxControl,
} from '@wordpress/components';
import { withInstanceId, compose, withState } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Fragment, Component } from '@wordpress/element';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';

// function PanelGroup({ children }) {
// 	const style = {
// 		display: 'flex',
// 		flexDirection: 'column',
// 	};

// 	return <div style={style}>{children}</div>;
// }

class LinksTo extends Component {
	constructor(props) {
		super(props);
		this.toggleStatus = this.toggleStatus.bind(this);
		this.state.enabled = this.hasUrl();
	}

	state = {
		prevUrl: '',
		prevNewTab: false,
	};

	getUrl() {
		return this.props.url || '';
	}

	getDisplayUrl() {
		const { prevUrl } = this.state;
		return this.getUrl() || prevUrl;
	}

	hasUrl() {
		return this.getUrl().length > 0;
	}

	opensInNewTab() {
		return this.props.newTab;
	}

	enabled() {
		return this.state.enabled;
	}

	toggleStatus(newValue) {
		const { prevUrl, prevNewTab } = this.state;
		const { onUpdateLink, onUpdateNewTab } = this.props;

		this.setState(prevState => {
			const newState = {
				enabled: newValue,
			};

			if (prevState.enabled) {
				newState.prevUrl = this.getUrl();
			}

			return newState;
		});

		if (newValue) {
			// We should restore the previous states of the url and new tab checkbox.
			onUpdateLink(prevUrl);
			onUpdateNewTab(prevNewTab);
		} else {
			onUpdateLink(null);
			onUpdateNewTab(false);

			// Hold on to the previous state, in case they change their mind.
			this.setState({
				prevUrl: this.getUrl(),
				prevNewTab: this.opensInNewTab(),
			});
		}
	}

	updateLink(link) {
		const { meta, onUpdateLink } = this.props;
		onUpdateLink(meta, link);
	}

	render() {
		const { onUpdateLink, onUpdateNewTab } = this.props;
		const PointsTo = withState({
			option: this.enabled() ? 'custom' : 'wordpress',
		})(({
			option,
			setState,
		}) => (
			<RadioControl
				label="Point this content to:"
				selected={option}
				options={[
					{ label: 'Its normal WordPress URL', value: 'wordpress' },
					{ label: 'A custom URL', value: 'custom' },
				]}
				onChange={(option) => {
					setState({ option });
					this.toggleStatus(option === 'custom');
				}}
			/>
		));

		return (
			<PluginDocumentSettingPanel
				title="Page Links To"
				name="PageLinksTo"
				icon={this.enabled() ? 'admin-links' : 'disabled'}
			>
				<PanelRow>
					<PointsTo />
				</PanelRow>

				{this.enabled() && (
					<>
						<PanelRow>
							<TextControl
								label="Links to"
								data-testid="plt-url"
								value={this.getDisplayUrl()}
								onChange={onUpdateLink}
								placeholder="https://"
							/>
						</PanelRow>
						<PanelRow>
							<CheckboxControl
								label="Open in new tab"
								data-testid="plt-newtab"
								checked={this.opensInNewTab()}
								onChange={onUpdateNewTab}
							/>
						</PanelRow>
					</>
				)}
			</PluginDocumentSettingPanel>
		);
	}
}

const PageLinksTo = compose([
	withSelect(select => {
		const getMeta = attr =>
			(select('core/editor').getEditedPostAttribute('meta') || [])[attr];
		return {
			url: getMeta('_links_to'),
			newTab: getMeta('_links_to_target') === '_blank',
		};
	}),
	withDispatch(dispatch => ({
		onUpdateLink: link => {
			dispatch('core/editor').editPost({ meta: { _links_to: link } });
		},
		onUpdateNewTab: enabled => {
			dispatch('core/editor').editPost({
				meta: { _links_to_target: enabled ? '_blank' : '' },
			});
		},
	})),
	withInstanceId,
])(LinksTo);

registerPlugin('page-links-to', {
	render: PageLinksTo,
});
