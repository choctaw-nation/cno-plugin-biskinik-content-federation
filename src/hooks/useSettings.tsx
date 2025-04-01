import React, { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { store as coreDataStore } from '@wordpress/core-data';

export default function useSettings() {
	const [ apiKey, setApiKey ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const { createNotice } = useDispatch( noticesStore );
	useEffect( () => {
		setIsLoading( true );
		apiFetch( { path: '/wp/v2/settings' } )
			.then( ( settings ) => {
				if ( settings.cno_biskinik_federated_content ) {
					setApiKey( settings.cno_biskinik_federated_content );
				}
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	}, [] );

	const saveSettings = () => {
		setIsLoading( true );
		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: { cno_biskinik_federated_content: apiKey },
		} )
			.then( ( settings ) => {
				if ( settings.cno_biskinik_federated_content ) {
					setApiKey( settings.cno_biskinik_federated_content );

					createNotice( 'success', 'Settings saved successfully.', {
						type: 'snackbar',
						isDismissible: true,
						actions: [
							{
								label: 'Dismiss',
							},
						],
					} );
				}
			} )
			.catch( ( error ) => {
				createNotice( 'error', `Error saving settings: ${ error }`, {
					type: 'snackbar',
					isDismissible: true,
				} );
			} )
			.finally( () => {
				setIsLoading( false );
			} );
	};

	const taxonomy = useSelect(
		( select ) =>
			select( coreDataStore ).getEntityRecords(
				'taxonomy',
				'federated-post'
			),
		[]
	);

	return {
		apiKey,
		isLoading,
		termsExist: taxonomy && taxonomy.length > 0,
		setApiKey,
		saveSettings,
	};
}
