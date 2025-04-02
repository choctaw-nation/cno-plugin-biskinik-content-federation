import useCreateToast from './useCreateToast';
import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';

export default function usePluginApi() {
	const [ nextFetch, setNextFetch ] = useState( '' );
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
						onClick: null,
					},
				] );
			}
		} catch ( err ) {
			console.error( err );
			createToast( 'error', `Error generating terms: ${ err.message }` );
		}
	}

	async function fetchPosts( slug: 'chiefs-blog' | 'iti-fabvssa' ) {
		createToast(
			'info',
			`Fetching ${ slugLookup( slug ) } posts...`,
			undefined
		);
		try {
			const response = await apiFetch( {
				path: 'cno-federated-content/v1/fetch-posts',
				method: 'POST',
				data: JSON.stringify( {
					slug,
				} ),
			} );
			if ( response.status === 'success' ) {
				createToast( 'success', response.message, [
					{
						label: 'View Posts',
						url: '/wp-admin/edit.php?post_type=post',
						onClick: null,
					},
				] );
			}
		} catch ( err ) {
			console.error( err );
			createToast( 'error', `Error fetching terms: ${ err.message }` );
		}
	}

	async function getNextFetch() {
		try {
			const response = await apiFetch( {
				path: 'cno-federated-content/v1/get-next-fetch',
			} );
			return response.message;
		} catch ( err ) {
			console.error( err );
			createToast(
				'error',
				`Error getting next fetch: ${ err.message }`
			);
		}
	}
	getNextFetch().then( ( date ) => setNextFetch( date ) );
	return {
		generateTerms,
		fetchPosts,
		nextFetch,
	};
}

/**
 * Swaps a slug for a title.
 *
 * @param slug the slug to swap
 * @returns
 */
function slugLookup( slug: string ): string {
	const slugsToTitles = {
		'chiefs-blog': "Chief's Blog",
		'iti-fabvssa': 'Iti Fabvssa',
	};
	if ( slug in slugsToTitles ) {
		return slugsToTitles[ slug ];
	}
	return slug;
}
