// WP Imports
import React from '@wordpress/element';
import {
	Panel,
	PanelBody,
	PanelRow,
	TextControl,
	Button,
	Flex,
	FlexItem,
} from '@wordpress/components';

// Components
import SettingsPageHeader from '../ui/SettingsPageHeader';
import useSettings from '../hooks/useSettings';
import Notices from '../ui/Notices';
import usePluginApi from '../hooks/usePluginApi';

export default function SettingsPage() {
	const { apiKey, isLoading, setApiKey, saveSettings, termsExist } =
		useSettings();
	const { generateTerms, fetchPosts, nextFetch } = usePluginApi();
	return (
		<>
			<SettingsPageHeader />
			<Panel>
				<PanelBody title="API Key" initialOpen={ '' === apiKey }>
					<PanelRow>
						<Flex style={ { alignItems: 'flex-end' } }>
							<FlexItem style={ { flexGrow: 1 } }>
								<TextControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									label="Insert API Key"
									type="text"
									placeholder="Api key..."
									value={ apiKey || '' }
									disabled={ isLoading }
									onChange={ ( value ) => setApiKey( value ) }
								/>
							</FlexItem>
							<FlexItem>
								<Button
									disabled={ isLoading }
									variant="primary"
									onClick={ saveSettings }
									__next40pxDefaultSize
								>
									Update API Key
								</Button>
							</FlexItem>
						</Flex>
					</PanelRow>
				</PanelBody>
				{ ! isLoading && '' !== apiKey && ! termsExist && (
					<PanelBody
						title="Generate Taxonomy Terms"
						initialOpen={ '' !== apiKey && ! termsExist }
					>
						<p>
							Generates Taxonomy terms for Chief's Blog and Iti
							Fabvssa under the newly created{ ' ' }
							<a href="/wp-admin/edit-tags.php?taxonomy=federated-post">
								Federated Posts
							</a>{ ' ' }
							taxonomy.
						</p>
						<PanelRow>
							<Flex style={ { width: 'auto' } }>
								<FlexItem>
									<Button
										disabled={ isLoading }
										variant="primary"
										onClick={ generateTerms }
										__next40pxDefaultSize
									>
										Generate Terms
									</Button>
								</FlexItem>
							</Flex>
						</PanelRow>
					</PanelBody>
				) }
				{ apiKey && (
					<PanelBody title="Fetch Posts" initialOpen={ true }>
						<p>
							Next Fetch Scheduled:{ ' ' }
							{ nextFetch || 'Not Scheduled' }
						</p>
						<PanelRow>
							<Flex style={ { width: 'auto' } }>
								<FlexItem>
									<Button
										disabled={ isLoading }
										variant="primary"
										onClick={ () =>
											fetchPosts( 'chiefs-blog' )
										}
										__next40pxDefaultSize
									>
										Fetch Chief's Blog Posts
									</Button>
								</FlexItem>
								<FlexItem>
									<Button
										disabled={ isLoading }
										variant="secondary"
										onClick={ () =>
											fetchPosts( 'iti-fabvssa' )
										}
										__next40pxDefaultSize
									>
										Fetch Iti Fabvssa Posts
									</Button>
								</FlexItem>
							</Flex>
						</PanelRow>
					</PanelBody>
				) }
			</Panel>
			<Notices />
		</>
	);
}
