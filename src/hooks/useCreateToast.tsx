import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import type { WPNoticeAction } from '@wordpress/notices/build-types/store/actions';

export default function useCreateToast() {
	const { createNotice } = useDispatch( noticesStore );

	const createToast = (
		status: 'info' | 'warning' | 'error' | 'success',
		message: string,
		actions: WPNoticeAction[] = [
			{
				label: 'Dismiss',
				url: null,
				onClick: null,
			},
		]
	) => {
		createNotice( status, message, {
			type: 'snackbar',
			isDismissible: true,
			actions,
		} );
	};
	return createToast;
}
