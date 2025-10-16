import { memo } from '@wordpress/element';

const SettingsPageHeader = memo( () => {
	return (
		<header style={ { marginBlock: 20 } }>
			<h1>Federated Content Settings</h1>
			<p>
				Get the latest content from the Choctaw Nation website to
				display natively on the site.
			</p>
		</header>
	);
} );
export default SettingsPageHeader;
