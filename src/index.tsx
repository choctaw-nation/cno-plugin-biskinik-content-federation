// WordPress dependencies
import React, { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

// Component
import SettingsPage from './components/SettingsPage';

domReady( () => {
	const root = document.getElementById(
		'cno-biskinik-federated-content-settings'
	);
	if ( root ) {
		createRoot( root ).render( <SettingsPage /> );
	}
} );
