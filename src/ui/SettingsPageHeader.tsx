import React, { memo } from '@wordpress/element';
import {
	__experimentalHeading as Heading,
	__experimentalText as Text,
} from '@wordpress/components';

const SettingsPageHeader = memo( () => {
	return (
		<header style={ { marginBlock: 20 } }>
			<Heading>Federated Content Settings</Heading>
			<Text>
				Get the latest content from the Choctaw Nation website to
				display natively on the site.
			</Text>
		</header>
	);
} );
export default SettingsPageHeader;
