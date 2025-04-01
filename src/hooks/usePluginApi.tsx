import useCreateToast from './useCreateToast';
import apiFetch from '@wordpress/api-fetch';

export default function usePluginApi() {
	const createToast = useCreateToast();
	async function generateTerms() {
		createToast( 'info', 'Generating terms...', undefined );
		try {
			const response = await apiFetch( {
				path: 'cno-federated-content/v1/generate-terms',
				method: 'POST',
			} );
			if ( response.status === 'success' ) {
				createToast( 'success', 'Terms generated successfully.', [
					{
						label: 'View Terms',
						url: '/wp-admin/edit-tags.php?taxonomy=federated-post',
					},
				] );
			}
		} catch ( err ) {
			console.error( err );
			createToast( 'error', `Error generating terms: ${ err.message }` );
		}
	}
	return {
		generateTerms,
	};
}
