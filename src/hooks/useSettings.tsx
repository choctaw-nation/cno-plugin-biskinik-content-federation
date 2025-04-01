import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import useCreateToast from './useCreateToast';

export default function useSettings() {
	const [ apiKey, setApiKey ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const createToast = useCreateToast();
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
					createToast( 'success', 'Settings saved successfully.' );
				}
			} )
			.catch( ( error ) => {
				createToast( 'error', `Error saving settings: ${ error }` );
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
