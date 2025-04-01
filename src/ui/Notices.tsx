import React from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { SnackbarList } from '@wordpress/components';

export default function Notices() {
	const { removeNotice } = useDispatch( noticesStore );
	const notices = useSelect( ( select ) => {
		return select( noticesStore ).getNotices();
	} );
	if ( notices.length === 0 ) {
		return null;
	}
	return (
		<SnackbarList
			notices={ notices }
			onRemove={ ( notice ) => {
				removeNotice( notice );
			} }
		/>
	);
}
