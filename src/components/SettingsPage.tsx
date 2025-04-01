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

export default function SettingsPage() {
	const { apiKey, isLoading, setApiKey, saveSettings, termsExist } =
		useSettings();

	return (
		<>
			<SettingsPageHeader />
			<Panel>
				<PanelBody
					title="API Key"
					initialOpen={ apiKey ? false : true }
				>
					<PanelRow>
						<div style={ { width: '100%' } }>
							<TextControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								label="Insert API Key"
								type="input"
								placeholder="Api key..."
								value={ apiKey || '' }
								disabled={ isLoading }
								onChange={ ( value ) => setApiKey( value ) }
							/>
						</div>
					</PanelRow>
				</PanelBody>
				{ apiKey && ! termsExist && (
					<PanelBody
						title="Generate Taxonomy Terms"
						initialOpen={ apiKey && ! termsExist }
					>
						<PanelRow>
							<Flex style={ { width: 'auto' } }>
								<FlexItem>
									<Button
										disabled={ isLoading }
										variant="primary"
										onClick={ saveSettings }
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
							{ new Date().toLocaleString( 'en-US' ) }
						</p>
						<PanelRow>
							<Flex style={ { width: 'auto' } }>
								<FlexItem>
									<Button
										disabled={ isLoading }
										variant="primary"
										onClick={ saveSettings }
										__next40pxDefaultSize
									>
										Fetch Posts
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
