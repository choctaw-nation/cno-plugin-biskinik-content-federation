import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

export default function useCreateToast() {
	const { createNotice } = useDispatch( noticesStore );

	const createToast = (
		status: 'info' | 'warning' | 'error' | 'success',
		message: string,
		actions: [
			{
				label: string;
				url?: string;
			},
		] = [
			{
				label: 'Dismiss',
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
